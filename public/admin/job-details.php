<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('/home.php');
}

$jobController = new JobController($conn);
$jobId = $_GET['id'] ?? '';

if (empty($jobId)) {
    $_SESSION['errors'] = ['Job ID is required'];
    redirect('/admin/jobs.php');
}

$job = $jobController->getJobDetails($jobId);

if (!$job) {
    $_SESSION['errors'] = ['Job not found'];
    redirect('/admin/jobs.php');
}

// Get applications for this job
$stmt = $conn->prepare("
    SELECT a.*, js.fullName, js.phone, u.email as job_seeker_email
    FROM applications a
    LEFT JOIN job_seekers js ON a.job_seeker_uuid = js.uuid
    LEFT JOIN users u ON js.user_uuid = u.uuid
    WHERE a.job_uuid = ?
    ORDER BY a.applied_at DESC
");
if ($stmt) {
    $stmt->bind_param("s", $jobId);
    $stmt->execute();
    $applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $applications = [];
}

$title = "Job Details - " . htmlspecialchars($job['title']);
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Job Details</h1>
            <div class="flex space-x-4">
                <a href="/admin/jobs.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                    ← Back to Jobs
                </a>
                <?php if ($job['status'] === 'open'): ?>
                <form method="POST" action="/admin/jobs.php" class="inline">
                    <input type="hidden" name="job_uuid" value="<?php echo $job['uuid']; ?>">
                    <input type="hidden" name="action" value="deactivate">
                    <button type="submit"
                        class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 transition"
                        onclick="return confirm('Are you sure you want to close this job?')">
                        Close Job
                    </button>
                </form>
                <?php else: ?>
                <form method="POST" action="/admin/jobs.php" class="inline">
                    <input type="hidden" name="job_uuid" value="<?php echo $job['uuid']; ?>">
                    <input type="hidden" name="action" value="activate">
                    <button type="submit"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
                        onclick="return confirm('Are you sure you want to open this job?')">
                        Open Job
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Job Information -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4"><?php echo htmlspecialchars($job['title']); ?>
                </h2>

                <div class="space-y-3">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span
                            class="text-gray-700"><?php echo htmlspecialchars($job['company_name'] ?? 'Company Name'); ?></span>
                    </div>

                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-gray-700"><?php echo htmlspecialchars($job['location']); ?></span>
                    </div>

                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-700"><?php echo htmlspecialchars(ucfirst($job['job_type'])); ?></span>
                    </div>

                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10m0 0l-2-2m2 2l2-2" />
                        </svg>
                        <span class="text-gray-700">Posted
                            <?php echo date('M j, Y', strtotime($job['created_at'])); ?></span>
                    </div>

                    <div class="flex items-center">
                        <span
                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            <?php echo $job['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo ucfirst($job['status']); ?>
                        </span>
                    </div>
                </div>

                <?php if (!empty($job['salary_range'])): ?>
                <div class="mt-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Salary Range</h3>
                    <p class="text-green-600 font-medium"><?php echo htmlspecialchars($job['salary_range']); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Job Description</h3>
                <div class="text-gray-700 whitespace-pre-line"><?php echo htmlspecialchars($job['job_description']); ?>
                </div>

                <?php if (!empty($job['requirements_qualifications'])): ?>
                <h3 class="text-lg font-medium text-gray-900 mb-2 mt-6">Requirements & Qualifications</h3>
                <div class="text-gray-700 whitespace-pre-line">
                    <?php echo htmlspecialchars($job['requirements_qualifications']); ?></div>
                <?php endif; ?>

                <?php if (!empty($job['additional_information'])): ?>
                <h3 class="text-lg font-medium text-gray-900 mb-2 mt-6">Additional Information</h3>
                <div class="text-gray-700 whitespace-pre-line">
                    <?php echo htmlspecialchars($job['additional_information']); ?></div>
                <?php endif; ?>

                <?php if (!empty($job['application_deadline'])): ?>
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                    <h3 class="text-lg font-medium text-yellow-800 mb-2">Application Deadline</h3>
                    <p class="text-yellow-700"><?php echo date('F j, Y', strtotime($job['application_deadline'])); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Applications Section -->
        <div class="border-t pt-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Applications (<?php echo count($applications); ?>)</h3>

            <?php if (empty($applications)): ?>
            <div class="text-center py-8">
                <div class="text-gray-500 mb-4">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No applications yet</h3>
                <p class="text-gray-500">Applications will appear here once job seekers apply.</p>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($applications as $application): ?>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h4 class="text-lg font-medium text-gray-900">
                                <?php echo htmlspecialchars($application['fullName'] ?? 'Unknown Applicant'); ?>
                            </h4>
                            <div class="mt-2 space-y-1">
                                <p class="text-sm text-gray-600">
                                    <strong>Email:</strong>
                                    <?php echo htmlspecialchars($application['job_seeker_email'] ?? 'N/A'); ?>
                                </p>
                                <?php if (!empty($application['phone'])): ?>
                                <p class="text-sm text-gray-600">
                                    <strong>Phone:</strong> <?php echo htmlspecialchars($application['phone']); ?>
                                </p>
                                <?php endif; ?>
                                <p class="text-sm text-gray-600">
                                    <strong>Applied:</strong>
                                    <?php echo date('M j, Y \a\t g:i A', strtotime($application['applied_at'])); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <strong>Status:</strong>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        <?php echo $application['status'] === 'reviewed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo ucfirst($application['status']); ?>
                                    </span>
                                </p>
                            </div>
                            <?php if (!empty($application['cover_letter'])): ?>
                            <div class="mt-3">
                                <h5 class="text-sm font-medium text-gray-900">Cover Letter</h5>
                                <p class="text-sm text-gray-700 mt-1">
                                    <?php echo htmlspecialchars(substr($application['cover_letter'], 0, 200)) . (strlen($application['cover_letter']) > 200 ? '...' : ''); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="ml-4 flex space-x-2">
                            <?php if (!empty($application['resume_file'])): ?>
                            <a href="/applications/download-resume.php?application_id=<?php echo $application['uuid']; ?>"
                                class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition">
                                Download Resume
                            </a>
                            <?php endif; ?>
                            <a href="/applications/application-details.php?id=<?php echo $application['uuid']; ?>"
                                class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700 transition">
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

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>