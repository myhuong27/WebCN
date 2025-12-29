-- phpMyAdmin SQL Dump
-- Hệ thống quản lý nuôi hộ và chăm sóc thú cưng gia đình - PHIÊN BẢN ĐẦY ĐỦ

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `quan_ly_thu_cung`
--

CREATE DATABASE IF NOT EXISTS `quan_ly_thu_cung` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `quan_ly_thu_cung`;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL,
  `ten_dang_nhap` varchar(50) NOT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `ho_ten` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `dia_chi` text DEFAULT NULL,
  `vai_tro` tinyint(4) DEFAULT 0 COMMENT '0: Khách hàng, 1: Nhân viên, 2: Admin',
  `trang_thai` tinyint(4) DEFAULT 1 COMMENT '0: Bị khóa, 1: Hoạt động',
  `avatar` varchar(255) DEFAULT NULL,
  `gioi_tinh` varchar(10) DEFAULT NULL,
  `ngay_sinh` date DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `lan_dang_nhap_cuoi` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `thu_cung`
--

CREATE TABLE `thu_cung` (
  `id` int(11) NOT NULL,
  `ma_thu_cung` varchar(50) NOT NULL,
  `ten_thu_cung` varchar(255) NOT NULL,
  `loai_thu_cung` varchar(100) DEFAULT NULL COMMENT 'Chó, Mèo, Chim, Cá, Hamster...',
  `giong` varchar(100) DEFAULT NULL,
  `tuoi` int(11) DEFAULT NULL,
  `gioi_tinh` varchar(10) DEFAULT NULL COMMENT 'Đực, Cái',
  `can_nang` decimal(10,2) DEFAULT NULL,
  `mau_sac` varchar(100) DEFAULT NULL,
  `tinh_trang_suc_khoe` text DEFAULT NULL,
  `dac_diem_rieng` text DEFAULT NULL COMMENT 'Tính cách, sở thích, hành vi',
  `chu_so_huu_id` int(11) DEFAULT NULL,
  `ngay_tiep_nhan` date DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `trang_thai` tinyint(4) DEFAULT 1 COMMENT '0: Không còn, 1: Đang chăm sóc, 2: Đã trả về',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dữ liệu mẫu cho bảng `thu_cung`
--

INSERT INTO `thu_cung` (`id`, `ma_thu_cung`, `ten_thu_cung`, `loai_thu_cung`, `giong`, `tuoi`, `gioi_tinh`, `can_nang`, `mau_sac`, `tinh_trang_suc_khoe`, `dac_diem_rieng`, `chu_so_huu_id`, `ngay_tiep_nhan`, `hinh_anh`, `ghi_chu`, `trang_thai`, `ngay_tao`) VALUES
(1, 'TC001', 'Milu', 'Chó', 'Golden Retriever', 3, 'Đực', 30.50, 'Vàng', 'Khỏe mạnh, đầy đủ tiêm chủng', 'Rất thân thiện, thích chơi đùa với trẻ em', NULL, '2024-12-01', 'milu.jpg', 'Cần vận động nhiều', 1, NOW()),
(2, 'TC002', 'Bi', 'Mèo', 'Mèo Ba Tư', 2, 'Cái', 4.20, 'Trắng', 'Khỏe mạnh', 'Ít vận động, thích ngủ, hiền lành', NULL, '2024-12-05', 'bi.jpg', 'Cần chải lông hàng ngày', 1, NOW()),
(3, 'TC003', 'Cún', 'Chó', 'Poodle', 1, 'Đực', 5.50, 'Nâu', 'Khỏe mạnh, năng động', 'Thông minh, dễ huấn luyện', NULL, '2024-12-10', 'cun.jpg', 'Cần cắt tỉa lông thường xuyên', 1, NOW()),
(4, 'TC004', 'Kitty', 'Mèo', 'Munchkin', 2, 'Cái', 3.80, 'Xám đốm', 'Khỏe mạnh', 'Chân ngắn, dễ thương, tò mò', NULL, '2024-12-12', 'kitty.jpg', 'Không thể nhảy cao', 1, NOW());

-- --------------------------------------------------------

--
-- Cấu trúc bảng `lich_tiem_phong`
--

CREATE TABLE `lich_tiem_phong` (
  `id` int(11) NOT NULL,
  `thu_cung_id` int(11) NOT NULL,
  `ten_vaccine` varchar(255) NOT NULL,
  `ngay_tiem` date NOT NULL,
  `ngay_tiem_lai` date DEFAULT NULL,
  `noi_tiem` varchar(255) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `trang_thai` tinyint(4) DEFAULT 0 COMMENT '0: Chưa tiêm, 1: Đã tiêm',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dữ liệu mẫu
--

INSERT INTO `lich_tiem_phong` (`id`, `thu_cung_id`, `ten_vaccine`, `ngay_tiem`, `ngay_tiem_lai`, `noi_tiem`, `ghi_chu`, `trang_thai`, `ngay_tao`) VALUES
(1, 1, 'Vaccine 7 bệnh', '2024-11-01', '2025-11-01', 'Phòng khám Thú Y ABC', 'Không phản ứng', 1, NOW()),
(2, 1, 'Vaccine dại', '2024-11-15', '2025-11-15', 'Phòng khám Thú Y ABC', 'Cần tiêm nhắc hàng năm', 1, NOW());

-- --------------------------------------------------------

--
-- Cấu trúc bảng `che_do_an_uong`
--

CREATE TABLE `che_do_an_uong` (
  `id` int(11) NOT NULL,
  `thu_cung_id` int(11) NOT NULL,
  `loai_thuc_an` varchar(255) NOT NULL,
  `thuong_hieu` varchar(255) DEFAULT NULL,
  `lieu_luong` varchar(100) DEFAULT NULL COMMENT 'VD: 200g/ngày',
  `so_bua_moi_ngay` int(11) DEFAULT 2,
  `gio_cho_an` varchar(255) DEFAULT NULL COMMENT 'VD: 7:00, 18:00',
  `thuc_an_cam` text DEFAULT NULL COMMENT 'Danh sách thức ăn không được ăn',
  `ghi_chu` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dữ liệu mẫu
--

INSERT INTO `che_do_an_uong` (`id`, `thu_cung_id`, `loai_thuc_an`, `thuong_hieu`, `lieu_luong`, `so_bua_moi_ngay`, `gio_cho_an`, `thuc_an_cam`, `ghi_chu`, `ngay_tao`) VALUES
(1, 1, 'Thức ăn khô cho chó trưởng thành', 'Royal Canin', '300g/ngày', 2, '7:00, 19:00', 'Chocolate, nho, hành, tỏi', 'Cần nhiều protein', NOW()),
(2, 2, 'Thức ăn ướt cho mèo', 'Whiskas', '150g/ngày', 2, '8:00, 20:00', 'Sữa, chocolate, hành', 'Uống nhiều nước', NOW());

-- --------------------------------------------------------

--
-- Cấu trúc bảng `nhat_ky_suc_khoe`
--

CREATE TABLE `nhat_ky_suc_khoe` (
  `id` int(11) NOT NULL,
  `thu_cung_id` int(11) NOT NULL,
  `ngay_kham` date NOT NULL,
  `trieu_chung` text DEFAULT NULL,
  `chan_doan` text DEFAULT NULL,
  `thuoc_da_dung` text DEFAULT NULL,
  `bac_si` varchar(255) DEFAULT NULL,
  `phong_kham` varchar(255) DEFAULT NULL,
  `chi_phi` decimal(15,2) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `dich_vu`
--

CREATE TABLE `dich_vu` (
  `id` int(11) NOT NULL,
  `ma_dich_vu` varchar(50) NOT NULL,
  `ten_dich_vu` varchar(255) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `gia_dich_vu` decimal(15,2) DEFAULT 0.00,
  `don_vi` varchar(50) DEFAULT NULL COMMENT 'Ngày, Tuần, Tháng, Lần',
  `thoi_gian_thuc_hien` int(11) DEFAULT NULL COMMENT 'Thời gian dự kiến (phút)',
  `hinh_anh` varchar(255) DEFAULT NULL,
  `trang_thai` tinyint(4) DEFAULT 1 COMMENT '0: Ẩn, 1: Hiện',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dữ liệu mẫu
--

INSERT INTO `dich_vu` (`id`, `ma_dich_vu`, `ten_dich_vu`, `mo_ta`, `gia_dich_vu`, `don_vi`, `thoi_gian_thuc_hien`, `hinh_anh`, `trang_thai`, `ngay_tao`) VALUES
(1, 'DV001', 'Nuôi hộ theo ngày', 'Dịch vụ nuôi hộ thú cưng theo ngày, bao gồm thức ăn và chăm sóc cơ bản', 100000.00, 'Ngày', 1440, 'nuoi_ho.jpg', 1, NOW()),
(2, 'DV002', 'Tắm và vệ sinh', 'Tắm, sấy khô, cắt móng, vệ sinh tai cho thú cưng', 150000.00, 'Lần', 60, 'tam_rua.jpg', 1, NOW()),
(3, 'DV003', 'Cắt tỉa lông', 'Dịch vụ cắt tỉa lông chuyên nghiệp theo yêu cầu', 200000.00, 'Lần', 90, 'cat_tia_long.jpg', 1, NOW()),
(4, 'DV004', 'Khám sức khỏe định kỳ', 'Khám sức khỏe tổng quát, tư vấn chăm sóc', 250000.00, 'Lần', 45, 'kham_suc_khoe.jpg', 1, NOW()),
(5, 'DV005', 'Huấn luyện cơ bản', 'Huấn luyện các kỹ năng cơ bản: ngồi, nằm, đứng, đi theo', 500000.00, 'Khóa', 300, 'huan_luyen.jpg', 1, NOW()),
(6, 'DV006', 'Chăm sóc đặc biệt', 'Chăm sóc cho thú cưng ốm, phục hồi sau phẫu thuật', 300000.00, 'Ngày', 1440, 'cham_soc_dac_biet.jpg', 1, NOW());

-- --------------------------------------------------------

--
-- Cấu trúc bảng `yeu_cau_nuoi_ho`
--

CREATE TABLE `yeu_cau_nuoi_ho` (
  `id` int(11) NOT NULL,
  `ma_yeu_cau` varchar(50) NOT NULL,
  `nguoi_gui_id` int(11) NOT NULL,
  `thu_cung_id` int(11) NOT NULL,
  `nguoi_nhan_id` int(11) DEFAULT NULL,
  `ngay_bat_dau` date NOT NULL,
  `ngay_ket_thuc` date NOT NULL,
  `dia_diem` varchar(255) DEFAULT NULL,
  `yeu_cau_dac_biet` text DEFAULT NULL,
  `gia_nuoi_ho` decimal(15,2) DEFAULT NULL,
  `trang_thai` tinyint(4) DEFAULT 0 COMMENT '0: Chờ duyệt, 1: Đã xác nhận, 2: Đang nuôi, 3: Hoàn thành, 4: Từ chối, 5: Hủy',
  `ly_do_tu_choi` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `lich_hen`
--

CREATE TABLE `lich_hen` (
  `id` int(11) NOT NULL,
  `ma_lich_hen` varchar(50) NOT NULL,
  `khach_hang_id` int(11) DEFAULT NULL,
  `thu_cung_id` int(11) DEFAULT NULL,
  `dich_vu_id` int(11) DEFAULT NULL,
  `nhan_vien_id` int(11) DEFAULT NULL,
  `ngay_hen` date NOT NULL,
  `gio_hen` time NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `trang_thai` tinyint(4) DEFAULT 0 COMMENT '0: Chờ xác nhận, 1: Đã xác nhận, 2: Đang thực hiện, 3: Hoàn thành, 4: Đã hủy',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `nhac_lich`
--

CREATE TABLE `nhac_lich` (
  `id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `thu_cung_id` int(11) NOT NULL,
  `loai_nhac` varchar(50) NOT NULL COMMENT 'tiem_chung, kham_suc_khoe, tam, cat_long, cho_an',
  `tieu_de` varchar(255) NOT NULL,
  `noi_dung` text DEFAULT NULL,
  `ngay_nhac` date NOT NULL,
  `gio_nhac` time DEFAULT NULL,
  `da_nhac` tinyint(4) DEFAULT 0 COMMENT '0: Chưa, 1: Đã nhắc',
  `lap_lai` varchar(50) DEFAULT NULL COMMENT 'hang_ngay, hang_tuan, hang_thang, hang_nam',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `bai_viet`
--

CREATE TABLE `bai_viet` (
  `id` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `tom_tat` text DEFAULT NULL,
  `noi_dung` longtext NOT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `danh_muc` varchar(100) DEFAULT NULL COMMENT 'cho, meo, chim, ca, hamster, chung',
  `tac_gia_id` int(11) DEFAULT NULL,
  `luot_xem` int(11) DEFAULT 0,
  `trang_thai` tinyint(4) DEFAULT 1 COMMENT '0: Nháp, 1: Công khai',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `ngay_cap_nhat` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dữ liệu mẫu
--

INSERT INTO `bai_viet` (`id`, `tieu_de`, `slug`, `tom_tat`, `noi_dung`, `hinh_anh`, `danh_muc`, `tac_gia_id`, `luot_xem`, `trang_thai`, `ngay_tao`) VALUES
(1, 'Cách chăm sóc chó Golden Retriever', 'cach-cham-soc-cho-golden-retriever', 'Hướng dẫn chi tiết cách chăm sóc Golden Retriever khỏe mạnh', 'Golden Retriever là giống chó thân thiện, thông minh...', 'golden.jpg', 'cho', NULL, 0, 1, NOW()),
(2, 'Chế độ ăn cho mèo Ba Tư', 'che-do-an-cho-meo-ba-tu', 'Tìm hiểu về chế độ dinh dưỡng phù hợp cho mèo Ba Tư', 'Mèo Ba Tư cần chế độ ăn đặc biệt...', 'persian-cat.jpg', 'meo', NULL, 0, 1, NOW());

-- --------------------------------------------------------

--
-- Cấu trúc bảng `video_huong_dan`
--

CREATE TABLE `video_huong_dan` (
  `id` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `url_video` varchar(500) NOT NULL COMMENT 'YouTube link hoặc video URL',
  `hinh_anh_thumbnail` varchar(255) DEFAULT NULL,
  `danh_muc` varchar(100) DEFAULT NULL,
  `thoi_luong` int(11) DEFAULT NULL COMMENT 'Thời lượng tính bằng giây',
  `luot_xem` int(11) DEFAULT 0,
  `trang_thai` tinyint(4) DEFAULT 1,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `hoa_don`
--

CREATE TABLE `hoa_don` (
  `id` int(11) NOT NULL,
  `ma_hoa_don` varchar(50) NOT NULL,
  `khach_hang_id` int(11) DEFAULT NULL,
  `thu_cung_id` int(11) DEFAULT NULL,
  `tong_tien` decimal(15,2) DEFAULT 0.00,
  `giam_gia` decimal(15,2) DEFAULT 0.00,
  `thanh_tien` decimal(15,2) DEFAULT 0.00,
  `phuong_thuc_thanh_toan` varchar(50) DEFAULT NULL COMMENT 'Tiền mặt, Chuyển khoản, Thẻ, Ví điện tử',
  `trang_thai_thanh_toan` tinyint(4) DEFAULT 0 COMMENT '0: Chưa thanh toán, 1: Đã thanh toán, 2: Đã hoàn tiền',
  `ngay_thanh_toan` datetime DEFAULT NULL,
  `ma_giao_dich` varchar(100) DEFAULT NULL,
  `ghi_chu` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `chi_tiet_hoa_don`
--

CREATE TABLE `chi_tiet_hoa_don` (
  `id` int(11) NOT NULL,
  `hoa_don_id` int(11) DEFAULT NULL,
  `dich_vu_id` int(11) DEFAULT NULL,
  `so_luong` int(11) DEFAULT 1,
  `don_gia` decimal(15,2) NOT NULL,
  `thanh_tien` decimal(15,2) NOT NULL,
  `ghi_chu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `danh_gia`
--

CREATE TABLE `danh_gia` (
  `id` int(11) NOT NULL,
  `nguoi_danh_gia_id` int(11) NOT NULL,
  `loai_danh_gia` varchar(50) NOT NULL COMMENT 'dich_vu, nguoi_nuoi_ho, nhan_vien',
  `doi_tuong_id` int(11) NOT NULL COMMENT 'ID của dịch vụ, người nuôi hộ, hoặc nhân viên',
  `so_sao` tinyint(4) NOT NULL COMMENT '1-5 sao',
  `noi_dung` text DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL,
  `trang_thai` tinyint(4) DEFAULT 1 COMMENT '0: Ẩn, 1: Hiện',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `binh_luan`
--

CREATE TABLE `binh_luan` (
  `id` int(11) NOT NULL,
  `bai_viet_id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `noi_dung` text NOT NULL,
  `parent_id` int(11) DEFAULT NULL COMMENT 'ID bình luận cha (để trả lời)',
  `trang_thai` tinyint(4) DEFAULT 1 COMMENT '0: Ẩn, 1: Hiện',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `lich_su_cham_soc`
--

CREATE TABLE `lich_su_cham_soc` (
  `id` int(11) NOT NULL,
  `thu_cung_id` int(11) DEFAULT NULL,
  `dich_vu_id` int(11) DEFAULT NULL,
  `nhan_vien_id` int(11) DEFAULT NULL,
  `ngay_thuc_hien` datetime DEFAULT current_timestamp(),
  `ghi_chu` text DEFAULT NULL,
  `tinh_trang_sau_cham_soc` text DEFAULT NULL,
  `hinh_anh` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng `thong_bao`
--

CREATE TABLE `thong_bao` (
  `id` int(11) NOT NULL,
  `nguoi_dung_id` int(11) NOT NULL,
  `tieu_de` varchar(255) NOT NULL,
  `noi_dung` text NOT NULL,
  `loai` varchar(50) DEFAULT NULL COMMENT 'lich_hen, nuoi_ho, thanh_toan, he_thong',
  `da_doc` tinyint(4) DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Chỉ mục cho các bảng
--

ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ten_dang_nhap` (`ten_dang_nhap`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `thu_cung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_thu_cung` (`ma_thu_cung`),
  ADD KEY `chu_so_huu_id` (`chu_so_huu_id`);

ALTER TABLE `lich_tiem_phong`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thu_cung_id` (`thu_cung_id`);

ALTER TABLE `che_do_an_uong`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thu_cung_id` (`thu_cung_id`);

ALTER TABLE `nhat_ky_suc_khoe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thu_cung_id` (`thu_cung_id`);

ALTER TABLE `dich_vu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_dich_vu` (`ma_dich_vu`);

ALTER TABLE `yeu_cau_nuoi_ho`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_yeu_cau` (`ma_yeu_cau`),
  ADD KEY `nguoi_gui_id` (`nguoi_gui_id`),
  ADD KEY `nguoi_nhan_id` (`nguoi_nhan_id`),
  ADD KEY `thu_cung_id` (`thu_cung_id`);

ALTER TABLE `lich_hen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_lich_hen` (`ma_lich_hen`),
  ADD KEY `khach_hang_id` (`khach_hang_id`),
  ADD KEY `thu_cung_id` (`thu_cung_id`),
  ADD KEY `dich_vu_id` (`dich_vu_id`),
  ADD KEY `nhan_vien_id` (`nhan_vien_id`);

ALTER TABLE `nhac_lich`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nguoi_dung_id` (`nguoi_dung_id`),
  ADD KEY `thu_cung_id` (`thu_cung_id`);

ALTER TABLE `bai_viet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `tac_gia_id` (`tac_gia_id`);

ALTER TABLE `video_huong_dan`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `hoa_don`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ma_hoa_don` (`ma_hoa_don`),
  ADD KEY `khach_hang_id` (`khach_hang_id`),
  ADD KEY `thu_cung_id` (`thu_cung_id`);

ALTER TABLE `chi_tiet_hoa_don`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hoa_don_id` (`hoa_don_id`),
  ADD KEY `dich_vu_id` (`dich_vu_id`);

ALTER TABLE `danh_gia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nguoi_danh_gia_id` (`nguoi_danh_gia_id`);

ALTER TABLE `binh_luan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bai_viet_id` (`bai_viet_id`),
  ADD KEY `nguoi_dung_id` (`nguoi_dung_id`),
  ADD KEY `parent_id` (`parent_id`);

ALTER TABLE `lich_su_cham_soc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `thu_cung_id` (`thu_cung_id`),
  ADD KEY `dich_vu_id` (`dich_vu_id`),
  ADD KEY `nhan_vien_id` (`nhan_vien_id`);

ALTER TABLE `thong_bao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nguoi_dung_id` (`nguoi_dung_id`);

--
-- AUTO_INCREMENT cho các bảng
--

ALTER TABLE `nguoi_dung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `thu_cung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `lich_tiem_phong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `che_do_an_uong`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `nhat_ky_suc_khoe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `dich_vu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `yeu_cau_nuoi_ho`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lich_hen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `nhac_lich`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `bai_viet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `video_huong_dan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `hoa_don`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `chi_tiet_hoa_don`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `danh_gia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `binh_luan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `lich_su_cham_soc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `thong_bao`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Các ràng buộc cho các bảng
--

ALTER TABLE `thu_cung`
  ADD CONSTRAINT `thu_cung_ibfk_1` FOREIGN KEY (`chu_so_huu_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL;

ALTER TABLE `lich_tiem_phong`
  ADD CONSTRAINT `lich_tiem_phong_ibfk_1` FOREIGN KEY (`thu_cung_id`) REFERENCES `thu_cung` (`id`) ON DELETE CASCADE;

ALTER TABLE `che_do_an_uong`
  ADD CONSTRAINT `che_do_an_uong_ibfk_1` FOREIGN KEY (`thu_cung_id`) REFERENCES `thu_cung` (`id`) ON DELETE CASCADE;

ALTER TABLE `nhat_ky_suc_khoe`
  ADD CONSTRAINT `nhat_ky_suc_khoe_ibfk_1` FOREIGN KEY (`thu_cung_id`) REFERENCES `thu_cung` (`id`) ON DELETE CASCADE;

ALTER TABLE `yeu_cau_nuoi_ho`
  ADD CONSTRAINT `yeu_cau_nuoi_ho_ibfk_1` FOREIGN KEY (`nguoi_gui_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `yeu_cau_nuoi_ho_ibfk_2` FOREIGN KEY (`nguoi_nhan_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `yeu_cau_nuoi_ho_ibfk_3` FOREIGN KEY (`thu_cung_id`) REFERENCES `thu_cung` (`id`) ON DELETE CASCADE;

ALTER TABLE `lich_hen`
  ADD CONSTRAINT `lich_hen_ibfk_1` FOREIGN KEY (`khach_hang_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lich_hen_ibfk_2` FOREIGN KEY (`thu_cung_id`) REFERENCES `thu_cung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lich_hen_ibfk_3` FOREIGN KEY (`dich_vu_id`) REFERENCES `dich_vu` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lich_hen_ibfk_4` FOREIGN KEY (`nhan_vien_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL;

ALTER TABLE `nhac_lich`
  ADD CONSTRAINT `nhac_lich_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nhac_lich_ibfk_2` FOREIGN KEY (`thu_cung_id`) REFERENCES `thu_cung` (`id`) ON DELETE CASCADE;

ALTER TABLE `bai_viet`
  ADD CONSTRAINT `bai_viet_ibfk_1` FOREIGN KEY (`tac_gia_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL;

ALTER TABLE `hoa_don`
  ADD CONSTRAINT `hoa_don_ibfk_1` FOREIGN KEY (`khach_hang_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `hoa_don_ibfk_2` FOREIGN KEY (`thu_cung_id`) REFERENCES `thu_cung` (`id`) ON DELETE SET NULL;

ALTER TABLE `chi_tiet_hoa_don`
  ADD CONSTRAINT `chi_tiet_hoa_don_ibfk_1` FOREIGN KEY (`hoa_don_id`) REFERENCES `hoa_don` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_hoa_don_ibfk_2` FOREIGN KEY (`dich_vu_id`) REFERENCES `dich_vu` (`id`) ON DELETE CASCADE;

ALTER TABLE `danh_gia`
  ADD CONSTRAINT `danh_gia_ibfk_1` FOREIGN KEY (`nguoi_danh_gia_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

ALTER TABLE `binh_luan`
  ADD CONSTRAINT `binh_luan_ibfk_1` FOREIGN KEY (`bai_viet_id`) REFERENCES `bai_viet` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `binh_luan_ibfk_2` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `binh_luan_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `binh_luan` (`id`) ON DELETE CASCADE;

ALTER TABLE `lich_su_cham_soc`
  ADD CONSTRAINT `lich_su_cham_soc_ibfk_1` FOREIGN KEY (`thu_cung_id`) REFERENCES `thu_cung` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lich_su_cham_soc_ibfk_2` FOREIGN KEY (`dich_vu_id`) REFERENCES `dich_vu` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lich_su_cham_soc_ibfk_3` FOREIGN KEY (`nhan_vien_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE SET NULL;

ALTER TABLE `thong_bao`
  ADD CONSTRAINT `thong_bao_ibfk_1` FOREIGN KEY (`nguoi_dung_id`) REFERENCES `nguoi_dung` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
