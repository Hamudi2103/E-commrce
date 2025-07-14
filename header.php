    <?php

    ?>
    <header>
        <div class="container">
            <div class="logo">
                <a href="index.php"><img src="your ways shoes.png" alt="Your Way's Shoes Logo" class="site-logo"></a>
            </div>

            <input type="checkbox" id="menu-toggle">
            <label for="menu-toggle" class="menu-icon"><i class="fas fa-bars"></i></label>

            <nav>
                <ul class="menu">
                    <li><a href="index.php"><i class="fas fa-shoe-prints"></i> Zapatos</a></li>
                    <li><a href="carrito.php"><i class="fas fa-shopping-cart"></i> Carrito</a></li>
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <li><a href="mi_perfil.php"><i class="fas fa-user"></i> Mi Perfil</a></li>
                        <li><a href="facturas.php"><i class="fas fa-file-invoice"></i> Mis Ordenes</a></li>
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                            <li><a href="agregar_articulo.php"><i class="fas fa-plus-circle"></i> Agregar Artículo</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a></li>
                        <li><a href="registro.php"><i class="fas fa-user-plus"></i> Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>

            <div class="header-search-filter-area">
                <form action="index.php" method="GET" class="search-filter-form">
                    <div class="search-bar">
                        <input type="text" name="search_query" placeholder="Buscar zapatos..." value="<?php echo htmlspecialchars($_GET['search_query'] ?? ''); ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>

                    <div class="filters">
                        <select id="category-filter" name="category">
                            <option value="">Categoría</option>
                            <option value="Deportivo" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Deportivo') ? 'selected' : ''; ?>>Deportivo</option>
                            <option value="Formal" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Formal') ? 'selected' : ''; ?>>Formal</option>
                            <option value="Casual" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Casual') ? 'selected' : ''; ?>>Casual</option>
                        </select>
                        
                        <select id="size-filter" name="size">
                            <option value="">Talla</option>
                            <?php
                            
                            $tallas_disponibles = ['36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];
                            foreach ($tallas_disponibles as $talla_opcion) {
                                $selected = (isset($_GET['size']) && $_GET['size'] == $talla_opcion) ? 'selected' : '';
                                echo "<option value=\"{$talla_opcion}\" {$selected}>{$talla_opcion}</option>";
                            }
                            ?>
                        </select>
                        </div>
                </form>
            </div>
        </div>
    </header>