<?php
require_once __DIR__ . '/../models/Job.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Application.php';

class ApplicationController {
    public $job;
    public $user;
    public $application;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->job = new Job($conn);
        $this->user = new User($conn);
        $this->application = new Application($conn);
    }

    /**
     * Get all applications for an employer
     */
    public function getEmployerApplications($employerUuid) {
        $stmt = $this->conn->prepare("
            SELECT a.*, jp.title as job_title, jp.uuid as job_uuid, js.phone, js.location, js.professional_title, js.fullName
            FROM applications a
            JOIN job_posts jp ON a.job_uuid = jp.uuid
            JOIN job_seekers js ON a.job_seeker_uuid = js.uuid
            WHERE jp.employer_uuid = ?
            ORDER BY a.applied_at DESC
        ");
        $stmt->bind_param("s", $employerUuid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get recent applications for employer dashboard
     */
    public function getRecentApplications($employerUuid, $limit = 5) {
        $applications = $this->getEmployerApplications($employerUuid);
        return array_slice($applications, 0, $limit);
    }

    /**
     * Get application details by ID - COMPLETELY FIXED VERSION
     */
    public function getApplicationDetails($applicationId, $employerUuid = null) {
        error_log("=== getApplicationDetails START ===");
        error_log("Application ID: " . $applicationId);
        error_log("Employer UUID: " . ($employerUuid ?? 'null'));

        // Get basic application data
        $application = $this->application->getApplicationById($applicationId);

        if (!$application) {
            error_log("ERROR: Application not found in database");
            return false;
        }

        error_log("Found application - Job UUID: " . $application['job_uuid']);

        // Get job details
        $job = $this->job->getJobById($application['job_uuid']);
        
        if (!$job) {
            error_log("ERROR: Job not found - UUID: " . $application['job_uuid']);
            return false;
        }

        error_log("Found job - Title: " . $job['title'] . ", Employer: " . $job['employer_uuid']);

        // If employer UUID provided, verify ownership
        if ($employerUuid) {
            if ($job['employer_uuid'] !== $employerUuid) {
                error_log("ERROR: Ownership mismatch");
                error_log("  Job employer: " . $job['employer_uuid']);
                error_log("  Current employer: " . $employerUuid);
                return false;
            }
            error_log("Ownership verified ✓");
        }

        // Attach job to application
        $application['job'] = $job;

        // Get jobseeker details with email - DIRECT QUERY
        $stmt = $this->conn->prepare("
            SELECT js.*, u.email 
            FROM job_seekers js
            JOIN users u ON js.user_uuid = u.uuid
            WHERE js.uuid = ?
        ");
        
        if (!$stmt) {
            error_log("ERROR: Failed to prepare jobseeker query: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("s", $application['job_seeker_uuid']);
        
        if (!$stmt->execute()) {
            error_log("ERROR: Failed to execute jobseeker query: " . $stmt->error);
            return false;
        }

        $jobseeker = $stmt->get_result()->fetch_assoc();
        
        if (!$jobseeker) {
            error_log("ERROR: Jobseeker not found - UUID: " . $application['job_seeker_uuid']);
            return false;
        }

        error_log("Found jobseeker - Name: " . ($jobseeker['fullName'] ?? 'Unknown') . ", Email: " . ($jobseeker['email'] ?? 'None'));

        // Attach jobseeker to application
        $application['jobseeker'] = $jobseeker;

        error_log("=== getApplicationDetails SUCCESS ===");
        return $application;
    }

    /**
     * Update application status
     */
    public function updateApplicationStatus($applicationId, $status, $employerUuid) {
        // First verify ownership
        $application = $this->getApplicationDetails($applicationId, $employerUuid);

        if (!$application) {
            error_log("updateApplicationStatus: Cannot update - verification failed");
            return false;
        }

        // Only allow 'pending' and 'reviewed' statuses
        $validStatuses = ['pending', 'reviewed'];
        if (!in_array($status, $validStatuses)) {
            error_log("updateApplicationStatus: Invalid status - " . $status);
            return false;
        }

        // Update status with timestamp if changing to reviewed
        if ($status === 'reviewed') {
            $stmt = $this->conn->prepare("
                UPDATE applications 
                SET status = ?, reviewed_at = NOW() 
                WHERE uuid = ?
            ");
            $stmt->bind_param("ss", $status, $applicationId);
            $result = $stmt->execute();
        } else {
            $result = $this->application->updateApplicationStatus($applicationId, $status);
        }
        
        if ($result) {
            error_log("updateApplicationStatus: SUCCESS - App: $applicationId, Status: $status");
        } else {
            error_log("updateApplicationStatus: FAILED - App: $applicationId");
        }

        return $result;
    }

    /**
     * Apply to a job
     */
    public function applyToJob($jobId, $jobseekerUuid, $coverLetter = null, $resumeFile = null) {
        error_log("=== applyToJob START ===");
        error_log("Job ID: " . $jobId);
        error_log("Jobseeker UUID: " . $jobseekerUuid);

        // Check for existing application
        $existingApplications = $this->getApplicationsByJobseeker($jobseekerUuid);
        
        foreach ($existingApplications as $app) {
            if ($app['job_uuid'] === $jobId) {
                error_log("ERROR: Duplicate application - already applied to this job");
                return false;
            }
        }

        // Apply to job
        $result = $this->application->applyToJob($jobId, $jobseekerUuid, $coverLetter, $resumeFile);

        if ($result) {
            error_log("applyToJob: SUCCESS");
        } else {
            error_log("applyToJob: FAILED");
        }

        return $result;
    }

    /**
     * Get applications by job seeker
     */
    public function getApplicationsByJobseeker($jobseekerUuid) {
        return $this->application->getApplicationsByJobseeker($jobseekerUuid);
    }

    /**
     * Get application statistics for employer dashboard
     */
    public function getApplicationStats($employerUuid) {
        $jobs = $this->job->getJobsByEmployer($employerUuid);
        $stats = [
            'total_applications' => 0,
            'pending_applications' => 0,
            'active_jobs' => 0,
            'total_jobs' => count($jobs)
        ];

        foreach ($jobs as $job) {
            if ($job['status'] === 'open') {
                $stats['active_jobs']++;
            }

            $applications = $this->application->getJobApplications($job['uuid'], $employerUuid);
            $stats['total_applications'] += count($applications);

            foreach ($applications as $app) {
                if ($app['status'] === 'pending') {
                    $stats['pending_applications']++;
                }
            }
        }

        return $stats;
    }

    /**
     * Check if user has applied to a job
     */
    public function checkApplicationStatus($jobId, $jobseekerUuid) {
        $applications = $this->getApplicationsByJobseeker($jobseekerUuid);
        foreach ($applications as $app) {
            if ($app['job_uuid'] == $jobId) {
                return $app;
            }
        }
        return false;
    }
}