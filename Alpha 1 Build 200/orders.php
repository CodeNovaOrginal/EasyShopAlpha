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

// --- FETCH CUSTOMER'S ORDERS ---
$orders = [];
try {
    // Prepare a statement to fetch orders for the logged-in user only
    $stmt = $pdo->prepare("SELECT id, order_date, total_amount, status FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->execute([$_SESSION['customer_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If there's an error, we can show a message or just have an empty list
    error_log("Could not fetch customer orders: " . $e->getMessage());
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - <?php echo htmlspecialchars($store_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/storefront.css">
    <style>
        /* Custom styles for the orders page */
        .orders-main {
            padding: 2rem 0;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .orders-table th, .orders-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .orders-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        .orders-table tbody tr:last-child td {
            border-bottom: none;
        }
        .order-id-link {
            font-weight: 600;
            color: #007bff;
            text-decoration: none;
        }
        .order-id-link:hover {
            text-decoration: underline;
        }
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-shipped { background-color: #d4edda; color: #155724; }
        .status-delivered { background-color: #d1ecf1; color: #0c5460; }
        .status-local_pending { background-color: #f8d7da; color: #721c24; }
        .status-cancelled { background-color: #e2e3e5; color: #383d41; }

        @media (max-width: 768px) {
            .orders-table {
                font-size: 0.9rem;
            }
            .orders-table th, .orders-table td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>
<body>
<!-- INCLUDE THE REUSABLE HEADER -->
<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container orders-main">
    <h1>My Orders</h1>
    <p>View the status of your recent orders.</p>

    <?php if (empty($orders)): ?>
        <div class="empty-cart">
            <p>You haven't placed any orders yet.</p>
            <a href="/" class="action-button">Start Shopping</a>
        </div>
    <?php else: ?>
        <table class="orders-table">
            <thead>
            <tr>
                <th>Order ID</th>
                <th>Date</th>
                <th>Total Amount</th>
                <th>Status</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td>
                        <!-- Link to a (future) public order details page -->
                        <a href="/order-details.php?id=<?php echo $order['id']; ?>" class="order-id-link">
                            #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>
                        </a>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td>
                            <span class="status-badge status-<?php echo str_replace('_', '-', $order['status']); ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $order['status']))); ?>
                            </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p style="margin-top: 2rem;">
        <a href="/account.php" class="action-button secondary">&laquo; Back to My Account</a>
    </p>
</main>

<!-- INCLUDE THE FOOTER -->
<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>
</html>