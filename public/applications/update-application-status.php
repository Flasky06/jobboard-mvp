<?php
require_once __DIR__ . '/../../middleware/auth.php';
require_once __DIR__ . '/../../helpers/session.php';
require_once __DIR__ . '/../../helpers/csrf.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../controllers/JobController.php';
require_once __DIR__ . '/../../controllers/ApplicationController.php';

// Check if user is employer
if (!isEmployer()) {
    redirect('/home.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['errors'] = ["Invalid request. Please try again."];
        header("Location: applications.php");
        exit;
    }

    $applicationId = $_POST['application_id'] ?? null;
    $newStatus = $_POST['status'] ?? null;

    if (!$applicationId || !$newStatus) {
        $_SESSION['errors'] = ["Invalid request parameters."];
        header("Location: applications.php");
        exit;
    }

    // Validate status
    $validStatuses = ['pending', 'reviewed', 'accepted', 'rejected'];
    if (!in_array($newStatus, $validStatuses)) {
        $_SESSION['errors'] = ["Invalid status value."];
        header("Location: applications.php");
        exit;
    }

    $jobController = new JobController($conn);
    $applicationController = new ApplicationController($conn);
    $userId = getUserId();
    $employer = $jobController->user->getUserProfile($userId);
    $employerUuid = $employer['employer_uuid'] ?? null;
    
    if (!$employerUuid) {
        $_SESSION['errors'] = ["Employer profile not found."];
        header("Location: applications.php");
        exit;
    }
    
    // Update application status using ApplicationController
    if ($applicationController->updateApplicationStatus($applicationId, $newStatus, $employerUuid)) {
        $_SESSION['success'] = "Application status updated successfully!";
    } else {
        $_SESSION['errors'] = ["Failed to update application status. Please try again."];
    }

    // Redirect back to application details
    header("Location: application-details.php?id=" . $applicationId);
    exit;
}

// If not POST request, redirect to applications
header("Location: applications.php");
exit;
?>