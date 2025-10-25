<?php
session_start();

function isAuthenticated() {
  return isset($_SESSION['user_id']);
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