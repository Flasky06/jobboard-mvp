<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class Mailer {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);

        // Server settings
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'alex987morgan@gmail.com';
        $this->mailer->Password = 'dgrtuuuzqmpozwgn';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;

        // Default settings
        $this->mailer->setFrom('noreply@yourdomain.com', 'Job Portal');
        $this->mailer->isHTML(true);
    }

    public function sendVerificationEmail($email, $token) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email);

            $this->mailer->Subject = 'Verify Your Email - Job Portal';
            $this->mailer->Body = $this->getVerificationEmailTemplate($token);
            $this->mailer->AltBody = 'Please verify your email by clicking the link: ' . $this->getVerificationUrl($token);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Email verification failed: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    public function sendPasswordResetEmail($email, $token) {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email);

            $this->mailer->Subject = 'Reset Your Password - Job Portal';
            $this->mailer->Body = $this->getPasswordResetEmailTemplate($token);
            $this->mailer->AltBody = 'Reset your password by clicking the link: ' . $this->getPasswordResetUrl($token);

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Password reset email failed: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    private function getVerificationEmailTemplate($token) {
        $url = $this->getVerificationUrl($token);
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Verify Your Email</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2563eb;'>Welcome to Job Portal!</h2>
                <p>Thank you for registering. Please verify your email address to complete your registration.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$url}' style='background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Verify Email</a>
                </div>
                <p>If the button doesn't work, copy and paste this link into your browser:</p>
                <p style='word-break: break-all; background-color: #f3f4f6; padding: 10px; border-radius: 3px;'>{$url}</p>
                <p>This link will expire in 24 hours.</p>
                <p>If you didn't create an account, please ignore this email.</p>
            </div>
        </body>
        </html>";
    }

    private function getPasswordResetEmailTemplate($token) {
        $url = $this->getPasswordResetUrl($token);
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Reset Your Password</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #2563eb;'>Reset Your Password</h2>
                <p>You requested a password reset for your Job Portal account.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$url}' style='background-color: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
                </div>
                <p>If the button doesn't work, copy and paste this link into your browser:</p>
                <p style='word-break: break-all; background-color: #f3f4f6; padding: 10px; border-radius: 3px;'>{$url}</p>
                <p>This link will expire in 1 hour.</p>
                <p>If you didn't request a password reset, please ignore this email.</p>
            </div>
        </body>
        </html>";
    }

    private function getVerificationUrl($token) {
        return "http://localhost:8000/auth/verify-email.php?token=" . urlencode($token);
    }

    private function getPasswordResetUrl($token) {
        return "http://localhost:8000/auth/reset-password.php?token=" . urlencode($token);
    }
}
?>