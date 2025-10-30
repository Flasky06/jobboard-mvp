<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/session.php';

// Get user role for conditional rendering
$userRole = $_SESSION['role'] ?? null;

// Get profile image path
$profile_image = '/assets/logo/lss_logo.png';
if (isset($_SESSION['user_id']) && $userRole) {
    try {
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User($conn);
        $profile = $userModel->getUserProfile($_SESSION['user_id']);
        if ($profile) {
            switch ($userRole) {
                case 'jobseeker':
                    $profile_image = !empty($profile['profile_picture']) ? $profile['profile_picture'] : '/uploads/profile_photos/default-avatar.png';
                    break;
                case 'employer':
                    $profile_image = !empty($profile['company_logo']) ? $profile['company_logo'] : '/uploads/company_logos/default-logo.png';
                    break;
                case 'admin':
                    $profile_image = !empty($profile['admin_photo']) ? $profile['admin_photo'] : '/uploads/profile_photos/default-avatar.png';
                    break;
            }
        }
    } catch (Exception $e) {
        // Silently fail and use default image
        error_log("Profile image error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'LSS Systems'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- TinyMCE CDN -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="/js/tinymce-config.js"></script>
    <script src="/js/navbar.js"></script>
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow mb-6">
        <div class="container mx-auto px-4">
            <!-- Desktop & Mobile Header -->
            <div class="flex justify-between items-center py-4">
                <!-- Logo/Brand -->
                <a href="<?php echo $userRole ? ($userRole === 'jobseeker' ? '//' : ($userRole === 'employer' ? '/dashboard/employer-dashboard.php' : '/dashboard/admin-dashboard.php')) : '//'; ?>"
                    class="font-bold text-lg text-blue-700 flex-shrink-0 flex items-center">
                    <img src="/assets/logo/Iss_logo.png" alt="LSS Systems Logo" class="h-8 w-8 mr-2">
                    LSS
                    Systems<?php echo $userRole === 'admin' ? ' - Admin' : ($userRole === 'employer' ? ' - Employer' : ''); ?>
                </a>

                <!-- Desktop Navigation (hidden on mobile/tablet) -->
                <div class="hidden md:flex items-center space-x-6">
                    <?php if ($userRole === 'jobseeker'): ?>
                    <a href="/" class="hover:text-blue-600 transition-colors">Home</a>
                    <a href="/jobs/saved-jobs.php" class="hover:text-blue-600 transition-colors">Saved Jobs</a>
                    <a href="/companies.php" class="hover:text-blue-600 transition-colors">Companies</a>
                    <a href="/profile.php" class="hover:text-blue-600 transition-colors">Profile</a>
                    <?php elseif ($userRole === 'employer'): ?>
                    <a href="/dashboard/employer-dashboard.php"
                        class="hover:text-blue-600 transition-colors">Dashboard</a>
                    <a href="/applications/applications.php"
                        class="hover:text-blue-600 transition-colors">Applications</a>
                    <a href="/jobs/my-jobs.php" class="hover:text-blue-600 transition-colors">My Jobs</a>
                    <a href="/jobs/post-job.php" class="hover:text-blue-600 transition-colors">Post Job</a>
                    <?php elseif ($userRole === 'admin'): ?>
                    <a href="dashboard/admin-dashboard.php" class="hover:text-blue-600 transition-colors">Dashboard</a>
                    <a href="dashboard/admin-profile.php" class="hover:text-blue-600 transition-colors">Profile</a>
                    <?php endif; ?>

                    <?php if ($userRole): ?>
                    <!-- Profile Dropdown (Desktop) -->
                    <div class="relative">
                        <button id="profileDropdownBtn"
                            class="flex items-center space-x-2 p-1 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                            <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile"
                                class="w-8 h-8 rounded-full object-cover border-2 border-gray-300"
                                onerror="this.src='/uploads/profile_photos/default-avatar.png'">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div id="profileDropdown"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 hidden">
                            <div class="py-1">
                                <a href="<?php echo $userRole === 'jobseeker' ? '/profile.php' : ($userRole === 'employer' ? '/dashboard/employer-profile.php' : '/dashboard/admin-profile.php'); ?>"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                    <?php echo $userRole === 'employer' ? 'Company Profile' : 'Profile'; ?>
                                </a>
                                <a href="/auth/logout.php"
                                    class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                        </path>
                                    </svg>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Guest Links (Desktop) -->
                    <a href="auth/login.php" class="hover:text-blue-600 transition-colors">Login</a>
                    <a href="auth/register.php"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">Register</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button (Hamburger) -->
                <button id="mobileMenuBtn"
                    class="md:hidden focus:outline-none p-2 hover:bg-gray-100 rounded-md transition-colors"
                    aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu (shown on small screens) -->
            <div id="mobileMenu" class="md:hidden hidden border-t border-gray-200">
                <div class="px-2 py-3 space-y-1">
                    <?php if ($userRole === 'jobseeker'): ?>
                    <a href="/"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-home w-5 inline-block"></i> Home
                    </a>
                    <a href="/jobs/saved-jobs.php"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-bookmark w-5 inline-block"></i> Saved Jobs
                    </a>
                    <a href="/companies.php"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-building w-5 inline-block"></i> Companies
                    </a>
                    <a href="/profile.php"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-user w-5 inline-block"></i> Profile
                    </a>
                    <hr class="my-2 border-gray-200">
                    <a href="/auth/logout.php"
                        class="block px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-md transition-colors">
                        <i class="fas fa-sign-out-alt w-5 inline-block"></i> Logout
                    </a>
                    <?php elseif ($userRole === 'employer'): ?>
                    <a href="/dashboard/employer-dashboard.php"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-tachometer-alt w-5 inline-block"></i> Dashboard
                    </a>
                    <a href="/applications/applications.php"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-file-alt w-5 inline-block"></i> Applications
                    </a>
                    <a href="/jobs/my-jobs.php"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-briefcase w-5 inline-block"></i> My Jobs
                    </a>
                    <a href="/jobs/post-job.php"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-plus-circle w-5 inline-block"></i> Post Job
                    </a>
                    <a href="/dashboard/employer-profile.php"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-building w-5 inline-block"></i> Company Profile
                    </a>
                    <hr class="my-2 border-gray-200">
                    <a href="/auth/logout.php"
                        class="block px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-md transition-colors">
                        <i class="fas fa-sign-out-alt w-5 inline-block"></i> Logout
                    </a>
                    <?php elseif ($userRole === 'admin'): ?>
                    <a href="dashboard/admin-dashboard.php"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-tachometer-alt w-5 inline-block"></i> Dashboard
                    </a>
                    <a href="dashboard/admin-profile.php"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-user-shield w-5 inline-block"></i> Profile
                    </a>
                    <hr class="my-2 border-gray-200">
                    <a href="/auth/logout.php"
                        class="block px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-md transition-colors">
                        <i class="fas fa-sign-out-alt w-5 inline-block"></i> Logout
                    </a>
                    <?php else: ?>
                    <!-- Guest Mobile Menu -->
                    <a href="auth/login.php"
                        class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md transition-colors">
                        <i class="fas fa-sign-in-alt w-5 inline-block"></i> Login
                    </a>
                    <a href="auth/register.php"
                        class="block px-3 py-2 text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors text-center font-medium">
                        <i class="fas fa-user-plus w-5 inline-block"></i> Register
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const dropdownBtn = document.getElementById("profileDropdownBtn");
        const dropdown = document.getElementById("profileDropdown");
        const mobileMenuBtn = document.getElementById("mobileMenuBtn");
        const mobileMenu = document.getElementById("mobileMenu");

        // Profile dropdown
        if (dropdownBtn && dropdown) {
            dropdownBtn.addEventListener("click", function(e) {
                e.stopPropagation();
                dropdown.classList.toggle("hidden");
            });

            // Close dropdown when clicking outside
            document.addEventListener("click", function(e) {
                if (!dropdown.contains(e.target) && !dropdownBtn.contains(e.target)) {
                    dropdown.classList.add("hidden");
                }
            });

            // Close dropdown on Escape key
            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape") {
                    dropdown.classList.add("hidden");
                }
            });
        }

        // Mobile menu toggle
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener("click", function(e) {
                e.stopPropagation();
                mobileMenu.classList.toggle("hidden");
            });

            // Close mobile menu when clicking outside
            document.addEventListener("click", function(e) {
                if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                    mobileMenu.classList.add("hidden");
                }
            });
        }
    });
    </script>
    <main class="container mx-auto p-4">