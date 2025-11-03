<?php
// Employer Profile Card Component
// Only show for logged-in employers, not on profile page

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'employer') {
    return;
}

// Get current page to exclude profile page
$currentPage = basename($_SERVER['PHP_SELF']);
if ($currentPage === 'employer-profile.php') {
    return;
}

// Load required dependencies
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

$userModel = new User($conn);
$userProfile = $userModel->getUserProfile($_SESSION['user_id']);

if (!$userProfile) {
    return;
}

// Extract profile data
$companyName = $userProfile['company_name'] ?? 'Company Name';
$industry = $userProfile['industry'] ?? '';
$location = $userProfile['location'] ?? '';
$aboutCompany = $userProfile['about_company'] ?? '';
$website = $userProfile['website'] ?? '';
$companyEmail = $userProfile['email'] ?? '';
$companyLogo = $userProfile['company_logo'] ?? '';

// Company logo path
$logoPath = !empty($companyLogo) && file_exists(__DIR__ . '/../uploads/company_logos/' . $companyLogo)
    ? '/job-finder/uploads/company_logos/' . $companyLogo
    : '/job-finder/uploads/company_logos/default-logo.png';

// Get job statistics
$employerUuid = $userProfile['employer_uuid'] ?? null;
try {
    if ($employerUuid) {
        $stmt = $conn->prepare("
            SELECT
                COUNT(*) as total_jobs,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as active_jobs,
                (SELECT COUNT(*) FROM applications a JOIN job_posts jp ON a.job_uuid = jp.uuid WHERE jp.employer_uuid = ?) as total_applications
            FROM job_posts WHERE employer_uuid = ?
        ");
        if ($stmt) {
            $stmt->bind_param("ss", $employerUuid, $employerUuid);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();
        } else {
            $stats = ['total_jobs' => 0, 'active_jobs' => 0, 'total_applications' => 0];
        }
    } else {
        $stats = ['total_jobs' => 0, 'active_jobs' => 0, 'total_applications' => 0];
    }
} catch (Exception $e) {
    $stats = ['total_jobs' => 0, 'active_jobs' => 0, 'total_applications' => 0];
}
?>

<!-- Employer Profile Card Sidebar - 30% Left -->
<div class="employer-profile-card hidden lg:block fixed left-0 top-16 w-[30%] bg-white shadow-lg border-r border-gray-200 overflow-y-auto"
    style="height: calc(100vh - 4rem);">
    <div class="p-6">
        <!-- Company Logo -->
        <div class="flex flex-col items-center mb-6">
            <img src="<?php echo htmlspecialchars($logoPath); ?>" alt="Company Logo"
                class="w-24 h-24 rounded-lg object-contain border-2 border-gray-200 bg-gray-50 p-2 mb-4">
            <h3 class="text-xl font-semibold text-gray-900 text-center"><?php echo htmlspecialchars($companyName); ?>
            </h3>
            <?php if (!empty($industry)): ?>
            <p class="text-sm text-gray-600 text-center mt-1"><?php echo htmlspecialchars($industry); ?></p>
            <?php endif; ?>
        </div>

        <!-- Company Stats -->
        <div class="grid grid-cols-3 gap-3 mb-6 p-4 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg">
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600"><?php echo $stats['total_jobs'] ?? 0; ?></div>
                <div class="text-xs text-gray-600">Total Jobs</div>
            </div>
            <div class="text-center border-l border-r border-gray-300">
                <div class="text-2xl font-bold text-green-600"><?php echo $stats['active_jobs'] ?? 0; ?></div>
                <div class="text-xs text-gray-600">Active</div>
            </div>
            <div class="text-center">
                <div class="text-2xl font-bold text-purple-600"><?php echo $stats['total_applications'] ?? 0; ?></div>
                <div class="text-xs text-gray-600">Applications</div>
            </div>
        </div>

        <!-- Company Info -->
        <?php if (!empty($location)): ?>
        <div class="mb-4">
            <div class="flex items-center text-sm text-gray-700 p-3 bg-gray-50 rounded-lg">
                <i class="fas fa-map-marker-alt text-blue-600 mr-3 w-4"></i>
                <span><?php echo htmlspecialchars($location); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($companyEmail)): ?>
        <div class="mb-4">
            <div class="flex items-center text-sm text-gray-700 p-3 bg-gray-50 rounded-lg">
                <i class="fas fa-envelope text-blue-600 mr-3 w-4"></i>
                <span class="truncate"><?php echo htmlspecialchars($companyEmail); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- About Company -->
        <?php if (!empty($aboutCompany)): ?>
        <div class="mb-6">
            <h4 class="text-sm font-semibold text-gray-900 mb-2">About Company</h4>
            <p class="text-sm text-gray-700 leading-relaxed line-clamp-4"><?php echo htmlspecialchars($aboutCompany); ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Website -->
        <?php if (!empty($website)): ?>
        <div class="mb-6">
            <a href="<?php echo htmlspecialchars($website); ?>" target="_blank"
                class="flex items-center justify-center w-full p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors text-sm text-gray-700 hover:text-blue-600">
                <i class="fas fa-globe mr-2"></i>
                Visit Website
                <i class="fas fa-external-link-alt ml-2 text-xs"></i>
            </a>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="space-y-2 mt-auto pt-6 border-t border-gray-200">
            <a href="/jobs/post-job.php"
                class="block w-full text-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                <i class="fas fa-plus-circle mr-2"></i>
                Post New Job
            </a>
            <a href="/dashboard/employer-dashboard.php"
                class="block w-full text-center px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                <i class="fas fa-tachometer-alt mr-2"></i>
                Dashboard
            </a>
            <a href="/dashboard/employer-profile.php"
                class="block w-full text-center px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                <i class="fas fa-building mr-2"></i>
                Company Profile
            </a>
            <a href="/applications/applications.php"
                class="block w-full text-center px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                <i class="fas fa-file-alt mr-2"></i>
                Applications
            </a>
        </div>
    </div>
</div>

<style>
/* Employer profile card scrollbar styling */
.employer-profile-card::-webkit-scrollbar {
    width: 6px;
}

.employer-profile-card::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.employer-profile-card::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.employer-profile-card::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Line clamp utilities */
.line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Ensure footer has proper z-index */
footer {
    position: relative;
    z-index: 50;
}

/* When footer is in view, make profile card scroll with page */
body.footer-visible .employer-profile-card {
    position: absolute;
    top: 4rem;
    bottom: auto;
}
</style>

<script>
// JavaScript to handle sidebar positioning and footer interaction
document.addEventListener('DOMContentLoaded', function() {
    const profileCard = document.querySelector('.employer-profile-card');
    const footer = document.querySelector('footer');

    if (!profileCard || !footer) return;

    function updateCardPosition() {
        const footerRect = footer.getBoundingClientRect();
        const windowHeight = window.innerHeight;

        // Check if footer is visible in viewport
        if (footerRect.top < windowHeight) {
            // Footer is visible - make card scroll with page
            document.body.classList.add('footer-visible');
            profileCard.style.position = 'absolute';

            // Calculate how far from top to position the card
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const footerTop = footer.offsetTop;
            const cardHeight = profileCard.offsetHeight;

            // Position card so it doesn't overlap footer
            const maxTop = footerTop - cardHeight - 20; // 20px margin
            const minTop = 80; // Keep at least 80px from page top
            const calculatedTop = Math.max(minTop, Math.min(scrollTop + 80, maxTop));

            profileCard.style.top = calculatedTop + 'px';
        } else {
            // Footer not visible - keep card fixed
            document.body.classList.remove('footer-visible');
            profileCard.style.position = 'fixed';
            profileCard.style.top = '4rem';
        }
    }

    // Update position on scroll and resize
    window.addEventListener('scroll', updateCardPosition);
    window.addEventListener('resize', updateCardPosition);

    // Initial position update
    updateCardPosition();
});
</script>