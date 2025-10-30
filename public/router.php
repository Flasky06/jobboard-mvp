<?php
// Router for PHP built-in server to handle clean URLs

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove query string
$path = strtok($path, '?');

// Remove leading and trailing slashes
$path = trim($path, '/');

// Define all routes - NO .php extension in paths
$routes = [
    '' => 'index.php',
    'home' => 'index.php',
    'profile' => 'profile.php',

    // Auth routes
    'auth/login' => 'auth/login.php',
    'auth/register' => 'auth/register.php',
    'auth/logout' => 'auth/logout.php',
    'auth/google' => 'auth/google.php',
    'auth/google/callback' => 'auth/google/callback.php',

    // Dashboard routes
    'employer-dashboard' => 'dashboard/employer-dashboard.php',
    'employer-profile' => 'dashboard/employer-profile.php',
    'admin-dashboard' => 'dashboard/admin-dashboard.php',
    'admin-profile' => 'dashboard/admin-profile.php',
    
    // Alternative paths for dashboard
    'dashboard/employer-dashboard' => 'dashboard/employer-dashboard.php',
    'dashboard/employer-profile' => 'dashboard/employer-profile.php',
    'dashboard/admin-dashboard' => 'dashboard/admin-dashboard.php',
    'dashboard/admin-profile' => 'dashboard/admin-profile.php',

    // Job routes
    'jobs' => 'jobs/index.php',
    'jobs/my-jobs' => 'jobs/my-jobs.php',
    'jobs/post-job' => 'jobs/post-job.php',
    'jobs/saved-jobs' => 'jobs/saved-jobs.php',
    'jobs/job-details' => 'jobs/job-details.php',
    'jobs/job-applications' => 'jobs/job-applications.php',
    'jobs/edit-job' => 'jobs/edit-job.php',
    'jobs/delete-job' => 'jobs/delete-job.php',

    // Application routes
    'applications/applications' => 'applications/applications.php',
    'applications/apply-job' => 'applications/apply-job.php',
    'applications/application-details' => 'applications/application-details.php',
    'applications/download-resume' => 'applications/download-resume.php',
    'applications/update-application-status' => 'applications/update-application-status.php',

    // Admin routes
    'admin/users' => 'admin/users.php',
    'admin/employers' => 'admin/employers.php',
    'admin/jobs' => 'admin/jobs.php',
    'admin/staff' => 'admin/staff.php',
];

// Check if path matches a specific route
if (isset($routes[$path])) {
    $file = __DIR__ . '/' . $routes[$path];
    if (file_exists($file)) {
        require $file;
        exit;
    }
}

// Check if file exists directly (for assets, CSS, JS, images)
$file = __DIR__ . '/' . $path;
if (file_exists($file) && !is_dir($file)) {
    return false; // Let PHP built-in server handle it
}

// Check if adding .php makes it exist (fallback)
$fileWithPhp = __DIR__ . '/' . $path . '.php';
if (file_exists($fileWithPhp)) {
    require $fileWithPhp;
    exit;
}

// 404 - Not Found
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <h1 class="text-6xl font-bold text-gray-800 mb-4">404</h1>
        <p class="text-xl text-gray-600 mb-8">Page Not Found</p>
        <a href="/" class="px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
            Go Home
        </a>
    </div>
</body>

</html>
<?php
exit;
?>