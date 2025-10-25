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
      redirect('/login.php');
      return;
    }

    $userId = $_SESSION['user_id'];
    $profile = $this->user->getUserProfile($userId);

    // Get additional data based on role
    $additionalData = [];
    if ($profile['role'] === 'jobseeker' && $profile['jobseeker_profile_id']) {
      $additionalData['skills'] = $this->user->getJobseekerSkills($profile['jobseeker_profile_id']);
      $additionalData['education'] = $this->user->getJobseekerEducation($profile['jobseeker_profile_id']);
      $additionalData['experience'] = $this->user->getJobseekerExperience($profile['jobseeker_profile_id']);
    }

    return ['profile' => $profile, 'additionalData' => $additionalData];
  }

  public function updateProfile() {
    if (!isAuthenticated()) {
      redirect('/login.php');
      return;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return;
    }

    // CSRF validation
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
      $_SESSION['errors'] = ["Invalid request. Please try again."];
      redirect('profile.php');
      return;
    }

    $userId = $_SESSION['user_id'];
    $user = $this->user->getUserById($userId);

    $errors = [];
    $success = false;

    try {
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

      if ($success) {
        $_SESSION['success'] = "Profile updated successfully!";
      } else {
        $errors[] = "Failed to update profile.";
      }
    } catch (Exception $e) {
      $errors[] = "An error occurred while updating your profile.";
    }

    if (!empty($errors)) {
      $_SESSION['errors'] = $errors;
    }

    redirect('profile.php');
  }

  private function updateJobseekerProfile($userId, $data) {
    $profileData = [];

    // Basic information
    if (isset($data['name'])) $profileData['name'] = trim($data['name']);
    if (isset($data['phone_number'])) $profileData['phone_number'] = trim($data['phone_number']);
    if (isset($data['gender'])) $profileData['gender'] = $data['gender'];
    if (isset($data['date_of_birth'])) $profileData['date_of_birth'] = $data['date_of_birth'];
    if (isset($data['nationality'])) $profileData['nationality'] = trim($data['nationality']);
    if (isset($data['professional_title'])) $profileData['professional_title'] = trim($data['professional_title']);
    if (isset($data['current_location'])) $profileData['current_location'] = trim($data['current_location']);
    if (isset($data['preferred_job_type'])) $profileData['preferred_job_type'] = $data['preferred_job_type'];
    if (isset($data['about_me'])) $profileData['about_me'] = trim($data['about_me']);

    // Handle file upload for profile photo
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
      $photoPath = $this->handleFileUpload($_FILES['profile_photo'], 'profile_photos');
      if ($photoPath) {
        $profileData['profile_photo'] = $photoPath;
      }
    }

    return $this->user->updateJobseekerProfile($userId, $profileData);
  }

  private function updateEmployerProfile($userId, $data) {
    $profileData = [];

    if (isset($data['company_name'])) $profileData['company_name'] = trim($data['company_name']);
    if (isset($data['company_website'])) $profileData['company_website'] = trim($data['company_website']);
    if (isset($data['company_description'])) $profileData['company_description'] = trim($data['company_description']);
    if (isset($data['location'])) $profileData['location'] = trim($data['location']);
    if (isset($data['contact_number'])) $profileData['contact_number'] = trim($data['contact_number']);

    // Handle file upload for company logo
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
      $logoPath = $this->handleFileUpload($_FILES['company_logo'], 'company_logos');
      if ($logoPath) {
        $profileData['company_logo'] = $logoPath;
      }
    }

    return $this->user->updateEmployerProfile($userId, $profileData);
  }

  private function updateAdminProfile($userId, $data) {
    $profileData = [];

    if (isset($data['full_name'])) $profileData['full_name'] = trim($data['full_name']);
    if (isset($data['contact_number'])) $profileData['contact_number'] = trim($data['contact_number']);

    // Handle file upload for admin photo
    if (isset($_FILES['admin_photo']) && $_FILES['admin_photo']['error'] === UPLOAD_ERR_OK) {
      $photoPath = $this->handleFileUpload($_FILES['admin_photo'], 'admin_photos');
      if ($photoPath) {
        $profileData['admin_photo'] = $photoPath;
      }
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
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($fileExtension), $allowedTypes)) {
      return false;
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
      return false;
    }

    if (move_uploaded_file($file['tmp_name'], $filePath)) {
      return '/uploads/' . $directory . '/' . $fileName;
    }

    return false;
  }
}
?>