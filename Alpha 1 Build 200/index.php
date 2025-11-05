<?php
// Load the core engine.
require_once __DIR__ . '/includes/init.php';

// --- LOGIC ---
// Fetch all products from the database
try {
    $stmt = $pdo->query("SELECT id, name, price, image FROM products WHERE stock > 0 ORDER BY created_at DESC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If the query fails, the error handler will redirect to error.php.
    $products = [];
}

// Get all settings from the database
try {
    $stmt = $pdo->query("SELECT store_name, slogan, frontend_theme FROM settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    $store_name = $settings['store_name'];
    $slogan = $settings['slogan'];
    $frontend_theme = $settings['frontend_theme'];
} catch (PDOException $e) {
    // Fallback values
    $store_name = "EasyShop";
    $slogan = "";
    $frontend_theme = "alpha1";
}

// Determine the theme path
$theme_path = __DIR__ . "/themes/frontend/{$frontend_theme}";
$template_file = $theme_path . '/index.php';
$style_file = $theme_path . '/style.css';

// Security: Ensure the theme name is valid to prevent path traversal
$allowed_themes = ['alpha1'];
if (!in_array($frontend_theme, $allowed_themes) || !file_exists($template_file)) {
    // Fallback to alpha1 if the selected theme is invalid or missing
    $template_file = __DIR__ . "/themes/frontend/alpha1/index.php";
    $style_file = __DIR__ . "/themes/frontend/alpha1/style.css";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($store_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- DYNAMIC THEME STYLESHEET -->
    <link rel="stylesheet" href="/themes/frontend/<?php echo htmlspecialchars($frontend_theme); ?>/style.css">
</head>
<body>
<!-- INCLUDE THE REUSABLE HEADER -->
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- INCLUDE THE THEME TEMPLATE -->
<?php require_once $template_file; ?>

<!-- INCLUDE THE FOOTER -->
<footer class="site-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($store_name); ?>. Powered by EasyShop.</p>
    </div>
</footer>
</body>
</html>