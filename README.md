# Job Finder Portal

A comprehensive PHP-based job portal application that connects job seekers with employers through a secure, user-friendly platform. Features role-based access control, email verification, job posting/management, application tracking, and Google OAuth integration.

## Table of Contents

- [Features](#features)
- [Prerequisites](#prerequisites)
- [Installation & Setup](#installation--setup)
- [Usage](#usage)
- [API Documentation](#api-documentation)
- [Dependencies](#dependencies)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)

## Features

### 🔐 **Authentication & Security**

- User registration and login with email verification
- Role-based access control (Job Seeker, Employer, Admin)
- Google OAuth integration for seamless login
- Password reset functionality
- CSRF protection on all forms
- Session-based authentication with secure session management

### 👥 **User Management**

- **Job Seekers**: Profile creation, resume upload, job applications, saved jobs
- **Employers**: Company profiles, job posting, application management, candidate review
- **Admins**: User management, system oversight, employer verification

### 💼 **Job Management**

- Advanced job posting with rich text descriptions
- Job search and filtering by location, industry, job type, salary
- Job application tracking with status updates
- Saved jobs functionality for job seekers
- Application deadline management

### 📊 **Dashboard & Analytics**

- Personalized dashboards for each user role
- Application statistics and insights
- Recent activity tracking
- Profile completion indicators

### 📧 **Communication**

- Automated email notifications for account verification
- Password reset emails
- Application status updates
- SMTP email configuration with Gmail integration

### 🎨 **User Interface**

- Responsive design for mobile and desktop
- Modern UI with intuitive navigation
- Rich text editor for job descriptions
- File upload for resumes and company logos

## Prerequisites

### System Requirements

- **PHP**: 8.0 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.0+
- **Web Server**: Apache/Nginx (XAMPP/WAMP recommended for development)
- **Composer**: For PHP dependency management
- **Node.js**: Optional, for frontend asset compilation
- **Git**: For version control

### External Services

- **Gmail Account**: For email functionality (SMTP)
- **Google OAuth**: For social login (optional)

## Installation & Setup

### Quick Start (XAMPP)

1. **Download and Install XAMPP**

   - Download from [apachefriends.org](https://www.apachefriends.org/)
   - Install with default settings

2. **Clone/Download the Project**

   ```bash
   # Navigate to XAMPP htdocs directory
   cd C:\xampp\htdocs

   # Clone the repository (or extract downloaded files)
   git clone <repository-url> job-finder
   # OR extract ZIP to job-finder folder
   ```

3. **Install PHP Dependencies**

   ```bash
   cd job-finder
   composer install
   ```

4. **Database Setup**

   ```bash
   # Start XAMPP Control Panel and start MySQL
   # Open phpMyAdmin at http://localhost/phpmyadmin

   # Create new database
   CREATE DATABASE job_finder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

   Import the database schema:

   ```sql
   -- Use phpMyAdmin or MySQL command line
   -- Import schema/SCHEMA.sql file
   ```

5. **Environment Configuration**

   ```bash
   # Copy environment template
   cp .env.example .env
   ```

   Edit `.env` file with your settings:

   ```env
   DB_HOST=localhost
   DB_NAME=job_finder
   DB_USER=root
   DB_PASS=

   MAIL_USERNAME=your-gmail@gmail.com
   MAIL_PASSWORD=your-app-password

   GOOGLE_CLIENT_ID=your-google-client-id
   GOOGLE_CLIENT_SECRET=your-google-client-secret
   ```

6. **Configure Gmail SMTP**

   - Enable 2-factor authentication on your Gmail account
   - Generate an App Password: [Google Account Settings](https://myaccount.google.com/apppasswords)
   - Update `MAIL_PASSWORD` in `.env` with the App Password

7. **Configure Google OAuth (Optional)**
   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project or select existing
   - Enable Google+ API
   - Create OAuth 2.0 credentials
   - Add authorized redirect URIs:
     - `http://localhost/job-finder/public/auth/google/callback.php`
   - Update `.env` with client ID and secret

### Alternative Setup Methods

#### Using Built-in PHP Server

```bash
cd /path/to/job-finder
php -S localhost:8000 -t public
```

Access at: `http://localhost:8000`

#### Using Docker (Advanced)

```bash
# If Docker setup exists
docker-compose up -d
```

### Post-Installation Steps

1. **Verify Installation**

   - Visit `http://localhost/job-finder/public/`
   - Register a new account
   - Check email verification works

2. **File Permissions**

   ```bash
   # Ensure web server can write to these directories
   chmod 755 uploads/
   chmod 755 uploads/resumes/
   chmod 755 uploads/company_logos/
   chmod 755 uploads/profile_photos/
   ```

3. **URL Rewriting**
   - Ensure Apache's `mod_rewrite` is enabled
   - `.htaccess` file is included in `public/` directory

## Usage

### Getting Started

1. **Access the Application**

   - Main URL: `http://localhost/job-finder/public/`
   - Admin Panel: `http://localhost/job-finder/public/admin/`

2. **User Registration**

   - Navigate to `/auth/register.php`
   - Fill in required information
   - Verify email before first login

3. **User Roles and Dashboards**
   - **Job Seekers**: Browse jobs, apply, manage profile
   - **Employers**: Post jobs, review applications, manage company profile
   - **Admins**: User management, system administration

### User Workflows

#### Job Seeker Workflow

```php
// Example: Applying to a job
$applicationController = new ApplicationController($conn);
$result = $applicationController->applyToJob(
    $jobId,
    $jobseekerUuid,
    $coverLetter,
    $resumePath
);
```

1. Register and verify email
2. Complete profile (personal info, skills, resume)
3. Search and browse job listings
4. Save interesting jobs
5. Apply with cover letter and resume
6. Track application status

#### Employer Workflow

```php
// Example: Posting a job
$jobController = new JobController($conn);
$jobData = [
    'title' => 'Software Developer',
    'job_description' => 'We are looking for...',
    'location' => 'Nairobi, Kenya',
    'salary_range' => 'KES 80,000 - 120,000'
];
$result = $jobController->postJob($jobData);
```

1. Register and complete company profile
2. Post job openings with detailed requirements
3. Review incoming applications
4. Update application statuses
5. Contact suitable candidates

#### Admin Workflow

- Manage user accounts
- Verify employer profiles
- Monitor system activity
- Handle support requests

### Key Features Demonstration

#### Job Search and Filtering

```
Location: Nairobi, Kenya
Industry: Technology
Job Type: Full-time
Salary Range: 80,000 - 150,000 KES
```

#### Application Management

- View all applications per job
- Update application status (pending → reviewed)
- Download resumes and cover letters
- Contact applicants directly

### Screenshots

_(Screenshots would be included here in a real project)_

- Login/Register Page
- Job Seeker Dashboard
- Employer Dashboard
- Job Posting Form
- Application Management

## API Documentation

This application uses traditional server-side rendering rather than REST APIs. However, here are the key programmatic interfaces:

### Core Classes and Methods

#### JobController

```php
class JobController {
    // Post a new job
    public function postJob($jobData)
    // View employer's jobs
    public function viewJobs()
    // Edit existing job
    public function editJob()
    // Delete job
    public function deleteJob($jobId)
    // Get job details
    public function getJobDetails($jobId)
}
```

#### ApplicationController

```php
class ApplicationController {
    // Get applications for employer
    public function getEmployerApplications($employerUuid)
    // Apply to job
    public function applyToJob($jobId, $jobseekerUuid, $coverLetter, $resumeFile)
    // Update application status
    public function updateApplicationStatus($applicationId, $status, $employerUuid)
    // Get application details
    public function getApplicationDetails($applicationId, $employerUuid)
}
```

#### User Model Methods

```php
class User {
    // Get user profile
    public function getUserProfile($userId)
    // Create new user
    public function createUser($userData)
    // Update user profile
    public function updateUser($userId, $userData)
}
```

### Database Schema Overview

```
users (Primary table)
├── job_seekers (Profile data)
├── employers (Company data)
├── admin_profiles (Admin data)
├── email_verifications (Email tokens)
└── password_resets (Reset tokens)

job_posts (Job listings)
├── applications (Job applications)
├── saved_jobs (Bookmarked jobs)
└── employers (Foreign key)
```

### Authentication Helpers

```php
// Check if user is authenticated
isAuthenticated()

// Check user role
isEmployer()
isJobSeeker()
isAdmin()

// Get current user ID
getUserId()

// CSRF protection
validate_csrf_token($token)
generate_csrf_token()
```

## Dependencies

### PHP Dependencies (Composer)

```json
{
  "require": {
    "phpmailer/phpmailer": "^7.0",
    "google/apiclient": "^2.18"
  }
}
```

### System Dependencies

- **PHPMailer**: Email sending functionality
- **Google API Client**: OAuth authentication
- **MySQLi**: Database connectivity
- **Sessions**: User session management

### Frontend Dependencies

- **TinyMCE**: Rich text editor for job descriptions
- **CSS**: Custom stylesheets
- **JavaScript**: Form validation and UI interactions

## Testing

### Manual Testing Procedures

#### 1. User Registration & Email Verification

**Test Case: Successful Registration**

1. Navigate to `/auth/register.php`
2. Fill form with valid data:
   - Name: "John Doe"
   - Email: "john.doe@example.com"
   - Password: "SecurePass123"
   - Confirm Password: "SecurePass123"
3. Submit form
4. **Expected**: Redirect to login with success message
5. Check email for verification link
6. Click verification link
7. **Expected**: Account verified, can now login

**Test Case: Invalid Registration**

- Try registering with existing email
- Try weak password (< 8 characters)
- Try mismatched passwords
- **Expected**: Appropriate error messages

#### 2. Authentication Testing

**Test Case: Login Flow**

1. Go to `/auth/login.php`
2. Enter verified credentials
3. **Expected**: Redirect to appropriate dashboard based on role

**Test Case: Password Reset**

1. Go to `/auth/forgot-password.php`
2. Enter registered email
3. Check email for reset link
4. Follow reset instructions
5. **Expected**: Password updated successfully

#### 3. Job Management Testing

**Test Case: Job Posting (Employer)**

1. Login as employer
2. Navigate to job posting form
3. Fill all required fields
4. Submit job
5. **Expected**: Job appears in employer's job list

**Test Case: Job Application (Job Seeker)**

1. Login as job seeker
2. Browse available jobs
3. Apply to a job with cover letter
4. **Expected**: Application recorded, status shows as pending

#### 4. Role-Based Access Testing

**Test Case: Access Control**

- Try accessing admin pages as job seeker
- Try accessing employer dashboard as admin
- **Expected**: Proper redirects and access denied messages

### Automated Testing

Currently, the application does not include automated tests. Recommended additions:

```php
// Example PHPUnit test structure
class JobControllerTest extends PHPUnit_Framework_TestCase {
    public function testPostJobValidation() {
        // Test job posting with valid/invalid data
    }

    public function testApplicationSubmission() {
        // Test job application process
    }
}
```

### Performance Testing

- Load testing with multiple concurrent users
- Database query optimization
- Email sending performance
- File upload handling

### Security Testing

- SQL injection prevention
- XSS vulnerability checks
- CSRF token validation
- Session security
- Password hashing verification

## Contributing

We welcome contributions to the Job Finder Portal! Please follow these guidelines:

### Development Setup

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature-name`
3. Set up development environment following installation steps
4. Make your changes
5. Test thoroughly
6. Commit with clear messages: `git commit -m "Add: Feature description"`

### Code Standards

#### PHP Code Style

```php
<?php
// Use meaningful variable names
class UserController {
    private $db;
    private $userModel;

    public function __construct($database) {
        $this->db = $database;
        $this->userModel = new User($database);
    }

    // Use type hints and return types
    public function getUserById(int $userId): ?array {
        return $this->userModel->findById($userId);
    }
}
```

#### Database Queries

```php
// Use prepared statements
$stmt = $this->conn->prepare("
    SELECT * FROM users
    WHERE email = ? AND is_verified = 1
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
```

#### Security Best Practices

- Always validate and sanitize user input
- Use prepared statements for database queries
- Implement CSRF protection on forms
- Hash passwords with bcrypt
- Validate file uploads and restrict file types

### Pull Request Process

1. **Update Documentation**: Ensure README and code comments are updated
2. **Add Tests**: Include tests for new features
3. **Code Review**: Request review from maintainers
4. **Merge**: Squash commits and merge after approval

### Issue Reporting

When reporting bugs, please include:

- PHP version and server environment
- Steps to reproduce
- Expected vs actual behavior
- Error messages/logs
- Browser/console information

### Feature Requests

For new features, please:

- Describe the problem it solves
- Provide mockups or examples if applicable
- Consider backward compatibility

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2024 Job Finder Portal

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

## Support

### Getting Help

- **Documentation**: Check this README and inline code comments
- **Issues**: [GitHub Issues](https://github.com/your-repo/job-finder/issues)
- **Discussions**: [GitHub Discussions](https://github.com/your-repo/job-finder/discussions)

### Contact Information

- **Project Lead**: Bonface Njuguna
- **Email**: bonfacenjuguna438@gmail.com
- **LinkedIn**: [Your LinkedIn Profile]
- **Website**: [Project Website]

### Community

- Join our Discord server for real-time support
- Follow us on Twitter for updates
- Subscribe to our newsletter for monthly updates

### Professional Support

For enterprise support, custom development, or consulting services:

- Email: support@jobfinderportal.com
- Phone: +254 XXX XXX XXX
- Business Hours: Monday-Friday, 9 AM - 6 PM EAT

---

## Application Architecture

### System Overview

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Web Browser   │────│   Apache/Nginx  │────│     PHP App     │
│                 │    │                 │    │                 │
│ - HTML/CSS/JS   │    │ - URL Routing   │    │ - Controllers   │
│ - AJAX Requests │    │ - Static Files  │    │ - Models        │
└─────────────────┘    └─────────────────┘    │ - Views         │
                                              └─────────────────┘
                                                       │
                                              ┌─────────────────┐
                                              │   MySQL DB      │
                                              │                 │
                                              │ - Users         │
                                              │ - Jobs          │
                                              │ - Applications  │
                                              └─────────────────┘
```

### Directory Structure

```
job-finder/
├── config/                 # Configuration files
│   ├── db.php             # Database connection
│   └── google-oauth.php   # OAuth configuration
├── controllers/           # Business logic
│   ├── AuthController.php
│   ├── JobController.php
│   └── ApplicationController.php
├── helpers/               # Utility functions
│   ├── csrf.php          # CSRF protection
│   ├── Mailer.php        # Email handling
│   └── session.php       # Session management
├── middleware/            # Request filters
│   └── auth.php          # Authentication checks
├── models/                # Data models
│   ├── User.php
│   ├── Job.php
│   └── Application.php
├── public/                # Web root
│   ├── index.php         # Entry point
│   ├── auth/             # Authentication pages
│   ├── jobs/             # Job-related pages
│   ├── applications/     # Application pages
│   └── admin/            # Admin panel
├── assets/                # Static assets
│   ├── css/
│   ├── js/
│   └── icons/
├── includes/              # Reusable components
│   ├── header.php
│   ├── footer.php
│   └── navbar.php
├── uploads/               # User uploads
│   ├── resumes/
│   ├── company_logos/
│   └── profile_photos/
├── schema/                # Database schema
├── .env                   # Environment variables
├── .env.example          # Environment template
└── composer.json         # PHP dependencies
```

### Database Schema

#### Core Tables

- **`users`**: User accounts with UUID primary keys
- **`job_seekers`**: Job seeker profile information
- **`employers`**: Employer/company profile information
- **`admin_profiles`**: Admin user profiles
- **`job_posts`**: Job listings with full details
- **`applications`**: Job applications with status tracking
- **`saved_jobs`**: User's saved/bookmarked jobs

#### Supporting Tables

- **`email_verifications`**: Email verification tokens
- **`password_resets`**: Password reset tokens

### Security Features

- **Authentication**: Session-based with secure cookies
- **Authorization**: Role-based access control (RBAC)
- **Data Protection**: Input validation and sanitization
- **CSRF Protection**: Tokens on all forms
- **XSS Prevention**: Output escaping
- **Password Security**: bcrypt hashing
- **File Upload Security**: Type and size restrictions

## Troubleshooting

### Common Issues and Solutions

#### Email Not Sending

**Symptoms**: Registration emails not received, password reset not working

**Solutions**:

1. Verify Gmail credentials in `.env` file
2. Ensure 2FA is enabled on Gmail account
3. Generate and use App Password (not regular password)
4. Check spam/junk folder
5. Review PHP error logs: `tail -f /xampp/php/logs/php_error_log`
6. Test SMTP connection manually

#### Database Connection Issues

**Symptoms**: "Connection failed" errors, blank pages

**Solutions**:

1. Verify MySQL service is running in XAMPP
2. Check database credentials in `.env`
3. Ensure database `job_finder` exists
4. Verify user has proper permissions
5. Test connection: `mysql -u username -p database_name`

#### 404 Errors

**Symptoms**: Page not found when accessing routes

**Solutions**:

1. Ensure URL structure: `http://localhost/job-finder/public/`
2. Verify Apache `mod_rewrite` is enabled
3. Check `.htaccess` file exists in `public/` directory
4. Confirm file permissions are correct

#### Registration/Login Issues

**Symptoms**: Cannot login after registration, "Invalid credentials"

**Solutions**:

1. Check if email is verified in database
2. Verify password requirements (8+ characters)
3. Check for database connection issues
4. Review PHP error logs
5. Clear browser cache/cookies

#### File Upload Issues

**Symptoms**: Resume/company logo uploads failing

**Solutions**:

1. Check upload directory permissions: `chmod 755 uploads/`
2. Verify file size limits in `php.ini`
3. Check allowed file types
4. Ensure sufficient disk space

### Debug Mode

Enable debug mode by setting in your environment:

```env
DEBUG=true
```

This will show detailed error messages and logging.

### Performance Issues

**Slow Loading**:

- Check database query performance
- Enable PHP opcode caching (OPcache)
- Optimize images and assets
- Consider database indexing

**High Memory Usage**:

- Monitor PHP memory_limit in `php.ini`
- Check for memory leaks in custom code
- Optimize database queries

## Development Notes

### Architecture Decisions

- **UUID Primary Keys**: Instead of auto-increment IDs for better security
- **Session-Based Auth**: Traditional server-side sessions for reliability
- **MVC Pattern**: Separated concerns with controllers, models, and views
- **Composer Dependencies**: Modern PHP dependency management

### Security Considerations

- **Email Verification**: Mandatory before account activation
- **Password Requirements**: Minimum 8 characters, hashed with bcrypt
- **Token Expiration**: Password resets (1 hour), Email verification (24 hours)
- **CSRF Protection**: Tokens on all forms to prevent cross-site request forgery
- **Input Validation**: Server-side validation on all user inputs

### Future Enhancements

- REST API endpoints for mobile app integration
- Real-time notifications with WebSockets
- Advanced search with Elasticsearch
- Multi-language support (i18n)
- Payment integration for premium features

---

_For additional support, please refer to the contact information above or create an issue in the project repository._
