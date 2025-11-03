<?php
// This file contains only the jobs list HTML for AJAX responses
// It expects $jobs to be available from the parent script

// For AJAX requests, we need to include the database connection and run the query
if (!isset($jobs) && isset($_GET['ajax'])) {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../controllers/JobController.php';

    // Get filter parameters
    $filters = [];
    if (!empty($_GET['search'])) {
        $filters['search'] = $_GET['search'];
    }
    if (!empty($_GET['industry'])) {
        $filters['industry'] = $_GET['industry'];
    }
    if (!empty($_GET['job_type'])) {
        $filters['job_type'] = $_GET['job_type'];
    }

    $jobController = new JobController($conn);
    $jobs = $jobController->job->getAllJobs($filters);
} elseif (!isset($jobs)) {
    echo json_encode(['error' => 'Jobs data not available']);
    exit;
}
?>

<!-- Jobs List -->
<?php if (empty($jobs)): ?>
<div class="text-center py-16 bg-gray-50 rounded-lg" id="no-jobs-message">
    <div class="mb-6">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-4">
            <i class="fas fa-briefcase text-blue-600 text-3xl"></i>
        </div>
    </div>
    <h3 class="text-2xl font-semibold text-gray-900 mb-2">No Jobs Found</h3>
    <p class="text-gray-600 max-w-md mx-auto">
        Try adjusting your search criteria or check back later for new opportunities.
    </p>
</div>
<?php else: ?>
<!-- Jobs Grid -->
<div id="jobs-list" class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach ($jobs as $job): ?>
    <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow bg-white cursor-pointer"
        onclick="window.location.href='jobs/job-details.php?id=<?php echo $job['uuid']; ?>'">
        <!-- Job Card Header -->
        <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-4 border-b border-gray-200">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">
                <div class="flex-1">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900 mb-2 hover:text-blue-600 transition-colors">
                                <?php echo htmlspecialchars($job['title']); ?>
                            </h3>

                            <!-- Job Meta Info -->
                            <div class="flex flex-wrap gap-3 text-sm text-gray-600 mb-3">
                                <span class="flex items-center">
                                    <i class="fas fa-building mr-1.5 text-gray-400"></i>
                                    <?php echo htmlspecialchars($job['company_name'] ?? 'Company Name'); ?>
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-map-marker-alt mr-1.5 text-gray-400"></i>
                                    <?php echo htmlspecialchars($job['location']); ?>
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-briefcase mr-1.5 text-gray-400"></i>
                                    <?php echo htmlspecialchars(ucfirst($job['job_type'])); ?>
                                </span>
                                <span class="flex items-center">
                                    <i class="fas fa-calendar mr-1.5 text-gray-400"></i>
                                    Posted <?php echo date('M j, Y', strtotime($job['created_at'])); ?>
                                </span>
                            </div>

                            <!-- Job Tags -->
                            <div class="flex flex-wrap gap-2">
                                <?php if (!empty($job['industry'])): ?>
                                <span class="px-3 py-1 bg-purple-100 text-purple-800 text-xs font-medium rounded-full">
                                    <?php echo htmlspecialchars($job['industry']); ?>
                                </span>
                                <?php endif; ?>

                                <?php if (!empty($job['salary_range'])): ?>
                                <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                    <i class="fas fa-money-bill-wave mr-1"></i>
                                    <?php echo htmlspecialchars($job['salary_range']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Card Body -->
            <div class="p-4">
                <!-- Job Description Preview -->
                <p class="text-gray-700 mb-4 line-clamp-3 leading-relaxed">
                    <?php echo htmlspecialchars(substr($job['job_description'], 0, 200) . (strlen($job['job_description']) > 200 ? '...' : '')); ?>
                </p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>