<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/session.php';

// Check if user is employer
if ($_SESSION['role'] !== 'employer') {
  redirect('/home.php');
}

$title = "Employer Dashboard";
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Employer Dashboard</h1>
        <p class="text-gray-600 mb-6">Welcome back! Manage your job postings and find the perfect candidates.</p>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                <div class="text-blue-600 text-3xl mb-3">📝</div>
                <h3 class="text-xl font-semibold mb-2">Post a Job</h3>
                <p class="text-gray-600 mb-4">Create and publish new job opportunities</p>
                <a href="#" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Post Job</a>
            </div>

            <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                <div class="text-green-600 text-3xl mb-3">📋</div>
                <h3 class="text-xl font-semibold mb-2">My Jobs</h3>
                <p class="text-gray-600 mb-4">Manage your active job postings</p>
                <a href="#" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">View Jobs</a>
            </div>

            <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                <div class="text-purple-600 text-3xl mb-3">👥</div>
                <h3 class="text-xl font-semibold mb-2">Applications</h3>
                <p class="text-gray-600 mb-4">Review and manage job applications</p>
                <a href="#" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">View Applications</a>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Applications</h2>
            <div class="space-y-4">
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-blue-600 text-xl mr-3">👤</div>
                    <div class="flex-1">
                        <p class="text-gray-800 font-medium">John Doe applied for Senior Developer</p>
                        <p class="text-gray-600 text-sm">Applied 2 hours ago</p>
                    </div>
                    <a href="#" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition">Review</a>
                </div>

                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-green-600 text-xl mr-3">👤</div>
                    <div class="flex-1">
                        <p class="text-gray-800 font-medium">Jane Smith applied for UX Designer</p>
                        <p class="text-gray-600 text-sm">Applied 4 hours ago</p>
                    </div>
                    <a href="#" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition">Review</a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Job Performance</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Active Jobs</span>
                    <span class="font-semibold text-lg">5</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Applications</span>
                    <span class="font-semibold text-lg">47</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Applications This Week</span>
                    <span class="font-semibold text-lg">12</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Profile Views</span>
                    <span class="font-semibold text-lg">234</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>