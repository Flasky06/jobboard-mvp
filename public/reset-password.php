<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../helpers/session.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    $_SESSION['errors'] = ["Invalid reset link."];
    header("Location: login.php");
    exit;
}

// Verify token is valid
$userModel = new User($conn);
$user = $userModel->getUserByResetToken($token);

if (!$user) {
    $_SESSION['errors'] = ["Invalid or expired reset link."];
    header("Location: login.php");
    exit;
}

$auth = new AuthController($conn);
$auth->resetPassword();

$title = "Reset Password";
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Reset Your Password</h2>

    <?php if (isset($_SESSION['errors'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside">
            <?php foreach ($_SESSION['errors'] as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <form action="reset-password.php" method="post" class="space-y-4">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
            <input type="password" id="password" name="password" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <button type="submit"
            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Reset Password
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            Remember your password? <a href="login.php" class="text-blue-600 hover:text-blue-500">Login here</a>
        </p>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>