<?php
define('IS_ADMIN_PANEL', true);
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

$active_theme = get_active_theme($pdo);
$version = "ALPHA 1 Build 100"; // You can define this elsewhere later
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About - EasyShop Admin</title>
    <link rel="stylesheet" href="../themes/<?php echo htmlspecialchars($active_theme); ?>/admin-style.css">
</head>
<body>
<main class="container">
    <!-- ADMIN MENU IS NOW INCLUDED -->
    <?php require_once __DIR__ . '/../includes/admin-menu.php'; ?>

    <h1>About EasyShop</h1>

    <div class="welcome-message" style="border-left-color: #646970;">
        <h2>EasyShop <?php echo htmlspecialchars($version); ?></h2>
        <p>A simple, powerful, and customizable e-commerce Content Management System (CMS) designed for ease of use and flexibility.</p>
    </div>

    <h3>Core Features</h3>
    <ul>
        <li>✅ Web-based installer for easy setup.</li>
        <li>✅ Secure user authentication and management.</li>
        <li>✅ Full Product CRUD (Create, Read, Update, Delete).</li>
        <li>✅ Dynamic theming system.</li>
        <li>✅ Custom error pages (404, 500).</li>
        <li>✅ Modern, responsive public storefront.</li>
    </ul>

    <h3>Technologies</h3>
    <p>Built with a classic and reliable stack:</p>
    <ul>
        <li><strong>Backend:</strong> PHP 8.x</li>
        <li><strong>Database:</strong> MySQL / MariaDB</li>
        <li><strong>Frontend:</strong> Vanilla HTML, CSS, and JavaScript</li>
    </ul>

    <p style="margin-top: 2rem; text-align: center; color: #646970;">
        Thank you for using EasyShop!
    </p>
</main>
</body>
</html>