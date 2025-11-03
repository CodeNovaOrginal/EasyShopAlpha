<?php
// Load the core engine
require_once __DIR__ . '/includes/init.php';

// If the user is already logged in, redirect them to their account page
if (isset($_SESSION['customer_logged_in']) && $_SESSION['customer_logged_in'] === true) {
    header('Location: account.php');
    exit();
}

// Get store name for the page title
try {
    $stmt = $pdo->query("SELECT store_name FROM settings LIMIT 1");
    $store_name = $stmt->fetchColumn();
} catch (PDOException $e) {
    $store_name = "EasyShop";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        try {
            // Prepare a statement to fetch the user by email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if user exists and the password is correct
            if ($user && password_verify($password, $user['password_hash'])) {
                // Password is correct, so start a new session
                $_SESSION['customer_logged_in'] = true;
                $_SESSION['customer_id'] = $user['id'];
                $_SESSION['customer_name'] = $user['name'];
                $_SESSION['customer_email'] = $user['email'];

                // Redirect to the account page
                header('Location: account.php');
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Login failed. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Log In - <?php echo htmlspecialchars($store_name); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/storefront.css">
</head>
<body>
<!-- INCLUDE THE REUSABLE HEADER -->
<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="container auth-main">
    <div class="auth-form-container">
        <h1>Log In</h1>
        <p>Welcome back! Please log in to your account.</p>

        <!-- SUCCESS MESSAGE FOR NEW REGISTRATIONS -->
        <?php if (isset($_GET['registered']) && $_GET['registered'] === 'success'): ?>
            <article class="user-message">Registration successful! Please log in.</article>
        <?php endif; ?>

        <!-- ERROR MESSAGE -->
        <?php if (isset($error)): ?>
            <article class="user-message error-message"><?php echo htmlspecialchars($error); ?></article>
        <?php endif; ?>

        <form method="post" class="auth-form">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Log In</button>
        </form>
        <p class="auth-switch">Don't have an account? <a href="/register.php">Register here</a>.</p>
    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($store_name); ?>. Powered by EasyShop.</p>
    </div>
</footer>
</body>
</html>