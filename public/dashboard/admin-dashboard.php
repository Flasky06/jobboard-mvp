<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
  redirect('/home.php');
}

// Get statistics
try {
    // Count total users by role
    $stmt = $conn->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    if ($stmt) {
        $stmt->execute();
        $userStats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $userStats = [];
    }

    // Count total jobs
    $stmt = $conn->prepare("SELECT COUNT(*) as total_jobs FROM jobs");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $totalJobs = $result ? $result->fetch_assoc()['total_jobs'] : 0;
    } else {
        $totalJobs = 0;
    }

    // Count total applications
    $stmt = $conn->prepare("SELECT COUNT(*) as total_applications FROM applications");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $totalApplications = $result ? $result->fetch_assoc()['total_applications'] : 0;
    } else {
        $totalApplications = 0;
    }

    // Count admin users
    $stmt = $conn->prepare("SELECT COUNT(*) as total_admins FROM admins");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $totalAdmins = $result ? $result->fetch_assoc()['total_admins'] : 0;
    } else {
        $totalAdmins = 0;
    }

    // Get recent activity (last 10 registrations)
    $stmt = $conn->prepare("
        SELECT u.email, u.role, u.created_at,
               CASE
                   WHEN u.role = 'jobseeker' THEN js.fullName
                   WHEN u.role = 'employer' THEN e.company_name
                   WHEN u.role = 'admin' THEN a.full_name
                   ELSE u.email
               END as display_name
        FROM users u
        LEFT JOIN job_seekers js ON u.uuid = js.user_uuid
        LEFT JOIN employers e ON u.uuid = e.user_uuid
        LEFT JOIN admins a ON u.uuid = a.user_uuid
        ORDER BY u.created_at DESC LIMIT 10
    ");
    if ($stmt) {
        $stmt->execute();
        $recentUsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $recentUsers = [];
    }

    // Get recent jobs (last 5)
    $stmt = $conn->prepare("
        SELECT j.title, j.created_at, e.company_name
        FROM jobs j
        LEFT JOIN employers e ON j.employer_uuid = e.uuid
        ORDER BY j.created_at DESC LIMIT 5
    ");
    if ($stmt) {
        $stmt->execute();
        $recentJobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $recentJobs = [];
    }

} catch (Exception $e) {
    error_log("Admin dashboard error: " . $e->getMessage());
    $userStats = [];
    $totalJobs = 0;
    $totalApplications = 0;
    $totalAdmins = 0;
    $recentUsers = [];
    $recentJobs = [];
}

// Process user stats
$userCounts = [
    'jobseeker' => 0,
    'employer' => 0,
    'admin' => 0
];

foreach ($userStats as $stat) {
    $userCounts[$stat['role']] = $stat['count'];
}

$title = "Admin Dashboard";
include __DIR__ . '/../../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Admin Dashboard</h1>
        <p class="text-gray-600 mb-6">Welcome back, Admin! Manage your job portal platform.</p>

        <!-- Statistics Cards -->
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                <h3 class="text-xl font-semibold mb-2">Job Seekers</h3>
                <p class="text-3xl font-bold text-blue-600"><?php echo $userCounts['jobseeker']; ?></p>
                <p class="text-gray-600 text-sm">Registered users</p>
            </div>

            <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                <h3 class="text-xl font-semibold mb-2">Employers</h3>
                <p class="text-3xl font-bold text-green-600"><?php echo $userCounts['employer']; ?></p>
                <p class="text-gray-600 text-sm">Companies registered</p>
            </div>

            <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                <h3 class="text-xl font-semibold mb-2">Jobs Posted</h3>
                <p class="text-3xl font-bold text-purple-600"><?php echo $totalJobs; ?></p>
                <p class="text-gray-600 text-sm">Active job listings</p>
            </div>

            <div class="bg-orange-50 p-6 rounded-lg border border-orange-200">
                <h3 class="text-xl font-semibold mb-2">Applications</h3>
                <p class="text-3xl font-bold text-orange-600"><?php echo $totalApplications; ?></p>
                <p class="text-gray-600 text-sm">Total applications</p>
            </div>
        </div>

        <!-- Management Actions -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-50 p-6 rounded-lg border border-blue-200">
                <h3 class="text-xl font-semibold mb-2">Employer Management</h3>
                <p class="text-gray-600 mb-4">Manage registered employers and companies</p>
                <a href="/admin/employers"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Manage
                    Employers</a>
            </div>

            <div class="bg-green-50 p-6 rounded-lg border border-green-200">
                <h3 class="text-xl font-semibold mb-2">Job Management</h3>
                <p class="text-gray-600 mb-4">Review and manage job postings</p>
                <a href="/admin/jobs"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">Manage
                    Jobs</a>
            </div>

            <div class="bg-purple-50 p-6 rounded-lg border border-purple-200">
                <h3 class="text-xl font-semibold mb-2">Admin Staff</h3>
                <p class="text-gray-600 mb-4">Manage admin staff accounts</p>
                <a href="/admin/staff"
                    class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700 transition">Manage
                    Staff</a>
            </div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Activity</h2>
            <div class="space-y-4">
                <?php if (!empty($recentUsers)): ?>
                <?php foreach ($recentUsers as $user): ?>
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-blue-600 text-xl mr-3">
                        <?php echo $user['role'] === 'jobseeker' ? '👤' : ($user['role'] === 'employer' ? '🏢' : '👨‍💼'); ?>
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-800 font-medium">New <?php echo ucfirst($user['role']); ?> registered</p>
                        <p class="text-gray-600 text-sm">
                            <?php echo htmlspecialchars($user['display_name'] ?: $user['email']); ?> joined</p>
                    </div>
                    <span
                        class="text-gray-500 text-sm"><?php echo date('M j, H:i', strtotime($user['created_at'])); ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($recentJobs)): ?>
                <?php foreach ($recentJobs as $job): ?>
                <div class="flex items-center p-4 bg-gray-50 rounded-lg">
                    <div class="text-green-600 text-xl mr-3">💼</div>
                    <div class="flex-1">
                        <p class="text-gray-800 font-medium">New job posted</p>
                        <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($job['title']); ?> at
                            <?php echo htmlspecialchars($job['company_name'] ?: 'Company'); ?></p>
                    </div>
                    <span
                        class="text-gray-500 text-sm"><?php echo date('M j, H:i', strtotime($job['created_at'])); ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <?php if (empty($recentUsers) && empty($recentJobs)): ?>
                <div class="text-center py-8 text-gray-500">
                    <p>No recent activity to display</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Platform Statistics</h2>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Users</span>
                    <span class="font-semibold text-lg"><?php echo array_sum($userCounts); ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Job Seekers</span>
                    <span class="font-semibold text-lg"><?php echo $userCounts['jobseeker']; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Employers</span>
                    <span class="font-semibold text-lg"><?php echo $userCounts['employer']; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Active Jobs</span>
                    <span class="font-semibold text-lg"><?php echo $totalJobs; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Total Applications</span>
                    <span class="font-semibold text-lg"><?php echo $totalApplications; ?></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Admin Staff</span>
                    <span class="font-semibold text-lg"><?php echo $totalAdmins; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>