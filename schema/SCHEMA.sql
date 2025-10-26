-- ========================================
-- JOB PORTAL MVP DATABASE SCHEMA (FINAL)
-- ========================================

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- USERS TABLE (Authentication)
-- --------------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  uuid CHAR(36) PRIMARY KEY,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  profile_picture VARCHAR(255),
  role ENUM('admin', 'employer', 'jobseeker') DEFAULT 'jobseeker',
  is_verified BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_email (email),
  INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- EMAIL VERIFICATION TOKENS
-- --------------------------------------------------------
DROP TABLE IF EXISTS email_verifications;
CREATE TABLE email_verifications (
  uuid CHAR(36) PRIMARY KEY,
  user_uuid CHAR(36) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  is_used BOOLEAN DEFAULT FALSE,
  verified_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_user_uuid (user_uuid),
  INDEX idx_token (token),
  INDEX idx_expires (expires_at),
  
  FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- PASSWORD RESET TOKENS
-- --------------------------------------------------------
DROP TABLE IF EXISTS password_resets;
CREATE TABLE password_resets (
  uuid CHAR(36) PRIMARY KEY,
  user_uuid CHAR(36) NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  is_used BOOLEAN DEFAULT FALSE,
  used_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_user_uuid (user_uuid),
  INDEX idx_token (token),
  INDEX idx_expires (expires_at),
  
  FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- EMPLOYERS
-- --------------------------------------------------------
DROP TABLE IF EXISTS employers;
CREATE TABLE employers (
  uuid CHAR(36) PRIMARY KEY,
  user_uuid CHAR(36) NOT NULL,
  company_name VARCHAR(150),
  contact_number VARCHAR(20),
  location VARCHAR(255),
  industry VARCHAR(100),
  website VARCHAR(255),  
  company_logo VARCHAR(255),
  about_company TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_user_uuid (user_uuid),
  INDEX idx_industry (industry),
  
  FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- JOB SEEKERS (Profile/Biodata)
-- --------------------------------------------------------
DROP TABLE IF EXISTS job_seekers;
CREATE TABLE job_seekers (
   uuid CHAR(36) PRIMARY KEY,
   user_uuid CHAR(36) NOT NULL,
   fullName VARCHAR(100),
   phone VARCHAR(20),
   gender ENUM('male', 'female', 'other'),
   dob DATE,
   location VARCHAR(255),
   bio TEXT,
   professional_title VARCHAR(100),
   skills TEXT,
   education TEXT,
   resume_file VARCHAR(255),
   profile_completed BOOLEAN DEFAULT FALSE,
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

   INDEX idx_user_uuid (user_uuid),
   INDEX idx_location (location),
   INDEX idx_professional_title (professional_title),

   FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- ADMIN PROFILES
-- --------------------------------------------------------
DROP TABLE IF EXISTS admin_profiles;
CREATE TABLE admin_profiles (
   uuid CHAR(36) PRIMARY KEY,
   user_uuid CHAR(36) NOT NULL,
   full_name VARCHAR(100),
   contact_number VARCHAR(20),
   created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

   INDEX idx_user_uuid (user_uuid),

   FOREIGN KEY (user_uuid) REFERENCES users(uuid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- JOB POSTS
-- --------------------------------------------------------
DROP TABLE IF EXISTS job_posts;
CREATE TABLE job_posts (
  uuid CHAR(36) PRIMARY KEY,
  employer_uuid CHAR(36) NOT NULL,
  title VARCHAR(150) NOT NULL,
  job_level VARCHAR(100),
  job_description TEXT NOT NULL,
  job_type VARCHAR(100),
  industry VARCHAR(100),
  location VARCHAR(255),
  salary_range VARCHAR(100),
  additional_information TEXT,
  requirements_qualifications TEXT,
  status ENUM('open', 'closed') DEFAULT 'open',
  application_deadline DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_employer_uuid (employer_uuid),
  INDEX idx_status (status),
  INDEX idx_industry (industry),
  INDEX idx_job_type (job_type),
  INDEX idx_location (location),
  INDEX idx_deadline (application_deadline),
  INDEX idx_created (created_at),
  
  FOREIGN KEY (employer_uuid) REFERENCES employers(uuid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- APPLICATIONS
-- --------------------------------------------------------
DROP TABLE IF EXISTS applications;
CREATE TABLE applications (
  uuid CHAR(36) PRIMARY KEY,
  job_uuid CHAR(36) NOT NULL,
  job_seeker_uuid CHAR(36) NOT NULL,
  cover_letter TEXT,
  resume_file VARCHAR(255),
  status ENUM('pending', 'reviewed', 'accepted', 'rejected') DEFAULT 'pending',
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_at TIMESTAMP NULL,

  INDEX idx_job_uuid (job_uuid),
  INDEX idx_job_seeker_uuid (job_seeker_uuid),
  INDEX idx_status (status),
  INDEX idx_applied_at (applied_at),

  UNIQUE KEY unique_application (job_uuid, job_seeker_uuid),

  FOREIGN KEY (job_uuid) REFERENCES job_posts(uuid) ON DELETE CASCADE,
  FOREIGN KEY (job_seeker_uuid) REFERENCES job_seekers(uuid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- SAVED JOBS
-- --------------------------------------------------------
DROP TABLE IF EXISTS saved_jobs;
CREATE TABLE saved_jobs (
  uuid CHAR(36) PRIMARY KEY,
  job_uuid CHAR(36) NOT NULL,
  job_seeker_uuid CHAR(36) NOT NULL,
  saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_job_uuid (job_uuid),
  INDEX idx_job_seeker_uuid (job_seeker_uuid),
  INDEX idx_saved_at (saved_at),
  
  UNIQUE KEY unique_saved_job (job_uuid, job_seeker_uuid),
  
  FOREIGN KEY (job_uuid) REFERENCES job_posts(uuid) ON DELETE CASCADE,
  FOREIGN KEY (job_seeker_uuid) REFERENCES job_seekers(uuid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ========================================
-- SEEDED DATA WITH EXTENSIVE DUMMY DATA
-- ========================================

-- ========================================
-- 1. USERS (10 job seekers, 5 employers, 2 admins)
-- ========================================

-- Your existing verified users
INSERT INTO users (uuid, email, password, profile_picture, role, is_verified, created_at) VALUES
('74ba64a8-091d-4c4c-ab44-408dc62c4bd9', 'bonnienjuguna106@gmail.com', '$2y$10$.SE.Lcs8gDqbFoSqe..5Mum690ImL0IUrt5aCJf.3swZUQiWmQmLy', NULL, 'jobseeker', TRUE, '2025-10-26 05:38:42'),
('4609a1c9-9755-4b93-b242-4b3113119340', 'bonfacenjuguna438@gmail.com', '$2y$10$cmHCVfO0i5VQn/dij2quuurohAIQAf8TzzxG.wfODRJhzalxyEC0.', NULL, 'employer', TRUE, '2025-10-26 08:57:51');

-- Admins
INSERT INTO users (uuid, email, password, profile_picture, role, is_verified, created_at) VALUES
('admin-uuid-001', 'admin@jobportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', TRUE, '2025-10-01 00:00:00'),
('admin-uuid-002', 'superadmin@jobportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', TRUE, '2025-10-01 00:00:00');

-- Job Seekers
INSERT INTO users (uuid, email, password, profile_picture, role, is_verified, created_at) VALUES
('js-uuid-002', 'jane.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', TRUE, '2025-10-15 09:00:00'),
('js-uuid-003', 'michael.ochieng@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', TRUE, '2025-10-16 10:30:00'),
('js-uuid-004', 'sarah.wanjiku@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', TRUE, '2025-10-17 11:15:00'),
('js-uuid-005', 'david.kimani@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', TRUE, '2025-10-18 14:20:00'),
('js-uuid-006', 'grace.akinyi@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', TRUE, '2025-10-19 08:45:00'),
('js-uuid-007', 'peter.mwangi@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', TRUE, '2025-10-20 13:30:00'),
('js-uuid-008', 'mercy.wambui@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', TRUE, '2025-10-21 09:10:00'),
('js-uuid-009', 'james.otieno@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', TRUE, '2025-10-22 15:00:00'),
('js-uuid-010', 'lucy.njeri@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', FALSE, '2025-10-23 10:20:00');

-- Employers
INSERT INTO users (uuid, email, password, profile_picture, role, is_verified, created_at) VALUES
('emp-uuid-002', 'hr@digitalventures.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'employer', TRUE, '2025-10-10 10:00:00'),
('emp-uuid-003', 'jobs@safaricomke.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'employer', TRUE, '2025-10-11 11:00:00'),
('emp-uuid-004', 'careers@kenyabank.co.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'employer', TRUE, '2025-10-12 12:00:00'),
('emp-uuid-005', 'recruitment@eastafricabreweries.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'employer', TRUE, '2025-10-13 13:00:00');

-- ========================================
-- 2. EMAIL VERIFICATIONS
-- ========================================

INSERT INTO email_verifications (uuid, user_uuid, token, expires_at, is_used, verified_at, created_at) VALUES
('285316ba-a959-49e7-b3ed-d65c76805266', '74ba64a8-091d-4c4c-ab44-408dc62c4bd9', '9064dceb239e8a92e5ad6d52908c6f7267d39500f744bc16fe9c880df2a4e0b3', '2025-10-27 06:38:42', TRUE, '2025-10-26 06:00:00', '2025-10-26 05:38:42'),
('7d38331f-9d1b-4306-9a21-2c3cbb430677', '4609a1c9-9755-4b93-b242-4b3113119340', '498a2d429192d4419f4e11ef1d3a997035f018a9e0e3b9d92792cf870e9f724e', '2025-10-27 09:57:51', TRUE, '2025-10-26 09:00:00', '2025-10-26 08:57:51'),
('ev-uuid-003', 'js-uuid-002', 'abc123token456def789ghi012jkl345mno678pqr901stu234vwx567yza890bcd', '2025-10-16 09:00:00', TRUE, '2025-10-15 09:30:00', '2025-10-15 09:00:00'),
('ev-uuid-004', 'js-uuid-010', 'xyz987token654wvu321tsr098qpo765nml432kji109hgf876edc543baz210yxw', '2025-10-24 10:20:00', FALSE, NULL, '2025-10-23 10:20:00');

-- ========================================
-- 3. PASSWORD RESETS
-- ========================================

INSERT INTO password_resets (uuid, user_uuid, token, expires_at, is_used, used_at, created_at) VALUES
('pr-uuid-001', 'js-uuid-003', 'reset123token456def789ghi012jkl345mno678pqr901stu234vwx567yza890bcd', '2025-10-25 12:00:00', TRUE, '2025-10-25 11:30:00', '2025-10-25 11:00:00'),
('pr-uuid-002', 'js-uuid-005', 'reset987token654wvu321tsr098qpo765nml432kji109hgf876edc543baz210yxw', '2025-10-27 15:00:00', FALSE, NULL, '2025-10-26 14:00:00');

-- ========================================
-- 4. ADMIN PROFILES
-- ========================================

INSERT INTO admin_profiles (uuid, user_uuid, full_name, contact_number, created_at) VALUES
('admin-profile-001', 'admin-uuid-001', 'John Admin', '0700123456', '2025-10-01 00:00:00'),
('admin-profile-002', 'admin-uuid-002', 'Mary SuperAdmin', '0711234567', '2025-10-01 00:00:00');

-- ========================================
-- 5. JOB SEEKERS PROFILES
-- ========================================

INSERT INTO job_seekers (uuid, user_uuid, fullName, phone, gender, dob, location, bio, professional_title, skills, education, resume_file, profile_completed, created_at) VALUES
('b4b1b964-31f8-4835-a4b7-5c06c33f2c57', '74ba64a8-091d-4c4c-ab44-408dc62c4bd9', 'Bonface Njuguna', '0717299106', 'male', '1998-03-15', 'Nairobi', 'Passionate software developer with experience in building scalable web applications.', 'Software Developer', 'PHP, JavaScript, MySQL, HTML, CSS, Laravel, React', 'Bachelor of Science in Computer Science\nUniversity of Nairobi, 2023', NULL, TRUE, '2025-10-26 08:52:50'),

('js-profile-002', 'js-uuid-002', 'Jane Doe', '0722334455', 'female', '1995-05-15', 'Mombasa, Kenya', 'Creative UI/UX designer with a passion for creating beautiful and functional user interfaces.', 'UI/UX Designer', 'Figma, Adobe XD, Sketch, Prototyping, User Research', 'Bachelor of Arts in Design\nStrathmore University, 2022', NULL, TRUE, '2025-10-15 09:30:00'),

('js-profile-003', 'js-uuid-003', 'Michael Ochieng', '0733445566', 'male', '1992-08-22', 'Kisumu, Kenya', 'Experienced data analyst with strong analytical and problem-solving skills.', 'Data Analyst', 'Python, SQL, Tableau, Excel, Power BI, Statistical Analysis', 'Master of Science in Data Science\nJomo Kenyatta University, 2020', NULL, TRUE, '2025-10-16 11:00:00'),

('js-profile-004', 'js-uuid-004', 'Sarah Wanjiku', '0744556677', 'female', '1997-12-10', 'Nakuru, Kenya', 'Marketing professional with expertise in digital marketing and brand management.', 'Digital Marketing Specialist', 'SEO, SEM, Social Media Marketing, Content Marketing, Google Analytics', 'Bachelor of Commerce in Marketing\nKenyatta University, 2021', NULL, TRUE, '2025-10-17 11:45:00'),

('js-profile-005', 'js-uuid-005', 'David Kimani', '0755667788', 'male', '1990-04-18', 'Nairobi, Kenya', 'Senior accountant with 8+ years of experience in financial management and auditing.', 'Senior Accountant', 'Financial Reporting, Auditing, Tax Planning, QuickBooks, SAP', 'Bachelor of Commerce in Accounting\nUniversity of Nairobi, 2015\nCPA (K) Certified', NULL, TRUE, '2025-10-18 14:50:00'),

('js-profile-006', 'js-uuid-006', 'Grace Akinyi', '0766778899', 'female', '1994-11-25', 'Eldoret, Kenya', 'Registered nurse with experience in critical care and patient management.', 'Registered Nurse', 'Patient Care, Emergency Response, Medical Documentation, Healthcare Management', 'Bachelor of Science in Nursing\nMoi University, 2019', NULL, TRUE, '2025-10-19 09:15:00'),

('js-profile-007', 'js-uuid-007', 'Peter Mwangi', '0777889900', 'male', '1996-02-14', 'Thika, Kenya', 'Mechanical engineer specializing in industrial automation and maintenance.', 'Mechanical Engineer', 'AutoCAD, SolidWorks, PLC Programming, Maintenance, Project Management', 'Bachelor of Engineering in Mechanical Engineering\nTechnical University of Kenya, 2020', NULL, TRUE, '2025-10-20 14:00:00'),

('js-profile-008', 'js-uuid-008', 'Mercy Wambui', '0788990011', 'female', '1999-07-08', 'Nyeri, Kenya', 'Recent graduate seeking opportunities in human resource management.', 'HR Assistant', 'Recruitment, Employee Relations, HR Policies, MS Office', 'Bachelor of Arts in Human Resource Management\nMount Kenya University, 2024', NULL, TRUE, '2025-10-21 09:40:00'),

('js-profile-009', 'js-uuid-009', 'James Otieno', '0799001122', 'male', '1993-09-30', 'Mombasa, Kenya', 'Sales professional with proven track record in B2B and B2C sales.', 'Sales Executive', 'Sales Strategy, Client Relations, Negotiation, CRM Software, Market Research', 'Diploma in Sales and Marketing\nKenya Institute of Management, 2017', NULL, TRUE, '2025-10-22 15:30:00'),

('js-profile-010', 'js-uuid-010', 'Lucy Njeri', '0700112233', 'female', '2000-01-20', 'Nairobi, Kenya', 'Aspiring graphic designer looking for entry-level opportunities.', 'Graphic Designer', 'Adobe Photoshop, Illustrator, InDesign, Canva', 'Certificate in Graphic Design\nNairobi Design School, 2023', NULL, FALSE, '2025-10-23 10:50:00');

-- ========================================
-- 6. EMPLOYERS PROFILES
-- ========================================

INSERT INTO employers (uuid, user_uuid, company_name, contact_number, location, industry, website, about_company, created_at) VALUES
('emp-4609a1c9-profile', '4609a1c9-9755-4b93-b242-4b3113119340', 'Tech Innovations Kenya', '0717299106', 'Nairobi, Kenya', 'Technology', 'https://techinnovations.co.ke', 'Leading technology company specializing in software development and digital transformation solutions.', '2025-10-26 08:58:00'),

('emp-profile-002', 'emp-uuid-002', 'Digital Ventures Ltd', '0733445566', 'Kisumu, Kenya', 'Digital Marketing', 'https://digitalventures.co.ke', 'Full-service digital marketing agency helping businesses grow their online presence.', '2025-10-10 10:15:00'),

('emp-profile-003', 'emp-uuid-003', 'Safaricom Kenya', '0722000000', 'Nairobi, Kenya', 'Telecommunications', 'https://safaricom.co.ke', 'Leading telecommunications company in East Africa providing mobile, internet, and financial services.', '2025-10-11 11:30:00'),

('emp-profile-004', 'emp-uuid-004', 'Kenya Commercial Bank', '0711123456', 'Nairobi, Kenya', 'Finance', 'https://kcbgroup.com', 'Leading financial services provider offering banking, insurance, and investment solutions across East Africa.', '2025-10-12 12:45:00'),

('emp-profile-005', 'emp-uuid-005', 'East Africa Breweries', '0700987654', 'Nairobi, Kenya', 'Manufacturing', 'https://eabl.com', 'Leading beverage alcohol company in East Africa with a diverse portfolio of beer, spirits and non-alcoholic brands.', '2025-10-13 13:15:00');

-- ========================================
-- 7. JOB POSTS (20 jobs - mix of open and closed)
-- ========================================

-- Tech Innovations Kenya Jobs
INSERT INTO job_posts (uuid, employer_uuid, title, job_level, job_description, job_type, industry, location, salary_range, requirements_qualifications, status, application_deadline, created_at) VALUES
('job-001', 'emp-4609a1c9-profile', 'Senior PHP Developer', 'Senior Level', 'We are looking for an experienced PHP developer to join our growing team. You will be responsible for developing and maintaining web applications using modern PHP frameworks.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 120,000 - 180,000', '- 5+ years of PHP development experience\n- Strong knowledge of Laravel or Symfony\n- Experience with MySQL and REST APIs\n- Bachelor''s degree in Computer Science or related field', 'open', '2025-11-30', '2025-10-20 10:00:00'),

('job-002', 'emp-4609a1c9-profile', 'Frontend Developer', 'Mid Level', 'Join our team as a Frontend Developer. You will work on creating responsive and user-friendly web interfaces using modern JavaScript frameworks.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 80,000 - 120,000', '- 3+ years of frontend development experience\n- Proficiency in React or Vue.js\n- Strong HTML, CSS, and JavaScript skills\n- Experience with responsive design', 'open', '2025-11-25', '2025-10-21 11:00:00'),

('job-003', 'emp-4609a1c9-profile', 'DevOps Engineer', 'Senior Level', 'We are seeking a talented DevOps Engineer to help us build and maintain our cloud infrastructure. You will work with cutting-edge technologies and automation tools.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 150,000 - 220,000', '- 4+ years of DevOps experience\n- Strong knowledge of AWS or Azure\n- Experience with Docker and Kubernetes\n- Proficiency in CI/CD pipelines\n- Linux system administration skills', 'open', '2025-12-15', '2025-10-22 12:00:00'),

('job-004', 'emp-4609a1c9-profile', 'Junior Web Developer', 'Entry Level', 'Great opportunity for fresh graduates or junior developers to start their career in web development. You will work under senior developers and gain hands-on experience.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 45,000 - 65,000', '- Bachelor''s degree in Computer Science or related field\n- Basic knowledge of HTML, CSS, JavaScript\n- Familiarity with PHP or Python\n- Eager to learn and grow\n- Good problem-solving skills', 'open', '2025-11-20', '2025-10-23 13:00:00'),

('job-005', 'emp-4609a1c9-profile', 'UI/UX Designer', 'Mid Level', 'We are looking for a creative UI/UX Designer to create amazing user experiences. You will work closely with our development team to design intuitive and attractive interfaces.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 90,000 - 130,000', '- 3+ years of UI/UX design experience\n- Proficiency in Figma or Adobe XD\n- Strong portfolio demonstrating design skills\n- Understanding of user-centered design principles\n- Experience with prototyping tools', 'open', '2025-11-28', '2025-10-24 14:00:00');

-- Digital Ventures Jobs
INSERT INTO job_posts (uuid, employer_uuid, title, job_level, job_description, job_type, industry, location, salary_range, requirements_qualifications, status, application_deadline, created_at) VALUES
('job-006', 'emp-profile-002', 'Digital Marketing Specialist', 'Mid Level', 'We are looking for a Digital Marketing Specialist to manage our clients'' social media accounts and digital campaigns.', 'Full-time', 'Marketing', 'Kisumu, Kenya', 'KES 70,000 - 100,000', '- 2+ years of digital marketing experience\n- Strong knowledge of social media platforms\n- Experience with Google Ads and Facebook Ads\n- Excellent communication skills', 'closed', '2025-10-15', '2025-09-15 10:00:00'),

('job-007', 'emp-profile-002', 'Content Writer', 'Entry Level', 'Join our creative team as a Content Writer. You will create engaging content for various digital platforms.', 'Full-time', 'Marketing', 'Kisumu, Kenya', 'KES 40,000 - 60,000', '- Bachelor''s degree in Journalism, Communications or related field\n- Excellent writing and editing skills\n- Knowledge of SEO best practices\n- Creative thinking and attention to detail', 'open', '2025-11-30', '2025-10-18 09:00:00'),

('job-008', 'emp-profile-002', 'SEO Specialist', 'Mid Level', 'Seeking an experienced SEO Specialist to optimize our clients'' websites and improve search rankings.', 'Full-time', 'Marketing', 'Remote', 'KES 75,000 - 110,000', '- 3+ years of SEO experience\n- Proficiency in Google Analytics and Search Console\n- Knowledge of keyword research and link building\n- Experience with SEO tools like SEMrush or Ahrefs', 'open', '2025-12-10', '2025-10-19 10:30:00');

-- Safaricom Kenya Jobs
INSERT INTO job_posts (uuid, employer_uuid, title, job_level, job_description, job_type, industry, location, salary_range, requirements_qualifications, status, application_deadline, created_at) VALUES
('job-009', 'emp-profile-003', 'Network Engineer', 'Senior Level', 'Join our infrastructure team as a Senior Network Engineer. You will design, implement, and maintain our telecommunications network.', 'Full-time', 'Telecommunications', 'Nairobi, Kenya', 'KES 180,000 - 250,000', '- 5+ years of network engineering experience\n- CCNP or equivalent certification\n- Experience with Cisco, Juniper networking equipment\n- Strong understanding of TCP/IP, routing protocols\n- Excellent problem-solving skills', 'open', '2025-12-05', '2025-10-15 08:00:00'),

('job-010', 'emp-profile-003', 'Customer Service Representative', 'Entry Level', 'We are hiring customer service representatives to join our call center team. You will handle customer inquiries and provide excellent service.', 'Full-time', 'Telecommunications', 'Nairobi, Kenya', 'KES 35,000 - 50,000', '- Diploma or degree in any field\n- Excellent communication skills\n- Customer service experience is an advantage\n- Ability to work in shifts\n- Computer literacy', 'open', '2025-11-30', '2025-10-16 09:30:00'),

('job-011', 'emp-profile-003', 'Data Analyst', 'Mid Level', 'Looking for a Data Analyst to join our business intelligence team. You will analyze data and provide insights to drive business decisions.', 'Full-time', 'Telecommunications', 'Nairobi, Kenya', 'KES 100,000 - 140,000', '- 3+ years of data analysis experience\n- Proficiency in SQL and Python\n- Experience with data visualization tools (Tableau, Power BI)\n- Strong analytical and problem-solving skills\n- Bachelor''s degree in Statistics, Mathematics or related field', 'open', '2025-12-20', '2025-10-17 10:00:00'),

('job-012', 'emp-profile-003', 'Mobile App Developer', 'Mid Level', 'Join our mobile development team to build innovative mobile applications for our customers.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 120,000 - 170,000', '- 3+ years of mobile app development experience\n- Proficiency in iOS (Swift) or Android (Kotlin)\n- Experience with RESTful APIs\n- Published apps on App Store or Play Store\n- Strong UI/UX understanding', 'open', '2025-12-15', '2025-10-18 11:00:00');

-- Kenya Commercial Bank Jobs
INSERT INTO job_posts (uuid, employer_uuid, title, job_level, job_description, job_type, industry, location, salary_range, requirements_qualifications, status, application_deadline, created_at) VALUES
('job-013', 'emp-profile-004', 'Branch Manager', 'Senior Level', 'We are seeking an experienced Branch Manager to oversee daily operations of our Nairobi branch.', 'Full-time', 'Finance', 'Nairobi, Kenya', 'KES 200,000 - 280,000', '- 7+ years of banking experience\n- 3+ years in management role\n- Bachelor''s degree in Finance, Business or related field\n- Strong leadership and people management skills\n- Knowledge of banking regulations and compliance', 'open', '2025-11-25', '2025-10-12 08:30:00'),

('job-014', 'emp-profile-004', 'Credit Analyst', 'Mid Level', 'Join our credit department as a Credit Analyst. You will assess loan applications and conduct financial analysis.', 'Full-time', 'Finance', 'Nairobi, Kenya', 'KES 90,000 - 130,000', '- 3+ years of credit analysis experience\n- Bachelor''s degree in Finance or Accounting\n- Strong financial modeling skills\n- Knowledge of credit risk assessment\n- CPA or CFA certification is an advantage', 'open', '2025-12-10', '2025-10-13 09:00:00'),

('job-015', 'emp-profile-004', 'Customer Relationship Officer', 'Entry Level', 'We are hiring Customer Relationship Officers to serve our clients and promote our banking products.', 'Full-time', 'Finance', 'Mombasa, Kenya', 'KES 45,000 - 65,000', '- Diploma or Bachelor''s degree in Business or related field\n- Excellent communication and interpersonal skills\n- Sales-oriented mindset\n- Customer service experience is an advantage\n- Ability to meet sales targets', 'open', '2025-11-30', '2025-10-14 10:00:00'),

('job-016', 'emp-profile-004', 'IT Security Specialist', 'Senior Level', 'Looking for an IT Security Specialist to protect our systems and data from cyber threats.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 160,000 - 220,000', '- 5+ years of IT security experience\n- Knowledge of security frameworks (ISO 27001, NIST)\n- Experience with SIEM, firewalls, and intrusion detection systems\n- Security certifications (CISSP, CEH) preferred\n- Bachelor''s degree in Computer Science or related field', 'open', '2025-12-20', '2025-10-15 11:30:00');

-- East Africa Breweries Jobs
INSERT INTO job_posts (uuid, employer_uuid, title, job_level, job_description, job_type, industry, location, salary_range, requirements_qualifications, status, application_deadline, created_at) VALUES
('job-017', 'emp-profile-005', 'Production Supervisor', 'Mid Level', 'We are seeking a Production Supervisor to oversee manufacturing operations at our Nairobi plant.', 'Full-time', 'Manufacturing', 'Nairobi, Kenya', 'KES 110,000 - 150,000', '- 4+ years of manufacturing experience\n- 2+ years in supervisory role\n- Knowledge of production planning and quality control\n- Strong leadership skills\n- Degree in Engineering or related field', 'open', '2025-12-05', '2025-10-13 09:00:00'),

('job-018', 'emp-profile-005', 'Sales Representative', 'Entry Level', 'Join our sales team to distribute our products to retailers and wholesalers across Kenya.', 'Full-time', 'Sales', 'Kisumu, Kenya', 'KES 40,000 - 60,000 + Commission', '- Diploma in Sales, Marketing or Business\n- Valid driving license\n- Good communication and negotiation skills\n- Sales experience is an advantage\n- Willingness to travel', 'open', '2025-11-30', '2025-10-14 10:30:00'),

('job-019', 'emp-profile-005', 'Quality Assurance Manager', 'Senior Level', 'Looking for a QA Manager to ensure our products meet quality standards and regulatory requirements.', 'Full-time', 'Manufacturing', 'Nairobi, Kenya', 'KES 180,000 - 240,000', '- 6+ years of quality assurance experience in FMCG\n- Knowledge of ISO standards and GMP\n- Experience in laboratory management\n- Strong analytical skills\n- Bachelor''s degree in Food Science, Chemistry or related field', 'open', '2025-12-15', '2025-10-15 11:00:00'),

('job-020', 'emp-profile-005', 'Marketing Manager', 'Senior Level', 'We are hiring a Marketing Manager to develop and execute marketing strategies for our brand portfolio.', 'Full-time', 'Marketing', 'Nairobi, Kenya', 'KES 190,000 - 260,000', '- 7+ years of marketing experience in FMCG\n- Proven track record in brand management\n- Strong strategic thinking and analytical skills\n- Experience with digital marketing\n- MBA or Master''s degree in Marketing preferred', 'closed', '2025-10-10', '2025-09-20 08:00:00');

-- ========================================
-- 8. APPLICATIONS (15 applications with different statuses)
-- ========================================

INSERT INTO applications (uuid, job_uuid, job_seeker_uuid, cover_letter, resume_file, status, applied_at, reviewed_at) VALUES
-- Bonface's application (from seed data)
('app-001', 'job-002', 'b4b1b964-31f8-4835-a4b7-5c06c33f2c57', 'Dear Hiring Manager,\n\nI am writing to express my strong interest in the Frontend Developer position at Tech Innovations Kenya. With my experience in JavaScript and modern frameworks, I believe I would be a great fit for your team.\n\nI have worked on several projects using React and have a solid understanding of responsive design principles. I am passionate about creating user-friendly interfaces and would love to contribute to your company''s success.\n\nThank you for considering my application.\n\nBest regards,\nBonface Njuguna', NULL, 'pending', '2025-10-26 15:00:00', NULL),

-- Jane's applications
('app-002', 'job-005', 'js-profile-002', 'I am excited to apply for the UI/UX Designer position. My portfolio demonstrates my ability to create user-centered designs that balance aesthetics with functionality.', NULL, 'reviewed', '2025-10-25 10:30:00', '2025-10-26 09:00:00'),

('app-003', 'job-007', 'js-profile-002', 'My design background and content creation skills make me a perfect fit for the Content Writer role at Digital Ventures.', NULL, 'accepted', '2025-10-24 14:20:00', '2025-10-25 16:30:00'),

-- Michael's applications
('app-004', 'job-011', 'js-profile-003', 'As a Data Analyst with extensive experience in Python and SQL, I am confident I can contribute valuable insights to Safaricom''s business intelligence team.', NULL, 'pending', '2025-10-26 11:45:00', NULL),

('app-005', 'job-014', 'js-profile-003', 'My analytical skills and financial background align perfectly with the Credit Analyst position at KCB.', NULL, 'reviewed', '2025-10-25 09:15:00', '2025-10-26 10:30:00'),

-- Sarah's applications
('app-006', 'job-006', 'js-profile-004', 'I am passionate about digital marketing and have successfully managed social media campaigns that increased engagement by 150%.', NULL, 'rejected', '2025-10-18 13:00:00', '2025-10-19 14:00:00'),

('app-007', 'job-008', 'js-profile-004', 'With my SEO expertise and proven track record of improving search rankings, I would be an asset to Digital Ventures.', NULL, 'pending', '2025-10-26 16:30:00', NULL),

-- David's applications
('app-008', 'job-013', 'js-profile-005', 'With over 8 years in banking and strong leadership skills, I am ready to take on the Branch Manager role at KCB.', NULL, 'reviewed', '2025-10-24 08:30:00', '2025-10-25 11:00:00'),

('app-009', 'job-014', 'js-profile-005', 'My CPA certification and credit analysis experience make me an ideal candidate for this position.', NULL, 'pending', '2025-10-26 10:00:00', NULL),

-- Grace's application
('app-010', 'job-010', 'js-profile-006', 'My nursing background has equipped me with excellent communication and patient care skills that translate well to customer service.', NULL, 'accepted', '2025-10-23 12:00:00', '2025-10-24 15:00:00'),

-- Peter's application
('app-011', 'job-017', 'js-profile-007', 'As a mechanical engineer with experience in industrial automation, I am well-suited for the Production Supervisor role.', NULL, 'pending', '2025-10-26 14:00:00', NULL),

-- Mercy's applications
('app-012', 'job-015', 'js-profile-008', 'I am eager to start my career in banking and am confident my HR background will help me excel as a Customer Relationship Officer.', NULL, 'reviewed', '2025-10-25 11:30:00', '2025-10-26 13:00:00'),

('app-013', 'job-010', 'js-profile-008', 'My excellent communication skills and enthusiasm make me a great fit for the Customer Service Representative role.', NULL, 'pending', '2025-10-26 09:00:00', NULL),

-- James's applications
('app-014', 'job-018', 'js-profile-009', 'With my proven sales track record and willingness to travel, I am excited about the Sales Representative opportunity at EABL.', NULL, 'accepted', '2025-10-22 15:45:00', '2025-10-23 10:00:00'),

('app-015', 'job-015', 'js-profile-009', 'My sales experience and customer-oriented approach align perfectly with the Customer Relationship Officer position.', NULL, 'pending', '2025-10-26 13:20:00', NULL);

-- ========================================
-- 9. SAVED JOBS (10 saved jobs)
-- ========================================

INSERT INTO saved_jobs (uuid, job_uuid, job_seeker_uuid, saved_at) VALUES
-- Bonface's saved jobs
('saved-001', 'job-001', 'b4b1b964-31f8-4835-a4b7-5c06c33f2c57', '2025-10-25 08:30:00'),
('saved-002', 'job-003', 'b4b1b964-31f8-4835-a4b7-5c06c33f2c57', '2025-10-25 09:15:00'),
('saved-003', 'job-012', 'b4b1b964-31f8-4835-a4b7-5c06c33f2c57', '2025-10-26 10:00:00'),

-- Jane's saved jobs
('saved-004', 'job-001', 'js-profile-002', '2025-10-24 11:20:00'),
('saved-005', 'job-008', 'js-profile-002', '2025-10-25 14:30:00'),

-- Michael's saved jobs
('saved-006', 'job-009', 'js-profile-003', '2025-10-25 16:00:00'),
('saved-007', 'job-016', 'js-profile-003', '2025-10-26 08:45:00'),

-- Sarah's saved jobs
('saved-008', 'job-020', 'js-profile-004', '2025-10-24 09:00:00'),

-- David's saved jobs
('saved-009', 'job-019', 'js-profile-005', '2025-10-25 10:30:00'),

-- Peter's saved job
('saved-010', 'job-017', 'js-profile-007', '2025-10-26 07:15:00');

-- ========================================
-- SUMMARY OF SEEDED DATA
-- ========================================
-- Users: 17 total (2 admins, 5 employers, 10 job seekers)
-- Admin Profiles: 2
-- Job Seekers: 10 (9 with complete profiles, 1 incomplete)
-- Employers: 5
-- Job Posts: 20 (18 open, 2 closed)
-- Applications: 15 (8 pending, 3 reviewed, 3 accepted, 1 rejected)
-- Saved Jobs: 10
-- Email Verifications: 4 (3 used, 1 pending)
-- Password Resets: 2 (1 used, 1 pending)
-- ========================================

-- Default password for all test users: 'password'