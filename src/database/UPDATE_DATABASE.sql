-- ============================================
-- SCRIPT CẬP NHẬT DATABASE - ĐỂ KHỚP VỚI CẤU TRÚC HIỆN TẠI
-- Chạy script này nếu bạn đã import database_full.sql trước đó
-- ============================================

USE quan_ly_thu_cung;

-- Thêm các trường còn thiếu vào bảng nguoi_dung
ALTER TABLE `nguoi_dung` 
ADD COLUMN `ten_dang_nhap` varchar(50) AFTER `id`,
ADD COLUMN `dia_chi` text AFTER `so_dien_thoai`,
ADD COLUMN `avatar` varchar(255) AFTER `trang_thai`,
ADD COLUMN `gioi_tinh` varchar(10) AFTER `avatar`,
ADD COLUMN `ngay_sinh` date AFTER `gioi_tinh`,
ADD COLUMN `lan_dang_nhap_cuoi` timestamp NULL DEFAULT NULL AFTER `ngay_tao`;

-- Tạo username từ email cho các user hiện có
UPDATE `nguoi_dung` 
SET `ten_dang_nhap` = SUBSTRING_INDEX(`email`, '@', 1)
WHERE `ten_dang_nhap` IS NULL OR `ten_dang_nhap` = '';

-- Thêm unique constraint cho ten_dang_nhap
ALTER TABLE `nguoi_dung` 
ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`);

-- Tạo tài khoản admin mới với username
INSERT INTO `nguoi_dung` 
(`ten_dang_nhap`, `mat_khau`, `ho_ten`, `email`, `so_dien_thoai`, `vai_tro`, `trang_thai`) 
VALUES 
('admin', MD5('admin123'), 'Administrator', 'admin@petcare.com', '0123456789', 2, 1)
ON DUPLICATE KEY UPDATE 
`ten_dang_nhap` = 'admin',
`mat_khau` = MD5('admin123');

-- Tạo một số tài khoản demo
INSERT INTO `nguoi_dung` 
(`ten_dang_nhap`, `mat_khau`, `ho_ten`, `email`, `so_dien_thoai`, `gioi_tinh`, `vai_tro`, `trang_thai`) 
VALUES 
('nhanvien1', MD5('123456'), 'Nguyễn Văn Nam', 'nhanvien1@petcare.com', '0987654321', 'Nam', 1, 1),
('khachhang1', MD5('123456'), 'Trần Thị Lan', 'khachhang1@gmail.com', '0912345678', 'Nữ', 0, 1),
('khachhang2', MD5('123456'), 'Lê Văn Hùng', 'khachhang2@gmail.com', '0923456789', 'Nam', 0, 1)
ON DUPLICATE KEY UPDATE `trang_thai` = 1;

-- Kiểm tra kết quả
SELECT id, ten_dang_nhap, ho_ten, email, vai_tro, trang_thai 
FROM nguoi_dung 
ORDER BY vai_tro DESC, id ASC;
