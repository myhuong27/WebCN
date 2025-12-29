-- Tạo bảng lịch khám bệnh
CREATE TABLE IF NOT EXISTS lich_kham (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ma_lich_kham VARCHAR(50) UNIQUE NOT NULL,
    nguoi_dung_id INT NOT NULL,
    thu_cung_id INT NOT NULL,
    ngay_kham DATE NOT NULL,
    gio_kham TIME NOT NULL,
    ly_do TEXT,
    trieu_chung TEXT,
    ghi_chu TEXT,
    trang_thai ENUM('cho_xac_nhan', 'da_xac_nhan', 'dang_kham', 'hoan_thanh', 'da_huy') DEFAULT 'cho_xac_nhan',
    chan_doan TEXT,
    don_thuoc TEXT,
    chi_phi DECIMAL(10,2),
    ngay_tao DATETIME DEFAULT CURRENT_TIMESTAMP,
    ngay_cap_nhat DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung(id) ON DELETE CASCADE,
    FOREIGN KEY (thu_cung_id) REFERENCES thu_cung(id) ON DELETE CASCADE
);
