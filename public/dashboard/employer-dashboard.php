<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../controllers/ApplicationController.php';

// Check if user is employer
if (!isEmployer()) {
  redirect('/home.php');
}

$jobController = new JobController($conn);
$applicationController = new ApplicationController($conn);
$userId = getUserId();
$employer = $jobController->user->getUserProfile($userId);
$employerUuid = $employer['employer_uuid'] ?? null;

// Get dashboard statistics
$totalJobs = 0;
$activeJobs = 0;
$closedJobs = 0;
$totalApplications = 0;
$pendingApplications = 0;
$recentApplications = [];

if ($employerUuid) {
    $jobs = $jobController->job->getJobsByEmployer($employerUuid);
    $totalJobs = count($jobs);

    foreach ($jobs as $job) {
        if ($job['status'] === 'open') {
            $activeJobs++;
        } else {
            $closedJobs++;
        }
    }

    // Get application stats using ApplicationController
    $appStats = $applicationController->getApplicationStats($employerUuid);
    $totalApplications = $appStats['total_applications'];
    $pendingApplications = $appStats['pending_applications'];

    // Get recent applications
    $recentApplications = $applicationController->getRecentApplications($employerUuid, 5);
}

$companyName = $employer['company_name'] ?? 'Your Company';

$title = "Employer Dashboard";
include __DIR__ . '/../../includes/employer-header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">Welcome back, <?php echo htmlspecialchars($companyName); ?>
        </h1>
        <p class="text-lg text-gray-600">Here's what's happening with your job postings today</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Active Jobs -->
        <div
            class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-blue-100 text-sm font-medium uppercase tracking-wide">Active Jobs</p>
                    <p class="text-4xl font-bold mt-2"><?php echo $activeJobs; ?></p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-lg p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-center text-sm">
                <span class="text-blue-100">Total: <?php echo $totalJobs; ?> jobs</span>
            </div>
        </div>

        <!-- Total Applications -->
        <div
            class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-green-100 text-sm font-medium uppercase tracking-wide">Total Applications</p>
                    <p class="text-4xl font-bold mt-2"><?php echo $totalApplications; ?></p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-lg p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-center text-sm">
                <span class="text-green-100">All time candidates</span>
            </div>
        </div>

        <!-- Pending Reviews -->
        <div
            class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-amber-100 text-sm font-medium uppercase tracking-wide">Pending Review</p>
                    <p class="text-4xl font-bold mt-2"><?php echo $pendingApplications; ?></p>
                </div>
                <div class="bg-amber-400 bg-opacity-30 rounded-lg p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-center text-sm">
                <span class="text-amber-100">Awaiting your review</span>
            </div>
        </div>

        <!-- Closed Jobs -->
        <div
            class="bg-gradient-to-br from-gray-600 to-gray-700 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition-transform duration-200">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-gray-100 text-sm font-medium uppercase tracking-wide">Closed Jobs</p>
                    <p class="text-4xl font-bold mt-2"><?php echo $closedJobs; ?></p>
                </div>
                <div class="bg-gray-500 bg-opacity-30 rounded-lg p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex items-center text-sm">
                <span class="text-gray-100">Completed postings</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Quick Actions -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="/jobs/post-job.php"
                        class="block w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-4 rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-md hover:shadow-lg text-center font-semibold">
                        Post New Job
                    </a>
                    <a href="/jobs/jobs.php"
                        class="block w-full bg-white border-2 border-gray-300 text-gray-700 px-6 py-4 rounded-lg hover:border-blue-500 hover:text-blue-600 transition-all duration-200 text-center font-semibold">
                        Manage Jobs
                    </a>
                    <a href="/applications/applications.php"
                        class="block w-full bg-white border-2 border-gray-300 text-gray-700 px-6 py-4 rounded-lg hover:border-green-500 hover:text-green-600 transition-all duration-200 text-center font-semibold">
                        View Applications
                    </a>
                    <a href="/dashboard/employer-profile.php"
                        class="block w-full bg-white border-2 border-gray-300 text-gray-700 px-6 py-4 rounded-lg hover:border-purple-500 hover:text-purple-600 transition-all duration-200 text-center font-semibold">
                        Update Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">Recent Applications</h2>
                    <a href="/applications/applications.php"
                        class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                        View All →
                    </a>
                </div>

                <?php if (empty($recentApplications)): ?>
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-500 text-lg">No applications yet</p>
                    <p class="text-gray-400 text-sm mt-2">Applications will appear here once candidates apply</p>
                </div>
                <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($recentApplications as $app): ?>
                    <div
                        class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-md transition-all duration-200">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div
                                        class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                        <?php echo strtoupper(substr($app['fullName'] ?? 'U', 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900">
                                            <?php echo htmlspecialchars($app['fullName'] ?? 'Unknown Applicant'); ?>
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            Applied for <span
                                                class="font-medium text-gray-900"><?php echo htmlspecialchars($app['job_title']); ?></span>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-gray-500 ml-13">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <?php 
                                                $time_diff = time() - strtotime($app['applied_at']);
                                                if ($time_diff < 3600) {
                                                    echo floor($time_diff / 60) . ' minutes ago';
                                                } elseif ($time_diff < 86400) {
                                                    echo floor($time_diff / 3600) . ' hours ago';
                                                } else {
                                                    echo floor($time_diff / 86400) . ' days ago';
                                                }
                                                ?>
                                    </span>
                                    <?php if (!empty($app['location'])): ?>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                        <?php echo htmlspecialchars($app['location']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'reviewed' => 'bg-blue-100 text-blue-800',
                                            'accepted' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800'
                                        ];
                                        $statusColor = $statusColors[$app['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $statusColor; ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                                <a href="/applications/application-details.php?id=<?php echo $app['uuid']; ?>"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Tips Section -->
    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl shadow-md p-6 border border-indigo-100">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Tips for Attracting Top Talent</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="font-semibold text-gray-900 mb-2">Write Clear Job Descriptions</h3>
                <p class="text-sm text-gray-600">Be specific about requirements and responsibilities to attract the
                    right candidates.</p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="font-semibold text-gray-900 mb-2">Respond Quickly</h3>
                <p class="text-sm text-gray-600">Review applications within 48 hours to show professionalism and keep
                    candidates engaged.</p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="font-semibold text-gray-900 mb-2">Update Your Profile</h3>
                <p class="text-sm text-gray-600">A complete company profile builds trust and attracts more quality
                    applications.</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>