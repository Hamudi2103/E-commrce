<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); 
require_once 'db_connection.php'; 

$zapatos = [];

$sql = "SELECT id, nombre, marca, precio, imagen, categoria, talla, descripcion FROM zapatos WHERE 1=1"; 
$params = []; 


if (isset($_GET['search_query']) && !empty(trim($_GET['search_query']))) {
    $search_query = '%' . trim($_GET['search_query']) . '%'; 

    $sql .= " AND (nombre LIKE ? OR marca LIKE ? OR descripcion LIKE ?)";
    $params[] = $search_query;
    $params[] = $search_query;
    $params[] = $search_query;
}


if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category = $_GET['category'];
    $sql .= " AND categoria = ?"; 
    $params[] = $category;
}


if (isset($_GET['size']) && !empty($_GET['size'])) {
    $size = $_GET['size'];
   
    $sql .= " AND talla LIKE ?"; 
    $params[] = '%' . $size . '%'; 
}


$sql .= " ORDER BY id DESC"; 

try {
    
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute($params);
    
    $zapatos = $stmt->fetchAll();
} catch (\PDOException $e) {
    
    echo "<p style='color: red;'>No se pudieron cargar los productos: " . $e->getMessage() . "</p>";
    
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
    <?php include 'header.php';  ?>

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
                                        
                                        if (!empty($zapato['talla'])) {
                                            $tallas_disponibles_zapato = explode(',', $zapato['talla']); 
                                            foreach ($tallas_disponibles_zapato as $talla_individual) {
                                                $talla_individual = trim($talla_individual); 
                                                if (!empty($talla_individual)) {
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