-- Tạo bảng đánh giá
CREATE TABLE IF NOT EXISTS danh_gia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nguoi_dung_id INT NOT NULL,
    loai ENUM('dich_vu', 'nuoi_ho') NOT NULL,
    dat_lich_id INT NULL,
    yeu_cau_nuoi_ho_id INT NULL,
    so_sao INT NOT NULL CHECK (so_sao BETWEEN 1 AND 5),
    noi_dung TEXT,
    phan_hoi_admin TEXT,
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    ngay_phan_hoi DATETIME NULL,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (dat_lich_id) REFERENCES dat_lich_dich_vu(id) ON DELETE CASCADE,
    FOREIGN KEY (yeu_cau_nuoi_ho_id) REFERENCES yeu_cau_nuoi_ho(id) ON DELETE CASCADE
);

-- Dữ liệu mẫu sẽ được thêm sau khi có user và đặt lịch thực tế
