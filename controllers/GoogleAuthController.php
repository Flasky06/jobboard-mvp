<?php
require_once __DIR__ . '/../config/google-oauth.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/auth.php';

class GoogleAuthController {
    private $userModel;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->userModel = new User($conn);
    }

    /**
     * Initiate Google OAuth login flow
     */
    public function login() {
        try {
            $client = getGoogleClient();
            $state = generateOAuthState();

            // Set the state on the client for CSRF protection
            $client->setState($state);

            // Debug: Log the state
            error_log("Generated OAuth state: " . $state);

            // Generate the authorization URL
            $authUrl = $client->createAuthUrl();

            // Debug: Log the authorization URL
            error_log("Google Auth URL: " . $authUrl);

            // Redirect to Google
            header('Location: ' . $authUrl);
            exit;
        } catch (Exception $e) {
            error_log("Google OAuth login error: " . $e->getMessage());
            $_SESSION['errors'] = ['Failed to initiate Google login. Please try again.'];
            header('Location: /auth/login.php');
            exit;
        }
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback() {
        try {
            // Debug: Log callback parameters
            error_log("=== Google Callback Debug ===");
            error_log("GET params: " . print_r($_GET, true));
            error_log("Session state: " . (isset($_SESSION['oauth_state']) ? $_SESSION['oauth_state'] : 'NOT SET'));

            // Validate state parameter for CSRF protection
            if (!isset($_GET['state']) || !validateOAuthState($_GET['state'])) {
                error_log("State validation failed. GET state: " . (isset($_GET['state']) ? $_GET['state'] : 'NOT SET'));
                throw new Exception('Invalid OAuth state');
            }

            clearOAuthState();

            $client = getGoogleClient();

            // Exchange authorization code for access token
            if (isset($_GET['code'])) {
                $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

                if (isset($token['error'])) {
                    throw new Exception('Failed to get access token: ' . $token['error']);
                }

                $client->setAccessToken($token);

                // Get user info from Google
                $oauth2 = new Google_Service_Oauth2($client);
                $googleUser = $oauth2->userinfo->get();

                // Process the Google user data
                $this->processGoogleUser($googleUser, $token);

            } else {
                throw new Exception('Authorization code not received');
            }

        } catch (Exception $e) {
            error_log("Google OAuth callback error: " . $e->getMessage());
            $_SESSION['errors'] = ['Google authentication failed. Please try again.'];
            header('Location: /auth/login.php');
            exit;
        }
    }

    /**
     * Process Google user data and create/login user
     */
    private function processGoogleUser($googleUser, $token) {
        try {
            // Check if user already exists with this Google ID
            $existingUser = $this->findUserByGoogleId($googleUser->id);

            if ($existingUser) {
                // User exists, log them in
                $this->loginExistingUser($existingUser, $token);
            } else {
                // Check if user exists with same email
                $existingUserByEmail = $this->findUserByEmail($googleUser->email);

                if ($existingUserByEmail) {
                    // Link Google account to existing user
                    $this->linkGoogleAccount($existingUserByEmail, $googleUser, $token);
                } else {
                    // Create new user
                    $this->createNewUser($googleUser, $token);
                }
            }

        } catch (Exception $e) {
            error_log("Process Google user error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Find user by Google ID
     */
    private function findUserByGoogleId($googleId) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE google_id = ?");
        $stmt->bind_param("s", $googleId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Find user by email
     */
    private function findUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Login existing user
     */
    private function loginExistingUser($user, $token) {
        // Store access token in session
        $_SESSION[GOOGLE_OAUTH_ACCESS_TOKEN] = $token;

        // Set user session
        setUserSession($user);

        // Redirect based on role
        $this->redirectBasedOnRole($user['role']);
    }

    /**
     * Link Google account to existing user
     */
    private function linkGoogleAccount($existingUser, $googleUser, $token) {
        // Update user with Google ID
        $stmt = $this->conn->prepare("UPDATE users SET google_id = ?, updated_at = NOW() WHERE uuid = ?");
        $stmt->bind_param("ss", $googleUser->id, $existingUser['uuid']);
        $stmt->execute();

        // Store access token in session
        $_SESSION[GOOGLE_OAUTH_ACCESS_TOKEN] = $token;

        // Set user session
        setUserSession($existingUser);

        // Redirect based on role
        $this->redirectBasedOnRole($existingUser['role']);
    }

    /**
     * Create new user from Google data
     */
    private function createNewUser($googleUser, $token) {
        try {
            $this->conn->begin_transaction();

            // Generate UUID and create user
            $userUuid = $this->generateUUID();
            $hashedPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT); // Random password for OAuth users

            // Insert user
            $stmt = $this->conn->prepare("
                INSERT INTO users (uuid, email, password, google_id, role, is_verified, created_at)
                VALUES (?, ?, ?, ?, 'jobseeker', 1, NOW())
            ");
            $stmt->bind_param("ssss", $userUuid, $googleUser->email, $hashedPassword, $googleUser->id);
            $stmt->execute();

            // Create jobseeker profile
            $jobseekerUuid = $this->generateUUID();
            $fullName = $googleUser->name ?: explode('@', $googleUser->email)[0];

            $stmt = $this->conn->prepare("
                INSERT INTO job_seekers (uuid, user_uuid, fullName, profile_completed, created_at)
                VALUES (?, ?, ?, 0, NOW())
            ");
            $stmt->bind_param("sss", $jobseekerUuid, $userUuid, $fullName);
            $stmt->execute();

            $this->conn->commit();

            // Store access token in session
            $_SESSION[GOOGLE_OAUTH_ACCESS_TOKEN] = $token;

            // Get the created user and set session
            $newUser = $this->findUserByGoogleId($googleUser->id);
            setUserSession($newUser);

            // Redirect to profile completion
            $_SESSION['success'] = 'Account created successfully! Please complete your profile.';
            header('Location: /profile.php');
            exit;

        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }

    /**
     * Redirect based on user role
     */
    private function redirectBasedOnRole($role) {
        switch ($role) {
            case 'admin':
                header('Location: /dashboard/admin-dashboard.php');
                break;
            case 'employer':
                header('Location: /dashboard/employer-dashboard.php');
                break;
            default:
                header('Location: /');
                break;
        }
        exit;
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