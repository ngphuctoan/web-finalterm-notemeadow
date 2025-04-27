-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 05, 2025 lúc 06:25 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `note_management`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_pinned` tinyint(4) DEFAULT 0,
  `category` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `font_size` varchar(20) DEFAULT '16px',
  `note_color` varchar(7) DEFAULT '#ffffff',
  `status_pass` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `notes`
--

INSERT INTO `notes` (`id`, `user_id`, `title`, `content`, `created_at`, `modified_at`, `is_pinned`, `category`, `tags`, `password`, `image`, `font_size`, `note_color`, `status_pass`) VALUES
(64, 18, 'Nonem', 'content', '2025-03-10 17:07:06', '2025-03-31 04:34:23', 1, 'category', 'omg,igi', '', '[\"uploads\\/3.jpg\",\"uploads\\/8.jpg\",\"uploads\\/5.jpg\"]', '16px', '#5e55e9', 1),
(65, 18, 'sdijsfil', 'haizz', '2025-03-10 17:08:35', '2025-03-30 23:44:41', 1, 'category', 'omg,igi', '123456', '[\"uploads\\/1.png\",\"uploads\\/8.png\",\"uploads\\/2.png\"]', '16px', '#5e55e9', 1),
(66, 18, 'sdijsfil', 'content', '2025-03-10 17:40:12', '2025-03-31 04:26:25', 1, 'category', 'omg,igi', '', '[\"uploads\\/4.jpg\"]', '16px', '#5e55e9', 0),
(67, 18, 'Test cap nhat ghi chu', 'content', '2025-03-10 17:40:15', '2025-03-31 04:34:38', 1, 'category', 'omg,igi', 'testthyu', '[\"uploads\\/3.jpg\",\"uploads\\/8.png\",\"uploads\\/4.jpg\"]', '16px', '#5e55e9', 1),
(69, 18, 'Tôi đang thử ghi chú', 'Tôi đang thử ghi chú bằng api', '2025-03-11 07:05:15', '2025-04-05 13:07:45', 0, 'test, api', 'shjk, test thử', '', '[\"uploads\\/4.jpg\",\"uploads\\/8.png\",\"uploads\\/2.png\"]', '16px', '#5e55e9', 0),
(76, 35, '7h52 note mowis', 'content', '2025-03-18 05:20:50', '2025-04-05 16:11:16', 0, 'category', 'omg,igi', '', '[\"uploads\\/1.png\",\"uploads\\/8.png\",\"uploads\\/7.png\"]', '16px', '#ffffff', 1),
(91, 35, 'abc', 'contentcontent', '2025-04-01 05:22:29', '2025-04-05 16:04:00', 0, 'sjcj', 'new_tag_, kjsahl', '12345678', '[\"uploads\\/oppo-find-n5-black-thumb-600x600.jpg\",\"uploads\\/vivo-y100-128gb-(10).jpg\"]', '20px', '#5e55e9', 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `note_history`
--

CREATE TABLE `note_history` (
  `id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `note_history`
--

INSERT INTO `note_history` (`id`, `note_id`, `user_id`, `action`, `timestamp`) VALUES
(52, 64, 18, 'Đã tạo mới ghi chú.', '2025-03-11 00:07:06'),
(55, 65, 18, 'Đã chia sẻ ghi chú với quocdat51930@gmail.com', '2025-03-11 00:25:50'),
(56, 66, 18, 'Đã tạo mới ghi chú.', '2025-03-11 00:40:12'),
(57, 67, 18, 'Đã tạo mới ghi chú.', '2025-03-11 00:40:15'),
(58, 69, 18, 'Đã tạo mới ghi chú.', '2025-03-11 14:05:15'),
(59, 67, 18, 'Đã thay đổi mật khẩu ghi chú cá nhân 67', '2025-03-11 14:28:43'),
(60, 67, 18, 'Đã thay đổi mật khẩu ghi chú 67', '2025-03-11 14:28:43'),
(61, 67, 18, 'Bảo vệ bằng mật khẩu đã được tắt.', '2025-03-11 14:36:01'),
(62, 65, 18, 'Đã chia sẻ ghi chú với quocdat51930@gmail.com', '2025-03-11 15:07:21'),
(63, 65, 18, 'Đã chia sẻ ghi chú với quocdat51930@gmail.com', '2025-03-11 15:11:22'),
(65, 65, 18, 'Đã chỉnh sửa quyền truy cập ghi chú thành read', '2025-03-12 10:16:47'),
(67, 67, 18, 'Đã thay đổi mật khẩu ghi chú cá nhân 67', '2025-03-13 19:27:45'),
(68, 67, 18, 'Đã thay đổi mật khẩu ghi chú 67', '2025-03-13 19:30:43'),
(69, 67, 18, 'Đã thay đổi mật khẩu ghi chú 67', '2025-03-13 19:32:19'),
(70, 67, 18, 'Đã thay đổi mật khẩu ghi chú 67', '2025-03-13 19:32:35'),
(72, 71, 18, 'Đã tạo mới ghi chú.', '2025-03-18 12:17:55'),
(73, 72, 18, 'Đã tạo mới ghi chú.', '2025-03-18 12:18:24'),
(74, 73, 18, 'Đã tạo mới ghi chú.', '2025-03-18 12:19:40'),
(75, 74, 18, 'Đã tạo mới ghi chú.', '2025-03-18 12:19:49'),
(76, 75, 18, 'Đã tạo mới ghi chú.', '2025-03-18 12:20:17'),
(77, 76, 18, 'Đã tạo mới ghi chú.', '2025-03-18 12:20:50'),
(78, 77, 18, 'Đã tạo mới ghi chú.', '2025-04-01 11:50:24'),
(79, 78, 18, 'Đã tạo mới ghi chú.', '2025-04-01 11:51:54'),
(80, 79, 18, 'Đã tạo mới ghi chú.', '2025-04-01 11:52:19'),
(81, 80, 18, 'Đã tạo mới ghi chú.', '2025-04-01 11:54:34'),
(82, 81, 18, 'Đã tạo mới ghi chú.', '2025-04-01 11:55:46'),
(83, 82, 18, 'Đã tạo mới ghi chú.', '2025-04-01 11:56:43'),
(84, 83, 18, 'Đã tạo mới ghi chú.', '2025-04-01 11:57:06'),
(85, 84, 18, 'Đã tạo mới ghi chú.', '2025-04-01 11:58:15'),
(86, 85, 18, 'Đã tạo mới ghi chú.', '2025-04-01 11:58:50'),
(87, 86, 18, 'Đã tạo mới ghi chú.', '2025-04-01 12:00:19'),
(88, 87, 18, 'Đã tạo mới ghi chú.', '2025-04-01 12:01:32'),
(89, 88, 18, 'Đã tạo mới ghi chú.', '2025-04-01 12:02:05'),
(90, 89, 18, 'Đã tạo mới ghi chú.', '2025-04-01 12:02:07'),
(91, 90, 18, 'Đã tạo mới ghi chú.', '2025-04-01 12:03:13'),
(92, 91, 18, 'Đã tạo mới ghi chú.', '2025-04-01 12:22:29'),
(93, 16, 18, 'Đã thu hồi quyền chia sẻ ghi chú 42', '2025-04-01 16:28:49'),
(94, 26, 18, 'Đã thu hồi quyền chia sẻ ghi chú 62', '2025-04-01 16:29:15'),
(95, 62, 18, 'Đã chia sẻ ghi chú với quocdat51930@gmail.com', '2025-04-01 16:29:25'),
(96, 63, 18, 'Đã chia sẻ ghi chú với quocdat51930@gmail.com', '2025-04-01 16:40:10'),
(97, 92, 33, 'Đã tạo mới ghi chú.', '2025-04-05 18:28:52'),
(98, 91, 33, 'Đã chia sẻ ghi chú với quocdat51930@gmail.com', '2025-04-05 18:33:58'),
(99, 91, 33, 'Đã thay đổi mật khẩu ghi chú 91', '2025-04-05 20:21:01'),
(100, 91, 33, 'Đã thay đổi mật khẩu ghi chú 91', '2025-04-05 20:21:30'),
(101, 91, 33, 'Bảo vệ bằng mật khẩu đã được tắt.', '2025-04-05 20:24:16'),
(102, 91, 33, 'Bảo vệ bằng mật khẩu đã được tắt.', '2025-04-05 20:25:07'),
(103, 91, 33, 'Bảo vệ bằng mật khẩu đã được vô hiệu hóa.', '2025-04-05 20:30:31'),
(104, 91, 33, 'Bảo vệ bằng mật khẩu đã bị vô hiệu hóa.', '2025-04-05 20:32:32'),
(105, 91, 33, 'Đã chỉnh sửa quyền truy cập ghi chú thành read', '2025-04-05 21:07:46'),
(106, 91, 33, 'Đã chia sẻ ghi chú với yuu3110duong@gmail.com', '2025-04-05 21:08:52'),
(107, 30, 33, 'Đã thu hồi quyền chia sẻ ghi chú 91', '2025-04-05 21:10:30'),
(108, 93, 35, 'Đã tạo mới ghi chú.', '2025-04-05 22:22:13'),
(109, 91, 35, 'Đã chia sẻ ghi chú với mtriet10052005@gmail.com', '2025-04-05 22:24:46'),
(110, 91, 35, 'Đã thay đổi mật khẩu ghi chú 91', '2025-04-05 22:41:05'),
(111, 91, 35, 'Bảo vệ bằng mật khẩu đã được kích hoạt.', '2025-04-05 22:42:26'),
(112, 91, 35, 'Bảo vệ bằng mật khẩu đã bị vô hiệu hóa.', '2025-04-05 22:42:44'),
(113, 91, 35, 'Đã chỉnh sửa quyền truy cập ghi chú thành read', '2025-04-05 22:43:49'),
(114, 31, 35, 'Đã thu hồi quyền chia sẻ ghi chú 91', '2025-04-05 22:44:35');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `note_tags`
--

CREATE TABLE `note_tags` (
  `note_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `note_tags`
--

INSERT INTO `note_tags` (`note_id`, `tag_id`) VALUES
(64, 5),
(64, 6),
(65, 5),
(65, 6),
(66, 5),
(66, 6),
(67, 5),
(67, 6),
(69, 7),
(69, 8),
(76, 5),
(76, 6),
(91, 11),
(91, 12);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires`, `created_at`) VALUES
(6, 'quocdatgmail@gmail.com', '5d6ca4edc5585b656f5ea338b49450efc6262a23e078db4bb153862bcc2149f6a425936283285413e1351d59a16e0c89b416', '2025-03-28 15:25:40', '2025-03-28 14:10:40');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shared_notes`
--

CREATE TABLE `shared_notes` (
  `id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  `permission` enum('read','edit') NOT NULL,
  `access_password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `shared_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `shared_notes`
--

INSERT INTO `shared_notes` (`id`, `note_id`, `recipient_email`, `permission`, `access_password`, `created_at`, `shared_by`) VALUES
(27, 62, 'quocdat51930@gmail.com', 'edit', '23ea544dd3', '2025-04-01 16:29:22', 18),
(28, 63, 'quocdat51930@gmail.com', 'edit', '212e771079', '2025-04-01 16:40:06', 18),
(29, 91, 'quocdat51930@gmail.com', 'edit', '34653d9708', '2025-04-05 18:33:54', 33);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tags`
--

INSERT INTO `tags` (`id`, `user_id`, `name`) VALUES
(5, 18, 'omg'),
(6, 18, 'igi'),
(7, 18, 'demo'),
(8, 18, 'test thử'),
(11, 18, 'jsjk'),
(12, 18, 'kjsdn'),
(13, 33, 'Tag mới thêm nè'),
(14, 33, 'new_tag_');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(4) DEFAULT 0,
  `activation_token` varchar(255) DEFAULT NULL,
  `preferences` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `image` varchar(255) DEFAULT 'https://cdn-icons-png.flaticon.com/512/9187/9187604.png',
  `theme` enum('light','dark') DEFAULT 'light'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `email`, `display_name`, `password`, `is_active`, `activation_token`, `preferences`, `image`, `theme`) VALUES
(18, 'quocdatforworkv2@gmail.com', 'Nguyễn Quốc', '$2y$10$dK2kZU/vsXtmjUPOkM1yk.VL/o9q7p75xOLEdw3F3D7ACuBOCGvzW', 1, NULL, NULL, 'uploads/Anomaly detection.png', 'dark'),
(28, 'itdatit12@gmail.com', 'Nguyen Dat', '$2y$10$GClj6wQaW6fEHHDmqzZvzeYrqAbgDl2gaa1x/JAm92/Yc53lsaTNy', 1, NULL, NULL, 'uploads/FinU_logo.png', 'dark'),
(33, 'matnaosua@gmail.com', 'Nguyễn Quốc', '$2y$10$4dAE8D0UKekp28KEKd/uReJla59YgznqnZ74/9op8NEmm9lAx9F/y', 0, NULL, 'ụaeoij', 'uploads/oppo-find-n5-black-thumb-600x600.jpg', 'dark'),
(35, 'quocdat51930@gmail.com', 'Nguyễn Quốc', '$2y$10$MNp9noGYwc4mBPf3B6Jz0uwlKNZTicJ8fPQ/Q6Ij.WaLzbEuqTKH2', 1, NULL, 'ụaeoij', 'uploads/oppo-find-n5-black-thumb-600x600.jpg', 'dark');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id_notes` (`user_id`),
  ADD KEY `idx_title` (`title`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Chỉ mục cho bảng `note_history`
--
ALTER TABLE `note_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `note_id` (`note_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `note_tags`
--
ALTER TABLE `note_tags`
  ADD PRIMARY KEY (`note_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Chỉ mục cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `shared_notes`
--
ALTER TABLE `shared_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_shared_notes_by` (`shared_by`);

--
-- Chỉ mục cho bảng `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT cho bảng `note_history`
--
ALTER TABLE `note_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT cho bảng `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `shared_notes`
--
ALTER TABLE `shared_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT cho bảng `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_user_id_notes` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `note_history`
--
ALTER TABLE `note_history`
  ADD CONSTRAINT `note_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `note_tags`
--
ALTER TABLE `note_tags`
  ADD CONSTRAINT `note_tags_ibfk_1` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `note_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `shared_notes`
--
ALTER TABLE `shared_notes`
  ADD CONSTRAINT `fk_shared_notes_by` FOREIGN KEY (`shared_by`) REFERENCES `users` (`id`);

--
-- Các ràng buộc cho bảng `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `tags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
