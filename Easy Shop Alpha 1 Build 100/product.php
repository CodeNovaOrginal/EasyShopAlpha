<?php
// Load the core engine. This will now handle errors gracefully.
require_once __DIR__ . '/includes/init.php';

// --- LOGIC ---
$product = null;
$message = '';

// Check if an 'id' is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch the specific product from the database
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // If the query fails, the error handler will redirect to error.php.
        $product = null;
    }
}

// Handle "Add to Cart" form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = (int)$_POST['quantity'];
    if ($product && $quantity > 0) {
        // If the cart doesn't exist, create it
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // If the product is already in the cart, just update the quantity
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            // Otherwise, add it to the cart
            $_SESSION['cart'][$product_id] = $quantity;
        }
        $message = "Product added to your cart!";
    } else {
        $message = "Please select a valid quantity.";
    }
}

// Get store name from settings for the page title
try {
    $stmt = $pdo->query("SELECT store_name FROM settings LIMIT 1");
    $store_name = $stmt->fetchColumn();
} catch (PDOException $e) {
    $store_name = "EasyShop"; // Fallback name
}

// --- APPLICATION-LEVEL 404 CHECK ---
// If no product was found with the given ID, show our 404 page.
if (!$product) {
    http_response_code(404); // Send the correct 404 header
    require __DIR__ . '/404.php'; // Include the 404 page content
    exit(); // Stop the rest of the script from running
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?> - <?php echo htmlspecialchars($store_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/storefront.css">
</head>
<body>
<!-- INCLUDE THE REUSABLE HEADER -->
<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container product-page-main">
    <!-- DISPLAY A MESSAGE IF ONE EXISTS -->
    <?php if ($message): ?>
        <article class="user-message"><?php echo htmlspecialchars($message); ?></article>
    <?php endif; ?>

    <div class="product-details-container">
        <div class="product-image-container">
            <img src="/uploads/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-detail-image">
        </div>
        <div class="product-info-container">
            <h1 class="product-detail-name"><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="product-detail-price">$<?php echo number_format($product['price'], 2); ?></p>
            <p class="product-stock">In Stock: <?php echo (int)$product['stock']; ?></p>

            <div class="product-description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <form method="post" class="add-to-cart-form">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo (int)$product['stock']; ?>" required>
                <button type="submit" name="add_to_cart" class="add-to-cart-button">Add to Cart</button>
            </form>
        </div>
    </div>
</main>

<!-- INCLUDE THE FOOTER -->
<footer class="site-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($store_name); ?>. Powered by EasyShop.</p>
    </div>
</footer>
</body>
</html>