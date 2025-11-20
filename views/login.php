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

            <a href="#" class="forgot">¿Olvidaste tu contraseña?</a>
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

        // Toast
        function showToast(message, success = false) {
            toast.textContent = message;
            toast.className = "toast show " + (success ? "success" : "error");

            setTimeout(() => {
                toast.className = "toast";
            }, 2500);
        }

        // Login demo
        form.addEventListener("submit", (e) => {
            e.preventDefault();

            const email = document.getElementById("email").value;
            const pass = passwordInput.value;

            if (email === "test@mail.com" && pass === "1234") {
                showToast("Bienvenido", true);
            } else {
                showToast("Credenciales incorrectas", false);
            }
        });
    </script>

</body>
</html>
