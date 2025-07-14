<?php
session_start();
require_once 'db_connection.php';


if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$mensaje = '';
$zapato = null;


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM zapatos WHERE id = ?");
        $stmt->execute([$id]);
        $zapato = $stmt->fetch();

        if (!$zapato) {
            $mensaje = "<p style='color: red;'>Error: Zapato no encontrado.</p>";
        }
    } catch (PDOException $e) {
        $mensaje = "<p style='color: red;'>Error al cargar el zapato: " . $e->getMessage() . "</p>";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $precio = $_POST['precio'] ?? '';
    $imagen = $_POST['imagen'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $talla = $_POST['talla'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    if (empty($id) || empty($nombre) || empty($precio) || !is_numeric($precio)) {
        $mensaje = "<p style='color: red;'>Error: ID, nombre y precio son obligatorios, y el precio debe ser un número.</p>";
       
        $stmt = $pdo->prepare("SELECT * FROM zapatos WHERE id = ?");
        $stmt->execute([$id]);
        $zapato = $stmt->fetch();
    } else {
        try {
       
            $stmt = $pdo->prepare("UPDATE zapatos SET nombre = ?, marca = ?, precio = ?, imagen = ?, categoria = ?, talla = ?, descripcion = ? WHERE id = ?");
            $stmt->execute([$nombre, $marca, $precio, $imagen, $categoria, $talla, $descripcion, $id]);
            
            
            header('Location: index.php?status=updated');
            exit();

        } catch (PDOException $e) {
            $mensaje = "<p style='color: red;'>Error al actualizar el zapato: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Artículo - Your Way's Shoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main class="container">
        <section class="form-container">
            <h2>Modificar Artículo</h2>
            <?php echo $mensaje; ?>
            <?php if ($zapato): ?>
                <form method="POST" action="editar_articulo.php">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($zapato['id']); ?>">
                    
                    <div class="form-group">
                        <label for="nombre">Nombre del Zapato:</label>
                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($zapato['nombre']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="marca">Marca:</label>
                        <input type="text" id="marca" name="marca" value="<?php echo htmlspecialchars($zapato['marca']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="precio">Precio:</label>
                        <input type="number" id="precio" name="precio" step="0.01" value="<?php echo htmlspecialchars($zapato['precio']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="imagen">URL de la Imagen:</label>
                        <input type="text" id="imagen" name="imagen" value="<?php echo htmlspecialchars($zapato['imagen']); ?>
                    </div>

                    <div class="form-group">
                        <label for="talla">Talla:</label>
                        <input type="text" id="talla" name="talla" value="<?php echo htmlspecialchars($zapato['talla']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción:</label>
                        <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($zapato['descripcion']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="categoria">Categoría:</label>
                        <select id="categoria" name="categoria" required>
                            <option value="deportivo" <?php echo ($zapato['categoria'] == 'deportivo') ? 'selected' : ''; ?>>Deportivo</option>
                            <option value="formal" <?php echo ($zapato['categoria'] == 'formal') ? 'selected' : ''; ?>>Formal</option>
                            <option value="casual" <?php echo ($zapato['categoria'] == 'casual') ? 'selected' : ''; ?>>Casual</option>
                            <option value="bota" <?php echo ($zapato['categoria'] == 'bota') ? 'selected' : ''; ?>>Bota</option>
                            <option value="tenis" <?php echo ($zapato['categoria'] == 'tenis') ? 'selected' : ''; ?>>Tenis</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Actualizar Artículo</button>
                    </div>
                </form>
            <?php elseif (empty($mensaje)): ?>
                <p>Por favor, selecciona un artículo desde la página principal para editarlo.</p>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>