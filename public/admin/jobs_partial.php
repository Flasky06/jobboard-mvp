<?php
// This file contains only the jobs list HTML for AJAX responses
// It expects $jobs to be available from the parent script

// For AJAX requests, we need to include the database connection and run the query
if (!isset($jobs) && isset($_GET['ajax'])) {
    require_once __DIR__ . '/../../config/db.php';

    // Get filter parameters
    $status_filter = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';

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
} elseif (!isset($jobs)) {
    echo json_encode(['error' => 'Jobs data not available']);
    exit;
}
?>

<!-- Jobs List -->
<div class="space-y-4" id="jobs-list">
    <?php foreach ($jobs as $job): ?>
    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow cursor-pointer"
        onclick="window.location.href='/admin/job-details.php?id=<?php echo $job['uuid']; ?>'">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <div class="flex items-center space-x-4 mb-2">
                    <h3 class="text-xl font-semibold text-gray-900 hover:text-blue-600">
                        <?php echo htmlspecialchars($job['title']); ?>
                    </h3>
                    <span
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                        <?php echo $job['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo ucfirst($job['status']); ?>
                    </span>
                </div>
                <div class="flex items-center space-x-4 text-sm text-gray-600 mb-3">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <?php echo htmlspecialchars($job['company_name'] ?: 'Company Name'); ?>
                        <?php if (!empty($job['contact_email'])): ?>
                        <span class="text-xs text-gray-500">•
                            <?php echo htmlspecialchars($job['contact_email']); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <?php echo htmlspecialchars($job['location']); ?>
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <?php echo htmlspecialchars(ucfirst($job['job_type'])); ?>
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10m0 0l-2-2m2 2l2-2" />
                        </svg>
                        Posted <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <?php echo $job['application_count'] ?? 0; ?> Applications
                    </span>
                </div>
                <p class="text-gray-700 mb-3 line-clamp-2">
                    <?php echo htmlspecialchars(substr($job['job_description'], 0, 200)) . (strlen($job['job_description']) > 200 ? '...' : ''); ?>
                </p>
                <?php if (!empty($job['salary_range'])): ?>
                <p class="text-green-600 font-medium mb-3">
                    <?php echo htmlspecialchars($job['salary_range']); ?>
                </p>
                <?php endif; ?>
                <div class="text-sm text-gray-500">
                    Contact: <?php echo htmlspecialchars($job['contact_email'] ?? 'N/A'); ?>
                </div>
            </div>
            <div class="ml-4 flex flex-col space-y-2" onclick="event.stopPropagation()">
                <?php if ($job['status'] === 'open'): ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="job_uuid" value="<?php echo $job['uuid']; ?>">
                    <input type="hidden" name="action" value="deactivate">
                    <button type="submit" class="text-yellow-600 hover:text-yellow-900 text-sm font-medium"
                        onclick="return confirm('Are you sure you want to close this job?')">
                        Close Job
                    </button>
                </form>
                <?php else: ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="job_uuid" value="<?php echo $job['uuid']; ?>">
                    <input type="hidden" name="action" value="activate">
                    <button type="submit" class="text-green-600 hover:text-green-900 text-sm font-medium"
                        onclick="return confirm('Are you sure you want to open this job?')">
                        Open Job
                    </button>
                </form>
                <?php endif; ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="job_uuid" value="<?php echo $job['uuid']; ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium"
                        onclick="return confirm('Are you sure you want to delete this job? This action cannot be undone.')">
                        Delete Job
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($jobs)): ?>
<div class="text-center py-12" id="no-jobs-message">
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