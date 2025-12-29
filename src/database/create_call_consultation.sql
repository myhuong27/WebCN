-- Tạo bảng yêu cầu tư vấn qua điện thoại

CREATE TABLE IF NOT EXISTS `yeu_cau_goi_dien` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_yeu_cau_id` int(11) NOT NULL,
  `so_dien_thoai` varchar(20) NOT NULL,
  `chu_de` varchar(255) DEFAULT 'Tư vấn dịch vụ',
  `noi_dung` text,
  `thoi_gian_mong_muon` datetime DEFAULT NULL,
  `trang_thai` tinyint(4) DEFAULT 0 COMMENT '0: Chờ gọi, 1: Đã gọi, 2: Không liên lạc được, 3: Hủy',
  `nguoi_xu_ly_id` int(11) DEFAULT NULL,
  `ghi_chu_admin` text,
  `thoi_gian_goi` datetime DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nguoi_yeu_cau_id` (`nguoi_yeu_cau_id`),
  KEY `trang_thai` (`trang_thai`),
  KEY `thoi_gian_mong_muon` (`thoi_gian_mong_muon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
