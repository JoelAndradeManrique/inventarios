<?php
// Capturamos el token de la URL. Si no viene, queda vacío.
$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña</title>

    <style>
        body { margin: 0; background: #f5f6fa; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-container { width: 350px; background: #ffffff; padding: 25px; border-radius: 10px; box-shadow: 0px 4px 12px rgba(0,0,0,0.1); text-align: center; }
        h2 { margin-bottom: 25px; color: #2c3e50; }
        .input-group { position: relative; width: 100%; margin-bottom: 15px; }
        input { width: 100%; padding: 12px 40px 12px 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; box-sizing: border-box; }
        .eye-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 18px; color: #7f8c8d; }
        .btn { width: 100%; padding: 12px; margin-top: 10px; border: none; background: #3498db; color: white; border-radius: 6px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #2980b9; }
        .btn:disabled { background: #95a5a6; cursor: not-allowed; }
        a { display: block; margin-top: 15px; text-decoration: none; color: #3498db; font-size: 14px; }
        a:hover { text-decoration: underline; }
        
        /* Agregamos estilos para mensajes de feedback */
        .message { margin-top: 15px; font-size: 14px; font-weight: bold; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>

<body>

<div class="login-container">
    <h2>Restablecer contraseña</h2>

    <?php if(empty($token)): ?>
        <div class="message error">
            Error: No se proporcionó un token válido.<br>
            <a href="solicitar_recuperacion.php">Solicitar nuevo enlace</a>
        </div>
    <?php else: ?>

        <form id="resetForm">
            <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="input-group">
                <input id="pass1" type="password" placeholder="Nueva contraseña" required minlength="8">
                <span class="eye-icon" onclick="togglePassword('pass1', this)">👁️</span>
            </div>

            <div class="input-group">
                <input id="pass2" type="password" placeholder="Confirmar contraseña" required>
                <span class="eye-icon" onclick="togglePassword('pass2', this)">👁️</span>
            </div>

            <button class="btn" type="submit">Actualizar contraseña</button>
        </form>
        
        <div id="mensaje" class="message"></div>

    <?php endif; ?>

    <a href="../views/login.php">Volver al inicio de sesión</a>
</div>

<script>
    function togglePassword(id, icon) {
        const field = document.getElementById(id);
        if (field.type === "password") {
            field.type = "text";
            icon.textContent = "🙈";
        } else {
            field.type = "password";
            icon.textContent = "👁️";
        }
    }

    // Lógica de envío si el formulario existe
    const form = document.getElementById('resetForm');
    if(form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const token = document.getElementById('token').value;
            const pass1 = document.getElementById('pass1').value;
            const pass2 = document.getElementById('pass2').value;
            const mensaje = document.getElementById('mensaje');
            const btn = form.querySelector('button');

            mensaje.textContent = "";
            mensaje.className = "message";

            // 1. Validar coincidencia
            if (pass1 !== pass2) {
                mensaje.textContent = "Las contraseñas no coinciden.";
                mensaje.className = "message error";
                return;
            }

            btn.disabled = true;
            btn.textContent = "Actualizando...";

            try {
                // 2. Enviar a la API
                const response = await fetch('../api/cambiar_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ token: token, password: pass1 })
                });

                const data = await response.json();

                if (data.success) {
                    mensaje.innerHTML = "¡Contraseña actualizada correctamente!<br>Redirigiendo al login...";
                    mensaje.className = "message success";
                    form.reset();
                    
                    setTimeout(() => {
                        window.location.href = "../views/login.php";
                    }, 2000);
                } else {
                    mensaje.textContent = data.message; // Ej: Token expirado
                    mensaje.className = "message error";
                    btn.disabled = false;
                    btn.textContent = "Actualizar contraseña";
                }

            } catch (error) {
                console.error(error);
                mensaje.textContent = "Error al conectar con el servidor.";
                mensaje.className = "message error";
                btn.disabled = false;
                btn.textContent = "Actualizar contraseña";
            }
        });
    }
</script>

</body>
</html>