-- Tạo bảng thông báo

CREATE TABLE IF NOT EXISTS `thong_bao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_nhan_id` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `noi_dung` text NOT NULL,
  `loai` varchar(50) DEFAULT 'info' COMMENT 'info, success, warning, error',
  `icon` varchar(50) DEFAULT 'fa-bell',
  `lien_ket` varchar(255) DEFAULT NULL,
  `da_doc` tinyint(1) DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nguoi_nhan_id` (`nguoi_nhan_id`),
  KEY `da_doc` (`da_doc`),
  KEY `ngay_tao` (`ngay_tao`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm một số thông báo mẫu
INSERT INTO `thong_bao` (`nguoi_nhan_id`, `tieu_de`, `noi_dung`, `loai`, `icon`, `lien_ket`, `da_doc`) 
SELECT 
    nd.id,
    'Nhắc lịch tiêm phòng',
    'Thú cưng của bạn cần tiêm phòng dại trong 2 ngày tới',
    'warning',
    'fa-calendar-check',
    'user/quan_ly_thucung_user.php',
    0
FROM nguoi_dung nd
WHERE nd.vai_tro = 1
LIMIT 1;
