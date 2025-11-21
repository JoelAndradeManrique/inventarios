<?php
session_start(); // Iniciar sesión PHP para guardar datos del usuario
header('Content-Type: application/json'); // Responderemos siempre en JSON

// 1. Incluir conexión (ajustando la ruta según tu estructura)
require_once '../config/db.php'; 

// 2. Obtener los datos enviados por el Frontend (JSON)
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    exit;
}

$email = $data['email'];
$password = $data['password'];

try {
    // 3. Buscar usuario por email
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 4. Verificar si existe y si la contraseña coincide
    if ($user && password_verify($password, $user['password'])) {
        
        // ¡LOGIN EXITOSO! Guardamos datos en sesión
        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_role'] = $user['rol']; // 'ADMIN' o 'EMPLEADO'

        // Respondemos éxito y la ruta a donde debe ir
        echo json_encode([
            'success' => true, 
            'redirect' => '../views/inicio_fase1.php' // O 'views/inventarios.php', tú decides la home
        ]);
    } else {
        // Login fallido
        echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>