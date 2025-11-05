<?php
require_once __DIR__ . '/includes/init.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
    header('Location: /login.php');
    exit();
}

// --- CREATE ORDER IN DATABASE ---
$total_price = 0;
$product_ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($product_ids), '?'));

$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute($product_ids);
$products_from_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products_from_db as $product) {
    $total_price += $product['price'] * $_SESSION['cart'][$product['id']];
}

try {
    $pdo->beginTransaction();

    // Create order record with 'local_pending' status
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'local_pending')");
    $stmt->execute([$_SESSION['customer_id'], $total_price]);
    $order_id = $pdo->lastInsertId();

    // Create order items records
    foreach ($products_from_db as $product) {
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $product['id'], $_SESSION['cart'][$product['id']], $product['price']]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    // You could redirect to an error page here
    die("Could not create order. Error: " . $e->getMessage());
}

// Clear the cart
unset($_SESSION['cart']);

// Redirect to a success page
header('Location: /payment-success.php?method=local');
exit();