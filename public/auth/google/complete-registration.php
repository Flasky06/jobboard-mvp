<?php
require_once __DIR__ . '/../../../helpers/session.php';
require_once __DIR__ . '/../../../helpers/csrf.php';
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../models/User.php';

// Check if we have Google user data
if (!isset($_SESSION['google_user_data'])) {
    header('Location: /job-finder/public/auth/login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /auth/google/role-selection.php');
    exit;
}

// Validate CSRF token
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    $_SESSION['errors'] = ['Invalid request. Please try again.'];
    header('Location: /auth/google/role-selection.php');
    exit;
}

// Validate role selection
$role = $_POST['role'] ?? '';
if (!in_array($role, ['jobseeker', 'employer'])) {
    $_SESSION['errors'] = ['Please select a valid role.'];
    header('Location: /job-finder/public/auth/google/role-selection');
    exit;
}

try {
    $conn->begin_transaction();

    $googleUserData = $_SESSION['google_user_data'];

    // Generate UUID and create user
    $userUuid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    $hashedPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT); // Random password for OAuth users

    // Insert user
    $stmt = $conn->prepare("
        INSERT INTO users (uuid, email, password, google_id, role, is_verified, created_at)
        VALUES (?, ?, ?, ?, ?, 1, NOW())
    ");
    $stmt->bind_param("sssss", $userUuid, $googleUserData['email'], $hashedPassword, $googleUserData['id'], $role);
    $stmt->execute();

    // Create profile based on role
    if ($role === 'jobseeker') {
        $profileUuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $stmt = $conn->prepare("
            INSERT INTO job_seekers (uuid, user_uuid, fullName, profile_completed, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        $stmt->bind_param("sss", $profileUuid, $userUuid, $googleUserData['name']);
        $stmt->execute();
    } else { // employer
        $profileUuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $stmt = $conn->prepare("
            INSERT INTO employers (uuid, user_uuid, company_name, profile_completed, created_at)
            VALUES (?, ?, ?, 0, NOW())
        ");
        $companyName = $googleUserData['name'] . "'s Company"; // Default company name
        $stmt->bind_param("sss", $profileUuid, $userUuid, $companyName);
        $stmt->execute();
    }

    $conn->commit();

    // Store access token in session
    $_SESSION['google_oauth_access_token'] = $googleUserData['token'];

    // Get the created user and set session
    $userModel = new User($conn);
    $newUser = $userModel->findUserByGoogleId($googleUserData['id']);
    setUserSession($newUser);

    // Clear Google user data
    unset($_SESSION['google_user_data']);

    // Redirect based on role
    if ($role === 'jobseeker') {
        $_SESSION['success'] = 'Account created successfully! Please complete your profile.';
        header('Location: /job-finder/public/profile');
    } else {
        $_SESSION['success'] = 'Account created successfully! Please complete your company profile.';
        header('Location: /job-finder/public/dashboard/employer-profile');
    }
    exit;

} catch (Exception $e) {
    $conn->rollback();
    error_log("Google OAuth registration error: " . $e->getMessage());
    $_SESSION['errors'] = ['Registration failed. Please try again.'];
    header('Location: /job-finder/public/auth/google/role-selection');
    exit;
}
?>