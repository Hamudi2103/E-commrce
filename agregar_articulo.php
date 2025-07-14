<?php
session_start(); 


if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once 'db_connection.php'; 

$mensaje = ''; 


$nombre_zapato = '';
$marca_zapato = '';
$precio_zapato = '';
$imagen_zapato = '';
$categoria_zapato = '';
$talla_zapato = '';
$descripcion_zapato = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $nombre_zapato = $_POST['nombre'] ?? '';
    $marca_zapato = $_POST['marca'] ?? '';
    $precio_zapato = filter_var($_POST['precio'] ?? '', FILTER_VALIDATE_FLOAT); 
    $imagen_zapato = $_POST['imagen'] ?? '';
    $categoria_zapato = $_POST['categoria'] ?? '';
    $talla_zapato = $_POST['talla'] ?? '';
    $descripcion_zapato = $_POST['descripcion'] ?? '';

    
    if (empty($nombre_zapato) || empty($marca_zapato) || $precio_zapato === false || empty($imagen_zapato) || empty($categoria_zapato) || empty($talla_zapato) || empty($descripcion_zapato)) {
        $mensaje = "<p style='color: red;'>Todos los campos son obligatorios y el precio debe ser un número válido.</p>";
    } else {
        try {
           
            $stmt = $pdo->prepare(
                "INSERT INTO zapatos (nombre, marca, precio, imagen, categoria, talla, descripcion) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

           
            $stmt->execute([
                $nombre_zapato,
                $marca_zapato,
                $precio_zapato,
                $imagen_zapato,
                $categoria_zapato,
                $talla_zapato,
                $descripcion_zapato
            ]);

            $mensaje = "<p style='color: green;'>¡Zapato agregado exitosamente!</p>";

            
            $nombre_zapato = '';
            $marca_zapato = '';
            $precio_zapato = '';
            $imagen_zapato = '';
            $categoria_zapato = '';
            $talla_zapato = '';
            $descripcion_zapato = '';

        } catch (PDOException $e) {
            
            $mensaje = "<p style='color: red;'>Error al agregar el zapato: " . $e->getMessage() . "</p>";
            
            error_log("Error al agregar zapato: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nuevo Artículo - Your Way's Shoes</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php';  ?>

    <main class="container">
        <h1>Agregar Nuevo Zapato</h1>

        <section class="form-container card">
            <?php echo $mensaje;  ?>

            <form method="POST" action="agregar_articulo.php">
                <div class="form-group">
                    <label for="nombre">Nombre del Zapato:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre_zapato); ?>" required>
                </div>
                <div class="form-group">
                    <label for="marca">Marca:</label>
                    <input type="text" id="marca" name="marca" value="<?php echo htmlspecialchars($marca_zapato); ?>" required>
                </div>
                <div class="form-group">
                    <label for="precio">Precio:</label>
                    <input type="number" id="precio" name="precio" step="0.01" value="<?php echo htmlspecialchars($precio_zapato); ?>" required>
                </div>
                <div class="form-group">
                    <label for="imagen">URL de la Imagen:</label>
                    <input type="url" id="imagen" name="imagen" value="<?php echo htmlspecialchars($imagen_zapato); ?>" placeholder="http://ejemplo.com/imagen.jpg" required>
                </div>
                <div class="form-group">
                    <label for="categoria">Categoría:</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Selecciona una categoría</option>
                        <option value="deportivo" <?php echo ($categoria_zapato == 'deportivo') ? 'selected' : ''; ?>>Deportivo</option>
                        <option value="formal" <?php echo ($categoria_zapato == 'formal') ? 'selected' : ''; ?>>Formal</option>
                        <option value="casual" <?php echo ($categoria_zapato == 'casual') ? 'selected' : ''; ?>>Casual</option>
                        <option value="bota" <?php echo ($categoria_zapato == 'bota') ? 'selected' : ''; ?>>Bota</option>
                        <option value="tenis" <?php echo ($categoria_zapato == 'tenis') ? 'selected' : ''; ?>>Tenis</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="talla">Talla:</label>
                    <input type="text" id="talla" name="talla" placeholder="Ej: 38, 39, 40 o S, M, L" value="<?php echo htmlspecialchars($talla_zapato); ?>" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" required><?php echo htmlspecialchars($descripcion_zapato); ?></textarea>
                </div>
                <button type="submit">Agregar Zapato</button>
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