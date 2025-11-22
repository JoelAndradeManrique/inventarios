<?php
require_once '../config/db.php';
require_once '../components/header.php'; 

if ($rol !== 'ADMIN') {
    echo "<script>window.location.href='panel.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f5f6fa; }
        .content { width: 90%; margin: 30px auto; max-width: 1200px; }
        h2 { font-size: 28px; color: #333; }
        
        .btn-main { background: #f5d04c; padding: 10px 18px; border-radius: 8px; border: none; cursor: pointer; font-weight: bold; margin-bottom: 20px; }
        .btn-main:hover { background: #e2be43; }

        table { width: 100%; border-collapse: collapse; background: white; border-radius: 12px; overflow: hidden; margin-top: 10px; box-shadow: 0px 3px 10px rgba(0,0,0,0.1); }
        th { background: #1e1e2f; color: white; padding: 14px; text-align: left; }
        td { padding: 14px; border-bottom: 1px solid #eee; }
        tr:hover { background: #f7f7f7; }

        .actions button { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; margin-right: 8px; color: white; font-weight: bold;}
        .edit-btn { background: #3498db; }
        .delete-btn { background: #e63946; }

        /* POPUP GENÉRICO */
        .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 1000; }
        .popup { background: white; padding: 25px; border-radius: 12px; width: 400px; position: relative;}
        .popup h3 { margin-top: 0; margin-bottom: 20px; color: #333;}
        .popup label { font-weight: bold; display: block; margin-top: 12px; font-size: 14px; color: #555;}
        .popup input, .popup select { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; box-sizing: border-box; }
        
        /* ESTILOS DEL OJITO */
        .password-wrapper { position: relative; width: 100%; }
        .password-wrapper input { padding-right: 40px; } 
        .eye-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-40%); cursor: pointer; font-size: 18px; color: #666; margin-top: 5px; }

        /* BOTONES DEL FORMULARIO */
        .popup button.save-btn { width: 100%; background: #f5d04c; padding: 12px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 20px; }
        
        .close-btn { position: absolute; top: 15px; right: 15px; cursor: pointer; font-size: 20px; font-weight: bold; color: #999;}

        /* ESTILOS ESPECÍFICOS PARA CONFIRMACIÓN */
        .confirm-buttons { display: flex; gap: 15px; margin-top: 25px; }
        .confirm-buttons button { flex: 1; padding: 12px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .btn-cancel { background: #e0e0e0; color: #333; }
        .btn-cancel:hover { background: #ccc; }
        .btn-confirm { background: #e63946; color: white; }
        .btn-confirm:hover { background: #c62828; }

        /* TOAST */
        .toast { visibility: hidden; min-width: 250px; background-color: #333; color: #fff; text-align: center; border-radius: 8px; padding: 16px; position: fixed; z-index: 1001; right: 30px; top: 30px; font-size: 15px; opacity: 0; transition: opacity 0.5s, top 0.5s; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .toast.show { visibility: visible; opacity: 1; top: 50px; }
        .toast.success { background-color: #4caf50; }
        .toast.error { background-color: #e63946; }
    </style>
</head>
<body>

    <div id="toast" class="toast">Mensaje</div>

    <div class="content">
        <h2>Usuarios del Sistema</h2>
        <button id="btnNuevo" class="btn-main">+ Nuevo Usuario</button>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tablaUsuarios"></tbody>
        </table>
    </div>

    <div id="overlay" class="overlay">
        <div class="popup">
            <span class="close-btn" onclick="cerrarModal()">&times;</span>
            <h3 id="modalTitle">Crear nuevo usuario</h3>

            <form id="userForm">
                <input type="hidden" id="userId">

                <label>Nombre completo</label>
                <input type="text" id="nombre" required placeholder="Ej: Juan Pérez">

                <label>Correo electrónico</label>
                <input type="email" id="correo" required placeholder="juan@empresa.com">

                <label>Rol</label>
                <select id="rolSelect" required>
                    <option value="EMPLEADO">Empleado</option>
                    <option value="ADMIN">Administrador</option>
                </select>

                <label>Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="pass" placeholder="Mínimo 8 caracteres">
                    <span class="eye-icon" onclick="togglePassword('pass', this)">
                        <i class="fa-solid fa-eye"></i>
                    </span>
                </div>
                <small style="color:#888; display:none;" id="passNote">Dejar vacío para mantener la actual</small>

                <label id="lblPass2">Confirmar contraseña</label>
                <div class="password-wrapper">
                    <input type="password" id="pass2">
                    <span class="eye-icon" onclick="togglePassword('pass', this)">
                        <i class="fa-solid fa-eye"></i>
                    </span>
                </div>

                <button type="submit" id="btnGuardar" class="save-btn">Guardar Usuario</button>
            </form>
        </div>
    </div>

    <div id="confirmOverlay" class="overlay">
        <div class="popup" style="text-align: center; width: 320px;">
            <h3 style="color: #e63946;">¿Eliminar Usuario?</h3>
            <p style="color: #666; font-size: 15px;">¿Estás seguro de que deseas eliminar a este usuario? Esta acción no se puede deshacer.</p>
            
            <div class="confirm-buttons">
                <button class="btn-cancel" onclick="cerrarConfirm()">Cancelar</button>
                <button id="btnSiEliminar" class="btn-confirm">Sí, eliminar</button>
            </div>
        </div>
    </div>

    <script>
        let usuarioAEliminar = null; // Variable para guardar el ID temporalmente

        // 1. TOAST
        function showToast(message, type = 'success') {
            const toast = document.getElementById("toast");
            toast.textContent = message;
            toast.className = "toast show " + type;
            setTimeout(() => { toast.className = toast.className.replace("show", ""); }, 3000);
        }

        // 2. TOGGLE PASSWORD
        function togglePassword(inputId, spanElement) {
            const input = document.getElementById(inputId);
            const icon = spanElement.querySelector('i'); // Buscamos el icono dentro del span

            if (input.type === "password") {
                input.type = "text";
                // Cambiamos la clase del icono a "ojo tachado"
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                // Regresamos al "ojo normal"
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // --- CARGA INICIAL ---
        document.addEventListener('DOMContentLoaded', loadUsers);

        async function loadUsers() {
            try {
                const res = await fetch('../api/usuarios.php');
                const users = await res.json();
                const tbody = document.getElementById('tablaUsuarios');
                tbody.innerHTML = ''; 

                users.forEach(user => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${user.nombre}</td>
                        <td>${user.email}</td>
                        <td>
                            <span style="padding:4px 8px; border-radius:4px; background:${user.rol === 'ADMIN' ? '#e8f5e9' : '#e3f2fd'}; color:${user.rol === 'ADMIN' ? '#2e7d32' : '#1565c0'}; font-size:12px; font-weight:bold;">
                                ${user.rol}
                            </span>
                        </td>
                        <td>${user.creado_en.substring(0,10)}</td>
                        <td class="actions">
                            <button class="edit-btn" onclick='editarUsuario(${JSON.stringify(user)})' title="Editar">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="delete-btn" onclick="preguntarEliminar(${user.id_usuario})" title="Eliminar">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (error) {
                console.error(error);
                showToast('Error al cargar usuarios', 'error');
            }
        }

        // --- MODALES ---
        const overlay = document.getElementById("overlay");
        const confirmOverlay = document.getElementById("confirmOverlay");
        const modalTitle = document.getElementById("modalTitle");
        const passNote = document.getElementById("passNote");
        
        document.getElementById("btnNuevo").onclick = () => {
            document.getElementById("userForm").reset();
            document.getElementById("userId").value = ""; 
            modalTitle.textContent = "Crear nuevo usuario";
            passNote.style.display = "none";
            overlay.style.display = "flex";
        };

        function cerrarModal() { overlay.style.display = "none"; }
        
        // Funciones para el Modal de Confirmación
        function preguntarEliminar(id) {
            usuarioAEliminar = id; // Guardamos el ID
            confirmOverlay.style.display = "flex"; // Mostramos el modal rojo
        }

        function cerrarConfirm() {
            confirmOverlay.style.display = "none";
            usuarioAEliminar = null;
        }

        // Al dar clic en "Sí, eliminar"
        document.getElementById("btnSiEliminar").addEventListener("click", async () => {
            if (usuarioAEliminar) {
                await ejecutarEliminacion(usuarioAEliminar);
                cerrarConfirm();
            }
        });

        // --- GUARDAR ---
        document.getElementById("userForm").addEventListener("submit", async (e) => {
            e.preventDefault();

            const id = document.getElementById("userId").value;
            const nombre = document.getElementById("nombre").value;
            const email = document.getElementById("correo").value;
            const rol = document.getElementById("rolSelect").value;
            const pass = document.getElementById("pass").value;
            const pass2 = document.getElementById("pass2").value;

            if (pass !== pass2) {
                showToast("Las contraseñas no coinciden", 'error');
                return;
            }

            if (pass.length > 0) { 
                const seguridadPass = /^(?=.*[0-9]).{8,}$/; 
                if (!seguridadPass.test(pass)) {
                    showToast("Contraseña débil: Mínimo 8 caracteres y un número.", 'error');
                    return; 
                }
            }

            if (!id && !pass) {
                showToast("Debes asignar una contraseña", 'error');
                return;
            }

            const datos = { nombre, email, rol, password: pass };
            let metodo = 'POST'; 
            if (id) {
                datos.id_usuario = id;
                metodo = 'PUT';
            }

            try {
                const res = await fetch('../api/usuarios.php', {
                    method: metodo,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(datos)
                });
                const result = await res.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    cerrarModal();
                    loadUsers();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast("Error de conexión", 'error');
            }
        });

        window.editarUsuario = (user) => {
            document.getElementById("userId").value = user.id_usuario;
            document.getElementById("nombre").value = user.nombre;
            document.getElementById("correo").value = user.email;
            document.getElementById("rolSelect").value = user.rol;
            document.getElementById("pass").value = "";
            document.getElementById("pass2").value = "";
            modalTitle.textContent = "Editar Usuario";
            passNote.style.display = "block";
            overlay.style.display = "flex";
        };

        // --- ELIMINACIÓN REAL ---
        async function ejecutarEliminacion(id) {
            try {
                const res = await fetch('../api/usuarios.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_usuario: id })
                });
                const result = await res.json();

                if (result.success) {
                    showToast(result.message, 'success');
                    loadUsers();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast("Error al eliminar", 'error');
            }
        };
    </script>
</body>
</html>