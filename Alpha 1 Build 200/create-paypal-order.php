<?php
require_once __DIR__ . '/includes/init.php';

// This file is accessed via AJAX, so we check for the session
if (!isset($_SESSION['customer_logged_in']) || $_SESSION['customer_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in.']);
    exit();
}

// --- Get PayPal Access Token ---
function getPayPalAccessToken($clientId, $clientSecret, $isSandbox) {
    $url = $isSandbox ? 'https://api-m.sandbox.paypal.com/v1/oauth2/token' : 'https://api-m.paypal.com/v1/oauth2/token';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $clientId . ":" . $clientSecret);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    curl_close($ch);
    $json = json_decode($result);
    return $json->access_token;
}

// Get settings
$settings_stmt = $pdo->query("SELECT paypal_client_id, paypal_client_secret, paypal_mode FROM settings LIMIT 1");
$settings = $settings_stmt->fetch(PDO::FETCH_ASSOC);

if (empty($settings['paypal_client_id']) || empty($settings['paypal_client_secret'])) {
    http_response_code(500);
    echo json_encode(['error' => 'PayPal is not configured correctly.']);
    exit();
}

$isSandbox = ($settings['paypal_mode'] === 'sandbox');
try {
    $accessToken = getPayPalAccessToken($settings['paypal_client_id'], $settings['paypal_client_secret'], $isSandbox);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not get PayPal access token.']);
    exit();
}

// --- Create Order in our Database FIRST ---
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

    // Create order record
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
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
    http_response_code(500);
    echo json_encode(['error' => 'Could not create order in database.']);
    exit();
}

// --- Create Order with PayPal using cURL ---
$paypal_url = $isSandbox ? 'https://api-m.sandbox.paypal.com/v2/checkout/orders' : 'https://api-m.paypal.com/v2/checkout/orders';

$payload = json_encode([
    'intent' => 'CAPTURE',
    'purchase_units' => [[
        'reference_id' => strval($order_id),
        'description' => 'Order from ' . $_SESSION['customer_email'],
        'amount' => [
            'currency_code' => 'USD',
            'value' => (string)number_format($total_price, 2, '.', '')
        ]
    ]]
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $paypal_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development, set to true in production

$response = curl_exec($ch);
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'PayPal API request failed.']);
    exit();
}
curl_close($ch);

$paypal_order = json_decode($response, true);

if (isset($paypal_order['id'])) {
    // Store PayPal Order ID in our payments table
    $paypal_order_id = $paypal_order['id'];
    $stmt = $pdo->prepare("INSERT INTO payments (order_id, transaction_id, amount, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$order_id, $paypal_order_id, $total_price]);

    echo json_encode(['id' => $paypal_order['id']]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create PayPal order.', 'details' => $paypal_order]);
}