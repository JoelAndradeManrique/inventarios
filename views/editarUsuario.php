<?php
require_once '../config/db.php';
require_once '../components/header.php';

// Obtenemos datos frescos del usuario logueado
$idUser = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nombre, email FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$idUser]);
$usuario = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <style>
        body { background: #f7f7f7; margin: 0; font-family: 'Poppins', sans-serif; }
        
        .edit-container { 
            width: 90%; max-width: 450px; margin: 50px auto; 
            background: white; padding: 30px; border-radius: 10px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.08); 
            border-top: 6px solid #f5d04c; 
        }
        
        .edit-container h2 { text-align: center; margin-bottom: 25px; color: #444; }
        
        label { font-weight: 600; margin-top: 15px; display: block; color: #555; font-size: 14px; }
        
        input { 
            width: 100%; padding: 12px; margin-top: 5px; 
            border-radius: 6px; border: 1px solid #ccc; 
            outline: none; box-sizing: border-box; 
        }
        
        input:focus { border-color: #f5d04c; box-shadow: 0 0 5px rgba(245, 208, 76, 0.35); }
        
        /* Icono del ojo */
        .input-pass { position: relative; }
        .input-pass input { padding-right: 40px; }
        .eye-icon { 
            position: absolute; right: 12px; top: 50%; 
            transform: translateY(-40%); cursor: pointer; 
            color: #777; font-size: 16px; 
        }
        .eye-icon:hover { color: #333; }

        /* Botón Guardar */
        .btn-save { 
            width: 100%; background: #f5d04c; border: none; 
            padding: 12px; border-radius: 6px; font-size: 16px; 
            cursor: pointer; margin-top: 30px; font-weight: 600; 
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: all 0.3s;
        }
        .btn-save:hover { background: #e2be43; transform: translateY(-2px); }
        
        /* Input readonly (Email) */
        input[readonly] { background-color: #f0f0f0; color: #777; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="edit-container">
    <h2><i class="fa-solid fa-user-pen"></i> Mi Perfil</h2>

    <form id="editUserForm">
        <input type="hidden" id="userId" value="<?php echo $idUser; ?>">
        <input type="hidden" id="userRol" value="<?php echo $_SESSION['user_role']; ?>">
        
        <label>Correo electrónico</label>
        <input type="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" readonly>

        <label>Nombre completo</label>
        <input type="text" id="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>

        <hr style="margin: 25px 0; border: 0; border-top: 1px solid #eee;">
        <p style="font-size:13px; color:#888; margin-bottom:15px; text-align:center;">
            <i class="fa-solid fa-circle-info"></i> Deja las contraseñas vacías si no deseas cambiarlas.
        </p>

        <label>Nueva Contraseña</label>
        <div class="input-pass">
            <input type="password" id="newPassword" placeholder="Mínimo 8 caracteres + 1 número">
            <span class="eye-icon" onclick="togglePassword('newPassword', this)">
                <i class="fa-solid fa-eye"></i>
            </span>
        </div>

        <label>Confirmar Contraseña</label>
        <div class="input-pass">
            <input type="password" id="confirmPassword" placeholder="Repite la contraseña">
            <span class="eye-icon" onclick="togglePassword('confirmPassword', this)">
                <i class="fa-solid fa-eye"></i>
            </span>
        </div>

        <button type="submit" class="btn-save">
            <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
        </button>
    </form>
</div>

<script>
    // Toggle Passwords (Versión FontAwesome)
    function togglePassword(inputId, span) {
        const input = document.getElementById(inputId);
        const icon = span.querySelector('i');
        
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = "password";
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // ENVÍO DEL FORMULARIO
    document.getElementById("editUserForm").addEventListener("submit", async (e) => {
        e.preventDefault();

        const id = document.getElementById("userId").value;
        const nombre = document.getElementById("nombre").value;
        const email = document.getElementById("email").value;
        const rol = document.getElementById("userRol").value;
        const pass = document.getElementById("newPassword").value;
        const pass2 = document.getElementById("confirmPassword").value;

        // Validaciones
        if (pass || pass2) {
            if (pass !== pass2) { alert("Las contraseñas no coinciden"); return; }
            
            // Validación Fuerte (Igual al backend)
            const seguridadPass = /^(?=.*[0-9]).{8,}$/; 
            if (!seguridadPass.test(pass)) {
                alert("Contraseña débil: Debe tener mínimo 8 caracteres y un número.");
                return; 
            }
        }

        const datos = {
            id_usuario: id,
            nombre: nombre,
            email: email,
            rol: rol,
            password: pass // Si va vacío, el backend lo ignora
        };

        // Feedback visual en botón
        const btn = document.querySelector('.btn-save');
        const textoOriginal = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

        try {
            const res = await fetch('../api/usuarios.php', {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(datos)
            });
            const result = await res.json();

            if (result.success) {
                alert("¡Perfil actualizado correctamente!");
                location.reload(); // Recargar para actualizar nombre en header
            } else {
                alert("Error: " + result.message);
                btn.disabled = false;
                btn.innerHTML = textoOriginal;
            }
        } catch (error) {
            alert("Error de conexión");
            btn.disabled = false;
            btn.innerHTML = textoOriginal;
        }
    });
</script>

</body>
</html>