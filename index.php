<?php
session_start();
date_default_timezone_set('Europe/Tallinn');

if (isset($_GET['code']) || isset($_GET['state']) || isset($_GET['session_state'])) {
    $callbackProvider = $_GET['provider'] ?? ($_SESSION['oauth2provider'] ?? 'azure');
    $callbackParams = $_GET;
    $callbackParams['provider'] = $callbackProvider;

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    header('Location: login-callback.php?' . http_build_query($callbackParams));
    exit;
}

// Domain detection and database path setup (MUST be before other requires)
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/ResultsModel.php';
require_once __DIR__ . '/models/StudentsModel.php';
require_once __DIR__ . '/controllers/TaskController.php';
require_once __DIR__ . '/controllers/ResultsController.php';
require_once __DIR__ . '/controllers/StudentsController.php';

// Check if config.php exists, if not, create it from the sample
if (!file_exists(__DIR__ . '/config.php') && file_exists(__DIR__ . '/config.sample.php')) {
    copy(__DIR__ . '/config.sample.php', __DIR__ . '/config.php');
}
require 'config.php';

// ─────────────────────────────────────────────────────────────────────────────
// Logout handler
// ─────────────────────────────────────────────────────────────────────────────

if (isset($_GET['logout'])) {
    $authProvider = $_SESSION['auth_provider'] ?? null;
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();

    // Redirect based on auth provider
    if ($authProvider === 'azure') {
        $logoutUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/logout?post_logout_redirect_uri=' . urlencode('https://torva.ee/riidaja/login.php');
        header('Location: ' . $logoutUrl);
    } else {
        // Google or bypass - just redirect to login
        header('Location: login.php');
    }
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// Authentication flow
// ─────────────────────────────────────────────────────────────────────────────

// Check if authentication bypass is enabled
if (defined('BYPASS_AZURE_AUTH') && BYPASS_AZURE_AUTH === true) {
    // Create a test user session without OAuth authentication
    if (!isset($_SESSION['accessToken'])) {
        $_SESSION['accessToken'] = 'bypass_token';
        $_SESSION['auth_provider'] = 'bypass';
        $_SESSION['user'] = [
            'name'  => 'Test User',
            'email' => 'test.user@example.com'
        ];
    }
} elseif (!isset($_SESSION['accessToken'])) {
    // Not authenticated - redirect to login page
    header('Location: login.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// Admin check
// ─────────────────────────────────────────────────────────────────────────────

$isAdmin = isset($_SESSION['user']['email']) && (
    $_SESSION['user']['email'] === ADMIN_EMAIL ||
    (defined('VKOK_ADMIN_EMAIL') && VKOK_ADMIN_EMAIL && $_SESSION['user']['email'] === VKOK_ADMIN_EMAIL)
);

$resultsModel = new ResultsModel();
$studentsModel = new StudentsModel();

if ($isAdmin && isset($_GET['delete'])) {
    $resultsModel->delete((int)$_GET['delete']);
    header('Location: ?page=results');
    exit;
}

$page = $_GET['page'] ?? 'tasks';

// Set cache control headers for the main page
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="Thu, 01 Jan 1970 00:00:00 GMT">
    <title>Õpilaste Ülesanded</title>
    <!-- Bootstrap CSS and JS with Popper.js -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Session tracker for exercise time tracking -->
    <script src="js/session-tracker.js"></script>
    <script>
        // User data for session tracking
        window.RIIDAJA_USER = {
            email: <?= json_encode($_SESSION['user']['email'] ?? '') ?>,
            name: <?= json_encode($_SESSION['user']['name'] ?? '') ?>
        };
        // WebSocket port for session tracking
        window.RIIDAJA_WS_PORT = <?= ($_SESSION['auth_provider'] ?? 'azure') === 'google' ? 8766 : 8765 ?>;
    </script>
    <script>
        // Clean up URL in browser address bar if it contains authentication parameters
        if (window.location.href.includes('code=') || window.location.href.includes('state=') || window.location.href.includes('session_state=')) {
            // Get current URL and remove authentication parameters
            const url = new URL(window.location.href);
            url.searchParams.delete('code');
            url.searchParams.delete('state');
            url.searchParams.delete('session_state');

            // Replace the URL in the browser without reloading the page
            window.history.replaceState({}, document.title, url.toString());
        }
    </script>
    <style>
        /* Universal box-sizing for consistent layout */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        /* Global styling improvements */
        body {
            font-family: sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Main content container with proper margins */
        .main-content {
            padding: 20px;
        }

        /* Navigation styling */
        nav.topbar {
            background: #f0f0f0;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        nav.topbar a { margin: 0 10px; text-decoration: none; }

        /* Table styling with shadow */
        table {
            border-collapse: collapse;
            margin-top: 20px;
            width: 100%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.07);
            background: white;
            border-radius: 4px;
            overflow: hidden;
        }

        td, th { border: 1px solid #999; padding: 6px 10px; text-align: center; }
        .delete-link { color: red; text-decoration: none; margin-left: 8px; }
    </style>
</head>
<body>
<nav class="topbar">
    <div class="nav-left">
        <strong><?php echo htmlspecialchars($_SESSION['user']['name'] ?? ''); ?></strong>
        <a href="?page=tasks">Ülesanded</a>
        <a href="?page=results">Tulemused</a>
        <a href="?page=students">Õpilased</a>
    </div>
    <div class="nav-right">
        <a href="?logout=1">Logi välja</a>
    </div>
</nav>

<div class="main-content">
    <?php
    if ($page === 'tasks') {
        $taskController = new TaskController($resultsModel);
        isset($_GET['task'])
                ? $taskController->show($_GET['task'])
                : $taskController->list($_SESSION['user']['email']);
    }

    if ($page === 'results') {
        $resultsController = new ResultsController($resultsModel, $isAdmin);
        $resultsController->show($_GET['exercise'] ?? null);
    }

    if ($page === 'students') {
        $studentsController = new StudentsController($studentsModel, $isAdmin);
        $studentsController->show();
    }
    ?>
</div>
</body>
</html>
