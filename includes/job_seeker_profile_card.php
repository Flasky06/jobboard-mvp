<?php
// Job Seeker Profile Card Component
// Only show for logged-in job seekers, not on profile page

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'jobseeker') {
    return;
}

// Get current page to exclude profile page
$currentPage = basename($_SERVER['PHP_SELF']);
if ($currentPage === 'profile.php') {
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
$fullName = $userProfile['fullName'] ?? 'Job Seeker';
$professionalTitle = $userProfile['professional_title'] ?? 'Professional';
$bio = $userProfile['bio'] ?? '';
$skills = $userProfile['skills'] ?? '';
$profilePicture = $userProfile['profile_picture'] ?? 'default-avatar.png';

// Process skills
$skillsArray = array_filter(array_map('trim', explode(',', $skills)));
$skillsDisplay = implode(', ', array_slice($skillsArray, 0, 5)); // Show first 5 skills
if (count($skillsArray) > 5) {
    $skillsDisplay .= '...';
}

// Profile picture path
$profilePicPath = !empty($profilePicture) && file_exists(__DIR__ . '/../uploads/profile_photos/' . $profilePicture)
    ? '/job-finder' . $profilePicture
    : '/job-finder/uploads/profile_photos/default-avatar.png';
?>

<!-- Job Seeker Profile Card Sidebar - 25% Left -->
<div class="job-seeker-profile-card hidden lg:block fixed left-0 top-16 w-[25%] bg-white shadow-lg border-r border-gray-200 overflow-y-auto"
    style="height: 100vh;">
    <div class="p-6">
        <!-- Profile Picture -->
        <div class="flex flex-col items-center mb-6">
            <img src="<?php echo htmlspecialchars($profilePicPath); ?>" alt="Profile Picture"
                class="w-24 h-24 rounded-full object-cover border-4 border-blue-100 mb-4">
            <h3 class="text-xl font-semibold text-gray-900 text-center"><?php echo htmlspecialchars($fullName); ?></h3>
            <p class="text-sm text-gray-600 text-center mt-1"><?php echo htmlspecialchars($professionalTitle); ?></p>
        </div>

        <!-- Bio -->
        <?php if (!empty($bio)): ?>
        <div class="mb-6">
            <h4 class="text-sm font-semibold text-gray-900 mb-2">About</h4>
            <p class="text-sm text-gray-700 leading-relaxed"><?php echo htmlspecialchars($bio); ?></p>
        </div>
        <?php endif; ?>

        <!-- Skills -->
        <?php if (!empty($skillsDisplay)): ?>
        <div class="mb-6">
            <h4 class="text-sm font-semibold text-gray-900 mb-3">Skills</h4>
            <div class="flex flex-wrap gap-2">
                <?php foreach (array_slice($skillsArray, 0, 10) as $skill): ?>
                <span
                    class="px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full"><?php echo htmlspecialchars(trim($skill)); ?></span>
                <?php endforeach; ?>
                <?php if (count($skillsArray) > 10): ?>
                <span
                    class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">+<?php echo count($skillsArray) - 10; ?>
                    more</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="mt-8 space-y-2">

            <a href="/jobs/saved-jobs.php"
                class="block w-full text-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors">
                Saved Jobs
            </a>
        </div>
    </div>
</div>

<style>
/* Profile card scrollbar styling */
.job-seeker-profile-card::-webkit-scrollbar {
    width: 6px;
}

.job-seeker-profile-card::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.job-seeker-profile-card::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.job-seeker-profile-card::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Ensure footer has proper z-index */
footer {
    position: relative;
    z-index: 40;
}

/* When footer is in view, make profile card scroll with page */
body.footer-visible .job-seeker-profile-card {
    position: absolute;
    top: 4rem;
    bottom: auto;
}
</style>

<script>
// JavaScript to handle sidebar positioning and footer interaction
document.addEventListener('DOMContentLoaded', function() {
    const profileCard = document.querySelector('.job-seeker-profile-card');
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
            const minTop = 64; // Keep at least 64px from page top (4rem)
            const calculatedTop = Math.max(minTop, Math.min(scrollTop + 64, maxTop));
            // Keep card fixed at top when footer is visible
            profileCard.style.top = '4rem';

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