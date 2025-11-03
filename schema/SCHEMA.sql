-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 03, 2025 at 11:20 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

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
  `status` enum('pending','reviewed','accepted','rejected') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`uuid`, `job_uuid`, `job_seeker_uuid`, `cover_letter`, `resume_file`, `status`, `applied_at`, `reviewed_at`) VALUES
('7bf3334a-28bd-4f2d-9581-57701685be19', '5f6d8248-290f-446a-adab-24600f151f4a', '79f37199-53bc-4017-bae4-65411835afbd', 'kkkkkkkk', 'uploads/resumes/resume_69087bfae03d87.23160430.pdf', 'pending', '2025-11-03 09:55:06', NULL),
('e54992e6-05c8-41c5-8a07-6a45a68d0dec', '4dd49d1d-a093-4920-aac6-5f78a5eb497c', '79f37199-53bc-4017-bae4-65411835afbd', 'Dear Hiring Team,\r\n\r\nI am excited to apply for the Senior PHP Developer position at TechNova Solutions. With over 5 years of experience in backend development using Laravel and MySQL, I have successfully led multiple API-driven projects that improved system performance and maintainability.\r\n\r\nI am particularly drawn to TechNova’s commitment to clean code and scalable architecture — values I strongly share. I’d love the opportunity to contribute my skills and continue growing with your dynamic team.\r\n\r\nSincerely,\r\nBrian Kamau', 'uploads/resumes/resume_69030992295065.49308198.pdf', 'reviewed', '2025-10-30 06:45:38', '2025-10-30 06:46:17');

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
('3ce77b15-1a49-4892-8118-acb85de487a1', '7d172742-a87c-445c-9f87-d5a733d85af7', 'TechNova Solutions Ltd', '+254 712 345 678', NULL, 'technology', 'https://www.technova.co.ke', '/uploads/company_logos/690306af1006b.png', 'TechNova Solutions is a Nairobi-based software development agency specializing in custom web and mobile solutions for SMEs and enterprises. We focus on clean code, scalable architecture, and delivering outstanding user experiences for clients across Africa.', '2025-10-30 06:33:19', '2025-10-30 06:33:19');

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
('24fbce6d-3a98-4c56-8741-059d165773f4', '3ce77b15-1a49-4892-8118-acb85de487a1', 'PRODUCTION MANAGER', 'Mid Level', 'TEKIRIA GENERAL SUPPLIERS LIMITED\r\nManagement & Business Development', 'Full-time', 'Finance', 'Nairobi', '', 'Do not make any payment without confirming with the BrighterMonday Customer Support Team.\r\nIf you think this advert is not genuine, please report it via the Report Job link below.', 'Diploma or degree in Textile Design, Fashion & Apparel Production, or Industrial Management.\r\nMinimum 3–5 years of experience in garment or uniforms production management.\r\nStrong knowledge of designing, cutting, stitching, and finishing processes.\r\nExcellent leadership and organizational skills.\r\nAbility to manage multiple orders and meet strict deadlines.\r\nPractical experience with sewing and cutting machines maintenance is an added advantage.', 'open', '2025-11-29', '2025-11-03 03:49:26', '2025-11-03 03:49:26'),
('4dd49d1d-a093-4920-aac6-5f78a5eb497c', '3ce77b15-1a49-4892-8118-acb85de487a1', 'Senior PHP Developer', 'Senior Level', 'We are looking for a highly skilled Senior PHP Developer to join our growing software engineering team. The candidate will be responsible for developing, maintaining, and improving web applications using PHP, MySQL, and modern frameworks.', 'Full-time', 'Technology', 'Nairobi, Kenya', '', 'We offer flexible working hours, health insurance, and a collaborative environment where developers are encouraged to innovate.', '4+ years of experience with PHP and Laravel or CodeIgniter\r\n\r\nStrong understanding of MySQL and RESTful APIs\r\n\r\nProficient in HTML, CSS, JavaScript, and Bootstrap\r\n\r\nFamiliarity with Git and CI/CD workflows\r\n\r\nBachelor’s Degree in Computer Science or related field', 'open', '2025-11-05', '2025-10-30 06:42:11', '2025-10-30 12:25:09'),
('5f6d8248-290f-446a-adab-24600f151f4a', '3ce77b15-1a49-4892-8118-acb85de487a1', 'Frontend React Developer', 'Mid Level', 'We’re hiring a React Developer to help build and maintain modern, responsive web applications. The candidate will work closely with backend engineers and designers to deliver high-quality user experiences.', 'Full-time', 'Technology', 'Remote', 'KSh 120,000 - 160,000 per month', 'This is a 6-month renewable remote contract with flexible hours and an opportunity to work with a global team.', '2+ years of experience in React.js, TypeScript, and Tailwind CSS\r\nFamiliar with REST and GraphQL APIs\r\nStrong understanding of responsive and accessible design\r\nGood problem-solving and communication skills', 'open', '2025-11-20', '2025-10-30 06:41:09', '2025-10-30 06:41:09'),
('7103c451-7b53-4c8f-9c9c-7dc5f366331f', '3ce77b15-1a49-4892-8118-acb85de487a1', 'Digital Marketing Specialist', 'Mid Level', 'We are seeking a creative Digital Marketing Specialist to design and execute marketing campaigns that drive engagement and growth across multiple channels including social media, email, and Google Ads.', 'Full-time', 'Technology', 'Mombasa, Kenya', 'KSh 60,000 - 90,000', 'We provide training, performance bonuses, and growth opportunities within our marketing department.', '1–2 years of experience in digital marketing or a related field\r\nExperience with Google Analytics, Facebook Ads Manager, and SEO tools\r\nStrong communication and copywriting skills\r\nDiploma or Degree in Marketing, Communication, or related field', 'open', '2025-11-25', '2025-10-30 06:39:33', '2025-10-30 06:39:33'),
('7427d00f-5eba-4be2-aab0-e569c9e23278', '3ce77b15-1a49-4892-8118-acb85de487a1', 'HR Recruiter (Healthcare & IT Sector)', 'Mid Level', 'The Recruiter is responsible for candidate generation direct sourcing and research. The ideal candidate has Excellent English communication skills, passion for helping others, and background in the recruitment of healthcare professionals in the US market or be willing to undergo training to learn.', 'Full-time', 'Healthcare', 'Nairobi', '', 'Competitive compensation and performance bonuses.\r\nLong-term engagement with potential for further collaboration on different projects in our BPO.\r\nClear onboarding, training, and ongoing support\r\nTransportation Benefit', 'Required Technical and Professional Expertise\r\nAt least 5 years experience in recruitment\r\nExcellent English communication skills\r\nBA/BS degree or equivalent work experience', 'open', '2025-11-30', '2025-11-03 03:50:50', '2025-11-03 03:50:50');

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
('79f37199-53bc-4017-bae4-65411835afbd', '17616f71-8b7f-4999-8ddc-8d4b86015796', 'bonface njuguna', '0717299106', 'male', '2010-06-15', 'Nairobi, kenya', 'An enthusistic, self driven, self motivated software deloper', 'Sofware Developer', 'Java, PHP, Javascript', 'Bsc Mathematics and computer Science', NULL, 0, '2025-10-28 17:50:17', '2025-11-01 04:50:13'),
('afebd279-ce5e-4444-8c26-cad7c2711954', '71d28d6b-c1cc-4f71-a6b1-a7360d4577b4', 'flasky 06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2025-10-28 17:46:44', '2025-10-28 17:46:44'),
('cf8f8f59-bc0c-4d20-96d1-bbb35dad978c', '70ad25c5-5b9a-472d-8bcd-89cae7020e39', 'Alex morgan', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2025-11-03 07:18:15', '2025-11-03 07:18:15'),
('fb780d6b-42e3-43fa-8079-4b28dc901ba5', '7d172742-a87c-445c-9f87-d5a733d85af7', 'bonface njuguna', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2025-10-28 18:06:36', '2025-10-28 18:06:36');

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
('65875452-a093-404b-a940-1a2b49985901', '5f6d8248-290f-446a-adab-24600f151f4a', '79f37199-53bc-4017-bae4-65411835afbd', '2025-10-30 07:34:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `uuid` char(36) NOT NULL,
  `email` varchar(150) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('admin','employer','jobseeker') DEFAULT 'jobseeker',
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`uuid`, `email`, `google_id`, `password`, `profile_picture`, `role`, `is_verified`, `created_at`, `updated_at`) VALUES
('17616f71-8b7f-4999-8ddc-8d4b86015796', 'bonfacenjuguna438@gmail.com', '105744728310091226436', '$2y$10$5ZDJMI1uoXLQeLvHLgCeCu/OYI0W4NcSiv0APZA.W6rWV9nMwiK6q', '/uploads/profile_photos/69087c0edf52d.JPG', 'jobseeker', 1, '2025-10-28 17:50:17', '2025-11-03 09:55:26'),
('70ad25c5-5b9a-472d-8bcd-89cae7020e39', 'alex987morgan@gmail.com', '102016787595065768633', '$2y$10$808lxu1hrn4spfjE/JcwH.0hHXyx0XlBZ9i73aapIj8dcxnj/40ry', NULL, 'jobseeker', 1, '2025-11-03 07:18:15', '2025-11-03 07:18:15'),
('71d28d6b-c1cc-4f71-a6b1-a7360d4577b4', 'flasky09@gmail.com', '103646506312782455940', '$2y$10$X.zjIsMMXzhaRhZFqgmTfupL26QyvdyTtbHGWjwYU55RrGqnewHY2', NULL, 'admin', 1, '2025-10-28 17:46:44', '2025-10-29 02:52:40'),
('7d172742-a87c-445c-9f87-d5a733d85af7', 'bonnienjuguna106@gmail.com', '115977763841019428874', '$2y$10$7/d2DQCSSHm3M66HfMbOiOKLQmX8OC4ZvmjcAKsin74yegUF.wj0u', NULL, 'employer', 1, '2025-10-28 18:06:36', '2025-10-30 06:03:17');

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
  ADD UNIQUE KEY `unique_application` (`job_uuid`,`job_seeker_uuid`),
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
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_google_id` (`google_id`);

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
