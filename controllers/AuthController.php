<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/session.php';
require_once __DIR__ . '/../helpers/csrf.php';

class AuthController {
  private $user;

  public function __construct($conn) {
    $this->user = new User($conn);
  }

  public function register() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // CSRF validation
      if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['errors'] = ["Invalid request. Please try again."];
        redirect('register.php');
        return;
      }

      $email = trim($_POST['email']);
      $password = $_POST['password'];
      $confirm_password = $_POST['confirm_password'];

      // Validation
      $errors = [];
      if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
      if (empty($password) || strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
      if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

      if (empty($errors)) {
        if ($this->user->register('', $email, $password)) {
          header("Location: login.php?registered=1");
          exit;
        } else {
          $errors[] = "Error registering user. Email might already exist.";
        }
      }

      // Store errors in session for display
      $_SESSION['errors'] = $errors;
      redirect('register.php');
    }
  }

  public function login() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // CSRF validation
      if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['errors'] = ["Invalid request. Please try again."];
        redirect('login.php');
        return;
      }

      $email = trim($_POST['email']);
      $password = $_POST['password'];

      // Validation
      $errors = [];
      if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
      if (empty($password)) $errors[] = "Password is required.";

      if (empty($errors)) {
        $user = $this->user->login($email, $password);

        if ($user) {
          // Check if email is verified
          if (!$user['email_verified']) {
            $_SESSION['errors'] = ["Please verify your email before logging in. Check your inbox for the verification link."];
            redirect('login.php');
            return;
          }

          session_regenerate_id(true);
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['user_name'] = $user['name'] ?? $user['email'];
          $_SESSION['role'] = $user['role'];

          // Redirect based on user role
          switch ($user['role']) {
            case 'admin':
              redirect('admin-dashboard.php');
              break;
            case 'employer':
              redirect('employer-dashboard.php');
              break;
            case 'jobseeker':
            default:
              redirect('home.php');
              break;
          }
        } else {
          $errors[] = "Invalid email or password.";
        }
      }

      // Store errors in session for display
      $_SESSION['errors'] = $errors;
      redirect('login.php');
    }
  }

  public function verifyEmail() {
    $token = $_GET['token'] ?? '';

    if (empty($token)) {
      $_SESSION['errors'] = ["Invalid verification link."];
      redirect('login.php');
      return;
    }

    if ($this->user->verifyEmail($token)) {
      $_SESSION['success'] = "Email verified successfully! You can now log in.";
      header("Location: login.php?verified=1");
      exit;
    } else {
      $_SESSION['errors'] = ["Invalid or expired verification link."];
      header("Location: login.php?error=verification_failed");
      exit;
    }
  }

  public function forgotPassword() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      // CSRF validation
      if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
        $_SESSION['errors'] = ["Invalid request. Please try again."];
        redirect('forgot-password.php');
        return;
      }

      $email = trim($_POST['email']);

      // Validation
      $errors = [];
      if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
      }

      if (empty($errors)) {
        if ($this->user->createPasswordReset($email)) {
          $_SESSION['success'] = "Password reset link sent to your email.";
          header("Location: forgot-password.php?sent=1");
          exit;
        } else {
          $_SESSION['errors'] = ["No account found with that email address."];
        }
      } else {
        $_SESSION['errors'] = $errors;
      }

      redirect('forgot-password.php');
    }
  }

  public function resetPassword() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $token = $_POST['token'] ?? '';
      $password = $_POST['password'] ?? '';
      $confirmPassword = $_POST['confirm_password'] ?? '';

      // Validation
      $errors = [];
      if (empty($password) || strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
      }
      if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
      }

      if (empty($errors)) {
        if ($this->user->resetPassword($token, $password)) {
          $_SESSION['success'] = "Password reset successfully! You can now log in.";
          header("Location: login.php?reset=1");
          exit;
        } else {
          $errors[] = "Invalid or expired reset link.";
        }
      }

      $_SESSION['errors'] = $errors;
      redirect('reset-password.php?token=' . urlencode($token));
    }
  }

  public function logout() {
    session_destroy();
    redirect('login.php');
  }
}
?>