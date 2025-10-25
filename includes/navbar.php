<nav class="bg-white shadow mb-6">
    <div class="container mx-auto flex justify-between items-center p-4">
        <a href="/index.php" class="font-bold text-lg text-blue-700">Job Portal</a>

        <ul class="flex gap-4 items-center">
            <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="/dashboard.php" class="hover:text-blue-600">Dashboard</a></li>
            <li><a href="/logout.php" class="hover:text-red-600">Logout</a></li>
            <li class="text-gray-500 text-sm">Hi, <?= htmlspecialchars($_SESSION['user_name']) ?></li>
            <?php else: ?>
            <li><a href="/login.php" class="hover:text-blue-600">Login</a></li>
            <li><a href="/register.php" class="hover:text-blue-600">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>