<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../controllers/JobController.php';
require_once __DIR__ . '/../config/db.php';

// FIX: Use helper functions
if (!isAuthenticated() || !isJobSeeker()) {
    header("Location: index.php");
    exit;
}

$jobController = new JobController($conn);

// FIX: Use getUserId() helper
$userId = getUserId();
$jobseeker = $jobController->user->getUserProfile($userId);
if (!$jobseeker) {
    header("Location: index.php");
    exit;
}

// FIX: Get jobseeker_uuid correctly
$jobseekerUuid = $jobseeker['jobseeker_uuid'] ?? null;
if (!$jobseekerUuid) {
    header("Location: index.php");
    exit;
}

$savedJobs = $jobController->job->getSavedJobs($jobseekerUuid);

$title = "My Saved Jobs";
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">My Saved Jobs</h1>
        <p class="text-gray-600 mb-6">Jobs you've saved for later consideration.</p>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['errors'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php unset($_SESSION['errors']); ?>
        </div>
        <?php endif; ?>

        <?php if (empty($savedJobs)): ?>
        <div class="text-center py-12">
            <div class="text-gray-500 mb-4">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No saved jobs yet</h3>
            <p class="text-gray-500 mb-6">Start saving jobs that interest you to keep track of them here.</p>
            <a href="index.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                Browse Jobs
            </a>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($savedJobs as $job): ?>
            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($job['title']); ?>
                        </h3>
                        <div class="flex items-center space-x-4 text-sm text-gray-600 mb-3">
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <?php echo htmlspecialchars($job['company_name'] ?? 'Company Name'); ?>
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <?php echo htmlspecialchars($job['location']); ?>
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <?php echo htmlspecialchars(ucfirst($job['job_type'])); ?>
                            </span>
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10m0 0l-2-2m2 2l2-2" />
                                </svg>
                                Saved <?php echo date('M j, Y', strtotime($job['saved_at'])); ?>
                            </span>
                        </div>
                        <p class="text-gray-700 mb-3 line-clamp-2">
                            <?php echo htmlspecialchars(substr($job['job_description'], 0, 200)) . (strlen($job['job_description']) > 200 ? '...' : ''); ?>
                        </p>
                        <?php if (!empty($job['salary_range'])): ?>
                        <p class="text-green-600 font-medium mb-3">
                            <?php echo htmlspecialchars($job['salary_range']); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="flex space-x-2 ml-4">
                        <button onclick="unsaveJob('<?php echo $job['uuid']; ?>', this.closest('.space-y-4 > div'))"
                            class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            Unsave
                        </button>
                        <a href="employer/job-details.php?id=<?php echo $job['uuid']; ?>"
                            class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            View Details
                        </a>
                        <a href="employer/apply-job.php?id=<?php echo $job['uuid']; ?>"
                            class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            Apply Now
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
function unsaveJob(jobUuid, jobCard) {
    if (!confirm('Are you sure you want to unsave this job?')) {
        return;
    }

    fetch('save-job.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'job_uuid=' + encodeURIComponent(jobUuid) + '&csrf_token=' + encodeURIComponent(
                '<?php echo generate_csrf_token(); ?>') + '&action=unsave'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the job card from the page
                jobCard.remove();

                // Check if there are any more saved jobs
                const remainingJobs = document.querySelectorAll('.space-y-4 > div');
                if (remainingJobs.length === 0) {
                    // Reload the page to show the "no saved jobs" message
                    window.location.reload();
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>