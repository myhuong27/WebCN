-- Tạo bảng lịch chăm sóc thú cưng
CREATE TABLE IF NOT EXISTS `lich_cham_soc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thu_cung_id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `loai_lich` varchar(50) NOT NULL COMMENT 'tiem_phong, tam, cho_an, kham_suc_khoe, khac',
  `tieu_de` varchar(255) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `ngay_thuc_hien` date NOT NULL,
  `gio_thuc_hien` time DEFAULT NULL,
  `lap_lai` varchar(50) DEFAULT NULL COMMENT 'khong, hang_ngay, hang_tuan, hang_thang, hang_nam',
  `nhac_truoc` int(11) DEFAULT 1 COMMENT 'Số ngày nhắc trước',
  `trang_thai` varchar(50) DEFAULT 'cho_thuc_hien' COMMENT 'cho_thuc_hien, hoan_thanh, bo_qua',
  `ghi_chu` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `thu_cung_id` (`thu_cung_id`),
  KEY `nguoi_dung_id` (`nguoi_dung_id`),
  KEY `ngay_thuc_hien` (`ngay_thuc_hien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tạo bảng đánh giá dịch vụ
CREATE TABLE IF NOT EXISTS `danh_gia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nguoi_dung_id` int(11) NOT NULL,
  `dich_vu_id` int(11) DEFAULT NULL,
  `dat_lich_id` int(11) DEFAULT NULL,
  `so_sao` int(11) NOT NULL CHECK (`so_sao` >= 1 AND `so_sao` <= 5),
  `tieu_de` varchar(255) DEFAULT NULL,
  `noi_dung` text NOT NULL,
  `trang_thai` tinyint(4) DEFAULT 1 COMMENT '0: Ẩn, 1: Hiển thị',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nguoi_dung_id` (`nguoi_dung_id`),
  KEY `dich_vu_id` (`dich_vu_id`),
  KEY `dat_lich_id` (`dat_lich_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tạo bảng thanh toán
CREATE TABLE IF NOT EXISTS `thanh_toan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_thanh_toan` varchar(50) NOT NULL UNIQUE,
  `nguoi_dung_id` int(11) NOT NULL,
  `dat_lich_id` int(11) DEFAULT NULL,
  `so_tien` decimal(10,2) NOT NULL,
  `phuong_thuc` varchar(50) NOT NULL COMMENT 'tien_mat, chuyen_khoan, momo, vnpay, zalopay',
  `trang_thai` varchar(50) DEFAULT 'cho_thanh_toan' COMMENT 'cho_thanh_toan, thanh_cong, that_bai, hoan_tien',
  `ma_giao_dich` varchar(255) DEFAULT NULL,
  `thong_tin_thanh_toan` text DEFAULT NULL,
  `ngay_thanh_toan` datetime DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nguoi_dung_id` (`nguoi_dung_id`),
  KEY `dat_lich_id` (`dat_lich_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu lịch chăm sóc
INSERT INTO `lich_cham_soc` (`thu_cung_id`, `nguoi_dung_id`, `loai_lich`, `tieu_de`, `mo_ta`, `ngay_thuc_hien`, `gio_thuc_hien`, `lap_lai`, `nhac_truoc`, `trang_thai`) VALUES
(1, 1, 'tiem_phong', 'Tiêm phòng dại lần 2', 'Tiêm vaccine phòng bệnh dại mũi 2', '2025-12-25', '09:00:00', 'khong', 1, 'cho_thuc_hien'),
(1, 1, 'tam', 'Tắm rửa định kỳ', 'Tắm và chải lông', '2025-12-28', '14:00:00', 'hang_tuan', 1, 'cho_thuc_hien'),
(2, 1, 'cho_an', 'Cho ăn sáng', 'Thức ăn hạt khô 50g', '2025-12-23', '07:00:00', 'hang_ngay', 0, 'cho_thuc_hien'),
(2, 1, 'kham_suc_khoe', 'Kiểm tra sức khỏe định kỳ', 'Khám tổng quát và kiểm tra răng miệng', '2025-12-30', '10:00:00', 'hang_thang', 3, 'cho_thuc_hien');
