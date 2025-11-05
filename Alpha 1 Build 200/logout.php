<?php
// Load the core engine to start the session
require_once __DIR__ . '/includes/init.php';

// Destroy all session data
session_destroy();

// Redirect to the homepage with a logout message
header('Location: /?logged_out=success');
exit();