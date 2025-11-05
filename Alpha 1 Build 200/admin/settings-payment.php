<?php
define('IS_ADMIN_PANEL', true);
require_once __DIR__ . '/../includes/init.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

$active_theme = get_active_theme($pdo);
$message = '';
$message_type = '';

// Fetch current settings
$stmt = $pdo->query("SELECT payment_method, paypal_enabled, paypal_client_id, paypal_client_secret, paypal_mode FROM settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'];
    $paypal_enabled = isset($_POST['paypal_enabled']) ? 1 : 0;
    $paypal_client_id = trim($_POST['paypal_client_id']);
    $paypal_client_secret = trim($_POST['paypal_client_secret']);
    $paypal_mode = $_POST['paypal_mode'];

    try {
        $sql = "UPDATE settings SET payment_method = ?, paypal_enabled = ?, paypal_client_id = ?, paypal_client_secret = ?, paypal_mode = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$payment_method, $paypal_enabled, $paypal_client_id, $paypal_client_secret, $paypal_mode]);
        $message = "Payment settings updated successfully!";
        $message_type = 'success';
        // Update local variables for the form
        $settings['payment_method'] = $payment_method;
        $settings['paypal_enabled'] = $paypal_enabled;
        $settings['paypal_client_id'] = $paypal_client_id;
        $settings['paypal_client_secret'] = $paypal_client_secret;
        $settings['paypal_mode'] = $paypal_mode;
    } catch (PDOException $e) {
        $message = "Database Error: " . $e->getMessage();
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Settings - EasyShop Admin</title>
    <link rel="stylesheet" href="../themes/<?php echo htmlspecialchars($active_theme); ?>/admin-style.css">
    <style>
        .payment-method-options label {
            font-weight: 600;
            margin-right: 1.5rem;
        }
        .payment-method-options input[type="radio"] {
            margin-right: 0.5rem;
        }
        .payment-provider-settings {
            margin-top: 1.5rem;
            padding: 1.5rem;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
<main class="container">
    <?php require_once __DIR__ . '/../includes/admin-menu.php'; ?>

    <h1>Settings: Payment</h1>

    <?php if ($message): ?>
        <article style="background-color: <?php echo ($message_type === 'success') ? '#eff9ef' : '#fcf0f1'; ?>; border-left: 4px solid <?php echo ($message_type === 'success') ? '#00a32a' : '#d63638'; ?>; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; color:#1d2327;">
            <?php echo htmlspecialchars($message); ?>
        </article>
    <?php endif; ?>

    <form method="post" action="">
        <h3>Payment Method</h3>
        <div class="payment-method-options">
            <label>
                <input type="radio" name="payment_method" value="paypal" <?php echo ($settings['payment_method'] === 'paypal') ? 'checked' : ''; ?> onchange="togglePaymentSettings('paypal')">
                PayPal
            </label>
            <label>
                <input type="radio" name="payment_method" value="local" <?php echo ($settings['payment_method'] === 'local') ? 'checked' : ''; ?> onchange="togglePaymentSettings('local')">
                Local Pay
            </label>
        </div>

        <!-- PayPal Settings -->
        <div id="paypal-settings" class="payment-provider-settings <?php echo ($settings['payment_method'] !== 'paypal') ? 'hidden' : ''; ?>">
            <h4>PayPal Configuration</h4>
            <label>
                <input type="checkbox" name="paypal_enabled" value="1" <?php echo ($settings['paypal_enabled'] == 1) ? 'checked' : ''; ?>>
                Enable PayPal Payments
            </label>

            <label for="paypal_mode">PayPal Mode</label>
            <select name="paypal_mode" id="paypal_mode">
                <option value="sandbox" <?php echo ($settings['paypal_mode'] === 'sandbox') ? 'selected' : ''; ?>>Sandbox (For Testing)</option>
                <option value="live" <?php echo ($settings['paypal_mode'] === 'live') ? 'selected' : ''; ?>>Live (Real Transactions)</option>
            </select>

            <label for="paypal_client_id">PayPal Client ID</label>
            <input type="text" id="paypal_client_id" name="paypal_client_id" value="<?php echo htmlspecialchars($settings['paypal_client_id']); ?>">

            <label for="paypal_client_secret">PayPal Client Secret</label>
            <input type="password" id="paypal_client_secret" name="paypal_client_secret" value="<?php echo htmlspecialchars($settings['paypal_client_secret']); ?>">
        </div>

        <!-- Local Pay Settings -->
        <div id="local-settings" class="payment-provider-settings <?php echo ($settings['payment_method'] !== 'local') ? 'hidden' : ''; ?>">
            <h4>Local Pay Configuration</h4>
            <p>When a customer chooses Local Pay, the order is created in the system with a "Pending" status. You can then arrange for payment through your preferred local method (e.g., bank transfer, cash on delivery).</p>
            <p>No further configuration is needed.</p>
        </div>

        <button type="submit">Save Changes</button>
    </form>
</main>

<script>
    function togglePaymentSettings(method) {
        const paypalDiv = document.getElementById('paypal-settings');
        const localDiv = document.getElementById('local-settings');

        if (method === 'paypal') {
            paypalDiv.classList.remove('hidden');
            localDiv.classList.add('hidden');
        } else {
            paypalDiv.classList.add('hidden');
            localDiv.classList.remove('hidden');
        }
    }
</script>

</body>
</html>