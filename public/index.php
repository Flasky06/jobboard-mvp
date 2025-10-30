<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/JobController.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/csrf.php';

// No authentication required for home page - job seekers can browse jobs without logging in
$jobController = new JobController($conn);

// Handle AJAX requests for live search
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    // Return only the jobs list HTML
    ob_start();
    include 'index_partial.php';
    $html = ob_get_clean();
    echo json_encode(['html' => $html]);
    exit;
}

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
                <button type="submit" id="search-btn"
                    class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Search Jobs
                </button>
            </div>
        </form>
    </div>

    <?php include 'index_partial.php'; ?>
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
    const searchInput = document.getElementById('search');
    const industrySelect = document.getElementById('industry');
    const jobTypeSelect = document.getElementById('job_type');
    const searchBtn = document.getElementById('search-btn');
    const jobsList = document.getElementById('jobs-list');
    const noJobsMessage = document.getElementById('no-jobs-message');

    let searchTimeout;

    function performSearch() {
        const searchValue = searchInput.value.trim();
        const industryValue = industrySelect.value;
        const jobTypeValue = jobTypeSelect.value;

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
        if (searchValue) params.append('search', searchValue);
        if (industryValue) params.append('industry', industryValue);
        if (jobTypeValue) params.append('job_type', jobTypeValue);

        // Make AJAX request
        fetch('/?' + params.toString())
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

    // Immediate search on select changes
    industrySelect.addEventListener('change', performSearch);
    jobTypeSelect.addEventListener('change', performSearch);

    // Search on button click (fallback)
    searchBtn.addEventListener('click', function(e) {
        e.preventDefault();
        performSearch();
    });
});

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