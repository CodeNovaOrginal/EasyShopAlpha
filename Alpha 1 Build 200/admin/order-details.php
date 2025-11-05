<?php
define('IS_ADMIN_PANEL', true);
require_once __DIR__ . '/../includes/init.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

$active_theme = get_active_theme($pdo);
$order = null;
$order_items = [];
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
    // Redirect to the same page to prevent form resubmission
    header('Location: order-details.php?id=' . $order_id);
    exit();
}

// --- FETCH ORDER DATA ---
// Check if an 'id' is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $order_id = $_GET['id'];

    // Fetch the main order details along with the customer's name and email
    try {
        $stmt = $pdo->prepare("
            SELECT o.id, o.user_id, o.order_date, o.total_amount, o.status, u.name, u.email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        // If no order is found, redirect back to the orders list
        if (!$order) {
            $_SESSION['message'] = "Order not found.";
            header('Location: orders.php');
            exit();
        }

        // Fetch the individual items for this order
        $stmt = $pdo->prepare("
            SELECT oi.quantity, oi.price, p.name, p.image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        die("Error fetching order details: " . $e->getMessage());
    }
} else {
    // If no valid ID was provided, redirect back to the orders list
    $_SESSION['message'] = "Invalid Order ID.";
    header('Location: orders.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details #<?php echo $order['id']; ?> - EasyShop Admin</title>
    <link rel="stylesheet" href="../themes/<?php echo htmlspecialchars($active_theme); ?>/admin-style.css">
    <style>
        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        .order-summary-card, .order-items-card {
            padding: 1.5rem;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
        }
        .order-summary-card h2, .order-items-card h2 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f0f0f1;
        }
        .summary-item:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.2rem;
            color: #2271b1;
        }
        .status-update-form {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }
        .status-update-form select, .status-update-form button {
            width: auto;
        }

        /* --- RESPONSIVE PRODUCT ITEM STYLING --- */
        .item-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Using a more specific selector to override theme styles */
        td .product-thumbnail {
            width: 80px;
            height: 80px;
            max-width: 80px;
            max-height: 80px;
            flex-shrink: 0;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
            background-color: #f0f0f0;
        }

        /* --- MEDIA QUERY FOR MOBILE --- */
        @media (max-width: 768px) {
            .order-details-grid {
                grid-template-columns: 1fr;
            }
            .item-info {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            /* Also make the mobile selector more specific */
            td .product-thumbnail {
                width: 120px;
                height: 120px;
                max-width: 120px;
                max-height: 120px;
            }
        }
    </style>
</head>
<body>
<main class="container">
    <?php require_once __DIR__ . '/../includes/admin-menu.php'; ?>

    <h1>Order Details #<?php echo htmlspecialchars($order['id']); ?></h1>

    <?php
    // Display success message from session
    if (isset($_SESSION['message'])) {
        echo '<article style="background-color: #eff9ef; border-left: 4px solid #00a32a; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; color:#1d2327;">';
        echo htmlspecialchars($_SESSION['message']);
        echo '</article>';
        unset($_SESSION['message']);
    }
    ?>

    <div class="order-details-grid">
        <!-- Order Summary Card -->
        <div class="order-summary-card">
            <h2>Order Summary</h2>
            <div class="summary-item">
                <span>Customer Name:</span>
                <span><?php echo htmlspecialchars($order['name']); ?></span>
            </div>
            <div class="summary-item">
                <span>Customer Email:</span>
                <span><?php echo htmlspecialchars($order['email']); ?></span>
            </div>
            <div class="summary-item">
                <span>Order Date:</span>
                <span><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></span>
            </div>
            <div class="summary-item">
                <span>Status:</span>
                <span><strong><?php echo htmlspecialchars(ucfirst($order['status'])); ?></strong></span>
            </div>
            <div class="summary-item">
                <span>Total Amount:</span>
                <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
            </div>

            <!-- Status Update Form -->
            <form method="post" action="order-details.php?id=<?php echo $order['id']; ?>" class="status-update-form">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <label for="status">Update Status:</label>
                <div style="display: flex; gap: 0.5rem;">
                    <select name="status" id="status" required>
                        <option value="pending" <?php echo ($order['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo ($order['status'] === 'processing') ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo ($order['status'] === 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo ($order['status'] === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo ($order['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="secondary">Update</button>
                </div>
            </form>
        </div>

        <!-- Order Items Card -->
        <div class="order-items-card">
            <h2>Order Items</h2>
            <table>
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td>
                            <div class="item-info">
                                <img src="/uploads/<?php echo htmlspecialchars($item['image'] ?? 'placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-thumbnail">
                                <span class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                            </div>
                        </td>
                        <td><?php echo (int)$item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <p style="margin-top: 2rem;">
        <a href="orders.php" role="button" class="secondary">&laquo; Back to Orders</a>
    </p>

</main>
</body>
</html>