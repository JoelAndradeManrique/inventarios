<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

// 1. SEGURIDAD: Verificar sesión activa
if (!isset($_SESSION['user_id'])) { 
    http_response_code(403);
    echo json_encode(['success'=>false, 'message'=>'Acceso denegado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Para PUT y DELETE leemos el JSON body
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        
        // === LISTAR Y FILTRAR (GET) ===
        case 'GET':
            $busqueda = $_GET['busqueda'] ?? '';
            $catId    = $_GET['categoria'] ?? ''; 

            // Query base con JOIN para traer el nombre de la categoría
            $sql = "SELECT p.*, c.nombre as nombre_categoria 
                    FROM productos p 
                    JOIN categorias c ON p.id_categoria = c.id_categoria WHERE 1=1";
            
            $params = [];
            
            // Filtro por Texto (Nombre o SKU)
            if (!empty($busqueda)) {
                $sql .= " AND (p.nombre LIKE ? OR p.codigo_sku LIKE ?)";
                $params[] = "%$busqueda%";
                $params[] = "%$busqueda%";
            }

            // Filtro por Categoría
            if (!empty($catId)) {
                $sql .= " AND p.id_categoria = ?";
                $params[] = $catId;
            }
            
            $sql .= " ORDER BY p.nombre ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $productos = $stmt->fetchAll();

            // Agregamos flag de ALERTA para el frontend
            foreach ($productos as &$prod) {
                $prod['alerta'] = ($prod['stock_actual'] <= $prod['stock_minimo']);
            }

            echo json_encode($productos);
            break;

        // === CREAR PRODUCTO (POST) ===
        case 'POST':
            // NOTA: Usamos $_POST y $_FILES porque viene como FormData (con foto)
            
            // 1. Validaciones de campos vacíos
            if (empty($_POST['id_categoria']) || empty($_POST['nombre']) || empty($_POST['precio_venta'])) {
                echo json_encode(['success'=>false, 'message'=>'Faltan datos obligatorios']); exit;
            }

            // 2. Validación de números NEGATIVOS (Blindaje)
            if ($_POST['precio_compra'] < 0 || $_POST['precio_venta'] < 0 || 
                $_POST['stock_minimo'] < 0 || ($_POST['stock_actual'] ?? 0) < 0) {
                echo json_encode(['success'=>false, 'message'=>'No se permiten valores negativos.']);
                exit;
            }

            $pdo->beginTransaction(); // Iniciamos transacción

            try {
                // 3. PROCESAR IMAGEN (Si se subió alguna)
                $nombreImagen = NULL;
                
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    $directorio = "../uploads/";
                    
                    // PROTECCIÓN: Si la carpeta no existe, la creamos
                    if (!is_dir($directorio)) {
                        mkdir($directorio, 0777, true);
                    }
                    
                    // Generar nombre único
                    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $nombreImagen = uniqid() . "." . $ext; 
                    
                    move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio . $nombreImagen);
                }

                // 4. INSERTAR DATOS (Sin SKU final aún)
                $sqlInsert = "INSERT INTO productos (id_categoria, codigo_sku, nombre, contenido_neto, precio_compra, precio_venta, stock_minimo, stock_actual, imagen) 
                              VALUES (?, 'TEMP', ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sqlInsert);
                $stmt->execute([
                    $_POST['id_categoria'],
                    $_POST['nombre'],
                    $_POST['contenido_neto'],
                    $_POST['precio_compra'],
                    $_POST['precio_venta'],
                    $_POST['stock_minimo'],
                    $_POST['stock_actual'] ?? 0,
                    $nombreImagen
                ]);

                $lastId = $pdo->lastInsertId();

                // 5. GENERAR SKU INTELIGENTE
                // Obtenemos prefijo de categoría (Ej: 'B')
                $stmtCat = $pdo->prepare("SELECT prefijo FROM categorias WHERE id_categoria = ?");
                $stmtCat->execute([$_POST['id_categoria']]);
                $prefijoCat = $stmtCat->fetchColumn();

                // Limpiamos nombre (COCA COLA -> COCA)
                $nombreCorto = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $_POST['nombre']), 0, 4));
                
                // Formato: P{Cat}-{Nombre}-{Cont}-{ID con ceros}
                $idPadding = str_pad($lastId, 5, "0", STR_PAD_LEFT);
                $skuFinal = "P{$prefijoCat}-{$nombreCorto}-{$_POST['contenido_neto']}-{$idPadding}";

                // 6. ACTUALIZAR SKU
                $sqlUpdate = $pdo->prepare("UPDATE productos SET codigo_sku = ? WHERE id_producto = ?");
                $sqlUpdate->execute([$skuFinal, $lastId]);

                $pdo->commit();
                echo json_encode(['success'=>true, 'message'=>'Producto registrado correctamente']);

            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success'=>false, 'message'=>'Error: ' . $e->getMessage()]);
            }
            break;

        // === EDITAR PRODUCTO (PUT) ===
        case 'PUT':
            // Validamos negativos
            if ($input['precio_compra'] < 0 || $input['precio_venta'] < 0 || $input['stock_minimo'] < 0) {
                echo json_encode(['success'=>false, 'message'=>'No se permiten precios o stock negativos.']);
                exit;
            }

            // Nota: El SKU y la Imagen NO se editan por aquí en esta versión simple
            $sql = "UPDATE productos SET nombre=?, contenido_neto=?, precio_compra=?, precio_venta=?, stock_minimo=? WHERE id_producto=?";
            $stmt = $pdo->prepare($sql);
            
            if($stmt->execute([
                $input['nombre'],
                $input['contenido_neto'],
                $input['precio_compra'],
                $input['precio_venta'],
                $input['stock_minimo'],
                $input['id_producto']
            ])) {
                echo json_encode(['success'=>true, 'message'=>'Producto actualizado']);
            } else {
                echo json_encode(['success'=>false, 'message'=>'Error al actualizar']);
            }
            break;

        // === ELIMINAR PRODUCTO (DELETE) ===
        case 'DELETE':
            // Solo Admin
            if ($_SESSION['user_role'] !== 'ADMIN') {
                echo json_encode(['success'=>false, 'message'=>'Solo el Administrador puede borrar productos']); exit;
            }
            
            try {
                // Primero intentamos borrar la imagen física si existe (Opcional, mejora limpieza)
                $stmtImg = $pdo->prepare("SELECT imagen FROM productos WHERE id_producto = ?");
                $stmtImg->execute([$input['id_producto']]);
                $img = $stmtImg->fetchColumn();
                
                $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
                $stmt->execute([$input['id_producto']]);
                
                // Si se borró de BD, borramos el archivo
                if ($img && file_exists("../uploads/" . $img)) {
                    unlink("../uploads/" . $img);
                }

                echo json_encode(['success'=>true, 'message'=>'Producto eliminado']);
            } catch (PDOException $e) {
                // Error común: FK constraint (tiene ventas asociadas)
                echo json_encode(['success'=>false, 'message'=>'No se puede borrar: El producto tiene historial de movimientos.']);
            }
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false, 'message'=>'Error servidor: '.$e->getMessage()]);
}
?>