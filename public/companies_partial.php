<?php
// This file contains only the companies list HTML for AJAX responses
// It expects $employers to be available from the parent script

// For AJAX requests, we need to include the database connection and run the query
if (!isset($employers) && isset($_GET['ajax'])) {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../controllers/JobController.php';

    $jobController = new JobController($conn);

    // Get filter parameters
    $search = $_GET['search'] ?? '';

    // Get all employers/companies with filters
    $employers = $jobController->user->getAllEmployers($search ? ['search' => $search] : []);
} elseif (!isset($employers)) {
    echo json_encode(['error' => 'Employers data not available']);
    exit;
}
?>

<?php if (empty($employers)): ?>
<div class="text-center py-12" id="no-companies-message">
    <svg class="w-24 h-24 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
    </svg>
    <h3 class="text-xl font-medium text-gray-900 mb-2">No Companies Found</h3>
    <p class="text-gray-500">There are no registered companies yet.</p>
</div>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="companies-list">
    <?php foreach ($employers as $employer): ?>
    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
        <div class="p-6">
            <div class="flex items-start gap-4 mb-4">
                <?php if (!empty($employer['company_logo'])): ?>
                <img src="<?php echo htmlspecialchars($employer['company_logo']); ?>" alt="Company Logo"
                    class="w-16 h-16 rounded-lg object-cover border-2 border-gray-200 flex-shrink-0">
                <?php endif; ?>

                <div class="flex-1 min-w-0">
                    <h3 class="text-xl font-semibold text-gray-900 mb-1">
                        <a href="/company-details.php?view=employer&id=<?php echo htmlspecialchars($employer['employer_uuid']); ?>"
                            class="text-blue-600 hover:text-blue-800 hover:underline">
                            <?php echo htmlspecialchars($employer['company_name'] ?? 'Company Name'); ?>
                        </a>
                    </h3>

                    <?php if (!empty($employer['industry'])): ?>
                    <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($employer['industry']); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($employer['location'])): ?>
                    <div class="flex items-center gap-1 text-sm text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        <span><?php echo htmlspecialchars($employer['location']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($employer['about_company'])): ?>
            <p class="text-gray-700 text-sm mb-4 line-clamp-3">
                <?php echo htmlspecialchars(substr($employer['about_company'], 0, 150)) . (strlen($employer['about_company']) > 150 ? '...' : ''); ?>
            </p>
            <?php endif; ?>

            <!-- Job Count -->
            <?php
            $jobCount = 0;
            if (!empty($employer['employer_uuid'])) {
                $jobs = $jobController->job->getJobsByEmployer($employer['employer_uuid']);
                $jobCount = count($jobs);
            }
            ?>
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-gray-600">
                    <?php echo $jobCount; ?> job<?php echo $jobCount !== 1 ? 's' : ''; ?> posted
                </span>
            </div>

            <a href="/company-details.php?view=employer&id=<?php echo htmlspecialchars($employer['employer_uuid']); ?>"
                class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-center block font-medium">
                View Company Profile
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>