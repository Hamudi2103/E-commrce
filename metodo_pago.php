<?php
session_start();
require_once 'db_connection.php'; // Asegúrate de que esta ruta sea correcta

// Redirigir si el usuario no está logueado
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['mensaje'] = "<div class='error-message'>Debes iniciar sesión para gestionar tus métodos de pago.</div>";
    header('Location: login.php');
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = $_SESSION['mensaje'] ?? ''; // Para mostrar mensajes de éxito/error
unset($_SESSION['mensaje']); // Limpiar el mensaje después de mostrarlo

// --- Lógica para añadir un nuevo método de pago ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar_metodo') {
    $tipo_tarjeta = htmlspecialchars(trim($_POST['tipo_tarjeta'] ?? ''));
    $numero_tarjeta = str_replace(' ', '', htmlspecialchars(trim($_POST['numero_tarjeta'] ?? ''))); // Limpiar espacios
    $fecha_expiracion = htmlspecialchars(trim($_POST['fecha_expiracion'] ?? ''));
    $cvv = htmlspecialchars(trim($_POST['cvv'] ?? '')); // Campo para el CVV
    $es_predeterminado = isset($_POST['predeterminado']) ? 1 : 0;
    
    // --- Procesar fecha de expiración ---
    $mes_expiracion = 0;
    $año_expiracion = 0;
    $fecha_valida = false;

    if (preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $fecha_expiracion, $matches)) {
        $mes_expiracion = (int)$matches[1];
        $año_expiracion = (int)('20' . $matches[2]); // Asume año en formato YY (ej. 25 -> 2025)
        $fecha_valida = true;

        // Validar que la fecha no esté en el pasado
        $fecha_actual = new DateTime();
        $fecha_exp = new DateTime("$año_expiracion-$mes_expiracion-01");
        // Establecer el día al último del mes para una validación más precisa
        $fecha_exp->setDate($año_expiracion, $mes_expiracion, date('t', mktime(0, 0, 0, $mes_expiracion, 1, $año_expiracion)));

        if ($fecha_exp < $fecha_actual) {
            $fecha_valida = false;
        }
    }


    // --- Validación de campos ---
    if (empty($tipo_tarjeta) || empty($numero_tarjeta) || empty($fecha_expiracion) || empty($cvv)) {
        $mensaje = "<div class='error-message'>Todos los campos obligatorios deben ser completados.</div>";
    } elseif (!preg_match('/^[0-9]{13,19}$/', $numero_tarjeta)) { // Validación básica de número de tarjeta (solo dígitos, longitud)
        $mensaje = "<div class='error-message'>El número de tarjeta no es válido.</div>";
    } elseif (!preg_match('/^[0-9]{3,4}$/', $cvv)) { // Validación CVV (3 o 4 dígitos)
        $mensaje = "<div class='error-message'>El código de seguridad (CVV) no es válido (3 o 4 dígitos).</div>";
    } elseif (!$fecha_valida) {
        $mensaje = "<div class='error-message'>La fecha de expiración no es válida o está en el pasado (formato MM/AA).</div>";
    } else {
        // Obtener los últimos 4 dígitos para almacenar
        $ultimos_cuatro = substr($numero_tarjeta, -4);
        
        // Simulación de token: en una app real, esto vendría de la pasarela de pago.
        // NUNCA PONGAS DATOS SENSIBLES DE TARJETAS AQUÍ (número completo, CVV).
        $token_pago = 'simulated_token_' . uniqid(); // Generar un token único simulado

        try {
            $pdo->beginTransaction();

            // Si se marca como predeterminado, desmarcar los otros métodos predeterminados del usuario
            if ($es_predeterminado) {
                $stmt = $pdo->prepare("UPDATE metodos_pago SET predeterminado = FALSE WHERE usuario_id = ?");
                $stmt->execute([$usuario_id]);
            }

            // Insertar el nuevo método de pago
            // NOTA DE SEGURIDAD: 'numero_tarjeta' y 'cvv' NO SE ALMACENAN en la DB.
            // Se almacena 'ultimos_cuatro_digitos' y 'token_pago'.
            $stmt = $pdo->prepare("INSERT INTO metodos_pago (usuario_id, tipo_tarjeta, ultimos_cuatro_digitos, mes_expiracion, año_expiracion, token_pago, predeterminado) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$usuario_id, $tipo_tarjeta, $ultimos_cuatro, $mes_expiracion, $año_expiracion, $token_pago, $es_predeterminado]);

            $pdo->commit();
            $_SESSION['mensaje'] = "<div class='success-message'>Método de pago añadido correctamente.</div>";
            header('Location: metodo_pago.php');
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje = "<div class='error-message'>Error al añadir método de pago: " . $e->getMessage() . "</div>";
            error_log("Error al añadir método de pago: " . $e->getMessage());
        }
    }
}

// --- Lógica para eliminar un método de pago ---
if (isset($_GET['accion']) && $_GET['accion'] === 'eliminar' && isset($_GET['id'])) {
    $metodo_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM metodos_pago WHERE id = ? AND usuario_id = ?");
        if ($stmt->execute([$metodo_id, $usuario_id])) {
            $_SESSION['mensaje'] = "<div class='success-message'>Método de pago eliminado correctamente.</div>";
        } else {
            $_SESSION['mensaje'] = "<div class='error-message'>No se pudo eliminar el método de pago o no te pertenece.</div>";
        }
    } catch (PDOException $e) {
        $_SESSION['mensaje'] = "<div class='error-message'>Error al eliminar método de pago: " . $e->getMessage() . "</div>";
        error_log("Error al eliminar método de pago: " . $e->getMessage());
    }
    header('Location: metodo_pago.php');
    exit();
}

// --- Lógica para establecer un método como predeterminado ---
if (isset($_GET['accion']) && $_GET['accion'] === 'establecer_predeterminado' && isset($_GET['id'])) {
    $metodo_id_a_establecer = (int)$_GET['id'];
    try {
        $pdo->beginTransaction();

        // Desmarcar todos los métodos como predeterminados para este usuario
        $stmt_reset = $pdo->prepare("UPDATE metodos_pago SET predeterminado = FALSE WHERE usuario_id = ?");
        $stmt_reset->execute([$usuario_id]);

        // Marcar el método seleccionado como predeterminado
        $stmt_set = $pdo->prepare("UPDATE metodos_pago SET predeterminado = TRUE WHERE id = ? AND usuario_id = ?");
        if ($stmt_set->execute([$metodo_id_a_establecer, $usuario_id])) {
            $pdo->commit();
            $_SESSION['mensaje'] = "<div class='success-message'>Método de pago establecido como predeterminado.</div>";
        } else {
            $pdo->rollBack();
            $_SESSION['mensaje'] = "<div class='error-message'>No se pudo establecer el método como predeterminado o no te pertenece.</div>";
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['mensaje'] = "<div class='error-message'>Error al establecer predeterminado: " . $e->getMessage() . "</div>";
        error_log("Error al establecer predeterminado: " . $e->getMessage());
    }
    header('Location: metodo_pago.php');
    exit();
}

// --- Lógica para realizar un pago (simulado) y vaciar el carrito ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'realizar_pago') {
    $metodo_pago_seleccionado_id = (int)($_POST['metodo_pago_seleccionado'] ?? 0);

    if ($metodo_pago_seleccionado_id > 0) {
        try {
            $pdo->beginTransaction();

            // 1. Obtener los ítems del carrito antes de vaciarlo
            // Asegúrate de que la tabla 'carrito' tiene 'producto_id', 'cantidad', 'precio_unitario', 'talla_seleccionada'
            $stmt_items_carrito = $pdo->prepare("
                SELECT 
                    ci.producto_id, 
                    ci.cantidad, 
                    ci.precio_unitario, 
                    ci.talla_seleccionada
                FROM 
                    carrito ci
                WHERE 
                    ci.usuario_id = ?
            ");
            $stmt_items_carrito->execute([$usuario_id]);
            $items_carrito = $stmt_items_carrito->fetchAll(PDO::FETCH_ASSOC);

            if (empty($items_carrito)) {
                $_SESSION['mensaje'] = "<div class='error-message'>Tu carrito está vacío. No se puede procesar el pago.</div>";
                $pdo->rollBack();
                header('Location: carrito.php'); // Redirigir al carrito si está vacío
                exit();
            }

            // Calcular el total de la orden
            $total_orden = 0;
            foreach ($items_carrito as $item) {
                $total_orden += ($item['cantidad'] * $item['precio_unitario']);
            }

            // 2. Insertar la orden principal en la tabla `ordenes`
            // Aquí podrías obtener la dirección de envío del perfil del usuario si la tuvieras
            // Por simplicidad, usaremos un valor por defecto o un campo extra si lo agregas al perfil
            $direccion_envio_simulada = "Dirección de envío del usuario (ej. desde el perfil)"; 

            $stmt_insert_orden = $pdo->prepare("
                INSERT INTO ordenes (usuario_id, metodo_pago_id, total_orden, estado_orden, direccion_envio)
                VALUES (?, ?, ?, 'Completado', ?)
            ");
            $stmt_insert_orden->execute([$usuario_id, $metodo_pago_seleccionado_id, $total_orden, $direccion_envio_simulada]);
            $orden_id = $pdo->lastInsertId(); // Obtener el ID de la orden recién insertada

            // 3. Insertar los detalles de la orden en la tabla `detalles_orden`
            // Asegúrate de que las columnas coincidan con las de tu tabla `detalles_orden`
            $stmt_insert_detalle = $pdo->prepare("
                INSERT INTO detalles_orden (orden_id, producto_id, cantidad, precio_unitario, talla_seleccionada)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($items_carrito as $item) {
                $stmt_insert_detalle->execute([
                    $orden_id,
                    $item['producto_id'],
                    $item['cantidad'],
                    $item['precio_unitario'],
                    $item['talla_seleccionada']
                ]);
            }

            // 4. Vaciar el carrito después de guardar los detalles de la orden
            $stmt_vaciar_carrito = $pdo->prepare("DELETE FROM carrito WHERE usuario_id = ?");
            $stmt_vaciar_carrito->execute([$usuario_id]);

            $pdo->commit();
            $_SESSION['mensaje'] = "<div class='success-message'>¡Pago realizado con éxito! Tu orden #{$orden_id} ha sido registrada y tu carrito ha sido vaciado.</div>";
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['mensaje'] = "<div class='error-message'>Error al procesar el pago o vaciar el carrito: " . $e->getMessage() . "</div>";
            error_log("Error al procesar pago/vaciar carrito: " . $e->getMessage());
        }
    } else {
        $_SESSION['mensaje'] = "<div class='error-message'>Por favor, selecciona un método de pago para realizar la compra.</div>";
    }
    header('Location: metodo_pago.php'); // Redirigir a la misma página para mostrar el mensaje
    exit();
}


// --- Obtener los métodos de pago del usuario para mostrar ---
$metodos_pago = [];
try {
    $stmt = $pdo->prepare("SELECT id, tipo_tarjeta, ultimos_cuatro_digitos, mes_expiracion, año_expiracion, predeterminado FROM metodos_pago WHERE usuario_id = ? ORDER BY predeterminado DESC, fecha_creacion DESC");
    $stmt->execute([$usuario_id]);
    $metodos_pago = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mensaje = "<div class='error-message'>Error al cargar métodos de pago: " . $e->getMessage() . "</div>";
    error_log("Error al cargar métodos de pago: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Métodos de Pago - Your Way's Shoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; // Incluye el header ?>

    <main class="container">
        <h1>Mis Métodos de Pago</h1>

        <section class="payment-methods-list card">
            <?php echo $mensaje; // Mostrar mensajes ?>

            <?php if (empty($metodos_pago)): ?>
                <p>No tienes ningún método de pago guardado. Por favor, añade uno a continuación.</p>
            <?php else: ?>
                <h2>Tus Tarjetas Guardadas</h2>
                <form action="metodo_pago.php" method="POST" class="select-payment-form">
                    <input type="hidden" name="accion" value="realizar_pago">
                    <div class="payment-methods-grid">
                        <?php foreach ($metodos_pago as $metodo): ?>
                            <div class="payment-method-item <?php echo $metodo['predeterminado'] ? 'default-method' : ''; ?>">
                                <input type="radio" 
                                        id="metodo_<?php echo htmlspecialchars($metodo['id']); ?>" 
                                        name="metodo_pago_seleccionado" 
                                        value="<?php echo htmlspecialchars($metodo['id']); ?>" 
                                        <?php echo $metodo['predeterminado'] ? 'checked' : ''; ?>
                                        required>
                                <label for="metodo_<?php echo htmlspecialchars($metodo['id']); ?>">
                                    <h3><?php echo htmlspecialchars($metodo['tipo_tarjeta']); ?> </h3>
                                    <p>**** **** **** <?php echo htmlspecialchars($metodo['ultimos_cuatro_digitos']); ?></p>
                                    <p>Expira: <?php echo htmlspecialchars(sprintf('%02d', $metodo['mes_expiracion']) . '/' . substr($metodo['año_expiracion'], -2)); ?></p>
                                    <?php if ($metodo['predeterminado']): ?>
                                        <span class="default-badge"><i class="fas fa-check-circle"></i> Predeterminado</span>
                                    <?php endif; ?>
                                </label>
                                <div class="method-actions">
                                    <?php if (!$metodo['predeterminado']): ?>
                                        <a href="metodo_pago.php?accion=establecer_predeterminado&id=<?php echo htmlspecialchars($metodo['id']); ?>" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-star"></i> Establecer como Predeterminado
                                        </a>
                                    <?php endif; ?>
                                    <a href="metodo_pago.php?accion=eliminar&id=<?php echo htmlspecialchars($metodo['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de que quieres eliminar este método de pago?');">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary large-btn"><i class="fas fa-credit-card"></i> Pagar con Tarjeta Seleccionada</button>
                </form>
            <?php endif; ?>
        </section>

        <section class="add-payment-method-form card">
            <h2>Añadir Nuevo Método de Pago</h2>
            <form action="metodo_pago.php" method="POST">
                <input type="hidden" name="accion" value="agregar_metodo">
                
                <div class="form-group">
                    <label for="tipo_tarjeta">Tipo de Tarjeta:</label>
                    <select id="tipo_tarjeta" name="tipo_tarjeta" required>
                        <option value="">Selecciona un tipo</option>
                        <option value="Visa">Visa</option>
                        <option value="MasterCard">MasterCard</option>
                        <option value="American Express">American Express</option>
                        <option value="Discover">Discover</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="numero_tarjeta">Número de Tarjeta:</label>
                    <input type="text" id="numero_tarjeta" name="numero_tarjeta" 
                            maxlength="19" pattern="[0-9 ]{13,19}" 
                            placeholder="XXXX XXXX XXXX XXXX" required 
                            oninput="this.value = this.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();">
                    <small>No almacenaremos tu número de tarjeta completo por seguridad. Solo los últimos 4 dígitos.</small>
                </div>
                
                <div class="form-group">
                    <label for="fecha_expiracion">Fecha de Expiración (MM/AA):</label>
                    <input type="text" id="fecha_expiracion" name="fecha_expiracion" 
                            maxlength="5" pattern="(0[1-9]|1[0-2])\/?([0-9]{2})" 
                            placeholder="MM/AA" required
                            oninput="if(this.value.length === 2 && !this.value.includes('/')) this.value += '/';">
                    <small>Formato: Mes/Año (ej. 12/25)</small>
                </div>

                <div class="form-group">
                    <label for="cvv">Código de Seguridad (CVV):</label>
                    <input type="text" id="cvv" name="cvv" 
                            maxlength="4" pattern="[0-9]{3,4}" 
                            placeholder="XXX" required>
                    <small>Los 3 o 4 dígitos en la parte trasera de tu tarjeta. NO se almacenará.</small>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="predeterminado" name="predeterminado">
                    <label for="predeterminado">Establecer como método predeterminado</label>
                </div>
                
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Añadir Método de Pago</button>
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