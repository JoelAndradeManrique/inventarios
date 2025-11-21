<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

// Validar sesión: Solo usuarios logueados pueden hacer corte
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

$idUsuario = $_SESSION['user_id'];

try {
    // Iniciamos Transacción (Todo o Nada)
    $pdo->beginTransaction();

    // 1. VERIFICAR SI HAY MOVIMIENTOS PENDIENTES (Sin corte)
    // Solo nos interesa sumar dinero de las SALIDAS (Ventas)
    // Las ENTRADAS (Compras) se archivan pero no suman dinero a la caja
    $sqlCalc = "SELECT 
                    SUM(cantidad * precio_venta_momento) as total_venta,
                    SUM(cantidad * (precio_venta_momento - precio_compra_momento)) as total_ganancia
                FROM movimientos 
                WHERE id_usuario = ? 
                  AND id_corte IS NULL 
                  AND tipo_movimiento = 'SALIDA'";

    $stmt = $pdo->prepare($sqlCalc);
    $stmt->execute([$idUsuario]);
    $totales = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si total_venta es NULL (no hubo ventas), lo ponemos en 0
    $venta = $totales['total_venta'] ?? 0;
    $ganancia = $totales['total_ganancia'] ?? 0;

    // Verificamos también si hubo ENTRADAS pendientes (para no cerrar caja vacía si solo hubo reabasto)
    $sqlEntradas = "SELECT COUNT(*) FROM movimientos WHERE id_usuario = ? AND id_corte IS NULL";
    $stmtEnt = $pdo->prepare($sqlEntradas);
    $stmtEnt->execute([$idUsuario]);
    $movimientosPendientes = $stmtEnt->fetchColumn();

    if ($movimientosPendientes == 0) {
        throw new Exception("No hay movimientos pendientes para realizar un corte.");
    }

    // 2. CREAR EL REPORTE EN LA TABLA 'CORTES'
    // Guardamos cuánto se vendió y cuánto se ganó limpio
    $sqlInsert = "INSERT INTO cortes (id_usuario, fecha_corte, ventas_totales, ganancias_totales) 
                  VALUES (?, NOW(), ?, ?)";
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([$idUsuario, $venta, $ganancia]);
    
    // Obtenemos el ID del corte recién creado (Ej: Corte #50)
    $idCorte = $pdo->lastInsertId();

    // 3. ACTUALIZAR LOS MOVIMIENTOS (SELLARLOS)
    // Le ponemos el id_corte a todos los movimientos pendientes de este usuario (Entradas y Salidas)
    $sqlUpdate = $pdo->prepare("UPDATE movimientos SET id_corte = ? WHERE id_usuario = ? AND id_corte IS NULL");
    $sqlUpdate->execute([$idCorte, $idUsuario]);

    // Confirmamos cambios
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => "Corte realizado con éxito.",
        'data' => [
            'folio_corte' => $idCorte,
            'total_vendido' => number_format($venta, 2),
            'ganancia_estimada' => number_format($ganancia, 2)
        ]
    ]);

} catch (Exception $e) {
    // Si algo falla, regresamos todo a como estaba
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>