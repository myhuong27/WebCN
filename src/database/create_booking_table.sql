-- Tạo bảng đặt lịch dịch vụ
CREATE TABLE IF NOT EXISTS `dat_lich_dich_vu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_dat_lich` varchar(50) NOT NULL UNIQUE,
  `nguoi_dung_id` int(11) NOT NULL,
  `dich_vu_id` int(11) NOT NULL,
  `thu_cung_id` int(11) DEFAULT NULL,
  `ngay_dat_lich` date NOT NULL,
  `gio_dat_lich` time NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `tong_tien` decimal(10,2) NOT NULL,
  `trang_thai_thanh_toan` varchar(50) DEFAULT 'chua_thanh_toan' COMMENT 'chua_thanh_toan, da_thanh_toan, hoan_tien',
  `phuong_thuc_thanh_toan` varchar(50) DEFAULT NULL COMMENT 'tien_mat, chuyen_khoan, momo, vnpay',
  `trang_thai` varchar(50) DEFAULT 'cho_xac_nhan' COMMENT 'cho_xac_nhan, da_xac_nhan, dang_thuc_hien, hoan_thanh, huy',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nguoi_dung_id` (`nguoi_dung_id`),
  KEY `dich_vu_id` (`dich_vu_id`),
  KEY `thu_cung_id` (`thu_cung_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
