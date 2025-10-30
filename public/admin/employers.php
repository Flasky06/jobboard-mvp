<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';

// Check if user is admin
if (!isAdmin()) {
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

// Handle AJAX requests for live search
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    // Return only the employers list HTML
    ob_start();
    include 'employers_partial.php';
    $html = ob_get_clean();
    echo json_encode(['html' => $html]);
    exit;
}

// Build query
$query = "
    SELECT u.uuid, u.email, u.is_verified, u.created_at,
           e.company_name, e.contact_number as contact_phone, e.website,
           e.industry, e.location, e.about_company as description,
           COUNT(jp.uuid) as total_jobs
    FROM users u
    INNER JOIN employers e ON u.uuid = e.user_uuid
    LEFT JOIN job_posts jp ON u.uuid = jp.employer_uuid
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
                   e.company_name, e.contact_number, e.website,
                   e.industry, e.location, e.about_company
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

$stmt = $conn->prepare("SELECT COUNT(*) as total_jobs FROM job_posts");
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
                    <select name="status" id="status-filter" class="border border-gray-300 rounded px-3 py-2">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search-input" value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Company name or email... (live search)"
                        class="border border-gray-300 rounded px-3 py-2">
                </div>
            </form>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>

        <?php include 'employers_partial.php'; ?>
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
    const employersList = document.getElementById('employers-list');
    const noEmployersMessage = document.getElementById('no-employers-message');

    let searchTimeout;

    function performSearch() {
        const searchValue = searchInput.value.trim();
        const statusValue = statusFilter.value;

        // Only search if search input has at least 3 characters or is empty
        if (searchValue.length > 0 && searchValue.length < 3) {
            return;
        }

        // Show loading state
        if (employersList) {
            employersList.innerHTML =
                '<div class="text-center py-8"><div class="text-gray-500">Searching...</div></div>';
        }
        if (noEmployersMessage) {
            noEmployersMessage.style.display = 'none';
        }

        // Build query parameters
        const params = new URLSearchParams();
        params.append('ajax', '1');
        if (statusValue) params.append('status', statusValue);
        if (searchValue) params.append('search', searchValue);

        // Make AJAX request
        fetch('/admin/employers.php?' + params.toString())
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.html) {
                    // Replace the employers list content
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    const newEmployersList = tempDiv.querySelector('#employers-list');
                    const newNoEmployersMessage = tempDiv.querySelector('#no-employers-message');

                    if (newEmployersList && employersList) {
                        employersList.innerHTML = newEmployersList.innerHTML;
                    }
                    if (newNoEmployersMessage && noEmployersMessage) {
                        if (newNoEmployersMessage.style.display !== 'none') {
                            noEmployersMessage.style.display = 'block';
                            noEmployersMessage.innerHTML = newNoEmployersMessage.innerHTML;
                        } else {
                            noEmployersMessage.style.display = 'none';
                        }
                    }
                } else if (data.error) {
                    console.error('Server error:', data.error);
                    if (employersList) {
                        employersList.innerHTML =
                            '<div class="text-center py-8"><div class="text-red-500">Error: ' + data.error +
                            '</div></div>';
                    }
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                if (employersList) {
                    employersList.innerHTML =
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