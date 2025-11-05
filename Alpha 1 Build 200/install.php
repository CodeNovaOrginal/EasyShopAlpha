<?php
// If config.php exists, the system is already installed. Redirect to login.
if (file_exists('config.php')) {
    header('Location: admin/index.php');
    exit();
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get database details from the form
    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];

    // Get admin user details from the form
    $admin_name = $_POST['admin_name'];
    $admin_email = $_POST['admin_email'];
    $admin_pass = $_POST['admin_pass'];
    $store_name = $_POST['store_name'];
    $store_slogan = $_POST['store_slogan'];

    try {
        // Connect to MySQL without selecting a database first
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create the database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
        $pdo->exec("USE `$db_name`");

        // --- SQL to create tables ---
        $sql_users = "
        CREATE TABLE `users` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) NOT NULL,
          `email` varchar(255) NOT NULL,
          `password_hash` varchar(255) NOT NULL,
          `role` varchar(50) NOT NULL DEFAULT 'admin',
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `email` (`email`)
        );";

        $sql_settings = "
        CREATE TABLE `settings` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `store_name` varchar(255) NOT NULL,
          `theme_name` varchar(255) NOT NULL DEFAULT 'graypress',
          `frontend_theme` varchar(255) NOT NULL DEFAULT 'alpha1',
          `slogan` TEXT NULL,
          PRIMARY KEY (`id`)
        );";

        $sql_products = "
        CREATE TABLE `products` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `description` text NOT NULL,
            `price` decimal(10, 2) NOT NULL,
            `stock` int(11) NOT NULL DEFAULT 0,
            `image` varchar(255) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        );";

        $sql_orders = "
        CREATE TABLE `orders` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `order_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `total_amount` DECIMAL(10, 2) NOT NULL,
            `status` VARCHAR(50) DEFAULT 'pending',
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        );";

        $sql_order_items = "
        CREATE TABLE `order_items` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `order_id` INT NOT NULL,
            `product_id` INT NOT NULL,
            `quantity` INT NOT NULL,
            `price` DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
        );";

        $pdo->exec($sql_users);
        $pdo->exec($sql_settings);
        $pdo->exec($sql_products);
        $pdo->exec($sql_orders);
        $pdo->exec($sql_order_items);

        // --- Insert initial data ---

        // 1. Insert the admin user
        $password_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$admin_name, $admin_email, $password_hash]);

        // 2. Insert the initial settings
        $stmt = $pdo->prepare("INSERT INTO settings (store_name, slogan, theme_name, frontend_theme) VALUES (?, ?, 'graypress', 'alpha1')");
        $stmt->execute([$store_name, $store_slogan]);

        // Create the config.php file
        $config_content = "<?php\n";
        $config_content .= "define('DB_HOST', '$db_host');\n";
        $config_content .= "define('DB_NAME', '$db_name');\n";
        $config_content .= "define('DB_USER', '$db_user');\n";
        $config_content .= "define('DB_PASS', '$db_pass');\n";
        $config_content .= "?>";

        file_put_contents('config.php', $config_content);

        // Redirect to the admin login page
        header('Location: admin/index.php?install=success');
        exit();

    } catch (PDOException $e) {
        $error = "Database Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Install EasyShop</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
</head>
<body>
<main class="container">
    <h1>Welcome to EasyShop!</h1>
    <p>Please fill in the details below to set up your store.</p>

    <?php if (isset($error)): ?>
        <article style="background-color: #ffdddd; border: 1px solid #ff4444; padding: 1rem; border-radius: 5px;">
            <strong>Error:</strong> <?php echo $error; ?>
        </article>
    <?php endif; ?>

    <form method="post" action="install.php">
        <h3>Store Settings</h3>
        <label for="store_name">Store Name</label>
        <input type="text" id="store_name" name="store_name" required>

        <!-- NEW SLOGAN FIELD -->
        <label for="store_slogan">Store Slogan</label>
        <textarea id="store_slogan" name="store_slogan"></textarea>

        <h3>Admin Account</h3>
        <label for="admin_name">Your Name</label>
        <input type="text" id="admin_name" name="admin_name" required>

        <label for="admin_email">Admin Email (will be your username)</label>
        <input type="email" id="admin_email" name="admin_email" required>

        <label for="admin_pass">Admin Password</label>
        <input type="password" id="admin_pass" name="admin_pass" required>

        <h3>Database Connection</h3>
        <label for="db_host">Database Host</label>
        <input type="text" id="db_host" name="db_host" value="localhost" required>

        <label for="db_name">Database Name</label>
        <input type="text" id="db_name" name="db_name" value="easyshop_db" required>

        <label for="db_user">Database User</label>
        <input type="text" id="db_user" name="db_user" value="root" required>

        <label for="db_pass">Database Password</label>
        <input type="password" id="db_pass" name="db_pass" required>

        <button type="submit">Install EasyShop</button>
    </form>
</main>
</body>
</html>