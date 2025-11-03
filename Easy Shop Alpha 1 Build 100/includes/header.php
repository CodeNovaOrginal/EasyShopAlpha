<?php
// This file expects a $store_name variable to be available in the scope it's called from.

// Calculate the number of unique items in the cart
$cart_item_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<header class="site-header">
    <div class="container">
        <h1 class="site-title"><?php echo htmlspecialchars($store_name ?? 'EasyShop'); ?></h1>
        <nav>
            <a href="/">Home</a>
            <!-- CUSTOMER NAVIGATION -->
            <?php if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'] === true): ?>
                <a href="/account.php">My Account</a>
                <a href="/logout.php">Log Out</a>
            <?php else: ?>
                <a href="/login.php">Log In</a>
                <a href="/register.php">Register</a>
            <?php endif; ?>
            <a href="/cart.php" class="cart-link">Cart (<?php echo $cart_item_count; ?>)</a>
        </nav>
    </div>
</header>