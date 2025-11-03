<?php
define('IS_ADMIN_PANEL', true);
// Load the core engine
require_once __DIR__ . '/../includes/init.php';

// --- AUTHENTICATION CHECK ---
// If the admin is not logged in, redirect them to the login page.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// --- LOGIC ---
// Check if an ID was provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    try {
        // Prepare the DELETE statement to prevent SQL injection
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);

        // Set a success message to show on the product list page
        $_SESSION['message'] = "Product deleted successfully!";

    } catch (PDOException $e) {
        // If something goes wrong, set an error message
        $_SESSION['message'] = "Error deleting product: " . $e->getMessage();
    }
} else {
    // If no valid ID was provided, set an error message
    $_SESSION['message'] = "Invalid product ID.";
}

// Redirect back to the product list page
header('Location: products.php');
exit();