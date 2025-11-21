<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { exit; }

// Simplemente devolvemos todas las categorías para el <select> del formulario
try {
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre ASC");
    echo json_encode($stmt->fetchAll());
} catch (Exception $e) {
    echo json_encode([]);
}
?>