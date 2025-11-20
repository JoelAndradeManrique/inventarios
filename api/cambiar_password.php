<?php
// api/cambiar_password.php
require_once '../config/db.php';
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$token = $data['token'] ?? '';
$newPass = $data['password'] ?? '';

if (!$token || !$newPass) { echo json_encode(['success'=>false, 'message'=>'Datos incompletos']); exit; }

try {
    // 1. Buscar usuario con ese token y que NO haya expirado
    // NOW() <= fecha_expiracion
    $sql = "SELECT id_usuario FROM usuarios WHERE token_recuperacion = ? AND fecha_expiracion_token > NOW()";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // 2. Hashear nueva contraseña
        $hash = password_hash($newPass, PASSWORD_DEFAULT);

        // 3. Actualizar pass y BORRAR el token (para que no se use 2 veces)
        $update = $pdo->prepare("UPDATE usuarios SET password = ?, token_recuperacion = NULL, fecha_expiracion_token = NULL WHERE id_usuario = ?");
        $update->execute([$hash, $user['id_usuario']]);

        echo json_encode(['success' => true]);
    } else {
        // Token no existe o ya expiró
        echo json_encode(['success' => false, 'message' => 'El enlace ha expirado o es inválido.', 'expired' => true]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>