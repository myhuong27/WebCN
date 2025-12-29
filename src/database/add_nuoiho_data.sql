-- Thêm dữ liệu mẫu cho bảng yeu_cau_nuoi_ho

-- Kiểm tra và thêm cột gia_tien nếu chưa có (yeucau_nuoiho.php sử dụng gia_tien thay vì gia_nuoi_ho)
ALTER TABLE yeu_cau_nuoi_ho ADD COLUMN IF NOT EXISTS gia_tien DECIMAL(15,2) DEFAULT NULL;

-- Xóa dữ liệu cũ nếu có
TRUNCATE TABLE yeu_cau_nuoi_ho;

-- Thêm yêu cầu nuôi hộ mẫu
INSERT INTO yeu_cau_nuoi_ho (id, ma_yeu_cau, nguoi_gui_id, thu_cung_id, ngay_bat_dau, ngay_ket_thuc, dia_diem, yeu_cau_dac_biet, gia_tien, trang_thai, ngay_tao) VALUES
(1, 'YC001', 1, 1, '2025-12-28', '2026-01-05', 'Quận 1, TP.HCM', 'Cần cho ăn 3 bữa/ngày, tắm 1 lần/tuần. Chó rất ngoan và thân thiện với trẻ em.', 500000, 0, NOW()),
(2, 'YC002', 1, 2, '2025-12-30', '2026-01-10', 'Quận 3, TP.HCM', 'Mèo ăn pate, cần vệ sinh khay cát hàng ngày. Không thích ồn ào.', 400000, 0, NOW()),
(3, 'YC003', 2, 3, '2026-01-02', '2026-01-15', 'Quận Bình Thạnh, TP.HCM', 'Chó Golden cần vận động nhiều, dắt đi dạo 2 lần/ngày. Rất hiền lành.', 600000, 0, NOW()),
(4, 'YC004', 2, 4, '2026-01-05', '2026-01-20', 'Quận 7, TP.HCM', 'Mèo Ba Tư cần chải lông hàng ngày, ăn thức ăn cao cấp. Có tính khí hơi cao ngạo.', 800000, 0, NOW()),
(5, 'YC005', 1, 5, '2026-01-10', '2026-02-01', 'Quận Tân Bình, TP.HCM', 'Chó Corgi rất năng động, cần người có kinh nghiệm. Không được để ở nơi cao.', 700000, 0, NOW());

-- Cập nhật trạng thái thú cưng (nếu cần đánh dấu đang được gửi nuôi hộ)
-- UPDATE thu_cung SET trang_thai = 2 WHERE id IN (1, 2, 3, 4, 5);
