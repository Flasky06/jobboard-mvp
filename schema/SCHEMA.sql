

--
-- Database: `job_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_profiles`
--

CREATE TABLE `admin_profiles` (
  `uuid` char(36) NOT NULL,
  `user_uuid` char(36) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_profiles`
--

INSERT INTO `admin_profiles` (`uuid`, `user_uuid`, `full_name`, `contact_number`, `created_at`, `updated_at`) VALUES
('admin-profile-001', 'admin-uuid-001', 'John Admin', '0700123456', '2025-09-30 21:00:00', '2025-10-26 13:51:40'),
('admin-profile-002', 'admin-uuid-002', 'Mary SuperAdmin', '0711234567', '2025-09-30 21:00:00', '2025-10-26 13:51:40');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `uuid` char(36) NOT NULL,
  `job_uuid` char(36) NOT NULL,
  `job_seeker_uuid` char(36) NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `resume_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','reviewed') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`uuid`, `job_uuid`, `job_seeker_uuid`, `cover_letter`, `resume_file`, `status`, `applied_at`, `reviewed_at`) VALUES
('app-001', 'job-002', 'b4b1b964-31f8-4835-a4b7-5c06c33f2c57', 'Dear Hiring Manager,\n\nI am writing to express my strong interest in the Frontend Developer position at Tech Innovations Kenya. With my experience in JavaScript and modern frameworks, I believe I would be a great fit for your team.\n\nI have worked on several projects using React and have a solid understanding of responsive design principles. I am passionate about creating user-friendly interfaces and would love to contribute to your company\'s success.\n\nThank you for considering my application.\n\nBest regards,\nBonface Njuguna', NULL, 'pending', '2025-10-26 12:00:00', NULL),
('app-002', 'job-005', 'js-profile-002', 'I am excited to apply for the UI/UX Designer position. My portfolio demonstrates my ability to create user-centered designs that balance aesthetics with functionality.', NULL, 'reviewed', '2025-10-25 07:30:00', '2025-10-26 06:00:00'),
('app-003', 'job-007', 'js-profile-002', 'My design background and content creation skills make me a perfect fit for the Content Writer role at Digital Ventures.', NULL, 'accepted', '2025-10-24 11:20:00', '2025-10-25 13:30:00'),
('app-004', 'job-011', 'js-profile-003', 'As a Data Analyst with extensive experience in Python and SQL, I am confident I can contribute valuable insights to Safaricom\'s business intelligence team.', NULL, 'pending', '2025-10-26 08:45:00', NULL),
('app-005', 'job-014', 'js-profile-003', 'My analytical skills and financial background align perfectly with the Credit Analyst position at KCB.', NULL, 'reviewed', '2025-10-25 06:15:00', '2025-10-26 07:30:00'),
('app-006', 'job-006', 'js-profile-004', 'I am passionate about digital marketing and have successfully managed social media campaigns that increased engagement by 150%.', NULL, 'rejected', '2025-10-18 10:00:00', '2025-10-19 11:00:00'),
('app-007', 'job-008', 'js-profile-004', 'With my SEO expertise and proven track record of improving search rankings, I would be an asset to Digital Ventures.', NULL, 'pending', '2025-10-26 13:30:00', NULL),
('app-008', 'job-013', 'js-profile-005', 'With over 8 years in banking and strong leadership skills, I am ready to take on the Branch Manager role at KCB.', NULL, 'reviewed', '2025-10-24 05:30:00', '2025-10-25 08:00:00'),
('app-009', 'job-014', 'js-profile-005', 'My CPA certification and credit analysis experience make me an ideal candidate for this position.', NULL, 'pending', '2025-10-26 07:00:00', NULL),
('app-010', 'job-010', 'js-profile-006', 'My nursing background has equipped me with excellent communication and patient care skills that translate well to customer service.', NULL, 'accepted', '2025-10-23 09:00:00', '2025-10-24 12:00:00'),
('app-011', 'job-017', 'js-profile-007', 'As a mechanical engineer with experience in industrial automation, I am well-suited for the Production Supervisor role.', NULL, 'pending', '2025-10-26 11:00:00', NULL),
('app-012', 'job-015', 'js-profile-008', 'I am eager to start my career in banking and am confident my HR background will help me excel as a Customer Relationship Officer.', NULL, 'reviewed', '2025-10-25 08:30:00', '2025-10-26 10:00:00'),
('app-013', 'job-010', 'js-profile-008', 'My excellent communication skills and enthusiasm make me a great fit for the Customer Service Representative role.', NULL, 'pending', '2025-10-26 06:00:00', NULL),
('app-014', 'job-018', 'js-profile-009', 'With my proven sales track record and willingness to travel, I am excited about the Sales Representative opportunity at EABL.', NULL, 'accepted', '2025-10-22 12:45:00', '2025-10-23 07:00:00'),
('app-015', 'job-015', 'js-profile-009', 'My sales experience and customer-oriented approach align perfectly with the Customer Relationship Officer position.', NULL, 'pending', '2025-10-26 10:20:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `uuid` char(36) NOT NULL,
  `user_uuid` char(36) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_verifications`
--

INSERT INTO `email_verifications` (`uuid`, `user_uuid`, `token`, `expires_at`, `is_used`, `verified_at`, `created_at`) VALUES
('285316ba-a959-49e7-b3ed-d65c76805266', '74ba64a8-091d-4c4c-ab44-408dc62c4bd9', '9064dceb239e8a92e5ad6d52908c6f7267d39500f744bc16fe9c880df2a4e0b3', '2025-10-27 06:38:42', 1, '2025-10-26 03:00:00', '2025-10-26 02:38:42'),
('7d38331f-9d1b-4306-9a21-2c3cbb430677', '4609a1c9-9755-4b93-b242-4b3113119340', '498a2d429192d4419f4e11ef1d3a997035f018a9e0e3b9d92792cf870e9f724e', '2025-10-27 09:57:51', 1, '2025-10-26 06:00:00', '2025-10-26 05:57:51'),
('ev-uuid-003', 'js-uuid-002', 'abc123token456def789ghi012jkl345mno678pqr901stu234vwx567yza890bcd', '2025-10-16 09:00:00', 1, '2025-10-15 06:30:00', '2025-10-15 06:00:00'),
('ev-uuid-004', 'js-uuid-010', 'xyz987token654wvu321tsr098qpo765nml432kji109hgf876edc543baz210yxw', '2025-10-24 10:20:00', 0, NULL, '2025-10-23 07:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `employers`
--

CREATE TABLE `employers` (
  `uuid` char(36) NOT NULL,
  `user_uuid` char(36) NOT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `about_company` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employers`
--

INSERT INTO `employers` (`uuid`, `user_uuid`, `company_name`, `contact_number`, `location`, `industry`, `website`, `company_logo`, `about_company`, `created_at`, `updated_at`) VALUES
('emp-4609a1c9-profile', '4609a1c9-9755-4b93-b242-4b3113119340', 'Tech Innovations Kenya', '0717299106', 'Nairobi, Kenya', 'Technology', 'https://techinnovations.co.ke', NULL, 'Leading technology company specializing in software development and digital transformation solutions.', '2025-10-26 05:58:00', '2025-10-26 13:51:40'),
('emp-profile-002', 'emp-uuid-002', 'Digital Ventures Ltd', '0733445566', 'Kisumu, Kenya', 'Digital Marketing', 'https://digitalventures.co.ke', NULL, 'Full-service digital marketing agency helping businesses grow their online presence.', '2025-10-10 07:15:00', '2025-10-26 13:51:40'),
('emp-profile-003', 'emp-uuid-003', 'Safaricom Kenya', '0722000000', 'Nairobi, Kenya', 'Telecommunications', 'https://safaricom.co.ke', NULL, 'Leading telecommunications company in East Africa providing mobile, internet, and financial services.', '2025-10-11 08:30:00', '2025-10-26 13:51:40'),
('emp-profile-004', 'emp-uuid-004', 'Kenya Commercial Bank', '0711123456', 'Nairobi, Kenya', 'Finance', 'https://kcbgroup.com', NULL, 'Leading financial services provider offering banking, insurance, and investment solutions across East Africa.', '2025-10-12 09:45:00', '2025-10-26 13:51:40'),
('emp-profile-005', 'emp-uuid-005', 'East Africa Breweries', '0700987654', 'Nairobi, Kenya', 'Manufacturing', 'https://eabl.com', NULL, 'Leading beverage alcohol company in East Africa with a diverse portfolio of beer, spirits and non-alcoholic brands.', '2025-10-13 10:15:00', '2025-10-26 13:51:40');

-- --------------------------------------------------------

--
-- Table structure for table `job_posts`
--

CREATE TABLE `job_posts` (
  `uuid` char(36) NOT NULL,
  `employer_uuid` char(36) NOT NULL,
  `title` varchar(150) NOT NULL,
  `job_level` varchar(100) DEFAULT NULL,
  `job_description` text NOT NULL,
  `job_type` varchar(100) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `salary_range` varchar(100) DEFAULT NULL,
  `additional_information` text DEFAULT NULL,
  `requirements_qualifications` text DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `application_deadline` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_posts`
--

INSERT INTO `job_posts` (`uuid`, `employer_uuid`, `title`, `job_level`, `job_description`, `job_type`, `industry`, `location`, `salary_range`, `additional_information`, `requirements_qualifications`, `status`, `application_deadline`, `created_at`, `updated_at`) VALUES
('1963b302-bb8d-419e-9624-c8c5adce50b1', 'emp-4609a1c9-profile', 'Full Stack Engineer', 'Mid Level', 'We are seeking a passionate Full Stack Engineer to join our dynamic team. You’ll be responsible for developing scalable web applications, designing efficient APIs, and collaborating with cross-functional teams to deliver high-quality products.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KSh 150,000 – 200,000', 'We offer a collaborative work culture, career growth opportunities, and regular team-building activities. Health insurance and remote work options are included.', 'Bachelor’s degree in Computer Science or related field\r\n3+ years of experience with JavaScript, Node.js, and React\r\nStrong backend experience with Java/Spring Boot or Python/Django\r\nKnowledge of REST APIs and MySQL/PostgreSQL\r\nExperience with Docker and CI/CD pipelines\r\nExcellent problem-solving and communication skills', 'open', '2025-10-31', '2025-10-27 17:08:04', '2025-10-27 17:08:04'),
('44ab93a3-0783-49b8-9bd3-8ddb881efdb7', 'emp-4609a1c9-profile', 'Full Stack Engineer', 'Mid Level', 'We are seeking a passionate Full Stack Engineer to join our dynamic team. You’ll be responsible for developing scalable web applications, designing efficient APIs, and collaborating with cross-functional teams to deliver high-quality products.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KSh 150,000 – 200,000', 'We offer a collaborative work culture, career growth opportunities, and regular team-building activities. Health insurance and remote work options are included.', 'Bachelor’s degree in Computer Science or related field\r\n3+ years of experience with JavaScript, Node.js, and React\r\nStrong backend experience with Java/Spring Boot or Python/Django\r\nKnowledge of REST APIs and MySQL/PostgreSQL\r\nExperience with Docker and CI/CD pipelines\r\nExcellent problem-solving and communication skills', 'open', '2025-10-31', '2025-10-27 17:12:21', '2025-10-27 17:12:21'),
('7360dd77-152c-44a1-a71f-1bba91f43c8c', 'emp-4609a1c9-profile', 'Full Stack Engineer', 'Mid Level', 'We are seeking a passionate Full Stack Engineer to join our dynamic team. You’ll be responsible for developing scalable web applications, designing efficient APIs, and collaborating with cross-functional teams to deliver high-quality products.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KSh 150,000 – 200,000', 'We offer a collaborative work culture, career growth opportunities, and regular team-building activities. Health insurance and remote work options are included.', 'Bachelor’s degree in Computer Science or related field\r\n3+ years of experience with JavaScript, Node.js, and React\r\nStrong backend experience with Java/Spring Boot or Python/Django\r\nKnowledge of REST APIs and MySQL/PostgreSQL\r\nExperience with Docker and CI/CD pipelines\r\nExcellent problem-solving and communication skills', 'open', '2025-10-31', '2025-10-27 17:08:12', '2025-10-27 17:08:12'),
('job-001', 'emp-4609a1c9-profile', 'Senior PHP Developer', 'Senior Level', 'We are looking for an experienced PHP developer to join our growing team. You will be responsible for developing and maintaining web applications using modern PHP frameworks.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 120,000 - 180,000', NULL, '- 5+ years of PHP development experience\n- Strong knowledge of Laravel or Symfony\n- Experience with MySQL and REST APIs\n- Bachelor\'s degree in Computer Science or related field', 'open', '2025-11-30', '2025-10-20 07:00:00', '2025-10-26 13:51:40'),
('job-002', 'emp-4609a1c9-profile', 'Frontend Developer', 'Mid Level', 'Join our team as a Frontend Developer. You will work on creating responsive and user-friendly web interfaces using modern JavaScript frameworks.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 80,000 - 120,000', NULL, '- 3+ years of frontend development experience\n- Proficiency in React or Vue.js\n- Strong HTML, CSS, and JavaScript skills\n- Experience with responsive design', 'open', '2025-11-25', '2025-10-21 08:00:00', '2025-10-26 13:51:40'),
('job-003', 'emp-4609a1c9-profile', 'DevOps Engineer', 'Senior Level', 'We are seeking a talented DevOps Engineer to help us build and maintain our cloud infrastructure. You will work with cutting-edge technologies and automation tools.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 150,000 - 220,000', NULL, '- 4+ years of DevOps experience\n- Strong knowledge of AWS or Azure\n- Experience with Docker and Kubernetes\n- Proficiency in CI/CD pipelines\n- Linux system administration skills', 'open', '2025-12-15', '2025-10-22 09:00:00', '2025-10-26 13:51:40'),
('job-004', 'emp-4609a1c9-profile', 'Junior Web Developer', 'Entry Level', 'Great opportunity for fresh graduates or junior developers to start their career in web development. You will work under senior developers and gain hands-on experience.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 45,000 - 65,000', NULL, '- Bachelor\'s degree in Computer Science or related field\n- Basic knowledge of HTML, CSS, JavaScript\n- Familiarity with PHP or Python\n- Eager to learn and grow\n- Good problem-solving skills', 'open', '2025-11-20', '2025-10-23 10:00:00', '2025-10-26 13:51:40'),
('job-005', 'emp-4609a1c9-profile', 'UI/UX Designer', 'Mid Level', 'We are looking for a creative UI/UX Designer to create amazing user experiences. You will work closely with our development team to design intuitive and attractive interfaces.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 90,000 - 130,000', NULL, '- 3+ years of UI/UX design experience\n- Proficiency in Figma or Adobe XD\n- Strong portfolio demonstrating design skills\n- Understanding of user-centered design principles\n- Experience with prototyping tools', 'open', '2025-11-28', '2025-10-24 11:00:00', '2025-10-26 13:51:40'),
('job-006', 'emp-profile-002', 'Digital Marketing Specialist', 'Mid Level', 'We are looking for a Digital Marketing Specialist to manage our clients\' social media accounts and digital campaigns.', 'Full-time', 'Marketing', 'Kisumu, Kenya', 'KES 70,000 - 100,000', NULL, '- 2+ years of digital marketing experience\n- Strong knowledge of social media platforms\n- Experience with Google Ads and Facebook Ads\n- Excellent communication skills', 'closed', '2025-10-15', '2025-09-15 07:00:00', '2025-10-26 13:51:40'),
('job-007', 'emp-profile-002', 'Content Writer', 'Entry Level', 'Join our creative team as a Content Writer. You will create engaging content for various digital platforms.', 'Full-time', 'Marketing', 'Kisumu, Kenya', 'KES 40,000 - 60,000', NULL, '- Bachelor\'s degree in Journalism, Communications or related field\n- Excellent writing and editing skills\n- Knowledge of SEO best practices\n- Creative thinking and attention to detail', 'open', '2025-11-30', '2025-10-18 06:00:00', '2025-10-26 13:51:40'),
('job-008', 'emp-profile-002', 'SEO Specialist', 'Mid Level', 'Seeking an experienced SEO Specialist to optimize our clients\' websites and improve search rankings.', 'Full-time', 'Marketing', 'Remote', 'KES 75,000 - 110,000', NULL, '- 3+ years of SEO experience\n- Proficiency in Google Analytics and Search Console\n- Knowledge of keyword research and link building\n- Experience with SEO tools like SEMrush or Ahrefs', 'open', '2025-12-10', '2025-10-19 07:30:00', '2025-10-26 13:51:40'),
('job-009', 'emp-profile-003', 'Network Engineer', 'Senior Level', 'Join our infrastructure team as a Senior Network Engineer. You will design, implement, and maintain our telecommunications network.', 'Full-time', 'Telecommunications', 'Nairobi, Kenya', 'KES 180,000 - 250,000', NULL, '- 5+ years of network engineering experience\n- CCNP or equivalent certification\n- Experience with Cisco, Juniper networking equipment\n- Strong understanding of TCP/IP, routing protocols\n- Excellent problem-solving skills', 'open', '2025-12-05', '2025-10-15 05:00:00', '2025-10-26 13:51:40'),
('job-010', 'emp-profile-003', 'Customer Service Representative', 'Entry Level', 'We are hiring customer service representatives to join our call center team. You will handle customer inquiries and provide excellent service.', 'Full-time', 'Telecommunications', 'Nairobi, Kenya', 'KES 35,000 - 50,000', NULL, '- Diploma or degree in any field\n- Excellent communication skills\n- Customer service experience is an advantage\n- Ability to work in shifts\n- Computer literacy', 'open', '2025-11-30', '2025-10-16 06:30:00', '2025-10-26 13:51:40'),
('job-011', 'emp-profile-003', 'Data Analyst', 'Mid Level', 'Looking for a Data Analyst to join our business intelligence team. You will analyze data and provide insights to drive business decisions.', 'Full-time', 'Telecommunications', 'Nairobi, Kenya', 'KES 100,000 - 140,000', NULL, '- 3+ years of data analysis experience\n- Proficiency in SQL and Python\n- Experience with data visualization tools (Tableau, Power BI)\n- Strong analytical and problem-solving skills\n- Bachelor\'s degree in Statistics, Mathematics or related field', 'open', '2025-12-20', '2025-10-17 07:00:00', '2025-10-26 13:51:40'),
('job-012', 'emp-profile-003', 'Mobile App Developer', 'Mid Level', 'Join our mobile development team to build innovative mobile applications for our customers.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 120,000 - 170,000', NULL, '- 3+ years of mobile app development experience\n- Proficiency in iOS (Swift) or Android (Kotlin)\n- Experience with RESTful APIs\n- Published apps on App Store or Play Store\n- Strong UI/UX understanding', 'open', '2025-12-15', '2025-10-18 08:00:00', '2025-10-26 13:51:40'),
('job-013', 'emp-profile-004', 'Branch Manager', 'Senior Level', 'We are seeking an experienced Branch Manager to oversee daily operations of our Nairobi branch.', 'Full-time', 'Finance', 'Nairobi, Kenya', 'KES 200,000 - 280,000', NULL, '- 7+ years of banking experience\n- 3+ years in management role\n- Bachelor\'s degree in Finance, Business or related field\n- Strong leadership and people management skills\n- Knowledge of banking regulations and compliance', 'open', '2025-11-25', '2025-10-12 05:30:00', '2025-10-26 13:51:40'),
('job-014', 'emp-profile-004', 'Credit Analyst', 'Mid Level', 'Join our credit department as a Credit Analyst. You will assess loan applications and conduct financial analysis.', 'Full-time', 'Finance', 'Nairobi, Kenya', 'KES 90,000 - 130,000', NULL, '- 3+ years of credit analysis experience\n- Bachelor\'s degree in Finance or Accounting\n- Strong financial modeling skills\n- Knowledge of credit risk assessment\n- CPA or CFA certification is an advantage', 'open', '2025-12-10', '2025-10-13 06:00:00', '2025-10-26 13:51:40'),
('job-015', 'emp-profile-004', 'Customer Relationship Officer', 'Entry Level', 'We are hiring Customer Relationship Officers to serve our clients and promote our banking products.', 'Full-time', 'Finance', 'Mombasa, Kenya', 'KES 45,000 - 65,000', NULL, '- Diploma or Bachelor\'s degree in Business or related field\n- Excellent communication and interpersonal skills\n- Sales-oriented mindset\n- Customer service experience is an advantage\n- Ability to meet sales targets', 'open', '2025-11-30', '2025-10-14 07:00:00', '2025-10-26 13:51:40'),
('job-016', 'emp-profile-004', 'IT Security Specialist', 'Senior Level', 'Looking for an IT Security Specialist to protect our systems and data from cyber threats.', 'Full-time', 'Technology', 'Nairobi, Kenya', 'KES 160,000 - 220,000', NULL, '- 5+ years of IT security experience\n- Knowledge of security frameworks (ISO 27001, NIST)\n- Experience with SIEM, firewalls, and intrusion detection systems\n- Security certifications (CISSP, CEH) preferred\n- Bachelor\'s degree in Computer Science or related field', 'open', '2025-12-20', '2025-10-15 08:30:00', '2025-10-26 13:51:40'),
('job-017', 'emp-profile-005', 'Production Supervisor', 'Mid Level', 'We are seeking a Production Supervisor to oversee manufacturing operations at our Nairobi plant.', 'Full-time', 'Manufacturing', 'Nairobi, Kenya', 'KES 110,000 - 150,000', NULL, '- 4+ years of manufacturing experience\n- 2+ years in supervisory role\n- Knowledge of production planning and quality control\n- Strong leadership skills\n- Degree in Engineering or related field', 'open', '2025-12-05', '2025-10-13 06:00:00', '2025-10-26 13:51:40'),
('job-018', 'emp-profile-005', 'Sales Representative', 'Entry Level', 'Join our sales team to distribute our products to retailers and wholesalers across Kenya.', 'Full-time', 'Sales', 'Kisumu, Kenya', 'KES 40,000 - 60,000 + Commission', NULL, '- Diploma in Sales, Marketing or Business\n- Valid driving license\n- Good communication and negotiation skills\n- Sales experience is an advantage\n- Willingness to travel', 'open', '2025-11-30', '2025-10-14 07:30:00', '2025-10-26 13:51:40'),
('job-019', 'emp-profile-005', 'Quality Assurance Manager', 'Senior Level', 'Looking for a QA Manager to ensure our products meet quality standards and regulatory requirements.', 'Full-time', 'Manufacturing', 'Nairobi, Kenya', 'KES 180,000 - 240,000', NULL, '- 6+ years of quality assurance experience in FMCG\n- Knowledge of ISO standards and GMP\n- Experience in laboratory management\n- Strong analytical skills\n- Bachelor\'s degree in Food Science, Chemistry or related field', 'open', '2025-12-15', '2025-10-15 08:00:00', '2025-10-26 13:51:40'),
('job-020', 'emp-profile-005', 'Marketing Manager', 'Senior Level', 'We are hiring a Marketing Manager to develop and execute marketing strategies for our brand portfolio.', 'Full-time', 'Marketing', 'Nairobi, Kenya', 'KES 190,000 - 260,000', NULL, '- 7+ years of marketing experience in FMCG\n- Proven track record in brand management\n- Strong strategic thinking and analytical skills\n- Experience with digital marketing\n- MBA or Master\'s degree in Marketing preferred', 'closed', '2025-10-10', '2025-09-20 05:00:00', '2025-10-26 13:51:40');

-- --------------------------------------------------------

--
-- Table structure for table `job_seekers`
--

CREATE TABLE `job_seekers` (
  `uuid` char(36) NOT NULL,
  `user_uuid` char(36) NOT NULL,
  `fullName` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `professional_title` varchar(100) DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `education` text DEFAULT NULL,
  `resume_file` varchar(255) DEFAULT NULL,
  `profile_completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_seekers`
--

INSERT INTO `job_seekers` (`uuid`, `user_uuid`, `fullName`, `phone`, `gender`, `dob`, `location`, `bio`, `professional_title`, `skills`, `education`, `resume_file`, `profile_completed`, `created_at`, `updated_at`) VALUES
('b4b1b964-31f8-4835-a4b7-5c06c33f2c57', '74ba64a8-091d-4c4c-ab44-408dc62c4bd9', 'Bonface Njuguna', '0717299106', 'male', '1998-03-15', 'Nairobi', 'Passionate software developer with experience in building scalable web applications.', 'Software Developer', 'PHP, JavaScript, MySQL, HTML, CSS, Laravel, React', 'Bachelor of Science in Computer Science\nUniversity of Nairobi, 2023', NULL, 1, '2025-10-26 05:52:50', '2025-10-26 13:51:40'),
('js-profile-002', 'js-uuid-002', 'Jane Doe', '0722334455', 'female', '1995-05-15', 'Mombasa, Kenya', 'Creative UI/UX designer with a passion for creating beautiful and functional user interfaces.', 'UI/UX Designer', 'Figma, Adobe XD, Sketch, Prototyping, User Research', 'Bachelor of Arts in Design\nStrathmore University, 2022', NULL, 1, '2025-10-15 06:30:00', '2025-10-26 13:51:40'),
('js-profile-003', 'js-uuid-003', 'Michael Ochieng', '0733445566', 'male', '1992-08-22', 'Kisumu, Kenya', 'Experienced data analyst with strong analytical and problem-solving skills.', 'Data Analyst', 'Python, SQL, Tableau, Excel, Power BI, Statistical Analysis', 'Master of Science in Data Science\nJomo Kenyatta University, 2020', NULL, 1, '2025-10-16 08:00:00', '2025-10-26 13:51:40'),
('js-profile-004', 'js-uuid-004', 'Sarah Wanjiku', '0744556677', 'female', '1997-12-10', 'Nakuru, Kenya', 'Marketing professional with expertise in digital marketing and brand management.', 'Digital Marketing Specialist', 'SEO, SEM, Social Media Marketing, Content Marketing, Google Analytics', 'Bachelor of Commerce in Marketing\nKenyatta University, 2021', NULL, 1, '2025-10-17 08:45:00', '2025-10-26 13:51:40'),
('js-profile-005', 'js-uuid-005', 'David Kimani', '0755667788', 'male', '1990-04-18', 'Nairobi, Kenya', 'Senior accountant with 8+ years of experience in financial management and auditing.', 'Senior Accountant', 'Financial Reporting, Auditing, Tax Planning, QuickBooks, SAP', 'Bachelor of Commerce in Accounting\nUniversity of Nairobi, 2015\nCPA (K) Certified', NULL, 1, '2025-10-18 11:50:00', '2025-10-26 13:51:40'),
('js-profile-006', 'js-uuid-006', 'Grace Akinyi', '0766778899', 'female', '1994-11-25', 'Eldoret, Kenya', 'Registered nurse with experience in critical care and patient management.', 'Registered Nurse', 'Patient Care, Emergency Response, Medical Documentation, Healthcare Management', 'Bachelor of Science in Nursing\nMoi University, 2019', NULL, 1, '2025-10-19 06:15:00', '2025-10-26 13:51:40'),
('js-profile-007', 'js-uuid-007', 'Peter Mwangi', '0777889900', 'male', '1996-02-14', 'Thika, Kenya', 'Mechanical engineer specializing in industrial automation and maintenance.', 'Mechanical Engineer', 'AutoCAD, SolidWorks, PLC Programming, Maintenance, Project Management', 'Bachelor of Engineering in Mechanical Engineering\nTechnical University of Kenya, 2020', NULL, 1, '2025-10-20 11:00:00', '2025-10-26 13:51:40'),
('js-profile-008', 'js-uuid-008', 'Mercy Wambui', '0788990011', 'female', '1999-07-08', 'Nyeri, Kenya', 'Recent graduate seeking opportunities in human resource management.', 'HR Assistant', 'Recruitment, Employee Relations, HR Policies, MS Office', 'Bachelor of Arts in Human Resource Management\nMount Kenya University, 2024', NULL, 1, '2025-10-21 06:40:00', '2025-10-26 13:51:40'),
('js-profile-009', 'js-uuid-009', 'James Otieno', '0799001122', 'male', '1993-09-30', 'Mombasa, Kenya', 'Sales professional with proven track record in B2B and B2C sales.', 'Sales Executive', 'Sales Strategy, Client Relations, Negotiation, CRM Software, Market Research', 'Diploma in Sales and Marketing\nKenya Institute of Management, 2017', NULL, 1, '2025-10-22 12:30:00', '2025-10-26 13:51:40'),
('js-profile-010', 'js-uuid-010', 'Lucy Njeri', '0700112233', 'female', '2000-01-20', 'Nairobi, Kenya', 'Aspiring graphic designer looking for entry-level opportunities.', 'Graphic Designer', 'Adobe Photoshop, Illustrator, InDesign, Canva', 'Certificate in Graphic Design\nNairobi Design School, 2023', NULL, 0, '2025-10-23 07:50:00', '2025-10-26 13:51:40');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `uuid` char(36) NOT NULL,
  `user_uuid` char(36) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`uuid`, `user_uuid`, `token`, `expires_at`, `is_used`, `used_at`, `created_at`) VALUES
('pr-uuid-001', 'js-uuid-003', 'reset123token456def789ghi012jkl345mno678pqr901stu234vwx567yza890bcd', '2025-10-25 12:00:00', 1, '2025-10-25 08:30:00', '2025-10-25 08:00:00'),
('pr-uuid-002', 'js-uuid-005', 'reset987token654wvu321tsr098qpo765nml432kji109hgf876edc543baz210yxw', '2025-10-27 15:00:00', 0, NULL, '2025-10-26 11:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `saved_jobs`
--

CREATE TABLE `saved_jobs` (
  `uuid` char(36) NOT NULL,
  `job_uuid` char(36) NOT NULL,
  `job_seeker_uuid` char(36) NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `saved_jobs`
--

INSERT INTO `saved_jobs` (`uuid`, `job_uuid`, `job_seeker_uuid`, `saved_at`) VALUES
('ace2b33b-819f-4d29-a9f6-0a2e2190c893', 'job-003', 'b4b1b964-31f8-4835-a4b7-5c06c33f2c57', '2025-10-26 19:46:32'),
('saved-001', 'job-001', 'b4b1b964-31f8-4835-a4b7-5c06c33f2c57', '2025-10-25 05:30:00'),
('saved-004', 'job-001', 'js-profile-002', '2025-10-24 08:20:00'),
('saved-005', 'job-008', 'js-profile-002', '2025-10-25 11:30:00'),
('saved-006', 'job-009', 'js-profile-003', '2025-10-25 13:00:00'),
('saved-007', 'job-016', 'js-profile-003', '2025-10-26 05:45:00'),
('saved-008', 'job-020', 'js-profile-004', '2025-10-24 06:00:00'),
('saved-009', 'job-019', 'js-profile-005', '2025-10-25 07:30:00'),
('saved-010', 'job-017', 'js-profile-007', '2025-10-26 04:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uuid` char(36) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('admin','employer','jobseeker') DEFAULT 'jobseeker',
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uuid`, `email`, `password`, `profile_picture`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES
('4609a1c9-9755-4b93-b242-4b3113119340', 'bonfacenjuguna438@gmail.com', '$2y$10$cmHCVfO0i5VQn/dij2quuurohAIQAf8TzzxG.wfODRJhzalxyEC0.', NULL, 'employer', 1, '2025-10-26 05:57:51', '2025-10-26 13:51:40'),
('74ba64a8-091d-4c4c-ab44-408dc62c4bd9', 'bonnienjuguna106@gmail.com', '$2y$10$.SE.Lcs8gDqbFoSqe..5Mum690ImL0IUrt5aCJf.3swZUQiWmQmLy', NULL, 'jobseeker', 1, '2025-10-26 02:38:42', '2025-10-26 13:51:40'),
('admin-uuid-001', 'admin@jobportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', 1, '2025-09-30 21:00:00', '2025-10-26 13:51:40'),
('admin-uuid-002', 'superadmin@jobportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'admin', 1, '2025-09-30 21:00:00', '2025-10-26 13:51:40'),
('emp-uuid-002', 'hr@digitalventures.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'employer', 1, '2025-10-10 07:00:00', '2025-10-26 13:51:40'),
('emp-uuid-003', 'jobs@safaricomke.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'employer', 1, '2025-10-11 08:00:00', '2025-10-26 13:51:40'),
('emp-uuid-004', 'careers@kenyabank.co.ke', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'employer', 1, '2025-10-12 09:00:00', '2025-10-26 13:51:40'),
('emp-uuid-005', 'recruitment@eastafricabreweries.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'employer', 1, '2025-10-13 10:00:00', '2025-10-26 13:51:40'),
('js-uuid-002', 'jane.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', 1, '2025-10-15 06:00:00', '2025-10-26 13:51:40'),
('js-uuid-003', 'michael.ochieng@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', 1, '2025-10-16 07:30:00', '2025-10-26 13:51:40'),
('js-uuid-004', 'sarah.wanjiku@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', 1, '2025-10-17 08:15:00', '2025-10-26 13:51:40'),
('js-uuid-005', 'david.kimani@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', 1, '2025-10-18 11:20:00', '2025-10-26 13:51:40'),
('js-uuid-006', 'grace.akinyi@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', 1, '2025-10-19 05:45:00', '2025-10-26 13:51:40'),
('js-uuid-007', 'peter.mwangi@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', 1, '2025-10-20 10:30:00', '2025-10-26 13:51:40'),
('js-uuid-008', 'mercy.wambui@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', 1, '2025-10-21 06:10:00', '2025-10-26 13:51:40'),
('js-uuid-009', 'james.otieno@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', 1, '2025-10-22 12:00:00', '2025-10-26 13:51:40'),
('js-uuid-010', 'lucy.njeri@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 'jobseeker', 0, '2025-10-23 07:20:00', '2025-10-26 13:51:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD PRIMARY KEY (`uuid`),
  ADD KEY `idx_user_uuid` (`user_uuid`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`uuid`),
  ADD KEY `idx_job_uuid` (`job_uuid`),
  ADD KEY `idx_job_seeker_uuid` (`job_seeker_uuid`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_applied_at` (`applied_at`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`uuid`),
  ADD KEY `idx_user_uuid` (`user_uuid`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `employers`
--
ALTER TABLE `employers`
  ADD PRIMARY KEY (`uuid`),
  ADD KEY `idx_user_uuid` (`user_uuid`),
  ADD KEY `idx_industry` (`industry`);

--
-- Indexes for table `job_posts`
--
ALTER TABLE `job_posts`
  ADD PRIMARY KEY (`uuid`),
  ADD KEY `idx_employer_uuid` (`employer_uuid`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_industry` (`industry`),
  ADD KEY `idx_job_type` (`job_type`),
  ADD KEY `idx_location` (`location`),
  ADD KEY `idx_deadline` (`application_deadline`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `job_seekers`
--
ALTER TABLE `job_seekers`
  ADD PRIMARY KEY (`uuid`),
  ADD KEY `idx_user_uuid` (`user_uuid`),
  ADD KEY `idx_location` (`location`),
  ADD KEY `idx_professional_title` (`professional_title`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`uuid`),
  ADD KEY `idx_user_uuid` (`user_uuid`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD PRIMARY KEY (`uuid`),
  ADD UNIQUE KEY `unique_saved_job` (`job_uuid`,`job_seeker_uuid`),
  ADD KEY `idx_job_uuid` (`job_uuid`),
  ADD KEY `idx_job_seeker_uuid` (`job_seeker_uuid`),
  ADD KEY `idx_saved_at` (`saved_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`uuid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_profiles`
--
ALTER TABLE `admin_profiles`
  ADD CONSTRAINT `admin_profiles_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`uuid`) ON DELETE CASCADE;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`job_uuid`) REFERENCES `job_posts` (`uuid`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_seeker_uuid`) REFERENCES `job_seekers` (`uuid`) ON DELETE CASCADE;

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`uuid`) ON DELETE CASCADE;

--
-- Constraints for table `employers`
--
ALTER TABLE `employers`
  ADD CONSTRAINT `employers_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`uuid`) ON DELETE CASCADE;

--
-- Constraints for table `job_posts`
--
ALTER TABLE `job_posts`
  ADD CONSTRAINT `job_posts_ibfk_1` FOREIGN KEY (`employer_uuid`) REFERENCES `employers` (`uuid`) ON DELETE CASCADE;

--
-- Constraints for table `job_seekers`
--
ALTER TABLE `job_seekers`
  ADD CONSTRAINT `job_seekers_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`uuid`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_uuid`) REFERENCES `users` (`uuid`) ON DELETE CASCADE;

--
-- Constraints for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD CONSTRAINT `saved_jobs_ibfk_1` FOREIGN KEY (`job_uuid`) REFERENCES `job_posts` (`uuid`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_jobs_ibfk_2` FOREIGN KEY (`job_seeker_uuid`) REFERENCES `job_seekers` (`uuid`) ON DELETE CASCADE;
COMMIT;

