<nav class="bg-white shadow mb-6">
    <div class="container mx-auto flex justify-between items-center p-4">
        <a href="/dashboard/employer-dashboard.php" class="font-bold text-lg text-blue-700">Job Portal</a>

        <ul class="flex gap-4 items-center">
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'employer'): ?>
            <?php
            $dashboard_url = '/dashboard/employer-dashboard.php'; // default for employers
            $profile_url = '/dashboard/employer-profile.php'; // default for employers
            ?>
            <li><a href="/dashboard/employer-dashboard.php" class="hover:text-blue-600">Home</a></li>
            <li><a href="/applications/applications.php" class="hover:text-blue-600">Applications</a></li>
            <li><a href="/jobs/my-jobs.php" class="hover:text-blue-600">My Jobs</a></li>
            <li><a href="/jobs/post-job.php" class="hover:text-blue-600">Post New Job</a></li>
            <li class="flex items-center space-x-2">

                <div class="relative">
                    <button id="profileDropdownBtn" class="flex items-center space-x-2 focus:outline-none">
                        <img src="<?php
                            // Get profile image based on role
                            $profile_image = '/uploads/company_logos/default-logo.png'; // default for employer
                            if (isset($_SESSION['role']) && isset($_SESSION['user_id'])) {
                                require_once __DIR__ . '/../config/db.php';
                                require_once __DIR__ . '/../models/User.php';
                                $userModel = new User($conn);
                                $profile = $userModel->getUserProfile($_SESSION['user_id']);
                                if ($profile) {
                                    $profile_image = !empty($profile['company_logo']) ? $profile['company_logo'] : '/uploads/company_logos/default-logo.png';
                                }
                            }
                            echo htmlspecialchars($profile_image);
                        ?>" alt="Profile" class="w-8 h-8 rounded-full object-cover border-2 border-gray-300">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </button>
                    <div id="profileDropdown"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 hidden">
                        <div class="py-1">
                            <a href="<?php echo $profile_url; ?>"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                View Profile
                            </a>
                            <a href="/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </li>
            <?php else: ?>
            <li><a href="/auth/login.php" class="hover:text-blue-600">Login</a></li>
            <li><a href="/auth/register.php" class="hover:text-blue-600">Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<script>
// Profile dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const dropdownBtn = document.getElementById('profileDropdownBtn');
    const dropdown = document.getElementById('profileDropdown');

    if (dropdownBtn && dropdown) {
        // Toggle dropdown
        dropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target) && !dropdownBtn.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Close dropdown on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdown.classList.add('hidden');
            }
        });
    }
});
</script>