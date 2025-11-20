<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validar Correo</title>

    <style>
        body { margin: 0; background: #1e1e2f; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .login-box { background: #ffffff; width: 350px; padding: 35px; border-radius: 14px; box-shadow: 0 5px 20px rgba(0,0,0,0.25); text-align: center; }
        h2 { margin-bottom: 20px; color: #333; }
        .input-group { margin-bottom: 25px; display: flex; flex-direction: column; align-items: center; }
        label { font-weight: bold; color: #444; width: 100%; text-align: left; }
        input { width: 80%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; font-size: 15px; text-align: center; }
        .btn { width: 100%; background: #f5d04c; border: none; padding: 12px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .btn:hover { background: #e2be43; }
        .btn:disabled { background: #ccc; cursor: not-allowed; } /* Estilo deshabilitado */
        .message { margin-top: 15px; font-size: 14px; font-weight: bold; }
        .success { color: green; }
        .error { color: red; }
        .back-link { margin-top: 15px; display: block; color: #555; text-decoration: none; font-size: 14px; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>

<body>

<div class="login-box">
    <h2>Validar Correo</h2>

    <div class="input-group">
        <label for="correo">Correo electrónico</label>
        <input type="email" id="correo" placeholder="Ingresa tu correo">
    </div>

    <button id="btnValidar" class="btn">Validar</button>

    <div id="mensaje" class="message"></div>

    <a href="../views/login.php" class="back-link">Volver al inicio</a>
</div>

<script>
    // Usamos addEventListener en lugar de onclick en el HTML para mejor práctica
    document.getElementById('btnValidar').addEventListener('click', validarCorreo);

    async function validarCorreo() {
        const correoInput = document.getElementById("correo");
        const mensaje = document.getElementById("mensaje");
        const btn = document.getElementById("btnValidar");
        const correo = correoInput.value.trim();

        // Limpiar mensajes previos
        mensaje.textContent = "";
        mensaje.className = "message";

        if (correo === "") {
            mensaje.textContent = "Ingresa un correo.";
            mensaje.className = "message error";
            return;
        }

        // Deshabilitar botón para evitar múltiples clics
        btn.disabled = true;
        btn.textContent = "Verificando...";

        try {
            // Petición real al Backend
            const response = await fetch('../api/enviar_token.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: correo })
            });

            const data = await response.json();

            if (data.success) {
                // ÉXITO
                mensaje.innerHTML = "✔ Enlace enviado. <br>Revisa tu correo (o el log).";
                mensaje.className = "message success";
                correoInput.value = ""; // Limpiar campo
            } else {
                // ERROR (Usuario no existe o error de servidor)
                mensaje.textContent = "✘ " + data.message;
                mensaje.className = "message error";
            }

        } catch (error) {
            console.error(error);
            mensaje.textContent = "Error de conexión.";
            mensaje.className = "message error";
        } finally {
            // Reactivar botón
            btn.disabled = false;
            btn.textContent = "Validar";
        }
    }
</script>

</body>
</html>