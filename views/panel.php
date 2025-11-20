<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Inventarios</title>
    <link rel="stylesheet" href="../css/panel.css">
</head>
<body>

    <header class="header">
        <div class="left-section">
            <a href="#" class="logo">Inventarios</a>

            <nav class="nav">
                <a href="#" class="active">Movimientos</a>
                <a href="#">Gestión de inventarios</a>
                <a href="#">Reportes</a>
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


    <main class="content">
        <h2>Bienvenido al Panel de Inventarios</h2>
        <p>Aquí puedes gestionar tus módulos.</p>
    </main>


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
    </script>

</body>
</html>
