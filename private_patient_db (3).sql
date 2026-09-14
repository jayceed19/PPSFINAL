-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Sep 14, 2026 at 05:41 AM
-- Server version: 5.6.16
-- PHP Version: 5.5.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `private_patient_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `role` varchar(30) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=185 ;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `username`, `full_name`, `role`, `action`, `description`, `created_at`) VALUES
(26, 1, 'admin', 'System Administrator', 'Administrator', 'LOGOUT', 'User logged out of the system.', '2026-09-08 00:12:55'),
(27, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGIN', 'User logged into the system.', '2026-09-08 00:13:05'),
(28, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-08 00:13:55'),
(29, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'CONSULTATION', 'Added consultation: 0010 - CLARA DELA CRUZ PELAGIOSSS - Consultation #2', '2026-09-08 00:17:36'),
(30, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'PRESCRIPTION', 'Added prescription: 0010 - CLARA DELA CRUZ PELAGIOSSS - 3 medicine(s) - 2026-09-08', '2026-09-08 00:18:58'),
(31, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LABORATORY', 'Added laboratory record: 0010 - CLARA DELA CRUZ PELAGIOSSS - 9 test(s) - 2026-09-08', '2026-09-08 00:22:39'),
(32, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'PRESCRIPTION', 'Added prescription: 0010 - CLARA DELA CRUZ PELAGIOSSS - 1 medicine(s) - 2026-09-08', '2026-09-08 00:41:11'),
(33, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'PRESCRIPTION', 'Deleted prescription medicine: 0010 - CLARA DELA CRUZ PELAGIOSSS - DSFDSDSFDJFJJGGJJFDJFDJFDSJFHSDSDFHSDHFJSDHFDJHFSDJHFSIDFSDHFSFSDFSF - 2026-09-08', '2026-09-08 00:42:01'),
(34, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'PRESCRIPTION', 'Updated prescription medicine: 0010 - CLARA DELA CRUZ PELAGIOSSS - PARACETAMOL - 2026-09-08', '2026-09-08 00:49:37'),
(35, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'DELETE_PRESCRIPTION', 'Deleted prescription medicine: 0010 - CLARA DELA CRUZ PELAGIOSSS - PARACETAMOL - 2026-09-08', '2026-09-08 00:49:49'),
(36, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGOUT', 'User logged out of the system.', '2026-09-08 01:59:27'),
(37, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-08 01:59:32'),
(38, 1, 'admin', 'System Administrator', 'Administrator', 'LOGOUT', 'User logged out of the system.', '2026-09-08 02:00:41'),
(39, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGIN', 'User logged into the system.', '2026-09-08 02:00:45'),
(40, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'ADD_PATIENT', 'Added patient: 0001 - JUAN DELA CRUZ TEST', '2026-09-08 02:07:38'),
(41, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'CONSULTATION', 'Added consultation: 0001 - JUAN DELA CRUZ TEST - Consultation #1', '2026-09-08 02:09:02'),
(42, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'PRESCRIPTION', 'Added prescription: 0001 - JUAN DELA CRUZ TEST - 1 medicine(s) - 2026-09-08', '2026-09-08 02:17:47'),
(43, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LABORATORY', 'Added laboratory record: 0001 - JUAN DELA CRUZ TEST - 20 test(s) - 2026-09-08', '2026-09-08 02:21:58'),
(44, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LABORATORY', 'Updated laboratory record: 0001 - JUAN DELA CRUZ TEST - APPEARANCE - 2026-09-08', '2026-09-08 02:22:25'),
(45, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-08 02:25:29'),
(46, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-08 02:43:32'),
(47, 1, 'admin', 'System Administrator', 'Administrator', 'MEDICAL_CERTIFICATE', 'Added medical certificate: 0001 - JUAN DELA CRUZ TEST - 2026-09-08', '2026-09-08 03:07:19'),
(48, 1, 'admin', 'System Administrator', 'Administrator', 'MEDICAL_CERTIFICATE', 'Added medical certificate: 0001 - JUAN DELA CRUZ TEST - 2026-09-08', '2026-09-08 03:14:29'),
(49, 1, 'admin', 'System Administrator', 'Administrator', 'LOGOUT', 'User logged out of the system.', '2026-09-08 03:45:19'),
(50, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGIN', 'User logged into the system.', '2026-09-08 03:45:31'),
(51, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGOUT', 'User logged out of the system.', '2026-09-08 03:45:45'),
(52, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-08 03:45:49'),
(53, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'EDIT_PATIENT', 'Updated patient: 0001 - JUAN DELA CRUZ TEST', '2026-09-08 05:31:51'),
(54, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGOUT', 'User logged out of the system.', '2026-09-08 05:47:23'),
(55, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-08 05:47:27'),
(56, 1, 'admin', 'System Administrator', 'Administrator', 'DATABASE_RESTORE', 'Restored database from backup file: private_patient_db_backup_2026-09-08_07-50-08.sql', '2026-09-08 05:56:07'),
(57, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0001 - JUAN DELA CRUZ TEST - 1 medicine(s) - 2026-09-08', '2026-09-08 06:41:54'),
(58, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Updated prescription medicine: 0001 - JUAN DELA CRUZ TEST - SDSLDHDSJGDFHKDSJFOJFKOSDJFPKFZKLVCXKVXKCVJXZKLCVJKXSCZJVKLXCJVKLJDFDSJKFHSDJFNSJDFHJSDHNDASDASFKJLFGGJKSNDFJKNKFJDFBDJSNLKGN;LNVKLFDNVJSD;VMDSVJK - 2026-09-08', '2026-09-08 06:42:44'),
(59, 1, 'admin', 'System Administrator', 'Administrator', 'DELETE_PRESCRIPTION', 'Deleted prescription medicine: 0001 - JUAN DELA CRUZ TEST - SDSLDHDSJGDFHKDSJFOJFKOSDJFPKFZKLVCXKVXKCVJXZKLCVJKXSCZJVKLXCJVKLJDFDSJKFHSDJFNSJDFHJSDHNDASDASFKJLFGGJKSNDFJKNKFJDFBDJSNLKGN;LNVKLFDNVJSD;VMDSVJK - 2026-09-08', '2026-09-08 06:43:00'),
(60, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_PATIENT', 'Added patient: 0013 - JARED ISIP MANALANSAN', '2026-09-08 06:51:35'),
(61, 1, 'admin', 'System Administrator', 'Administrator', 'CONSULTATION', 'Added consultation: 0013 - JARED ISIP MANALANSAN - Consultation #1', '2026-09-08 06:56:18'),
(62, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0013 - JARED ISIP MANALANSAN - 2 medicine(s) - 2026-09-08', '2026-09-08 07:01:45'),
(63, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_PATIENT', 'Added patient: 0014 - MARIA CLARA DELA CRUZ SANTOS', '2026-09-08 07:41:47'),
(64, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_PATIENT', 'Added patient: 0002 - MARIA CLARA DELA CRUZ DELA CRUZ', '2026-09-08 08:02:37'),
(65, 1, 'admin', 'System Administrator', 'Administrator', 'LOGOUT', 'User logged out of the system.', '2026-09-08 08:30:35'),
(66, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGIN', 'User logged into the system.', '2026-09-08 08:30:53'),
(67, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGOUT', 'User logged out of the system.', '2026-09-08 08:31:00'),
(68, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-08 08:31:15'),
(69, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0001 - JUAN DELA CRUZ TEST - 1 medicine(s) - 2026-09-08', '2026-09-08 08:45:59'),
(70, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0001 - JUAN DELA CRUZ TEST - 1 medicine(s) - 2026-09-08', '2026-09-08 08:49:23'),
(71, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-09 00:05:27'),
(72, 1, 'admin', 'System Administrator', 'Administrator', 'DEACTIVATE_MEDICINE', 'Medicine ID 16: AMBROXOL - 30 MG - TABLET', '2026-09-09 03:17:53'),
(73, 1, 'admin', 'System Administrator', 'Administrator', 'ACTIVATE_MEDICINE', 'Medicine ID 16: AMBROXOL - 30 MG - TABLET', '2026-09-09 03:18:17'),
(74, 1, 'admin', 'System Administrator', 'Administrator', 'DELETE_PRESCRIPTION', 'Deleted prescription medicine: 0001 - JUAN DELA CRUZ TEST - CETIRIZINE - 2026-09-08', '2026-09-09 03:19:38'),
(75, 1, 'admin', 'System Administrator', 'Administrator', 'DELETE_PRESCRIPTION', 'Deleted prescription medicine: 0001 - JUAN DELA CRUZ TEST - PARACETAMOL - 2026-09-08', '2026-09-09 03:19:40'),
(76, 1, 'admin', 'System Administrator', 'Administrator', 'DELETE_PRESCRIPTION', 'Deleted prescription medicine: 0001 - JUAN DELA CRUZ TEST - ASCORBIC ACID - 2026-09-08', '2026-09-09 03:19:41'),
(77, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0001 - JUAN DELA CRUZ TEST - 1 medicine(s) - 2026-09-09', '2026-09-09 03:22:47'),
(78, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0001 - JUAN DELA CRUZ TEST - 1 medicine(s) - 2026-09-09', '2026-09-09 03:39:58'),
(79, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Updated prescription medicine: 0001 - JUAN DELA CRUZ TEST - AMBROXOLSDASDASDASFSASFDFVERVFSDFVTBWEARVFCSJFJGDJKFGJSDF;JKSD;FJKJKSDHHSFPUIFUIHGFJKSD - 2026-09-09', '2026-09-09 03:40:34'),
(80, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Updated prescription medicine: 0001 - JUAN DELA CRUZ TEST - AMBROXOL - 2026-09-09', '2026-09-09 03:41:01'),
(81, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Updated prescription medicine: 0001 - JUAN DELA CRUZ TEST - AMBROXOLSSSS - 2026-09-09', '2026-09-09 03:51:04'),
(82, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Updated prescription medicine: 0001 - JUAN DELA CRUZ TEST - AMBROXOLSSSS - 2026-09-09', '2026-09-09 03:51:22'),
(83, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Updated prescription medicine: 0001 - JUAN DELA CRUZ TEST - AMBROXOL - 2026-09-09', '2026-09-09 03:51:41'),
(84, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Updated prescription medicine: 0001 - JUAN DELA CRUZ TEST - AMBROXOL - 2026-09-09', '2026-09-09 05:03:30'),
(85, 1, 'admin', 'System Administrator', 'Administrator', 'DELETE_PRESCRIPTION', 'Deleted prescription medicine: 0001 - JUAN DELA CRUZ TEST - AMBROXOL - 2026-09-09', '2026-09-09 05:03:34'),
(86, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0001 - JUAN DELA CRUZ TEST - 2 medicine(s) - 2026-09-09', '2026-09-09 05:07:26'),
(87, 1, 'admin', 'System Administrator', 'Administrator', 'DELETE_PRESCRIPTION', 'Deleted prescription medicine: 0001 - JUAN DELA CRUZ TEST - AMLODIPINE - 2026-09-09', '2026-09-09 05:08:06'),
(88, 1, 'admin', 'System Administrator', 'Administrator', 'DELETE_PRESCRIPTION', 'Deleted prescription medicine: 0001 - JUAN DELA CRUZ TEST - CEPHALEXIN - 2026-09-09', '2026-09-09 05:08:08'),
(89, 1, 'admin', 'System Administrator', 'Administrator', 'LOGOUT', 'User logged out of the system.', '2026-09-09 07:50:32'),
(90, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-09 07:50:53'),
(91, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-09 23:57:40'),
(92, 1, 'admin', 'System Administrator', 'Administrator', 'CONSULTATION', 'Added consultation: 0002 - MARIA CLARA DELA CRUZ DELA CRUZ - Consultation #1', '2026-09-10 00:07:46'),
(93, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0002 - MARIA CLARA DELA CRUZ DELA CRUZ - 1 medicine(s) - 2026-09-10', '2026-09-10 00:09:17'),
(94, 1, 'admin', 'System Administrator', 'Administrator', 'MEDICAL_CERTIFICATE', 'Added medical certificate: 0002 - MARIA CLARA DELA CRUZ DELA CRUZ - 2026-09-10', '2026-09-10 00:23:29'),
(95, 1, 'admin', 'System Administrator', 'Administrator', 'LOGOUT', 'User logged out of the system.', '2026-09-10 00:53:40'),
(96, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGIN', 'User logged into the system.', '2026-09-10 00:53:47'),
(97, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGOUT', 'User logged out of the system.', '2026-09-10 01:39:20'),
(98, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGIN', 'User logged into the system.', '2026-09-10 01:40:29'),
(99, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGOUT', 'User logged out of the system.', '2026-09-10 01:40:33'),
(100, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-10 01:40:41'),
(101, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_PATIENT', 'Added patient: 0003 - JARED ISIP MANALANSAN', '2026-09-10 01:42:34'),
(102, 1, 'admin', 'System Administrator', 'Administrator', 'CONSULTATION', 'Added consultation: 0003 - JARED ISIP MANALANSAN - Consultation #1', '2026-09-10 01:49:42'),
(103, 1, 'admin', 'System Administrator', 'Administrator', 'LABORATORY', 'Added laboratory record: 0003 - JARED ISIP MANALANSAN - 13 test(s) - 2026-09-10', '2026-09-10 02:06:20'),
(104, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0003 - JARED ISIP MANALANSAN - 1 medicine(s) - 2026-09-10', '2026-09-10 02:09:45'),
(105, 1, 'admin', 'System Administrator', 'Administrator', 'MEDICAL_CERTIFICATE', 'Added medical certificate: 0003 - JARED ISIP MANALANSAN - 2026-09-10', '2026-09-10 02:11:33'),
(106, 1, 'admin', 'System Administrator', 'Administrator', 'CREATE', 'Created consultation for patient TEST, JUAN DELA CRUZ (Patient ID: 12) on 2026-09-10', '2026-09-10 03:48:21'),
(107, 1, 'admin', 'System Administrator', 'Administrator', 'LABORATORY', 'Updated laboratory record: 0001 - JUAN DELA CRUZ TEST - HEMOGLOBIN - 2026-09-08', '2026-09-10 05:29:47'),
(108, 1, 'admin', 'System Administrator', 'Administrator', 'LABORATORY', 'Deleted laboratory record: 0001 - JUAN DELA CRUZ TEST - HEMOGLOBIN - 2026-09-08', '2026-09-10 05:30:01'),
(109, 1, 'admin', 'System Administrator', 'Administrator', 'LABORATORY', 'Updated laboratory record: 0001 - JUAN DELA CRUZ TEST - HEMATOCRIT - 2026-09-08', '2026-09-10 05:30:52'),
(110, 1, 'admin', 'System Administrator', 'Administrator', 'LABORATORY', 'Updated laboratory result: 0003 - JARED ISIP MANALANSAN - FASTING BLOOD SUGAR - 2026-09-10', '2026-09-10 06:36:29'),
(111, 1, 'admin', 'System Administrator', 'Administrator', 'LOGOUT', 'User logged out of the system.', '2026-09-10 06:58:39'),
(112, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGIN', 'User logged into the system.', '2026-09-10 06:58:51'),
(113, 2, 'juan', 'Juan Dela Cruz', 'Staff', 'LOGOUT', 'User logged out of the system.', '2026-09-10 07:04:41'),
(114, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-10 07:04:46'),
(115, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_PATIENT', 'Added patient: 0004 - JOHN CARLO GARCIA DAVID', '2026-09-10 07:06:00'),
(116, 1, 'admin', 'System Administrator', 'Administrator', 'CREATE', 'Created consultation for patient DAVID, JOHN CARLO GARCIA (Patient ID: 17) on 2026-09-10', '2026-09-10 07:07:06'),
(117, 1, 'admin', 'System Administrator', 'Administrator', 'LABORATORY', 'Added laboratory record: 0003 - JARED ISIP MANALANSAN - 25 test(s) - 2026-09-10', '2026-09-10 07:54:51'),
(118, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_PATIENT', 'Added patient: 0002 - JARED ISIP MANALANSAN', '2026-09-10 08:22:24'),
(119, 1, 'admin', 'System Administrator', 'Administrator', 'CREATE', 'Created consultation for patient MANALANSAN, JARED ISIP (Patient ID: 18) on 2026-09-10', '2026-09-10 08:26:18'),
(120, 1, 'admin', 'System Administrator', 'Administrator', 'LABORATORY', 'Added laboratory record: 0002 - JARED ISIP MANALANSAN - 28 test(s) - 2026-09-10', '2026-09-10 08:36:07'),
(121, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0002 - JARED ISIP MANALANSAN - 2 medicine(s) - 2026-09-10', '2026-09-10 08:38:57'),
(122, 1, 'admin', 'System Administrator', 'Administrator', 'LOGOUT', 'User logged out of the system.', '2026-09-10 08:46:47'),
(123, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-11 05:57:27'),
(124, 1, 'admin', 'System Administrator', 'Administrator', 'LABORATORY', 'Updated laboratory result: 0002 - JARED ISIP MANALANSAN - FASTING BLOOD SUGAR - 2026-09-10', '2026-09-11 08:30:08'),
(125, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_PATIENT', 'Added patient: 0002 - JARED ISIP MANALANSAN', '2026-09-11 08:39:27'),
(126, 1, 'admin', 'System Administrator', 'Administrator', 'CREATE', 'Created consultation for patient MANALANSAN, JARED ISIP (Patient ID: 19) on 2026-09-11', '2026-09-11 08:40:39'),
(127, 1, 'admin', 'System Administrator', 'Administrator', 'LABORATORY', 'Added laboratory record: 0002 - JARED ISIP MANALANSAN - 28 test(s) - 2026-09-11', '2026-09-11 08:47:13'),
(128, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0002 - JARED ISIP MANALANSAN - 1 medicine(s) - 2026-09-11', '2026-09-11 08:48:19'),
(129, 1, 'admin', 'System Administrator', 'Administrator', 'LOGIN', 'User logged into the system.', '2026-09-13 23:55:42'),
(130, 1, 'admin', 'System Administrator', 'Administrator', 'MEDICAL_CERTIFICATE', 'Added medical certificate: 0002 - JARED ISIP MANALANSAN - Diagnosis: ACUTE UPPER RESPIRATORY TRACT INFECTION - 2026-09-14', '2026-09-14 00:05:12'),
(131, 1, 'admin', 'System Administrator', 'Administrator', 'MEDICAL_CERTIFICATE', 'Added medical certificate: 0001 - JUAN DELA CRUZ TEST - Diagnosis: ACUTE UPPER RESPIRATORY TRACT INFECTION - 2026-09-14', '2026-09-14 00:10:56'),
(132, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_PATIENT', 'Added patient: 0003 - JOHN CARLO GARCIA DAVID', '2026-09-14 00:20:58'),
(133, 1, 'admin', 'System Administrator', 'Administrator', 'CREATE', 'Created consultation for patient DAVID, JOHN CARLO GARCIA (Patient ID: 20) on 2026-09-14', '2026-09-14 00:21:44'),
(134, 1, 'admin', 'System Administrator', 'Administrator', 'CREATE', 'Created consultation for patient DAVID, JOHN CARLO GARCIA (Patient ID: 20) on 2026-09-14', '2026-09-14 01:12:15'),
(135, 1, 'admin', 'System Administrator', 'Administrator', 'CREATE', 'Created consultation for patient DAVID, JOHN CARLO GARCIA (Patient ID: 20) on 2026-09-14', '2026-09-14 01:15:30'),
(136, 1, 'admin', 'System Administrator', 'Administrator', 'CREATE', 'Created consultation for patient DAVID, JOHN CARLO GARCIA (Patient ID: 20) on 2026-09-14', '2026-09-14 01:25:37'),
(137, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_PATIENT', 'Added patient: 0004 - JOHN JACOB BONIFACIO DAVID', '2026-09-14 01:51:26'),
(138, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_PATIENT', 'Updated patient: 0004 - JOHN JACOB BONIFACIO DAVID', '2026-09-14 01:55:33'),
(139, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_PATIENT', 'Updated patient: 0004 - JOHN JACOB BONIFACIO DAVID', '2026-09-14 01:55:53'),
(140, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_PATIENT', 'Updated patient: 0004 - JACOB BONIFACIO DAVID', '2026-09-14 01:58:04'),
(141, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_PATIENT', 'Updated patient: 0004 - JOHN JACOB BONIFACIO DAVID', '2026-09-14 02:00:06'),
(142, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: METFORMIN HYDROCHLORIDE - 500MG - TABLET', '2026-09-14 02:03:56'),
(143, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: CLOPIDOGREL - 75MG - TABLET', '2026-09-14 02:04:49'),
(144, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: CETIRIZINE HYDROCHLORIDE - 10MG - TABLET', '2026-09-14 02:05:40'),
(145, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 24: CLOPIDOGREL - 75 MG - TABLET', '2026-09-14 02:05:49'),
(146, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 25: CETIRIZINE HYDROCHLORIDE - 10 MG - TABLET', '2026-09-14 02:05:53'),
(147, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: GLICLAZIDE - 60 MG - TABLET', '2026-09-14 02:07:14'),
(148, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: ASCORBIC ACID - 500 MG - TABLET', '2026-09-14 02:07:48'),
(149, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: LOSARTAN POTASSIUM - 50 MG - TABLET', '2026-09-14 02:08:13'),
(150, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: LOSARTAN(NATRAZOL) - 50 MG - TABLET', '2026-09-14 02:08:55'),
(151, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 28: LOSARTAN POTASSIUM(SAPHLOR) - 50 MG - TABLET', '2026-09-14 02:09:33'),
(152, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 21: AMLODIPINE BESILATE(AMLYTROL) - 5 MG - TABLET', '2026-09-14 02:10:22'),
(153, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: AMLODIPINE (DIADIPINE) - 5 MG - TABLET', '2026-09-14 02:10:37'),
(154, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: AMLODIPINE - 10MG - TABLET', '2026-09-14 02:10:54'),
(155, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: SIMVASTATIN (PHILSTAT) - 20 MG - TABLET', '2026-09-14 02:12:17'),
(156, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: SIMVASTATIN (PHILSTAT) - 40 MG - TABLET', '2026-09-14 02:12:36'),
(157, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 1: PARACETAMOL (PARASETH) - 500 MG - TABLET', '2026-09-14 02:13:14'),
(158, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 1: PARACETAMOL - 500 MG - TABLET', '2026-09-14 02:13:30'),
(159, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: CLONIDINE HYDROCHLORIDE - 150 MG - TABLET', '2026-09-14 02:14:09'),
(160, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: CIPROFLOXACIN - 500 MG - TABLET', '2026-09-14 02:14:26'),
(161, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: CLONIDINE HYDROCHLORIDE (CLODIN) - 75 - TABLET', '2026-09-14 02:15:38'),
(162, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 36: CLONIDINE HYDROCHLORIDE - 75 - TABLET', '2026-09-14 02:15:56'),
(163, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: CEFALEXIN - 500 MG - TABLET', '2026-09-14 02:16:37'),
(164, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 37: CEFALEXIN - 500 MG - CAPSULE', '2026-09-14 02:17:07'),
(165, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: METROPOLOL - 50 MG - TABLET', '2026-09-14 02:17:50'),
(166, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 18: SALBUTAMOL - 2 MG - SYRUP', '2026-09-14 02:18:29'),
(167, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 27: ASCORBIC ACID (CEVIT) - 500 MG - TABLET', '2026-09-14 02:19:15'),
(168, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: ASCORBIC ACID - 100 MG - SYRUP', '2026-09-14 02:19:29'),
(169, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: CO-AMOX - 62.5 ML - SYRUP', '2026-09-14 02:20:18'),
(170, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 39: ASCORBIC ACID - 5 ML - SYRUP', '2026-09-14 02:20:30'),
(171, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 18: SALBUTAMOL - 2 MG / 5 ML - SYRUP', '2026-09-14 02:21:06'),
(172, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 3: PARACETAMOL - 120 MG / 5 ML - SYRUP', '2026-09-14 02:21:14'),
(173, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 39: ASCORBIC ACID - 100 MG / 5ML - SYRUP', '2026-09-14 02:21:38'),
(174, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 40: CO-AMOX - 250 MG / 62.5 ML - SYRUP', '2026-09-14 02:22:02'),
(175, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: CETIRIZINE(SYRUP) - 1MG/5ML - SYRUP', '2026-09-14 02:23:26'),
(176, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: MULTIVITAMINS - 5ML - SYRUP', '2026-09-14 02:23:49'),
(177, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: PARACETAMOL - 250MG/5ML - SYRUP', '2026-09-14 02:24:48'),
(178, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: AMOXICILLIN - 250 MG / 5ML - SYRUP', '2026-09-14 02:25:27'),
(179, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 43: PARACETAMOL(SYRUP) - 250 MG / 5ML - SYRUP', '2026-09-14 02:25:48'),
(180, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 3: PARACETAMOL(SYRUP) - 120 MG / 5 ML - SYRUP', '2026-09-14 02:26:05'),
(181, 1, 'admin', 'System Administrator', 'Administrator', 'EDIT_MEDICINE', 'Edited medicine ID 42: MULTIVITAMINS - 5 ML - SYRUP', '2026-09-14 02:26:17'),
(182, 1, 'admin', 'System Administrator', 'Administrator', 'ADD_MEDICINE', 'Added medicine: CHLORPHENAMINE MALEATE - 2 MG / 5 ML - SYRUP', '2026-09-14 02:26:48'),
(183, 1, 'admin', 'System Administrator', 'Administrator', 'CREATE', 'Created consultation for patient DAVID, JOHN JACOB BONIFACIO (Patient ID: 21) on 2026-09-14', '2026-09-14 02:58:39'),
(184, 1, 'admin', 'System Administrator', 'Administrator', 'PRESCRIPTION', 'Added prescription: 0004 - JOHN JACOB BONIFACIO DAVID - 3 medicine(s) - 2026-09-14', '2026-09-14 03:22:36');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE IF NOT EXISTS `consultations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `chief_complaint` text,
  `history_illness` text,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `bp_status` varchar(50) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `pulse_rate` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `oxygen_saturation` int(11) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `bmi` decimal(5,2) DEFAULT NULL,
  `bmi_status` varchar(30) DEFAULT NULL,
  `assessment` text,
  `medication` text,
  `management` text,
  `follow_up_date` date DEFAULT NULL,
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `follow_up_status` varchar(30) NOT NULL DEFAULT 'PENDING',
  PRIMARY KEY (`id`),
  KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=44 ;

--
-- Dumping data for table `consultations`
--

INSERT INTO `consultations` (`id`, `patient_id`, `visit_date`, `chief_complaint`, `history_illness`, `blood_pressure`, `bp_status`, `temperature`, `pulse_rate`, `respiratory_rate`, `oxygen_saturation`, `weight`, `height`, `bmi`, `bmi_status`, `assessment`, `medication`, `management`, `follow_up_date`, `remarks`, `created_at`, `follow_up_status`) VALUES
(32, 12, '2026-08-31', 'HEADACHE', 'HEADACHE FOR 2 DAYS', '120/80', NULL, '36.5', 72, 18, 98, '60.00', '165.00', NULL, NULL, 'G44.1 - VASCULAR HEADACHE', NULL, 'REST AND HYDRATION', '2026-09-07', '-', '2026-09-08 02:09:02', 'COMPLETED'),
(35, 12, '2026-09-10', 'FEVER', 'PATIENT REPORTS FEVER STARTED 2 DAYS AGO, ASSOCIATED WITH BODY WEAKNESS AND HEADACHE. NO VOMITING OR DIARRHEA REPORTED.', '120/80', NULL, '38.5', 95, 20, NULL, '65.00', '170.00', '22.49', 'Normal Weight', 'JO3 - ACUTE TONSILLITIS', NULL, 'ADVISED ADEQUATE FLUID INTAKE AND REST. MONITOR BODY TEMPERATURE. ADVISED TO SEEK MEDICAL CONSULTATION IF SYMPTOMS WORSEN OR PERSIST.', '2026-09-14', 'RETURN FOR FOLLOW-UP IF FEVER PERSISTS.', '2026-09-10 03:48:21', 'NO SHOW'),
(38, 19, '2026-09-11', 'NORMAL', 'NONESSSSSSSSS', '118/76', NULL, '36.3', 93, 21, NULL, '95.00', '180.00', '29.32', 'Overweight', '000 - ESSENTIALLY WELL INDIVIDUAL', '', 'ASSSS', '2026-09-12', '11 AM', '2026-09-11 08:40:39', 'NO SHOW'),
(42, 20, '2026-09-14', 'COUGH', 'ABCD', '180/100', 'Very High BP / Urgent Alert', '36.5', 95, 19, NULL, '70.00', '170.00', '24.22', 'Normal Weight', '000 - ESSENTIALLY WELL INDIVIDUAL', '', 'ABCD', '2026-09-14', 'ABCDEFG', '2026-09-14 01:25:37', 'PENDING'),
(43, 21, '2026-09-14', 'HEADACHE', 'PATIENT REPORTS HEADACHE SINCE YESTERDAY.', '120/75', 'Elevated', '36.8', 82, 18, NULL, '70.00', '170.00', '24.22', 'Normal Weight', 'G44.1 - VASCULAR HEADACHE', '', 'REST AND MONITOR VITAL SIGNS', '2026-09-14', 'FIRST CONSULTATION TEST', '2026-09-14 02:58:39', 'PENDING');

-- --------------------------------------------------------

--
-- Table structure for table `laboratory`
--

CREATE TABLE IF NOT EXISTS `laboratory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `test_date` date NOT NULL,
  `test_name` varchar(150) NOT NULL,
  `result` varchar(255) NOT NULL,
  `unit` varchar(50) DEFAULT '',
  `reference_range` varchar(100) DEFAULT '',
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `facility_type` varchar(30) NOT NULL DEFAULT 'WITHIN_FACILITY',
  `health_care_institution` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_patient_id` (`patient_id`),
  KEY `idx_test_date` (`test_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=150 ;

--
-- Dumping data for table `laboratory`
--

INSERT INTO `laboratory` (`id`, `patient_id`, `test_date`, `test_name`, `result`, `unit`, `reference_range`, `remarks`, `created_at`, `facility_type`, `health_care_institution`) VALUES
(28, 12, '2026-09-08', 'HEMATOCRIT', '41', '%', 'MALE: 41-53% | FEMALE: 36-46%', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(29, 12, '2026-09-08', 'WHITE BLOOD CELL COUNT', '7.2', 'K/UL', '4.0-11.0 K/UL', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(30, 12, '2026-09-08', 'PLATELET COUNT', '250', 'K/UL', '150-450 K/UL', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(31, 12, '2026-09-08', 'RANDOM BLOOD SUGAR', '98', 'MG/DL', '70-140 MG/DL', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(32, 12, '2026-09-08', 'CREATININE', '0.9', 'MG/DL', 'MALE: 0.74-1.35 MG/DL | FEMALE: 0.59-1.04 MG/DL', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(33, 12, '2026-09-08', 'TOTAL CHOLESTEROL', '180', 'MG/DL', '<200 MG/DL', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(34, 12, '2026-09-08', 'TRIGLYCERIDES', '120', 'MG/DL', '<150 MG/DL', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(35, 12, '2026-09-08', 'HDL CHOLESTEROL', '55', 'MG/DL', '>=40 MG/DL', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(36, 12, '2026-09-08', 'LDL CHOLESTEROL', '101', 'MG/DL', '<100 MG/DL', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(37, 12, '2026-09-08', 'COLOR', 'YELLOW', '', 'YELLOW', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(38, 12, '2026-09-08', 'APPEARANCE', 'CLEAR', '', 'CLEAR', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(39, 12, '2026-09-08', 'SPECIFIC GRAVITY', '1.015', '', '1.005-1.030', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(40, 12, '2026-09-08', 'PH', '6.0', '', '5.0-8.0', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(41, 12, '2026-09-08', 'PROTEIN', 'NEGATIVE', '', 'NEGATIVE', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(42, 12, '2026-09-08', 'GLUCOSE', 'NEGATIVE', '', 'NEGATIVE', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(43, 12, '2026-09-08', 'KETONES', 'NEGATIVE', '', 'NEGATIVE', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(44, 12, '2026-09-08', 'BLOOD', 'NEGATIVE', '', 'NEGATIVE', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(45, 12, '2026-09-08', 'RBC', '0-2', '/HPF', '0-2 /HPF', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(46, 12, '2026-09-08', 'WBC', '0-2', '/HPF', '0-5 /HPF', '', '2026-09-08 02:21:58', 'WITHIN_FACILITY', NULL),
(122, 19, '2026-09-11', 'FASTING BLOOD SUGAR', '114.52', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(123, 19, '2026-09-11', 'CREATININE', '0.95', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(124, 19, '2026-09-11', 'URIC ACID', '3.39', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(125, 19, '2026-09-11', 'SGPT / ALT', '32.61', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(126, 19, '2026-09-11', 'VLDL', '42.79', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(127, 19, '2026-09-11', 'TOTAL CHOLESTEROL', '169.42', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(128, 19, '2026-09-11', 'TRIGLYCERIDES', '213.96', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(129, 19, '2026-09-11', 'HDL', '33.88', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(130, 19, '2026-09-11', 'LDL', '92.75', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(131, 19, '2026-09-11', 'HEMOGLOBIN', '125', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(132, 19, '2026-09-11', 'HEMATOCRIT', '0.38', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(133, 19, '2026-09-11', 'WBC COUNT', '7.20', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(134, 19, '2026-09-11', 'SEGMENTER', '0.63', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(135, 19, '2026-09-11', 'LYMPHOCYTE', '0.37', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(136, 19, '2026-09-11', 'PLATELET COUNT', '258', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(137, 19, '2026-09-11', 'COLOR', 'YELLOW', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(138, 19, '2026-09-11', 'APPEARANCE', 'HAZY', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(139, 19, '2026-09-11', 'PH', '6.5', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(140, 19, '2026-09-11', 'SPECIFIC GRAVITY', '1.020', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(141, 19, '2026-09-11', 'ALBUMIN', 'NEGATIVE', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(142, 19, '2026-09-11', 'SUGAR', 'NEGATIVE', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(143, 19, '2026-09-11', 'EPITHELIAL CELLS', 'FEW', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(144, 19, '2026-09-11', 'MUCUS THREADS', 'FEW', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(145, 19, '2026-09-11', 'RBC CELLS', '0-1', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(146, 19, '2026-09-11', 'PUS CELLS', '0-1', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(147, 19, '2026-09-11', 'BACTERIA', 'RARE', '', '', '', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'AVM'),
(148, 19, '2026-09-11', 'CHEST X-RAY', 'WITH FINDING', '', '', 'FINDINGS: SULCI AND GREAT VESSEL ARE INTACT | IMPRESSION: NORMAL', '2026-09-11 08:47:13', 'ACCREDITED_FACILITY', 'RMC'),
(149, 19, '2026-09-11', 'ELECTROCARDIOGRAM (ECG)', 'WITH FINDING', '', '', 'ABNORMAL T-WAVE', '2026-09-11 08:47:13', 'WITHIN_FACILITY', 'DCMD CLINIC');

-- --------------------------------------------------------

--
-- Table structure for table `medical_certificates`
--

CREATE TABLE IF NOT EXISTS `medical_certificates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `certificate_date` date NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `diagnosis` text,
  `findings` text,
  `recommendations` text,
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `medical_certificates`
--

INSERT INTO `medical_certificates` (`id`, `patient_id`, `consultation_id`, `certificate_date`, `purpose`, `diagnosis`, `findings`, `recommendations`, `remarks`, `created_at`) VALUES
(2, 12, NULL, '2026-09-08', 'FOR WORK', 'HYPERTENSION', 'PATIENT WAS EVALUATED AT THE CLINIC.', 'REST AND MONITOR BLOOD PRESSURE.', 'NONE', '2026-09-08 03:14:29'),
(3, 15, NULL, '2026-09-10', 'FOR SCHOOL', 'DSDAS', 'SADSD', 'ASDASF', 'SADASFASD', '2026-09-10 00:23:29'),
(4, 16, NULL, '2026-09-10', 'FOR WORK', 'NORMAL', 'NORMAL', '', '', '2026-09-10 02:11:33'),
(5, 19, NULL, '2026-09-14', 'MEDICAL CERTIFICATE', 'ACUTE UPPER RESPIRATORY TRACT INFECTION', '', '', '', '2026-09-14 00:05:12'),
(6, 12, NULL, '2026-09-14', 'MEDICAL CERTIFICATE', 'ACUTE UPPER RESPIRATORY TRACT INFECTION', '', '', '', '2026-09-14 00:10:56');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE IF NOT EXISTS `medicines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medicine_name` varchar(150) NOT NULL,
  `strength` varchar(100) DEFAULT NULL,
  `form` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=46 ;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`id`, `medicine_name`, `strength`, `form`, `is_active`, `created_at`) VALUES
(1, 'PARACETAMOL', '500 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(2, 'PARACETAMOL', '650 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(3, 'PARACETAMOL(SYRUP)', '120 MG / 5 ML', 'SYRUP', 1, '2026-09-09 01:30:39'),
(4, 'IBUPROFEN', '200 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(5, 'IBUPROFEN', '400 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(6, 'AMOXICILLIN', '250 MG', 'CAPSULE', 1, '2026-09-09 01:30:39'),
(7, 'AMOXICILLIN', '500 MG', 'CAPSULE', 1, '2026-09-09 01:30:39'),
(8, 'AZITHROMYCIN', '250 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(9, 'AZITHROMYCIN', '500 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(10, 'CEPHALEXIN', '500 MG', 'CAPSULE', 1, '2026-09-09 01:30:39'),
(11, 'CETIRIZINE', '10 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(12, 'LORATADINE', '10 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(13, 'OMEPRAZOLE', '20 MG', 'CAPSULE', 1, '2026-09-09 01:30:39'),
(14, 'PANTOPRAZOLE', '40 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(15, 'LOPERAMIDE', '2 MG', 'CAPSULE', 1, '2026-09-09 01:30:39'),
(16, 'AMBROXOL', '30 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(17, 'CARBOCISTEINE', '500 MG', 'CAPSULE', 1, '2026-09-09 01:30:39'),
(18, 'SALBUTAMOL', '2 MG / 5 ML', 'SYRUP', 1, '2026-09-09 01:30:39'),
(19, 'METFORMIN', '500 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(20, 'LOSARTAN', '50 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(21, 'AMLODIPINE BESILATE(AMLYTROL)', '5 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(22, 'ATORVASTATIN', '20 MG', 'TABLET', 1, '2026-09-09 01:30:39'),
(23, 'METFORMIN HYDROCHLORIDE', '500MG', 'TABLET', 1, '2026-09-14 02:03:56'),
(24, 'CLOPIDOGREL', '75 MG', 'TABLET', 1, '2026-09-14 02:04:49'),
(25, 'CETIRIZINE HYDROCHLORIDE', '10 MG', 'TABLET', 1, '2026-09-14 02:05:40'),
(26, 'GLICLAZIDE', '60 MG', 'TABLET', 1, '2026-09-14 02:07:14'),
(27, 'ASCORBIC ACID (CEVIT)', '500 MG', 'TABLET', 1, '2026-09-14 02:07:48'),
(28, 'LOSARTAN POTASSIUM(SAPHLOR)', '50 MG', 'TABLET', 1, '2026-09-14 02:08:13'),
(29, 'LOSARTAN(NATRAZOL)', '50 MG', 'TABLET', 1, '2026-09-14 02:08:55'),
(30, 'AMLODIPINE (DIADIPINE)', '5 MG', 'TABLET', 1, '2026-09-14 02:10:37'),
(31, 'AMLODIPINE', '10MG', 'TABLET', 1, '2026-09-14 02:10:54'),
(32, 'SIMVASTATIN (PHILSTAT)', '20 MG', 'TABLET', 1, '2026-09-14 02:12:17'),
(33, 'SIMVASTATIN (PHILSTAT)', '40 MG', 'TABLET', 1, '2026-09-14 02:12:36'),
(34, 'CLONIDINE HYDROCHLORIDE', '150 MG', 'TABLET', 1, '2026-09-14 02:14:09'),
(35, 'CIPROFLOXACIN', '500 MG', 'TABLET', 1, '2026-09-14 02:14:26'),
(36, 'CLONIDINE HYDROCHLORIDE', '75', 'TABLET', 1, '2026-09-14 02:15:38'),
(37, 'CEFALEXIN', '500 MG', 'CAPSULE', 1, '2026-09-14 02:16:37'),
(38, 'METROPOLOL', '50 MG', 'TABLET', 1, '2026-09-14 02:17:50'),
(39, 'ASCORBIC ACID', '100 MG / 5ML', 'SYRUP', 1, '2026-09-14 02:19:29'),
(40, 'CO-AMOX', '250 MG / 62.5 ML', 'SYRUP', 1, '2026-09-14 02:20:18'),
(41, 'CETIRIZINE(SYRUP)', '1MG/5ML', 'SYRUP', 1, '2026-09-14 02:23:26'),
(42, 'MULTIVITAMINS', '5 ML', 'SYRUP', 1, '2026-09-14 02:23:49'),
(43, 'PARACETAMOL(SYRUP)', '250 MG / 5ML', 'SYRUP', 1, '2026-09-14 02:24:48'),
(44, 'AMOXICILLIN', '250 MG / 5ML', 'SYRUP', 1, '2026-09-14 02:25:27'),
(45, 'CHLORPHENAMINE MALEATE', '2 MG / 5 ML', 'SYRUP', 1, '2026-09-14 02:26:48');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE IF NOT EXISTS `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` varchar(10) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `birthdate` date NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `civil_status` varchar(30) DEFAULT NULL,
  `philhealth_no` varchar(30) DEFAULT NULL,
  `philhealth_yakap_status` varchar(30) DEFAULT NULL,
  `address` text,
  `contact_no` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `emergency_contact` varchar(150) DEFAULT NULL,
  `emergency_contact_no` varchar(20) DEFAULT NULL,
  `date_registered` date NOT NULL,
  `status` enum('Active','Inactive','Deceased') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_id` (`patient_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `patient_id`, `last_name`, `first_name`, `middle_name`, `birthdate`, `sex`, `civil_status`, `philhealth_no`, `philhealth_yakap_status`, `address`, `contact_no`, `email`, `emergency_contact`, `emergency_contact_no`, `date_registered`, `status`, `created_at`) VALUES
(12, '0001', 'TEST', 'JUAN', 'DELA CRUZ', '1990-01-01', 'Male', NULL, '072506362785', NULL, 'TEST ADDRESS', '09123456789', NULL, 'TEST TWO', '09202345678', '2026-09-08', 'Active', '2026-09-08 02:07:38'),
(19, '0002', 'MANALANSAN', 'JARED', 'ISIP', '2000-05-19', 'Male', NULL, '', NULL, 'PUROK 3 LAMBAC GUAGUA PAMPANGA', '09173468548', NULL, 'JONEL DAVID', '09942513659', '2026-09-11', 'Active', '2026-09-11 08:39:27'),
(20, '0003', 'DAVID', 'JOHN CARLO', 'GARCIA', '2002-11-19', 'Male', NULL, '', NULL, 'SAN PABLO GUAGUA PAMPANGA', '09307832575', NULL, 'JONEL DAVID', '09560667105', '2026-09-14', 'Active', '2026-09-14 00:20:58'),
(21, '0004', 'DAVID', 'JOHN JACOB', 'BONIFACIO', '2000-07-05', 'Male', 'Single', '072540840300', 'REGISTERED', 'GUAGUA PAMPANGA', '09123456789', '', 'MARIA DELA CRUZ', '09942513659', '2026-09-14', 'Active', '2026-09-14 01:51:26');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prescription_header_id` int(11) DEFAULT NULL,
  `consultation_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `medicine_name` varchar(150) NOT NULL,
  `strength` varchar(100) DEFAULT '',
  `dosage` varchar(100) DEFAULT '',
  `frequency` varchar(100) DEFAULT '',
  `duration` varchar(100) DEFAULT '',
  `quantity` int(11) DEFAULT NULL,
  `sig` varchar(255) DEFAULT NULL,
  `breakfast` varchar(30) NOT NULL DEFAULT '',
  `lunch` varchar(30) NOT NULL DEFAULT '',
  `dinner` varchar(30) NOT NULL DEFAULT '',
  `instructions` text,
  `prescribed_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `consultation_id` (`consultation_id`),
  KEY `patient_id` (`patient_id`),
  KEY `idx_prescription_header_id` (`prescription_header_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `prescriptions`
--

INSERT INTO `prescriptions` (`id`, `prescription_header_id`, `consultation_id`, `patient_id`, `medicine_name`, `strength`, `dosage`, `frequency`, `duration`, `quantity`, `sig`, `breakfast`, `lunch`, `dinner`, `instructions`, `prescribed_date`, `created_at`) VALUES
(1, 10, 33, 15, 'AMBROXOL', '30 MG', '', '', '', NULL, '3 TAB PER DAY', '1 TAB', '1 TAB', '1 TAB', NULL, '2026-09-10', '2026-09-10 00:09:17'),
(2, 11, 34, 16, 'CETIRIZINE', '10 MG', '', '', '', 9, 'TAKE 1 TAB ONCE A DAY AT BEDTIME', '', '', '1 TAB', NULL, '2026-09-10', '2026-09-10 02:09:45'),
(3, 12, 37, 18, 'PARACETAMOL', '500 MG', '', '', '', 15, 'TAKE 1 TAB THREE TIMES A DAY FOR FIVE DAYS', '1 TAB', '1 TAB', '1 TAB', NULL, '2026-09-10', '2026-09-10 08:38:57'),
(4, 12, 37, 18, 'SALBUTAMOL', '500 MG', '', '', '', 30, 'TAKE 1 CAP ONCE A DAY', '', '1 CAP', '', NULL, '2026-09-10', '2026-09-10 08:38:57'),
(5, 13, 38, 19, 'AMBROXOL', '30 MG', '', '', '', 9, '3 TAB PER DAY', '1 TAB', '1 CAP', '1 TAB', NULL, '2026-09-11', '2026-09-11 08:48:19'),
(6, 14, 43, 21, 'AMOXICILLIN', '500 MG', '', '', '', 21, 'TAKE 7 DAYS FOR 3 TIMES A DAY', '1 CAP', '1 CAP', '1 CAP', NULL, '2026-09-14', '2026-09-14 03:22:36'),
(7, 14, 43, 21, 'PARACETAMOL', '500 MG', '', '', '', 12, 'EVERY 6 HOURS AS NEEDED 3 DAYS', '1 TAB', '1 TAB', '1 TAB', NULL, '2026-09-14', '2026-09-14 03:22:36'),
(8, 14, 43, 21, 'CETIRIZINE', '10 MG', '', '', '', 5, 'ONCE A DAY', '1', '', '', NULL, '2026-09-14', '2026-09-14 03:22:36');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_headers`
--

CREATE TABLE IF NOT EXISTS `prescription_headers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `prescribed_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_patient_date` (`patient_id`,`prescribed_date`),
  KEY `idx_patient_id` (`patient_id`),
  KEY `idx_prescribed_date` (`prescribed_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `prescription_headers`
--

INSERT INTO `prescription_headers` (`id`, `patient_id`, `prescribed_date`, `created_at`) VALUES
(9, 13, '2026-09-08', '2026-09-08 07:01:45'),
(10, 15, '2026-09-10', '2026-09-10 00:09:17'),
(11, 16, '2026-09-10', '2026-09-10 02:09:45'),
(12, 18, '2026-09-10', '2026-09-10 08:38:57'),
(13, 19, '2026-09-11', '2026-09-11 08:48:19'),
(14, 21, '2026-09-14', '2026-09-14 03:22:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'Staff',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$WrnjGjrhnxSMCxv8Er/tweYim1H9yWSxyzSCFWQlRvm7Yhrzq8nzW', 'System Administrator', 'Administrator', 1, '2026-09-07 00:08:44'),
(2, 'juan', '$2y$10$BSN.AntjF2EwKGTquTd2geLnUgsN/pofh1kJ5/8Fq5r3FJB94EI/S', 'Juan Dela Cruz', 'Staff', 1, '2026-09-07 01:15:22');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `laboratory`
--
ALTER TABLE `laboratory`
  ADD CONSTRAINT `fk_laboratory_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
