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
            if (empty($input['tipo']) || empty($input['productos'])) {
                echo json_encode(['success'=>false, 'message'=>'Datos incompletos']); exit;
            }

            $tipo = $input['tipo']; 
            $listaProductos = $input['productos'];
            $idUsuario = $_SESSION['user_id']; 
            $folio = strtoupper(uniqid('MOV-')); 

            $pdo->beginTransaction(); 
            
            // NUEVO: Dos listas separadas
            $alertasBajas = []; // Para stock <= minimo (pero > 0)
            $alertasCero = [];  // Para stock == 0

            try {
                foreach ($listaProductos as $item) {
                    $idProd = $item['id'];
                    $cantidad = $item['cantidad'];

                    if ($cantidad <= 0) continue;

                    // 1. Obtener datos
                    $stmt = $pdo->prepare("SELECT stock_actual, stock_minimo, nombre, codigo_sku, precio_compra, precio_venta FROM productos WHERE id_producto = ?");
                    $stmt->execute([$idProd]);
                    $prodDB = $stmt->fetch();

                    if (!$prodDB) { throw new Exception("Producto ID $idProd no encontrado."); }

                    // 2. VALIDACIÓN Y CÁLCULO
                    if ($tipo === 'SALIDA') {
                        if ($prodDB['stock_actual'] < $cantidad) {
                            throw new Exception("Stock insuficiente para '{$prodDB['nombre']}'. Disponibles: {$prodDB['stock_actual']}");
                        }
                        $nuevoStock = $prodDB['stock_actual'] - $cantidad;

                        // === LÓGICA DE DOBLE AVISO ===
                        if ($nuevoStock == 0) {
                            // CASO CRÍTICO: Se acabó
                            $alertasCero[] = $prodDB['nombre']; // Guardamos el nombre para el mensaje
                        } elseif ($nuevoStock <= $prodDB['stock_minimo']) {
                            // CASO ADVERTENCIA: Bajó del mínimo
                            $alertasBajas[] = $prodDB['nombre'];
                        }

                    } else {
                        $nuevoStock = $prodDB['stock_actual'] + $cantidad;
                    }

                    // 3. ACTUALIZAR STOCK
                    $updateStmt = $pdo->prepare("UPDATE productos SET stock_actual = ? WHERE id_producto = ?");
                    $updateStmt->execute([$nuevoStock, $idProd]);

                    // 4. HISTORIAL
                    $sqlMov = "INSERT INTO movimientos (tipo_movimiento, id_producto, id_usuario, cantidad, precio_compra_momento, precio_venta_momento, folio_transaccion) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmtMov = $pdo->prepare($sqlMov);
                    $stmtMov->execute([
                        $tipo, $idProd, $idUsuario, $cantidad,
                        $prodDB['precio_compra'], $prodDB['precio_venta'], $folio
                    ]);
                }

                $pdo->commit();
                
                // Enviamos las dos listas en la respuesta
                echo json_encode([
                    'success'=>true, 
                    'message'=>"Movimiento registrado. Folio: $folio",
                    'alertas_bajas' => $alertasBajas,
                    'alertas_cero' => $alertasCero
                ]);

            } catch (Exception $e) {
                $pdo->rollBack(); 
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