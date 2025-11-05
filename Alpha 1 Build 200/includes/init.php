<?php
// Start the session for things like login messages and user state
session_start();

// Define a constant to show we are in the admin panel.
// We will define this in all admin files.
if (!defined('IS_ADMIN_PANEL')) {
    // For the public site, we want to hide all non-fatal errors.
    // The .htaccess file will handle the fatal ones.
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
}

// Load the database connection
require_once __DIR__ . '/db.php';

// Load other core functions (like our theme getter)
require_once __DIR__ . '/functions.php';