<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';

// Redirect based on user role - only employers and admins can view applicant profiles
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'employer':
        case 'admin':
            // Allow access
            break;
        case 'jobseeker':
        default:
            header("Location: /home.php");
            exit;
    }
} else {
    header("Location: /auth/login.php");
    exit;
}

require_once __DIR__ . '/../../controllers/ProfileController.php';
require_once __DIR__ . '/../../config/db.php';

$profileController = new ProfileController($conn);

// Get profile data
$data = $profileController->showProfile();
$profile = $data['profile'];
$additionalData = $data['additionalData'];

$title = "Applicant Profile - " . htmlspecialchars($profile['fullName'] ?? 'Unknown');
include __DIR__ . '/../../includes/header.php';
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
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Applicant Profile</h1>
        <p class="text-gray-600 mb-6">View applicant information and qualifications.</p>

        <!-- Profile Display -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4">Basic Information</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 mb-2">Email</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars($profile['email'] ?? 'Not provided'); ?></p>
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
                    <p class="text-gray-900"><?php echo htmlspecialchars($profile['professional_title']); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['gender'])): ?>
                <div>
                    <label class="block text-gray-700 mb-2">Gender</label>
                    <p class="text-gray-900"><?php echo htmlspecialchars(ucfirst($profile['gender'])); ?></p>
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
                    <p class="text-gray-900"><?php echo htmlspecialchars(implode(', ', $additionalData['skills'])); ?>
                    </p>
                </div>
                <?php endif; ?>
                <?php if (!empty($additionalData['education'])): ?>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-2">Education</label>
                    <p class="text-gray-900">
                        <?php echo htmlspecialchars(implode(', ', $additionalData['education'])); ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['profile_picture'])): ?>
                <div>
                    <label class="block text-gray-700 mb-2">Profile Picture</label>
                    <img src="<?php echo htmlspecialchars($profile['profile_picture']); ?>" alt="Profile Picture"
                        class="w-20 h-20 rounded-full object-cover">
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>