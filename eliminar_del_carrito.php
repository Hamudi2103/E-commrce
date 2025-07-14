<?php
session_start();
require_once 'db_connection.php';


if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensaje'] = "<div class='error-message'>Debes iniciar sesión para modificar el carrito.</div>";
    header('Location: login.php');
    exit();
}

if (isset($_GET['item_id'])) {
    $carrito_item_id = intval($_GET['item_id']);
    $usuario_id = $_SESSION['usuario_id'];

    try {
        
        $stmt = $pdo->prepare("DELETE FROM carrito WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$carrito_item_id, $usuario_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['mensaje'] = "<div class='success-message'>Producto eliminado del carrito correctamente.</div>";
        } else {
            $_SESSION['mensaje'] = "<div class='error-message'>El producto no se encontró en tu carrito o ya fue eliminado.</div>";
        }
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "<div class='error-message'>Error al eliminar el producto del carrito: " . $e->getMessage() . "</div>";
        error_log("Error al eliminar del carrito: " . $e->getMessage()); 
    }
} else {
    $_SESSION['mensaje'] = "<div class='error-message'>ID de artículo de carrito no especificado.</div>";
}


header('Location: carrito.php');
exit();
?>