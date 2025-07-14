<?php
session_start();
require_once 'db_connection.php'; 

if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensaje'] = "<div class='error-message'>Debes iniciar sesión para ver tus facturas.</div>";
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$ordenes = []; 
$mensaje_ordenes = ''; 

try {
    

    $stmt_ordenes = $pdo->prepare("
        SELECT 
            o.id AS orden_id, 
            o.fecha_orden, 
            o.total_orden, 
            o.estado_orden,
            mp.tipo_tarjeta,
            mp.ultimos_cuatro_digitos
        FROM 
            ordenes o
        LEFT JOIN 
            metodos_pago mp ON o.metodo_pago_id = mp.id
        WHERE 
            o.usuario_id = ?
        ORDER BY 
            o.fecha_orden DESC
    ");
    $stmt_ordenes->execute([$usuario_id]);
    $ordenes = $stmt_ordenes->fetchAll(PDO::FETCH_ASSOC);

    if (empty($ordenes)) {
        $mensaje_ordenes = "<p class='info-message'>No has realizado ninguna compra aún.</p>";
    }

} catch (PDOException $e) {
    $mensaje_ordenes = "<p class='error-message'>Error al cargar el historial de órdenes: " . $e->getMessage() . "</p>";
    error_log("Error al cargar historial de órdenes: " . $e->getMessage()); 
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Facturas - Your Way's Shoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php';  ?>

    <main class="container">
        <h1>Mi Historial de Órdenes (Facturas)</h1>

        <section class="order-history card">
            <?php echo $mensaje_ordenes;  ?>

            <?php if (!empty($ordenes)):  ?>
                <?php foreach ($ordenes as $orden): ?>
                    <div class="order-item">
                        <h3>Factura #<?php echo htmlspecialchars($orden['orden_id']); ?></h3>
                        <p><strong>Fecha:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($orden['fecha_orden']))); ?></p>
                        <p><strong>Total:</strong> $<?php echo number_format($orden['total_orden'], 2); ?></p>
                        <p><strong>Estado:</strong> <span class="order-status <?php echo strtolower(str_replace(' ', '-', $orden['estado_orden'])); ?>"><?php echo htmlspecialchars($orden['estado_orden']); ?></span></p>
                        <?php if ($orden['tipo_tarjeta']): ?>
                            <p><strong>Pagado con:</strong> <?php echo htmlspecialchars($orden['tipo_tarjeta']); ?> (**** <?php echo htmlspecialchars($orden['ultimos_cuatro_digitos']); ?>)</p>
                        <?php endif; ?>

                        <h4>Detalles de los Productos:</h4>
                        <ul class="order-details-list">
                            <?php
                            
                            $stmt_detalles = $pdo->prepare("
                                SELECT 
                                    od.cantidad, 
                                    od.precio_unitario, 
                                    od.talla_seleccionada,
                                    z.nombre AS nombre_producto,
                                    z.imagen AS imagen_producto
                                FROM 
                                    detalles_orden od
                                JOIN 
                                    zapatos z ON od.producto_id = z.id
                                WHERE 
                                    od.orden_id = ?
                            ");
                            $stmt_detalles->execute([$orden['orden_id']]);
                            $detalles_productos = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($detalles_productos as $detalle):
                            ?>
                                <li>
                                    <img src="<?php echo htmlspecialchars($detalle['imagen_producto']); ?>" alt="<?php echo htmlspecialchars($detalle['nombre_producto']); ?>" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                    <?php echo htmlspecialchars($detalle['nombre_producto']); ?> (Talla: <?php echo htmlspecialchars($detalle['talla_seleccionada']); ?>) - Cantidad: <?php echo htmlspecialchars($detalle['cantidad']); ?> x $<?php echo number_format($detalle['precio_unitario'], 2); ?> = $<?php echo number_format($detalle['cantidad'] * $detalle['precio_unitario'], 2); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="back-link">
                <a href="mi_perfil.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver a Mi Perfil</a>
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