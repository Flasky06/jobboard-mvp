<?php
session_start();

function isAuthenticated() {
  return isset($_SESSION['user_id']);
}

function isJobSeeker() {
  return isset($_SESSION['role']) && $_SESSION['role'] === 'jobseeker';
}

function isEmployer() {
  return isset($_SESSION['role']) && $_SESSION['role'] === 'employer';
}

function getUserId() {
  return $_SESSION['user_id'] ?? null;
}

function setUserSession($user) {
  $_SESSION['user_id'] = $user['uuid'];
  $_SESSION['user_name'] = $user['name'] ?? $user['email'];
  $_SESSION['role'] = $user['role'];
}

function redirect($url) {
  // Ensure URL starts with /
  if (!str_starts_with($url, '/')) {
    $url = '/' . $url;
  }
  header("Location: " . $url);
  exit;
}
?>