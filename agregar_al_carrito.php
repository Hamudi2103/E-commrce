<?php
session_start();
require_once 'db_connection.php'; 


if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && is_numeric($_POST['id']) && isset($_POST['cantidad']) && is_numeric($_POST['cantidad']) && isset($_POST['talla'])) {
    $producto_id = (int)$_POST['id']; 
    $cantidad = (int)$_POST['cantidad'];
    $talla_seleccionada = htmlspecialchars($_POST['talla']);

    
    if ($cantidad <= 0) {
        $_SESSION['mensaje'] = "<div class='error-message'>La cantidad debe ser un número positivo.</div>";
        header('Location: index.php');
        exit();
    }

    
    if (!isset($_SESSION['usuario_id'])) {
        $_SESSION['mensaje'] = "<div class='error-message'>Debes iniciar sesión para añadir productos al carrito.</div>";
        header('Location: login.php');
        exit();
    }

    $usuario_id = $_SESSION['usuario_id'];

    try {
        
        $stmt_producto = $pdo->prepare("SELECT `id`, `nombre`, `precio`, `imagen` FROM `zapatos` WHERE `id` = ?");
        $stmt_producto->execute([$producto_id]);
        $producto_data = $stmt_producto->fetch(PDO::FETCH_ASSOC);

        if ($producto_data) {
            $nombre_producto = $producto_data['nombre'];
            $precio_unitario = $producto_data['precio'];
            $imagen_producto = $producto_data['imagen']; 
            $stmt_check = $pdo->prepare("SELECT `id`, `cantidad` FROM `carrito` WHERE `usuario_id` = ? AND `producto_id` = ? AND `talla_seleccionada` = ?");
            $stmt_check->execute([$usuario_id, $producto_id, $talla_seleccionada]);
            $item_existente = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($item_existente) {
               
                $nueva_cantidad = $item_existente['cantidad'] + $cantidad;
                $stmt = $pdo->prepare("UPDATE `carrito` SET `cantidad` = ? WHERE `id` = ?");
                $stmt->execute([$nueva_cantidad, $item_existente['id']]);
            } else {
                
                $stmt = $pdo->prepare("INSERT INTO `carrito` (`usuario_id`, `producto_id`, `cantidad`, `precio_unitario`, `talla_seleccionada`) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$usuario_id, $producto_id, $cantidad, $precio_unitario, $talla_seleccionada]);
            }

            $_SESSION['mensaje'] = "<div class='success-message'>Producto añadido al carrito correctamente.</div>";
            header('Location: carrito.php'); 
            exit();

        } else {
            $_SESSION['mensaje'] = "<div class='error-message'>El producto no fue encontrado en el catálogo.</div>";
            header('Location: index.php'); 
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "<div class='error-message'>Error al añadir el producto al carrito: " . $e->getMessage() . "</div>";
        error_log("Error al añadir al carrito: " . $e->getMessage());
        exit();
    }
} else {
    
    $_SESSION['mensaje'] = "<div class='error-message'>Datos inválidos para añadir al carrito.</div>";
    header('Location: index.php');
    exit();
}
?>