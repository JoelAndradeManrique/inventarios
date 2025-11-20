<?php
require '../config/db.php'; // Incluimos la conexión

// Datos del Admin por defecto
$nombre = 'Joel';
$email = 'andrademanrique38@gmail.com';
$password_plano = 'Andrade21'; // Cumple mínimo 8 caracteres [cite: 5]
$rol = 'ADMIN';

// 1. Hasheamos la contraseña
// PASSWORD_DEFAULT usa el algoritmo bcrypt actualmente, que es muy seguro
$password_hash = password_hash($password_plano, PASSWORD_DEFAULT);

try {
    // 2. Preparamos la consulta SQL (Para evitar inyecciones SQL)
    $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (:nombre, :email, :pass, :rol)";
    $stmt = $pdo->prepare($sql);
    
    // 3. Ejecutamos la consulta con los datos
    $stmt->execute([
        ':nombre' => $nombre,
        ':email' => $email,
        ':pass' => $password_hash, // Guardamos el HASH, no el texto plano
        ':rol' => $rol
    ]);

    echo "<h1>¡Éxito!</h1>";
    echo "<p>El usuario Administrador ha sido creado.</p>";
    echo "<ul>
            <li>Email: <strong>$email</strong></li>
            <li>Password: <strong>$password_plano</strong> (Guardada como hash seguro)</li>
          </ul>";
    echo "<p>Ahora puedes borrar este archivo 'setup_admin.php' por seguridad.</p>";

} catch (PDOException $e) {
    // Si hay error (ej. si ya existe el email), lo mostramos
    echo "Error al crear usuario: " . $e->getMessage();
}
?>