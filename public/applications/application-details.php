<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../controllers/ApllicationController.php';

// Check if user is employer
if (!isEmployer()) {
    redirect('/home.php');
}

$applicationId = $_GET['id'] ?? null;
if (!$applicationId) {
    header("Location: applications.php");
    exit;
}

$jobController = new JobController($conn);
$applicationController = new ApplicationController($conn);
$userId = getUserId();
$employer = $jobController->user->getUserProfile($userId);
$employerUuid = $employer['employer_uuid'] ?? null;

// Get application details
$application = null;
$job = null;
$jobseeker = null;

if ($employerUuid) {
    // Get application details using ApplicationController
    $application = $applicationController->getApplicationDetails($applicationId, $employerUuid);

    if ($application) {
        $job = $application['job'];
        $jobseeker = $application['jobseeker'];
    }
}

if (!$application || !$job || !$jobseeker) {
    header("Location: applications.php");
    exit;
}

$title = "Application Details - " . htmlspecialchars($jobseeker['fullName'] ?? 'Unknown');
include __DIR__ . '/../../includes/employer-header.php';
?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Application Details</h1>
                <p class="text-lg text-gray-600">
                    Review application for <span
                        class="font-semibold"><?php echo htmlspecialchars($job['title']); ?></span>
                </p>
            </div>
            <a href="applications.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                ← Back to Applications
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Applicant Information -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Applicant Information</h2>

                <div class="flex items-start gap-6 mb-6">
                    <div
                        class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                        <?php echo strtoupper(substr($jobseeker['fullName'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                            <?php echo htmlspecialchars($jobseeker['fullName'] ?? 'Unknown Applicant'); ?>
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <?php if (!empty($jobseeker['email'])): ?>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <a href="mailto:<?php echo htmlspecialchars($jobseeker['email']); ?>"
                                    class="text-blue-600 hover:text-blue-700">
                                    <?php echo htmlspecialchars($jobseeker['email']); ?>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($jobseeker['phone'])): ?>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <a href="tel:<?php echo htmlspecialchars($jobseeker['phone']); ?>"
                                    class="text-blue-600 hover:text-blue-700">
                                    <?php echo htmlspecialchars($jobseeker['phone']); ?>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($jobseeker['location'])): ?>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <span><?php echo htmlspecialchars($jobseeker['location']); ?></span>
                            </div>
                            <?php endif; ?>

                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Applied
                                    <?php echo date('M j, Y \a\t g:i A', strtotime($application['applied_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Status -->
                <div class="border-t pt-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 mb-2">Application Status</h4>
                            <?php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'reviewed' => 'bg-blue-100 text-blue-800',
                                'accepted' => 'bg-green-100 text-green-800',
                                'rejected' => 'bg-red-100 text-red-800'
                            ];
                            $statusColor = $statusColors[$application['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?php echo $statusColor; ?>">
                                <?php echo ucfirst($application['status']); ?>
                            </span>
                        </div>

                        <!-- Status Update Form -->
                        <form method="post" action="update-application-status.php" class="flex items-center gap-3">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="application_id" value="<?php echo $application['uuid']; ?>">
                            <select name="status"
                                class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending"
                                    <?php echo $application['status'] === 'pending' ? 'selected' : ''; ?>>Pending
                                </option>
                                <option value="reviewed"
                                    <?php echo $application['status'] === 'reviewed' ? 'selected' : ''; ?>>Reviewed
                                </option>
                                <option value="accepted"
                                    <?php echo $application['status'] === 'accepted' ? 'selected' : ''; ?>>Accepted
                                </option>
                                <option value="rejected"
                                    <?php echo $application['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected
                                </option>
                            </select>
                            <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                                Update Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cover Letter -->
            <?php if (!empty($application['cover_letter'])): ?>
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Cover Letter</h2>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700 whitespace-pre-wrap">
                        <?php echo nl2br(htmlspecialchars($application['cover_letter'])); ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Resume -->
            <?php if (!empty($application['resume_file'])): ?>
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Resume</h2>
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <div>
                        <p class="font-medium text-gray-900">Resume Document</p>
                        <p class="text-sm text-gray-500">Click to download</p>
                    </div>
                    <a href="/uploads/resumes/<?php echo htmlspecialchars($application['resume_file']); ?>"
                        target="_blank"
                        class="ml-auto bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        Download
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Job Information -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Job Title</dt>
                        <dd class="text-sm text-gray-900"><?php echo htmlspecialchars($job['title']); ?></dd>
                    </div>
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
                        <dd class="text-sm text-gray-900"><?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="text-sm">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <?php echo ucfirst($job['status']); ?>
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="job-details.php?id=<?php echo $job['uuid']; ?>"
                        class="block w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-center">
                        View Job Details
                    </a>
                    <a href="edit-job.php?id=<?php echo $job['uuid']; ?>"
                        class="block w-full bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition text-center">
                        Edit Job
                    </a>
                    <a href="mailto:<?php echo htmlspecialchars($jobseeker['email']); ?>"
                        class="block w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition text-center">
                        Contact Applicant
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>