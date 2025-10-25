<?php
class User {
  private $conn;

  public function __construct($db) {
    $this->conn = $db;
  }

  public function register($email, $password) {
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $verificationToken = bin2hex(random_bytes(32));

    $stmt = $this->conn->prepare("INSERT INTO users (email, password, email_verification_token) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $hashed, $verificationToken);
    $result = $stmt->execute();

    if ($result) {
      // Send verification email
      require_once __DIR__ . '/../helpers/Mailer.php';
      $mailer = new Mailer();
      $mailer->sendVerificationEmail($email, $verificationToken);
    }

    return $result;
  }

  public function verifyEmail($token) {
    $stmt = $this->conn->prepare("UPDATE users SET email_verified = TRUE, email_verification_token = NULL WHERE email_verification_token = ?");
    $stmt->bind_param("s", $token);
    return $stmt->execute() && $stmt->affected_rows > 0;
  }

  public function createPasswordReset($email) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = $this->conn->prepare("UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE email = ?");
    $stmt->bind_param("sss", $token, $expires, $email);
    $result = $stmt->execute();

    if ($result && $stmt->affected_rows > 0) {
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
    $stmt = $this->conn->prepare("SELECT id FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
      return false;
    }

    $user = $result->fetch_assoc();
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update password and clear reset token
    $stmt = $this->conn->prepare("UPDATE users SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $user['id']);
    return $stmt->execute();
  }

  public function getUserByVerificationToken($token) {
    $stmt = $this->conn->prepare("SELECT * FROM users WHERE email_verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public function getUserByResetToken($token) {
    $stmt = $this->conn->prepare("SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
  }

  public function login($email, $password) {
    $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
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
    $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
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
    $types .= 'i';

    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
    $stmt = $this->conn->prepare($sql);

    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function getUserProfile($userId) {
    $stmt = $this->conn->prepare("
      SELECT u.*, jp.*, ep.*, ap.*
      FROM users u
      LEFT JOIN jobseeker_profiles jp ON u.id = jp.user_id
      LEFT JOIN employer_profiles ep ON u.id = ep.user_id
      LEFT JOIN admin_profiles ap ON u.id = ap.user_id
      WHERE u.id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
  }

  public function updateJobseekerProfile($userId, $data) {
    // Check if profile exists
    $stmt = $this->conn->prepare("SELECT id FROM jobseeker_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $allowedFields = [
      'name', 'phone_number', 'gender', 'date_of_birth', 'nationality',
      'professional_title', 'current_location', 'preferred_job_type', 'about_me', 'profile_photo'
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
      $types .= 'i';
      $sql = "UPDATE jobseeker_profiles SET " . implode(', ', $updates) . " WHERE user_id = ?";
    } else {
      // Create new profile
      $updates[] = "user_id = ?";
      $values[] = $userId;
      $types .= 'i';
      $sql = "INSERT INTO jobseeker_profiles SET " . implode(', ', $updates);
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function updateEmployerProfile($userId, $data) {
    // Check if profile exists
    $stmt = $this->conn->prepare("SELECT id FROM employer_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $allowedFields = [
      'company_name', 'company_website', 'company_description',
      'location', 'contact_number', 'company_logo'
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
      $types .= 'i';
      $sql = "UPDATE employer_profiles SET " . implode(', ', $updates) . " WHERE user_id = ?";
    } else {
      // Create new profile
      $updates[] = "user_id = ?";
      $values[] = $userId;
      $types .= 'i';
      $sql = "INSERT INTO employer_profiles SET " . implode(', ', $updates);
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function updateAdminProfile($userId, $data) {
    // Check if profile exists
    $stmt = $this->conn->prepare("SELECT id FROM admin_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
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
      $types .= 'i';
      $sql = "UPDATE admin_profiles SET " . implode(', ', $updates) . " WHERE user_id = ?";
    } else {
      // Create new profile
      $updates[] = "user_id = ?";
      $values[] = $userId;
      $types .= 'i';
      $sql = "INSERT INTO admin_profiles SET " . implode(', ', $updates);
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
  }

  public function getJobseekerSkills($profileId) {
    $stmt = $this->conn->prepare("
      SELECT s.name, s.category, js.proficiency_level
      FROM jobseeker_skills js
      JOIN skills s ON js.skill_id = s.id
      WHERE js.jobseeker_profile_id = ?
      ORDER BY s.category, s.name
    ");
    $stmt->bind_param("i", $profileId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getJobseekerEducation($profileId) {
    $stmt = $this->conn->prepare("
      SELECT * FROM education
      WHERE jobseeker_profile_id = ?
      ORDER BY start_date DESC
    ");
    $stmt->bind_param("i", $profileId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function getJobseekerExperience($profileId) {
    $stmt = $this->conn->prepare("
      SELECT * FROM work_experience
      WHERE jobseeker_profile_id = ?
      ORDER BY start_date DESC
    ");
    $stmt->bind_param("i", $profileId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }
}
?>