<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

// SEGURIDAD: Solo Admin puede ver reportes avanzados [cite: 8]
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$tipo = $_GET['tipo'] ?? 'MOVIMIENTOS'; // 'MOVIMIENTOS' o 'CORTES'
$fechaInicio = $_GET['desde'] ?? date('Y-m-d');
$fechaFin = $_GET['hasta'] ?? date('Y-m-d');

// Ajustamos las horas para cubrir todo el día (00:00:00 a 23:59:59)
$inicio = "$fechaInicio 00:00:00";
$fin = "$fechaFin 23:59:59";

try {
    if ($tipo === 'CORTES') {
        // REPORTE FINANCIERO + DETALLE DE PRODUCTOS
        // Usamos una subconsulta para concatenar los productos vendidos en ese corte
        $sql = "SELECT c.*, u.nombre as usuario,
                (
                    SELECT GROUP_CONCAT(CONCAT(p.nombre, ' (x', m.cantidad, ')') SEPARATOR '||') 
                    FROM movimientos m
                    JOIN productos p ON m.id_producto = p.id_producto
                    WHERE m.id_corte = c.id_corte AND m.tipo_movimiento = 'SALIDA'
                ) as detalle_productos
                FROM cortes c 
                JOIN usuarios u ON c.id_usuario = u.id_usuario
                WHERE c.fecha_corte BETWEEN ? AND ?
                ORDER BY c.fecha_corte DESC";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$inicio, $fin]);
        echo json_encode($stmt->fetchAll());

    } else {
        // REPORTE OPERATIVO (Entradas y Salidas detalladas) 
        // Aquí mostramos TODO: Ventas y Reabastecimientos
        $sql = "SELECT m.*, p.nombre as producto, p.codigo_sku, u.nombre as usuario 
                FROM movimientos m
                JOIN productos p ON m.id_producto = p.id_producto
                JOIN usuarios u ON m.id_usuario = u.id_usuario
                WHERE m.fecha_movimiento BETWEEN ? AND ?
                ORDER BY m.fecha_movimiento DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$inicio, $fin]);
        echo json_encode($stmt->fetchAll());
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>