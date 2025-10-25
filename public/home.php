<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/session.php';

// Check if user is jobseeker
if ($_SESSION['role'] !== 'jobseeker') {
  redirect('/admin-dashboard.php');
}

$title = "Job Seeker Dashboard";
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Welcome,
            <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p class="text-gray-600 mb-6">Find your dream job and advance your career with our comprehensive job platform.
        </p>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                <div class="text-blue-600 text-3xl mb-3">🔍</div>
                <h3 class="text-xl font-semibold mb-2">Browse Jobs</h3>
                <p class="text-gray-600 mb-4">Discover new job opportunities</p>
                <a href="#" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Find Jobs</a>
            </div>

            <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                <div class="text-green-600 text-3xl mb-3">📋</div>
                <h3 class="text-xl font-semibold mb-2">My Applications</h3>
                <p class="text-gray-600 mb-4">Track your job applications</p>
                <a href="#" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">View
                    Applications</a>
            </div>

            <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                <div class="text-purple-600 text-3xl mb-3">👤</div>
                <h3 class="text-xl font-semibold mb-2">My Profile</h3>
                <p class="text-gray-600 mb-4">Update your profile and resume</p>
                <a href="#" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">Edit
                    Profile</a>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Recommended Jobs</h2>
            <div class="space-y-4">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800">Senior PHP Developer</h4>
                    <p class="text-gray-600 text-sm">Tech Solutions Inc. • Remote</p>
                    <p class="text-gray-500 text-sm mt-2">Looking for experienced PHP developer with Laravel
                        expertise...</p>
                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block">Apply
                        Now →</a>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800">Full Stack Developer</h4>
                    <p class="text-gray-600 text-sm">StartupXYZ • New York, NY</p>
                    <p class="text-gray-500 text-sm mt-2">Join our growing team and work on exciting projects...</p>
                    <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium mt-2 inline-block">Apply
                        Now →</a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Application Status</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-green-50 rounded">
                    <span class="text-gray-700">Applications Sent</span>
                    <span class="font-semibold text-green-600">12</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-blue-50 rounded">
                    <span class="text-gray-700">Interviews Scheduled</span>
                    <span class="font-semibold text-blue-600">3</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-yellow-50 rounded">
                    <span class="text-gray-700">Under Review</span>
                    <span class="font-semibold text-yellow-600">5</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-purple-50 rounded">
                    <span class="text-gray-700">Profile Views</span>
                    <span class="font-semibold text-purple-600">28</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>