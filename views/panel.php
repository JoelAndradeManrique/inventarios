<?php
// 1. Incluimos BD y Header (SEGURIDAD: Esto se ejecuta antes de cargar HTML)
// Si el usuario no tiene sesión, header.php lo expulsa inmediatamente.
require_once '../config/db.php';
require_once '../components/header.php'; 

// 2. Lógica de Estadísticas (KPIs)
try {
    // Contar productos
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos");
    $totalProductos = $stmt->fetchColumn();

    // Contar alertas (Stock bajo)
    $stmt = $pdo->query("SELECT COUNT(*) FROM productos WHERE stock_actual <= stock_minimo");
    $stockBajo = $stmt->fetchColumn();

    // Sumar valor inventario (Solo Admin)
    $valorInventario = 0;
    if ($rol === 'ADMIN') {
        $stmt = $pdo->query("SELECT SUM(precio_compra * stock_actual) FROM productos");
        $valorInventario = $stmt->fetchColumn() ?: 0;
    }
} catch (Exception $e) {
    $totalProductos = 0;
    $stockBajo = 0;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Inventarios</title>
    <style>
        /* Estilos base que ya tenías */
        body { background: #f7f7f7; margin: 0; font-family: Arial, sans-serif; }
        .container { width: 95%; max-width: 1200px; margin: 25px auto; }
        
        /* Estilos rápidos para las tarjetas si no creaste panel.css */
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.07); border-left: 5px solid #ccc; transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); }
        .card h3 { margin: 0 0 10px 0; color: #555; font-size: 16px; text-transform: uppercase; }
        .card .number { font-size: 36px; font-weight: bold; color: #333; margin-bottom: 5px; }
        
        .card.blue { border-left-color: #3498db; }
        .card.blue .number { color: #3498db; }
        .card.red { border-left-color: #e74c3c; background-color: #fff5f5; }
        .card.red .number { color: #e74c3c; }
        .card.yellow { border-left-color: #f1c40f; }
        .card.yellow .number { color: #f39c12; }
    </style>
</head>
<body>

<div class="container">
    
    <div class="welcome-box" style="background:white; padding:20px; border-radius:10px; margin-bottom:20px;">
        <h2 style="margin-top:0; color:#444;">Hola, <?php echo htmlspecialchars($nombre); ?> 👋</h2>
        <p style="color:#666;">Aquí tienes el resumen de tu sistema al día de hoy.</p>
    </div>

    <div class="dashboard-grid">
        
        <div class="card blue">
            <h3>Total Productos</h3>
            <div class="number"><?php echo $totalProductos; ?></div>
            <small>Registrados en sistema</small>
        </div>

        <div class="card <?php echo ($stockBajo > 0) ? 'red' : 'green'; ?>">
            <h3>Alertas Stock</h3>
            <div class="number"><?php echo $stockBajo; ?></div>
            <small><?php echo ($stockBajo > 0) ? 'Productos por agotarse' : 'Inventario saludable'; ?></small>
        </div>

        <?php if ($rol === 'ADMIN'): ?>
        <div class="card yellow">
            <h3>Valor Inventario</h3>
            <div class="number">$<?php echo number_format($valorInventario, 2); ?></div>
            <small>Capital invertido</small>
        </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>