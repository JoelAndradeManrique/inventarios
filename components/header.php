<?php
// == 1. SEGURIDAD Y SESIÓN ==
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit;
}

// == 2. VARIABLES GLOBALES ==
$rol = $_SESSION['user_role'] ?? 'EMPLEADO';
$nombre = $_SESSION['user_name'] ?? 'Usuario';

// Lógica para obtener iniciales (Ej: "Joel Andrade" -> "JA")
$iniciales = "";
$parts = explode(" ", $nombre);
foreach ($parts as $p) {
    if(!empty($p)) $iniciales .= strtoupper($p[0]);
    if(strlen($iniciales) >= 2) break;
}
?>

<header class="header">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="../css/header.css">

    <style>
        /* Aplicar fuente a todo el sistema */
        body { font-family: 'Poppins', sans-serif !important; }
        
        /* Iconos en el menú */
        .nav a i { margin-right: 8px; }
        
        /* Estilos del Dropdown para asegurar que funcione */
        .dropdown { display: none; position: absolute; right: 10px; top: 60px; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); flex-direction: column; width: 180px; z-index: 1000; overflow: hidden; }
        .dropdown.show { display: flex; }
        .dropdown a { padding: 12px 15px; text-decoration: none; color: #333; display: block; font-size: 14px; transition: background 0.2s; }
        .dropdown a:hover { background-color: #f5f5f5; }
        .user-info-text { padding: 15px; font-size: 13px; color: #666; text-align: center; background: #f9f9f9; border-bottom: 1px solid #eee; }
        .header { position: relative; z-index: 999; }
        
        /* Ajuste para el logo */
        .logo i { margin-right: 10px; color: #f5d04c; }
    </style>

    <div class="left-section">
        <a href="../views/panel.php" class="logo">
            <i class="fa-solid fa-boxes-stacked"></i> Inventarios
        </a>

        <nav class="nav">
            <?php if ($rol === 'ADMIN'): ?>
                <a href="../views/usuarios.php">
                    <i class="fa-solid fa-users"></i> Usuarios
                </a>
            <?php endif; ?>
            
            <a href="../views/movimientos.php">
                <i class="fa-solid fa-cash-register"></i> Movimientos
            </a>
            <a href="../views/inventarios.php">
                <i class="fa-solid fa-clipboard-list"></i> Inventarios
            </a>

            <?php if ($rol === 'ADMIN'): ?>
                <a href="../views/reportes.php">
                    <i class="fa-solid fa-chart-line"></i> Reportes
                </a>
            <?php endif; ?>
        </nav>
    </div>

    <div class="right-section">
        <div class="user-menu">
            <div id="userInitials" class="user-initials" title="Clic para menú">
                <?php echo $iniciales; ?>
            </div>

            <div id="dropdown" class="dropdown">
                <div class="user-info-text">
                    Hola, <strong><?php echo htmlspecialchars($nombre); ?></strong><br>
                    <small style="color:#888; text-transform:uppercase; font-size:10px;"><?php echo $rol; ?></small>
                </div>
                
                <a href="../views/editarUsuario.php">
                    <i class="fa-solid fa-user-pen"></i> Mi Perfil
                </a>
                
                <a href="../api/logout.php" style="color: #e74c3c;">
                    <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userInitials = document.getElementById('userInitials');
            const dropdown = document.getElementById('dropdown');

            if(userInitials && dropdown){
                // Abrir/Cerrar al dar clic en las iniciales
                userInitials.addEventListener('click', (e) => {
                    e.stopPropagation(); // Evita que el clic llegue al window
                    dropdown.classList.toggle('show');
                });

                // Cerrar si haces clic en cualquier otra parte de la pantalla
                window.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target) && !userInitials.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>
</header>