<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar_cambios'])) {
        $nuevo_nombre_usuario = $_POST['nombre_usuario'] ?? '';
        $nuevo_email = $_POST['email'] ?? '';

        if (empty($nuevo_nombre_usuario) || empty($nuevo_email)) {
            $mensaje = "<p class='error-message'>Todos los campos son obligatorios.</p>";
        } elseif (!filter_var($nuevo_email, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "<p class='error-message'>El formato del correo electrónico no es válido.</p>";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE (nombre_usuario = ? OR email = ?) AND id != ?");
                $stmt->execute([$nuevo_nombre_usuario, $nuevo_email, $usuario_id]);
                if ($stmt->fetch()) {
                    $mensaje = "<p class='error-message'>El nombre de usuario o correo electrónico ya está en uso por otra cuenta.</p>";
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre_usuario = ?, email = ? WHERE id = ?");
                    if ($stmt->execute([$nuevo_nombre_usuario, $nuevo_email, $usuario_id])) {
                        $_SESSION['nombre_usuario'] = $nuevo_nombre_usuario;
                        $_SESSION['mensaje_perfil'] = "<p class='success-message'> Perfil actualizado exitosamente.</p>";
                        header('Location: mi_perfil.php');
                        exit();
                    } else {
                        $mensaje = "<p class='error-message'>Error al actualizar el perfil.</p>";
                    }
                }
            } catch (PDOException $e) {
                $mensaje = "<p class='error-message'>Error de base de datos al actualizar perfil: " . $e->getMessage() . "</p>";
            }
        }
    }
    $nombre_usuario_actual = $_POST['nombre_usuario'] ?? $nombre_usuario_actual;
    $email_actual = $_POST['email'] ?? $email_actual;
} else {
    try {
        $stmt = $pdo->prepare("SELECT nombre_usuario, email FROM usuarios WHERE id = ?");
        $stmt->execute([$usuario_id]);
        $usuario_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario_data) {
            $nombre_usuario_actual = $usuario_data['nombre_usuario'];
            $email_actual = $usuario_data['email'];
        } else {
            $mensaje = "<p class='error-message'>Error: No se pudieron cargar los datos del usuario.</p>";
        }
    } catch (PDOException $e) {
        $mensaje = "<p class='error-message'>Error de base de datos al cargar perfil: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Mi Perfil - Your Way's Shoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <section class="form-container card">
            <h2>Editar Mi Perfil</h2>
            <?php echo $mensaje; ?>
            <form method="POST" action="editar_mi_perfil.php">
                <div class="form-group">
                    <label for="nombre_usuario">Nombre de Usuario:</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" value="<?php echo htmlspecialchars($nombre_usuario_actual); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email_actual); ?>" required>
                </div>
                <button type="submit" name="guardar_cambios" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
                <a href="mi_perfil.php" class="btn btn-secondary"><i class="fas fa-times-circle"></i> Cancelar</a>
            </form>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Your Way's Shoes. Tu Estilo, Tus Pasos.</p>
        </div>
    </footer>
</body>
</html>