<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
define('GOOGLE_REDIRECT_URI', $_ENV['GOOGLE_REDIRECT_URI'] ?? '');
define('GOOGLE_OAUTH_ACCESS_TOKEN', 'google_oauth_access_token');

/**
 * Get configured Google Client
 */
function getGoogleClient() {
    $client = new Google_Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setClientSecret(GOOGLE_CLIENT_SECRET);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    $client->addScope("email");
    $client->addScope("profile");

    // Set state for CSRF protection
    $state = generateOAuthState();
    $client->setState($state);

    // Debug: Log state setting
    error_log("Setting OAuth state in client: " . $state);

    return $client;
}

/**
 * Generate OAuth state for CSRF protection
 */
function generateOAuthState() {
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;
    return $state;
}

/**
 * Validate OAuth state
 */
function validateOAuthState($state) {
    return isset($_SESSION['oauth_state']) && hash_equals($_SESSION['oauth_state'], $state);
}

/**
 * Clear OAuth state
 */
function clearOAuthState() {
    unset($_SESSION['oauth_state']);
}
?>