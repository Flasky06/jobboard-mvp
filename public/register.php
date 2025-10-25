<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../helpers/session.php';

$auth = new AuthController($conn);
$auth->register();

$title = "Register";
include __DIR__ . '/../includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md mt-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Create Your Account</h2>

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

    <form action="register.php" method="post" class="space-y-4">
        <?php echo csrf_field(); ?>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
            <input type="email" id="email" name="email" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <div class="relative">
                <input type="password" id="password" name="password" required
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <button type="button" id="toggle-password"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5">
                    Show
                </button>
            </div>
        </div>

        <div>
            <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>

        <button type="submit"
            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            Create Account
        </button>
    </form>

    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            Already have an account? <a href="login.php" class="text-blue-600 hover:text-blue-500">Login here</a>
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('toggle-password');
    const passwordField = document.getElementById('password');
    toggleBtn.addEventListener('click', function() {
        const isHidden = passwordField.type === 'password';
        passwordField.type = isHidden ? 'text' : 'password';
        toggleBtn.textContent = isHidden ? 'Hide' : 'Show';
    });

    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }

        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long!');
            return false;
        }
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>