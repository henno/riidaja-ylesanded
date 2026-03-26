<?php
session_start();

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Firebase\JWT\JWT;
use League\OAuth2\Client\Provider\Google;

$provider = $_GET['provider'] ?? null;

if (!$provider) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['code'])) {
    header('Location: login.php');
    exit;
}

if ($provider === 'google') {
    $oauthProvider = new Google([
        'clientId'     => GOOGLE_CLIENT_ID,
        'clientSecret' => GOOGLE_CLIENT_SECRET,
        'redirectUri'  => 'https://torva.ee/riidaja/login-callback.php?provider=google',
    ]);

    if (empty($_GET['state']) || $_GET['state'] !== $_SESSION['oauth2state'] ?? null) {
        unset($_SESSION['oauth2state']);
        http_response_code(403);
        exit('Invalid state');
    }
    unset($_SESSION['oauth2state']);

    try {
        $token = $oauthProvider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
    } catch (Exception $e) {
        error_log('Google OAuth error: ' . $e->getMessage());
        header('Location: login.php?error=auth_failed');
        exit;
    }

    $resourceOwner = $oauthProvider->getResourceOwner($token);
    $email = $resourceOwner->getEmail();

    $_SESSION['accessToken'] = $token->getToken();
    $_SESSION['auth_provider'] = 'google';
    $_SESSION['user'] = [
        'name'  => $resourceOwner->getName(),
        'email' => $email,
    ];

    // Redirect to clean URL
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Location: ./');
    exit;

} elseif ($provider === 'azure') {
    JWT::$leeway = 300;
    require_once __DIR__ . '/AzureWithLeeway.php';

    $oauthProvider = new AzureWithLeeway([
        'clientId'               => AZURE_CLIENT_ID,
        'clientSecret'           => AZURE_CLIENT_SECRET,
        'scopes'                 => ['openid', 'profile', 'email', 'offline_access', 'User.Read'],
        'defaultEndPointVersion' => '2.0',
        'resource'               => 'https://graph.microsoft.com',
        'tokenLeeway'            => 300,
    ]);

    if (empty($_GET['state']) || $_GET['state'] !== $_SESSION['oauth2state'] ?? null) {
        unset($_SESSION['oauth2state']);
        http_response_code(403);
        exit('Invalid state');
    }
    unset($_SESSION['oauth2state']);

    try {
        $token = $oauthProvider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
    } catch (Exception $e) {
        error_log('Azure OAuth error: ' . $e->getMessage());
        header('Location: login.php?error=auth_failed');
        exit;
    }

    $_SESSION['accessToken'] = $token->getToken();
    $_SESSION['auth_provider'] = 'azure';

    $graph = new Graph();
    $graph->setAccessToken($_SESSION['accessToken']);

    $user = $graph->createRequest('GET', '/me')
        ->setReturnType(Model\User::class)
        ->execute();

    $_SESSION['user'] = [
        'name'  => $user->getDisplayName(),
        'email' => $user->getUserPrincipalName()
    ];

    // Redirect to clean URL
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Location: ./');
    exit;
}

// Invalid provider
header('Location: login.php');
exit;
