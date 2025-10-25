<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/session.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
  redirect('/home.php');
}

$title = "Admin Dashboard";
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Admin Dashboard</h1>
        <p class="text-gray-600 mb-6">Welcome back, Admin! Manage your job portal platform.</p>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                <div class="text-blue-600 text-3xl mb-3">👥</div>
                <h3 class="text-xl font-semibold mb-2">User Management</h3>
                <p class="text-gray-600 mb-4">Manage users, employers, and job seekers</p>
                <a href="#" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Manage
                    Users</a>
            </div>

            <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                <div class="text-green-600 text-3xl mb-3">💼</div>
                <h3 class="text-xl font-semibold mb-2">Job Management</h3>
                <p class="text-gray-600 mb-4">Review and manage job postings</p>
                <a href="#" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">Manage
                    Jobs</a>
            </div>

            <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                <div class="text-purple-600 text-3xl mb-3">📊</div>
                <h3 class="text-xl font-semibold mb-2">Analytics</h3>
                <p class="text-gray-600 mb-4">View platform statistics and reports</p>
                <a href="#" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">View
                    Reports</a>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Activity</h2>
            <div class="space-y-4">
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-blue-600 text-xl mr-3">👤</div>
                    <div class="flex-1">
                        <p class="text-gray-800 font-medium">New user registered</p>
                        <p class="text-gray-600 text-sm">john.doe@example.com joined as job seeker</p>
                    </div>
                    <span class="text-gray-500 text-sm">2 hours ago</span>
                </div>

                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-green-600 text-xl mr-3">💼</div>
                    <div class="flex-1">
                        <p class="text-gray-800 font-medium">New job posted</p>
                        <p class="text-gray-600 text-sm">Senior Developer position at Tech Corp</p>
                    </div>
                    <span class="text-gray-500 text-sm">4 hours ago</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Quick Stats</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Users</span>
                    <span class="font-semibold text-lg">1,234</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Active Jobs</span>
                    <span class="font-semibold text-lg">89</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Applications Today</span>
                    <span class="font-semibold text-lg">23</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Employers</span>
                    <span class="font-semibold text-lg">156</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>