<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Inicia la sesión al principio del archivo
require_once 'db_connection.php'; // Asegúrate de que esta ruta sea correcta para tu conexión a la base de datos

$zapatos = [];
// Consulta SQL base con una cláusula WHERE 1=1 para facilitar la adición de condiciones
$sql = "SELECT id, nombre, marca, precio, imagen, categoria, talla, descripcion FROM zapatos WHERE 1=1"; 
$params = []; // Array para los parámetros de la consulta preparada, crucial para la seguridad (PDO prepared statements)

// --- Procesar la búsqueda por texto (search_query) ---
if (isset($_GET['search_query']) && !empty(trim($_GET['search_query']))) {
    $search_query = '%' . trim($_GET['search_query']) . '%'; // Para búsquedas parciales (LIKE %texto%)
    // Añadir condiciones para buscar el término en nombre, marca o descripción
    $sql .= " AND (nombre LIKE ? OR marca LIKE ? OR descripcion LIKE ?)";
    $params[] = $search_query;
    $params[] = $search_query;
    $params[] = $search_query;
}

// --- Procesar el filtro de categoría ---
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category = $_GET['category'];
    $sql .= " AND categoria = ?"; // Filtrar por categoría exacta
    $params[] = $category;
}

// --- Procesar el filtro de talla ---
if (isset($_GET['size']) && !empty($_GET['size'])) {
    $size = $_GET['size'];
    // Usamos LIKE para buscar la talla exacta dentro del campo 'talla'.
    // Esto es útil si tu campo 'talla' en la DB contiene múltiples valores separados por coma (ej. "38,39,40")
    // o si contiene un solo valor (ej. "38").
    $sql .= " AND talla LIKE ?"; 
    $params[] = '%' . $size . '%'; // Buscar la talla como parte de la cadena
}

// Añadir ordenamiento al final de la consulta (los más nuevos primero)
$sql .= " ORDER BY id DESC"; 

try {
    // Preparar la consulta SQL con los parámetros dinámicos
    $stmt = $pdo->prepare($sql);
    // Ejecutar la consulta pasando los parámetros de forma segura (prevención de inyección SQL)
    $stmt->execute($params);
    // Obtener todos los resultados como un array de objetos o arrays asociativos
    $zapatos = $stmt->fetchAll();
} catch (\PDOException $e) {
    // Mostrar un mensaje de error amigable al usuario si la base de datos falla
    echo "<p style='color: red;'>No se pudieron cargar los productos: " . $e->getMessage() . "</p>";
    // Registrar el error detallado en los logs del servidor para depuración
    error_log("Error al cargar productos con filtros en index.php: " . $e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Way's Shoes - Nuestra Colección</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; // Incluye el header con la barra de búsqueda y filtros funcionales ?>

    <main class="container">
        <h2>Nuestra Colección</h2>
        <?php if (empty($zapatos)): ?>
            <p>No hay zapatos disponibles que coincidan con tus criterios de búsqueda/filtro.</p>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach ($zapatos as $zapato): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($zapato['imagen']); ?>" alt="<?php echo htmlspecialchars($zapato['nombre']); ?>">
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($zapato['nombre']); ?></h3>
                            <p class="marca"><?php echo htmlspecialchars($zapato['marca']); ?></p>
                            <p class="categoria"><?php echo htmlspecialchars($zapato['categoria']); ?></p>
                            <p class="talla">Talla(s) disponibles: <?php echo htmlspecialchars($zapato['talla']); ?></p>
                            <p class="descripcion"><?php echo htmlspecialchars(substr($zapato['descripcion'], 0, 100)); ?>...</p>
                            <p class="price">$<?php echo number_format($zapato['precio'], 2); ?></p>
                        </div>
                        <div class="product-actions">
                            <form action="agregar_al_carrito.php" method="POST" class="add-to-cart-form">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($zapato['id']); ?>">
                                
                                <div class="form-group-inline"> <label for="cantidad_<?php echo $zapato['id']; ?>">Cant:</label>
                                    <input type="number" id="cantidad_<?php echo $zapato['id']; ?>" name="cantidad" value="1" min="1" class="quantity-input">
                                </div>
                                
                                <div class="form-group"> <label for="talla_<?php echo $zapato['id']; ?>">Talla a comprar:</label>
                                    <select id="talla_<?php echo $zapato['id']; ?>" name="talla" class="size-select" required>
                                        <option value="">Selecciona una talla</option>
                                        <?php
                                        // Generar opciones de talla dinámicamente si el campo 'talla' contiene múltiples tallas separadas por comas
                                        if (!empty($zapato['talla'])) {
                                            $tallas_disponibles_zapato = explode(',', $zapato['talla']); // Divide la cadena "36,37,38" en un array
                                            foreach ($tallas_disponibles_zapato as $talla_individual) {
                                                $talla_individual = trim($talla_individual); // Elimina espacios en blanco
                                                if (!empty($talla_individual)) { // Asegura que no se añadan opciones vacías
                                                    echo '<option value="' . htmlspecialchars($talla_individual) . '">' . htmlspecialchars($talla_individual) . '</option>';
                                                }
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn add-to-cart-btn"><i class="fas fa-cart-plus"></i> Añadir al Carrito</button>
                            </form>
                            
                            <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                                <a href="editar_articulo.php?id=<?php echo htmlspecialchars($zapato['id']); ?>" class="btn edit-btn"><i class="fas fa-edit"></i> Editar</a>
                                
                                <form action="eliminar_articulo.php" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este artículo?');" style="display: inline-block;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($zapato['id']); ?>">
                                    <button type="submit" class="btn delete-btn"><i class="fas fa-trash-alt"></i> Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Your Way's Shoes. Tu Estilo, Tus Pasos.</p>
        </div>
    </footer>
</body>
</html>