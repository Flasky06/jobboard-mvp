<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/JobController.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/csrf.php';

// No authentication required for home page - job seekers can browse jobs without logging in
$jobController = new JobController($conn);

// Get all jobs for job seekers with filters
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
$jobs = $jobController->job->getAllJobs($filters);

$title = "Find Your Dream Job";
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-md mt-8">
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Find Your Dream Job</h1>
        <p class="text-xl text-gray-600">Discover opportunities that match your skills and career goals</p>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-gray-50 p-6 rounded-lg mb-8">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Jobs</label>
                <input type="text" name="search" id="search"
                    value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                    placeholder="Job title, company, or keywords"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label for="industry" class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                <select name="industry" id="industry"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Industries</option>
                    <option value="Technology"
                        <?php echo ($_GET['industry'] ?? '') === 'Technology' ? 'selected' : ''; ?>>Technology</option>
                    <option value="Marketing"
                        <?php echo ($_GET['industry'] ?? '') === 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                    <option value="Finance" <?php echo ($_GET['industry'] ?? '') === 'Finance' ? 'selected' : ''; ?>>
                        Finance</option>
                    <option value="Healthcare"
                        <?php echo ($_GET['industry'] ?? '') === 'Healthcare' ? 'selected' : ''; ?>>Healthcare</option>
                    <option value="Education"
                        <?php echo ($_GET['industry'] ?? '') === 'Education' ? 'selected' : ''; ?>>Education</option>
                    <option value="Retail" <?php echo ($_GET['industry'] ?? '') === 'Retail' ? 'selected' : ''; ?>>
                        Retail</option>
                    <option value="Manufacturing"
                        <?php echo ($_GET['industry'] ?? '') === 'Manufacturing' ? 'selected' : ''; ?>>Manufacturing
                    </option>
                    <option value="Consulting"
                        <?php echo ($_GET['industry'] ?? '') === 'Consulting' ? 'selected' : ''; ?>>Consulting</option>
                </select>
            </div>
            <div>
                <label for="job_type" class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                <select name="job_type" id="job_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="full-time"
                        <?php echo ($_GET['job_type'] ?? '') === 'full-time' ? 'selected' : ''; ?>>Full Time</option>
                    <option value="part-time"
                        <?php echo ($_GET['job_type'] ?? '') === 'part-time' ? 'selected' : ''; ?>>Part Time</option>
                    <option value="contract" <?php echo ($_GET['job_type'] ?? '') === 'contract' ? 'selected' : ''; ?>>
                        Contract</option>
                    <option value="freelance"
                        <?php echo ($_GET['job_type'] ?? '') === 'freelance' ? 'selected' : ''; ?>>Freelance</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit"
                    class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Search Jobs
                </button>
            </div>
        </form>
    </div>

    <!-- Jobs List -->
    <?php if (empty($jobs)): ?>
    <div class="text-center py-12">
        <div class="text-gray-500 mb-4">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0V8a2 2 0 01-2 2H8a2 2 0 01-2-2V6m8 0H8m0 0V4" />
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No jobs found</h3>
        <p class="text-gray-500">Try adjusting your search criteria or check back later for new opportunities.</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($jobs as $job): ?>
        <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow cursor-pointer"
            onclick="window.location.href='jobs/job-details.php?id=<?php echo $job['uuid']; ?>'">
            <div class="flex justify-between items-start">
                <div class="flex-1 cursor-pointer">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2 hover:text-blue-600">
                        <?php echo htmlspecialchars($job['title']); ?>
                    </h3>
                    <div class="flex items-center space-x-4 text-sm text-gray-600 mb-3">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <?php echo htmlspecialchars($job['company_name'] ?? 'Company Name'); ?>
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
                    </div>
                    <p class="text-gray-700 mb-3 line-clamp-2">
                        <?php echo htmlspecialchars(substr($job['job_description'], 0, 200)) . (strlen($job['job_description']) > 200 ? '...' : ''); ?>
                    </p>
                    <?php if (!empty($job['salary_range'])): ?>
                    <p class="text-green-600 font-medium mb-3">
                        <?php echo htmlspecialchars($job['salary_range']); ?>
                    </p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
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
function toggleSaveJob(jobUuid, button) {
    fetch('save-job.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'job_uuid=' + encodeURIComponent(jobUuid) + '&csrf_token=' + encodeURIComponent(
                '<?php echo generate_csrf_token(); ?>')
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const saveText = button.querySelector('.save-text');
                saveText.textContent = data.saved ? 'Unsave' : 'Save';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>