-- USERS TABLE (Auth)
CREATE TABLE users (
  uuid CHAR(36) PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  profile_picture VARCHAR(255),
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'employer', 'jobseeker') DEFAULT 'jobseeker',
  is_verified BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- EMAIL VERIFICATION TOKENS
CREATE TABLE email_verifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_uuid CHAR(36) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  is_used BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
);

-- PASSWORD RESET TOKENS
CREATE TABLE password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_uuid CHAR(36) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  is_used BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
);

-- EMPLOYERS
CREATE TABLE employers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_uuid CHAR(36) NOT NULL,
  company_name VARCHAR(150),
  contact_number VARCHAR(20),
  position VARCHAR(100),
  FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
);

-- JOB SEEKERS (biodata/profile)
CREATE TABLE job_seekers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_uuid CHAR(36) NOT NULL,
  phone VARCHAR(20),
  gender ENUM('male', 'female', 'other'),
  dob DATE,
  location VARCHAR(255),
  bio TEXT,
  professional_title VARCHAR(100),
  skills TEXT,
  education TEXT,
  FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
);

-- JOBS
CREATE TABLE jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employer_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  location VARCHAR(255),
  type ENUM('full-time', 'part-time', 'contract', 'remote') DEFAULT 'full-time',
  salary_range VARCHAR(100),
  requirements TEXT,
  status ENUM('open', 'closed') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (employer_id) REFERENCES employers(id) ON DELETE CASCADE
);

-- APPLICATIONS
CREATE TABLE applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  job_id INT NOT NULL,
  job_seeker_id INT NOT NULL,
  cover_letter TEXT,
  status ENUM('pending', 'reviewed', 'accepted', 'rejected') DEFAULT 'pending',
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  FOREIGN KEY (job_seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE
);
