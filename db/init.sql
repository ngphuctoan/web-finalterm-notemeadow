-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: May 16, 2025 at 01:26 AM
-- Server version: 8.4.5
-- PHP Version: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `notemeadow`
--

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_pinned` tinyint DEFAULT '0',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `font_size` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '16px',
  `note_color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT 'gray',
  `status_pass` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `user_id`, `title`, `content`, `created_at`, `modified_at`, `is_pinned`, `category`, `tags`, `password`, `image`, `font_size`, `note_color`, `status_pass`) VALUES
(1, 1, '🪴 Welcome to notemeadow!', '[{\"insert\":\"We are excited to have you here!\"},{\"attributes\":{\"header\":3},\"insert\":\"\\n\"},{\"insert\":\"\\nThis is your personal space to:\\n\\n📔 \"},{\"attributes\":{\"underline\":true},\"insert\":\"Take\"},{\"insert\":\" notes\"},{\"attributes\":{\"list\":\"bullet\"},\"insert\":\"\\n\"},{\"insert\":\"💡 \"},{\"attributes\":{\"underline\":true},\"insert\":\"Organize\"},{\"insert\":\" your thoughts\"},{\"attributes\":{\"list\":\"bullet\"},\"insert\":\"\\n\"},{\"insert\":\"📨 \"},{\"attributes\":{\"underline\":true},\"insert\":\"Share\"},{\"insert\":\" your insights with others\"},{\"attributes\":{\"list\":\"bullet\"},\"insert\":\"\\n\"},{\"insert\":\"\\nTips\"},{\"attributes\":{\"header\":3},\"insert\":\"\\n\"},{\"insert\":\"\\n🏷️ Use \"},{\"attributes\":{\"bold\":true},\"insert\":\"tags\"},{\"insert\":\" to group related notes.\"},{\"attributes\":{\"list\":\"bullet\"},\"insert\":\"\\n\"},{\"attributes\":{\"bold\":true},\"insert\":\"🔒 Lock\"},{\"insert\":\" notes with passwords to prevent others from viewing them.\"},{\"attributes\":{\"list\":\"bullet\"},\"insert\":\"\\n\"},{\"insert\":\"✒️ Click the \"},{\"attributes\":{\"bold\":true},\"insert\":\"\\\"Share\\\"\"},{\"insert\":\" button to collaborate.\"},{\"attributes\":{\"list\":\"bullet\"},\"insert\":\"\\n\"},{\"insert\":\"💾 All your notes are \"},{\"attributes\":{\"bold\":true},\"insert\":\"saved automatically\"},{\"insert\":\" and can be accessed anytime.\"},{\"attributes\":{\"list\":\"bullet\"},\"insert\":\"\\n\"},{\"insert\":\"\\nYou can discover more when you have the time. Happy noting!\\n\\n\"},{\"attributes\":{\"italic\":true},\"insert\":\"– The notemeadow team\"},{\"insert\":\"\\n\\n\"},{\"insert\":{\"image\":\"http://localhost:8080/uploads/images/682693a3931b2.jpg\"}},{\"insert\":\"\\n\"}]', '2025-05-16 01:07:32', '2025-05-16 01:23:47', 1, NULL, '', NULL, '[]', '16px', 'gray', 0),
(2, 1, '⚙️ Cool note settings!', '[{\"attributes\":{\"italic\":true},\"insert\":\"You can change the settings of each notes by clicking the ⚙️ icon in the toolbar!\"},{\"insert\":\"\\n\\n\"},{\"attributes\":{\"bold\":true},\"insert\":\"Font sizes:\"},{\"insert\":\" 12px up to 24px\\n\"},{\"attributes\":{\"bold\":true},\"insert\":\"Available colors:\"},{\"insert\":\" grey, red, yellow, green, blue and purple\\n\\n\"},{\"attributes\":{\"strike\":true,\"bold\":true},\"insert\":\"Hint:\"},{\"attributes\":{\"strike\":true},\"insert\":\" the locked note has a password of \\\"1234\\\".\"},{\"insert\":\"\\n\"}]', '2025-05-16 01:15:41', '2025-05-16 01:20:14', 0, NULL, '', NULL, '[]', '16px', 'blue', 0),
(3, 1, '🔒 This is a locked note', '[{\"insert\":\"Congrats on guessing the password!\\n\"}]', '2025-05-16 01:19:02', '2025-05-16 01:19:26', 0, NULL, '', '1234', '[]', '20px', 'green', 0);

-- --------------------------------------------------------

--
-- Table structure for table `note_history`
--

CREATE TABLE `note_history` (
  `id` int NOT NULL,
  `note_id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `note_history`
--

INSERT INTO `note_history` (`id`, `note_id`, `user_id`, `action`, `timestamp`) VALUES
(1, 1, 1, 'Note has been created.', '2025-05-16 08:07:32'),
(2, 1, 1, 'Note has been edited', '2025-05-16 08:08:05'),
(3, 1, 1, 'Note has been edited', '2025-05-16 08:08:06'),
(4, 1, 1, 'Note has been edited', '2025-05-16 08:08:35'),
(5, 1, 1, 'Note has been edited', '2025-05-16 08:08:37'),
(6, 1, 1, 'Note has been edited', '2025-05-16 08:08:38'),
(7, 1, 1, 'Note has been edited', '2025-05-16 08:08:39'),
(8, 1, 1, 'Note has been edited', '2025-05-16 08:08:41'),
(9, 1, 1, 'Note has been edited', '2025-05-16 08:08:43'),
(10, 1, 1, 'Note has been edited', '2025-05-16 08:08:43'),
(11, 1, 1, 'Note has been edited', '2025-05-16 08:08:45'),
(12, 1, 1, 'Note has been edited', '2025-05-16 08:08:46'),
(13, 1, 1, 'Note has been edited', '2025-05-16 08:08:46'),
(14, 1, 1, 'Note has been edited', '2025-05-16 08:08:49'),
(15, 1, 1, 'Note has been edited', '2025-05-16 08:08:50'),
(16, 1, 1, 'Note has been edited', '2025-05-16 08:08:51'),
(17, 1, 1, 'Note has been edited', '2025-05-16 08:09:06'),
(18, 1, 1, 'Note has been edited', '2025-05-16 08:09:07'),
(19, 1, 1, 'Note has been edited', '2025-05-16 08:09:08'),
(20, 1, 1, 'Note has been edited', '2025-05-16 08:09:09'),
(21, 1, 1, 'Note has been edited', '2025-05-16 08:09:26'),
(22, 1, 1, 'Note has been edited', '2025-05-16 08:09:26'),
(23, 1, 1, 'Note has been edited', '2025-05-16 08:09:27'),
(24, 1, 1, 'Note has been edited', '2025-05-16 08:09:27'),
(25, 1, 1, 'Note has been edited', '2025-05-16 08:09:28'),
(26, 1, 1, 'Note has been edited', '2025-05-16 08:09:29'),
(27, 1, 1, 'Note has been edited', '2025-05-16 08:09:31'),
(28, 1, 1, 'Note has been edited', '2025-05-16 08:09:33'),
(29, 1, 1, 'Note has been edited', '2025-05-16 08:09:34'),
(30, 1, 1, 'Note has been edited', '2025-05-16 08:09:34'),
(31, 1, 1, 'Note has been edited', '2025-05-16 08:09:35'),
(32, 1, 1, 'Note has been edited', '2025-05-16 08:09:36'),
(33, 1, 1, 'Note has been edited', '2025-05-16 08:09:36'),
(34, 1, 1, 'Note has been edited', '2025-05-16 08:09:37'),
(35, 1, 1, 'Note has been edited', '2025-05-16 08:09:37'),
(36, 1, 1, 'Note has been edited', '2025-05-16 08:10:04'),
(37, 1, 1, 'Note has been edited', '2025-05-16 08:10:04'),
(38, 1, 1, 'Note has been edited', '2025-05-16 08:10:07'),
(39, 1, 1, 'Note has been edited', '2025-05-16 08:10:08'),
(40, 1, 1, 'Note has been edited', '2025-05-16 08:10:09'),
(41, 1, 1, 'Note has been edited', '2025-05-16 08:10:14'),
(42, 1, 1, 'Note has been edited', '2025-05-16 08:10:14'),
(43, 1, 1, 'Note has been edited', '2025-05-16 08:10:20'),
(44, 1, 1, 'Note has been edited', '2025-05-16 08:10:21'),
(45, 1, 1, 'Note has been edited', '2025-05-16 08:10:22'),
(46, 1, 1, 'Note has been edited', '2025-05-16 08:10:24'),
(47, 1, 1, 'Note has been edited', '2025-05-16 08:10:24'),
(48, 1, 1, 'Note has been edited', '2025-05-16 08:10:25'),
(49, 1, 1, 'Note has been edited', '2025-05-16 08:10:27'),
(50, 1, 1, 'Note has been edited', '2025-05-16 08:10:37'),
(51, 1, 1, 'Note has been edited', '2025-05-16 08:10:37'),
(52, 1, 1, 'Note has been edited', '2025-05-16 08:10:38'),
(53, 1, 1, 'Note has been edited', '2025-05-16 08:10:39'),
(54, 1, 1, 'Note has been edited', '2025-05-16 08:10:40'),
(55, 1, 1, 'Note has been edited', '2025-05-16 08:10:43'),
(56, 1, 1, 'Note has been edited', '2025-05-16 08:10:47'),
(57, 1, 1, 'Note has been edited', '2025-05-16 08:10:48'),
(58, 1, 1, 'Note has been edited', '2025-05-16 08:10:50'),
(59, 1, 1, 'Note has been edited', '2025-05-16 08:10:51'),
(60, 1, 1, 'Note has been edited', '2025-05-16 08:10:53'),
(61, 1, 1, 'Note has been edited', '2025-05-16 08:10:54'),
(62, 1, 1, 'Note has been edited', '2025-05-16 08:10:55'),
(63, 1, 1, 'Note has been edited', '2025-05-16 08:10:56'),
(64, 1, 1, 'Note has been edited', '2025-05-16 08:10:59'),
(65, 1, 1, 'Note has been edited', '2025-05-16 08:11:00'),
(66, 1, 1, 'Note has been edited', '2025-05-16 08:11:03'),
(67, 1, 1, 'Note has been edited', '2025-05-16 08:11:04'),
(68, 1, 1, 'Note has been edited', '2025-05-16 08:11:10'),
(69, 1, 1, 'Note has been edited', '2025-05-16 08:11:11'),
(70, 1, 1, 'Note has been edited', '2025-05-16 08:11:11'),
(71, 1, 1, 'Note has been edited', '2025-05-16 08:11:13'),
(72, 1, 1, 'Note has been edited', '2025-05-16 08:11:16'),
(73, 1, 1, 'Note has been edited', '2025-05-16 08:11:16'),
(74, 1, 1, 'Note has been edited', '2025-05-16 08:11:19'),
(75, 1, 1, 'Note has been edited', '2025-05-16 08:11:20'),
(76, 1, 1, 'Note has been edited', '2025-05-16 08:11:22'),
(77, 1, 1, 'Note has been edited', '2025-05-16 08:11:23'),
(78, 1, 1, 'Note has been edited', '2025-05-16 08:11:23'),
(79, 1, 1, 'Note has been edited', '2025-05-16 08:11:32'),
(80, 1, 1, 'Note has been edited', '2025-05-16 08:11:37'),
(81, 1, 1, 'Note has been edited', '2025-05-16 08:11:40'),
(82, 1, 1, 'Note has been edited', '2025-05-16 08:12:00'),
(83, 1, 1, 'Note has been edited', '2025-05-16 08:12:00'),
(84, 1, 1, 'Note has been edited', '2025-05-16 08:12:21'),
(85, 1, 1, 'Note has been edited', '2025-05-16 08:12:21'),
(86, 1, 1, 'Note has been edited', '2025-05-16 08:12:33'),
(87, 1, 1, 'Note has been edited', '2025-05-16 08:12:38'),
(88, 1, 1, 'Note has been edited', '2025-05-16 08:12:43'),
(89, 1, 1, 'Note has been edited', '2025-05-16 08:12:45'),
(90, 1, 1, 'Note has been edited', '2025-05-16 08:12:52'),
(91, 1, 1, 'Note has been edited', '2025-05-16 08:12:53'),
(92, 1, 1, 'Note has been edited', '2025-05-16 08:12:54'),
(93, 1, 1, 'Note has been edited', '2025-05-16 08:12:55'),
(94, 1, 1, 'Note has been edited', '2025-05-16 08:12:57'),
(95, 1, 1, 'Note has been edited', '2025-05-16 08:12:58'),
(96, 1, 1, 'Note has been edited', '2025-05-16 08:13:00'),
(97, 1, 1, 'Note has been edited', '2025-05-16 08:13:02'),
(98, 1, 1, 'Note has been edited', '2025-05-16 08:13:03'),
(99, 1, 1, 'Note has been edited', '2025-05-16 08:13:04'),
(100, 1, 1, 'Note has been edited', '2025-05-16 08:13:07'),
(101, 1, 1, 'Note has been edited', '2025-05-16 08:13:07'),
(102, 1, 1, 'Note has been edited', '2025-05-16 08:13:08'),
(103, 1, 1, 'Note has been edited', '2025-05-16 08:13:13'),
(104, 1, 1, 'Note has been edited', '2025-05-16 08:13:13'),
(105, 1, 1, 'Note has been edited', '2025-05-16 08:13:15'),
(106, 1, 1, 'Note has been edited', '2025-05-16 08:13:15'),
(107, 1, 1, 'Note has been edited', '2025-05-16 08:13:19'),
(108, 1, 1, 'Note has been edited', '2025-05-16 08:13:30'),
(109, 1, 1, 'Note has been edited', '2025-05-16 08:13:30'),
(110, 1, 1, 'Note has been edited', '2025-05-16 08:13:32'),
(111, 1, 1, 'Note has been edited', '2025-05-16 08:13:32'),
(112, 1, 1, 'Note has been edited', '2025-05-16 08:13:39'),
(113, 1, 1, 'Note has been edited', '2025-05-16 08:13:41'),
(114, 1, 1, 'Note has been edited', '2025-05-16 08:13:42'),
(115, 1, 1, 'Note has been edited', '2025-05-16 08:13:43'),
(116, 1, 1, 'Note has been edited', '2025-05-16 08:13:43'),
(117, 1, 1, 'Note has been edited', '2025-05-16 08:13:44'),
(118, 1, 1, 'Note has been edited', '2025-05-16 08:13:45'),
(119, 1, 1, 'Note has been edited', '2025-05-16 08:13:46'),
(120, 1, 1, 'Note has been edited', '2025-05-16 08:13:47'),
(121, 1, 1, 'Note has been edited', '2025-05-16 08:13:48'),
(122, 1, 1, 'Note has been edited', '2025-05-16 08:13:49'),
(123, 1, 1, 'Note has been edited', '2025-05-16 08:13:49'),
(124, 1, 1, 'Note has been edited', '2025-05-16 08:13:50'),
(125, 1, 1, 'Note has been edited', '2025-05-16 08:13:51'),
(126, 1, 1, 'Note has been edited', '2025-05-16 08:13:51'),
(127, 1, 1, 'Note has been edited', '2025-05-16 08:13:52'),
(128, 1, 1, 'Note has been edited', '2025-05-16 08:13:55'),
(129, 1, 1, 'Note has been edited', '2025-05-16 08:13:56'),
(130, 1, 1, 'Note has been edited', '2025-05-16 08:13:56'),
(131, 1, 1, 'Note has been edited', '2025-05-16 08:14:13'),
(132, 1, 1, 'Note has been edited', '2025-05-16 08:14:14'),
(133, 1, 1, 'Note has been edited', '2025-05-16 08:14:16'),
(134, 1, 1, 'Note has been edited', '2025-05-16 08:14:17'),
(135, 1, 1, 'Note has been edited', '2025-05-16 08:14:17'),
(136, 1, 1, 'Note has been edited', '2025-05-16 08:14:21'),
(137, 1, 1, 'Note has been edited', '2025-05-16 08:14:56'),
(138, 2, 1, 'Note has been created.', '2025-05-16 08:15:41'),
(139, 2, 1, 'Note has been edited', '2025-05-16 08:15:53'),
(140, 2, 1, 'Note has been edited', '2025-05-16 08:15:54'),
(141, 2, 1, 'Note has been edited', '2025-05-16 08:15:56'),
(142, 2, 1, 'Note has been edited', '2025-05-16 08:15:57'),
(143, 2, 1, 'Note has been edited', '2025-05-16 08:15:57'),
(144, 2, 1, 'Note has been edited', '2025-05-16 08:15:59'),
(145, 2, 1, 'Note has been edited', '2025-05-16 08:16:01'),
(146, 2, 1, 'Note has been edited', '2025-05-16 08:16:02'),
(147, 2, 1, 'Note has been edited', '2025-05-16 08:16:03'),
(148, 2, 1, 'Note has been edited', '2025-05-16 08:16:04'),
(149, 2, 1, 'Note has been edited', '2025-05-16 08:16:05'),
(150, 2, 1, 'Note has been edited', '2025-05-16 08:16:07'),
(151, 2, 1, 'Note has been edited', '2025-05-16 08:16:08'),
(152, 2, 1, 'Note has been edited', '2025-05-16 08:16:11'),
(153, 2, 1, 'Note has been edited', '2025-05-16 08:16:11'),
(154, 2, 1, 'Note has been edited', '2025-05-16 08:16:12'),
(155, 2, 1, 'Note has been edited', '2025-05-16 08:16:12'),
(156, 2, 1, 'Note has been edited', '2025-05-16 08:16:13'),
(157, 2, 1, 'Note has been edited', '2025-05-16 08:16:13'),
(158, 2, 1, 'Note has been edited', '2025-05-16 08:16:14'),
(159, 2, 1, 'Note has been edited', '2025-05-16 08:16:15'),
(160, 2, 1, 'Note has been edited', '2025-05-16 08:16:15'),
(161, 2, 1, 'Note has been edited', '2025-05-16 08:16:17'),
(162, 2, 1, 'Note has been edited', '2025-05-16 08:16:19'),
(163, 2, 1, 'Note has been edited', '2025-05-16 08:16:20'),
(164, 2, 1, 'Note has been edited', '2025-05-16 08:16:21'),
(165, 2, 1, 'Note has been edited', '2025-05-16 08:16:29'),
(166, 2, 1, 'Note has been edited', '2025-05-16 08:16:34'),
(167, 2, 1, 'Note has been edited', '2025-05-16 08:16:34'),
(168, 2, 1, 'Note has been edited', '2025-05-16 08:16:35'),
(169, 2, 1, 'Note has been edited', '2025-05-16 08:16:36'),
(170, 2, 1, 'Note has been edited', '2025-05-16 08:16:36'),
(171, 2, 1, 'Note has been edited', '2025-05-16 08:16:37'),
(172, 2, 1, 'Note has been edited', '2025-05-16 08:16:37'),
(173, 2, 1, 'Note has been edited', '2025-05-16 08:16:38'),
(174, 2, 1, 'Note has been edited', '2025-05-16 08:16:39'),
(175, 2, 1, 'Note has been edited', '2025-05-16 08:16:44'),
(176, 2, 1, 'Note has been edited', '2025-05-16 08:16:46'),
(177, 2, 1, 'Note has been edited', '2025-05-16 08:16:46'),
(178, 2, 1, 'Note has been edited', '2025-05-16 08:16:47'),
(179, 2, 1, 'Note has been edited', '2025-05-16 08:16:48'),
(180, 2, 1, 'Note has been edited', '2025-05-16 08:16:48'),
(181, 2, 1, 'Note has been edited', '2025-05-16 08:16:49'),
(182, 2, 1, 'Note has been edited', '2025-05-16 08:17:04'),
(183, 2, 1, 'Note has been edited', '2025-05-16 08:17:13'),
(184, 2, 1, 'Note has been edited', '2025-05-16 08:17:17'),
(185, 2, 1, 'Note has been edited', '2025-05-16 08:17:19'),
(186, 2, 1, 'Note has been edited', '2025-05-16 08:17:24'),
(187, 2, 1, 'Note has been edited', '2025-05-16 08:17:41'),
(188, 2, 1, 'Note has been edited', '2025-05-16 08:17:42'),
(189, 2, 1, 'Note has been edited', '2025-05-16 08:17:42'),
(190, 2, 1, 'Note has been edited', '2025-05-16 08:17:45'),
(191, 2, 1, 'Note has been edited', '2025-05-16 08:17:45'),
(192, 2, 1, 'Note has been edited', '2025-05-16 08:17:46'),
(193, 2, 1, 'Note has been edited', '2025-05-16 08:17:48'),
(194, 2, 1, 'Note has been edited', '2025-05-16 08:17:50'),
(195, 2, 1, 'Note has been edited', '2025-05-16 08:17:51'),
(196, 2, 1, 'Note has been edited', '2025-05-16 08:17:52'),
(197, 2, 1, 'Note has been edited', '2025-05-16 08:17:54'),
(198, 2, 1, 'Note has been edited', '2025-05-16 08:17:55'),
(199, 2, 1, 'Note has been edited', '2025-05-16 08:17:56'),
(200, 2, 1, 'Note has been edited', '2025-05-16 08:17:57'),
(201, 2, 1, 'Note has been edited', '2025-05-16 08:17:59'),
(202, 2, 1, 'Note has been edited', '2025-05-16 08:18:02'),
(203, 2, 1, 'Note has been edited', '2025-05-16 08:18:03'),
(204, 2, 1, 'Note has been edited', '2025-05-16 08:18:04'),
(205, 2, 1, 'Note has been edited', '2025-05-16 08:18:05'),
(206, 2, 1, 'Note has been edited', '2025-05-16 08:18:05'),
(207, 2, 1, 'Note has been edited', '2025-05-16 08:18:07'),
(208, 2, 1, 'Note has been edited', '2025-05-16 08:18:08'),
(209, 2, 1, 'Note has been edited', '2025-05-16 08:18:08'),
(210, 2, 1, 'Note has been edited', '2025-05-16 08:18:11'),
(211, 2, 1, 'Note has been edited', '2025-05-16 08:18:11'),
(212, 2, 1, 'Note has been edited', '2025-05-16 08:18:13'),
(213, 2, 1, 'Note has been edited', '2025-05-16 08:18:15'),
(214, 2, 1, 'Note has been edited', '2025-05-16 08:18:16'),
(215, 2, 1, 'Note has been edited', '2025-05-16 08:18:19'),
(216, 2, 1, 'Note has been edited', '2025-05-16 08:18:20'),
(217, 2, 1, 'Note has been edited', '2025-05-16 08:18:23'),
(218, 2, 1, 'Note has been edited', '2025-05-16 08:18:25'),
(219, 2, 1, 'Note has been edited', '2025-05-16 08:18:32'),
(220, 3, 1, 'Note has been created.', '2025-05-16 08:19:02'),
(221, 3, 1, 'Note has been edited', '2025-05-16 08:19:06'),
(222, 3, 1, 'Note has been edited', '2025-05-16 08:19:06'),
(223, 3, 1, 'Note has been edited', '2025-05-16 08:19:09'),
(224, 3, 1, 'Password has been changed for note 3', '2025-05-16 08:19:26'),
(225, 2, 1, 'Note has been edited', '2025-05-16 08:19:40'),
(226, 2, 1, 'Note has been edited', '2025-05-16 08:19:43'),
(227, 2, 1, 'Note has been edited', '2025-05-16 08:19:45'),
(228, 2, 1, 'Note has been edited', '2025-05-16 08:19:47'),
(229, 2, 1, 'Note has been edited', '2025-05-16 08:19:49'),
(230, 2, 1, 'Note has been edited', '2025-05-16 08:19:52'),
(231, 2, 1, 'Note has been edited', '2025-05-16 08:19:54'),
(232, 2, 1, 'Note has been edited', '2025-05-16 08:20:02'),
(233, 2, 1, 'Note has been edited', '2025-05-16 08:20:07'),
(234, 2, 1, 'Note has been edited', '2025-05-16 08:20:08'),
(235, 2, 1, 'Note has been edited', '2025-05-16 08:20:11'),
(236, 2, 1, 'Note has been edited', '2025-05-16 08:20:15'),
(237, 1, 1, 'Note has been edited', '2025-05-16 08:22:32'),
(238, 1, 1, 'Note has been edited', '2025-05-16 08:23:47');

-- --------------------------------------------------------

--
-- Table structure for table `note_tags`
--

CREATE TABLE `note_tags` (
  `note_id` int NOT NULL,
  `tag_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `note_tags`
--

INSERT INTO `note_tags` (`note_id`, `tag_id`) VALUES
(1, 1),
(2, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shared_notes`
--

CREATE TABLE `shared_notes` (
  `id` int NOT NULL,
  `note_id` int NOT NULL,
  `recipient_email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `permission` enum('read','edit') COLLATE utf8mb4_general_ci NOT NULL,
  `access_password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `shared_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `user_id`, `name`) VALUES
(1, 1, 'welcome'),
(2, 1, 'tips');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint DEFAULT '0',
  `activation_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `preferences` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `theme` enum('light','dark','auto') COLLATE utf8mb4_general_ci DEFAULT 'auto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `display_name`, `password`, `is_active`, `activation_token`, `preferences`, `image`, `theme`) VALUES
(1, 'demo@notemeadow.store', 'Demo', '$2y$12$NX5dvrK23GJcE6wXrdNDvuSlwwnnLWn8RoeeS2mJ2UL5rHHYKSwY.', 1, '572a59d03707396212179db13b827572', NULL, '', 'auto');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_title` (`title`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_user_id_notes` (`user_id`);

--
-- Indexes for table `note_history`
--
ALTER TABLE `note_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_note_id` (`note_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `note_tags`
--
ALTER TABLE `note_tags`
  ADD PRIMARY KEY (`note_id`,`tag_id`),
  ADD KEY `idx_tag_id` (`tag_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shared_notes`
--
ALTER TABLE `shared_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_shared_by` (`shared_by`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `note_history`
--
ALTER TABLE `note_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shared_notes`
--
ALTER TABLE `shared_notes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_user_id_notes` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `note_history`
--
ALTER TABLE `note_history`
  ADD CONSTRAINT `fk_note_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `note_tags`
--
ALTER TABLE `note_tags`
  ADD CONSTRAINT `fk_note_tags_note` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_note_tags_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shared_notes`
--
ALTER TABLE `shared_notes`
  ADD CONSTRAINT `fk_shared_notes_by` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `fk_tags_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
