<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes</title>

    <style>
        /* -------------------- ESTILOS GENERALES -------------------- */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f5f6fa;
        }

        /* -------------------- HEADER -------------------- */
        .header {
            width: 100%;
            background: #1d1f2f;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .left-section {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: #f7d44c;
            text-decoration: none;
        }

        .nav a {
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .nav a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .nav .active {
            background: rgba(255, 255, 255, 0.25);
        }

        .right-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-initials {
            background: #f7d44c;
            color: #1d1f2f;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            cursor: pointer;
        }

        .dropdown {
            display: none;
            position: absolute;
            background: white;
            right: 30px;
            margin-top: 50px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .dropdown a {
            display: block;
            padding: 10px 20px;
            color: #333;
            text-decoration: none;
        }

        .dropdown a:hover {
            background: #f7d44c33;
        }

        .dropdown.show {
            display: block;
        }

        /* -------------------- CONTENIDO -------------------- */
        .content {
            padding: 40px;
        }

        .section-title {
            font-size: 28px;
            margin-bottom: 25px;
        }

        /* Buscador */
        .filters {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
        }

        .filters input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .filters button {
            background: #f7d44c;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.2s;
        }

        .filters button:hover {
            background: #e6c43f;
        }

        /* Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        table thead {
            background: #1d1f2f;
            color: white;
        }

        table th,
        table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e5e5e5;
        }

        table tr:hover {
            background: #f7f7f7;
        }

        /* -------------------- POPUP -------------------- */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }

        .popup {
            background: white;
            padding: 25px;
            border-radius: 12px;
            width: 340px;
        }

        .popup h3 {
            margin-top: 0;
        }

        .popup input {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .popup button {
            width: 100%;
            background: #f7d44c;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .popup button:hover {
            background: #e1c03c;
        }

    </style>

</head>
<body>

    <!-- -------------------- HEADER -------------------- -->
    <header class="header">
        <div class="left-section">
            <a href="#" class="logo">Inventarios</a>

            <nav class="nav">
                <a href="movimientos.html">Movimientos</a>
                <a href="gestion.html">Gestión de inventarios</a>
                <a href="reportes.html" class="active">Reportes</a>
            </nav>
        </div>

        <div class="right-section">
            <div class="user-menu">
                <div id="userInitials" class="user-initials">JA</div>

                <div id="dropdown" class="dropdown">
                    <a href="#">Editar usuario</a>
                    <a href="#">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>

    <!-- -------------------- CONTENIDO -------------------- -->
    <main class="content">
        <h2 class="section-title">Reportes de cortes</h2>

        <!-- Filtros -->
        <div class="filters">
            <input type="date">
            <input type="date">
            <button>Buscar</button>

            <button id="openPopup" style="margin-left:auto;">Descargar reporte</button>
        </div>

        <!-- Tabla -->
        <table>
            <thead>
                <tr>
                    <th>Fecha del corte</th>
                    <th>Monto total</th>
                    <th>Realizado por</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2025-01-10</td>
                    <td>$4,350</td>
                    <td>Joel A.</td>
                </tr>
                <tr>
                    <td>2025-01-09</td>
                    <td>$2,980</td>
                    <td>Lucas M.</td>
                </tr>
            </tbody>
        </table>
    </main>

    <!-- -------------------- POPUP -------------------- -->
    <div class="overlay" id="overlay">
        <div class="popup">
            <h3>Descargar reporte</h3>

            <label>Desde:</label>
            <input type="date">

            <label>Hasta:</label>
            <input type="date">

            <button>Descargar</button>
        </div>
    </div>

    <!-- -------------------- SCRIPT -------------------- -->
    <script>
        const userInitials = document.getElementById("userInitials");
        const dropdown = document.getElementById("dropdown");

        userInitials.addEventListener("click", () => {
            dropdown.classList.toggle("show");
        });

        window.addEventListener("click", (e) => {
            if (!userInitials.contains(e.target)) {
                dropdown.classList.remove("show");
            }
        });

        /* --- Popup Descargar --- */
        const overlay = document.getElementById("overlay");
        const openPopup = document.getElementById("openPopup");

        openPopup.addEventListener("click", () => {
            overlay.style.display = "flex";
        });

        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) {
                overlay.style.display = "none";
            }
        });
    </script>

</body>
</html>
