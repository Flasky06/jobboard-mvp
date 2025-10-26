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
        $allApplications = [];
        $jobs = $this->job->getJobsByEmployer($employerUuid);

        foreach ($jobs as $job) {
            $applications = $this->application->getJobApplications($job['uuid'], $employerUuid);
            foreach ($applications as $app) {
                $allApplications[] = array_merge($app, ['job_title' => $job['title'], 'job_uuid' => $job['uuid']]);
            }
        }

        // Sort by applied_at (most recent first)
        usort($allApplications, function($a, $b) {
            return strtotime($b['applied_at']) - strtotime($a['applied_at']);
        });

        return $allApplications;
    }

    /**
     * Get recent applications for employer dashboard
     */
    public function getRecentApplications($employerUuid, $limit = 5) {
        $applications = $this->getEmployerApplications($employerUuid);
        return array_slice($applications, 0, $limit);
    }

    /**
     * Get application details by ID
     */
    public function getApplicationDetails($applicationId, $employerUuid = null) {
        $application = $this->application->getApplicationById($applicationId);

        if (!$application) {
            return false;
        }

        // If employer UUID provided, verify ownership
        if ($employerUuid) {
            $employerJobs = $this->job->getJobsByEmployer($employerUuid);
            $jobBelongsToEmployer = false;

            foreach ($employerJobs as $empJob) {
                if ($empJob['uuid'] === $application['job_uuid']) {
                    $jobBelongsToEmployer = true;
                    $application['job'] = $empJob;
                    break;
                }
            }

            if (!$jobBelongsToEmployer) {
                return false;
            }
        }

        // Get jobseeker details
        $application['jobseeker'] = $this->user->getUserProfileByUuid($application['job_seeker_uuid']);

        return $application;
    }

    /**
     * Update application status
     */
    public function updateApplicationStatus($applicationId, $status, $employerUuid) {
        // First verify ownership
        $application = $this->getApplicationDetails($applicationId, $employerUuid);

        if (!$application) {
            return false;
        }

        // Validate status
        $validStatuses = ['pending', 'reviewed', 'accepted', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return $this->application->updateApplicationStatus($applicationId, $status);
    }

    /**
     * Apply to a job
     */
    public function applyToJob($jobId, $jobseekerUuid, $coverLetter = null, $resumeFile = null) {
        // Check if already applied
        $existingApplications = $this->application->getApplicationsByJobseeker($jobseekerUuid);
        foreach ($existingApplications as $app) {
            if ($app['job_uuid'] === $jobId) {
                return false; // Already applied
            }
        }

        return $this->application->applyToJob($jobId, $jobseekerUuid, $coverLetter, $resumeFile);
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
?>