<?php
session_start();

function redirectOAuthCallbackToHandler($defaultProvider = null)
{
    $provider = $_GET['provider'] ?? ($_SESSION['oauth2provider'] ?? $defaultProvider);
    if (!$provider) {
        return;
    }

    $params = $_GET;
    $params['provider'] = $provider;

    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
    header('Location: login-callback.php?' . http_build_query($params));
    exit;
}

// Support OAuth callbacks that still land on login.php.
if (isset($_GET['code']) || isset($_GET['state']) || isset($_GET['session_state'])) {
    redirectOAuthCallbackToHandler('azure');
}

// If already logged in, redirect to main page
if (isset($_SESSION['accessToken'])) {
    header('Location: ./');
    exit;
}

// Get the selected provider from URL
$provider = $_GET['provider'] ?? null;

// If provider selected, start OAuth flow
if ($provider) {
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/config.php';

    if ($provider === 'google') {
        $googleRedirectUri = (defined('GOOGLE_REDIRECT_URI') && GOOGLE_REDIRECT_URI)
            ? GOOGLE_REDIRECT_URI
            : 'https://torva.ee/riidaja/login-callback.php?provider=google';

        $oauthProvider = new \League\OAuth2\Client\Provider\Google([
            'clientId'     => GOOGLE_CLIENT_ID,
            'clientSecret' => GOOGLE_CLIENT_SECRET,
            'redirectUri'  => $googleRedirectUri,
        ]);

        $authUrl = $oauthProvider->getAuthorizationUrl([
            'scope' => ['openid', 'profile', 'email'],
        ]);
        $_SESSION['oauth2provider'] = 'google';
        $_SESSION['oauth2state'] = $oauthProvider->getState();
        header('Location: ' . $authUrl);
        exit;

    } elseif ($provider === 'azure') {
        \Firebase\JWT\JWT::$leeway = 300;
        require_once __DIR__ . '/AzureWithLeeway.php';

        $oauthProvider = new AzureWithLeeway([
            'clientId'               => AZURE_CLIENT_ID,
            'clientSecret'           => AZURE_CLIENT_SECRET,
            'scopes'                 => ['openid', 'profile', 'email', 'offline_access', 'User.Read'],
            'defaultEndPointVersion' => '2.0',
            'resource'               => 'https://graph.microsoft.com',
            'tokenLeeway'            => 300,
        ]);

        $authUrl = $oauthProvider->getAuthorizationUrl(['prompt' => 'select_account']);
        $_SESSION['oauth2provider'] = 'azure';
        $_SESSION['oauth2state'] = $oauthProvider->getState();
        header('Location: ' . $authUrl);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="et">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riidaja - Sisse logimine</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }

        .login-container h1 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
            font-size: 28px;
        }

        .login-container p {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .login-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .login-btn {
            padding: 14px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .azure-btn {
            background: #0078d4;
            color: white;
        }

        .azure-btn:hover {
            background: #006cbe;
            box-shadow: 0 4px 12px rgba(0, 120, 212, 0.4);
            transform: translateY(-2px);
        }

        .google-btn {
            background: white;
            color: #333;
            border: 2px solid #dadce0;
        }

        .google-btn:hover {
            background: #f8f9fa;
            border-color: #4285f4;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

    </style>
</head>
<body>
    <div class="login-container">
        <h1>Riidaja</h1>
        <p>Õpilaste ülesanded</p>

        <div class="login-buttons">
            <a href="?provider=azure" class="login-btn azure-btn">
                Logi sisse @torva.edu.ee kontoga
            </a>
            <a href="?provider=google" class="login-btn google-btn">
                Logi sisse @vkok.ee kontoga
            </a>
        </div>
    </div>
</body>
</html>
