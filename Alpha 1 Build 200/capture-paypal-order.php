<?php
require_once __DIR__ . '/includes/init.php';

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);
$paypal_order_id = $data['orderID'];

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

$isSandbox = ($settings['paypal_mode'] === 'sandbox');
try {
    $accessToken = getPayPalAccessToken($settings['paypal_client_id'], $settings['paypal_client_secret'], $isSandbox);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Could not get PayPal access token.']);
    exit();
}

// --- Capture Order with PayPal using cURL ---
$paypal_url = $isSandbox ? 'https://api-m.sandbox.paypal.com/v2/checkout/orders/' . $paypal_order_id . '/capture' : 'https://api-m.paypal.com/v2/checkout/orders/' . $paypal_order_id . '/capture';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $paypal_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $accessToken
]);
curl_setopt($ch, CURLOPT_POST, true); // This is a POST request to capture
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For development, set to true in production

$response = curl_exec($ch);
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'PayPal API request failed.']);
    exit();
}
curl_close($ch);

$capture_data = json_decode($response, true);

if ($capture_data['status'] === 'COMPLETED') {
    // Payment successful! Update our database
    $transaction_id = $capture_data['purchase_units'][0]['payments']['captures'][0]['id'];

    // Find our internal order ID using the PayPal order ID
    $stmt = $pdo->prepare("SELECT order_id FROM payments WHERE transaction_id = ?");
    $stmt->execute([$paypal_order_id]);
    $payment_record = $stmt->fetch(PDO::FETCH_ASSOC);
    $internal_order_id = $payment_record['order_id'];

    // Update payment and order status
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE payments SET status = 'completed', transaction_id = ? WHERE transaction_id = ?");
    $stmt->execute([$transaction_id, $paypal_order_id]);

    $stmt = $pdo->prepare("UPDATE orders SET status = 'processing' WHERE id = ?");
    $stmt->execute([$internal_order_id]);
    $pdo->commit();

    // Clear the cart
    unset($_SESSION['cart']);
}

echo $response; // Send back the full response from PayPal