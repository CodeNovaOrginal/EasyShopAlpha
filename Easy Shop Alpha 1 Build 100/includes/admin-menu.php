<?php
// This file expects $active_theme and $logged_in to be available.
// It also now expects a $current_page variable to highlight the correct sub-menu item.
?>
<nav>
    <ul>
        <li><strong>EasyShop <span class="alpha-tag">Alpha</span></strong></li>
    </ul>
    <ul>
        <li><a href="index.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'aria-current="page"' : ''; ?>>Dashboard</a></li>
        <li><a href="products.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'aria-current="page"' : ''; ?>>Products</a></li>

        <!-- NEW DROPDOWN MENU FOR SETTINGS -->
        <li class="dropdown">
            <a href="#" class="dropdown-toggle">Settings</a>
            <ul class="dropdown-menu">
                <li><a href="settings-store.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'settings-store.php') ? 'aria-current="page"' : ''; ?>>Store Info</a></li>
                <li><a href="settings-personalize.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'settings-personalize.php') ? 'aria-current="page"' : ''; ?>>Personalize</a></li>
            </ul>
        </li>

        <li><a href="about.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'aria-current="page"' : ''; ?>>About</a></li>
        <li><a href="?logout">Log Out</a></li>
    </ul>
</nav>