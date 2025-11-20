<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Inventarios</title>

    <style>
        body {
            background: #f7f7f7;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* CONTENEDOR */
        .container {
            width: 95%;
            margin: 25px auto;
        }

        h2 {
            color: #444;
        }

        p {
            color: #555;
            font-size: 16px;
        }

        /* Caja bienvenida */
        .welcome-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.07);
        }
    </style>
</head>
<body>

<!-- == HEADER CARGADO AUTOMÁTICO == -->
<div id="headerContainer"></div>

<script>
    // Cargar header.php
    fetch("../components/header.php")
        .then(response => response.text())
        .then(html => {
            document.getElementById("headerContainer").innerHTML = html;

            // Activar dropdown
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

            // Marca la sección activa (Inicio / Panel)
            const links = document.querySelectorAll(".nav a");
            links.forEach(a => {
                if (a.textContent.trim() === "Movimientos") {
                    a.classList.remove("active");
                }
                if (a.textContent.trim() === "Inicio" || a.textContent.trim() === "Inventarios") {
                    a.classList.add("active");
                }
            });
        });
</script>

<!-- == CONTENIDO PRINCIPAL == -->
<div class="container">
    <div class="welcome-box">
        <h2>Bienvenido al Panel de Inventarios</h2>
        <p>
            Selecciona un módulo del menú superior para comenzar a trabajar.
            Puedes gestionar movimientos, inventarios y generar reportes de ventas.
        </p>
    </div>
</div>

</body>
</html>
