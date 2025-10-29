<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log that this file was accessed
error_log("=== /auth/google.php ACCESSED ===");
error_log("Request URI: " . $_SERVER['REQUEST_URI']);

require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';

error_log("Files loaded successfully");

require_once __DIR__ . '/../../config/google-oauth.php';

error_log("Google OAuth config loaded");

require_once __DIR__ . '/../../controllers/GoogleAuthController.php';

error_log("GoogleAuthController loaded");

try {
    // Initiate Google OAuth flow
    error_log("Creating GoogleAuthController instance...");
    $googleAuth = new GoogleAuthController($conn);

    error_log("Calling login method...");
    $googleAuth->login();

    error_log("Login method completed");

} catch (Exception $e) {
    error_log("EXCEPTION in google.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $_SESSION['errors'] = ['Failed to start Google login: ' . $e->getMessage()];
    header('Location: /auth/login.php');
    exit;
}
?>