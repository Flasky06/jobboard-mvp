<?php
echo "<h1>Google OAuth Debug</h1>";

// Check if vendor autoload exists
$autoloadPath = __DIR__ . '/../../../vendor/autoload.php';
echo "<h2>Files Exist:</h2>";
echo "vendor/autoload.php: " . (file_exists($autoloadPath) ? '✓ YES' : '✗ NO') . "<br>";

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// Load .env
$envPath = __DIR__ . '/../../../.env';
echo ".env file: " . (file_exists($envPath) ? '✓ YES' : '✗ NO') . "<br><br>";

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

echo "<h2>Environment Variables:</h2>";
echo "GOOGLE_CLIENT_ID: " . (isset($_ENV['GOOGLE_CLIENT_ID']) ? substr($_ENV['GOOGLE_CLIENT_ID'], 0, 20) . '...' : '✗ NOT SET') . "<br>";
echo "GOOGLE_CLIENT_SECRET: " . (isset($_ENV['GOOGLE_CLIENT_SECRET']) ? '✓ SET' : '✗ NOT SET') . "<br>";
echo "GOOGLE_REDIRECT_URI: " . ($_ENV['GOOGLE_REDIRECT_URI'] ?? '✗ NOT SET') . "<br><br>";

// Check if Google Client can be created
echo "<h2>Google Client Test:</h2>";
try {
    if (class_exists('Google_Client')) {
        echo "Google_Client class: ✓ EXISTS<br>";
        $client = new Google_Client();
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? '');
        $client->addScope("email");
        $client->addScope("profile");
        
        $authUrl = $client->createAuthUrl();
        echo "Auth URL generated: ✓ YES<br><br>";
        echo '<a href="' . htmlspecialchars($authUrl) . '" style="padding: 10px 20px; background: #4285F4; color: white; text-decoration: none; border-radius: 4px;">Test Google Login</a><br>';
    } else {
        echo "Google_Client class: ✗ NOT FOUND<br>";
    }
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "<br>";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Config Files:</h2>";
$files = [
    'google-oauth.php' => __DIR__ . '/../../../config/google-oauth.php',
    'db.php' => __DIR__ . '/../../../config/db.php',
    'GoogleAuthController.php' => __DIR__ . '/../../../controllers/GoogleAuthController.php',
    'session.php' => __DIR__ . '/../../../helpers/session.php'
];

foreach ($files as $name => $path) {
    echo "$name: " . (file_exists($path) ? '✓ YES' : '✗ NO') . "<br>";
}

echo "<h2>PHP Version:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Required: 8.0+ (you have PHP " . phpversion() . ")<br>";
?>