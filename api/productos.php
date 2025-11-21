<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { 
    http_response_code(403);
    echo json_encode(['success'=>false, 'message'=>'Acceso denegado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        
        // === LISTAR Y BUSCAR [cite: 13, 14] ===
        case 'GET':
            $busqueda = $_GET['busqueda'] ?? '';
            $catId    = $_GET['categoria'] ?? ''; // <--- NUEVO FILTRO

            $sql = "SELECT p.*, c.nombre as nombre_categoria 
                    FROM productos p 
                    JOIN categorias c ON p.id_categoria = c.id_categoria WHERE 1=1"; // Truco SQL para concatenar ANDs
            
            $params = [];
            
            // Filtro por Texto
            if (!empty($busqueda)) {
                $sql .= " AND (p.nombre LIKE ? OR p.codigo_sku LIKE ?)";
                $params[] = "%$busqueda%";
                $params[] = "%$busqueda%";
            }

            // NUEVO: Filtro por Categoría
            if (!empty($catId)) {
                $sql .= " AND p.id_categoria = ?";
                $params[] = $catId;
            }
            
            $sql .= " ORDER BY p.nombre ASC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $productos = $stmt->fetchAll();

            // Flag de alerta
            foreach ($productos as &$prod) {
                $prod['alerta'] = ($prod['stock_actual'] <= $prod['stock_minimo']);
            }

            echo json_encode($productos);
            break;

        // === CREAR PRODUCTO (Con SKU Inteligente) [cite: 11] ===
        case 'POST':
            // NOTA: Al enviar archivos (FormData), los datos llegan en $_POST y $_FILES, no en el input JSON.
            
            // 1. Validaciones básicas (usamos $_POST porque viene de FormData)
            if (empty($_POST['id_categoria']) || empty($_POST['nombre']) || empty($_POST['precio_venta'])) {
                echo json_encode(['success'=>false, 'message'=>'Faltan datos obligatorios']); exit;
            }

            $pdo->beginTransaction();

            try {
                // 2. PROCESAR IMAGEN (Si se subió alguna)
                $nombreImagen = NULL;
                
                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                    // Definir directorio (Asegúrate de crear la carpeta 'uploads' en la raíz de tu proyecto)
                    $directorio = "../uploads/";
                    
                    // Generar nombre único para evitar reemplazar fotos con el mismo nombre
                    $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $nombreImagen = uniqid() . "." . $ext; // Ej: 654a3d2.jpg
                    
                    // Mover el archivo temporal a la carpeta final
                    move_uploaded_file($_FILES['imagen']['tmp_name'], $directorio . $nombreImagen);
                }

                // 3. INSERTAR DATOS (Usando $_POST)
                $sqlInsert = "INSERT INTO productos (id_categoria, codigo_sku, nombre, contenido_neto, precio_compra, precio_venta, stock_minimo, stock_actual, imagen) 
                              VALUES (?, 'TEMP', ?, ?, ?, ?, ?, ?, ?)"; // Agregamos el campo imagen
                $stmt = $pdo->prepare($sqlInsert);
                $stmt->execute([
                    $_POST['id_categoria'],
                    $_POST['nombre'],
                    $_POST['contenido_neto'],
                    $_POST['precio_compra'],
                    $_POST['precio_venta'],
                    $_POST['stock_minimo'],
                    $_POST['stock_actual'] ?? 0,
                    $nombreImagen // Guardamos el nombre del archivo o NULL
                ]);

                $lastId = $pdo->lastInsertId();

                // 4. GENERAR SKU INTELIGENTE (Lógica idéntica a la anterior)
                $stmtCat = $pdo->prepare("SELECT prefijo FROM categorias WHERE id_categoria = ?");
                $stmtCat->execute([$_POST['id_categoria']]);
                $prefijoCat = $stmtCat->fetchColumn();

                $nombreCorto = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $_POST['nombre']), 0, 4));
                $idPadding = str_pad($lastId, 5, "0", STR_PAD_LEFT);
                $skuFinal = "P{$prefijoCat}-{$nombreCorto}-{$_POST['contenido_neto']}-{$idPadding}";

                // 5. ACTUALIZAR SKU
                $sqlUpdate = $pdo->prepare("UPDATE productos SET codigo_sku = ? WHERE id_producto = ?");
                $sqlUpdate->execute([$skuFinal, $lastId]);

                $pdo->commit();
                echo json_encode(['success'=>true, 'message'=>'Producto registrado con éxito']);

            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success'=>false, 'message'=>'Error: ' . $e->getMessage()]);
            }
            break;
        // === EDITAR PRODUCTO ===
        case 'PUT':
            // Nota: El SKU NO se edita, es único del producto
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

        // === ELIMINAR PRODUCTO [cite: 9] ===
        // Solo el Admin debería poder borrar (validar en frontend también)
        case 'DELETE':
            if ($_SESSION['user_role'] !== 'ADMIN') {
                echo json_encode(['success'=>false, 'message'=>'Solo Admin puede borrar productos']); exit;
            }
            
            try {
                $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = ?");
                $stmt->execute([$input['id_producto']]);
                echo json_encode(['success'=>true, 'message'=>'Producto eliminado']);
            } catch (PDOException $e) {
                // Error común: No se puede borrar si ya tiene ventas (integridad referencial)
                echo json_encode(['success'=>false, 'message'=>'No se puede borrar: El producto tiene movimientos asociados.']);
            }
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success'=>false, 'message'=>'Error servidor: '.$e->getMessage()]);
}
?>