<?php
// Router for PHP built-in server to handle clean URLs

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove query string
$path = strtok($path, '?');

// Remove leading slash
$path = ltrim($path, '/');

// Handle specific routes
$routes = [
    '' => 'index.php',
    'home' => 'home.php',
    'profile' => 'profile.php',

    // Auth routes
    'login' => 'auth/login.php',
    'register' => 'auth/register.php',
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

    // Job routes
    'saved-jobs' => 'jobs/saved-jobs.php',
    'jobs/my-jobs' => 'jobs/my-jobs.php',
    'jobs/post-job' => 'jobs/post-job.php',
    'jobs/saved-jobs' => 'jobs/saved-jobs.php',

    // Application routes
    'applications/applications' => 'applications/applications.php',

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

// Check if file exists directly
$file = __DIR__ . '/' . $path;
if (file_exists($file) && !is_dir($file)) {
    return false; // Let PHP serve the file directly
}

// Check if adding .php makes it exist
$fileWithPhp = __DIR__ . '/' . $path . '.php';
if (file_exists($fileWithPhp)) {
    require $fileWithPhp;
    exit;
}

// If nothing found, return 404
http_response_code(404);
echo "Not Found";
exit;
?>