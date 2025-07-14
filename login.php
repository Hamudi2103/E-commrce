<?php
session_start();
require_once 'db_connection.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario_input = $_POST['nombre_usuario'] ?? '';
    $contrasena_input = $_POST['contrasena'] ?? '';

    $nombre_usuario = trim($nombre_usuario_input);
    $contrasena = trim($contrasena_input);

    if (empty($nombre_usuario) || empty($contrasena)) {
        $mensaje = "<div class='error-message'>Por favor, ingresa tu nombre de usuario y contraseña.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, nombre_usuario, contrasena_hash, rol FROM usuarios WHERE nombre_usuario = ?");
            $stmt->execute([$nombre_usuario]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($contrasena, $usuario['contrasena_hash'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
                $_SESSION['rol'] = $usuario['rol'];
                header('Location: index.php');
                exit();
            } else {
                $mensaje = "<div class='error-message'>Nombre de usuario o contraseña incorrectos.</div>";
            }
        } catch (PDOException $e) {
            $mensaje = "<div class='error-message'>Error de base de datos: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Your Way's Shoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header_minimal.php'; ?>

    <main class="container">
        <section class="form-container card">
            <h2>Iniciar Sesión</h2>
            <?php echo $mensaje; ?>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="nombre_usuario">Nombre de Usuario:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                </div>
                <div class="form-group">
                    <label for="contrasena">Contraseña:</label>
                    <input type="password" id="contrasena" name="contrasena" required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</button>
            </form>
            <p class="form-link">¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>
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