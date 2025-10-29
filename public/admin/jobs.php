<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    redirect('/home.php');
}

// Handle job status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $job_uuid = $_POST['job_uuid'] ?? '';
    $action = $_POST['action'];

    if ($action === 'deactivate' || $action === 'activate') {
        $status = $action === 'activate' ? 1 : 0;
        $stmt = $conn->prepare("UPDATE jobs SET is_active = ? WHERE uuid = ?");
        if ($stmt) {
            $stmt->bind_param("is", $status, $job_uuid);
            $stmt->execute();
            $_SESSION['success'] = "Job " . ($action === 'activate' ? 'activated' : 'deactivated') . " successfully";
        }
    } elseif ($action === 'delete') {
        // Soft delete job
        $stmt = $conn->prepare("UPDATE jobs SET is_active = 0 WHERE uuid = ?");
        if ($stmt) {
            $stmt->bind_param("s", $job_uuid);
            $stmt->execute();
            $_SESSION['success'] = "Job deleted successfully";
        }
    }

    redirect('/admin/jobs');
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$employer_filter = $_GET['employer'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "
    SELECT j.uuid, j.title, j.job_description, j.job_type, j.location, j.salary_range,
           j.is_active, j.created_at, j.updated_at,
           e.company_name, e.contact_email,
           COUNT(a.uuid) as application_count
    FROM jobs j
    LEFT JOIN employers e ON j.employer_uuid = e.uuid
    LEFT JOIN applications a ON j.uuid = a.job_uuid
";

$params = [];
$types = '';
$where_conditions = [];

if ($status_filter !== '') {
    $where_conditions[] = "j.is_active = ?";
    $params[] = (int)$status_filter;
    $types .= 'i';
}

if ($employer_filter) {
    $where_conditions[] = "e.company_name LIKE ?";
    $params[] = "%$employer_filter%";
    $types .= 's';
}

if ($search) {
    $where_conditions[] = "(j.title LIKE ? OR j.job_description LIKE ? OR e.company_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " GROUP BY j.uuid, j.title, j.job_description, j.job_type, j.location, j.salary_range,
                   j.is_active, j.created_at, j.updated_at, e.company_name, e.contact_email
           ORDER BY j.created_at DESC";

// Execute query
$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $jobs = [];
}

// Get job statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_jobs FROM jobs");
$totalJobs = $stmt ? ($stmt->execute() ? $stmt->get_result()->fetch_assoc()['total_jobs'] : 0) : 0;

$stmt = $conn->prepare("SELECT COUNT(*) as active_jobs FROM jobs WHERE is_active = 1");
$activeJobs = $stmt ? ($stmt->execute() ? $stmt->get_result()->fetch_assoc()['active_jobs'] : 0) : 0;

$stmt = $conn->prepare("SELECT COUNT(*) as total_applications FROM applications");
$totalApplications = $stmt ? ($stmt->execute() ? $stmt->get_result()->fetch_assoc()['total_applications'] : 0) : 0;

$title = "Job Management";
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Job Management</h1>
            <a href="/admin-dashboard" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                ← Back to Dashboard
            </a>
        </div>

        <!-- Job Statistics -->
        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h3 class="text-lg font-semibold text-blue-800">Total Jobs</h3>
                <p class="text-2xl font-bold text-blue-600"><?php echo $totalJobs; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <h3 class="text-lg font-semibold text-green-800">Active Jobs</h3>
                <p class="text-2xl font-bold text-green-600"><?php echo $activeJobs; ?></p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <h3 class="text-lg font-semibold text-purple-800">Total Applications</h3>
                <p class="text-2xl font-bold text-purple-600"><?php echo $totalApplications; ?></p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="border border-gray-300 rounded px-3 py-2">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Employer</label>
                    <input type="text" name="employer" value="<?php echo htmlspecialchars($employer_filter); ?>"
                        placeholder="Company name..." class="border border-gray-300 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Job title or description..." class="border border-gray-300 rounded px-3 py-2">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>

        <!-- Jobs Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Applications</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Posted</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="max-w-xs">
                                <div class="text-sm font-medium text-gray-900 truncate">
                                    <?php echo htmlspecialchars($job['title']); ?>
                                </div>
                                <div class="text-sm text-gray-500 line-clamp-2">
                                    <?php echo htmlspecialchars(substr($job['job_description'], 0, 100)) . (strlen($job['job_description']) > 100 ? '...' : ''); ?>
                                </div>
                                <?php if ($job['salary_range']): ?>
                                <div class="text-sm text-green-600 font-medium">
                                    <?php echo htmlspecialchars($job['salary_range']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($job['company_name'] ?: 'N/A'); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($job['contact_email'] ?: ''); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars(ucfirst($job['job_type'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($job['location']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $job['application_count']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                <?php echo $job['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $job['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <?php if ($job['is_active']): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="job_uuid" value="<?php echo $job['uuid']; ?>">
                                    <input type="hidden" name="action" value="deactivate">
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900"
                                        onclick="return confirm('Are you sure you want to deactivate this job?')">
                                        Deactivate
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="job_uuid" value="<?php echo $job['uuid']; ?>">
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="text-green-600 hover:text-green-900"
                                        onclick="return confirm('Are you sure you want to activate this job?')">
                                        Activate
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="job_uuid" value="<?php echo $job['uuid']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('Are you sure you want to delete this job? This action cannot be undone.')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (empty($jobs)): ?>
        <div class="text-center py-12">
            <div class="text-gray-500 mb-4">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0V8a2 2 0 01-2 2H8a2 2 0 01-2-2V6m8 0H8m0 0V4" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No jobs found</h3>
            <p class="text-gray-500">Try adjusting your search filters.</p>
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>