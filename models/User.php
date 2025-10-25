<?php
class User {
  private $conn;

  public function __construct($db) {
    $this->conn = $db;
  }

  public function register($name, $email, $password) {
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $uuid = $this->generateUUID();

    $stmt = $this->conn->prepare("INSERT INTO users (uuid, name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $uuid, $name, $email, $hashed);
    $result = $stmt->execute();

    if ($result) {
      // Create email verification token
      $verificationToken = bin2hex(random_bytes(32));
      $expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));

      $tokenStmt = $this->conn->prepare("INSERT INTO email_verifications (user_uuid, token, expires_at) VALUES (?, ?, ?)");
      $tokenStmt->bind_param("sss", $uuid, $verificationToken, $expiresAt);
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

    $stmt = $this->conn->prepare("INSERT INTO password_resets (user_uuid, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $uuid, $token, $expires);
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
    $stmt = $this->conn->prepare("SELECT uuid, name, email, password, role, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
      return $user;
    }
    return false;
  }

  public function getUserById($userId) {
    $stmt = $this->conn->prepare("SELECT * FROM users WHERE uuid = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
  }

  public function updateUser($userId, $data) {
    $allowedFields = ['email', 'role'];
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

    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function getUserProfile($userId) {
    $stmt = $this->conn->prepare("
      SELECT u.*, js.*, e.*, a.*
      FROM users u
      LEFT JOIN job_seekers js ON u.uuid = js.user_uuid
      LEFT JOIN employers e ON u.uuid = e.user_uuid
      LEFT JOIN admin_profiles a ON u.uuid = a.user_uuid
      WHERE u.uuid = ?
    ");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
  }

  public function updateJobseekerProfile($userId, $data) {
    // Check if profile exists
    $stmt = $this->conn->prepare("SELECT id FROM job_seekers WHERE user_uuid = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $allowedFields = [
      'phone', 'gender', 'dob', 'location', 'bio', 'professional_title', 'skills'
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
      $sql = "UPDATE job_seekers SET " . implode(', ', $updates) . " WHERE user_uuid = ?";
    } else {
      // Create new profile
      $updates[] = "user_uuid = ?";
      $values[] = $userId;
      $types .= 's';
      $sql = "INSERT INTO job_seekers SET " . implode(', ', $updates);
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function updateEmployerProfile($userId, $data) {
    // Check if profile exists
    $stmt = $this->conn->prepare("SELECT id FROM employers WHERE user_uuid = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $allowedFields = [
      'company_name', 'contact_number', 'position'
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
      $updates[] = "user_uuid = ?";
      $values[] = $userId;
      $types .= 's';
      $sql = "INSERT INTO employers SET " . implode(', ', $updates);
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function updateAdminProfile($userId, $data) {
    // Check if profile exists
    $stmt = $this->conn->prepare("SELECT id FROM admin_profiles WHERE user_uuid = ?");
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
      $updates[] = "user_uuid = ?";
      $values[] = $userId;
      $types .= 's';
      $sql = "INSERT INTO admin_profiles SET " . implode(', ', $updates);
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function getJobseekerSkills($profileId) {
    // Skills are stored as TEXT in job_seekers.skills
    $stmt = $this->conn->prepare("SELECT skills FROM job_seekers WHERE id = ?");
    $stmt->bind_param("i", $profileId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? explode(',', $result['skills']) : [];
  }

  public function getJobseekerEducation($profileId) {
    // Education is stored as TEXT in job_seekers.education
    $stmt = $this->conn->prepare("SELECT education FROM job_seekers WHERE id = ?");
    $stmt->bind_param("i", $profileId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result ? explode("\n", $result['education']) : [];
  }

  public function getJobseekerExperience($profileId) {
    // Experience is not in the current schema, return empty array
    return [];
  }
}
?>