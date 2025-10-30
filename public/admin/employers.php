<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    redirect('/home.php');
}

// Handle employer status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $employer_uuid = $_POST['employer_uuid'] ?? '';
    $action = $_POST['action'];

    if ($action === 'deactivate' || $action === 'activate') {
        $status = $action === 'activate' ? 1 : 0;
        $stmt = $conn->prepare("UPDATE users SET is_verified = ? WHERE uuid = ? AND role = 'employer'");
        if ($stmt) {
            $stmt->bind_param("is", $status, $employer_uuid);
            $stmt->execute();
            $_SESSION['success'] = "Employer " . ($action === 'activate' ? 'activated' : 'deactivated') . " successfully";
        }
    } elseif ($action === 'delete') {
        // Soft delete employer
        $stmt = $conn->prepare("UPDATE users SET is_verified = 0 WHERE uuid = ? AND role = 'employer'");
        if ($stmt) {
            $stmt->bind_param("s", $employer_uuid);
            $stmt->execute();
            $_SESSION['success'] = "Employer deleted successfully";
        }
    }

    redirect('/admin/employers.php');
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "
    SELECT u.uuid, u.email, u.is_verified, u.created_at,
           e.company_name, e.contact_email, e.contact_phone, e.website,
           e.industry, e.company_size, e.location, e.description,
           COUNT(j.uuid) as total_jobs
    FROM users u
    INNER JOIN employers e ON u.uuid = e.user_uuid
    LEFT JOIN jobs j ON u.uuid = j.employer_uuid
    WHERE u.role = 'employer'
";

$params = [];
$types = '';

if ($status_filter !== '') {
    $query .= " AND u.is_verified = ?";
    $params[] = (int)$status_filter;
    $types .= 'i';
}

if ($search) {
    $query .= " AND (u.email LIKE ? OR e.company_name LIKE ? OR e.contact_email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

$query .= " GROUP BY u.uuid, u.email, u.is_verified, u.created_at,
                   e.company_name, e.contact_email, e.contact_phone, e.website,
                   e.industry, e.company_size, e.location, e.description
           ORDER BY u.created_at DESC";

// Execute query
$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $employers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $employers = [];
}

// Get employer statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_employers FROM employers");
$totalEmployers = $stmt ? ($stmt->execute() ? $stmt->get_result()->fetch_assoc()['total_employers'] : 0) : 0;

$stmt = $conn->prepare("SELECT COUNT(*) as active_employers FROM users WHERE role = 'employer' AND is_verified = 1");
$activeEmployers = $stmt ? ($stmt->execute() ? $stmt->get_result()->fetch_assoc()['active_employers'] : 0) : 0;

$stmt = $conn->prepare("SELECT COUNT(*) as total_jobs FROM jobs");
$totalJobs = $stmt ? ($stmt->execute() ? $stmt->get_result()->fetch_assoc()['total_jobs'] : 0) : 0;

$title = "Employer Management";
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Employer Management</h1>
            <a href="/admin-dashboard" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                ← Back to Dashboard
            </a>
        </div>

        <!-- Employer Statistics -->
        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h3 class="text-lg font-semibold text-blue-800">Total Employers</h3>
                <p class="text-2xl font-bold text-blue-600"><?php echo $totalEmployers; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <h3 class="text-lg font-semibold text-green-800">Active Employers</h3>
                <p class="text-2xl font-bold text-green-600"><?php echo $activeEmployers; ?></p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <h3 class="text-lg font-semibold text-purple-800">Total Jobs Posted</h3>
                <p class="text-2xl font-bold text-purple-600"><?php echo $totalJobs; ?></p>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Company name or email..." class="border border-gray-300 rounded px-3 py-2">
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

        <!-- Employers Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Company</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Industry</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jobs
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($employers as $employer): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            <?php echo strtoupper(substr($employer['company_name'], 0, 1)); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($employer['company_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($employer['email']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div><?php echo htmlspecialchars($employer['contact_email']); ?></div>
                            <div><?php echo htmlspecialchars($employer['contact_phone'] ?: 'N/A'); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($employer['industry'] ?: 'N/A'); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $employer['total_jobs']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                <?php echo $employer['is_verified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $employer['is_verified'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($employer['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <?php if ($employer['is_verified']): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="employer_uuid" value="<?php echo $employer['uuid']; ?>">
                                    <input type="hidden" name="action" value="deactivate">
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900"
                                        onclick="return confirm('Are you sure you want to deactivate this employer?')">
                                        Deactivate
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="employer_uuid" value="<?php echo $employer['uuid']; ?>">
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="text-green-600 hover:text-green-900"
                                        onclick="return confirm('Are you sure you want to activate this employer?')">
                                        Activate
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="employer_uuid" value="<?php echo $employer['uuid']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('Are you sure you want to delete this employer? This action cannot be undone.')">
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

        <?php if (empty($employers)): ?>
        <div class="text-center py-12">
            <div class="text-gray-500 mb-4">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No employers found</h3>
            <p class="text-gray-500">Try adjusting your search filters.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>