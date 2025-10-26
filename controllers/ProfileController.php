<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/csrf.php';

class ProfileController {
  private $user;

  public function __construct($conn) {
    $this->user = new User($conn);
  }

  public function showProfile() {
    if (!isAuthenticated()) {
      redirect('/auth/login.php');
      return;
    }

    // FIX: Support both user_id and user_uuid in session
    $userId = $_SESSION['user_uuid'] ?? $_SESSION['user_id'] ?? $_SESSION['uuid'] ?? null;
    
    if (!$userId) {
      error_log("ProfileController: No user ID found in session. Session data: " . print_r($_SESSION, true));
      $_SESSION['errors'] = ["Session expired. Please login again."];
      redirect('/auth/login.php');
      return;
    }

    error_log("ProfileController: Looking up user with ID: " . $userId);

    $profile = $this->user->getUserProfile($userId);

    // Get additional data based on role
    $additionalData = [];
    if ($profile && $profile['role'] === 'jobseeker') {
      // For job seekers, get skills and education from the profile data directly
      $additionalData['skills'] = !empty($profile['skills']) ? array_map('trim', explode(',', $profile['skills'])) : [];
      $additionalData['education'] = !empty($profile['education']) ? array_filter(array_map('trim', explode("\n", $profile['education']))) : [];
      $additionalData['experience'] = [];
    }

    // Debug: Check if profile data exists
    if (!$profile) {
      error_log("ProfileController: No profile found for user_id: " . $userId);
      return ['profile' => null, 'additionalData' => $additionalData];
    }

    // Debug: Check profile structure
    error_log("ProfileController: Profile data found. Role: " . ($profile['role'] ?? 'unknown'));

    return ['profile' => $profile, 'additionalData' => $additionalData];
  }

  public function updateProfile() {
    if (!isAuthenticated()) {
      redirect('/auth/login.php');
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return;
    }

    // CSRF validation
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
      $_SESSION['errors'] = ["Invalid request. Please try again."];
      $this->redirectToProfile();
      return;
    }

    // FIX: Support both user_id and user_uuid in session
    $userId = $_SESSION['user_uuid'] ?? $_SESSION['user_id'] ?? $_SESSION['uuid'] ?? null;
    
    if (!$userId) {
      $_SESSION['errors'] = ["Session expired. Please login again."];
      redirect('/auth/login.php');
      return;
    }

    $user = $this->user->getUserById($userId);

    if (!$user) {
      $_SESSION['errors'] = ["User not found."];
      redirect('/auth/login.php');
      return;
    }

    $errors = [];
    $success = false;

    try {
      // Handle password reset request
      if (isset($_POST['request_password_reset'])) {
        if ($this->user->createPasswordReset($user['email'])) {
          $_SESSION['success'] = "Password reset link sent to your email!";
          $this->redirectToProfile();
          return;
        } else {
          $errors[] = "Failed to send password reset email.";
        }
      }

      // Handle profile picture upload (now in users table)
      if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $photoPath = $this->handleFileUpload($_FILES['profile_photo'], 'profile_photos');
        if ($photoPath) {
          $this->user->updateUserProfilePicture($userId, $photoPath);
        }
      }

      // Handle different profile types based on role
      switch ($user['role']) {
        case 'jobseeker':
          $success = $this->updateJobseekerProfile($userId, $_POST);
          break;
        case 'employer':
          $success = $this->updateEmployerProfile($userId, $_POST);
          break;
        case 'admin':
          $success = $this->updateAdminProfile($userId, $_POST);
          break;
        default:
          $errors[] = "Invalid user role.";
      }

      if ($success && empty($errors)) {
        $_SESSION['success'] = "Profile updated successfully!";
      } elseif (!$success && empty($errors)) {
        $errors[] = "Failed to update profile.";
      }
    } catch (Exception $e) {
      error_log("ProfileController: Update error - " . $e->getMessage());
      $errors[] = "An error occurred while updating your profile.";
    }

    if (!empty($errors)) {
      $_SESSION['errors'] = $errors;
    }

    $this->redirectToProfile();
  }

  private function redirectToProfile() {
    // Get fresh user data
    $userId = $_SESSION['user_uuid'] ?? $_SESSION['user_id'] ?? $_SESSION['uuid'] ?? null;
    $user = $this->user->getUserById($userId);
    
    if (!$user) {
      redirect('/auth/login.php');
      return;
    }

    // Redirect based on role
    switch ($user['role']) {
      case 'employer':
        redirect('../dashboard/employer-profile.php');
        break;
      case 'admin':
        redirect('../dashboard/admin-profile.php');
        break;
      default:
        redirect('profile.php');
    }
  }

  private function updateJobseekerProfile($userId, $data) {
    $profileData = [];

    // Only set fields that are not empty
    $allowedFields = [
      'fullName', 'phone', 'gender', 'dob', 'professional_title', 'location', 'bio', 'skills', 'education'
    ];

    foreach ($allowedFields as $field) {
      if (isset($data[$field]) && trim($data[$field]) !== '') {
        $profileData[$field] = trim($data[$field]);
      }
    }

    // Handle resume upload
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
      $resumePath = $this->handleFileUpload($_FILES['resume'], 'resumes');
      if ($resumePath) {
        $profileData['resume_file'] = $resumePath;
      }
    }

    if (empty($profileData)) {
      return true; // No changes, but not an error
    }

    return $this->user->updateJobseekerProfile($userId, $profileData);
  }

  private function updateEmployerProfile($userId, $data) {
    $profileData = [];

    if (isset($data['company_name']) && trim($data['company_name']) !== '') {
      $profileData['company_name'] = trim($data['company_name']);
    }
    if (isset($data['company_website']) && trim($data['company_website']) !== '') {
      $profileData['website'] = trim($data['company_website']);
    }
    if (isset($data['location']) && trim($data['location']) !== '') {
      $profileData['location'] = trim($data['location']);
    }
    if (isset($data['contact_number']) && trim($data['contact_number']) !== '') {
      $profileData['contact_number'] = trim($data['contact_number']);
    }
    if (isset($data['industry']) && trim($data['industry']) !== '') {
      $profileData['industry'] = trim($data['industry']);
    }
    if (isset($data['about_company']) && trim($data['about_company']) !== '') {
      $profileData['about_company'] = trim($data['about_company']);
    }

    // Handle file upload for company logo
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
      $logoPath = $this->handleFileUpload($_FILES['company_logo'], 'company_logos');
      if ($logoPath) {
        $profileData['company_logo'] = $logoPath;
      }
    }

    if (empty($profileData)) {
      return true; // No changes, but not an error
    }

    return $this->user->updateEmployerProfile($userId, $profileData);
  }

  private function updateAdminProfile($userId, $data) {
    $profileData = [];

    if (isset($data['full_name']) && trim($data['full_name']) !== '') {
      $profileData['full_name'] = trim($data['full_name']);
    }
    if (isset($data['contact_number']) && trim($data['contact_number']) !== '') {
      $profileData['contact_number'] = trim($data['contact_number']);
    }

    if (empty($profileData)) {
      return true; // No changes, but not an error
    }

    return $this->user->updateAdminProfile($userId, $profileData);
  }

  private function handleFileUpload($file, $directory) {
    $uploadDir = __DIR__ . '/../uploads/' . $directory . '/';

    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;

    // Validate file type
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if ($directory === 'resumes') {
      $allowedTypes = ['pdf', 'doc', 'docx'];
    }
    
    if (!in_array(strtolower($fileExtension), $allowedTypes)) {
      error_log("Invalid file type: " . $fileExtension);
      return false;
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
      error_log("File too large: " . $file['size']);
      return false;
    }

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
      return '/uploads/' . $directory . '/' . $fileName;
    }

    error_log("Failed to move uploaded file");
    return false;
  }
}
?>