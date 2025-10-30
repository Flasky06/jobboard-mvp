<?php
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/auth.php';

class JobController {
    public $job;
    public $user;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->job = new Job($conn);
        $this->user = new User($conn);
    }

    public function postJob() {
        // FIX: Use helper functions
        if (!isAuthenticated() || !isEmployer()) {
            header("Location: /auth/login.php");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF validation
            if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
                $_SESSION['errors'] = ["Invalid request. Please try again."];
                header("Location: post-job.php");
                exit;
            }

            $title = trim($_POST['title']);
            $job_description = trim($_POST['job_description']);
            $location = trim($_POST['location']);
            $job_type = $_POST['job_type'] ?? null;
            $job_level = $_POST['job_level'] ?? null;
            $industry = $_POST['industry'] ?? null;
            $salary_range = trim($_POST['salary_range']);
            $requirements_qualifications = trim($_POST['requirements_qualifications']);
            $additional_information = trim($_POST['additional_information'] ?? '');
            $application_deadline = !empty($_POST['application_deadline']) ? $_POST['application_deadline'] : null;

            // Validation
            $errors = [];
            if (empty($title)) $errors[] = "Job title is required.";
            if (empty($job_description)) $errors[] = "Job description is required.";
            if (empty($location)) $errors[] = "Job location is required.";
            if (empty($requirements_qualifications)) $errors[] = "Job requirements are required.";

            if (empty($errors)) {
                // FIX: Use getUserId() helper
                $userId = getUserId();
                $employer = $this->user->getUserProfile($userId);

                // FIX: Get employer_uuid correctly
                $employerUuid = $employer['employer_uuid'] ?? null;

                if ($employerUuid) {
                    $jobData = [
                        'title' => $title,
                        'job_description' => $job_description,
                        'location' => $location,
                        'job_type' => $job_type,
                        'job_level' => $job_level,
                        'industry' => $industry,
                        'salary_range' => $salary_range,
                        'requirements_qualifications' => $requirements_qualifications,
                        'additional_information' => $additional_information,
                        'application_deadline' => $application_deadline
                    ];

                    if ($this->job->createJob($employerUuid, $jobData)) {
                        $_SESSION['success'] = "Job posted successfully!";
                    } else {
                        $errors[] = "Failed to post job. Please try again.";
                    }
                } else {
                    $errors[] = "Employer profile not found. Please complete your profile first.";
                }
            }

            $_SESSION['errors'] = $errors;
            header("Location: post-job.php");
            exit;
        }
    }

    public function viewJobs() {
        // FIX: Use helper functions
        if (!isAuthenticated() || !isEmployer()) {
            header("Location: /auth/login.php");
            exit;
        }

        // FIX: Use getUserId() helper
        $userId = getUserId();
        $employer = $this->user->getUserProfile($userId);

        if ($employer) {
            $employerUuid = $employer['employer_uuid'] ?? null;
            if ($employerUuid) {
                return $this->job->getJobsByEmployer($employerUuid);
            }
        }

        return [];
    }

    public function editJob() {
        // Check if user is employer
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
            header("Location: auth/login.php");
            exit;
        }

        $jobId = $_GET['id'] ?? 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF validation
            if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
                $_SESSION['errors'] = ["Invalid request. Please try again."];
                header("Location: /jobs/edit-job.php?id=" . $jobId);
                exit;
            }

            // Get employer UUID from user UUID
            $userModel = new User($this->conn);
            $employer = $userModel->getUserProfile($_SESSION['user_id']);

            if (!$employer || !isset($employer['employer_uuid'])) {
                $_SESSION['errors'] = ["Employer profile not found."];
                header("Location: /jobs/my-jobs.php");
                exit;
            }

            $title = trim($_POST['title']);
            $job_description = trim($_POST['job_description']);
            $location = trim($_POST['location']);
            $job_type = $_POST['job_type'] ?? null;
            $job_level = $_POST['job_level'] ?? null;
            $industry = $_POST['industry'] ?? null;
            $salary_range = trim($_POST['salary_range']);
            $requirements_qualifications = trim($_POST['requirements_qualifications']);
            $additional_information = trim($_POST['additional_information'] ?? '');
            $application_deadline = !empty($_POST['application_deadline']) ? $_POST['application_deadline'] : null;

            // Validation
            $errors = [];
            if (empty($title)) $errors[] = "Job title is required.";
            if (empty($job_description)) $errors[] = "Job description is required.";
            if (empty($location)) $errors[] = "Job location is required.";
            if (empty($requirements_qualifications)) $errors[] = "Job requirements are required.";

            if (empty($errors)) {
                $jobData = [
                    'title' => $title,
                    'job_description' => $job_description,
                    'location' => $location,
                    'job_type' => $job_type,
                    'job_level' => $job_level,
                    'industry' => $industry,
                    'salary_range' => $salary_range,
                    'requirements_qualifications' => $requirements_qualifications,
                    'additional_information' => $additional_information,
                    'application_deadline' => $application_deadline
                ];

                if ($this->job->updateJob($jobId, $employer['employer_uuid'], $jobData)) {
                    $_SESSION['success'] = "Job updated successfully!";
                    header("Location: /employer/jobs.php");
                    exit;
                } else {
                    $errors[] = "Failed to update job. Please try again.";
                }
            }

            $_SESSION['errors'] = $errors;
            header("Location: employer/edit-job.php?id=" . $jobId);
            exit;
        }

        // For GET request, return job data
        $job = $this->job->getJobById($jobId);
        return $job;
    }

    public function deleteJob() {
        // Check if user is employer
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
            header("Location: auth/login.php");
            exit;
        }

        $jobId = $_POST['job_id'] ?? 0;

        // Get employer UUID from user UUID
        $userModel = new User($this->conn);
        $employer = $userModel->getUserProfile($_SESSION['user_id']);

        if ($employer && isset($employer['employer_uuid'])) {
            if ($this->job->deleteJob($jobId, $employer['employer_uuid'])) {
                $_SESSION['success'] = "Job deleted successfully!";
            } else {
                $_SESSION['errors'] = ["Failed to delete job."];
            }
        } else {
            $_SESSION['errors'] = ["Employer profile not found."];
        }

        header("Location: /employer/jobs.php");
        exit;
    }

    public function getJobDetails($jobId) {
        return $this->job->getJobById($jobId);
    }
}
?>