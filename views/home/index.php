<?php
require_once __DIR__ . '/../middleware/auth.php';
$title = "Dashboard";
include __DIR__ . '/../includes/header.php';
?>

<h1 class="text-2xl font-semibold mb-4">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
<p>This is your job portal dashboard.</p>

<?php include __DIR__ . '/../includes/footer.php'; ?>