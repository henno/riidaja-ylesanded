<?php
session_start();
date_default_timezone_set('Europe/Tallinn');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/models/Database.php';
require_once __DIR__ . '/models/ResultsModel.php';
require_once __DIR__ . '/controllers/TaskController.php';
require_once __DIR__ . '/controllers/ResultsController.php';
// Check if config.php exists, if not, create it from the sample
if (!file_exists(__DIR__ . '/config.php') && file_exists(__DIR__ . '/config.sample.php')) {
    copy(__DIR__ . '/config.sample.php', __DIR__ . '/config.php');
}
require 'config.php';

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use TheNetworg\OAuth2\Client\Provider\Azure;

$provider = new Azure([
  'clientId' => AZURE_CLIENT_ID,
  'clientSecret' => AZURE_CLIENT_SECRET,
  'scopes'                 => ['openid', 'profile', 'email', 'offline_access', 'User.Read'],
  'defaultEndPointVersion' => '2.0',
  'resource'               => 'https://graph.microsoft.com'
]);

if (isset($_GET['logout'])) {
  $_SESSION = [];
  if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
  }
  session_destroy();

  // If bypass is enabled, just redirect to the home page
  if (defined('BYPASS_AZURE_AUTH') && BYPASS_AZURE_AUTH === true) {
    header('Location: ./');
    exit;
  } else {
    // Normal Azure logout
    $logoutUrl = 'https://login.microsoftonline.com/common/oauth2/v2.0/logout?post_logout_redirect_uri=' . urlencode('https://torva.ee/riidaja/');
    header('Location: ' . $logoutUrl);
    exit;
  }
}

// Check if authentication bypass is enabled
if (defined('BYPASS_AZURE_AUTH') && BYPASS_AZURE_AUTH === true) {
  // Create a test user session without Azure authentication
  if (!isset($_SESSION['accessToken'])) {
    $_SESSION['accessToken'] = 'bypass_token';
    $_SESSION['user'] = [
      'name'  => 'Test User',
      'email' => 'test.user@example.com'
    ];
  }
} else {
  // Normal Azure authentication flow
  if (!isset($_SESSION['accessToken'])) {
    if (!isset($_GET['code'])) {
      $authUrl = $provider->getAuthorizationUrl(['prompt' => 'select_account']);
      $_SESSION['oauth2state'] = $provider->getState();
      header('Location: ' . $authUrl);
      exit;
    }
    if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
      unset($_SESSION['oauth2state']);
      exit('Invalid state');
    }
    $token = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
    $_SESSION['accessToken'] = $token->getToken();

    $graph = new Graph();
    $graph->setAccessToken($_SESSION['accessToken']);

    $user = $graph->createRequest('GET', '/me')
                  ->setReturnType(Model\User::class)
                  ->execute();

    $_SESSION['user'] = [
      'name'  => $user->getDisplayName(),
      'email' => $user->getUserPrincipalName()
    ];

    // Redirect to clean URL after successful authentication
    if (isset($_GET['code']) || isset($_GET['state']) || isset($_GET['session_state'])) {
      // Preserve important query parameters but remove authentication-related ones
      $params = $_GET;
      unset($params['code']);
      unset($params['state']);
      unset($params['session_state']);

      $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?');

      // Add remaining parameters back to the URL
      if (!empty($params)) {
        $redirectUrl .= '?' . http_build_query($params);
      }

      // Add cache control headers to prevent caching of the authentication URL
      header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
      header('Pragma: no-cache');
      header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

      header('Location: ' . $redirectUrl);
      exit;
    }
  }
}

$isAdmin = isset($_SESSION['user']['email']) && $_SESSION['user']['email'] === 'henno.taht@torva.edu.ee';

$resultsModel = new ResultsModel();

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
    body { font-family: sans-serif; }
    nav.topbar { background: #f0f0f0; padding: 10px; display: flex; justify-content: space-between; align-items: center; }
    nav.topbar a { margin: 0 10px; text-decoration: none; }
    table { border-collapse: collapse; margin-top: 20px; width: 100%; }
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
    </div>
    <div class="nav-right">
      <a href="?logout=1">Logi välja</a>
    </div>
  </nav>

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
?>
</body>
</html>
