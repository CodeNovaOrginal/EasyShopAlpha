<?php
// Load the core engine
require_once __DIR__ . '/includes/init.php';

// --- HANDLE CART ACTIONS (UPDATE/REMOVE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        // Update quantities
        foreach ($_POST['quantities'] as $product_id => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id] = $quantity;
            } else {
                // If quantity is 0 or less, remove the item
                unset($_SESSION['cart'][$product_id]);
            }
        }
    } elseif (isset($_POST['remove_from_cart'])) {
        // Remove a single item
        $product_id_to_remove = $_POST['product_id'];
        unset($_SESSION['cart'][$product_id_to_remove]);
    }
    // Redirect to the same page to prevent form resubmission
    header('Location: cart.php');
    exit();
}

// --- FETCH CART DATA ---
$cart_products = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    // Create a string of placeholders for the IN clause
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));

    // Fetch all product details for items in the cart
    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products_from_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Combine cart quantities with product details
    foreach ($products_from_db as $product) {
        $product_id = $product['id'];
        $cart_products[$product_id] = $product;
        $cart_products[$product_id]['quantity'] = $_SESSION['cart'][$product_id];
        $cart_products[$product_id]['subtotal'] = $product['price'] * $_SESSION['cart'][$product_id];
        $total_price += $cart_products[$product_id]['subtotal'];
    }
}

// Get store name for the page title
try {
    $stmt = $pdo->query("SELECT store_name FROM settings LIMIT 1");
    $store_name = $stmt->fetchColumn();
} catch (PDOException $e) {
    $store_name = "EasyShop";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart - <?php echo htmlspecialchars($store_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/storefront.css">
</head>
<body>
<!-- INCLUDE THE REUSABLE HEADER -->
<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container cart-main">
    <h1>Shopping Cart</h1>

    <?php if (empty($cart_products)): ?>
        <div class="empty-cart">
            <p>Your cart is currently empty.</p>
            <a href="/" class="action-button">Continue Shopping</a>
        </div>
    <?php else: ?>
        <form method="post" action="cart.php">
            <table class="cart-table">
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($cart_products as $product): ?>
                    <tr>
                        <td class="cart-product-info">
                            <img src="/uploads/<?php echo htmlspecialchars($product['image'] ?? 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="cart-product-image">
                            <span><?php echo htmlspecialchars($product['name']); ?></span>
                        </td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td>
                            <input type="number" name="quantities[<?php echo $product['id']; ?>]" value="<?php echo $product['quantity']; ?>" min="1" class="cart-quantity-input">
                        </td>
                        <td>$<?php echo number_format($product['subtotal'], 2); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="remove_from_cart" class="remove-button">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <div class="cart-totals">
                    <h3>Cart Totals</h3>
                    <p><strong>Total:</strong> $<?php echo number_format($total_price, 2); ?></p>
                </div>
                <div class="cart-actions">
                    <button type="submit" name="update_cart" class="action-button secondary">Update Cart</button>
                    <a href="/checkout.php" class="action-button">Proceed to Checkout</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</main>

<footer class="site-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($store_name); ?>. Powered by EasyShop.</p>
    </div>
</footer>
</body>
</html>