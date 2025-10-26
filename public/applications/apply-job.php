<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../controllers/ApllicationController.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/csrf.php';

$jobController = new JobController($conn);
$applicationController = new ApplicationController($conn);

$jobId = $_GET['id'] ?? 0;
$job = $jobController->job->getJobById($jobId);

if (!$job) {
    header("Location: ../index.php");
    exit;
}

$title = "Apply for " . htmlspecialchars($job['title']);

// Check if user is authenticated and is jobseeker
$canApply = isset($_SESSION['user_id']) && $_SESSION['role'] === 'jobseeker';
$hasApplied = false;

if ($canApply) {
    // Check if user has already applied
    $jobseeker = $jobController->user->getUserProfile($_SESSION['user_id']);
    if ($jobseeker && isset($jobseeker['uuid'])) {
        $application = $applicationController->checkApplicationStatus($jobId, $jobseeker['uuid']);
        if ($application) {
            $hasApplied = true;
        }
    }
}

include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-md mt-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Apply for: <?php echo htmlspecialchars($job['title']); ?></h1>
        <p class="text-gray-600"><?php echo htmlspecialchars($job['company_name'] ?? 'Company Name'); ?> •
            <?php echo htmlspecialchars($job['location']); ?></p>
    </div>

    <?php if ($canApply && !$hasApplied): ?>
    <form method="post" action="apply-job.php" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="job_id" value="<?php echo $job['uuid']; ?>">

        <div class="mb-6">
            <label for="cover_letter" class="block text-sm font-medium text-gray-700 mb-2">Cover Letter *</label>
            <textarea name="cover_letter" id="cover_letter" rows="8" required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Tell us why you're interested in this position and what makes you a good fit..."></textarea>
        </div>

        <div class="mb-6">
            <label for="resume_file" class="block text-sm font-medium text-gray-700 mb-2">Resume/CV (Optional)</label>
            <input type="file" name="resume_file" id="resume_file" accept=".pdf,.doc,.docx"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            <p class="text-sm text-gray-500 mt-1">Accepted formats: PDF, DOC, DOCX. Max size: 5MB</p>
        </div>

        <div class="flex space-x-4">
            <button type="submit"
                class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Submit Application
            </button>
            <a href="job-details.php?id=<?php echo $job['uuid']; ?>"
                class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-center">
                Cancel
            </a>
        </div>
    </form>
    <?php elseif ($hasApplied): ?>
    <div class="bg-green-50 border-l-4 border-green-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    <strong>You have already applied for this job!</strong><br>
                    Application status: <span class="font-medium"><?php echo ucfirst($application['status']); ?></span>
                </p>
            </div>
        </div>
    </div>
    <div class="mt-6">
        <a href="job-details.php?id=<?php echo $job['uuid']; ?>"
            class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-center block">
            ← Back to Job Details
        </a>
    </div>
    <?php elseif (!$canApply): ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>Login Required:</strong> You need to be logged in as a job seeker to apply for this job.
                    <a href="../auth/login.php"
                        class="font-medium underline text-yellow-700 hover:text-yellow-600">Login here</a>
                </p>
            </div>
        </div>
    </div>
    <div class="mt-6">
        <a href="job-details.php?id=<?php echo $job['uuid']; ?>"
            class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-center block">
            ← Back to Job Details
        </a>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>