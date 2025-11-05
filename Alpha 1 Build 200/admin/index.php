<?php
define('IS_ADMIN_PANEL', true);

// Load the core engine
require_once __DIR__ . '/../includes/init.php';

// Get the active theme from the database
$active_theme = get_active_theme($pdo);

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_user_name'] = $user['name'];
            $_SESSION['admin_user_role'] = $user['role'];
            header('Location: index.php');
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } catch (PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

$logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// --- Fetch Dashboard Stats ---
$product_count = 0;
$user_count = 0;
$low_stock_count = 0;

if ($logged_in) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM products");
        $product_count = $stmt->fetchColumn();
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $user_count = $stmt->fetchColumn();
        $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 5");
        $low_stock_count = $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EasyShop Admin</title>
    <link rel="stylesheet" href="../themes/<?php echo htmlspecialchars($active_theme); ?>/admin-style.css">
</head>
<body>
<main class="container">
    <?php if ($logged_in): ?>
        <!-- ADMIN MENU IS NOW INCLUDED -->
        <?php require_once __DIR__ . '/../includes/admin-menu.php'; ?>

        <h1>Dashboard</h1>

        <div class="welcome-message">
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_user_name'] ?? 'Admin'); ?>!</h2>
            <p>Here's an overview of your store's performance.</p>
        </div>

        <div class="stat-cards-grid">
            <div class="stat-card">
                <h3>Total Products</h3>
                <p class="stat-number"><?php echo $product_count; ?></p>
                <p class="stat-description">Items in your catalog</p>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <p class="stat-number"><?php echo $user_count; ?></p>
                <p class="stat-description">Registered accounts</p>
            </div>
            <div class="stat-card">
                <h3>Low Stock Alert</h3>
                <p class="stat-number"><?php echo $low_stock_count; ?></p>
                <p class="stat-description">Items need restocking</p>
            </div>
        </div>

    <?php else: ?>
        <div style="max-width: 400px; margin: 4rem auto;">
            <h1 style="text-align: center;">EasyShop Admin</h1>
            <form method="post">
                <?php if (isset($error)): ?>
                    <article style="background-color: #fcf0f1; border-left: 4px solid #d63638; padding: 1rem; margin-bottom: 1rem; border-radius: 4px;">
                        <strong>Error:</strong> <?php echo $error; ?>
                    </article>
                <?php endif; ?>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <button type="submit" name="login">Log In</button>
            </form>
        </div>
    <?php endif; ?>
</main>
</body>
</html>