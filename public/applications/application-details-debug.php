<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../controllers/ApplicationController.php';

// Check if user is employer
if (!isEmployer()) {
    redirect('/home.php');
}

$applicationId = $_GET['id'] ?? null;

if (!$applicationId) {
    die("Application ID is required");
}

$jobController = new JobController($conn);
$applicationController = new ApplicationController($conn);
$userId = getUserId();

echo "<h1>Application Details Debug</h1>";
echo "<h2>Session Data</h2>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

echo "<h2>User ID</h2>";
echo "<pre>$userId</pre>";

echo "<h2>Employer Profile</h2>";
$employer = $jobController->user->getUserProfile($userId);
echo "<pre>" . print_r($employer, true) . "</pre>";

$employerUuid = $employer['employer_uuid'] ?? null;
echo "<h2>Employer UUID</h2>";
echo "<pre>$employerUuid</pre>";

if (!$employerUuid) {
    die("Employer UUID not found");
}

echo "<h2>Application ID</h2>";
echo "<pre>$applicationId</pre>";

echo "<h2>Raw Application Data from Database</h2>";
$stmt = $conn->prepare("SELECT * FROM applications WHERE uuid = ?");
$stmt->bind_param("s", $applicationId);
$stmt->execute();
$appResult = $stmt->get_result()->fetch_assoc();
echo "<pre>" . print_r($appResult, true) . "</pre>";

if (!$appResult) {
    die("Application not found in database");
}

echo "<h2>Raw Job Data from Database</h2>";
$stmt = $conn->prepare("SELECT * FROM job_posts WHERE uuid = ?");
$stmt->bind_param("s", $appResult['job_uuid']);
$stmt->execute();
$jobResult = $stmt->get_result()->fetch_assoc();
echo "<pre>" . print_r($jobResult, true) . "</pre>";

echo "<h2>Raw Jobseeker Data from Database</h2>";
$stmt = $conn->prepare("SELECT * FROM job_seekers WHERE uuid = ?");
$stmt->bind_param("s", $appResult['job_seeker_uuid']);
$stmt->execute();
$jobseekerResult = $stmt->get_result()->fetch_assoc();
echo "<pre>" . print_r($jobseekerResult, true) . "</pre>";

echo "<h2>Ownership Check</h2>";
$jobBelongsToEmployer = ($jobResult['employer_uuid'] === $employerUuid);
echo "<pre>Job employer_uuid: {$jobResult['employer_uuid']}</pre>";
echo "<pre>Session employer_uuid: $employerUuid</pre>";
echo "<pre>Ownership check: " . ($jobBelongsToEmployer ? 'PASS' : 'FAIL') . "</pre>";

echo "<h2>getApplicationDetails() Result</h2>";
$application = $applicationController->getApplicationDetails($applicationId, $employerUuid);
echo "<pre>" . print_r($application, true) . "</pre>";

if ($application) {
    echo "<h2>Success - Application Details Retrieved</h2>";
} else {
    echo "<h2>Failed - Application Details Not Retrieved</h2>";
}
?>