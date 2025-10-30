<?php
// This file contains only the employers list HTML for AJAX responses
// It expects $employers to be available from the parent script

// For AJAX requests, we need to include the database connection and run the query
if (!isset($employers) && isset($_GET['ajax'])) {
    require_once __DIR__ . '/../../config/db.php';

    // Get filter parameters
    $status_filter = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';

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
} elseif (!isset($employers)) {
    echo json_encode(['error' => 'Employers data not available']);
    exit;
}
?>

<!-- Employers List -->
<div class="space-y-4" id="employers-list">
    <?php foreach ($employers as $employer): ?>
    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow cursor-pointer"
        onclick="window.location.href='/admin/employer-details.php?id=<?php echo $employer['uuid']; ?>'">
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <div class="flex items-center space-x-4 mb-2">
                    <div class="flex-shrink-0 h-12 w-12">
                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                            <span class="text-lg font-medium text-gray-700">
                                <?php echo strtoupper(substr($employer['company_name'], 0, 1)); ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 hover:text-blue-600">
                            <?php echo htmlspecialchars($employer['company_name']); ?>
                        </h3>
                        <span
                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                            <?php echo $employer['is_verified'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                            <?php echo $employer['is_verified'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-4 text-sm text-gray-600 mb-3">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <?php echo htmlspecialchars($employer['email']); ?>
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <?php echo htmlspecialchars($employer['contact_phone'] ?? 'N/A'); ?>
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Contact: <?php echo htmlspecialchars($employer['contact_email'] ?? 'N/A'); ?>
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <?php echo htmlspecialchars($employer['industry'] ?? 'N/A'); ?>
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <?php echo htmlspecialchars($employer['location'] ?? 'N/A'); ?>
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10m0 0l-2-2m2 2l2-2" />
                        </svg>
                        Joined <?php echo date('M j, Y', strtotime($employer['created_at'])); ?>
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <?php echo $employer['total_jobs'] ?? 0; ?> Jobs Posted
                    </span>
                </div>
                <?php if (!empty($employer['description'])): ?>
                <p class="text-gray-700 mb-3 line-clamp-2">
                    <?php echo htmlspecialchars(substr($employer['description'], 0, 200)) . (strlen($employer['description']) > 200 ? '...' : ''); ?>
                </p>
                <?php endif; ?>
            </div>
            <div class="ml-4 flex flex-col space-y-2" onclick="event.stopPropagation()">
                <?php if ($employer['is_verified']): ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="employer_uuid" value="<?php echo $employer['uuid']; ?>">
                    <input type="hidden" name="action" value="deactivate">
                    <button type="submit" class="text-yellow-600 hover:text-yellow-900 text-sm font-medium"
                        onclick="return confirm('Are you sure you want to deactivate this employer?')">
                        Deactivate
                    </button>
                </form>
                <?php else: ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="employer_uuid" value="<?php echo $employer['uuid']; ?>">
                    <input type="hidden" name="action" value="activate">
                    <button type="submit" class="text-green-600 hover:text-green-900 text-sm font-medium"
                        onclick="return confirm('Are you sure you want to activate this employer?')">
                        Activate
                    </button>
                </form>
                <?php endif; ?>
                <form method="POST" class="inline">
                    <input type="hidden" name="employer_uuid" value="<?php echo $employer['uuid']; ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium"
                        onclick="return confirm('Are you sure you want to delete this employer? This action cannot be undone.')">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (empty($employers)): ?>
<div class="text-center py-12" id="no-employers-message">
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