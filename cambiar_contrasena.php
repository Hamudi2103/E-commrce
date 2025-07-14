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
   
    if (isset($_POST['cambiar_contrasena'])) { 
        $contrasena_actual = $_POST['contrasena_actual'] ?? '';
        $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
        $confirmar_nueva_contrasena = $_POST['confirmar_nueva_contrasena'] ?? '';

        
        if (empty($contrasena_actual) || empty($nueva_contrasena) || empty($confirmar_nueva_contrasena)) {
            $mensaje = "<p class='error-message'>Todos los campos son obligatorios.</p>";
        } elseif ($nueva_contrasena !== $confirmar_nueva_contrasena) {
            $mensaje = "<p class='error-message'>La nueva contraseña y la confirmación no coinciden.</p>";
        } elseif (strlen($nueva_contrasena) < 6) { 
            $mensaje = "<p class='error-message'>La nueva contraseña debe tener al menos 6 caracteres.</p>";
        } else {
            try {
               
                $stmt = $pdo->prepare("SELECT contrasena FROM usuarios WHERE id = ?");
                $stmt->execute([$usuario_id]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario && password_verify($contrasena_actual, $usuario['contrasena'])) {
                   
                    $nueva_contrasena_hashed = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

                    
                    $stmt_update = $pdo->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
                    $stmt_update->execute([$nueva_contrasena_hashed, $usuario_id]);

                    $mensaje = "<p class='success-message'>Contraseña actualizada exitosamente.</p>";
                } else {
                    $mensaje = "<p class='error-message'>La contraseña actual es incorrecta.</p>";
                }

            } catch (PDOException $e) {
                
                $mensaje = "<p class='error-message'>Error de base de datos al cambiar contraseña: " . $e->getMessage() . "</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña - Your Way's Shoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <section class="form-container card">
            <h2>Cambiar Contraseña</h2>
            <?php echo $mensaje; ?>
            <form method="POST" action="cambiar_contrasena.php">
                <div class="form-group">
                    <label for="contrasena_actual">Contraseña Actual:</label>
                    <input type="password" id="contrasena_actual" name="contrasena_actual" required>
                </div>
                <div class="form-group">
                    <label for="nueva_contrasena">Nueva Contraseña:</label>
                    <input type="password" id="nueva_contrasena" name="nueva_contrasena" required>
                </div>
                <div class="form-group">
                    <label for="confirmar_nueva_contrasena">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="confirmar_nueva_contrasena" name="confirmar_nueva_contrasena" required>
                </div>
                <button type="submit" name="cambiar_contrasena" class="btn btn-primary"><i class="fas fa-key"></i> Cambiar Contraseña</button>
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