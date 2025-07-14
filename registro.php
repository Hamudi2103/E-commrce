<?php
session_start();
require_once 'db_connection.php';

$mensaje = '';
$nombre_usuario = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $email = $_POST['email'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

    if (empty($nombre_usuario) || empty($email) || empty($contrasena) || empty($confirmar_contrasena)) {
        $mensaje = "<div class='error-message'>Todos los campos son obligatorios.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "<div class='error-message'>El formato del correo electrónico no es válido.</div>";
    } elseif ($contrasena !== $confirmar_contrasena) {
        $mensaje = "<div class='error-message'>Las contraseñas no coinciden.</div>";
    } elseif (strlen($contrasena) < 6) {
        $mensaje = "<div class='error-message'>La contraseña debe tener al menos 6 caracteres.</div>";
    } else {
        try {
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE nombre_usuario = ? OR email = ?");
            $stmt_check->execute([$nombre_usuario, $email]);
            if ($stmt_check->fetchColumn() > 0) {
                $mensaje = "<div class='error-message'>El nombre de usuario o correo electrónico ya están registrados.</div>";
            } else {
                $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);

                $stmt_insert = $pdo->prepare("INSERT INTO usuarios (nombre_usuario, email, contrasena_hash, rol) VALUES (?, ?, ?, 'usuario')");
                $stmt_insert->execute([$nombre_usuario, $email, $contrasena_hash]);

                $mensaje = "<div class='success-message'>¡Registro exitoso! Ahora puedes <a href='login.php'>iniciar sesión</a>.</div>";

                $nombre_usuario = $email = '';
            }
        } catch (PDOException $e) {
            $mensaje = "<div class='error-message'>Error al registrar usuario: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Your Way's Shoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header_minimal.php';  ?>

    <main class="container">
        <section class="form-container card">
            <h2>Registrarse</h2>
            <?php echo $mensaje; ?>
            <form method="POST" action="registro.php">
                <div class="form-group">
                    <label for="nombre_usuario">Nombre de Usuario:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" required value="<?php echo htmlspecialchars($nombre_usuario); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="form-group">
                    <label for="contrasena">Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" required>
                </div>
                <div class="form-group">
                    <label for="confirmar_contrasena">Confirmar Contraseña:</label>
                    <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Registrarse</button>
            </form>
            <p class="form-link">¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
            <a href="index.php" class="btn btn-secondary back-btn"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Your Way's Shoes. Tu Estilo, Tus Pasos.</p>
        </div>
    </footer>
</body>
</html>