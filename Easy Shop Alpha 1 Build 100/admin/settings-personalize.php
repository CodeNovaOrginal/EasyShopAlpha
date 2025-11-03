<?php
define('IS_ADMIN_PANEL', true);
require_once __DIR__ . '/../includes/init.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

$active_theme = get_active_theme($pdo);
$message = '';
$message_type = '';

// Fetch current settings
$stmt = $pdo->query("SELECT theme_name, frontend_theme FROM settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_admin_theme = $_POST['theme_name'];
    $new_frontend_theme = $_POST['frontend_theme'];

    try {
        $sql = "UPDATE settings SET theme_name = ?, frontend_theme = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_admin_theme, $new_frontend_theme]);
        $message = "Appearance settings updated successfully!";
        $message_type = 'success';
        // Update local variables for the form
        $settings['theme_name'] = $new_admin_theme;
        $settings['frontend_theme'] = $new_frontend_theme;
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
    <title>Personalize - EasyShop Admin</title>
    <link rel="stylesheet" href="../themes/<?php echo htmlspecialchars($active_theme); ?>/admin-style.css">
</head>
<body>
<main class="container">
    <?php require_once __DIR__ . '/../includes/admin-menu.php'; ?>

    <h1>Settings: Personalize</h1>

    <?php if ($message): ?>
        <article style="background-color: <?php echo ($message_type === 'success') ? '#eff9ef' : '#fcf0f1'; ?>; border-left: 4px solid <?php echo ($message_type === 'success') ? '#00a32a' : '#d63638'; ?>; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; color:#1d2327;">
            <?php echo htmlspecialchars($message); ?>
        </article>
    <?php endif; ?>

    <form method="post" action="">
        <label for="theme_name">Admin Theme</label>
        <select name="theme_name" id="theme_name">
            <option value="graypress" <?php echo ($settings['theme_name'] === 'graypress') ? 'selected' : ''; ?>>GrayPress</option>
            <option value="bluenight" <?php echo ($settings['theme_name'] === 'bluenight') ? 'selected' : ''; ?>>BlueNight</option>
        </select>

        <label for="frontend_theme">Frontend Theme</label>
        <select name="frontend_theme" id="frontend_theme">
            <option value="alpha1" <?php echo ($settings['frontend_theme'] === 'alpha1') ? 'selected' : ''; ?>>Alpha 1</option>
        </select>

        <button type="submit">Save Changes</button>
    </form>
</main>
</body>
</html>