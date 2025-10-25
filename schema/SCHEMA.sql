CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'employer', 'jobseeker') DEFAULT 'jobseeker',
  email_verified BOOLEAN DEFAULT FALSE,
  email_verification_token VARCHAR(255),
  password_reset_token VARCHAR(255),
  password_reset_expires TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE jobseeker_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150),
  user_id INT NOT NULL,
  phone_number VARCHAR(20),
  gender ENUM('male','female','other'),
  date_of_birth DATE,
  nationality VARCHAR(100),
  professional_title VARCHAR(100),
  current_location VARCHAR(100),
  preferred_job_type ENUM('full-time','part-time','remote','internship'),
  about_me TEXT,
  profile_photo VARCHAR(255),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE employer_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  company_name VARCHAR(255),
  company_website VARCHAR(255),
  company_description TEXT,
  location VARCHAR(100),
  contact_number VARCHAR(20),
  company_logo VARCHAR(255),
  FOREIGN KEY (user_id) REFERENCES users(id)
);


CREATE TABLE admin_profiles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  full_name VARCHAR(150),
  contact_number VARCHAR(20),
  admin_photo VARCHAR(255),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Skills table for jobseekers
CREATE TABLE skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  category VARCHAR(100)
);

-- Jobseeker skills junction table
CREATE TABLE jobseeker_skills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jobseeker_profile_id INT NOT NULL,
  skill_id INT NOT NULL,
  proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'beginner',
  FOREIGN KEY (jobseeker_profile_id) REFERENCES jobseeker_profiles(id) ON DELETE CASCADE,
  FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
  UNIQUE KEY unique_jobseeker_skill (jobseeker_profile_id, skill_id)
);

-- Education table for jobseekers
CREATE TABLE education (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jobseeker_profile_id INT NOT NULL,
  institution_name VARCHAR(255) NOT NULL,
  degree VARCHAR(255) NOT NULL,
  field_of_study VARCHAR(255),
  start_date DATE,
  end_date DATE,
  grade VARCHAR(50),
  description TEXT,
  FOREIGN KEY (jobseeker_profile_id) REFERENCES jobseeker_profiles(id) ON DELETE CASCADE
);

-- Work experience table for jobseekers
CREATE TABLE work_experience (
  id INT AUTO_INCREMENT PRIMARY KEY,
  jobseeker_profile_id INT NOT NULL,
  company_name VARCHAR(255) NOT NULL,
  job_title VARCHAR(255) NOT NULL,
  employment_type ENUM('full-time', 'part-time', 'contract', 'internship', 'freelance') DEFAULT 'full-time',
  location VARCHAR(255),
  start_date DATE NOT NULL,
  end_date DATE,
  is_current_position BOOLEAN DEFAULT FALSE,
  description TEXT,
  FOREIGN KEY (jobseeker_profile_id) REFERENCES jobseeker_profiles(id) ON DELETE CASCADE
);

-- Jobs table
CREATE TABLE jobs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  employer_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  requirements TEXT,
  location VARCHAR(255),
  job_type ENUM('full-time', 'part-time', 'contract', 'internship', 'remote') DEFAULT 'full-time',
  salary_min DECIMAL(10,2),
  salary_max DECIMAL(10,2),
  currency VARCHAR(3) DEFAULT 'USD',
  application_deadline DATE,
  status ENUM('active', 'inactive', 'expired', 'filled') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Job applications table
CREATE TABLE job_applications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  job_id INT NOT NULL,
  jobseeker_id INT NOT NULL,
  cover_letter TEXT,
  resume_file VARCHAR(255),
  status ENUM('pending', 'reviewed', 'shortlisted', 'interviewed', 'accepted', 'rejected') DEFAULT 'pending',
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
  FOREIGN KEY (jobseeker_id) REFERENCES users(id) ON DELETE CASCADE,
  UNIQUE KEY unique_job_application (job_id, jobseeker_id)
);

-- Indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_jobseeker_profiles_user_id ON jobseeker_profiles(user_id);
CREATE INDEX idx_employer_profiles_user_id ON employer_profiles(user_id);
CREATE INDEX idx_admin_profiles_user_id ON admin_profiles(user_id);
CREATE INDEX idx_jobs_employer_id ON jobs(employer_id);
CREATE INDEX idx_jobs_status ON jobs(status);
CREATE INDEX idx_job_applications_job_id ON job_applications(job_id);
CREATE INDEX idx_job_applications_jobseeker_id ON job_applications(jobseeker_id);
CREATE INDEX idx_job_applications_status ON job_applications(status);
