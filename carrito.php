<?php
session_start();
require_once 'db_connection.php'; 


if (file_exists('db_connection.php')) {
    require_once 'db_connection.php';
} else {
    
    die("Error: db_connection.php no encontrado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu Carrito de Compras - Your Way's Shoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php';  ?>

    <main class="container">
        <h1>Tu Carrito de Compras</h1>

        <section class="cart-section card">
            <?php
            
            $items_carrito = [];
            $total_carrito = 0;
            $mensaje = $_SESSION['mensaje'] ?? ''; 
            unset($_SESSION['mensaje']);

            
            if (isset($_SESSION['usuario_id'])) {
                try {
                    $stmt = $pdo->prepare("
                        SELECT
                            ci.id AS carrito_item_id,
                            ci.producto_id,
                            ci.cantidad,
                            ci.precio_unitario,
                            ci.talla_seleccionada,
                            z.nombre,
                            z.imagen
                        FROM
                            carrito ci
                        JOIN
                            zapatos z ON ci.producto_id = z.id
                        WHERE
                            ci.usuario_id = ?
                    ");
                    $stmt->execute([$_SESSION['usuario_id']]);
                    $items_carrito = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($items_carrito as $item) {
                        $total_carrito += $item['precio_unitario'] * $item['cantidad'];
                    }

                } catch (PDOException $e) {
                    $mensaje = "<div class='error-message'>Error al cargar el carrito: " . $e->getMessage() . "</div>";
                }
            }
            ?>

            <?php if (!empty($mensaje)): ?>
                <div class="message-container">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($items_carrito)): ?>
                <p>Tu carrito de compras está vacío.</p>
                <a href="index.php" class="btn continue-shopping-btn"><i class="fas fa-shopping-basket"></i> Seguir Comprando</a>
            <?php else: ?>
                <div class="cart-items-container">
                    <?php foreach ($items_carrito as $item):
                       
                    ?>
                        <div class="cart-item card">
                            <img src="<?php echo htmlspecialchars($item['imagen']); ?>" alt="<?php echo htmlspecialchars($item['nombre']); ?>">
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
                                <p>Precio Unitario: $<?php echo number_format($item['precio_unitario'], 2); ?></p>
                                <p>Cantidad: <?php echo htmlspecialchars($item['cantidad']); ?></p>
                                <p>Talla: <?php echo htmlspecialchars($item['talla_seleccionada'] ?? 'N/A'); ?></p>
                                <p>Subtotal: $<?php echo number_format($item['precio_unitario'] * $item['cantidad'], 2); ?></p>
                            </div>
                            <a href="eliminar_del_carrito.php?item_id=<?php echo htmlspecialchars($item['carrito_item_id']); ?>" class="btn remove-item-btn">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="cart-summary">
                    <h3>Total del Carrito: $<?php echo number_format($total_carrito, 2); ?></h3>
                    <a href="metodo_pago.php" class="btn checkout-btn"><i class="fas fa-credit-card"></i> Proceder al Pago</a>
                    <a href="index.php" class="btn continue-shopping-btn"><i class="fas fa-shopping-basket"></i> Seguir Comprando</a>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Your Way's Shoes. Tu Estilo, Tus Pasos.</p>
        </div>
    </footer>
</body> 
</html>