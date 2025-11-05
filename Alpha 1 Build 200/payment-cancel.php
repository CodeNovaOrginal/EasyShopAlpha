<?php
require_once __DIR__ . '/includes/init.php';
// Get store name
$stmt = $pdo->query("SELECT store_name FROM settings LIMIT 1");
$store_name = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payment Cancelled - <?php echo htmlspecialchars($store_name); ?></title>
    <link rel="stylesheet" href="/assets/storefront.css">
</head>
<body>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<main class="container auth-main">
    <div class="auth-form-container">
        <h1>Payment Cancelled</h1>
        <p>Your payment has been cancelled. You have not been charged.</p>
        <p>If you wish to complete your purchase, please return to your cart.</p>
        <a href="/cart.php" class="action-button">Return to Cart</a>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>