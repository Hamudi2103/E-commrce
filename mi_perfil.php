<?php
session_start();
require_once 'db_connection.php'; 

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre_usuario'];
$email = '';
$rol = $_SESSION['rol'] ?? 'usuario'; 
$mensaje = $_SESSION['mensaje_perfil'] ?? ''; // Recuperar el mensaje de la sesión
unset($_SESSION['mensaje_perfil']); // Limpiar el mensaje después de mostrarlo


try {
    $stmt = $pdo->prepare("SELECT email FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario_id]);
    $usuario_data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario_data) {
        $email = $usuario_data['email'];
    }
} catch (PDOException $e) {
    $error_db = "Error al obtener la información del perfil: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Your Way's Shoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <section class="profile-container card">
            <h2>Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?></h2>
            
            <?php if (isset($error_db)): ?>
                <div class="error-message"><?php echo $error_db; ?></div>
            <?php endif; ?>

            <div class="profile-details">
                <h3>Detalles de tu cuenta</h3>
                <p><strong>Nombre de Usuario:</strong> <?php echo htmlspecialchars($nombre_usuario); ?></p>
                <p><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($email); ?></p>
                <p><strong>Rol:</strong> <?php echo htmlspecialchars($rol); ?></p>
            </div>

            <div class="profile-actions">
                <h3>Acciones</h3>
                <ul class="action-list">
                    <li><a href="editar_mi_perfil.php" class="btn btn-primary"><i class="fas fa-edit"></i> Editar Información Personal</a></li>
                    <li><a href="cambiar_contrasena.php" class="btn btn-secondary"><i class="fas fa-key"></i> Cambiar Contraseña</a></li>
                    <li><a href="facturas.php" class="btn btn-info"><i class="fas fa-file-invoice"></i> Ver Facturas</a></li> <li><a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                </ul>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Your Way's Shoes. Tu Estilo, Tus Pasos.</p>
        </div>
    </footer>
</body>
</html>