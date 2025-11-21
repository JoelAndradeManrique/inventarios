<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

// Validar sesión 
if (!isset($_SESSION['user_id'])) { 
    http_response_code(403);
    echo json_encode(['success'=>false, 'message'=>'Acceso denegado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        
        // === REGISTRAR MOVIMIENTO (COMPRA O VENTA) ===
        case 'POST':
            // Esperamos: { tipo: 'SALIDA', productos: [ {id: 1, cantidad: 5}, ... ] }
            
            if (empty($input['tipo']) || empty($input['productos'])) {
                echo json_encode(['success'=>false, 'message'=>'Datos incompletos']); exit;
            }

            $tipo = $input['tipo']; // 'ENTRADA' o 'SALIDA'
            $listaProductos = $input['productos'];
            $idUsuario = $_SESSION['user_id']; // 
            
            // Generamos un FOLIO único para este grupo de movimientos (El "Ticket")
            $folio = strtoupper(uniqid('MOV-')); 

            $pdo->beginTransaction(); // ¡CRÍTICO! Todo o nada.

            try {
                foreach ($listaProductos as $item) {
                    $idProd = $item['id'];
                    $cantidad = $item['cantidad'];

                    if ($cantidad <= 0) continue;

                    // 1. Obtenemos datos actuales del producto (Stock y Precios)
                    $stmt = $pdo->prepare("SELECT stock_actual, nombre, precio_compra, precio_venta FROM productos WHERE id_producto = ?");
                    $stmt->execute([$idProd]);
                    $prodDB = $stmt->fetch();

                    if (!$prodDB) {
                        throw new Exception("El producto ID $idProd no existe.");
                    }

                    // 2. VALIDACIÓN DE STOCK 
                    if ($tipo === 'SALIDA') {
                        if ($prodDB['stock_actual'] < $cantidad) {
                            throw new Exception("Stock insuficiente para '{$prodDB['nombre']}'. Disponibles: {$prodDB['stock_actual']}");
                        }
                        $nuevoStock = $prodDB['stock_actual'] - $cantidad;
                    } else {
                        // ENTRADA (Sumar stock) 
                        $nuevoStock = $prodDB['stock_actual'] + $cantidad;
                    }

                    // 3. ACTUALIZAR STOCK EN PRODUCTOS
                    $updateStmt = $pdo->prepare("UPDATE productos SET stock_actual = ? WHERE id_producto = ?");
                    $updateStmt->execute([$nuevoStock, $idProd]);

                    // 4. REGISTRAR EL MOVIMIENTO EN EL HISTORIAL 
                    // Guardamos precio_compra y precio_venta HISTÓRICOS para calcular ganancias exactas después
                    $sqlMov = "INSERT INTO movimientos (tipo_movimiento, id_producto, id_usuario, cantidad, precio_compra_momento, precio_venta_momento, folio_transaccion) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmtMov = $pdo->prepare($sqlMov);
                    $stmtMov->execute([
                        $tipo,
                        $idProd,
                        $idUsuario,
                        $cantidad,
                        $prodDB['precio_compra'], // Precio congelado al momento
                        $prodDB['precio_venta'],  // Precio congelado al momento
                        $folio
                    ]);
                }

                $pdo->commit();
                echo json_encode(['success'=>true, 'message'=>"Movimiento registrado con éxito. Folio: $folio"]);

            } catch (Exception $e) {
                $pdo->rollBack(); // Si falla algo (ej: falta stock en el 3er producto), deshacemos TODO.
                echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
            }
            break;

        // === VER HISTORIAL (Para el módulo de Reportes o Movimientos) ===
        case 'GET':
            // Últimos 50 movimientos
            $sql = "SELECT m.*, p.nombre as producto, p.codigo_sku, u.nombre as usuario 
                    FROM movimientos m
                    JOIN productos p ON m.id_producto = p.id_producto
                    JOIN usuarios u ON m.id_usuario = u.id_usuario
                    ORDER BY m.fecha_movimiento DESC LIMIT 50";
            
            $stmt = $pdo->query($sql);
            echo json_encode($stmt->fetchAll());
            break;
    }

} catch (Exception $e) {
    echo json_encode(['success'=>false, 'message'=>'Error servidor: ' . $e->getMessage()]);
}
?>