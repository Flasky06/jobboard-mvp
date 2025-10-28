<?php
class Job {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createJob($employerUuid, $data) {
        $jobUuid = $this->generateUUID();

        $stmt = $this->conn->prepare("
            INSERT INTO job_posts (uuid, employer_uuid, title, job_level, job_description, job_type, industry, location, salary_range, additional_information, requirements_qualifications, application_deadline)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $job_level = $data['job_level'] ?? null;
        $job_type = $data['job_type'] ?? null;
        $industry = $data['industry'] ?? null;
        $additional_information = $data['additional_information'] ?? null;
        $application_deadline = $data['application_deadline'] ?? null;

        $stmt->bind_param("ssssssssssss",
            $jobUuid,
            $employerUuid,
            $data['title'],
            $job_level,
            $data['job_description'],
            $job_type,
            $industry,
            $data['location'],
            $data['salary_range'],
            $additional_information,
            $data['requirements_qualifications'],
            $application_deadline
        );

        return $stmt->execute();
    }

    public function getJobsByEmployer($employerUuid) {
        $stmt = $this->conn->prepare("
            SELECT jp.*, e.company_name
            FROM job_posts jp
            LEFT JOIN employers e ON jp.employer_uuid = e.uuid
            WHERE jp.employer_uuid = ?
            ORDER BY jp.created_at DESC
        ");
        $stmt->bind_param("s", $employerUuid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getJobById($jobUuid) {
        $stmt = $this->conn->prepare("
            SELECT jp.*, e.company_name, e.contact_number
            FROM job_posts jp
            LEFT JOIN employers e ON jp.employer_uuid = e.uuid
            WHERE jp.uuid = ?
        ");
        $stmt->bind_param("s", $jobUuid);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateJob($jobUuid, $employerUuid, $data) {
        $stmt = $this->conn->prepare("
            UPDATE job_posts
            SET title = ?, job_level = ?, job_description = ?, job_type = ?, industry = ?, location = ?, salary_range = ?, additional_information = ?, requirements_qualifications = ?, application_deadline = ?
            WHERE uuid = ? AND employer_uuid = ?
        ");

        $stmt->bind_param("sssssssssss",
            $data['title'],
            $data['job_level'] ?? null,
            $data['job_description'],
            $data['job_type'] ?? null,
            $data['industry'] ?? null,
            $data['location'],
            $data['salary_range'],
            $data['additional_information'] ?? null,
            $data['requirements_qualifications'],
            $data['application_deadline'] ?? null,
            $jobUuid,
            $employerUuid
        );

        return $stmt->execute();
    }

    public function deleteJob($jobUuid, $employerUuid) {
        $stmt = $this->conn->prepare("DELETE FROM job_posts WHERE uuid = ? AND employer_uuid = ?");
        $stmt->bind_param("ss", $jobUuid, $employerUuid);
        return $stmt->execute();
    }

    public function getAllJobs($filters = []) {
        $where = "WHERE jp.status = 'open'";
        $params = [];
        $types = '';

        if (!empty($filters['location'])) {
            $where .= " AND jp.location LIKE ?";
            $params[] = '%' . $filters['location'] . '%';
            $types .= 's';
        }

        if (!empty($filters['job_type'])) {
            $where .= " AND jp.job_type = ?";
            $params[] = $filters['job_type'];
            $types .= 's';
        }

        if (!empty($filters['industry'])) {
            $where .= " AND jp.industry = ?";
            $params[] = $filters['industry'];
            $types .= 's';
        }

        if (!empty($filters['search'])) {
            $where .= " AND (jp.title LIKE ? OR jp.job_description LIKE ? OR jp.requirements_qualifications LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= 'sss';
        }

        $sql = "
            SELECT jp.*, e.company_name
            FROM job_posts jp
            LEFT JOIN employers e ON jp.employer_uuid = e.uuid
            $where
            ORDER BY jp.created_at DESC
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


    public function getAllJobsAdmin($filters = []) {
        $where = "";
        $params = [];
        $types = '';

        if (!empty($filters['status'])) {
            $where .= " WHERE jp.status = ?";
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (!empty($filters['employer'])) {
            $where .= ($where ? " AND" : " WHERE") . " jp.employer_uuid = ?";
            $params[] = $filters['employer'];
            $types .= 's';
        }

        $sql = "
            SELECT jp.*, e.company_name
            FROM job_posts jp
            LEFT JOIN employers e ON jp.employer_uuid = e.uuid
            $where
            ORDER BY jp.created_at DESC
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

    public function updateJobAdmin($jobUuid, $data) {
        $allowedFields = ['title', 'job_level', 'job_description', 'job_type', 'industry', 'location', 'salary_range',
        'additional_information', 'requirements_qualifications', 'status', 'application_deadline'];
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

        $values[] = $jobUuid;
        $types .= 's';

        $sql = "UPDATE job_posts SET " . implode(', ', $updates) . " WHERE uuid = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param($types, ...$values);
        return $stmt->execute();
    }

    public function deleteJobAdmin($jobUuid) {
        $stmt = $this->conn->prepare("DELETE FROM job_posts WHERE uuid = ?");
        $stmt->bind_param("s", $jobUuid);
        return $stmt->execute();
    }


    public function saveJob($jobUuid, $jobseekerUuid) {
        $savedJobUuid = $this->generateUUID();

        $stmt = $this->conn->prepare("
            INSERT INTO saved_jobs (uuid, job_uuid, job_seeker_uuid)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE saved_at = CURRENT_TIMESTAMP
        ");

        $stmt->bind_param("sss",
            $savedJobUuid,
            $jobUuid,
            $jobseekerUuid
        );

        return $stmt->execute();
    }

    public function unsaveJob($jobUuid, $jobseekerUuid) {
        $stmt = $this->conn->prepare("DELETE FROM saved_jobs WHERE job_uuid = ? AND job_seeker_uuid = ?");
        $stmt->bind_param("ss", $jobUuid, $jobseekerUuid);
        return $stmt->execute();
    }

    public function isJobSaved($jobUuid, $jobseekerUuid) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count FROM saved_jobs
            WHERE job_uuid = ? AND job_seeker_uuid = ?
        ");
        $stmt->bind_param("ss", $jobUuid, $jobseekerUuid);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'] > 0;
    }

    public function getSavedJobs($jobseekerUuid) {
        $stmt = $this->conn->prepare("
            SELECT jp.*, e.company_name, sj.saved_at
            FROM saved_jobs sj
            JOIN job_posts jp ON sj.job_uuid = jp.uuid
            LEFT JOIN employers e ON jp.employer_uuid = e.uuid
            WHERE sj.job_seeker_uuid = ? AND jp.status = 'open'
            ORDER BY sj.saved_at DESC
        ");
        $stmt->bind_param("s", $jobseekerUuid);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

}
?>