<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../controllers/ApplicationController.php';
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

$title = htmlspecialchars($job['title']) . " - Job Details";

// FIX: Use helper functions
$canApply = isAuthenticated() && isJobSeeker();
$hasApplied = false;
$application = null;

if ($canApply) {
    // FIX: Use getUserId() helper
    $userId = getUserId();
    $jobseeker = $jobController->user->getUserProfile($userId);
    
    if ($jobseeker) {
        // FIX: Get jobseeker_uuid correctly
        $jobseekerUuid = $jobseeker['jobseeker_uuid'] ?? null;
        
        if ($jobseekerUuid) {
            $application = $applicationController->checkApplicationStatus($jobId, $jobseekerUuid);
            if ($application) {
                $hasApplied = true;
            }
        }
    }
}

// FIX: Check if user can edit (is employer who posted the job)
$canEdit = false;
if (isAuthenticated() && isEmployer()) {
    $userId = getUserId();
    $employer = $jobController->user->getUserProfile($userId);
    
    if ($employer) {
        $employerUuid = $employer['employer_uuid'] ?? null;
        if ($employerUuid && $employerUuid === $job['employer_uuid']) {
            $canEdit = true;
        }
    }
}

// Rest of your job-details.php file continues here...
?>
<?php
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-4xl mx-auto bg-white p-4 md:p-8 rounded-lg shadow-md mt-4 md:mt-8">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($job['title']); ?></h1>
            <div class="flex items-center space-x-4 text-sm text-gray-600 mb-3">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <?php echo htmlspecialchars($job['location']); ?>
                </span>
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?php echo htmlspecialchars(ucfirst($job['job_type'])); ?>
                </span>
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10m0 0l-2-2m2 2l2-2" />
                    </svg>
                    Posted <?php echo date('F j, Y', strtotime($job['created_at'])); ?>
                </span>
            </div>

            <!-- Company Info -->
            <?php
            $employer = null;
            if (!empty($job['employer_uuid'])) {
                $employerStmt = $conn->prepare("
                    SELECT e.*, u.email
                    FROM employers e
                    JOIN users u ON e.user_uuid = u.uuid
                    WHERE e.uuid = ?
                ");
                $employerStmt->bind_param("s", $job['employer_uuid']);
                $employerStmt->execute();
                $employer = $employerStmt->get_result()->fetch_assoc();
            }
            ?>

            <?php if ($employer): ?>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <?php if (!empty($employer['company_logo'])): ?>
                        <img src="<?php echo htmlspecialchars($employer['company_logo']); ?>" alt="Company Logo"
                            class="w-12 h-12 rounded-full border border-gray-400 object-cover">
                        <?php else: ?>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-lg font-medium text-blue-900">
                            <a href="/company-details.php?view=employer&id=<?php echo htmlspecialchars($employer['uuid']); ?>"
                                class="hover:text-blue-800 hover:underline">
                                <?php echo htmlspecialchars($employer['company_name'] ?? 'Company'); ?>
                            </a>
                        </h3>
                        <?php if (!empty($employer['industry'])): ?>
                        <p class="text-sm text-blue-700"><?php echo htmlspecialchars($employer['industry']); ?></p>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($canEdit): ?>
        <div class="hidden md:flex space-x-3">
            <a href="edit-job.php?id=<?php echo $job['uuid']; ?>"
                class="bg-yellow-500 text-white px-4 py-2 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Job
            </a>
            <form method="post" action="delete-job.php" class="inline"
                onsubmit="return confirm('Are you sure you want to delete this job? This action cannot be undone.')">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="job_id" value="<?php echo $job['uuid']; ?>">
                <button type="submit"
                    class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete Job
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($job['salary_range'])): ?>
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
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
                    <strong>Salary Range:</strong> <?php echo htmlspecialchars($job['salary_range']); ?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Mobile Action Buttons (Bottom) -->
        <?php if ($canEdit): ?>
        <div class="md:hidden bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Manage Job</h3>
            <div class="flex flex-col space-y-3">
                <a href="edit-job.php?id=<?php echo $job['uuid']; ?>"
                    class="bg-yellow-500 text-white px-4 py-3 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 text-center">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Job
                </a>
                <form method="post" action="delete-job.php"
                    onsubmit="return confirm('Are you sure you want to delete this job? This action cannot be undone.')">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="job_id" value="<?php echo $job['uuid']; ?>">
                    <button type="submit"
                        class="w-full bg-red-500 text-white px-4 py-3 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Job
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="lg:col-span-2">
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Job Description</h2>
                <div class="prose max-w-none text-gray-700">
                    <?php echo nl2br(htmlspecialchars($job['job_description'])); ?>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Requirements</h2>
                <div class="prose max-w-none text-gray-700">
                    <?php echo nl2br(htmlspecialchars($job['requirements_qualifications'])); ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-1">
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Job Summary</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Location</dt>
                        <dd class="text-sm text-gray-900"><?php echo htmlspecialchars($job['location']); ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Job Type</dt>
                        <dd class="text-sm text-gray-900"><?php echo htmlspecialchars(ucfirst($job['job_type'])); ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Posted Date</dt>
                        <dd class="text-sm text-gray-900"><?php echo date('F j, Y', strtotime($job['created_at'])); ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="text-sm">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="mt-6">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'jobseeker'): ?>
                <div class="flex space-x-4 mb-4">
                    <button onclick="toggleSaveJob('<?php echo $job['uuid']; ?>', this)"
                        class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        <span class="save-text">
                            <?php
                            $jobseeker = $jobController->user->getUserProfile($_SESSION['user_id']);
                            echo ($jobseeker && $jobController->job->isJobSaved($job['uuid'], $jobseeker['uuid'])) ? 'Unsave Job' : 'Save Job';
                            ?>
                        </span>
                    </button>
                </div>
                <?php endif; ?>

                <?php if ($canApply && !$hasApplied): ?>
                <a href="../applications/apply-job.php?id=<?php echo $job['uuid']; ?>"
                    class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 text-center block mb-4">
                    Apply for this Job
                </a>
                <?php elseif ($hasApplied): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
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
                                <strong>Application Submitted!</strong> Your application status: <span
                                    class="font-medium"><?php echo ucfirst($application['status']); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
                <?php elseif (!$canApply): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
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
                                <strong>Login Required:</strong> You need to be logged in as a job seeker to apply for
                                this job.
                                <a href="../auth/login.php"
                                    class="font-medium underline text-yellow-700 hover:text-yellow-600">Login here</a>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <a href="../index.php"
                    class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-center block">
                    ← Back to Job Listings
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.prose {
    color: #374151;
}

.prose p {
    margin-bottom: 1em;
}

.prose ul {
    list-style-type: disc;
    padding-left: 1.5em;
    margin-bottom: 1em;
}

.prose ol {
    list-style-type: decimal;
    padding-left: 1.5em;
    margin-bottom: 1em;
}
</style>

<script>
function toggleSaveJob(jobUuid, button) {
    fetch('../save-job.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'job_uuid=' + encodeURIComponent(jobUuid) + '&csrf_token=' + encodeURIComponent(
                '<?php echo generate_csrf_token(); ?>')
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const saveText = button.querySelector('.save-text');
                saveText.textContent = data.saved ? 'Unsave Job' : 'Save Job';
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>