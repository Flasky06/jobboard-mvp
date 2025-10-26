<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../controllers/ProfileController.php';
require_once __DIR__ . '/../../config/db.php';

$profileController = new ProfileController($conn);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profileController->updateProfile();
}

// Get profile data
$data = $profileController->showProfile();
$profile = $data['profile'];

$title = "Admin Profile";
include __DIR__ . '/../../includes/admin-header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Admin Profile</h1>
        <p class="text-gray-600 mb-6">Manage your admin profile information.</p>

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
                <button id="editProfileBtn"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                    Edit Profile
                </button>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 mb-2">Email</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($profile['email'] ?? 'Not provided'); ?></p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Change Password</label>
                    <p class="text-sm text-gray-600 mb-2">Click the Edit Profile button to request a password reset link
                        via email.</p>
                </div>
            </div>
        </div>

        <!-- Admin Information Display -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4">Admin Information</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 mb-2">Full Name</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($profile['full_name'] ?? 'Not provided'); ?>
                    </p>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Contact Number</label>
                    <p class="text-gray-900">
                        <?php echo htmlspecialchars($profile['contact_number'] ?? 'Not provided'); ?></p>
                </div>
                <div>
                    <label class="block text-gray-700 mb-2">Admin Photo</label>
                    <?php if (!empty($profile['admin_photo'])): ?>
                    <img src="<?php echo htmlspecialchars($profile['admin_photo']); ?>" alt="Admin Photo"
                        class="w-20 h-20 rounded-full object-cover">
                    <?php else: ?>
                    <p class="text-gray-900">No photo uploaded</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div id="profileModal"
            class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Edit Profile</h3>
                        <button id="closeModalBtn" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>
                    <form action="admin-profile.php" method="POST" enctype="multipart/form-data">
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
                                    <label class="block text-gray-700 mb-2">Change Password</label>
                                    <p class="text-sm text-gray-600 mb-2">Click the button below to receive a password
                                        reset link
                                        via email.</p>
                                    <button type="submit" name="request_password_reset" value="1"
                                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                                        Send Password Reset Email
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Admin Information -->
                        <div class="mb-6">
                            <h4 class="text-md font-semibold mb-4">Admin Information</h4>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 mb-2">Full Name</label>
                                    <input type="text" name="full_name"
                                        value="<?php echo htmlspecialchars($profile['full_name'] ?? ''); ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Contact Number</label>
                                    <input type="tel" name="contact_number"
                                        value="<?php echo htmlspecialchars($profile['contact_number'] ?? ''); ?>"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 mb-2">Admin Photo</label>
                                    <input type="file" name="admin_photo" accept="image/*"
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
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>