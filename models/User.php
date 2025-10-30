<?php
class User {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Update user profile picture (in users table)
     */
    public function updateUserProfilePicture($userId, $profilePicturePath) {
  $stmt = $this->conn->prepare("UPDATE users SET profile_picture = ?, updated_at = NOW() WHERE uuid = ?");
  if (!$stmt) {
    error_log("Failed to prepare statement: " . $this->conn->error);
    return false;
  }
  $stmt->bind_param("ss", $profilePicturePath, $userId);
  $result = $stmt->execute();
  
  if (!$result) {
    error_log("Failed to update profile picture: " . $stmt->error);
  }
  
  return $result;
}

/**
 * Get user profile - UPDATED VERSION
 * This joins all relevant tables based on user role
 */
public function getUserProfile($userId) {
  // First get the user's role
  $roleStmt = $this->conn->prepare("SELECT role FROM users WHERE uuid = ?");
  if (!$roleStmt) {
    error_log("getUserProfile: Failed to prepare role statement: " . $this->conn->error);
    return false;
  }
  
  $roleStmt->bind_param("s", $userId);
  $roleStmt->execute();
  $roleResult = $roleStmt->get_result();
  
  if ($roleResult->num_rows === 0) {
    error_log("getUserProfile: No user found with uuid: " . $userId);
    return false;
  }
  
  $roleData = $roleResult->fetch_assoc();
  $role = $roleData['role'];
  
  error_log("getUserProfile: Found user with role: " . $role);
  
  // Build query based on role
  if ($role === 'jobseeker') {
    $sql = "
      SELECT 
        u.uuid,
        u.email,
        u.profile_picture,
        u.role,
        u.is_verified,
        u.created_at,
        js.uuid as jobseeker_uuid,
        js.fullName,
        js.phone,
        js.gender,
        js.dob,
        js.location,
        js.bio,
        js.professional_title,
        js.skills,
        js.education,
        js.resume_file,
        js.profile_completed
      FROM users u
      LEFT JOIN job_seekers js ON u.uuid = js.user_uuid
      WHERE u.uuid = ?
    ";
  } elseif ($role === 'employer') {
    $sql = "
      SELECT 
        u.uuid,
        u.email,
        u.profile_picture,
        u.role,
        u.is_verified,
        u.created_at,
        e.uuid as employer_uuid,
        e.company_name,
        e.contact_number,
        e.location,
        e.industry,
        e.website,
        e.company_logo,
        e.about_company
      FROM users u
      LEFT JOIN employers e ON u.uuid = e.user_uuid
      WHERE u.uuid = ?
    ";
  } elseif ($role === 'admin') {
    $sql = "
      SELECT 
        u.uuid,
        u.email,
        u.profile_picture,
        u.role,
        u.is_verified,
        u.created_at,
        a.uuid as admin_uuid,
        a.full_name,
        a.contact_number
      FROM users u
      LEFT JOIN admin_profiles a ON u.uuid = a.user_uuid
      WHERE u.uuid = ?
    ";
  } else {
    // Default: just return user data
    $sql = "SELECT * FROM users WHERE uuid = ?";
  }
  
  $stmt = $this->conn->prepare($sql);
  if (!$stmt) {
    error_log("getUserProfile: Failed to prepare main statement: " . $this->conn->error);
    return false;
  }
  
  $stmt->bind_param("s", $userId);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows === 0) {
    error_log("getUserProfile: Query returned no results");
    return false;
  }
  
  $profile = $result->fetch_assoc();
  error_log("getUserProfile: Profile fetched successfully. Keys: " . implode(', ', array_keys($profile)));
  
  return $profile;
}

  public function register($name, $email, $password, $role = 'jobseeker') {
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $uuid = $this->generateUUID();

    $stmt = $this->conn->prepare("INSERT INTO users (uuid, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $uuid, $email, $hashed, $role);
    $result = $stmt->execute();

    if ($result) {
      // Create email verification token
      $verificationToken = bin2hex(random_bytes(32));
      $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
      $verificationUuid = $this->generateUUID();

      $tokenStmt = $this->conn->prepare("INSERT INTO email_verifications (uuid, user_uuid, token, expires_at) VALUES (?, ?, ?, ?)");
      $tokenStmt->bind_param("ssss", $verificationUuid, $uuid, $verificationToken, $expiresAt);
      $tokenStmt->execute();

      // Send verification email
      require_once __DIR__ . '/../helpers/Mailer.php';
      $mailer = new Mailer();
      $mailer->sendVerificationEmail($email, $verificationToken);
    }

    return $result;
  }

  public function verifyEmail($token) {
    $stmt = $this->conn->prepare("
      UPDATE users u
      JOIN email_verifications ev ON u.uuid = ev.user_uuid
      SET u.is_verified = TRUE, ev.is_used = TRUE
      WHERE ev.token = ? AND ev.expires_at > NOW() AND ev.is_used = FALSE
    ");
    $stmt->bind_param("s", $token);
    return $stmt->execute() && $stmt->affected_rows > 0;
  }

  public function createPasswordReset($email) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Get user uuid by email
    $userStmt = $this->conn->prepare("SELECT uuid FROM users WHERE email = ?");
    $userStmt->bind_param("s", $email);
    $userStmt->execute();
    $userResult = $userStmt->get_result();

    if ($userResult->num_rows === 0) {
      return false;
    }

    $user = $userResult->fetch_assoc();
    $uuid = $user['uuid'];
    $resetUuid = $this->generateUUID();

    $stmt = $this->conn->prepare("INSERT INTO password_resets (uuid, user_uuid, token, expires_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $resetUuid, $uuid, $token, $expires);
    $result = $stmt->execute();

    if ($result) {
      // Send password reset email
      require_once __DIR__ . '/../helpers/Mailer.php';
      $mailer = new Mailer();
      $mailer->sendPasswordResetEmail($email, $token);
      return true;
    }

    return false;
  }

  public function resetPassword($token, $newPassword) {
    // First verify token is valid and not expired
    $stmt = $this->conn->prepare("
      SELECT u.uuid FROM users u
      JOIN password_resets pr ON u.uuid = pr.user_uuid
      WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.is_used = FALSE
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      return false;
    }

    $user = $result->fetch_assoc();
    $uuid = $user['uuid'];
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update password and mark reset token as used
    $this->conn->begin_transaction();

    try {
      $updateStmt = $this->conn->prepare("UPDATE users SET password = ? WHERE uuid = ?");
      $updateStmt->bind_param("ss", $hashedPassword, $uuid);
      $updateStmt->execute();

      $tokenStmt = $this->conn->prepare("UPDATE password_resets SET is_used = TRUE WHERE token = ?");
      $tokenStmt->bind_param("s", $token);
      $tokenStmt->execute();

      $this->conn->commit();
      return true;
    } catch (Exception $e) {
      $this->conn->rollback();
      return false;
    }
  }

  public function getUserByVerificationToken($token) {
    $stmt = $this->conn->prepare("
      SELECT u.* FROM users u
      JOIN email_verifications ev ON u.uuid = ev.user_uuid
      WHERE ev.token = ? AND ev.expires_at > NOW() AND ev.is_used = FALSE
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public function getUserByResetToken($token) {
    $stmt = $this->conn->prepare("
      SELECT u.* FROM users u
      JOIN password_resets pr ON u.uuid = pr.user_uuid
      WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.is_used = FALSE
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
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

  public function login($email, $password) {
    $stmt = $this->conn->prepare("SELECT uuid, email, password, role, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
      // Add name from email prefix for backward compatibility
      $user['name'] = explode('@', $user['email'])[0];
      return $user;
    }
    return false;
  }

  public function getUserById($userId) {
    $stmt = $this->conn->prepare("SELECT *, SUBSTRING_INDEX(email, '@', 1) as name FROM users WHERE uuid = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
  }

  public function getUserProfileByUuid($userUuid) {
    return $this->getUserProfile($userUuid);
  }

  public function updateUser($userId, $data) {
    $allowedFields = ['email', 'role', 'password'];
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

    $values[] = $userId;
    $types .= 's';

    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE uuid = ?";
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return false;
    }

    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function updateJobseekerProfile($userId, $data) {
    // Check if profile exists
    $stmt = $this->conn->prepare("SELECT uuid FROM job_seekers WHERE user_uuid = ?");
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $allowedFields = [
      'fullName', 'phone', 'gender', 'dob', 'location', 'bio', 'professional_title', 'skills', 'education', 'profile_picture'
    ];

    $updates = [];
    $types = '';
    $values = [];

    foreach ($data as $field => $value) {
      if (in_array($field, $allowedFields) && !empty($value)) {
        $updates[] = "$field = ?";
        $types .= 's';
        $values[] = $value;
      }
    }

    if (empty($updates)) {
      return false;
    }

    if ($result->num_rows > 0) {
      // Update existing profile
      $values[] = $userId;
      $types .= 's';
      $sql = "UPDATE job_seekers SET " . implode(', ', $updates) . " WHERE user_uuid = ?";
    } else {
      // Create new profile
      $jobseekerUuid = $this->generateUUID();
      $updates[] = "uuid = ?";
      $values[] = $jobseekerUuid;
      $updates[] = "user_uuid = ?";
      $values[] = $userId;
      $types .= 'ss';
      $sql = "INSERT INTO job_seekers SET " . implode(', ', $updates);
    }

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function updateEmployerProfile($userId, $data) {
    // Check if profile exists
    $stmt = $this->conn->prepare("SELECT uuid FROM employers WHERE user_uuid = ?");
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $allowedFields = [
      'company_name', 'contact_number', 'location', 'industry', 'website', 'company_logo', 'about_company'
    ];

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

    if ($result->num_rows > 0) {
      // Update existing profile
      $values[] = $userId;
      $types .= 's';
      $sql = "UPDATE employers SET " . implode(', ', $updates) . " WHERE user_uuid = ?";
    } else {
      // Create new profile
      $employerUuid = $this->generateUUID();
      $updates[] = "uuid = ?";
      $values[] = $employerUuid;
      $updates[] = "user_uuid = ?";
      $values[] = $userId;
      $types .= 'ss';
      $sql = "INSERT INTO employers SET " . implode(', ', $updates);
    }

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function updateAdminProfile($userId, $data) {
    // Check if profile exists
    $stmt = $this->conn->prepare("SELECT uuid FROM admin_profiles WHERE user_uuid = ?");
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $allowedFields = ['full_name', 'contact_number', 'admin_photo'];

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

    if ($result->num_rows > 0) {
      // Update existing profile
      $values[] = $userId;
      $types .= 's';
      $sql = "UPDATE admin_profiles SET " . implode(', ', $updates) . " WHERE user_uuid = ?";
    } else {
      // Create new profile
      $adminUuid = $this->generateUUID();
      $updates[] = "uuid = ?";
      $values[] = $adminUuid;
      $updates[] = "user_uuid = ?";
      $values[] = $userId;
      $types .= 'ss';
      $sql = "INSERT INTO admin_profiles SET " . implode(', ', $updates);
    }

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return false;
    }
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function getJobseekerSkills($profileId) {
    // Skills are stored as TEXT in job_seekers.skills
    $stmt = $this->conn->prepare("SELECT skills FROM job_seekers WHERE uuid = ?");
    if (!$stmt) {
      return [];
    }
    $stmt->bind_param("s", $profileId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? explode(',', $result['skills']) : [];
  }

  public function getJobseekerEducation($profileId) {
    // Education is stored as TEXT in job_seekers.education
    $stmt = $this->conn->prepare("SELECT education FROM job_seekers WHERE uuid = ?");
    if (!$stmt) {
      return [];
    }
    $stmt->bind_param("s", $profileId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? explode("\n", $result['education']) : [];
  }

  public function getJobseekerExperience($profileId) {
    // Experience is not in the current schema, return empty array
    return [];
  }

  public function getAllUsers($filters = []) {
    $where = "";
    $params = [];
    $types = '';

    if (!empty($filters['role'])) {
      $where .= " WHERE u.role = ?";
      $params[] = $filters['role'];
      $types .= 's';
    }

    if (!empty($filters['is_verified'])) {
      $where .= ($where ? " AND" : " WHERE") . " u.is_verified = ?";
      $params[] = $filters['is_verified'];
      $types .= 'i';
    }

    $sql = "
      SELECT u.*, js.fullName, e.company_name, a.full_name as admin_name
      FROM users u
      LEFT JOIN job_seekers js ON u.uuid = js.user_uuid
      LEFT JOIN employers e ON u.uuid = e.user_uuid
      LEFT JOIN admin_profiles a ON u.uuid = a.user_uuid
      $where
      ORDER BY u.created_at DESC
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

  public function getAllEmployers() {
    $sql = "
      SELECT
        e.uuid as employer_uuid,
        e.company_name,
        e.contact_number,
        e.location,
        e.industry,
        e.website,
        e.company_logo,
        e.about_company,
        u.email,
        u.created_at as user_created_at,
        u.is_verified
      FROM employers e
      JOIN users u ON e.user_uuid = u.uuid
      WHERE u.role = 'employer'
      ORDER BY e.company_name ASC
    ";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return [];
    }

    if ($stmt->execute()) {
      return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    return [];
  }

  public function getEmployerByUuid($employerUuid) {
    $sql = "
      SELECT
        e.uuid as employer_uuid,
        e.company_name,
        e.contact_number,
        e.location,
        e.industry,
        e.website,
        e.company_logo,
        e.about_company,
        u.email,
        u.created_at as user_created_at,
        u.is_verified,
        'employer' as role
      FROM employers e
      JOIN users u ON e.user_uuid = u.uuid
      WHERE e.uuid = ?
    ";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
      return false;
    }

    $stmt->bind_param("s", $employerUuid);
    if ($stmt->execute()) {
      $result = $stmt->get_result();
      return $result->fetch_assoc();
    }
    return false;
  }

  public function deleteUser($userId) {
    // Start transaction to ensure all related data is deleted
    $this->conn->begin_transaction();

    try {
      // Delete from related tables first due to foreign key constraints
      $tables = ['email_verifications', 'password_resets', 'job_seekers', 'employers', 'admin_profiles'];

      foreach ($tables as $table) {
        $stmt = $this->conn->prepare("DELETE FROM $table WHERE user_uuid = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
      }

      // Delete applications (job_seeker_uuid and job_uuid references)
      $stmt = $this->conn->prepare("DELETE FROM applications WHERE job_seeker_uuid IN (SELECT uuid FROM job_seekers WHERE user_uuid = ?) OR job_uuid IN (SELECT uuid FROM job_posts WHERE employer_uuid IN (SELECT uuid FROM employers WHERE user_uuid = ?))");
      $stmt->bind_param("ss", $userId, $userId);
      $stmt->execute();

      // Delete job posts
      $stmt = $this->conn->prepare("DELETE FROM job_posts WHERE employer_uuid IN (SELECT uuid FROM employers WHERE user_uuid = ?)");
      $stmt->bind_param("s", $userId);
      $stmt->execute();

      // Finally delete the user
      $stmt = $this->conn->prepare("DELETE FROM users WHERE uuid = ?");
      $stmt->bind_param("s", $userId);
      $stmt->execute();

      $this->conn->commit();
      return true;
    } catch (Exception $e) {
      $this->conn->rollback();
      return false;
    }
  }
}
?>