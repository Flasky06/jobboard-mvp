<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/ApplicationController.php';

// Check if user is authenticated and employer
if (!isAuthenticated() || !isEmployer()) {
    http_response_code(403);
    echo "Access denied";
    exit;
}

$applicationId = $_GET['id'] ?? null;

if (!$applicationId) {
    http_response_code(400);
    echo "Application ID required";
    exit;
}

$applicationController = new ApplicationController($conn);
$userId = getUserId();
$employer = $applicationController->user->getUserProfile($userId);
$employerUuid = $employer['employer_uuid'] ?? null;

if (!$employerUuid) {
    http_response_code(403);
    echo "Employer profile not found";
    exit;
}

// Verify ownership and get application
$application = $applicationController->getApplicationDetails($applicationId, $employerUuid);

// Debug: Check if application exists
if (!$application) {
    error_log("Application not found: " . $applicationId);
    http_response_code(404);
    echo "Application not found";
    exit;
}

if (empty($application['resume_file'])) {
    error_log("No resume file for application: " . $applicationId);
    http_response_code(404);
    echo "Resume not found";
    exit;
}

$resumePath = $application['resume_file'];

// Convert relative path to absolute file path
// Database stores /uploads/resumes/filename.pdf
// Convert to absolute path: C:\xampp\htdocs\job-finder\uploads\resumes\filename.pdf
// Since DOCUMENT_ROOT might not be set in CLI, use relative path from project root
$projectRoot = __DIR__ . '/../../'; // public/applications/ -> job-finder/
$filePath = realpath($projectRoot . ltrim($resumePath, '/'));

if (!file_exists($filePath)) {
    error_log("File not found: " . $filePath . " (from resume_path: " . $resumePath . ")");
    http_response_code(404);
    echo "File not found on server";
    exit;
}

// Get file info
$fileName = basename($filePath);
$fileSize = filesize($filePath);

// Set headers for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Clear output buffer
if (ob_get_level()) {
    ob_clean();
}

// Read and output file
readfile($filePath);
exit;
?>