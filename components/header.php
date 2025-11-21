<?php
// == 1. SEGURIDAD Y SESIÓN (ESTO DEBE IR PRIMERO) ==
// Si no hay sesión iniciada, la iniciamos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificamos si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    // Si no hay usuario, REDIRIGIMOS al login y MATAMOS el script
    header("Location: ../views/login.php");
    exit; // ¡Importante! Esto detiene la carga del resto de la página
}

// == 2. VARIABLES GLOBALES ==
// Estas variables estarán disponibles en cualquier archivo que incluya este header
$rol = $_SESSION['user_role'] ?? 'EMPLEADO'; // Si falla, por defecto empleado
$nombre = $_SESSION['user_name'] ?? 'Usuario';

// Lógica de iniciales
$iniciales = "";
$parts = explode(" ", $nombre);
foreach ($parts as $p) {
    if(!empty($p)) $iniciales .= strtoupper($p[0]);
    if(strlen($iniciales) >= 2) break;
}
?>
<header class="header">
    <link rel="stylesheet" href="../css/header.css">

    <div class="left-section">
        <a href="../views/panel.php" class="logo">Inventarios</a>
        <nav class="nav">
            <?php if ($rol === 'ADMIN'): ?>
                <a href="../views/usuarios.php">Usuarios</a>
            <?php endif; ?>
            
            <a href="../views/movimientos.php">Movimientos</a>
            <a href="../views/inventarios.php">Gestión de inventarios</a>

            <?php if ($rol === 'ADMIN'): ?>
                <a href="../views/reportes.php">Reportes</a>
            <?php endif; ?>
        </nav>
    </div>

    <div class="right-section">
        <div class="user-menu">
            <div id="userInitials" class="user-initials"><?php echo $iniciales; ?></div>

            <div id="dropdown" class="dropdown">
                <div class="user-info-text">Hola, <strong><?php echo htmlspecialchars($nombre); ?></strong></div>
                <hr>
                <a href="../views/editarUsuario.php">Editar usuario</a>
                <a href="../api/logout.php" style="color: #d9534f;">Cerrar sesión</a>
            </div>
        </div>
    </div>

    <script>
        // Script del dropdown (para que no dependa de fetch externo)
        const userInitials = document.getElementById('userInitials');
        const dropdown = document.getElementById('dropdown');

        if(userInitials && dropdown){
            userInitials.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('show');
            });
            window.addEventListener('click', () => {
                dropdown.classList.remove('show');
            });
        }
    </script>

    <style>
        /* Estilos mínimos para que el dropdown funcione siempre */
        .dropdown { display: none; position: absolute; right: 10px; top: 50px; background: white; border: 1px solid #ccc; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.2); flex-direction: column; width: 150px; z-index: 1000; }
        .dropdown.show { display: flex; }
        .dropdown a { padding: 10px; text-decoration: none; color: #333; display: block; font-size: 14px; }
        .dropdown a:hover { background-color: #f0f0f0; }
        .user-info-text { padding: 10px; font-size: 12px; color: #666; text-align: center; background: #f9f9f9;}
        .header { position: relative; }
    </style>
</header>