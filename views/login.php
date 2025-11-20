<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

    <div class="login-container">
        <h2>Iniciar sesión</h2>

        <form id="loginForm">
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" required>

            <label for="password">Contraseña</label>

            <div class="password-wrapper">
                <input type="password" id="password" required>
                <span id="togglePass" class="eye">👁️</span>
            </div>

            <button type="submit">Entrar</button>

            <a href="recuperar_password.php" class="forgot">¿Olvidaste tu contraseña?</a>
        </form>
    </div>

    <div id="toast" class="toast"></div>

    <script>
        const form = document.getElementById("loginForm");
        const toast = document.getElementById("toast");
        const togglePass = document.getElementById("togglePass");
        const passwordInput = document.getElementById("password");

        // Mostrar/Ocultar contraseña
        togglePass.addEventListener("click", () => {
            const isPassword = passwordInput.type === "password";
            passwordInput.type = isPassword ? "text" : "password";
            togglePass.textContent = isPassword ? "🙈" : "👁️";
        });

        // Toast (Tu función original)
        function showToast(message, success = false) {
            toast.textContent = message;
            toast.className = "toast show " + (success ? "success" : "error");

            setTimeout(() => {
                toast.className = "toast";
            }, 2500);
        }

        // --- LÓGICA DE LOGIN REAL ---
        form.addEventListener("submit", async (e) => {
            e.preventDefault(); // Evita que se recargue la página

            const email = document.getElementById("email").value;
            const pass = passwordInput.value;
            const submitBtn = form.querySelector('button');

            // Deshabilitar botón para evitar doble clic
            submitBtn.disabled = true;
            submitBtn.textContent = "Verificando...";

            try {
                // 1. Petición a tu API
                const response = await fetch('../api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email: email, password: pass })
                });

                // 2. Convertir respuesta a JSON
                const data = await response.json();

                if (data.success) {
                    // CASO ÉXITO:
                    showToast("¡Bienvenido! Redirigiendo...", true);
                    
                    // Esperamos 1 segundo para que vea el mensaje y redirigimos
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);

                } else {
                    // CASO ERROR (Contraseña mal, usuario no existe, etc):
                    showToast(data.message, false);
                    submitBtn.disabled = false;
                    submitBtn.textContent = "Entrar";
                }

            } catch (error) {
                console.error(error);
                showToast("Error de conexión con el servidor", false);
                submitBtn.disabled = false;
                submitBtn.textContent = "Entrar";
            }
        });
    </script>

</body>
</html>
