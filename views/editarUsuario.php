<?php
require_once '../config/db.php';
require_once '../components/header.php';

// Obtenemos datos frescos del usuario logueado para llenar el input
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
        /* Tus estilos originales (respetados) */
        body { background: #f7f7f7; margin: 0; font-family: Arial, sans-serif; }
        .edit-container { width: 90%; max-width: 450px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); border-top: 6px solid #f5d04c; }
        .edit-container h2 { text-align: center; margin-bottom: 25px; color: #444; }
        label { font-weight: bold; margin-top: 10px; display: block; color: #555; }
        input { width: 100%; padding: 12px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; outline: none; box-sizing: border-box; } /* box-sizing importante */
        input:focus { border-color: #f5d04c; box-shadow: 0 0 5px rgba(245, 208, 76, 0.35); }
        .input-pass { position: relative; }
        .eye { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; font-size: 18px; opacity: 0.6; }
        .btn-save { width: 100%; background: #f5d04c; border: none; padding: 12px; border-radius: 6px; font-size: 16px; cursor: pointer; margin-top: 25px; font-weight: bold; }
        .btn-save:hover { background: #e2be43; }
    </style>
</head>
<body>

<div class="edit-container">
    <h2>Mi Perfil</h2>

    <form id="editUserForm">
        <input type="hidden" id="userId" value="<?php echo $idUser; ?>">
        <input type="hidden" id="userRol" value="<?php echo $_SESSION['user_role']; ?>">
        
        <label>Correo electrónico</label>
        <input type="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" readonly style="background:#eee; color:#777;">

        <label>Nombre completo</label>
        <input type="text" id="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>

        <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">
        <p style="font-size:12px; color:#888; margin-bottom:15px;">Deja las contraseñas en blanco si no deseas cambiarlas.</p>

        <label>Nueva Contraseña</label>
        <div class="input-pass">
            <input type="password" id="newPassword" placeholder="Mínimo 8 caracteres">
            <span id="togglePassword" class="eye">👁️</span>
        </div>

        <label>Confirmar Contraseña</label>
        <div class="input-pass">
            <input type="password" id="confirmPassword" placeholder="Repite la contraseña">
            <span id="togglePassword2" class="eye">👁️</span>
        </div>

        <button type="submit" class="btn-save">Guardar cambios</button>
    </form>
</div>

<script>
    // Toggle Passwords
    document.getElementById("togglePassword").onclick = () => {
        const i = document.getElementById("newPassword");
        i.type = i.type === "password" ? "text" : "password";
    };
    document.getElementById("togglePassword2").onclick = () => {
        const i = document.getElementById("confirmPassword");
        i.type = i.type === "password" ? "text" : "password";
    };

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
            if (pass.length < 8) { alert("La contraseña debe tener mínimo 8 caracteres"); return; }
        }

        const datos = {
            id_usuario: id,
            nombre: nombre,
            email: email,
            rol: rol,
            password: pass // Si va vacío, el backend lo ignora
        };

        try {
            const res = await fetch('../api/usuarios.php', {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(datos)
            });
            const result = await res.json();

            if (result.success) {
                alert("¡Perfil actualizado correctamente!");
                location.reload(); // Recargar para ver el nombre nuevo en el Header
            } else {
                alert("Error: " + result.message);
            }
        } catch (error) {
            alert("Error de conexión");
        }
    });
</script>

</body>
</html>