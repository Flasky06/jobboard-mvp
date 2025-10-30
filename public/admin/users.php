<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    redirect('/home.php');
}

// Handle user status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_uuid = $_POST['user_uuid'] ?? '';
    $action = $_POST['action'];

    if ($action === 'deactivate' || $action === 'activate') {
        $status = $action === 'activate' ? 1 : 0;
        $stmt = $conn->prepare("UPDATE users SET is_verified = ? WHERE uuid = ?");
        $stmt->bind_param("is", $status, $user_uuid);
        $stmt->execute();
        $_SESSION['success'] = "User " . ($action === 'activate' ? 'activated' : 'deactivated') . " successfully";
    } elseif ($action === 'delete') {
        // Soft delete by setting a deleted flag (assuming you have this column)
        $stmt = $conn->prepare("UPDATE users SET is_verified = 0 WHERE uuid = ?");
        $stmt->bind_param("s", $user_uuid);
        $stmt->execute();
        $_SESSION['success'] = "User deleted successfully";
    }

    redirect('/admin/users.php');
}

// Get filter parameters
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "
    SELECT u.uuid, u.email, u.role, u.is_verified, u.created_at,
           CASE
               WHEN u.role = 'jobseeker' THEN COALESCE(js.fullName, 'N/A')
               WHEN u.role = 'employer' THEN COALESCE(e.company_name, 'N/A')
               WHEN u.role = 'admin' THEN COALESCE(a.full_name, 'N/A')
               ELSE 'N/A'
           END as display_name,
           CASE
               WHEN u.role = 'jobseeker' THEN COALESCE(js.phone, 'N/A')
               WHEN u.role = 'employer' THEN COALESCE(e.contact_email, 'N/A')
               WHEN u.role = 'admin' THEN COALESCE(a.contact_number, 'N/A')
               ELSE 'N/A'
           END as contact_info
    FROM users u
    LEFT JOIN job_seekers js ON u.uuid = js.user_uuid
    LEFT JOIN employers e ON u.uuid = e.user_uuid
    LEFT JOIN admins a ON u.uuid = a.user_uuid
    WHERE 1=1
";

$params = [];
$types = '';

if ($role_filter) {
    $query .= " AND u.role = ?";
    $params[] = $role_filter;
    $types .= 's';
}

if ($status_filter !== '') {
    $query .= " AND u.is_verified = ?";
    $params[] = (int)$status_filter;
    $types .= 'i';
}

if ($search) {
    $query .= " AND (u.email LIKE ? OR
                     CASE
                         WHEN u.role = 'jobseeker' THEN js.fullName
                         WHEN u.role = 'employer' THEN e.company_name
                         WHEN u.role = 'admin' THEN a.full_name
                         ELSE ''
                     END LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$query .= " ORDER BY u.created_at DESC";

// Execute query
$stmt = $conn->prepare($query);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $users = [];
}

// Get user counts by role
$stmt = $conn->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
$stmt->execute();
$userStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$userCounts = ['jobseeker' => 0, 'employer' => 0, 'admin' => 0];
foreach ($userStats as $stat) {
    $userCounts[$stat['role']] = $stat['count'];
}

$title = "User Management";
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">User Management</h1>
            <a href="/admin-dashboard" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                ← Back to Dashboard
            </a>
        </div>

        <!-- User Statistics -->
        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <h3 class="text-lg font-semibold text-blue-800">Job Seekers</h3>
                <p class="text-2xl font-bold text-blue-600"><?php echo $userCounts['jobseeker']; ?></p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <h3 class="text-lg font-semibold text-green-800">Employers</h3>
                <p class="text-2xl font-bold text-green-600"><?php echo $userCounts['employer']; ?></p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                <h3 class="text-lg font-semibold text-purple-800">Admins</h3>
                <p class="text-2xl font-bold text-purple-600"><?php echo $userCounts['admin']; ?></p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <form method="GET" class="flex flex-wrap gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role" class="border border-gray-300 rounded px-3 py-2">
                        <option value="">All Roles</option>
                        <option value="jobseeker" <?php echo $role_filter === 'jobseeker' ? 'selected' : ''; ?>>Job
                            Seeker</option>
                        <option value="employer" <?php echo $role_filter === 'employer' ? 'selected' : ''; ?>>Employer
                        </option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
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
                        placeholder="Email or name..." class="border border-gray-300 rounded px-3 py-2">
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

        <!-- Users Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Joined</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-700">
                                            <?php echo strtoupper(substr($user['display_name'], 0, 1)); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($user['display_name']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                <?php echo $user['role'] === 'jobseeker' ? 'bg-blue-100 text-blue-800' :
                                           ($user['role'] === 'employer' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800'); ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo htmlspecialchars($user['contact_info']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                <?php echo $user['is_verified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $user['is_verified'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M j, Y', strtotime($user['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <?php if ($user['is_verified']): ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="user_uuid" value="<?php echo $user['uuid']; ?>">
                                    <input type="hidden" name="action" value="deactivate">
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900"
                                        onclick="return confirm('Are you sure you want to deactivate this user?')">
                                        Deactivate
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="user_uuid" value="<?php echo $user['uuid']; ?>">
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="text-green-600 hover:text-green-900"
                                        onclick="return confirm('Are you sure you want to activate this user?')">
                                        Activate
                                    </button>
                                </form>
                                <?php endif; ?>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="user_uuid" value="<?php echo $user['uuid']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="text-red-600 hover:text-red-900"
                                        onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
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

        <?php if (empty($users)): ?>
        <div class="text-center py-12">
            <div class="text-gray-500 mb-4">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No users found</h3>
            <p class="text-gray-500">Try adjusting your search filters.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>