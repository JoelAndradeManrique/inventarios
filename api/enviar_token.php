<?php
// api/enviar_token.php

// 1. Cargar el Autoloader de Composer (¡Esto carga PHPMailer y todo lo demás!)
require '../vendor/autoload.php'; 
require_once '../config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Leer JSON de entrada
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

if (!$email) { echo json_encode(['success'=>false, 'message'=>'Falta email']); exit; }

try {
    // 2. Buscar usuario
    $stmt = $pdo->prepare("SELECT id_usuario, nombre FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // 3. Generar Token
        $token = bin2hex(random_bytes(32));
        $expiracion = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // Actualizar BD
        $update = $pdo->prepare("UPDATE usuarios SET token_recuperacion = ?, fecha_expiracion_token = ? WHERE email = ?");
        $update->execute([$token, $expiracion, $email]);

        // Link de recuperación
        $link = "http://localhost/Inventarios/views/recuperarContrasena.php?token=" . $token;

        // 4. Configurar PHPMailer con Composer
        $mail = new PHPMailer(true);

        try {
            // Configuración SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'joelmanrique38@gmail.com'; // <--- CAMBIAR AQUÍ
            $mail->Password   = 'xwms kppj fhah wfzg';      // <--- TU CONTRASEÑA DE APLICACIÓN (16 LETRAS)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Corrección para XAMPP local (Certificados SSL)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Destinatarios
            $mail->setFrom('tu_correo_real@gmail.com', 'Sistema Inventarios'); // <--- CAMBIAR AQUÍ
            $mail->addAddress($email, $user['nombre']);

            // Contenido
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8'; // Importante para acentos y ñ
            $mail->Subject = 'Recuperar Contraseña';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; color: #333;'>
                    <h2>Hola, {$user['nombre']}</h2>
                    <p>Recibimos una solicitud para restablecer tu contraseña.</p>
                    <p>Haz clic en el botón de abajo para continuar:</p>
                    <br>
                    <a href='$link' style='background-color: #f5d04c; color: #000; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Restablecer Contraseña</a>
                    <br><br>
                    <p><small>Este enlace expira en 5 minutos. Si no fuiste tú, ignora este mensaje.</small></p>
                </div>
            ";

            $mail->send();
            echo json_encode(['success' => true, 'message' => 'Correo enviado con éxito.']);

        } catch (Exception $e) {
            // Error al enviar correo (pero el usuario existe)
            echo json_encode(['success' => false, 'message' => 'Error al enviar correo: ' . $mail->ErrorInfo]);
        }

    } else {
    // AVISO REAL: El correo no existe en la base de datos
    echo json_encode(['success' => false, 'message' => 'El correo ingresado no está registrado.']);
}

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>