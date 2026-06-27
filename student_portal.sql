-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 21 أكتوبر 2025 الساعة 16:19
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_portal`
--

-- --------------------------------------------------------

--
-- بنية الجدول `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `class_name` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `announcements`
--

INSERT INTO `announcements` (`id`, `class_name`, `title`, `content`, `created_by`, `created_at`) VALUES
(1, 'Grade 7A', 'Arabic Reading Contest', 'All students must prepare for next week’s contest.', 2, '2025-10-19 16:36:44'),
(2, 'Grade 7B', 'Math Revision', 'Extra math class scheduled for Thursday.', 3, '2025-10-19 16:36:44'),
(3, 'Grade 6A', 'Islamic Studies Quiz', 'Quiz on Unit 3 will be next Monday.', 2, '2025-10-19 16:36:44'),
(4, 'Grade 6B', 'Science Project', 'Submit your project by Friday.', 3, '2025-10-19 16:36:44'),
(5, 'Grade 7A', 'Sports Day', 'Bring your sports uniform.', 2, '2025-10-19 16:36:44'),
(6, 'Grade 7B', 'English Essay', 'Essay submission deadline extended.', 3, '2025-10-19 16:36:44');

-- --------------------------------------------------------

--
-- بنية الجدول `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `attendance_date` date DEFAULT NULL,
  `status` enum('present','absent','late') DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `attendance_date`, `status`, `notes`, `created_at`) VALUES
(1, 1, '2025-03-05', 'present', '', '2025-10-19 16:36:44'),
(2, 1, '2025-03-06', 'absent', 'Family reason', '2025-10-19 16:36:44'),
(3, 2, '2025-03-07', 'late', 'Came 10 minutes late', '2025-10-19 16:36:44'),
(4, 3, '2025-03-05', 'present', '', '2025-10-19 16:36:44'),
(5, 4, '2025-03-05', 'absent', 'Sick leave', '2025-10-19 16:36:44'),
(6, 5, '2025-03-05', 'present', '', '2025-10-19 16:36:44'),
(7, 6, '2025-03-06', 'present', '', '2025-10-19 16:36:44');

-- --------------------------------------------------------

--
-- بنية الجدول `counseling_sessions`
--

CREATE TABLE `counseling_sessions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `session_date` datetime DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') DEFAULT 'scheduled',
  `treatment_plan` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `counseling_sessions`
--

INSERT INTO `counseling_sessions` (`id`, `student_id`, `counselor_id`, `session_date`, `status`, `treatment_plan`, `notes`, `created_at`) VALUES
(1, 1, 4, '2025-03-20 09:00:00', 'completed', 'Boost confidence in class participation.', 'Aisha participated well.', '2025-10-19 16:36:44'),
(2, 2, 4, '2025-03-21 10:00:00', 'scheduled', 'Encourage focus and time management.', '', '2025-10-19 16:36:44'),
(3, 3, 4, '2025-03-22 09:30:00', 'completed', 'Maintain motivation and creativity.', 'Sara doing great.', '2025-10-19 16:36:44'),
(4, 4, 4, '2025-03-23 11:00:00', 'cancelled', 'Reschedule due to student absence.', '', '2025-10-19 16:36:44'),
(5, 5, 4, '2025-03-24 09:00:00', 'completed', 'Discuss study habits.', 'Session went well.', '2025-10-19 16:36:44'),
(6, 6, 4, '2025-03-25 10:00:00', 'scheduled', 'Work on confidence.', '', '2025-10-19 16:36:44');

-- --------------------------------------------------------

--
-- بنية الجدول `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `grade` decimal(5,2) DEFAULT NULL,
  `exam_type` varchar(50) DEFAULT NULL,
  `date_recorded` date DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `subject`, `grade`, `exam_type`, `date_recorded`, `teacher_id`, `created_at`) VALUES
(1, 1, 'Arabic Language', 93.50, 'Midterm', '2025-03-10', 2, '2025-10-19 16:36:44'),
(2, 2, 'Mathematics', 85.00, 'Final', '2025-06-15', 3, '2025-10-19 16:36:44'),
(3, 3, 'Arabic Language', 97.00, 'Final', '2025-06-16', 2, '2025-10-19 16:36:44'),
(4, 4, 'Mathematics', 78.00, 'Midterm', '2025-03-12', 3, '2025-10-19 16:36:44'),
(5, 5, 'Islamic Studies', 90.50, 'Final', '2025-06-17', 2, '2025-10-19 16:36:44'),
(6, 6, 'Science', 92.00, 'Final', '2025-06-18', 3, '2025-10-19 16:36:44'),
(7, 3, 'x', 10.00, 'Midterm', '2025-10-19', 3, '2025-10-19 17:02:39'),
(8, 3, 'Programming', 5.00, 'Quiz', '2025-10-20', 3, '2025-10-20 13:53:11');

-- --------------------------------------------------------

--
-- بنية الجدول `guardians`
--

CREATE TABLE `guardians` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `guardians`
--

INSERT INTO `guardians` (`id`, `user_id`, `relationship`, `occupation`, `address`, `created_at`) VALUES
(1, 5, 'Father', 'Civil Engineer', 'King Abdullah Rd, Riyadh', '2025-10-19 16:36:44'),
(2, 6, 'Mother', 'Teacher', 'Prince Sultan St, Jeddah', '2025-10-19 16:36:44');

-- --------------------------------------------------------

--
-- بنية الجدول `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `attachment_path`, `is_read`, `created_at`) VALUES
(1, 5, 2, 'Salam Ms. Fatimah, how is Aisha doing in Arabic?', NULL, 0, '2025-10-19 16:36:44'),
(2, 2, 5, 'Aisha is improving well, thank you.', NULL, 0, '2025-10-19 16:36:44'),
(3, 6, 3, 'Hello Mr. Hassan, can you update me about Sara?', NULL, 0, '2025-10-19 16:36:44'),
(4, 3, 6, 'Sara is doing great in Math.', NULL, 0, '2025-10-19 16:36:44'),
(5, 4, 5, 'Mr. Mohammed, please attend tomorrow’s parent meeting.', NULL, 0, '2025-10-19 16:36:44'),
(6, 2, 4, 'Meeting reminder sent successfully.', NULL, 0, '2025-10-19 16:36:44'),
(7, 6, 5, 'hi', NULL, 0, '2025-10-19 17:07:41'),
(9, 3, 6, 'Test message from teacher to parent', NULL, 0, '2025-10-20 14:24:35'),
(32, 3, 7, 'Test message from teacher to parent', NULL, 0, '2025-10-20 15:33:58'),
(33, 3, 7, 'Test message from teacher to parent', NULL, 0, '2025-10-20 15:33:58');

-- --------------------------------------------------------

--
-- بنية الجدول `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `note_type` enum('academic','behavioral','positive','warning') NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `details` text NOT NULL,
  `importance_level` enum('low','medium','high') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `notes`
--

INSERT INTO `notes` (`id`, `student_id`, `teacher_id`, `note_type`, `subject`, `details`, `importance_level`, `created_at`) VALUES
(1, 1, 2, 'academic', 'Arabic Language', 'Aisha needs to improve reading comprehension.', 'medium', '2025-10-19 16:36:44'),
(2, 2, 3, 'behavioral', 'Mathematics', 'Omar often talks during class.', 'low', '2025-10-19 16:36:44'),
(3, 3, 2, 'positive', 'Arabic Language', 'Sara shows great creativity in writing.', 'high', '2025-10-19 16:36:44'),
(4, 4, 3, 'warning', 'Mathematics', 'Yousef missed two assignments.', 'high', '2025-10-19 16:36:44'),
(5, 5, 2, 'academic', 'Islamic Studies', 'Mariam shows consistent participation.', 'medium', '2025-10-19 16:36:44'),
(6, 6, 3, 'positive', 'Science', 'Huda completed her project early.', 'high', '2025-10-19 16:36:44'),
(7, 3, 3, 'academic', 'z', 'cc', 'low', '2025-10-19 17:02:05'),
(8, 4, 3, 'academic', 'H', 'you are a good student', 'medium', '2025-10-20 13:51:32'),
(9, 3, 3, 'academic', 'A', 'SS', 'high', '2025-10-20 13:51:49'),
(10, 6, 3, 'behavioral', 'K', 'aa', 'medium', '2025-10-20 13:52:07');

-- --------------------------------------------------------

--
-- بنية الجدول `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `related_id`, `is_read`, `created_at`) VALUES
(1, 7, 'New Grade', 'Your Arabic grade has been updated.', 'grade', 1, 0, '2025-10-19 16:36:44'),
(2, 8, 'Exam Reminder', 'Math exam scheduled for next week.', 'exam', 2, 0, '2025-10-19 16:36:44'),
(3, 9, 'Attendance Alert', 'You were absent yesterday.', 'attendance', 3, 0, '2025-10-19 16:36:44'),
(4, 10, 'Homework Update', 'Submit your assignment by Monday.', 'homework', 4, 0, '2025-10-19 16:36:44'),
(5, 11, 'Event', 'Science fair next Thursday.', 'event', 5, 0, '2025-10-19 16:36:44'),
(6, 12, 'Counseling', 'Session with Mr. Khalid scheduled.', 'counseling', 6, 0, '2025-10-19 16:36:44'),
(7, 6, 'New Note', 'A new academic note has been added for your student', 'note', 3, 0, '2025-10-19 17:02:05'),
(8, 6, 'New Grade', 'A new grade has been recorded for your student in x', 'grade', 3, 0, '2025-10-19 17:02:39'),
(9, 5, 'New Message', 'Mohammed Al-Hassan sent you a message', 'message', 6, 0, '2025-10-19 17:07:41'),
(10, 5, 'New Note', 'A new academic note has been added for your student', 'note', 4, 0, '2025-10-20 13:51:32'),
(11, 6, 'New Note', 'A new academic note has been added for your student', 'note', 3, 0, '2025-10-20 13:51:49'),
(12, 6, 'New Note', 'A new behavioral note has been added for your student', 'note', 6, 0, '2025-10-20 13:52:07'),
(13, 6, 'New Grade', 'A new grade has been recorded for your student in Programming', 'grade', 3, 0, '2025-10-20 13:53:11'),
(14, 6, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 14:31:46'),
(15, 6, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 14:39:44'),
(16, 6, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 14:40:52'),
(17, 6, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 14:47:37'),
(18, 6, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 14:50:09'),
(19, 6, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 14:52:34'),
(20, 6, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 14:52:49'),
(21, 6, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 14:55:27'),
(22, 6, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 14:56:59'),
(23, 7, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 15:10:50'),
(24, 7, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 15:12:34'),
(25, 7, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 15:16:30'),
(26, 7, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 15:21:33'),
(27, 7, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 15:21:40'),
(28, 7, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 15:29:32'),
(29, 7, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 15:31:03'),
(30, 7, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 15:31:03'),
(31, 6, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 1, '2025-10-20 15:31:11'),
(32, 6, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 15:31:11'),
(33, 7, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 15:33:58'),
(34, 7, 'New Message', 'You have a new message from Fatimah Al-Harbi', 'message', 3, 0, '2025-10-20 15:33:58'),
(35, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:06:26'),
(36, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:06:26'),
(37, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:06:30'),
(38, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:06:30'),
(39, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:08:41'),
(40, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:08:41'),
(41, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:09:00'),
(42, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:09:00'),
(43, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:09:38'),
(44, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:09:38'),
(45, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:17:42'),
(46, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:17:42'),
(47, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:20:59'),
(48, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:20:59'),
(49, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:22:09'),
(50, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:22:09'),
(51, 4, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:27:22'),
(52, 4, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:27:22'),
(53, 4, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:31:27'),
(54, 4, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:31:27'),
(55, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:44:13'),
(56, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:44:13'),
(57, 4, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:56:52'),
(58, 4, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 16:56:52'),
(59, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 17:01:35'),
(60, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 17:01:35'),
(61, 3, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 17:05:01'),
(62, 5, 'New Message', 'You have a new message from Mohammed Al-Hassan', 'message', 6, 0, '2025-10-20 17:05:28');

-- --------------------------------------------------------

--
-- بنية الجدول `special_circumstances`
--

CREATE TABLE `special_circumstances` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `confidential_notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `special_circumstances`
--

INSERT INTO `special_circumstances` (`id`, `student_id`, `description`, `confidential_notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Temporary concentration issue.', 'Improved after counseling.', 4, '2025-10-19 16:36:44', '2025-10-19 16:36:44'),
(2, 2, 'Mild stress due to exams.', 'Handled with rest and support.', 4, '2025-10-19 16:36:44', '2025-10-19 16:36:44'),
(3, 3, 'Family relocation soon.', 'Monitor adjustment after move.', 4, '2025-10-19 16:36:44', '2025-10-19 16:36:44'),
(4, 4, 'Health-related absence.', 'Doctor report provided.', 4, '2025-10-19 16:36:44', '2025-10-19 16:36:44'),
(5, 5, 'Low participation in class.', 'Encourage engagement.', 4, '2025-10-19 16:36:44', '2025-10-19 16:36:44'),
(6, 6, 'Shy in presentations.', 'Counselor following up.', 4, '2025-10-19 16:36:44', '2025-10-19 16:36:44');

-- --------------------------------------------------------

--
-- بنية الجدول `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `class_name` varchar(50) DEFAULT NULL,
  `guardian_id` int(11) DEFAULT NULL,
  `health_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `students`
--

INSERT INTO `students` (`id`, `user_id`, `student_id`, `date_of_birth`, `gender`, `class_name`, `guardian_id`, `health_info`, `created_at`) VALUES
(1, 7, 'STU1001', '2011-04-18', 'Female', 'Grade 7A', 5, 'Healthy, no issues.', '2025-10-19 16:36:44'),
(2, 8, 'STU1002', '2010-11-10', 'Male', 'Grade 7A', 5, 'Mild seasonal allergy.', '2025-10-19 16:36:44'),
(3, 9, 'STU1003', '2011-03-05', 'Female', 'Grade 7B', 6, 'Normal vision, wears glasses.', '2025-10-19 16:36:44'),
(4, 10, 'STU1004', '2010-08-22', 'Male', 'Grade 7B', 5, 'No health concerns.', '2025-10-19 16:36:44'),
(5, 11, 'STU1005', '2011-02-17', 'Female', 'Grade 6A', 6, 'Asthma (controlled).', '2025-10-19 16:36:44'),
(6, 12, 'STU1006', '2011-12-01', 'Female', 'Grade 6B', 6, 'Healthy.', '2025-10-19 16:36:44');

-- --------------------------------------------------------

--
-- بنية الجدول `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `class_assigned` varchar(50) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `teachers`
--

INSERT INTO `teachers` (`id`, `user_id`, `subject`, `class_assigned`, `specialization`, `created_at`) VALUES
(1, 2, 'Arabic Language', 'Grade 7A', 'Grammar', '2025-10-19 16:36:44'),
(2, 3, 'Mathematics', 'Grade 7B', 'Algebra', '2025-10-19 16:36:44'),
(3, 2, 'Islamic Studies', 'Grade 6A', 'Fiqh and Aqeedah', '2025-10-19 16:36:44'),
(4, 3, 'Science', 'Grade 6B', 'Biology', '2025-10-19 16:36:44'),
(5, 2, 'Social Studies', 'Grade 7A', 'Geography', '2025-10-19 16:36:44'),
(6, 3, 'English Language', 'Grade 7B', 'Grammar and Writing', '2025-10-19 16:36:44');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `role` enum('admin','teacher','counselor','parent','student') NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `phone`, `profile_picture`, `created_at`, `updated_at`, `is_active`) VALUES
(2, 'admin1', '$2y$10$zxIhg9B6VoLIQqFrYUqI2eis.fFDbXKIKqIYEmQNkSzmmGYvvUeui', 'admin1@school.com', 'Ahmed Al-Rashid', 'admin', '+966500000001', 'uploads/admin1.jpg', '2025-10-19 16:36:44', '2025-10-20 13:41:39', 1),
(3, 'teacher1', '$2y$10$p/6XlIg3/vutEl6JD0nbe.lewxJllGCCI9WkkzuhS8ecnk7/fk.z6', 'teacher1@school.com', 'Fatimah Al-Harbi', 'teacher', '+966500000002', 'uploads/teacher1.jpg', '2025-10-19 16:36:44', '2025-10-19 16:55:19', 1),
(4, 'teacher2', '$2y$10$kbvAJfEJdoSlIaEwC3Xy4.y14KCY3ppEjPGwYaa3svXSVE1MVjQTy', 'teacher2@school.com', 'Hassan Al-Qahtani', 'teacher', '+966500000003', 'uploads/teacher2.jpg', '2025-10-19 16:36:44', '2025-10-19 16:47:33', 1),
(5, 'counselor1', '$2y$10$urZv8jgvn1Tg/6PyErYVp.pa7e9XToYrStXVETrKlXdLn3FAO2Lyu', 'counselor1@school.com', 'Khalid Al-Mutairi', 'counselor', '+966500000004', 'uploads/counselor1.jpg', '2025-10-19 16:36:44', '2025-10-19 17:03:53', 1),
(6, 'parent1', '$2y$10$KzRckuqQy5F9dpWdaDgEPOgM4eTni0EBmoJTyI2ujTA.y.bFU83Lq', 'parent1@school.com', 'Mohammed Al-Hassan', 'parent', '+966500000005', 'uploads/parent1.jpg', '2025-10-19 16:36:44', '2025-10-19 17:04:18', 1),
(7, 'parent2', '$2y$10$BBx1TvmUKKnXJ1uwl//gCeQNoKc6aaS.2EKJ8ickx6KglOwknFzBS', 'parent2@school.com', 'Layla Al-Zahrani', 'parent', '+966500000006', 'uploads/parent2.jpg', '2025-10-19 16:36:44', '2025-10-19 17:04:26', 1),
(8, 'student1', '$2y$10$uOBuLEk4I54bkcqg2n.aq.6PfQzvBF0Y2iunVbSwrHAWyNt13XFK.', 'student1@school.com', 'Aisha Al-Hassan', 'student', '+966500000007', 'uploads/student1.jpg', '2025-10-19 16:36:44', '2025-10-19 16:48:08', 1),
(9, 'student2', '$2y$10$EqYf2Jlwyefr9XUr9.Cc1eoYfvpZUJo9xycsp60I1sH.YxLQNljBG', 'student2@school.com', 'Omar Al-Hassan', 'student', '+966500000008', 'uploads/student2.jpg', '2025-10-19 16:36:44', '2025-10-19 16:48:13', 1),
(10, 'student3', '$2y$10$v4EOG8hqw48kIqKemeZ.yOCkXm34qjOkgv7hLbRXuQ/yI0IZU60Ea', 'student3@school.com', 'Sara Al-Qahtani', 'student', '+966500000009', 'uploads/student3.jpg', '2025-10-19 16:36:44', '2025-10-19 16:48:18', 1),
(11, 'student4', '$2y$10$hash10', 'student4@school.com', 'Yousef Al-Harbi', 'student', '+966500000010', 'uploads/student4.jpg', '2025-10-19 16:36:44', '2025-10-19 16:36:44', 1),
(12, 'student5', '$2y$10$hash11', 'student5@school.com', 'Mariam Al-Zahrani', 'student', '+966500000011', 'uploads/student5.jpg', '2025-10-19 16:36:44', '2025-10-19 16:36:44', 1),
(13, 'student6', '$2y$10$hash12', 'student6@school.com', 'Huda Al-Mutairi', 'student', '+966500000012', 'uploads/student6.jpg', '2025-10-19 16:36:44', '2025-10-19 16:36:44', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `counselor_id` (`counselor_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `special_circumstances`
--
ALTER TABLE `special_circumstances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `guardian_id` (`guardian_id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `special_circumstances`
--
ALTER TABLE `special_circumstances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- قيود الجداول `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  ADD CONSTRAINT `counseling_sessions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `counseling_sessions_ibfk_2` FOREIGN KEY (`counselor_id`) REFERENCES `users` (`id`);

--
-- قيود الجداول `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`);

--
-- قيود الجداول `guardians`
--
ALTER TABLE `guardians`
  ADD CONSTRAINT `guardians_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`);

--
-- قيود الجداول `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- قيود الجداول `special_circumstances`
--
ALTER TABLE `special_circumstances`
  ADD CONSTRAINT `special_circumstances_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `special_circumstances_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- قيود الجداول `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_ibfk_2` FOREIGN KEY (`guardian_id`) REFERENCES `users` (`id`);

--
-- قيود الجداول `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `teachers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
