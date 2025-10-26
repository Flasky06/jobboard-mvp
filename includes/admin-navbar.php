<nav class="bg-white shadow mb-6">
    <div class="container mx-auto flex justify-between items-center p-4">
        <a href="/jobs.php" class="font-bold text-lg text-blue-700">Job Portal - Admin</a>

        <ul class="flex gap-4 items-center">
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
            <li><a href="/jobs.php" class="hover:text-blue-600">Browse Jobs</a></li>
            <li><a href="/dashboard/admin-dashboard.php" class="hover:text-blue-600">Dashboard</a></li>
            <li><a href="/dashboard/Jobs.php" class="hover:text-blue-600">Manage Jobs</a></li>
            <li><a href="/dashboard/admin-profile.php" class="hover:text-blue-600">Profile</a></li>
            <li><a href="/admin/users.php" class="hover:text-blue-600">Manage Users</a></li>
            <li><a href="/auth/logout.php" class="hover:text-red-600">Logout</a></li>
            <li class="text-gray-500 text-sm">Hi, <?= htmlspecialchars($_SESSION['user_name']) ?> (Admin)</li>
            <?php else: ?>
            <li><a href="/auth/login.php" class="hover:text-blue-600">Login</a></li>
            <li><a href="/auth/register.php" class="hover:text-blue-600">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>