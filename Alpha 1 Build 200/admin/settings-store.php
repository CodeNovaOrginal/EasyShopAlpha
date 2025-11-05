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
$stmt = $pdo->query("SELECT store_name, slogan FROM settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_store_name = trim($_POST['store_name']);
    $new_slogan = trim($_POST['slogan']);

    if (empty($new_store_name)) {
        $message = "Store Name cannot be empty.";
        $message_type = 'error';
    } else {
        try {
            $sql = "UPDATE settings SET store_name = ?, slogan = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_store_name, $new_slogan]);
            $message = "Store information updated successfully!";
            $message_type = 'success';
            // Update local variables for the form
            $settings['store_name'] = $new_store_name;
            $settings['slogan'] = $new_slogan;
        } catch (PDOException $e) {
            $message = "Database Error: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Store Info - EasyShop Admin</title>
    <link rel="stylesheet" href="../themes/<?php echo htmlspecialchars($active_theme); ?>/admin-style.css">
</head>
<body>
<main class="container">
    <?php require_once __DIR__ . '/../includes/admin-menu.php'; ?>

    <h1>Settings: Store Info</h1>

    <?php if ($message): ?>
        <article style="background-color: <?php echo ($message_type === 'success') ? '#eff9ef' : '#fcf0f1'; ?>; border-left: 4px solid <?php echo ($message_type === 'success') ? '#00a32a' : '#d63638'; ?>; padding: 1rem; margin-bottom: 1rem; border-radius: 4px; color:#1d2327;">
            <?php echo htmlspecialchars($message); ?>
        </article>
    <?php endif; ?>

    <form method="post" action="">
        <label for="store_name">Store Name</label>
        <input type="text" id="store_name" name="store_name" value="<?php echo htmlspecialchars($settings['store_name']); ?>" required>

        <label for="slogan">Store Slogan</label>
        <textarea id="slogan" name="slogan"><?php echo htmlspecialchars($settings['slogan']); ?></textarea>

        <button type="submit">Save Changes</button>
    </form>
</main>
</body>
</html>