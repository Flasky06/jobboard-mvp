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

// Get all applications for this employer
$allApplications = [];
$jobs = [];

if ($employerUuid) {
    $jobs = $jobController->job->getJobsByEmployer($employerUuid);
    $allApplications = $applicationController->getEmployerApplications($employerUuid);
}

$title = "Applications";
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">

    <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($_SESSION['success']); ?>
    </div>
    <?php unset($_SESSION['success']); ?>
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

    <!-- Info Banner -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    <strong>Auto-review enabled:</strong> Applications automatically change to "Reviewed" when you view
                    their details.
                </p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status-filter"
                    class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Applications</option>
                    <option value="pending">Pending (New)</option>
                    <option value="reviewed">Reviewed</option>
                </select>
            </div>
            <div>
                <label for="job-filter" class="block text-sm font-medium text-gray-700 mb-1">Job</label>
                <select id="job-filter"
                    class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">All Jobs</option>
                    <?php foreach ($jobs as $job): ?>
                    <option value="<?php echo $job['uuid']; ?>"><?php echo htmlspecialchars($job['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Applications List -->
    <?php if (empty($allApplications)): ?>
    <div class="bg-white rounded-xl shadow-md p-6 md:p-12 text-center mobile-p-4">
        <svg class="w-12 h-12 md:w-16 md:h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-lg md:text-xl font-medium text-gray-900 mb-2">No applications yet</h3>
        <p class="text-sm md:text-base text-gray-500 mb-4 md:mb-6">Applications will appear here once candidates apply
            to your jobs</p>
        <a href="../jobs/post-job.php"
            class="bg-blue-600 text-white px-4 py-2 md:px-6 md:py-2 rounded hover:bg-blue-700 transition mobile-btn-full">
            Post a Job
        </a>
    </div>
    <?php else: ?>
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-4 py-3 md:px-6 md:py-4 border-b border-gray-200">
            <h2 class="text-lg md:text-xl font-semibold text-gray-900">
                All Applications (<?php echo count($allApplications); ?>)
            </h2>
        </div>

        <div class="divide-y divide-gray-200">
            <?php foreach ($allApplications as $app): ?>
            <div class="p-4 md:p-6 hover:bg-gray-50 transition-colors application-card mobile-p-4"
                data-status="<?php echo $app['status']; ?>" data-job="<?php echo $app['job_uuid']; ?>">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div
                                class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm md:text-lg">
                                <?php echo strtoupper(substr($app['fullName'] ?? 'U', 0, 1)); ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-base md:text-lg font-semibold text-gray-900 truncate">
                                    <?php echo htmlspecialchars($app['fullName'] ?? 'Unknown Applicant'); ?>
                                </h3>
                                <p class="text-xs md:text-sm text-gray-600 truncate">
                                    Applied for <span
                                        class="font-medium text-gray-900"><?php echo htmlspecialchars($app['job_title']); ?></span>
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex flex-col md:flex-row md:items-center gap-2 md:gap-6 text-xs md:text-sm text-gray-500 ml-0 md:ml-15">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <?php echo date('M j, Y \a\t g:i A', strtotime($app['applied_at'])); ?>
                            </span>
                            <?php if (!empty($app['location'])): ?>
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <span class="truncate"><?php echo htmlspecialchars($app['location']); ?></span>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-3 mobile-btn-stack">
                        <?php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'reviewed' => 'bg-blue-100 text-blue-800',
                            'accepted' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800'
                        ];
                        $statusColor = $statusColors[$app['status']] ?? 'bg-gray-100 text-gray-800';

                        $statusLabels = [
                            'pending' => 'New',
                            'reviewed' => 'Reviewed',
                            'accepted' => 'Accepted',
                            'rejected' => 'Rejected'
                        ];
                        $statusLabel = $statusLabels[$app['status']] ?? ucfirst($app['status']);
                        ?>
                        <span
                            class="px-2 py-1 md:px-3 md:py-1 rounded-full text-xs font-semibold <?php echo $statusColor; ?> self-start md:self-center">
                            <?php echo $statusLabel; ?>
                        </span>
                        <a href="application-details.php?id=<?php echo $app['uuid']; ?>"
                            class="px-3 py-2 md:px-4 md:py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-xs md:text-sm font-medium text-center mobile-btn-full">
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
// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('status-filter');
    const jobFilter = document.getElementById('job-filter');
    const applicationCards = document.querySelectorAll('.application-card');

    function filterApplications() {
        const selectedStatus = statusFilter.value;
        const selectedJob = jobFilter.value;

        applicationCards.forEach(card => {
            const cardStatus = card.dataset.status;
            const cardJob = card.dataset.job;

            const statusMatch = selectedStatus === 'all' || cardStatus === selectedStatus;
            const jobMatch = selectedJob === 'all' || cardJob === selectedJob;

            if (statusMatch && jobMatch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    statusFilter.addEventListener('change', filterApplications);
    jobFilter.addEventListener('change', filterApplications);
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>