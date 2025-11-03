<?php
// Load the core engine
require_once __DIR__ . '/includes/init.php';

// --- AUTHENTICATION CHECK ---
// If the customer is NOT logged in, redirect them to the login page.
if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
    header('Location: /login.php');
    exit();
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
    <title>My Account - <?php echo htmlspecialchars($store_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/storefront.css">
</head>
<body>
<!-- INCLUDE THE REUSABLE HEADER -->
<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container account-main">
    <h1>My Account</h1>
    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!</p>

    <div class="account-details">
        <h2>Account Details</h2>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($_SESSION['customer_name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['customer_email']); ?></p>
        <p><strong>Customer ID:</strong> #<?php echo (int)$_SESSION['customer_id']; ?></p>
    </div>

    <div class="account-actions">
        <h2>Quick Actions</h2>
        <a href="/orders.php" class="action-button">View My Orders</a>
        <a href="/logout.php" class="action-button secondary">Log Out</a>
    </div>

</main>

<footer class="site-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($store_name); ?>. Powered by EasyShop.</p>
    </div>
</footer>
</body>
</html>