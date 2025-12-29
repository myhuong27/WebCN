-- Tạo bảng hỗ trợ chat giữa khách hàng và admin

CREATE TABLE IF NOT EXISTS `chat_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT 'Hỗ trợ khách hàng',
  `status` tinyint(4) DEFAULT 1 COMMENT '0: Đã đóng, 1: Đang mở, 2: Chờ phản hồi',
  `last_message_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `nguoi_dung`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_admin` tinyint(4) DEFAULT 0 COMMENT '0: Khách hàng, 1: Admin',
  `is_read` tinyint(4) DEFAULT 0 COMMENT '0: Chưa đọc, 1: Đã đọc',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `conversation_id` (`conversation_id`),
  KEY `sender_id` (`sender_id`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `nguoi_dung`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
