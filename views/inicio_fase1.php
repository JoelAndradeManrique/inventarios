<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Recuperamos datos de sesión
$nombre = $_SESSION['user_name'] ?? 'Usuario';
$rolRaw = $_SESSION['user_role'] ?? 'INVITADO';

// Formateamos el rol para que se vea bonito (ADMIN -> Administrador)
$rolBonito = ($rolRaw === 'ADMIN') ? 'Administrador' : 'Empleado';

// Iniciales para el avatar
$iniciales = strtoupper(substr($nombre, 0, 2)); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - Fase 1</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f4f4; }
        
        /* --- ESTILOS DEL HEADER (MAQUETADO) --- */
        .header { background-color: #1e1e2f; color: white; padding: 0 20px; height: 60px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .logo { font-size: 20px; font-weight: bold; color: #f5d04c; text-decoration: none; margin-right: 40px; }
        
        .nav { display: flex; gap: 20px; }
        .nav a { text-decoration: none; color: #ccc; font-size: 14px; transition: 0.3s; cursor: not-allowed; }
        .nav a:hover { color: white; }
        .nav a.active { color: white; font-weight: bold; border-bottom: 2px solid #f5d04c; }

        .user-menu { display: flex; align-items: center; gap: 10px; cursor: pointer; position: relative; }
        .user-initials { width: 35px; height: 35px; background-color: #f5d04c; color: #1e1e2f; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 14px; }

        .dropdown { position: absolute; top: 50px; right: 0; background: white; border: 1px solid #ccc; border-radius: 5px; width: 150px; display: none; flex-direction: column; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .user-menu:hover .dropdown { display: flex; }
        .dropdown a { color: #333; padding: 10px; text-decoration: none; font-size: 13px; cursor: pointer; }
        .dropdown a:hover { background: #f0f0f0; }

        /* --- CONTENIDO --- */
        .container { padding: 40px; max-width: 1200px; margin: 0 auto; }
        
        .welcome-banner { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 30px; border-left: 5px solid #f5d04c; }
        .welcome-banner h1 { margin: 0; color: #333; font-size: 24px; display: flex; align-items: center; gap: 10px; }
        .welcome-banner p { margin: 10px 0 0; color: #666; }

        /* Etiqueta de Rol */
        .role-badge { 
            background-color: #e0e0e0; 
            color: #555; 
            font-size: 12px; 
            padding: 4px 10px; 
            border-radius: 15px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            vertical-align: middle;
        }
        /* Si es Admin, la ponemos dorada/amarilla */
        .role-admin { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }

        .grid-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        
        .card { background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); opacity: 0.7; }
        .card h3 { margin: 0 0 10px; color: #555; font-size: 14px; text-transform: uppercase; }
        .card .number { font-size: 30px; font-weight: bold; color: #ccc; margin-bottom: 5px; }
        .card small { font-size: 12px; color: #999; font-style: italic; }

        .alert-box { margin-top: 40px; padding: 15px; background: #e8f4fd; color: #0c5460; border: 1px solid #bee5eb; border-radius: 5px; text-align: center; }
    </style>
</head>
<body>

    <header class="header">
        <div style="display:flex; align-items:center;">
            <a href="#" class="logo">Inventarios v1.0</a>
            <nav class="nav">
                <a href="#" class="active">Inicio</a>
                <a href="#" title="Módulo en desarrollo">Usuarios</a>
                <a href="#" title="Módulo en desarrollo">Movimientos</a>
                <a href="#" title="Módulo en desarrollo">Inventarios</a>
                <a href="#" title="Módulo en desarrollo">Reportes</a>
            </nav>
        </div>

        <div class="user-menu">
            <div class="user-initials"><?php echo $iniciales; ?></div>
            <div class="dropdown">
                <a href="#">Perfil (Próximamente)</a>
                <a href="../api/logout.php" style="color:red;">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <div class="container">
        
        <div class="welcome-banner">
            <h1>
                Bienvenido, <?php echo htmlspecialchars($nombre); ?>
                <span class="role-badge <?php echo ($rolRaw === 'ADMIN') ? 'role-admin' : ''; ?>">
                    <?php echo $rolBonito; ?>
                </span>
            </h1>
            <p>Has ingresado correctamente al sistema de gestión.</p>
        </div>

        <div class="alert-box">
            ℹ️ <strong>Estado del Proyecto:</strong> Fase 1 completada (Autenticación y Estructura Base). <br>
            Los módulos operativos se encuentran en desarrollo.
        </div>

        <h3 style="color:#555; margin-top:30px;">Resumen General (Mockup)</h3>
        
        <div class="grid-cards">
            <div class="card">
                <h3>Total Productos</h3>
                <div class="number">---</div>
                <small>Conectando a base de datos...</small>
            </div>
            <div class="card">
                <h3>Alertas de Stock</h3>
                <div class="number">---</div>
                <small>Sin datos registrados</small>
            </div>
            <div class="card">
                <h3>Ventas del Día</h3>
                <div class="number">$0.00</div>
                <small>Caja cerrada</small>
            </div>
        </div>

    </div>

</body>
</html>