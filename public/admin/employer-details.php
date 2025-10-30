<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';

if (!isAdmin()) {
    redirect('/home.php');
}

$employerId = $_GET['id'] ?? '';

if (empty($employerId)) {
    $_SESSION['errors'] = ['Employer ID is required'];
    redirect('/admin/employers.php');
}

// Get employer details
$stmt = $conn->prepare("
    SELECT u.*, e.*
    FROM users u
    INNER JOIN employers e ON u.uuid = e.user_uuid
    WHERE u.uuid = ? AND u.role = 'employer'
");
$stmt->bind_param("s", $employerId);
$stmt->execute();
$employer = $stmt->get_result()->fetch_assoc();

if (!$employer) {
    $_SESSION['errors'] = ['Employer not found'];
    redirect('/admin/employers.php');
}

// Get employer's jobs count - need to join with employers table since employer_uuid references employers.uuid, not users.uuid
$stmt = $conn->prepare("
    SELECT COUNT(*) as total_jobs
    FROM job_posts jp
    INNER JOIN employers e ON jp.employer_uuid = e.uuid
    WHERE e.user_uuid = ?
");
$stmt->bind_param("s", $employerId);
$stmt->execute();
$totalJobs = $stmt->get_result()->fetch_assoc()['total_jobs'];

$stmt = $conn->prepare("
    SELECT COUNT(*) as active_jobs
    FROM job_posts jp
    INNER JOIN employers e ON jp.employer_uuid = e.uuid
    WHERE e.user_uuid = ? AND jp.status = 'open'
");
$stmt->bind_param("s", $employerId);
$stmt->execute();
$activeJobs = $stmt->get_result()->fetch_assoc()['active_jobs'];

$jobs = []; // Don't fetch all jobs, just counts

$title = "Employer Details - " . htmlspecialchars($employer['company_name']);
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Employer Details</h1>
            <div class="flex space-x-4">
                <a href="/admin/employers.php"
                    class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                    ← Back to Employers
                </a>
                <?php if ($employer['is_verified']): ?>
                <form method="POST" action="/admin/employers.php" class="inline">
                    <input type="hidden" name="employer_uuid" value="<?php echo $employer['uuid']; ?>">
                    <input type="hidden" name="action" value="deactivate">
                    <button type="submit"
                        class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 transition"
                        onclick="return confirm('Are you sure you want to deactivate this employer?')">
                        Deactivate Employer
                    </button>
                </form>
                <?php else: ?>
                <form method="POST" action="/admin/employers.php" class="inline">
                    <input type="hidden" name="employer_uuid" value="<?php echo $employer['uuid']; ?>">
                    <input type="hidden" name="action" value="activate">
                    <button type="submit"
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
                        onclick="return confirm('Are you sure you want to activate this employer?')">
                        Activate Employer
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Employer Information -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div>
                <div class="flex items-center space-x-4 mb-4">
                    <div class="flex-shrink-0 h-16 w-16">
                        <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-2xl font-medium text-gray-700">
                                <?php echo strtoupper(substr($employer['company_name'], 0, 1)); ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900">
                            <?php echo htmlspecialchars($employer['company_name']); ?></h2>
                        <span
                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            <?php echo $employer['is_verified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo $employer['is_verified'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-gray-700"><?php echo htmlspecialchars($employer['email']); ?></span>
                    </div>

                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span
                            class="text-gray-700"><?php echo htmlspecialchars($employer['contact_phone'] ?? 'N/A'); ?></span>
                    </div>

                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span
                            class="text-gray-700"><?php echo htmlspecialchars($employer['industry'] ?? 'N/A'); ?></span>
                    </div>

                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span
                            class="text-gray-700"><?php echo htmlspecialchars($employer['location'] ?? 'N/A'); ?></span>
                    </div>

                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10m0 0l-2-2m2 2l2-2" />
                        </svg>
                        <span class="text-gray-700">Joined
                            <?php echo date('M j, Y', strtotime($employer['created_at'])); ?></span>
                    </div>

                    <?php if (!empty($employer['website'])): ?>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        <a href="<?php echo htmlspecialchars($employer['website']); ?>" target="_blank"
                            class="text-blue-600 hover:text-blue-800"><?php echo htmlspecialchars($employer['website']); ?></a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($employer['company_size'])): ?>
                <div class="mt-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Company Size</h3>
                    <p class="text-gray-700"><?php echo htmlspecialchars($employer['company_size']); ?> employees</p>
                </div>
                <?php endif; ?>
            </div>

            <div>
                <?php if (!empty($employer['description'])): ?>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Company Description</h3>
                <div class="text-gray-700 whitespace-pre-line mb-6">
                    <?php echo htmlspecialchars($employer['description']); ?></div>
                <?php endif; ?>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Statistics</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-blue-600"><?php echo $totalJobs; ?></p>
                            <p class="text-sm text-gray-600">Total Jobs</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold text-green-600"><?php echo $activeJobs; ?></p>
                            <p class="text-sm text-gray-600">Active Jobs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jobs Section -->
        <div class="border-t pt-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Job Posts</h3>
                <a href="/admin/jobs.php?employer=<?php echo urlencode($employer['company_name']); ?>"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    View Job Posts (<?php echo $totalJobs; ?>)
                </a>
            </div>
            <p class="text-gray-600">Click the button above to view and manage all job posts by this employer.</p>
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