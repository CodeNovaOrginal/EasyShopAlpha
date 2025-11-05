<?php
/**
 * Gets the active theme name from the database.
 * @param PDO $pdo The database connection object.
 * @return string The name of the active theme.
 */
function get_active_theme($pdo) {
    try {
        $stmt = $pdo->query("SELECT theme_name FROM settings LIMIT 1");
        $theme = $stmt->fetchColumn();
        // Fallback to 'graypress' if something goes wrong
        return $theme ? $theme : 'graypress';
    } catch (PDOException $e) {
        // If the query fails, return the default theme
        return 'graypress';
    }
}

/**
 * Gets the current EasyShop version.
 * @return string The current version string.
 */
function get_easyshop_version() {
    // IMPORTANT: Update this value whenever you release a new version
    return "Alpha 1 Build 200";
}