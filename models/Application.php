<?php
class Application {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Get all applications with optional filters
     */
    public function getAllApplications($filters = []) {
        $where = "";
        $params = [];
        $types = '';

        if (!empty($filters['status'])) {
            $where .= " WHERE a.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (!empty($filters['job'])) {
            $where .= ($where ? " AND" : " WHERE") . " a.job_uuid = ?";
            $params[] = $filters['job'];
            $types .= 's';
        }

        $sql = "
            SELECT a.*, jp.title, js.fullName as jobseeker_name, e.company_name
            FROM applications a
            JOIN job_posts jp ON a.job_uuid = jp.uuid
            JOIN job_seekers js ON a.job_seeker_uuid = js.uuid
            LEFT JOIN employers e ON jp.employer_uuid = e.uuid
            $where
            ORDER BY a.applied_at DESC
        ";

        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if ($stmt->execute()) {
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    /**
     * Get application by ID
     */
    public function getApplicationById($applicationUuid) {
        $stmt = $this->conn->prepare("
            SELECT a.*, jp.title as job_title, jp.employer_uuid, js.fullName, js.phone, js.location
            FROM applications a
            JOIN job_posts jp ON a.job_uuid = jp.uuid
            JOIN job_seekers js ON a.job_seeker_uuid = js.uuid
            WHERE a.uuid = ?
        ");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("s", $applicationUuid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Get applications for a specific job
     */
    public function getJobApplications($jobUuid, $employerUuid) {
        $stmt = $this->conn->prepare("
            SELECT a.*, js.phone, js.location, js.professional_title
            FROM applications a
            JOIN job_seekers js ON a.job_seeker_uuid = js.uuid
            WHERE a.job_uuid = ? AND a.job_uuid IN (
                SELECT uuid FROM job_posts WHERE employer_uuid = ?
            )
            ORDER BY a.applied_at DESC
        ");
        $stmt->bind_param("ss", $jobUuid, $employerUuid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Get applications by job seeker
     */
    public function getApplicationsByJobseeker($jobseekerUuid) {
        $stmt = $this->conn->prepare("
            SELECT a.*, jp.title, e.company_name, jp.location, jp.salary_range
            FROM applications a
            JOIN job_posts jp ON a.job_uuid = jp.uuid
            LEFT JOIN employers e ON jp.employer_uuid = e.uuid
            WHERE a.job_seeker_uuid = ?
            ORDER BY a.applied_at DESC
        ");
        $stmt->bind_param("s", $jobseekerUuid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Update application status
     */
    public function updateApplicationStatus($applicationUuid, $status) {
        $stmt = $this->conn->prepare("UPDATE applications SET status = ? WHERE uuid = ?");
        $stmt->bind_param("ss", $status, $applicationUuid);
        return $stmt->execute();
    }

    /**
     * Apply to a job
     */
    public function applyToJob($jobUuid, $jobseekerUuid, $coverLetter = null, $resumeFile = null) {
        $applicationUuid = $this->generateUUID();

        $stmt = $this->conn->prepare("
            INSERT INTO applications (uuid, job_uuid, job_seeker_uuid, cover_letter, resume_file)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("sssss",
            $applicationUuid,
            $jobUuid,
            $jobseekerUuid,
            $coverLetter,
            $resumeFile
        );

        return $stmt->execute();
    }

    /**
     * Update application
     */
    public function updateApplication($applicationUuid, $jobseekerUuid, $data) {
        $allowedFields = ['cover_letter', 'resume_file'];
        $updates = [];
        $types = '';
        $values = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = ?";
                $types .= 's';
                $values[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $applicationUuid;
        $values[] = $jobseekerUuid;
        $types .= 'ss';

        $sql = "UPDATE applications SET " . implode(', ', $updates) . " WHERE uuid = ? AND job_seeker_uuid = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    /**
     * Delete application
     */
    public function deleteApplication($applicationUuid, $jobseekerUuid) {
        $stmt = $this->conn->prepare("DELETE FROM applications WHERE uuid = ? AND job_seeker_uuid = ?");
        $stmt->bind_param("ss", $applicationUuid, $jobseekerUuid);
        return $stmt->execute();
    }

    /**
     * Update application admin
     */
    public function updateApplicationAdmin($applicationUuid, $data) {
        $allowedFields = ['status', 'reviewed_at'];
        $updates = [];
        $types = '';
        $values = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = ?";
                $types .= 's';
                $values[] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        $values[] = $applicationUuid;
        $types .= 's';

        $sql = "UPDATE applications SET " . implode(', ', $updates) . " WHERE uuid = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    /**
     * Delete application admin
     */
    public function deleteApplicationAdmin($applicationUuid) {
        $stmt = $this->conn->prepare("DELETE FROM applications WHERE uuid = ?");
        $stmt->bind_param("s", $applicationUuid);
        return $stmt->execute();
    }

    /**
     * Generate UUID
     */
    private function generateUUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>