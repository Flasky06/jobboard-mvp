<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/session.php';

// Redirect based on user role
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'employer':
            header("Location: /dashboard/employer-profile.php");
            exit;
        case 'admin':
            header("Location: /dashboard/admin-profile.php");
            exit;
        case 'jobseeker':
        default:
            // Show jobseeker profile
            break;
    }
}

require_once __DIR__ . '/../controllers/ProfileController.php';
require_once __DIR__ . '/../config/db.php';

$profileController = new ProfileController($conn);

// Handle form submission (only if not viewing another user's profile)
$isViewingOther = isset($_GET['view']) || isset($_GET['id']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isViewingOther) {
    $profileController->updateProfile();
}

// Get profile data
$data = $profileController->showProfile();
$profile = $data['profile'];
$additionalData = $data['additionalData'];
$isViewingOther = $additionalData['isViewingOther'] ?? false;

$title = "Job Seeker Profile";
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <!-- Profile Picture Header -->
    <?php if (!empty($profile['profile_picture'])): ?>
    <div class="bg-white rounded-lg shadow-md p-6 mb-6 text-center">
        <img src="<?php echo htmlspecialchars($profile['profile_picture']); ?>" alt="Profile Picture"
            class="w-24 h-24 rounded-full object-cover mx-auto mb-4 border-4 border-gray-200">
        <h1 class="text-2xl font-bold text-gray-800">
            <?php echo htmlspecialchars($profile['fullName'] ?? 'Full Name'); ?></h1>
        <p class="text-gray-600"><?php echo htmlspecialchars($profile['professional_title'] ?? 'Professional Title'); ?>
        </p>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">My Profile</h1>
        <p class="text-gray-600 mb-6">Manage your profile information and settings.</p>

        <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['errors'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                <?php foreach ($_SESSION['errors'] as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <?php unset($_SESSION['errors']); ?>
        </div>
        <?php endif; ?>

        <!-- Profile Display -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Basic Information</h2>
                <?php if (!$isViewingOther): ?>
                <button id="editProfileBtn"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    Edit Profile
                </button>
                <?php endif; ?>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 mb-2">Email</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($profile['email'] ?? ''); ?></p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Change Password</label>
                    <p class="text-sm text-gray-600 mb-2">Click the Edit Profile button to request a password reset link
                        via email.</p>
                </div>
            </div>
        </div>

        <!-- Job Seeker Specific Fields -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4">Professional Information</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <?php if (!empty($profile['fullName'])): ?>
                <div>
                    <label class="block text-gray-700 mb-2">Full Name</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($profile['fullName']); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['phone'])): ?>
                <div>
                    <label class="block text-gray-700 mb-2">Phone Number</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($profile['phone']); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['professional_title'])): ?>
                <div>
                    <label class="block text-gray-700 mb-2">Professional Title</label>
                    <p class="text-gray-900">
                        <?php echo htmlspecialchars($profile['professional_title']); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['gender'])): ?>
                <div>
                    <label class="block text-gray-700 mb-2">Gender</label>
                    <p class="text-gray-900">
                        <?php echo htmlspecialchars(ucfirst($profile['gender'])); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['dob'])): ?>
                <div>
                    <label class="block text-gray-700 mb-2">Date of Birth</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($profile['dob']); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['location'])): ?>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Location</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($profile['location']); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['bio'])): ?>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Bio</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($profile['bio']); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($additionalData['skills'])): ?>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Skills</label>
                    <p class="text-gray-900">
                        <?php echo htmlspecialchars(implode(', ', $additionalData['skills'])); ?>
                    </p>
                </div>
                <?php endif; ?>
                <?php if (!empty($additionalData['education'])): ?>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Education</label>
                    <p class="text-gray-900">
                        <?php echo htmlspecialchars(implode(', ', $additionalData['education'])); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal -->
        <?php if (!$isViewingOther): ?>
        <div id="profileModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Edit Profile</h3>
                        <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <form action="profile.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                        <!-- Basic Information -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold mb-4">Basic Information</h4>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Email</label>
                                    <input type="email" name="email"
                                        value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" readonly
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100">
                                </div>
                                <div class="md:col-span-2">
                                    <script>
                                    // Modal functionality
                                    const modal = document.getElementById('profileModal');
                                    const editBtn = document.getElementById('editProfileBtn');
                                    const closeBtn = document.getElementById('closeModalBtn');
                                    const cancelBtn = document.getElementById('cancelBtn');

                                    // Open modal
                                    editBtn.addEventListener('click', () => {
                                        modal.classList.remove('hidden');
                                    });

                                    // Close modal functions
                                    const closeModal = () => {
                                        modal.classList.add('hidden');
                                    };

                                    closeBtn.addEventListener('click', closeModal);
                                    cancelBtn.addEventListener('click', closeModal);

                                    // Close modal when clicking outside
                                    modal.addEventListener('click', (e) => {
                                        if (e.target === modal) {
                                            closeModal();
                                        }
                                    });

                                    // Close modal on Escape key
                                    document.addEventListener('keydown', (e) => {
                                        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                                            closeModal();
                                        }
                                    });
                                    </script>
                                    <label class="block text-gray-700 mb-2">Change Password</label>
                                    <p class="text-sm text-gray-600 mb-2">Click the button below to receive a password
                                        reset link via email.</p>
                                    <button type="submit" name="request_password_reset" value="1"
                                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                                        Send Password Reset Email
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Job Seeker Specific Fields -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold mb-4">Professional Information</h4>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Full Name</label>
                                    <input type="text" name="fullName"
                                        value="<?php echo htmlspecialchars($profile['fullName'] ?? ''); ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Phone Number</label>
                                    <input type="tel" name="phone"
                                        value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Professional Title</label>
                                    <input type="text" name="professional_title"
                                        value="<?php echo htmlspecialchars($profile['professional_title'] ?? ''); ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Gender</label>
                                    <select name="gender"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Select Gender</option>
                                        <option value="male"
                                            <?php echo ($profile['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male
                                        </option>
                                        <option value="female"
                                            <?php echo ($profile['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>
                                            Female</option>
                                        <option value="other"
                                            <?php echo ($profile['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>
                                            Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Date of Birth</label>
                                    <input type="date" name="dob"
                                        value="<?php echo htmlspecialchars($profile['dob'] ?? ''); ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Location</label>
                                    <input type="text" name="location"
                                        value="<?php echo htmlspecialchars($profile['location'] ?? ''); ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Bio</label>
                                    <textarea name="bio" rows="4"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Skills</label>
                                    <input type="text" name="skills"
                                        value="<?php echo htmlspecialchars(isset($additionalData['skills']) ? implode(', ', $additionalData['skills']) : ''); ?>"
                                        placeholder="e.g., PHP, JavaScript, MySQL"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-gray-700 mb-2">Education</label>
                                    <textarea name="education" rows="3" placeholder="List your educational background"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars(isset($additionalData['education']) ? implode("\n", $additionalData['education']) : ''); ?></textarea>
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Profile Picture</label>
                                    <input type="file" name="profile_photo" accept="image/*"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <button type="button" id="cancelBtn"
                                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">Cancel</button>
                            <button type="submit"
                                class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Save
                                Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>