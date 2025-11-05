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

// --- HANDLE STATUS UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    // Basic validation
    $allowed_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (is_numeric($order_id) && in_array($new_status, $allowed_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            $_SESSION['message'] = "Order status updated successfully!";
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error updating order status: " . $e->getMessage();
        }
    } else {
        $_SESSION['message'] = "Invalid request.";
    }
    // Redirect to prevent form resubmission
    header('Location: orders.php');
    exit();
}

// --- FETCH ORDERS FROM DATABASE ---
$orders = [];
try {
    // Join orders with users to get customer name
    $sql = "
        SELECT o.id, o.user_id, o.order_date, o.total_amount, o.status, u.name AS customer_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC
    ";
    $stmt = $pdo->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Could not fetch orders: " . $e->getMessage());
    // You could set a generic error message here if needed
}

// --- HELPER FUNCTION FOR STATUS STYLING ---
function getStatusClass($status) {
    switch ($status) {
        case 'pending':
            return 'status-pending';
        case 'processing':
            return 'status-processing';
        case 'shipped':
            return 'status-shipped';
        case 'delivered':
            return 'status-delivered';
        case 'cancelled':
            return 'status-cancelled';
        default:
            return '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders - EasyShop Admin</title>
    <link rel="stylesheet" href="../themes/<?php echo htmlspecialchars($active_theme); ?>/admin-style.css">
    <style>
        /* Add some color-coding for order statuses */
        .status-pending { color: #b32d2e; font-weight: bold; }
        .status-processing { color: #d63638; font-weight: bold; }
        .status-shipped { color: #2271b1; font-weight: bold; }
        .status-delivered { color: #00a32a; font-weight: bold; }
        .status-cancelled { color: #50575e; text-decoration: line-through; }

        .order-status-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .order-status-form select {
            width: auto;
            padding: 0.25rem 0.5rem;
        }
        .order-status-form button {
            padding: 0.25rem 0.75rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<main class="container">
    <?php require_once __DIR__ . '/../includes/admin-menu.php'; ?>

    <h1>Manage Orders</h1>

    <?php
    // Display success message from session
    if (isset($_SESSION['message'])) {
        echo '<article style="background-color: #eff9ef; border-left: 4px solid #00a32a; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; color:#1d2327;">';
        echo htmlspecialchars($_SESSION['message']);
        echo '</article>';
        unset($_SESSION['message']);
    }
    ?>

    <?php if (empty($orders)): ?>
        <div class="welcome-message">
            <h2>No Orders Found</h2>
            <p>There are no orders in the system yet.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><a href="order-details.php?id=<?php echo $order['id']; ?>">#<?php echo $order['id']; ?></a></td>
                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                    <td><?php echo date('M j, Y, g:i a', strtotime($order['order_date'])); ?></td>
                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td>
                        <form method="post" action="orders.php" class="order-status-form">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status" required>
                                <option value="pending" <?php echo ($order['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo ($order['status'] === 'processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo ($order['status'] === 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo ($order['status'] === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo ($order['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="secondary">Update</button>
                        </form>
                    </td>
                    <td>
                        <a href="order-details.php?id=<?php echo $order['id']; ?>" role="button" class="secondary">View Details</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
</body>
</html>