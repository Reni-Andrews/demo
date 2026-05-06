-- --------------------------------------------------------
-- Database Structure for Attendance Management System
-- Compatible with Automatic Batch Rollover Logic
-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";

--
-- 1. Create Database
--
CREATE DATABASE IF NOT EXISTS `attendance_db`;
USE `attendance_db`;

-- --------------------------------------------------------

--
-- 2. Table structure for `students`
--
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reg_no` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `year_of_study` int(1) NOT NULL COMMENT '1, 2, or 3',
  `password` varchar(255) NOT NULL DEFAULT '1234',
  `attendance_percent` float DEFAULT 0,
  `profile_image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reg_no` (`reg_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- DUMPING INITIAL STUDENT DATA (55 Students per Batch)
--

-- 3rd Year Students (Batch 2023 - BU231701 to BU231755)
-- These will be DELETED when you click "Process Graduation"
INSERT INTO `students` (`reg_no`, `name`, `year_of_study`) VALUES 
('BU231701', 'Student 23-01', 3), ('BU231702', 'Student 23-02', 3), ('BU231703', 'Student 23-03', 3), ('BU231704', 'Student 23-04', 3), ('BU231705', 'Student 23-05', 3),
('BU231706', 'Student 23-06', 3), ('BU231707', 'Student 23-07', 3), ('BU231708', 'Student 23-08', 3), ('BU231709', 'Student 23-09', 3), ('BU231710', 'Student 23-10', 3),
('BU231711', 'Student 23-11', 3), ('BU231712', 'Student 23-12', 3), ('BU231713', 'Student 23-13', 3), ('BU231714', 'Student 23-14', 3), ('BU231715', 'Student 23-15', 3),
('BU231716', 'Student 23-16', 3), ('BU231717', 'Student 23-17', 3), ('BU231718', 'Student 23-18', 3), ('BU231719', 'Student 23-19', 3), ('BU231720', 'Student 23-20', 3),
('BU231721', 'Student 23-21', 3), ('BU231722', 'Student 23-22', 3), ('BU231723', 'Student 23-23', 3), ('BU231724', 'Student 23-24', 3), ('BU231725', 'Student 23-25', 3),
('BU231726', 'Student 23-26', 3), ('BU231727', 'Student 23-27', 3), ('BU231728', 'Student 23-28', 3), ('BU231729', 'Student 23-29', 3), ('BU231730', 'Student 23-30', 3),
('BU231731', 'Student 23-31', 3), ('BU231732', 'Student 23-32', 3), ('BU231733', 'Student 23-33', 3), ('BU231734', 'Student 23-34', 3), ('BU231735', 'Student 23-35', 3),
('BU231736', 'Student 23-36', 3), ('BU231737', 'Student 23-37', 3), ('BU231738', 'Student 23-38', 3), ('BU231739', 'Student 23-39', 3), ('BU231740', 'Student 23-40', 3),
('BU231741', 'Student 23-41', 3), ('BU231742', 'Student 23-42', 3), ('BU231743', 'Student 23-43', 3), ('BU231744', 'Student 23-44', 3), ('BU231745', 'Student 23-45', 3),
('BU231746', 'Student 23-46', 3), ('BU231747', 'Student 23-47', 3), ('BU231748', 'Student 23-48', 3), ('BU231749', 'Student 23-49', 3), ('BU231750', 'Student 23-50', 3),
('BU231751', 'Student 23-51', 3), ('BU231752', 'Student 23-52', 3), ('BU231753', 'Student 23-53', 3), ('BU231754', 'Student 23-54', 3), ('BU231755', 'Student 23-55', 3);

-- 2nd Year Students (Batch 2024 - BU241701 to BU241755)
-- These will become 3rd Years
INSERT INTO `students` (`reg_no`, `name`, `year_of_study`) VALUES 
('BU241701', 'Student 24-01', 2), ('BU241702', 'Student 24-02', 2), ('BU241703', 'Student 24-03', 2), ('BU241704', 'Student 24-04', 2), ('BU241705', 'Student 24-05', 2),
('BU241706', 'Student 24-06', 2), ('BU241707', 'Student 24-07', 2), ('BU241708', 'Student 24-08', 2), ('BU241709', 'Student 24-09', 2), ('BU241710', 'Student 24-10', 2),
('BU241711', 'Student 24-11', 2), ('BU241712', 'Student 24-12', 2), ('BU241713', 'Student 24-13', 2), ('BU241714', 'Student 24-14', 2), ('BU241715', 'Student 24-15', 2),
('BU241716', 'Student 24-16', 2), ('BU241717', 'Student 24-17', 2), ('BU241718', 'Student 24-18', 2), ('BU241719', 'Student 24-19', 2), ('BU241720', 'Student 24-20', 2),
('BU241721', 'Student 24-21', 2), ('BU241722', 'Student 24-22', 2), ('BU241723', 'Student 24-23', 2), ('BU241724', 'Student 24-24', 2), ('BU241725', 'Student 24-25', 2),
('BU241726', 'Student 24-26', 2), ('BU241727', 'Student 24-27', 2), ('BU241728', 'Student 24-28', 2), ('BU241729', 'Student 24-29', 2), ('BU241730', 'Student 24-30', 2),
('BU241731', 'Student 24-31', 2), ('BU241732', 'Student 24-32', 2), ('BU241733', 'Student 24-33', 2), ('BU241734', 'Student 24-34', 2), ('BU241735', 'Student 24-35', 2),
('BU241736', 'Student 24-36', 2), ('BU241737', 'Student 24-37', 2), ('BU241738', 'Student 24-38', 2), ('BU241739', 'Student 24-39', 2), ('BU241740', 'Student 24-40', 2),
('BU241741', 'Student 24-41', 2), ('BU241742', 'Student 24-42', 2), ('BU241743', 'Student 24-43', 2), ('BU241744', 'Student 24-44', 2), ('BU241745', 'Student 24-45', 2),
('BU241746', 'Student 24-46', 2), ('BU241747', 'Student 24-47', 2), ('BU241748', 'Student 24-48', 2), ('BU241749', 'Student 24-49', 2), ('BU241750', 'Student 24-50', 2),
('BU241751', 'Student 24-51', 2), ('BU241752', 'Student 24-52', 2), ('BU241753', 'Student 24-53', 2), ('BU241754', 'Student 24-54', 2), ('BU241755', 'Student 24-55', 2);

-- 1st Year Students (Batch 2025 - BU251701 to BU251755)
-- These will become 2nd Years
INSERT INTO `students` (`reg_no`, `name`, `year_of_study`) VALUES 
('BU251701', 'Student 25-01', 1), ('BU251702', 'Student 25-02', 1), ('BU251703', 'Student 25-03', 1), ('BU251704', 'Student 25-04', 1), ('BU251705', 'Student 25-05', 1),
('BU251706', 'Student 25-06', 1), ('BU251707', 'Student 25-07', 1), ('BU251708', 'Student 25-08', 1), ('BU251709', 'Student 25-09', 1), ('BU251710', 'Student 25-10', 1),
('BU251711', 'Student 25-11', 1), ('BU251712', 'Student 25-12', 1), ('BU251713', 'Student 25-13', 1), ('BU251714', 'Student 25-14', 1), ('BU251715', 'Student 25-15', 1),
('BU251716', 'Student 25-16', 1), ('BU251717', 'Student 25-17', 1), ('BU251718', 'Student 25-18', 1), ('BU251719', 'Student 25-19', 1), ('BU251720', 'Student 25-20', 1),
('BU251721', 'Student 25-21', 1), ('BU251722', 'Student 25-22', 1), ('BU251723', 'Student 25-23', 1), ('BU251724', 'Student 25-24', 1), ('BU251725', 'Student 25-25', 1),
('BU251726', 'Student 25-26', 1), ('BU251727', 'Student 25-27', 1), ('BU251728', 'Student 25-28', 1), ('BU251729', 'Student 25-29', 1), ('BU251730', 'Student 25-30', 1),
('BU251731', 'Student 25-31', 1), ('BU251732', 'Student 25-32', 1), ('BU251733', 'Student 25-33', 1), ('BU251734', 'Student 25-34', 1), ('BU251735', 'Student 25-35', 1),
('BU251736', 'Student 25-36', 1), ('BU251737', 'Student 25-37', 1), ('BU251738', 'Student 25-38', 1), ('BU251739', 'Student 25-39', 1), ('BU251740', 'Student 25-40', 1),
('BU251741', 'Student 25-41', 1), ('BU251742', 'Student 25-42', 1), ('BU251743', 'Student 25-43', 1), ('BU251744', 'Student 25-44', 1), ('BU251745', 'Student 25-45', 1),
('BU251746', 'Student 25-46', 1), ('BU251747', 'Student 25-47', 1), ('BU251748', 'Student 25-48', 1), ('BU251749', 'Student 25-49', 1), ('BU251750', 'Student 25-50', 1),
('BU251751', 'Student 25-51', 1), ('BU251752', 'Student 25-52', 1), ('BU251753', 'Student 25-53', 1), ('BU251754', 'Student 25-54', 1), ('BU251755', 'Student 25-55', 1);

-- --------------------------------------------------------

--
-- 3. Table structure for `faculty`
--
CREATE TABLE `faculty` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping Real Faculty Data
--
INSERT INTO `faculty` (`id`, `name`, `username`, `password`, `image`) VALUES
(1, 'Dr. R. Sandrilla', 'sandrilla', 'admin123', 'T100501.jpg'),
(2, 'Prof. Vijay', 'vijay', 'admin123', 'vijay.jpg'),
(3, 'Prof. Mariam', 'mariam', 'admin123', 'mariam.jpg'),
(4, 'Prof. Bevina', 'bevina', 'admin123', 'bevina.jpg'),
(5, 'Mr. H. Irfan', 'irfan', 'admin123', 'WhatsApp Image 2025-07-04 at 10.58.59_4643a762.jpg'),
(6, 'Mr. S. Kumaresan', 'kumaresan', 'admin123', 'T251704.jpg');

-- --------------------------------------------------------

--
-- 4. Table structure for `attendance_logs`
--
CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `log_date` date NOT NULL,
  `status` enum('Present','Absent') NOT NULL DEFAULT 'Absent',
  `reason` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_logs_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 5. Table structure for `assignments`
--
CREATE TABLE `assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `faculty_id` (`faculty_id`),
  CONSTRAINT `fk_assign_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assign_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- 6. Table structure for `faculty_deadlines`
--
CREATE TABLE `faculty_deadlines` (
  `faculty_id` int(11) NOT NULL,
  `deadline_date` datetime NOT NULL,
  PRIMARY KEY (`faculty_id`),
  CONSTRAINT `fk_deadline_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

---------------------------------------------
USE `attendance_db`;

CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `applied_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;