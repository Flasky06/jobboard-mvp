<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) : 'Job Portal - Admin'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/css/styles.css" rel="stylesheet">
</head>

<body class="bg-gray-50">
    <?php include __DIR__ . '/admin-navbar.php'; ?>