<?php
require_once __DIR__ . '/../../../helpers/session.php';
require_once __DIR__ . '/../../../helpers/csrf.php';

// Check if we have Google user data
if (!isset($_SESSION['google_user_data'])) {
    header('Location: /job-finder/public/auth/login');
    exit;
}

$googleUserData = $_SESSION['google_user_data'];

$title = "Choose Your Role";
include __DIR__ . '/../../../includes/header.php';
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Welcome, <?php echo htmlspecialchars($googleUserData['name']); ?>!
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Please choose your role to complete your registration
            </p>
        </div>

        <form method="POST" action="complete-registration.php" class="mt-8 space-y-6">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

            <div class="space-y-4">
                <div class="relative">
                    <input type="radio" id="jobseeker" name="role" value="jobseeker" class="sr-only peer" required>
                    <label for="jobseeker"
                        class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-blue-600 peer-checked:bg-blue-50">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Job Seeker</div>
                                <div class="text-sm text-gray-500">Find and apply for jobs</div>
                            </div>
                        </div>
                        <div
                            class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-blue-600 peer-checked:bg-blue-600">
                            <div class="w-2 h-2 bg-white rounded-full m-0.5 peer-checked:bg-white"></div>
                        </div>
                    </label>
                </div>

                <div class="relative">
                    <input type="radio" id="employer" name="role" value="employer" class="sr-only peer">
                    <label for="employer"
                        class="flex items-center justify-between p-4 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 peer-checked:border-blue-600 peer-checked:bg-blue-50">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">Employer</div>
                                <div class="text-sm text-gray-500">Post jobs and find talent</div>
                            </div>
                        </div>
                        <div
                            class="w-4 h-4 border-2 border-gray-300 rounded-full peer-checked:border-blue-600 peer-checked:bg-blue-600">
                            <div class="w-2 h-2 bg-white rounded-full m-0.5 peer-checked:bg-white"></div>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Continue
                </button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>