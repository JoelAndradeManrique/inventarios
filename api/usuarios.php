<?php
require_once '../config/db.php';

// 1. SEGURIDAD: Iniciar sesión
session_start();
header('Content-Type: application/json');

// 2. LEER DATOS (Una sola vez al inicio)
$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// 3. VALIDAR SESIÓN
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); 
    echo json_encode(['success'=>false, 'message'=>'Acceso denegado']); 
    exit;
}

// 4. VALIDAR PERMISOS (Admin o Auto-edición)
if ($_SESSION['user_role'] !== 'ADMIN') {
    // Si NO es admin, solo permitimos PUT (Editar) y solo si es su propio ID
    if ($method !== 'PUT' || (isset($input['id_usuario']) && $input['id_usuario'] != $_SESSION['user_id'])) {
        http_response_code(403); 
        echo json_encode(['success'=>false, 'message'=>'No tienes permiso para realizar esta acción.']); 
        exit;
    }
}

try {
    switch ($method) {
        
        // === LEER USUARIOS (READ) ===
        case 'GET':
            $stmt = $pdo->query("SELECT id_usuario, nombre, email, rol, creado_en FROM usuarios ORDER BY id_usuario DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        // === CREAR USUARIO (CREATE) ===
        case 'POST':
            if (empty($input['nombre']) || empty($input['email']) || empty($input['password']) || empty($input['rol'])) {
                echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']); exit;
            }

            // Validación FUERTE (Longitud + Número)
            if (strlen($input['password']) < 8 || !preg_match('/[0-9]/', $input['password'])) {
                echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres y un número.']); exit;
            }

            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$input['email']]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El correo ya está registrado.']); exit;
            }

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
            // Validamos datos básicos (Nota: en auto-edición el frontend debe enviar el rol actual)
            if (empty($input['id_usuario']) || empty($input['nombre']) || empty($input['email']) || empty($input['rol'])) {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos para actualizar.']); exit;
            }

            $passwordQuery = "";
            $params = [$input['nombre'], $input['email'], $input['rol']];

            // Si envía contraseña nueva, aplicamos la MISMA validación fuerte
            if (!empty($input['password'])) {
                if (strlen($input['password']) < 8 || !preg_match('/[0-9]/', $input['password'])) {
                    echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres y un número.']); exit;
                }
                $passwordQuery = ", password = ?";
                $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
            }

            $params[] = $input['id_usuario']; // ID al final para el WHERE

            $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? $passwordQuery WHERE id_usuario = ?";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute($params)) {
                // Si el usuario se editó a sí mismo, actualizamos la sesión para que el header cambie el nombre
                if ($input['id_usuario'] == $_SESSION['user_id']) {
                    $_SESSION['user_name'] = $input['nombre'];
                }
                echo json_encode(['success' => true, 'message' => 'Usuario actualizado.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar.']);
            }
            break;

        // === ELIMINAR USUARIO (DELETE) ===
        case 'DELETE':
            if (empty($input['id_usuario'])) {
                echo json_encode(['success' => false, 'message' => 'Falta el ID.']); exit;
            }
            if ($input['id_usuario'] == $_SESSION['user_id']) {
                echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta.']); exit;
            }

            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
            if ($stmt->execute([$input['id_usuario']])) {
                echo json_encode(['success' => true, 'message' => 'Usuario eliminado.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar.']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']); break;
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>