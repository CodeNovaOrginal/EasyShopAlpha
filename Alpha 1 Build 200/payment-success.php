<?php
require_once __DIR__ . '/includes/init.php';
// Get store name
$stmt = $pdo->query("SELECT store_name FROM settings LIMIT 1");
$store_name = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payment Successful - <?php echo htmlspecialchars($store_name); ?></title>
    <link rel="stylesheet" href="/assets/storefront.css">
</head>
<body>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<main class="container auth-main">
    <div class="auth-form-container">
        <h1>Thank You!</h1>
        <p>Your payment was successful and your order has been placed.</p>
        <p>You will receive a confirmation email shortly.</p>
        <a href="/account.php" class="action-button">View My Account</a>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>