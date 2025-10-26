<?php
require_once __DIR__ . '/../helpers/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Job Portal - Employer'; ?></title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/css/styles.css" rel="stylesheet">
</head>

<body class="bg-gray-50 text-gray-800">

    <?php include __DIR__ . '/employer-navbar.php'; ?>
    <main class="container mx-auto p-4">