<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../controllers/ApplicationController.php';

// Check if user is employer
if (!isEmployer()) {
    redirect('/home.php');
}

$jobId = $_GET['id'] ?? null;

if (!$jobId) {
    $_SESSION['errors'] = ['Job ID is required'];
    header("Location: ../jobs/jobs.php");
    exit;
}

$jobController = new JobController($conn);
$applicationController = new ApplicationController($conn);
$userId = getUserId();
$employer = $jobController->user->getUserProfile($userId);
$employerUuid = $employer['employer_uuid'] ?? null;

if (!$employerUuid) {
    $_SESSION['errors'] = ['Employer profile not found. Please complete your profile.'];
    header("Location: ../jobs/jobs.php");
    exit;
}

// Get job details and verify ownership
$job = $jobController->job->getJobById($jobId);

if (!$job || $job['employer_uuid'] !== $employerUuid) {
    $_SESSION['errors'] = ['Job not found or you do not have permission to view it'];
    header("Location: ../jobs/jobs.php");
    exit;
}

// Get all applications for this job
$applications = $applicationController->application->getJobApplications($jobId, $employerUuid);

// Calculate statistics
$stats = [
    'total' => count($applications),
    'pending' => 0,
    'reviewed' => 0,
    'accepted' => 0,
    'rejected' => 0
];

foreach ($applications as $app) {
    $stats[$app['status']]++;
}

$title = "Applications for " . htmlspecialchars($job['title']);
include __DIR__ . '/../../includes/employer-header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Job Applications</h1>
                <p class="text-lg text-gray-600">
                    Applications for <span class="font-semibold"><?php echo htmlspecialchars($job['title']); ?></span>
                </p>
            </div>
            <div class="flex gap-3">
                <a href="../jobs/job-details.php?id=<?php echo $job['uuid']; ?>"
                    class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                    View Job Details
                </a>
                <a href="applications.php"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    All Applications
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Reviewed</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo $stats['reviewed']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Accepted</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo $stats['accepted']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Rejected</p>
                        <p class="text-2xl font-bold text-red-600"><?php echo $stats['rejected']; ?></p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <ul class="list-disc list-inside">
            <?php foreach ($_SESSION['errors'] as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                <select id="status-filter"
                    class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Applications</option>
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="accepted">Accepted</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
            <div class="ml-auto">
                <p class="text-sm text-gray-600">
                    Showing <span id="visible-count"><?php echo count($applications); ?></span> of
                    <?php echo count($applications); ?> applications
                </p>
            </div>
        </div>
    </div>

    <!-- Applications List -->
    <?php if (empty($applications)): ?>
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No applications yet</h3>
        <p class="text-gray-500 mb-6">This job hasn't received any applications yet</p>
        <a href="../jobs/jobs.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
            View All Jobs
        </a>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="divide-y divide-gray-200">
            <?php foreach ($applications as $app): ?>
            <div class="p-6 hover:bg-gray-50 transition-colors application-card"
                data-status="<?php echo $app['status']; ?>">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div
                                class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                <?php 
                                $jobseekerStmt = $conn->prepare("SELECT fullName FROM job_seekers WHERE uuid = ?");
                                $jobseekerStmt->bind_param("s", $app['job_seeker_uuid']);
                                $jobseekerStmt->execute();
                                $jobseekerResult = $jobseekerStmt->get_result()->fetch_assoc();
                                $fullName = $jobseekerResult['fullName'] ?? 'Unknown';
                                echo strtoupper(substr($fullName, 0, 1)); 
                                ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($fullName); ?>
                                </h3>
                                <?php if (!empty($app['professional_title'])): ?>
                                <p class="text-sm text-gray-600">
                                    <?php echo htmlspecialchars($app['professional_title']); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 text-sm text-gray-500 ml-15">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Applied <?php echo date('M j, Y', strtotime($app['applied_at'])); ?>
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
                            <?php if (!empty($app['phone'])): ?>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <?php echo htmlspecialchars($app['phone']); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
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
                        <a href="../applications/application-details.php?id=<?php echo $app['uuid']; ?>"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('status-filter');
    const applicationCards = document.querySelectorAll('.application-card');
    const visibleCount = document.getElementById('visible-count');

    function filterApplications() {
        const selectedStatus = statusFilter.value;
        let visible = 0;

        applicationCards.forEach(card => {
            const cardStatus = card.dataset.status;
            const statusMatch = selectedStatus === 'all' || cardStatus === selectedStatus;

            if (statusMatch) {
                card.style.display = 'block';
                visible++;
            } else {
                card.style.display = 'none';
            }
        });

        visibleCount.textContent = visible;
    }

    statusFilter.addEventListener('change', filterApplications);
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>