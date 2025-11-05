<?php
// Load the core engine
require_once __DIR__ . '/includes/init.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
    $_SESSION['redirect_after_login'] = '/checkout.php';
    header('Location: /login.php');
    exit();
}

// --- LOGIC ---
// Redirect to cart if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: /cart.php');
    exit();
}

// Get store settings for payment
$settings_stmt = $pdo->query("SELECT payment_method, paypal_enabled, paypal_client_id, paypal_client_secret, paypal_mode FROM settings LIMIT 1");
$settings = $settings_stmt->fetch(PDO::FETCH_ASSOC);

// Check if a payment method is available
if (empty($settings['payment_method'])) {
    die("No payment method is configured. Please contact the store administrator.");
}

// Fetch cart data
$cart_products = [];
$total_price = 0;
$product_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));

$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products_from_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products_from_db as $product) {
    $product_id = $product['id'];
    $cart_products[$product_id] = $product;
    $cart_products[$product_id]['quantity'] = $_SESSION['cart'][$product_id];
    $cart_products[$product_id]['subtotal'] = $product['price'] * $_SESSION['cart'][$product_id];
    $total_price += $cart_products[$product_id]['subtotal'];
}

// Get store name for the page title
$stmt = $pdo->query("SELECT store_name FROM settings LIMIT 1");
$store_name = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout - <?php echo htmlspecialchars($store_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/storefront.css">
    <!-- Only include PayPal JS if it's the chosen method -->
    <?php if ($settings['payment_method'] === 'paypal'): ?>
        <script src="https://www.paypal.com/sdk/js?client-id=<?php echo htmlspecialchars($settings['paypal_client_id']); ?>&currency=USD"></script>
    <?php endif; ?>
</head>
<body>
<!-- INCLUDE THE REUSABLE HEADER -->
<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container">
    <h1>Checkout</h1>

    <div class="checkout-container">
        <div class="checkout-summary">
            <h2>Order Summary</h2>
            <table class="cart-table">
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($cart_products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['quantity']; ?></td>
                        <td>$<?php echo number_format($product['subtotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="cart-totals">
                <h3>Total: $<?php echo number_format($total_price, 2); ?></h3>
            </div>
        </div>
        <div class="checkout-payment">
            <h2>Payment</h2>

            <?php if ($settings['payment_method'] === 'paypal'): ?>
                <?php if ($settings['paypal_enabled'] && !empty($settings['paypal_client_id'])): ?>
                    <p>You will be redirected to PayPal to complete your purchase.</p>
                    <div id="paypal-button-container"></div>
                <?php else: ?>
                    <p>PayPal is not currently available. Please choose another payment method or contact us.</p>
                <?php endif; ?>

            <?php elseif ($settings['payment_method'] === 'local'): ?>
                <p>You have chosen to pay locally. Your order will be created, and you can arrange payment with us directly.</p>
                <form method="post" action="/create-local-order.php">
                    <button type="submit" class="add-to-cart-button">Confirm Order</button>
                </form>

            <?php else: ?>
                <p>No payment method is available. Please contact the store administrator.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php if ($settings['payment_method'] === 'paypal'): ?>
    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                return fetch('/create-paypal-order.php', { method: 'post' }).then(function(res) { return res.json(); }).then(function(orderData) { return orderData.id; });
            },
            onApprove: function(data, actions) {
                return fetch('/capture-paypal-order.php', { method: 'post', headers: { 'content-type': 'application/json' }, body: JSON.stringify({ orderID: data.orderID }) }).then(function(res) { return res.json(); }).then(function(orderData) {
                    window.location.href = '/payment-success.php';
                });
            }
        }).render('#paypal-button-container');
    </script>
<?php endif; ?>

</body>
</html>