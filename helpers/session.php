<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

function isJobSeeker() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'jobseeker';
}

function isEmployer() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'employer';
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function setUserSession($user) {
    $_SESSION['user_id'] = $user['uuid'];
    $_SESSION['user_name'] = $user['name'] ?? $user['email'] ?? explode('@', $user['email'] ?? '')[0];
    $_SESSION['role'] = $user['role'];
    $_SESSION['is_verified'] = $user['is_verified'] ?? false;

    // Regenerate session ID for security
    session_regenerate_id(true);
}

function clearUserSession() {
    // Unset all session variables
    $_SESSION = [];

    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    // Destroy the session
    session_destroy();
}

function redirect($url) {
    // Ensure URL starts with /
    if (!str_starts_with($url, '/')) {
        $url = '/' . $url;
    }
    header("Location: " . $url);
    exit;
}

function requireAuth() {
    if (!isAuthenticated()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect('/auth/login.php');
    }
}

function requireRole($role) {
    requireAuth();
    if (getUserRole() !== $role) {
        redirect('/auth/login.php');
    }
}

function requireVerified() {
    requireAuth();
    if (!($_SESSION['is_verified'] ?? false)) {
        $_SESSION['errors'] = ['Please verify your email address before accessing this page.'];
        redirect('/auth/login.php');
    }
}