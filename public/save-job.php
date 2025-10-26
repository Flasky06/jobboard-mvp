<?php
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../controllers/JobController.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../helpers/csrf.php';

header('Content-Type: application/json');

// Check if user is logged in and is a job seeker
// FIX: Use getUserId() helper function instead of $_SESSION['user_id']
if (!isAuthenticated() || !isJobSeeker()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// CSRF validation
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$jobUuid = $_POST['job_uuid'] ?? '';

if (empty($jobUuid)) {
    echo json_encode(['success' => false, 'message' => 'Job UUID is required']);
    exit;
}

$jobController = new JobController($conn);

// FIX: Use getUserId() helper function
$userId = getUserId();

// Get job seeker profile
$jobseeker = $jobController->user->getUserProfile($userId);

if (!$jobseeker) {
    echo json_encode(['success' => false, 'message' => 'Job seeker profile not found']);
    exit;
}

// FIX: Get the correct jobseeker UUID
// For jobseekers, the profile query joins job_seekers table, so we need the jobseeker_uuid
$jobseekerUuid = $jobseeker['jobseeker_uuid'] ?? $jobseeker['uuid'];

if (!$jobseekerUuid) {
    echo json_encode(['success' => false, 'message' => 'Job seeker profile incomplete']);
    exit;
}

try {
    // Check if job is already saved
    $isSaved = $jobController->job->isJobSaved($jobUuid, $jobseekerUuid);

    if ($isSaved) {
        // Unsave the job
        $result = $jobController->job->unsaveJob($jobUuid, $jobseekerUuid);
        $message = $result ? 'Job unsaved successfully' : 'Failed to unsave job';
        echo json_encode(['success' => $result, 'message' => $message, 'saved' => false]);
    } else {
        // Save the job
        $result = $jobController->job->saveJob($jobUuid, $jobseekerUuid);
        $message = $result ? 'Job saved successfully' : 'Failed to save job';
        echo json_encode(['success' => $result, 'message' => $message, 'saved' => true]);
    }
} catch (Exception $e) {
    error_log("Save job error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>