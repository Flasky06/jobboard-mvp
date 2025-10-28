<?php
/**
 * Update Application Status
 * 
 * Allows employers to update the status of job applications
 * Validates ownership and CSRF protection
 */

require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../controllers/ApplicationController.php';

// Check if user is employer
if (!isEmployer()) {
    $_SESSION['errors'] = ["Unauthorized access. Employer login required."];
    redirect('/home.php');
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['errors'] = ["Invalid request method."];
    header("Location: applications.php");
    exit;
}

// CSRF validation
if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
    $_SESSION['errors'] = ["Invalid request. Please try again."];
    header("Location: applications.php");
    exit;
}

// Get and validate parameters
$applicationId = $_POST['application_id'] ?? null;
$newStatus = $_POST['status'] ?? null;

if (!$applicationId || !$newStatus) {
    $_SESSION['errors'] = ["Missing required parameters."];
    header("Location: applications.php");
    exit;
}

// Validate status value
$validStatuses = ['pending', 'reviewed'];
if (!in_array($newStatus, $validStatuses)) {
    $_SESSION['errors'] = ["Invalid status value. Must be one of: " . implode(', ', $validStatuses)];
    header("Location: application-details.php?id=" . urlencode($applicationId));
    exit;
}

try {
    // Initialize controllers
    $jobController = new JobController($conn);
    $applicationController = new ApplicationController($conn);
    
    // Get employer information
    $userId = getUserId();
    $employer = $jobController->user->getUserProfile($userId);
    $employerUuid = $employer['employer_uuid'] ?? null;
    
    if (!$employerUuid) {
        $_SESSION['errors'] = ["Employer profile not found. Please complete your profile."];
        header("Location: applications.php");
        exit;
    }
    
    // Get application details to verify ownership
    $application = $applicationController->getApplicationDetails($applicationId, $employerUuid);
    
    if (!$application) {
        $_SESSION['errors'] = ["Application not found or you do not have permission to update it."];
        header("Location: applications.php");
        exit;
    }
    
    // Update application status
    $updateResult = $applicationController->updateApplicationStatus($applicationId, $newStatus, $employerUuid);
    
    if ($updateResult) {
        // Set success message with status-specific text
        $statusMessages = [
            'pending' => 'Application marked as pending.',
            'reviewed' => 'Application marked as reviewed.',
            'accepted' => 'Application accepted! Consider contacting the candidate.',
            'rejected' => 'Application rejected.'
        ];
        
        $_SESSION['success'] = $statusMessages[$newStatus] ?? "Application status updated successfully!";
        
        // Log the status change (optional but recommended)
        error_log("Application {$applicationId} status changed to {$newStatus} by employer {$employerUuid}");
    } else {
        $_SESSION['errors'] = ["Failed to update application status. Please try again."];
        error_log("Failed to update application {$applicationId} status to {$newStatus} by employer {$employerUuid}");
    }
    
} catch (Exception $e) {
    // Log error for debugging
    error_log("Error updating application status: " . $e->getMessage());
    $_SESSION['errors'] = ["An error occurred while updating the application. Please try again."];
}

// Redirect back to application details page
header("Location: application-details.php?id=" . urlencode($applicationId));
exit;
?>