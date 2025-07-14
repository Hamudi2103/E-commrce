<?php

?>
<header>
    <div class="container">
        <div class="logo">
            <a href="index.php"><img src="Your Ways Shoes.png" alt="Your Way's Shoes Logo" class="site-logo"></a>
        </div>

        <input type="checkbox" id="menu-toggle">
        <label for="menu-toggle" class="menu-icon"><i class="fas fa-bars"></i></label>

        <nav>
            <ul class="menu">
                <?php if (!isset($_SESSION['usuario_id'])):  ?>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a></li>
                    <li><a href="registro.php"><i class="fas fa-user-plus"></i> Registrarse</a></li>
                <?php else:  ?>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        </div>
</header>