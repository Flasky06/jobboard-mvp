# job-finder Application

A PHP-based job portal application with user authentication, email verification, and role-based access control.

## Prerequisites

- PHP 8.0 or higher
- MySQL/MariaDB
- Composer (for dependency management)
- XAMPP or similar web server environment
- Gmail account (for email functionality)

## Installation & Setup

### 1. Clone/Download the Project

Place the project files in your web server's document root:

- XAMPP: `C:\xampp\htdocs\job-finder`
- WAMP: `C:\wamp\www\job-finder`

### 2. Install Dependencies

```bash
composer install
```

### 3. Database Setup

1. Start your MySQL server (via XAMPP control panel)
2. Create a new database named `job_finder`
3. Import the schema:

```sql
-- Run the contents of schema/SCHEMA.sql in your MySQL client
-- Or use phpMyAdmin to import the file
```

4. Configure database connection in `config/db.php`:
   - Update host, username, password, and database name as needed

### 4. Email Configuration

Update `helpers/Mailer.php` with your Gmail credentials:

```php
$this->Username = 'your-email@gmail.com';
$this->Password = 'your-app-password'; // Use App Password, not regular password
```

**Important**: Enable 2-factor authentication on your Gmail account and generate an App Password for this application.

## Running the Application

### Option 1: Using PHP Built-in Server

```bash
cd "C:\xampp\htdocs\job-finder"
php -S localhost:8000 -t public
```

Access at: http://localhost:8000

### Option 2: Using XAMPP

1. Start Apache and MySQL from XAMPP control panel
2. Access at: http://localhost/job-finder/public/

## Testing the Application

### 1. User Registration & Email Verification

1. Navigate to `http://localhost:8000/register`
2. Fill out the registration form:
   - Name: Any name (e.g., "John Doe")
   - Email: A valid email address you can access
   - Password: At least 8 characters
   - Confirm Password: Same as password
3. Click "Create Account"
4. Check your email for the verification link
5. Click the verification link in the email
6. Try to log in with your credentials

**Expected Behavior:**

- After registration, you should be redirected to login page with a success message
- Email verification is required before login
- Unverified users cannot log in

### 2. User Login

1. Go to `http://localhost:8000/login`
2. Enter your verified email and password
3. Click "Login"

**Expected Behavior:**

- Successful login redirects based on user role:
  - Job Seekers: `home`
  - Employers: `employer-dashboard`
  - Admins: `admin-dashboard`

### 3. Password Reset

1. Go to `http://localhost:8000/forgot-password`
2. Enter your registered email
3. Check email for reset link
4. Click the reset link and follow instructions

### 4. Role-Based Access

Test different user roles by manually updating the database:

```sql
UPDATE users SET role = 'employer' WHERE email = 'your-email@example.com';
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

## Application Structure

```
job-finder/
├── config/
│   └── db.php                 # Database configuration
├── controllers/
│   ├── AuthController.php     # Authentication logic
│   └── ProfileController.php  # User profile management
├── helpers/
│   ├── csrf.php              # CSRF protection
│   ├── Mailer.php            # Email functionality
│   └── session.php           # Session management
├── middleware/
│   └── auth.php              # Authentication middleware
├── models/
│   └── User.php              # User model and database operations
├── public/                   # Publicly accessible files
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── verify-email.php
│   ├── forgot-password.php
│   ├── reset-password.php
│   ├── home.php
│   ├── employer-dashboard.php
│   └── admin-dashboard.php
├── views/                    # View templates
├── includes/                 # Reusable components
│   ├── header.php
│   ├── navbar.php
│   └── footer.php
├── css/
│   └── styles.css            # Application styles
├── schema/
│   └── SCHEMA.sql            # Database schema
└── composer.json             # PHP dependencies
```

## Database Tables

- `users`: User accounts with UUID primary keys
- `email_verifications`: Email verification tokens
- `password_resets`: Password reset tokens
- `employers`: Employer profile information
- `job_seekers`: Job seeker profile information
- `jobs`: Job postings
- `applications`: Job applications

## Security Features

- CSRF protection on all forms
- Password hashing with bcrypt
- Email verification required for account activation
- Session-based authentication
- Input validation and sanitization

## Troubleshooting

### Email Not Sending

1. Check Gmail credentials in `Mailer.php`
2. Ensure 2FA is enabled and using App Password
3. Check PHP error logs for SMTP errors

### Database Connection Issues

1. Verify MySQL server is running
2. Check credentials in `config/db.php`
3. Ensure database and tables exist

### 404 Errors

1. Ensure you're accessing via the correct URL
2. Check that Apache rewrite module is enabled (for clean URLs)
3. Verify file permissions

### Registration/Login Issues

1. Check database for user records
2. Verify email verification status
3. Check PHP error logs for database errors

## Development Notes

- The application uses UUIDs for user identification instead of auto-increment IDs
- Email verification is mandatory before login
- Password reset tokens expire after 1 hour
- Email verification tokens expire after 24 hours
- All forms include CSRF protection

## Support

For issues or questions, check the application logs and ensure all prerequisites are properly configured.
