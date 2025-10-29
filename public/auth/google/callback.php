<?php
require_once __DIR__ . '/../../../helpers/session.php';
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../controllers/GoogleAuthController.php';

// Log everything for debugging
error_log("=== Google Callback Debug ===");
error_log("GET params: " . print_r($_GET, true));
error_log("Session state: " . ($_SESSION['oauth_state'] ?? 'not set'));

// Handle errors from Google
if (isset($_GET['error'])) {
    error_log("Google OAuth error: " . $_GET['error']);
    $_SESSION['errors'] = ['Google authentication was cancelled or failed. Please try again.'];
    header('Location: /auth/login.php');
    exit;
}

try {
    // Process the callback
    $googleAuth = new GoogleAuthController($conn);
    $googleAuth->callback();
} catch (Exception $e) {
    error_log("Exception in callback: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $_SESSION['errors'] = ['Error: ' . $e->getMessage()];
    header('Location: /auth/login.php');
    exit;
}
?>