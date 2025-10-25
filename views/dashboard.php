<?php
require_once __DIR__ . '/../middleware/auth.php';
$title = "Dashboard";
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
        <p class="text-gray-600 mb-6">Welcome to your job portal dashboard. Here you can manage your job applications,
            update your profile, and explore new opportunities.</p>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="text-blue-600 text-2xl mb-2">📋</div>
                <h3 class="font-semibold text-gray-800 mb-1">My Applications</h3>
                <p class="text-sm text-gray-600">View and manage your job applications</p>
                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block">View
                    Applications →</a>
            </div>

            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <div class="text-green-600 text-2xl mb-2">🔍</div>
                <h3 class="font-semibold text-gray-800 mb-1">Browse Jobs</h3>
                <p class="text-sm text-gray-600">Find new job opportunities</p>
                <a href="#" class="text-green-600 hover:text-green-800 text-sm font-medium mt-2 inline-block">Browse
                    Jobs →</a>
            </div>

            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <div class="text-purple-600 text-2xl mb-2">👤</div>
                <h3 class="font-semibold text-gray-800 mb-1">My Profile</h3>
                <p class="text-sm text-gray-600">Update your profile information</p>
                <a href="#" class="text-purple-600 hover:text-purple-800 text-sm font-medium mt-2 inline-block">Edit
                    Profile →</a>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Activity</h2>
        <div class="space-y-4">
            <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                <div class="text-blue-600 text-xl mr-3">📝</div>
                <div class="flex-1">
                    <p class="text-gray-800 font-medium">Welcome to the Job Portal!</p>
                    <p class="text-gray-600 text-sm">Your account has been successfully created.</p>
                </div>
                <span class="text-gray-500 text-sm">Just now</span>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>