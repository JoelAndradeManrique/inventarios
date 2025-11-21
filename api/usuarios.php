<?php
require_once '../config/db.php';

// 1. SEGURIDAD: Iniciar sesión y verificar si es ADMIN
session_start();
header('Content-Type: application/json');

// Si no hay sesión o el rol no es ADMIN, bloqueamos el acceso [cite: 3, 8]
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'ADMIN') {
    http_response_code(403); // Prohibido
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo administradores.']);
    exit;
}

// Leemos el método de la petición (GET, POST, PUT, DELETE)
$method = $_SERVER['REQUEST_METHOD'];

// Leemos los datos JSON que vienen del frontend (para POST, PUT, DELETE)
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        
        // === LEER USUARIOS (READ) ===
        case 'GET':
            // Seleccionamos todo MENOS la contraseña por seguridad
            $stmt = $pdo->query("SELECT id_usuario, nombre, email, rol, creado_en FROM usuarios ORDER BY id_usuario DESC");
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($usuarios);
            break;

        // === CREAR USUARIO (CREATE) ===
        case 'POST':
            // Validar datos mínimos 
            if (empty($input['nombre']) || empty($input['email']) || empty($input['password']) || empty($input['rol'])) {
                echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
                exit;
            }

            // Validar contraseña mínima 8 caracteres 
            if (strlen($input['password']) < 8 || !preg_match('/[0-9]/', $input['password'])) {
                echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres y un número.']);
                exit;
            }

            // Validar que el email no exista ya
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$input['email']]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El correo ya está registrado.']);
                exit;
            }

            // Hashear password e insertar
            $hash = password_hash($input['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$input['nombre'], $input['email'], $hash, $input['rol']])) {
                echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar en BD.']);
            }
            break;

        // === ACTUALIZAR USUARIO (UPDATE) ===
        case 'PUT':
            if (empty($input['id_usuario']) || empty($input['nombre']) || empty($input['email']) || empty($input['rol'])) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos para actualizar.']);
                exit;
            }

            // ¿El usuario quiere cambiar la contraseña?
            // Si el campo 'password' viene lleno, la actualizamos. Si viene vacío, la dejamos igual.
            $passwordQuery = "";
            $params = [$input['nombre'], $input['email'], $input['rol']];

            if (!empty($input['password'])) {
                if (strlen($input['password']) < 8) {
                    echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres.']);
                    exit;
                }
                $passwordQuery = ", password = ?";
                $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
            }

            // Agregamos el ID al final de los parámetros para el WHERE
            $params[] = $input['id_usuario'];

            $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? $passwordQuery WHERE id_usuario = ?";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute($params)) {
                echo json_encode(['success' => true, 'message' => 'Usuario actualizado.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar.']);
            }
            break;

        // === ELIMINAR USUARIO (DELETE) ===
        case 'DELETE':
            if (empty($input['id_usuario'])) {
                echo json_encode(['success' => false, 'message' => 'Falta el ID del usuario.']);
                exit;
            }

            // SEGURIDAD CRÍTICA: Evitar que el admin se borre a sí mismo
            if ($input['id_usuario'] == $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta mientras la usas.']);
                exit;
            }

            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
            if ($stmt->execute([$input['id_usuario']])) {
                echo json_encode(['success' => true, 'message' => 'Usuario eliminado.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar.']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            break;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>