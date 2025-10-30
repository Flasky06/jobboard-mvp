<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/JobController.php';

$jobController = new JobController($conn);

// Handle AJAX requests for live search
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    // Return only the companies list HTML
    ob_start();
    include 'companies_partial.php';
    $html = ob_get_clean();
    echo json_encode(['html' => $html]);
    exit;
}

// Get filter parameters
$search = $_GET['search'] ?? '';

// Get all employers/companies with filters
$employers = $jobController->user->getAllEmployers($search ? ['search' => $search] : []);

$title = "Companies - Browse All Employers";
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">

    <!-- Search Section -->
    <div class="bg-gray-50 p-6 rounded-lg mb-8">
        <div class="max-w-md">
            <label for="search-input" class="block text-sm font-medium text-gray-700 mb-2">Search Companies</label>
            <input type="text" id="search-input" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Company name, industry, or location..."
                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        <ul class="list-disc list-inside">
            <?php foreach ($_SESSION['errors'] as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php unset($_SESSION['errors']); ?>
    </div>
    <?php endif; ?>

    <?php include 'companies_partial.php'; ?>
</div>

<style>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const companiesList = document.getElementById('companies-list');
    const noCompaniesMessage = document.getElementById('no-companies-message');

    let searchTimeout;

    function performSearch() {
        const searchValue = searchInput.value.trim();

        // Only search if search input has at least 3 characters or is empty
        if (searchValue.length > 0 && searchValue.length < 3) {
            return;
        }

        // Show loading state
        if (companiesList) {
            companiesList.innerHTML =
                '<div class="text-center py-8"><div class="text-gray-500">Searching...</div></div>';
        }
        if (noCompaniesMessage) {
            noCompaniesMessage.style.display = 'none';
        }

        // Build query parameters
        const params = new URLSearchParams();
        params.append('ajax', '1');
        if (searchValue) params.append('search', searchValue);

        // Make AJAX request
        fetch('/companies.php?' + params.toString())
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.html) {
                    // Replace the companies list content
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    const newCompaniesList = tempDiv.querySelector('#companies-list');
                    const newNoCompaniesMessage = tempDiv.querySelector('#no-companies-message');

                    if (newCompaniesList && companiesList) {
                        companiesList.innerHTML = newCompaniesList.innerHTML;
                    }
                    if (newNoCompaniesMessage && noCompaniesMessage) {
                        if (newNoCompaniesMessage.style.display !== 'none') {
                            noCompaniesMessage.style.display = 'block';
                            noCompaniesMessage.innerHTML = newNoCompaniesMessage.innerHTML;
                        } else {
                            noCompaniesMessage.style.display = 'none';
                        }
                    }
                } else if (data.error) {
                    console.error('Server error:', data.error);
                    if (companiesList) {
                        companiesList.innerHTML =
                            '<div class="text-center py-8"><div class="text-red-500">Error: ' + data.error +
                            '</div></div>';
                    }
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                if (companiesList) {
                    companiesList.innerHTML =
                        '<div class="text-center py-8"><div class="text-red-500">Error loading results. Please try again.</div></div>';
                }
            });
    }

    // Debounced search on input
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 300); // 300ms debounce
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>