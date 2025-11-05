<?php
define('IS_ADMIN_PANEL', true);
require_once __DIR__ . '/../includes/init.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

$active_theme = get_active_theme($pdo);

// --- UPDATE CHECK LOGIC ---
// Get the current version from our centralized function
$current_version_string = get_easyshop_version();
$update_check_result = '';
$update_check_type = ''; // 'success', 'error', 'info'

// Function to parse a version string into an array
function parse_version($version_string) {
    // Regex to capture Type (Alpha/Beta), Phase (1), and Build (200)
    if (preg_match('/^(Alpha|Beta)\s+(\d+)\s+Build\s+(\d+)$/i', $version_string, $matches)) {
        return [
            'type' => strtolower($matches[1]),
            'phase' => (int)$matches[2],
            'build' => (int)$matches[3]
        ];
    }
    return null;
}

// Function to compare two parsed versions
function compare_versions($current, $latest) {
    if ($latest['type'] !== $current['type']) {
        // Assuming Beta > Alpha
        return ($latest['type'] === 'beta') ? 1 : -1;
    }
    if ($latest['phase'] !== $current['phase']) {
        return $latest['phase'] - $current['phase'];
    }
    return $latest['build'] - $current['build'];
}

// Handle the check for updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_for_updates'])) {
    $github_api_url = 'https://api.github.com/repos/CodeNovaOrginal/EasyShopAlpha/releases/latest';

    // Create a stream context to set a user-agent, as required by GitHub API
    $options = [
        'http' => [
            'header' => "User-Agent: EasyShop-Updater\r\n"
        ]
    ];
    $context = stream_context_create($options);

    $response = @file_get_contents($github_api_url, false, $context);

    if ($response === false) {
        $update_check_result = "Could not connect to the update server. Please check your server's internet connection or try again later.";
        $update_check_type = 'error';
    } else {
        $release_data = json_decode($response, true);
        $latest_version_string = $release_data['name'] ?? null;

        if ($latest_version_string) {
            $current_parsed = parse_version($current_version_string);
            $latest_parsed = parse_version($latest_version_string);

            if ($current_parsed && $latest_parsed) {
                $comparison = compare_versions($current_parsed, $latest_parsed);

                if ($comparison < 0) {
                    // A new version is available
                    $update_check_result = "<strong>A new EasyShop update is available!</strong><br>";
                    $update_check_result .= "Current Version: " . htmlspecialchars($current_version_string) . "<br>";
                    $update_check_result .= "Latest Version: " . htmlspecialchars($latest_version_string) . "<br><br>";
                    $update_check_result .= "<strong>Release Notes:</strong><br>" . nl2br(htmlspecialchars($release_data['body'] ?? 'No release notes provided.'));
                    $update_check_type = 'success';
                } else {
                    // You are on the latest version
                    $update_check_result = "Your EasyShop is up to date. (Version: " . htmlspecialchars($current_version_string) . ")";
                    $update_check_type = 'info';
                }
            } else {
                $update_check_result = "Failed to parse version information from the server.";
                $update_check_type = 'error';
            }
        } else {
            $update_check_result = "Could not retrieve version information from the update server.";
            $update_check_type = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EasyShop Settings - EasyShop Admin</title>
    <link rel="stylesheet" href="../themes/<?php echo htmlspecialchars($active_theme); ?>/admin-style.css">
</head>
<body>
<main class="container">
    <?php require_once __DIR__ . '/../includes/admin-menu.php'; ?>

    <h1>Settings: EasyShop</h1>

    <div class="welcome-message">
        <h2>EasyShop Updater</h2>
        <p>Check for the latest version of EasyShop to ensure your store has the newest features and security updates.</p>
    </div>

    <form method="post" action="">
        <button type="submit" name="check_for_updates">Check for Updates</button>
    </form>

    <?php if ($update_check_result): ?>
        <article style="background-color: <?php echo ($update_check_type === 'success') ? '#eff9ef' : (($update_check_type === 'error') ? '#fcf0f1' : '#f0f6fc'); ?>; border-left: 4px solid <?php echo ($update_check_type === 'success') ? '#00a32a' : (($update_check_type === 'error') ? '#d63638' : '#2271b1'); ?>; padding: 1rem; margin-top: 1.5rem; border-radius: 4px; color:#1d2327;">
            <?php echo $update_check_result; ?>
        </article>
    <?php endif; ?>

</main>
</body>
</html>