<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>

    <style>
        body {
            background: #f7f7f7;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .edit-container {
            width: 90%;
            max-width: 450px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            border-top: 6px solid #f5d04c; /* Amarillo suave */
        }

        .edit-container h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #444;
        }

        label {
            font-weight: bold;
            margin-top: 10px;
            display: block;
            color: #555;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
            outline: none;
            transition: 0.2s;
        }

        input:focus {
            border-color: #f5d04c;
            box-shadow: 0 0 5px rgba(245, 208, 76, 0.35);
        }

        .input-pass {
            position: relative;
        }

        .eye {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            user-select: none;
            font-size: 18px;
            opacity: 0.6;
        }

        .eye:hover {
            opacity: 1;
        }

        .btn-save {
            width: 100%;
            background: #f5d04c;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 25px;
            transition: 0.2s;
            font-weight: bold;
        }

        .btn-save:hover {
            background: #e2be43;
        }
    </style>
</head>

<body>

<div class="edit-container">
    <h2>Editar Usuario</h2>

    <form id="editUserForm">

        <label>Nombre completo</label>
        <input type="text" placeholder="Ingresa tu nombre" required>

        <label>Contraseña nueva</label>
        <div class="input-pass">
            <input type="password" id="newPassword" placeholder="Ingresa una contraseña nueva" required>
            <span id="togglePassword" class="eye">👁️</span>
        </div>

        <label>Confirmar contraseña</label>
        <div class="input-pass">
            <input type="password" id="confirmPassword" placeholder="Confirma la contraseña" required>
            <span id="togglePassword2" class="eye">👁️</span>
        </div>

        <button type="submit" class="btn-save">Guardar cambios</button>
    </form>
</div>

<script>
    // Mostrar / ocultar contraseña 1
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("newPassword");

    togglePassword.addEventListener("click", () => {
        const isText = passwordInput.type === "text";
        passwordInput.type = isText ? "password" : "text";
    });

    // Mostrar / ocultar contraseña 2
    const togglePassword2 = document.getElementById("togglePassword2");
    const confirmInput = document.getElementById("confirmPassword");

    togglePassword2.addEventListener("click", () => {
        const isText = confirmInput.type === "text";
        confirmInput.type = isText ? "password" : "text";
    });
</script>

</body>
</html>
