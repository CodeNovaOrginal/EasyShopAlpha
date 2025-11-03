<?php
define('IS_ADMIN_PANEL', true);

// Load the core engine
require_once __DIR__ . '/../includes/init.php';

// Get the active theme from the database
$active_theme = get_active_theme($pdo);

// --- AUTHENTICATION CHECK ---
// If the admin is not logged in, redirect them to the login page.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// --- LOGIC ---
// Fetch all products from the database
try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If there's an error, we can show a message or just have an empty list
    $products = [];
    $error_message = "Could not fetch products: " . $e->getMessage();
}

// --- VIEW ---
// This is the HTML part of the page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products - EasyShop Admin</title>
    <!-- DYNAMIC THEME LOADING -->
    <link rel="stylesheet" href="../themes/<?php echo htmlspecialchars($active_theme); ?>/admin-style.css">
</head>
<body>
<main class="container">
    <nav>
        <ul>
            <li><strong>EasyShop <span class="alpha-tag">Alpha</span></strong></li>
        </ul>
        <ul>
            <li><a href="index.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'aria-current="page"' : ''; ?>>Dashboard</a></li>
            <li><a href="products.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'aria-current="page"' : ''; ?>>Products</a></li>
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

    <h1>Manage Products</h1>

    <?php
    // Display a success message if it exists (e.g., after a product is created or updated)
    if (isset($_SESSION['message'])) {
        echo '<article style="background-color: #eff9ef; border-left: 4px solid #00a32a; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; color:#1d2327;">';
        echo htmlspecialchars($_SESSION['message']);
        echo '</article>';
        // Unset the message so it doesn't show again on refresh
        unset($_SESSION['message']);
    }
    ?>

    <?php if (isset($error_message)): ?>
        <article style="background-color: #fcf0f1; border-left: 4px solid #d63638; padding: 1rem; margin-bottom: 1rem; border-radius: 4px;">
            <strong>Error:</strong> <?php echo $error_message; ?>
        </article>
    <?php endif; ?>

    <p>
        <a href="product-edit.php" role="button" class="primary">Add New Product</a>
    </p>

    <table>
        <thead>
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
            <tr>
                <td colspan="4" style="text-align: center;">No products found. <a href="product-edit.php">Add one now!</a></td>
            </tr>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>$<?php echo number_format($product['price'], 2); ?></td>
                    <td><?php echo (int)$product['stock']; ?></td>
                    <td>
                        <a href="product-edit.php?id=<?php echo $product['id']; ?>" role="button" class="secondary">Edit</a>
                        <a href="product-delete.php?id=<?php echo $product['id']; ?>" role="button" class="contrast" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</main>
</body>
</html>