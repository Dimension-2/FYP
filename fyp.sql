-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2026 at 04:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fyp`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `course_title` varchar(100) DEFAULT NULL,
  `credit_hours` varchar(10) DEFAULT NULL,
  `total_classes` int(11) DEFAULT NULL,
  `attended_classes` int(11) DEFAULT NULL,
  `semester` int(11) DEFAULT 7,
  `registration_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `course_code`, `course_title`, `credit_hours`, `total_classes`, `attended_classes`, `semester`, `registration_no`) VALUES
(8, 'ELE-729', 'BASISC ELECTRONICS (Lab)', '3(0-3)', 0, 0, 8, 'UW-22M-CS-BS-105'),
(9, 'ADP-249', 'SYNCHRONIZAION METHODOLOGY', '3(3-0)', 0, 0, 8, 'UW-22M-CS-BS-105'),
(10, 'MGT-102', 'Professional Practices', '3', 0, 0, 1, 'UW-24M-CS-BS-015'),
(11, 'TTS-105', 'C SHARP', '3(3-0)', 0, 0, 1, 'UW-24M-CS-BS-015');

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `log_id` int(11) NOT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` enum('Present','Absent','Leave') DEFAULT NULL,
  `topic` varchar(255) DEFAULT NULL,
  `lecture_room` varchar(50) DEFAULT NULL,
  `lecture_no` int(11) DEFAULT 1,
  `registration_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `attendance_logs`
--

INSERT INTO `attendance_logs` (`log_id`, `course_code`, `date`, `status`, `topic`, `lecture_room`, `lecture_no`, `registration_no`) VALUES
(19, 'ALT-927', '2026-05-15', 'Absent', 'data base queries', 'lr 4', 2, 'UW-2024M-DS-MS-099'),
(20, 'ALT-927', '2026-05-16', 'Present', 'advance DB', 'lr 4', 5, 'UW-2024M-DS-MS-099'),
(21, 'ADP-249', '2026-05-15', 'Absent', 'data base queriessssss', 'lr 4', 1, 'UW-25M-MTH-BS-001'),
(22, 'ADP-249', '2026-05-15', 'Present', 'data base queriessssss', 'lr 4', 1, 'UW-22M-CS-BS-105'),
(23, 'ADP-249', '2026-09-17', 'Present', 'asdasd', 'lr 4', 2, 'UW-25M-MTH-BS-001'),
(24, 'ADP-249', '2026-09-17', 'Present', 'asdasd', 'lr 4', 2, 'UW-22M-CS-BS-105'),
(29, 'ADP-249', '2026-05-15', 'Present', 'asdasdasd', 'asd', 1, 'UW-25M-MTH-BS-001'),
(30, 'ADP-249', '2026-05-15', 'Absent', 'asdasdasd', 'asd', 1, 'UW-22M-CS-BS-105'),
(31, 'TTS-105', '2026-05-15', 'Present', 'OKOKO', 'LR3', 2, 'UW-23M-CS-BS-075'),
(32, 'TTS-105', '2026-05-15', 'Absent', 'OKOKO', 'LR3', 2, 'UW-24M-CS-BS-015');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `dept_id` int(11) DEFAULT NULL,
  `program` enum('BS','MS','PhD') DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `section` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `complaint_id` varchar(20) DEFAULT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Resolved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `complaint_id`, `registration_no`, `category`, `subject`, `notes`, `status`, `created_at`) VALUES
(1, 'CMP-6372', 'UW-22-CS-BS-053', 'IT / AVICENNA', 'asd', 'ASDASDASDASD', 'Pending', '2026-03-18 08:07:44'),
(2, 'CMP-6042', '123', 'Dean', 'asdasdasdasd', 'asdasdasdasd', 'Pending', '2026-04-07 13:37:58'),
(3, 'CMP26-4474', '123', 'IT / AVICENNA', 'asd', 'asdasd', 'Pending', '2026-04-08 06:26:09'),
(4, 'CMP26-9122', '123', 'IT / AVICENNA', 'adasdasd', 'asdasd', 'Pending', '2026-04-08 08:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `course_assignments`
--

CREATE TABLE `course_assignments` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `course_title` varchar(100) DEFAULT NULL,
  `teacher_name` varchar(100) DEFAULT NULL,
  `credit_hours` varchar(10) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `course_assignments`
--

INSERT INTO `course_assignments` (`id`, `course_code`, `course_title`, `teacher_name`, `credit_hours`, `semester`) VALUES
(3, 'CS-101', 'Cloud Computing', 'Muhammad Ali', '3', 3),
(5, 'MGT-102', 'Professional Practices', 'ABCD', '3', 1),
(7, 'ELE-154', 'ANALYSIS ON QUANTUM ALGO\'S', 'Muhammad Ali', '4', 4),
(8, 'CS-101', 'CLOUD ANALYSIS', 'Muhammad Ali', '3', 2),
(9, 'CS-109', 'PYTHON FUNDAMENTALS (Lab)', 'ABCD', '3(0-3)', 6),
(11, 'ELE-729', 'BASISC ELECTRONICS (Lab)', 'ABCD', '3(0-3)', 8),
(12, 'MGT-102', 'PHP , laravel (Lab)', 'FAIZAN AZHAR LONE', '2(0-2)', 1),
(13, 'ADP-151', 'Advance Programming (Lab)', NULL, '4(0-4)', 5),
(14, 'PPS-117', 'Quantum Computing (Lab)', 'Muhammad Ali', '4(0-4)', 6),
(15, 'ALT-927', 'Machine Learning (Lab)', 'AZHAR IQBAL', '3(0-3)', 7),
(16, 'ADP-249', 'SYNCHRONIZAION METHODOLOGY', 'AZHAR IQBAL', '3(3-0)', 8),
(17, 'TTS-105', 'C SHARP', 'FAIZAN AZHAR LONE', '3(3-0)', 1);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `dept_name` varchar(50) DEFAULT NULL,
  `dept_code` varchar(10) DEFAULT NULL,
  `color_accent` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `dept_name`, `dept_code`, `color_accent`) VALUES
(1, 'Computer Science', 'CS', '#10b981'),
(2, 'Artificial Intelligence', 'AI', '#8b5cf6'),
(3, 'Mathematics', 'MTH', '#3b82f6'),
(4, 'Physics', 'PHY', '#f59e0b');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `type` enum('Hostel','Transport') DEFAULT NULL,
  `location_zone` varchar(100) DEFAULT NULL,
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_datesheet`
--

CREATE TABLE `exam_datesheet` (
  `id` int(11) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `course_title` varchar(100) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `exam_time` varchar(20) DEFAULT NULL,
  `attendance_pct` int(11) DEFAULT NULL,
  `status` enum('Eligible','Short Attendance','Not Allowed') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_feedback`
--

CREATE TABLE `faculty_feedback` (
  `id` int(11) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `course_title` varchar(255) DEFAULT NULL,
  `scores` text DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `submission_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `faculty_feedback`
--

INSERT INTO `faculty_feedback` (`id`, `registration_no`, `course_code`, `course_title`, `scores`, `comments`, `submission_date`, `created_at`) VALUES
(8, '123', NULL, NULL, 'null', 'kkkkkkkkkkkkkkkkkkk', '2026-04-08', '2026-04-08 06:28:55'),
(9, NULL, 'CS-451', 'Compiler Construction (Th)', '{\"q0\":\"5\",\"q1\":\"5\",\"q2\":\"3\",\"q3\":\"5\",\"q4\":\"4\",\"q5\":\"4\",\"q6\":\"3\",\"q7\":\"5\",\"q8\":\"4\",\"q9\":\"5\",\"q10\":\"4\",\"q11\":\"5\",\"q12\":\"3\",\"q13\":\"5\",\"q14\":\"3\",\"q15\":\"4\",\"q16\":\"4\",\"q17\":\"3\",\"q18\":\"5\",\"q19\":\"3\"}', 'kkkkkkkkkkkkkkkkkkk', '2026-04-08', '2026-04-08 06:28:55'),
(10, '123', NULL, NULL, 'null', 'ijijij', '2026-04-08', '2026-04-08 09:04:39'),
(11, NULL, 'CS-447', 'Information Security (Th)', '{\"q0\":\"5\",\"q1\":\"5\",\"q2\":\"4\",\"q3\":\"3\",\"q4\":\"4\",\"q5\":\"4\",\"q6\":\"5\",\"q7\":\"5\",\"q8\":\"5\",\"q9\":\"5\",\"q10\":\"4\",\"q11\":\"4\",\"q12\":\"4\",\"q13\":\"5\",\"q14\":\"5\",\"q15\":\"5\",\"q16\":\"5\",\"q17\":\"5\",\"q18\":\"4\",\"q19\":\"5\"}', 'ijijij', '2026-04-08', '2026-04-08 09:04:39'),
(12, '123', NULL, NULL, 'null', 'Demo Comment', '2026-04-08', '2026-04-08 11:48:22'),
(13, NULL, 'CS-447', 'Information Security (Th)', '{\"q0\":\"5\",\"q1\":\"5\",\"q2\":\"5\",\"q3\":\"5\",\"q4\":\"5\",\"q5\":\"5\",\"q6\":\"5\",\"q7\":\"5\",\"q8\":\"5\",\"q9\":\"5\",\"q10\":\"5\",\"q11\":\"5\",\"q12\":\"5\",\"q13\":\"5\",\"q14\":\"5\",\"q15\":\"5\",\"q16\":\"5\",\"q17\":\"5\",\"q18\":\"5\",\"q19\":\"5\"}', 'Demo Comment', '2026-04-08', '2026-04-08 11:48:22'),
(14, 'uw-22M-cs-bs-105', 'CS-447', 'Information Security (Th)', '{\"q0\":\"5\",\"q1\":\"5\",\"q2\":\"5\",\"q3\":\"5\",\"q4\":\"5\",\"q5\":\"5\",\"q6\":\"5\",\"q7\":\"5\",\"q8\":\"5\",\"q9\":\"5\",\"q10\":\"5\",\"q11\":\"5\",\"q12\":\"5\",\"q13\":\"5\",\"q14\":\"5\",\"q15\":\"5\",\"q16\":\"5\",\"q17\":\"5\",\"q18\":\"5\",\"q19\":\"5\"}', 'ARMAGHAAN', '2026-05-15', '2026-05-15 04:42:14'),
(15, 'uw-22M-cs-bs-105', 'N/A', 'Selected Course', '{\"q0\":\"5\",\"q1\":\"5\",\"q2\":\"5\",\"q3\":\"5\",\"q4\":\"5\",\"q5\":\"5\",\"q6\":\"5\",\"q7\":\"5\",\"q8\":\"5\",\"q9\":\"5\",\"q10\":\"5\",\"q11\":\"5\",\"q12\":\"5\",\"q13\":\"5\",\"q14\":\"5\",\"q15\":\"5\",\"q16\":\"5\",\"q17\":\"5\",\"q18\":\"5\",\"q19\":\"5\"}', 'ezio', '2026-05-15', '2026-05-15 05:11:26'),
(16, 'UW-24M-CS-BS-015', 'N/A', 'Selected Course', '{\"q0\":\"5\",\"q1\":\"5\",\"q2\":\"5\",\"q3\":\"5\",\"q4\":\"5\",\"q5\":\"5\",\"q6\":\"5\",\"q7\":\"5\",\"q8\":\"5\",\"q9\":\"5\",\"q10\":\"5\",\"q11\":\"5\",\"q12\":\"5\",\"q13\":\"5\",\"q14\":\"5\",\"q15\":\"5\",\"q16\":\"5\",\"q17\":\"5\",\"q18\":\"5\",\"q19\":\"5\"}', 'OKOKOKOKOK', '2026-05-15', '2026-05-15 13:27:53');

-- --------------------------------------------------------

--
-- Table structure for table `fee_uploads`
--

CREATE TABLE `fee_uploads` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `slip_path` varchar(255) DEFAULT NULL,
  `upload_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_vouchers`
--

CREATE TABLE `fee_vouchers` (
  `id` int(11) NOT NULL,
  `voucher_no` varchar(20) DEFAULT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `semester_label` varchar(10) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `tuition_fee` float DEFAULT NULL,
  `exam_fee` float DEFAULT NULL,
  `it_charges` float DEFAULT NULL,
  `fine` float DEFAULT 0,
  `scholarship` float DEFAULT 0,
  `status` enum('Unpaid','Pending Verification','Paid') DEFAULT 'Unpaid',
  `paid_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grading_items`
--

CREATE TABLE `grading_items` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `item_type` enum('Assignment','Quiz','Mid','Final') DEFAULT NULL,
  `category` enum('Sessional','Mid','Final') NOT NULL DEFAULT 'Sessional',
  `item_label` varchar(50) DEFAULT NULL,
  `total_marks` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `grading_items`
--

INSERT INTO `grading_items` (`id`, `course_code`, `item_type`, `category`, `item_label`, `total_marks`) VALUES
(1, 'ALT-927', NULL, 'Sessional', 'QUIZ 1', 20),
(2, 'ALT-927', NULL, 'Sessional', 'QUIZ 2', 25),
(4, 'ALT-927', 'Final', 'Sessional', 'FINAL TERM', 45),
(6, 'ALT-927', 'Mid', 'Sessional', 'MIDS', 30),
(15, 'ADP-249', 'Final', 'Sessional', 'FINALS', 45),
(17, 'ADP-249', 'Mid', 'Sessional', 'MIds', 30),
(20, 'ADP-249', '', 'Sessional', 'QUIZ 1', 20),
(21, 'ADP-249', '', 'Sessional', 'QUIZ 2', 20),
(22, 'ADP-249', '', 'Sessional', 'Quiz 3', 20),
(23, 'TTS-105', '', 'Sessional', 'QUIZ 1', 20),
(24, 'TTS-105', 'Mid', 'Sessional', 'MIds', 30),
(25, 'TTS-105', 'Final', 'Sessional', 'FINALS', 45),
(26, 'TTS-105', '', 'Sessional', 'Quiz 3', 20);

-- --------------------------------------------------------

--
-- Table structure for table `hostel_mess_vouchers`
--

CREATE TABLE `hostel_mess_vouchers` (
  `id` int(11) NOT NULL,
  `challan_no` varchar(50) DEFAULT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `billing_month` varchar(50) DEFAULT NULL,
  `mess_charges` decimal(10,2) DEFAULT NULL,
  `special_charges` decimal(10,2) DEFAULT NULL,
  `total_payable` decimal(10,2) DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostel_vouchers`
--

CREATE TABLE `hostel_vouchers` (
  `id` int(11) NOT NULL,
  `challan_no` varchar(50) DEFAULT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `security_fee` decimal(10,2) DEFAULT NULL,
  `card_charges` decimal(10,2) DEFAULT NULL,
  `total_payable` decimal(10,2) DEFAULT NULL,
  `status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

CREATE TABLE `profile` (
  `id` int(11) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `photo` varchar(255) DEFAULT 'default.png',
  `full_name` varchar(100) DEFAULT NULL,
  `status_badge` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `semester` varchar(10) DEFAULT NULL,
  `session` varchar(10) DEFAULT NULL,
  `admission_date` varchar(20) DEFAULT NULL,
  `domicile` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `cnic` varchar(20) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `family_income` varchar(50) DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `fsc_total` varchar(10) DEFAULT NULL,
  `fsc_obtained` varchar(10) DEFAULT NULL,
  `fsc_per` varchar(10) DEFAULT NULL,
  `fsc_year` varchar(10) DEFAULT NULL,
  `fsc_board` varchar(100) DEFAULT NULL,
  `fsc_major` varchar(50) DEFAULT NULL,
  `ssc_total` varchar(10) DEFAULT NULL,
  `ssc_obtained` varchar(10) DEFAULT NULL,
  `ssc_per` varchar(10) DEFAULT NULL,
  `ssc_year` varchar(10) DEFAULT NULL,
  `ssc_board` varchar(100) DEFAULT NULL,
  `ssc_major` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `profile`
--

INSERT INTO `profile` (`id`, `registration_no`, `photo`, `full_name`, `status_badge`, `department`, `program`, `semester`, `session`, `admission_date`, `domicile`, `dob`, `cnic`, `nationality`, `gender`, `father_name`, `guardian_name`, `guardian_phone`, `family_income`, `current_address`, `permanent_address`, `email`, `phone`, `fsc_total`, `fsc_obtained`, `fsc_per`, `fsc_year`, `fsc_board`, `fsc_major`, `ssc_total`, `ssc_obtained`, `ssc_per`, `ssc_year`, `ssc_board`, `ssc_major`) VALUES
(8, 'UW-25-CS-BS-55', 'default.png', 'John Doe', 'Active', 'Computer Science', 'BSCS', '6th', '2021-2025', '2021-09-15', 'Lahore', '2002-05-20', '35201-1234567-1', 'Pakistani', 'Male', 'Robert Doe', 'Robert Doe', '0300-1234567', '75000', '123 Street, Phase 5, DHA, Lahore', 'House 45, Sector B, Islamabad', 'john.doe@example.com', '0321-7654321', '1100', '980', '89.09%', '2021', 'BISE Lahore', 'Pre-Engineering', '1100', '1010', '91.81%', '2019', 'BISE Lahore', 'Science'),
(14, 'UW-25M-AI-BS-053', 'default.png', 'OSAMA', 'Regular', 'Computer Science', 'BS', '6', '2025-2029', '2026-05-10', 'taxila', '2026-05-01', '32131-321321321-4', 'Pakistani', 'Male', 'asdasd', 'asdasd', 'aszxc', 'ASD', 'ASDASD', 'SD5FS4', 'abc@gmail.com', '031065445235', '651', '651', '651', 'ASD21', 'SAD621', 'A6S2D16', '61651', '6231651', 'AS321D65AW', 'A6S16W5D1A', 'AS6D1W6A1D', 'A6SD1A6W5D1'),
(15, 'UW-25M-MTH-BS-001', 'UW-25M-MTH-BS-001.webp', 'ASAD', 'Regular', 'MATH', 'Bs Math', '8', '2022-2027', '2026-05-04', 'taxila', '2026-05-05', '32131-321321321-4', 'Pakistani', 'Male', 'asdasdZXC', 'ZXCZXC', '123130', '31232132', '312AS3D21ASD15A321', 'A3SC84A6W4D32', 'abc@gmail.com', '031065445235', '65464', '654351', '32164', '646546', 'ASDA4', '654654ASD', '65464', '654654', 'AS6D54A6SD', '64654', 'A6S4DA65S4D', 'A6SD4A65SD4'),
(16, 'UW-25M-CYS-MS-001', 'default.png', 'Armaghan AliIIIIIIIIIIIIIIIIII', 'Active', 'ASD', 'MS', 'ASD', 'ASD', '21202-12-02', '', '0001-02-20', '20202121', '20', 'Male', 'AS2D1A2SD1', 'AS2D1A2SD1', 'AS2D1A2SD1', 'AS2D1A2SD1', 'AS2D1A2SD1', 'AS2D1A2SD1', '21212A0SD20@GMAIL.COM', 'A2SD1A20D', '212121', '21212', '21212', '21ASDASD', '21AS2D1A2S1D', 'A2SD1A2S1D', '2121', '21212', 'A2S1D2A1SD', 'A2SD1A2S1D', 'A2SD1A2S1D', 'AS2D1ASD21'),
(17, 'UW-23-AI-MS-001', 'default.png', 'ASHRAF', 'Regular', 'ASD', 'MS', 'ASD', 'ASD', '1212-12-12', 'ASDASDASD', '1212-12-12', '1212132', 'PAKIS', 'Female', 'ASD', 'ASDASD', 'ASDASD', 'ASDASD', 'ASDASD', 'ASDASD', 'ASD@GMAIL.COM', '3031031032', '32132', '321', '32', '132', '132', '132', '1', '321', '321', '321', '321321', '321'),
(18, 'UW-2024M-DS-MS-099', 'default.png', 'Babar ALi', 'Regular', 'Computer Science', 'MS', '7', '2022-2026', '2022-06-12', 'Punjab', '2002-07-12', '374065-1526489-3', 'Pakistan', 'Male', 'Muhammad Ali', 'Muhammad Ali', '03155623489', '100000', 'Abc,123,Model Town', 'Abc,123,Model Town', 'baber123@gmail.com', '0312-56234123', '1050', '900', '85', '2022', 'FBISE', 'FCS', '1100', '1036', '96', '2022', 'FBISE', 'Computer'),
(19, 'UW-24M-AI-BS-077', 'default.png', 'ABUZAR', 'Active', 'AI', 'BS', '5', '2022-2026', '0022-12-12', '1212', '0012-12-12', '12121', 'Pakistani', 'Male', '', 'kj', 'njkn', 'kjn', 'kjn', 'kjnkjn', '121@gmail.com', '021', '13', '12', '132', '13', '21', '321', '321', '32', '132', '1', '321', '3'),
(20, 'UW-25-AI-BS-022', 'default.png', 'ADIL RAZA', 'Regular', 'cS', 'BS', '21', '2025-2029', '1212-12-12', 'sdsa', '0020-12-12', '212131', 'Pakistani', 'Male', 'asdasd', 'ZXCZXC', 'AS2D1A2SD1', 'ASD', 'ASDASD', 'SD5FS4', 'ASD@GMAIL.COM', '0312-56234123', '121', '12', '12', '1', '21', '2', '12', '1', '21', '21', '2', '12'),
(21, 'UW-23M-CS-BS-075', 'default.png', 'Faizan Azhar Lone', 'Regular', 'Computer Science', 'BS', '1', '2023-2027', '2023-04-12', 'chija watni', '2004-02-12', '9125-5326249-8', 'Pakistani', 'Female', 'Azhar Iqbal', 'Azhar Iqbal', '03155626564', '1000000000000000000000', 'new city', 'new city', 'iamalone@gmail.com', '0301562348', '1100', '900', '85', '2022', 'Fbise', 'FSC', '1100', '980', '89', '2020', 'Fbise', 'Bio'),
(22, 'UW-22M-CS-BS-105', 'default.png', 'Hashir ali butt', 'Active', 'CS', 'BS', '8', '2022-2026', '2022-12-12', 'KARAK', '2005-04-12', '123321-1320321-1', 'Pakistani', 'Male', 'MR ABC', 'MR ABC', '0315-4891326', '120000000000000000000000000000', 'abc', 'abc', 'gunarar123@gmail.com', '0312-1526489', '11', '12', '1', '21', '21', '2', '12', '1', '2', '12', '1', '12'),
(23, 'UW-24M-CS-BS-015', 'default.png', 'MUHAMMAD AFNAN', 'Regular', 'Computer Science', 'BS', '1', '2024-2028', '2024-04-12', 'Punjab', '2002-05-12', '34102-4563147-8', 'Pakistani', 'Male', 'MOAZZAM ALI ', 'MOAZZAM ALI', '03002536147', '123456', 'abc/123 new city', 'abc/123 new city', 'afnan123@gmail.com', '0315-2563498', '1100', '900', '85', '2021', 'FBISE', 'FCS', '1100', '895', '78', '2019', 'FBISE', 'Science');

-- --------------------------------------------------------

--
-- Table structure for table `research_submissions`
--

CREATE TABLE `research_submissions` (
  `id` int(11) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `abstract` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `status` enum('Under Review','Accepted','Rejected') DEFAULT 'Under Review',
  `submission_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `research_submissions`
--

INSERT INTO `research_submissions` (`id`, `registration_no`, `title`, `abstract`, `file_path`, `status`, `submission_date`, `created_at`) VALUES
(4, NULL, 'ASD', 'ASD', 'uploads/research/1768822412_Lisense Armaghan.pdf', 'Under Review', '2026-01-19', '2026-01-19 11:33:32'),
(6, NULL, 'GSDFG', 'SDFGSDFG', 'uploads/research/1768822467_Lisense Armaghan.pdf', 'Under Review', '2026-01-19', '2026-01-19 11:34:27'),
(15, 'UW-2024M-DS-MS-099', 'asd', 'asd', 'uploads/research/RES_UW_2024M_DS_MS_099_1778709156.pdf', 'Under Review', '2026-05-13', '2026-05-13 21:52:36'),
(16, 'UW-22M-CS-BS-105', 'Quantum Computing ', 'asdlaksdlkasmd', 'uploads/research/RES_UW_22M_CS_BS_105_1778742275.pdf', 'Accepted', '2026-05-14', '2026-05-14 07:04:35'),
(17, 'UW-24M-CS-BS-015', 'jiojo', 'lmlml', 'uploads/research/RES_UW_24M_CS_BS_015_1778851051.pdf', 'Accepted', '2026-05-15', '2026-05-15 13:17:31');

-- --------------------------------------------------------

--
-- Table structure for table `results`
--

CREATE TABLE `results` (
  `id` int(11) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `subject_name` varchar(100) DEFAULT NULL,
  `credit_hours` int(11) DEFAULT NULL,
  `mid_term` float DEFAULT NULL,
  `final_term` float DEFAULT NULL,
  `sessional` float DEFAULT NULL,
  `total_marks` float DEFAULT NULL,
  `grade` varchar(2) DEFAULT NULL,
  `gp` float DEFAULT NULL,
  `semester_no` int(11) DEFAULT NULL,
  `quiz_marks` float DEFAULT 0,
  `assignment_marks` float DEFAULT 0,
  `project_marks` float DEFAULT 0,
  `viva_marks` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_prices`
--

CREATE TABLE `service_prices` (
  `service_type` varchar(50) NOT NULL,
  `regular_fee` int(11) DEFAULT NULL,
  `urgent_fee` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `service_type` enum('Transcript','Clearance','Degree','Verification') DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `applied_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_settings`
--

CREATE TABLE `service_settings` (
  `id` int(11) NOT NULL,
  `service_name` varchar(50) DEFAULT NULL,
  `regular_fee` decimal(10,2) DEFAULT NULL,
  `urgent_fee` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `registration_no` varchar(50) NOT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `roll_no` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL DEFAULT 'student123',
  `hostel_security_rate` int(11) DEFAULT 0,
  `card_rate` int(11) DEFAULT 0,
  `hostel_due_date` date DEFAULT NULL,
  `monthly_mess_rate` int(11) DEFAULT 12500
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`registration_no`, `student_name`, `father_name`, `roll_no`, `department`, `semester`, `password`, `hostel_security_rate`, `card_rate`, `hostel_due_date`, `monthly_mess_rate`) VALUES
('TEST-001', NULL, NULL, NULL, NULL, NULL, 'student123', 0, 0, NULL, 12500);

-- --------------------------------------------------------

--
-- Table structure for table `student_grades`
--

CREATE TABLE `student_grades` (
  `id` int(11) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `course_title` varchar(100) DEFAULT NULL,
  `faculty_name` varchar(100) DEFAULT NULL,
  `credit_hours` varchar(10) DEFAULT NULL,
  `a1_obtained` float DEFAULT NULL,
  `a1_total` float DEFAULT NULL,
  `a2_obtained` float DEFAULT NULL,
  `a2_total` float DEFAULT NULL,
  `q1_obtained` float DEFAULT NULL,
  `q1_total` float DEFAULT NULL,
  `q2_obtained` float DEFAULT NULL,
  `q2_total` float DEFAULT NULL,
  `q3_obtained` float DEFAULT NULL,
  `q3_total` float DEFAULT NULL,
  `mid_obtained` float DEFAULT NULL,
  `mid_total` float DEFAULT NULL,
  `final_obtained` float DEFAULT NULL,
  `final_total` float DEFAULT NULL,
  `semester` int(11) DEFAULT 7
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_marks`
--

CREATE TABLE `student_marks` (
  `id` int(11) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `obtained_marks` float DEFAULT NULL,
  `is_absent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_marks`
--

INSERT INTO `student_marks` (`id`, `registration_no`, `course_code`, `item_id`, `obtained_marks`, `is_absent`) VALUES
(1, 'UW-2024M-DS-MS-099', 'ALT-927', 4, 21, 0),
(76, 'UW-25M-MTH-BS-001', 'ADP-249', 15, 0, 0),
(78, 'UW-22M-CS-BS-105', 'ADP-249', 15, 37, 0),
(111, 'UW-25M-MTH-BS-001', 'ADP-249', 17, 0, 0),
(112, 'UW-22M-CS-BS-105', 'ADP-249', 17, 22, 0),
(123, 'UW-25M-MTH-BS-001', 'ADP-249', 20, 0, 0),
(124, 'UW-22M-CS-BS-105', 'ADP-249', 20, 15, 0),
(125, 'UW-25M-MTH-BS-001', 'ADP-249', 21, 0, 0),
(126, 'UW-22M-CS-BS-105', 'ADP-249', 21, 20, 0),
(127, 'UW-25M-MTH-BS-001', 'ADP-249', 22, 0, 0),
(128, 'UW-22M-CS-BS-105', 'ADP-249', 22, 14, 0),
(129, 'UW-23M-CS-BS-075', 'TTS-105', 23, 0, 0),
(130, 'UW-24M-CS-BS-015', 'TTS-105', 23, 20, 0),
(131, 'UW-23M-CS-BS-075', 'TTS-105', 24, 0, 0),
(132, 'UW-24M-CS-BS-015', 'TTS-105', 24, 28, 0),
(135, 'UW-23M-CS-BS-075', 'TTS-105', 25, 0, 0),
(136, 'UW-24M-CS-BS-015', 'TTS-105', 25, 40, 0),
(137, 'UW-23M-CS-BS-075', 'TTS-105', 26, 0, 0),
(138, 'UW-24M-CS-BS-015', 'TTS-105', 26, 13.95, 0);

-- --------------------------------------------------------

--
-- Table structure for table `student_results`
--

CREATE TABLE `student_results` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `grade` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_services`
--

CREATE TABLE `student_services` (
  `id` int(11) NOT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Ready for Collection','Completed','Rejected') DEFAULT 'Pending',
  `collection_date` date DEFAULT NULL,
  `voucher_cleared` tinyint(1) DEFAULT 0,
  `applied_on` timestamp NOT NULL DEFAULT current_timestamp(),
  `academic_level` varchar(50) DEFAULT NULL,
  `processing_type` enum('Regular','Urgent') DEFAULT 'Regular',
  `selected_semester` varchar(20) DEFAULT NULL,
  `urgency_fee` int(11) DEFAULT 0,
  `gender` varchar(10) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `current_section` varchar(20) DEFAULT NULL,
  `hod_name` varchar(100) DEFAULT NULL,
  `events_participated` text DEFAULT NULL,
  `distance_km` int(11) DEFAULT 0,
  `mess_type` varchar(50) DEFAULT NULL,
  `total_calculated_fee` int(11) DEFAULT 0,
  `degree_level` enum('BS','MS','PhD') DEFAULT NULL,
  `extra_courses` text DEFAULT NULL,
  `clearance_reason` varchar(100) DEFAULT NULL,
  `library_clearance` varchar(50) DEFAULT 'Pending',
  `hostel_type` enum('Standard','Premium','Luxury') DEFAULT NULL,
  `bus_zone` varchar(50) DEFAULT NULL,
  `shift_timing` varchar(20) DEFAULT NULL,
  `research_title` text DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `emergency_contact` varchar(20) DEFAULT NULL,
  `preferred_roommate` varchar(100) DEFAULT NULL,
  `allergies_medical` text DEFAULT NULL,
  `pickup_point` varchar(100) DEFAULT NULL,
  `arrival_time_slot` varchar(20) DEFAULT NULL,
  `student_cnic` varchar(15) DEFAULT NULL,
  `guardian_contact` varchar(20) DEFAULT NULL,
  `medical_condition` text DEFAULT NULL,
  `mess_preference` enum('Veg','Non-Veg','Both') DEFAULT NULL,
  `stay_duration` varchar(50) DEFAULT NULL,
  `clearance_type` enum('Final','Semester-wise','Migration') DEFAULT 'Final',
  `library_book_return` varchar(10) DEFAULT 'Yes',
  `sports_kit_return` varchar(10) DEFAULT 'Yes',
  `outstanding_dues` varchar(50) DEFAULT 'None',
  `reason_for_leaving` text DEFAULT NULL,
  `department_hod` varchar(100) DEFAULT NULL,
  `transcript_type` enum('Official','Interim','Duplicate','Revised') DEFAULT 'Official',
  `academic_session` varchar(20) DEFAULT NULL,
  `total_cgpa` decimal(3,2) DEFAULT NULL,
  `passing_year` int(11) DEFAULT NULL,
  `attachment_check` text DEFAULT NULL,
  `doc_cnic` varchar(255) DEFAULT NULL,
  `doc_matric` varchar(255) DEFAULT NULL,
  `doc_inter` varchar(255) DEFAULT NULL,
  `doc_clearance` varchar(255) DEFAULT NULL,
  `transport_route` varchar(100) DEFAULT NULL,
  `bus_number` varchar(20) DEFAULT NULL,
  `doc_transport_voucher` varchar(255) DEFAULT NULL,
  `doc_hostel_voucher` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_services`
--

INSERT INTO `student_services` (`id`, `registration_no`, `student_name`, `service_type`, `details`, `status`, `collection_date`, `voucher_cleared`, `applied_on`, `academic_level`, `processing_type`, `selected_semester`, `urgency_fee`, `gender`, `father_name`, `current_section`, `hod_name`, `events_participated`, `distance_km`, `mess_type`, `total_calculated_fee`, `degree_level`, `extra_courses`, `clearance_reason`, `library_clearance`, `hostel_type`, `bus_zone`, `shift_timing`, `research_title`, `blood_group`, `emergency_contact`, `preferred_roommate`, `allergies_medical`, `pickup_point`, `arrival_time_slot`, `student_cnic`, `guardian_contact`, `medical_condition`, `mess_preference`, `stay_duration`, `clearance_type`, `library_book_return`, `sports_kit_return`, `outstanding_dues`, `reason_for_leaving`, `department_hod`, `transcript_type`, `academic_session`, `total_cgpa`, `passing_year`, `attachment_check`, `doc_cnic`, `doc_matric`, `doc_inter`, `doc_clearance`, `transport_route`, `bus_number`, `doc_transport_voucher`, `doc_hostel_voucher`) VALUES
(55, 'guest', 'ARMAGHAN', 'Transcript', NULL, 'Pending', NULL, 0, '2026-05-12 22:18:18', NULL, 'Regular', '5', 0, 'Male', NULL, NULL, NULL, NULL, 0, NULL, 2000, '', NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '111111111111', NULL, NULL, NULL, NULL, 'Final', 'Yes', 'Yes', 'None', NULL, NULL, 'Official', '21022-2025', NULL, NULL, NULL, 'guest_doc_cnic_1778624298.png', 'guest_doc_matric_1778624298.png', 'guest_doc_inter_1778624298.png', 'guest_doc_clearance_1778624298.png', NULL, NULL, NULL, NULL),
(56, 'guest', 'Student', 'Clearance', NULL, 'Pending', NULL, 0, '2026-05-12 22:21:18', NULL, 'Regular', NULL, 0, 'Male', NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2222222', NULL, NULL, NULL, NULL, 'Final', 'Yes', 'Yes', '1200', 'ok yes', 'ABC', 'Official', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'guest_doc_clearance_1778624478.png', NULL, NULL, NULL, NULL),
(57, 'guest', 'Student', 'Hostel', NULL, 'Pending', NULL, 0, '2026-05-12 22:21:43', NULL, 'Regular', NULL, 0, 'Male', '333333333333333', NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'Pending', 'Premium', NULL, NULL, NULL, 'A-', NULL, NULL, NULL, NULL, NULL, '33333333333333', '03033030', NULL, 'Veg', NULL, 'Final', 'Yes', 'Yes', 'None', NULL, NULL, 'Official', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'guest_doc_hostel_voucher_1778624503.png'),
(58, 'guest', 'Student', 'Transport', NULL, 'Pending', NULL, 0, '2026-05-12 22:22:05', NULL, 'Regular', NULL, 0, 'Male', NULL, NULL, NULL, NULL, 0, NULL, 0, NULL, NULL, NULL, 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '32030', NULL, '444444444444444', NULL, NULL, NULL, NULL, 'Final', 'Yes', 'Yes', 'None', NULL, NULL, 'Official', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Route A - Rawalpindi', '1010', 'guest_doc_transport_voucher_1778624525.png', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_surveys`
--

CREATE TABLE `student_surveys` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `cnic` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `university` varchar(100) DEFAULT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `degree` varchar(50) DEFAULT NULL,
  `discipline` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_pref` varchar(50) DEFAULT NULL,
  `internet_access` varchar(50) DEFAULT NULL,
  `load_shedding` varchar(50) DEFAULT NULL,
  `satisfaction` varchar(50) DEFAULT NULL,
  `suggestions` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_surveys`
--

INSERT INTO `student_surveys` (`id`, `full_name`, `father_name`, `cnic`, `whatsapp`, `university`, `registration_no`, `degree`, `discipline`, `email`, `address`, `contact_pref`, `internet_access`, `load_shedding`, `satisfaction`, `suggestions`, `submitted_at`) VALUES
(1, 'asdasd', 'sdf', '37406-4647736-8', 'sdf', 'University of Wah', 'asd', 'BS (Undergraduate)', 'cvb', 'aldrago696@gmail.com', 'fgh', 'Email', 'Friend\'s house', '6-12 hours per day', 'Satisfied', 'asdasdasd', '2026-04-08 06:25:32'),
(2, 'Armaghan Ali', 'asdasdZXC', '32131-321321321-4', '1313030', 'University of Wah', '2121', 'MS / MPhil', 'cs', 'abc@gmail.com', '3a2sd', 'Mobile Phone', 'Friend\'s house', '0-6 hours per day', 'Somewhat', 'asdasdasd', '2026-05-13 00:16:43'),
(3, 'Faizan Azhar Lone', 'Azhar Iqbal', '151515115151', '0313123121', 'University of Wah', 'UW-22-CS-BS-075', 'BS (Undergraduate)', 'CS', 'iamalone@gmail.com', 'NEW CITY', 'Postal Mail', 'Friend\'s house', 'More than 12 hours per day', 'Not Satisfied', 'asdasdasdkmaks;mdkm', '2026-05-14 06:23:41'),
(4, 'MUHAMMAD AFNAN', 'MOAZZAM ALI ', '34102-4563147-8', '03015151', 'University of Wah', '015', 'BS (Undergraduate)', 'CS', 'afnan123@gmail.com', 'HVJVJV', 'Mobile Phone', 'Internet Cafe', 'None', 'Satisfied', 'LKNLKNLKJNLNL', '2026-05-15 13:36:39');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `cnic` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `address` text NOT NULL,
  `designation` varchar(100) NOT NULL,
  `department` enum('CS','AI','CYS','DS','PHY','MTH','PSG','ENG') NOT NULL,
  `joining_date` date NOT NULL,
  `employment_type` enum('Permanent','Contract','Visiting') NOT NULL,
  `role` enum('Teacher','HOD','Coordinator') DEFAULT 'Teacher',
  `status` enum('Active','Inactive','On Leave') DEFAULT 'Active',
  `salary` decimal(10,2) DEFAULT NULL,
  `highest_degree` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `experience_years` int(2) NOT NULL,
  `research_interests` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `employee_id`, `full_name`, `email`, `phone`, `cnic`, `dob`, `gender`, `address`, `designation`, `department`, `joining_date`, `employment_type`, `role`, `status`, `salary`, `highest_degree`, `specialization`, `experience_years`, `research_interests`, `bio`, `created_at`) VALUES
(1, '123123', 'ABCD', 'abc@gmail.com', '321321321', '32131-321321321-4', '1221-12-12', 'Male', '3a2sd', 'a32sd232a1sd321', 'AI', '1221-12-12', 'Visiting', 'Teacher', 'Active', -1323.00, 's1da2d1s', 'asdas', -132, 'asdasd', 'asdasd', '2026-05-09 02:18:06'),
(2, '101010', 'Muhammad Ali', 'mu123@gmail.com', '03152648965', '34160-15206489-7', '2300-05-12', 'Male', 'absc.cscasc/ascasc', 'HOD', 'AI', '2022-12-12', 'Contract', 'HOD', 'Active', 160000.00, 'MS', 'AI', 15, 'ABCd', 'ASDASDASD', '2026-05-10 22:31:01'),
(3, '123455', 'XYZ', 'ASD@GMAIL.COM', '1321', '1213', '1202-12-12', 'Male', '12A1SD2A1SD', 'ASDASD', 'AI', '1212-12-12', 'Permanent', 'Coordinator', 'Active', 212131.00, 'ASDASD', 'ASDASD', 212, 'ASDASDASD31', 'ASDASDASD', '2026-05-12 23:30:23'),
(4, 'T-102-105', 'AZHAR IQBAL', 'TEACHER@GMAIL.COM', '0314565659556', '31750-4526251-5', '1926-05-12', 'Male', 'NEW CITY', 'PROFESSOR', 'CS', '2021-04-12', 'Permanent', 'Teacher', 'Active', 99999999.99, 'PHD', 'MACHINE LEARNING', 50, 'ABCDEF', 'ASDASDASDASD', '2026-05-14 06:19:28'),
(5, 't-2015', 'FAIZAN AZHAR LONE', 'abc@mail.com', '0312654456454', '313131-1321313-4', '2005-05-12', 'Female', 'abcasdasd', 'Professor', 'CS', '2024-02-12', 'Permanent', 'Teacher', 'Active', 99999999.99, 'phd', 'ML', 5, 'asdasd', 'adfsdfdf', '2026-05-15 13:20:40');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_courses`
--

CREATE TABLE `teacher_courses` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `teacher_name` varchar(100) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `day_name` enum('Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `instructor_name` varchar(100) DEFAULT NULL,
  `location` varchar(50) DEFAULT NULL,
  `type` enum('Class','Lab','Break') DEFAULT 'Class',
  `color_code` varchar(7) DEFAULT '#008080'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transport_vouchers`
--

CREATE TABLE `transport_vouchers` (
  `id` int(11) NOT NULL,
  `challan_no` varchar(50) DEFAULT NULL,
  `registration_no` varchar(50) DEFAULT NULL,
  `student_name` varchar(100) DEFAULT NULL,
  `route_info` varchar(100) DEFAULT NULL,
  `semester_name` varchar(50) DEFAULT NULL,
  `transport_fee` decimal(10,2) DEFAULT NULL,
  `maintenance_fund` decimal(10,2) DEFAULT NULL,
  `total_payable` decimal(10,2) DEFAULT NULL,
  `status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `registration_no` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `registration_no`, `password`) VALUES
(4, 'UW-25M-AI-BS-053', '123456'),
(5, 'UW-25M-MTH-BS-001', '123456'),
(6, 'UW-25M-CYS-MS-001', '123456'),
(7, 'UW-23-AI-MS-001', '123456'),
(8, '123123', 'teacher123'),
(9, 'UW-2024M-DS-MS-099', '123456'),
(10, '101010', 'teacher123'),
(11, 'UW-24M-AI-BS-077', '123456'),
(12, 'UW-25-AI-BS-022', '123456'),
(13, '123455', 'teacher123'),
(14, 'UW-23M-CS-BS-075', '123456'),
(15, 'T-102-105', 'teacher123'),
(16, 'UW-22M-CS-BS-105', '123456'),
(17, 'UW-24M-CS-BS-015', '123456'),
(18, 't-2015', 'teacher123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dept_id` (`dept_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `complaint_id` (`complaint_id`);

--
-- Indexes for table `course_assignments`
--
ALTER TABLE `course_assignments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dept_code` (`dept_code`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_datesheet`
--
ALTER TABLE `exam_datesheet`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reg_no` (`registration_no`);

--
-- Indexes for table `faculty_feedback`
--
ALTER TABLE `faculty_feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fee_uploads`
--
ALTER TABLE `fee_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voucher_id` (`voucher_id`);

--
-- Indexes for table `fee_vouchers`
--
ALTER TABLE `fee_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_no` (`voucher_no`);

--
-- Indexes for table `grading_items`
--
ALTER TABLE `grading_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hostel_mess_vouchers`
--
ALTER TABLE `hostel_mess_vouchers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hostel_vouchers`
--
ALTER TABLE `hostel_vouchers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `profile`
--
ALTER TABLE `profile`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `research_submissions`
--
ALTER TABLE `research_submissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `results`
--
ALTER TABLE `results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_prices`
--
ALTER TABLE `service_prices`
  ADD PRIMARY KEY (`service_type`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_settings`
--
ALTER TABLE `service_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_name` (`service_name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`registration_no`);

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reg_no` (`registration_no`);

--
-- Indexes for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`registration_no`,`course_code`,`item_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `student_results`
--
ALTER TABLE `student_results`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_services`
--
ALTER TABLE `student_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_surveys`
--
ALTER TABLE `student_surveys`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cnic` (`cnic`);

--
-- Indexes for table `teacher_courses`
--
ALTER TABLE `teacher_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transport_vouchers`
--
ALTER TABLE `transport_vouchers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_no` (`registration_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_assignments`
--
ALTER TABLE `course_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_datesheet`
--
ALTER TABLE `exam_datesheet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculty_feedback`
--
ALTER TABLE `faculty_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `fee_uploads`
--
ALTER TABLE `fee_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_vouchers`
--
ALTER TABLE `fee_vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grading_items`
--
ALTER TABLE `grading_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `hostel_mess_vouchers`
--
ALTER TABLE `hostel_mess_vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostel_vouchers`
--
ALTER TABLE `hostel_vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `profile`
--
ALTER TABLE `profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `research_submissions`
--
ALTER TABLE `research_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `results`
--
ALTER TABLE `results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `service_settings`
--
ALTER TABLE `service_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `student_marks`
--
ALTER TABLE `student_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `student_results`
--
ALTER TABLE `student_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_services`
--
ALTER TABLE `student_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `student_surveys`
--
ALTER TABLE `student_surveys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `teacher_courses`
--
ALTER TABLE `teacher_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transport_vouchers`
--
ALTER TABLE `transport_vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `exam_datesheet`
--
ALTER TABLE `exam_datesheet`
  ADD CONSTRAINT `exam_datesheet_ibfk_1` FOREIGN KEY (`registration_no`) REFERENCES `students` (`registration_no`);

--
-- Constraints for table `fee_uploads`
--
ALTER TABLE `fee_uploads`
  ADD CONSTRAINT `fee_uploads_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `fee_vouchers` (`id`);

--
-- Constraints for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD CONSTRAINT `student_grades_ibfk_1` FOREIGN KEY (`registration_no`) REFERENCES `students` (`registration_no`);

--
-- Constraints for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD CONSTRAINT `student_marks_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `grading_items` (`id`);

--
-- Constraints for table `teacher_courses`
--
ALTER TABLE `teacher_courses`
  ADD CONSTRAINT `teacher_courses_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course_assignments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
