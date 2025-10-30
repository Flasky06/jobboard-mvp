<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('/home.php');
}

// Handle job status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $job_uuid = $_POST['job_uuid'] ?? '';
    $action = $_POST['action'];

    if ($action === 'deactivate' || $action === 'activate') {
        $status = $action === 'activate' ? 'open' : 'closed';
        $stmt = $conn->prepare("UPDATE job_posts SET status = ? WHERE uuid = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $status, $job_uuid);
            $stmt->execute();
            $_SESSION['success'] = "Job " . ($action === 'activate' ? 'opened' : 'closed') . " successfully";
        }
    } elseif ($action === 'delete') {
        // Soft delete job
        $stmt = $conn->prepare("UPDATE job_posts SET status = 'closed' WHERE uuid = ?");
        if ($stmt) {
            $stmt->bind_param("s", $job_uuid);
            $stmt->execute();
            $_SESSION['success'] = "Job deleted successfully";
        }
    }

    redirect('/admin/jobs.php');
}

$jobController = new JobController($conn);

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Handle AJAX requests for live search
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    // Return only the jobs list HTML
    ob_start();
    include 'jobs_partial.php';
    $html = ob_get_clean();
    echo json_encode(['html' => $html]);
    exit;
}

 // Build query for admin jobs with filters
$query = "
    SELECT jp.uuid, jp.title, jp.job_description, jp.job_type, jp.location, jp.salary_range,
           jp.status, jp.created_at, jp.updated_at,
           e.company_name, u.email as contact_email,
           COUNT(a.uuid) as application_count
    FROM job_posts jp
    LEFT JOIN employers e ON jp.employer_uuid = e.uuid
    LEFT JOIN users u ON e.user_uuid = u.uuid
    LEFT JOIN applications a ON jp.uuid = a.job_uuid
";

$params = [];
$types = '';
$where_conditions = [];

if ($status_filter !== '') {
    $status_value = $status_filter === '1' ? 'open' : 'closed';
    $where_conditions[] = "jp.status = ?";
    $params[] = $status_value;
    $types .= 's';
}


if ($search) {
    $where_conditions[] = "(jp.title LIKE ? OR jp.job_description LIKE ? OR e.company_name LIKE ? OR u.email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

if (!empty($where_conditions)) {
    $query .= " WHERE " . implode(" AND ", $where_conditions);
}

$query .= " GROUP BY jp.uuid, jp.title, jp.job_description, jp.job_type, jp.location, jp.salary_range,
                   jp.status, jp.created_at, jp.updated_at, e.company_name, u.email
           ORDER BY jp.created_at DESC";

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
$stmt = $conn->prepare("SELECT COUNT(*) as total_jobs FROM job_posts");
$totalJobs = $stmt ? ($stmt->execute() ? $stmt->get_result()->fetch_assoc()['total_jobs'] : 0) : 0;

$stmt = $conn->prepare("SELECT COUNT(*) as active_jobs FROM job_posts WHERE status = 'open'");
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
                    <select name="status" id="status-filter" class="border border-gray-300 rounded px-3 py-2">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search-input" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search jobs, companies... (live search)"
                        class="w-full border border-gray-300 rounded px-3 py-2">
                </div>
            </form>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>

        <?php include 'jobs_partial.php'; ?>
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
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const jobsList = document.getElementById('jobs-list');
    const noJobsMessage = document.getElementById('no-jobs-message');

    let searchTimeout;

    function performSearch() {
        const searchValue = searchInput.value.trim();
        const statusValue = statusFilter.value;

        // Only search if search input has at least 3 characters or is empty
        if (searchValue.length > 0 && searchValue.length < 3) {
            return;
        }

        // Show loading state
        if (jobsList) {
            jobsList.innerHTML =
                '<div class="text-center py-8"><div class="text-gray-500">Searching...</div></div>';
        }
        if (noJobsMessage) {
            noJobsMessage.style.display = 'none';
        }

        // Build query parameters
        const params = new URLSearchParams();
        params.append('ajax', '1');
        if (statusValue) params.append('status', statusValue);
        if (searchValue) params.append('search', searchValue);

        // Make AJAX request
        fetch('/admin/jobs.php?' + params.toString())
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.html) {
                    // Replace the jobs list content
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    const newJobsList = tempDiv.querySelector('#jobs-list');
                    const newNoJobsMessage = tempDiv.querySelector('#no-jobs-message');

                    if (newJobsList && jobsList) {
                        jobsList.innerHTML = newJobsList.innerHTML;
                    }
                    if (newNoJobsMessage && noJobsMessage) {
                        if (newNoJobsMessage.style.display !== 'none') {
                            noJobsMessage.style.display = 'block';
                            noJobsMessage.innerHTML = newNoJobsMessage.innerHTML;
                        } else {
                            noJobsMessage.style.display = 'none';
                        }
                    }
                } else if (data.error) {
                    console.error('Server error:', data.error);
                    if (jobsList) {
                        jobsList.innerHTML =
                            '<div class="text-center py-8"><div class="text-red-500">Error: ' + data.error +
                            '</div></div>';
                    }
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                if (jobsList) {
                    jobsList.innerHTML =
                        '<div class="text-center py-8"><div class="text-red-500">Error loading results. Please try again.</div></div>';
                }
            });
    }

    // Debounced search on input
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 300); // 300ms debounce
    });

    // Immediate search on status change
    statusFilter.addEventListener('change', performSearch);
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>