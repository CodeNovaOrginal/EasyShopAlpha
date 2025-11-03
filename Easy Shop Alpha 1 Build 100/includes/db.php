<?php
// Check if config file exists before trying to use it
if (!file_exists(__DIR__ . '/../config.php')) {
    die("Error: EasyShop is not installed. Please run the installer first.");
}

require_once __DIR__ . '/../config.php';

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If connection fails, stop the script and show an error
    die("Database Connection Failed: " . $e->getMessage());
}