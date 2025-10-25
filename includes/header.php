<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Job Portal' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 text-gray-800">

    <?php include __DIR__ . '/navbar.php'; ?>
    <main class="container mx-auto p-4">