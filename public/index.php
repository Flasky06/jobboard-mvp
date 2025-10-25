<?php
require_once __DIR__ . '/../helpers/session.php';
$title = "Job Portal - Find Your Dream Job";
include __DIR__ . '/../includes/header.php';
?>

<div class="hero bg-gradient-to-r from-blue-600 to-purple-700 text-white py-20">
    <div class="max-w-6xl mx-auto text-center px-4">
        <h2 class="text-5xl font-bold mb-6">Find Your Dream Job or Hire Top Talent</h2>
        <p class="text-xl mb-8">Connect employers with job seekers through our comprehensive platform</p>
        <div class="flex justify-center gap-4 flex-wrap">
            <?php if (isAuthenticated()): ?>
            <a href="dashboard.php"
                class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">Go
                to Dashboard</a>
            <?php else: ?>
            <a href="register.php"
                class="bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">Get
                Started</a>
            <a href="home.php"
                class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">Browse
                Jobs</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="features py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-gray-800 text-center mb-12">Choose Your Path</h2>
        <div class="grid md:grid-cols-2 gap-8 max-w-4xl mx-auto">
            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <div class="text-blue-600 text-5xl mb-4">👤</div>
                <h3 class="text-2xl font-semibold mb-4">Job Seekers</h3>
                <p class="text-gray-600 mb-6">Find your dream job and advance your career</p>
                <ul class="text-left text-gray-600 mb-6 space-y-2">
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span>Browse thousands of job
                        opportunities</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span>Easy application process
                    </li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span>Track your applications</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span>Build your professional
                        profile</li>
                </ul>
                <a href="register.php"
                    class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300 inline-block">Get
                    Started</a>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-md text-center">
                <div class="text-green-600 text-5xl mb-4">🏢</div>
                <h3 class="text-2xl font-semibold mb-4">Employers</h3>
                <p class="text-gray-600 mb-6">Find the perfect candidates for your team</p>
                <ul class="text-left text-gray-600 mb-6 space-y-2">
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span>Post jobs and reach
                        qualified candidates</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span>Manage applications
                        efficiently</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span>Access detailed candidate
                        profiles</li>
                    <li class="flex items-center"><span class="text-green-500 mr-2">✓</span>Streamline your hiring
                        process</li>
                </ul>
                <a href="register.php"
                    class="bg-green-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-green-700 transition duration-300 inline-block">Post
                    a Job</a>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>