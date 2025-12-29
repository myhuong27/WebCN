# 🎯 HƯỚNG DẪN NHANH - ĐĂNG NHẬP HỆ THỐNG

## ✅ Đã hoàn thành việc import database?

Nếu bạn đã import file `database_full.sql` thành công, hãy làm theo các bước sau:

---

## 📝 BƯỚC 1: TẠO TÀI KHOẢN ADMIN

Mở **phpMyAdmin** (http://localhost/phpmyadmin), chọn database `quan_ly_thu_cung`, vào tab **SQL** và chạy câu lệnh sau:

```sql
-- Tạo tài khoản Admin
INSERT INTO `nguoi_dung` 
(`ten_dang_nhap`, `mat_khau`, `ho_ten`, `email`, `so_dien_thoai`, `vai_tro`, `trang_thai`) 
VALUES 
('admin', MD5('admin123'), 'Administrator', 'admin@petcare.com', '0123456789', 2, 1);

-- Tạo tài khoản Nhân viên
INSERT INTO `nguoi_dung` 
(`ten_dang_nhap`, `mat_khau`, `ho_ten`, `email`, `so_dien_thoai`, `gioi_tinh`, `vai_tro`, `trang_thai`) 
VALUES 
('nhanvien1', MD5('123456'), 'Nguyễn Văn Nam', 'nhanvien1@petcare.com', '0987654321', 'Nam', 1, 1);

-- Tạo tài khoản Khách hàng
INSERT INTO `nguoi_dung` 
(`ten_dang_nhap`, `mat_khau`, `ho_ten`, `email`, `so_dien_thoai`, `gioi_tinh`, `vai_tro`, `trang_thai`) 
VALUES 
('khachhang1', MD5('123456'), 'Trần Thị Lan', 'khachhang1@gmail.com', '0912345678', 'Nữ', 0, 1),
('khachhang2', MD5('123456'), 'Lê Văn Hùng', 'khachhang2@gmail.com', '0923456789', 'Nam', 0, 1);
```

---

## 🔐 BƯỚC 2: ĐĂNG NHẬP

### Trang đăng nhập mới (Khuyến nghị):
👉 **http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/login_update.php**

### Hoặc sử dụng trang đăng nhập cũ:
http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/login_page.php

---

## 👥 TÀI KHOẢN DEMO

### 🔴 Admin (Quản trị viên)
- **Username**: `admin`
- **Email**: `admin@petcare.com`  
- **Password**: `admin123`
- **Quyền**: Toàn quyền quản lý hệ thống

### 🟡 Nhân viên
- **Username**: `nhanvien1`
- **Email**: `nhanvien1@petcare.com`
- **Password**: `123456`
- **Quyền**: Xử lý lịch hẹn, chăm sóc thú cưng

### 🟢 Khách hàng 1
- **Username**: `khachhang1`
- **Email**: `khachhang1@gmail.com`
- **Password**: `123456`
- **Quyền**: Đăng ký dịch vụ, gửi yêu cầu nuôi hộ

### 🟢 Khách hàng 2
- **Username**: `khachhang2`
- **Email**: `khachhang2@gmail.com`
- **Password**: `123456`
- **Quyền**: Nhận thú cưng nuôi hộ, xem blog/video

---

## 🌐 CÁC TRANG QUAN TRỌNG

### Trang người dùng:
- **Trang chủ**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/index.php
- **Thú cưng**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/thucung.php
- **Dịch vụ**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/dichvu.php
- **Gửi nuôi hộ**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/yeucau_nuoiho.php
- **Nhận nuôi hộ**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/nhan_nuoiho.php
- **Blog**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/blog.php
- **Video**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/video.php

### Trang Admin:
- **Dashboard**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/admin/dashboard.php

---

## 🔄 LƯU Ý QUAN TRỌNG

### 1. Đăng nhập bằng Username HOẶC Email
Hệ thống hỗ trợ đăng nhập bằng **cả 2 cách**:
- ✅ Nhập `admin` hoặc `admin@petcare.com`
- ✅ Nhập `khachhang1` hoặc `khachhang1@gmail.com`

### 2. Mật khẩu được mã hóa MD5
- Database sử dụng MD5 hash cho mật khẩu
- **KHÔNG** lưu mật khẩu dạng plain text

### 3. Vai trò (Role) trong hệ thống:
- `0` = Khách hàng (Customer)
- `1` = Nhân viên (Employee)
- `2` = Admin (Administrator)

### 4. Trạng thái tài khoản:
- `0` = Bị khóa (Locked)
- `1` = Hoạt động (Active)

---

## 📊 KIỂM TRA DỮ LIỆU

Sau khi tạo tài khoản, chạy câu lệnh SQL này để kiểm tra:

```sql
SELECT 
    id,
    ten_dang_nhap,
    ho_ten,
    email,
    so_dien_thoai,
    CASE vai_tro 
        WHEN 0 THEN 'Khách hàng'
        WHEN 1 THEN 'Nhân viên'
        WHEN 2 THEN 'Admin'
    END as vai_tro_text,
    CASE trang_thai
        WHEN 0 THEN 'Bị khóa'
        WHEN 1 THEN 'Hoạt động'
    END as trang_thai_text,
    ngay_tao
FROM nguoi_dung
ORDER BY vai_tro DESC, id ASC;
```

Kết quả mong đợi:
```
| id | ten_dang_nhap | ho_ten          | email                    | vai_tro_text | trang_thai_text |
|----|---------------|-----------------|--------------------------|--------------|-----------------|
| 1  | admin         | Administrator   | admin@petcare.com        | Admin        | Hoạt động       |
| 2  | nhanvien1     | Nguyễn Văn Nam  | nhanvien1@petcare.com    | Nhân viên    | Hoạt động       |
| 3  | khachhang1    | Trần Thị Lan    | khachhang1@gmail.com     | Khách hàng   | Hoạt động       |
| 4  | khachhang2    | Lê Văn Hùng     | khachhang2@gmail.com     | Khách hàng   | Hoạt động       |
```

---

## 🐛 XỬ LÝ LỖI

### Lỗi: "Duplicate entry for key 'ten_dang_nhap'"
**Nguyên nhân**: Username đã tồn tại

**Giải pháp**: Xóa user cũ trước khi tạo mới:
```sql
DELETE FROM nguoi_dung WHERE ten_dang_nhap = 'admin';
```

### Lỗi: "Tên đăng nhập/Email hoặc mật khẩu không đúng"
**Kiểm tra**:
1. Username/Email có đúng không?
2. Password có đúng không? (phân biệt HOA/thường)
3. Tài khoản có bị khóa không? (`trang_thai = 1`)

**Kiểm tra mật khẩu trong database**:
```sql
SELECT ten_dang_nhap, mat_khau, MD5('admin123') as password_hash
FROM nguoi_dung 
WHERE ten_dang_nhap = 'admin';
```

Nếu `mat_khau` KHÔNG khớp với `password_hash`, reset password:
```sql
UPDATE nguoi_dung 
SET mat_khau = MD5('admin123') 
WHERE ten_dang_nhap = 'admin';
```

### Lỗi: "Table 'nguoi_dung' doesn't exist"
**Giải pháp**: Import lại file `database_full.sql`

---

## 🎯 TEST FLOW HOÀN CHỈNH

### 1. Test Admin Dashboard:
1. Đăng nhập với `admin` / `admin123`
2. Truy cập: http://localhost/.../src/admin/dashboard.php
3. Kiểm tra:
   - ✅ Thống kê hiển thị
   - ✅ Biểu đồ Chart.js load
   - ✅ Bảng lịch hẹn hiển thị
   - ✅ Menu sidebar hoạt động

### 2. Test Hệ thống nuôi hộ:
1. Đăng nhập `khachhang1` / `123456`
2. Vào "Yêu cầu nuôi hộ"
3. Gửi yêu cầu (cần có thú cưng trước)
4. Đăng xuất
5. Đăng nhập `khachhang2` / `123456`
6. Vào "Nhận nuôi hộ"
7. Nhận yêu cầu từ khachhang1

### 3. Test Blog & Video:
1. Vào trang Blog
2. Vào trang Video
3. Kiểm tra filter danh mục
4. Click vào bài viết/video xem chi tiết

---

## 📱 LIÊN HỆ HỖ TRỢ

Nếu gặp vấn đề:
1. Kiểm tra Apache & MySQL đang chạy trong XAMPP
2. Xem console browser (F12) để check lỗi JavaScript
3. Xem log Apache: `C:\xampp\apache\logs\error.log`
4. Xem log MySQL: `C:\xampp\mysql\data\*.err`

---

## ✨ TÍNH NĂNG ĐÃ HOÀN THÀNH

- ✅ Admin Dashboard với thống kê & biểu đồ
- ✅ Hồ sơ thú cưng chi tiết (5 tabs)
- ✅ Hệ thống gửi/nhận nuôi hộ
- ✅ Nhắc lịch tự động (cron job)
- ✅ Blog & Video hướng dẫn
- ✅ Đăng nhập bằng username/email
- ✅ Responsive design
- ✅ Security: Session, MD5 hash, PDO prepared statements

---

🐾 **Chúc bạn sử dụng hệ thống thành công!** 🐾
