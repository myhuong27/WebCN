# 🐾 HƯỚNG DẪN CÀI ĐẶT & SỬ DỤNG - HỆ THỐNG QUẢN LÝ NUÔI HỘ & CHĂM SÓC THÚ CƯNG

## 📋 MỤC LỤC
1. [Yêu cầu hệ thống](#yêu-cầu-hệ-thống)
2. [Cài đặt bước đầu](#cài-đặt-bước-đầu)
3. [Import Database](#import-database)
4. [Cấu hình hệ thống](#cấu-hình-hệ-thống)
5. [Chạy website](#chạy-website)
6. [Tính năng đã hoàn thành](#tính-năng-đã-hoàn-thành)
7. [Hướng dẫn sử dụng](#hướng-dẫn-sử-dụng)
8. [Cấu hình nhắc lịch tự động](#cấu-hình-nhắc-lịch-tự-động)
9. [Troubleshooting](#troubleshooting)

---

## 🔧 YÊU CẦU HỆ THỐNG

- **XAMPP** (hoặc tương đương):
  - PHP 7.4 trở lên
  - MySQL/MariaDB 5.7 trở lên
  - Apache Server
- **Trình duyệt**: Chrome, Firefox, Edge (phiên bản mới nhất)
- **RAM**: Tối thiểu 2GB
- **Dung lượng ổ đĩa**: 500MB

---

## 🚀 CÀI ĐẶT BƯỚC ĐẦU

### Bước 1: Cài đặt XAMPP
1. Tải XAMPP từ: https://www.apachefriends.org/
2. Cài đặt vào thư mục mặc định: `C:\xampp`
3. Chạy XAMPP Control Panel
4. Start **Apache** và **MySQL**

### Bước 2: Kiểm tra thư mục dự án
Đảm bảo thư mục dự án nằm tại:
```
C:\xampp\htdocs\csn-da22ttd-chauthimyhuong-webbanhang\src\
```

---

## 💾 IMPORT DATABASE

### Bước 1: Mở phpMyAdmin
1. Vào trình duyệt, truy cập: http://localhost/phpmyadmin
2. Đăng nhập (mặc định không có password)

### Bước 2: Tạo Database
1. Click tab **"Databases"**
2. Nhập tên database: `quan_ly_thu_cung`
3. Collation: `utf8mb4_unicode_ci`
4. Click **"Create"**

### Bước 3: Import dữ liệu
1. Click vào database `quan_ly_thu_cung` vừa tạo
2. Click tab **"Import"**
3. Click **"Choose File"**
4. Chọn file: `src/database_full.sql`
5. Click **"Go"** ở cuối trang
6. Đợi import hoàn tất (có thể mất vài phút)

### Bước 4: Tạo tài khoản Admin mặc định
Sau khi import xong, chạy SQL sau để tạo tài khoản admin:

```sql
INSERT INTO nguoi_dung (ho_ten, email, so_dien_thoai, mat_khau, vai_tro, trang_thai) 
VALUES ('Admin', 'admin@petcare.com', '0123456789', MD5('admin123'), 2, 1);
```

**Thông tin đăng nhập Admin:**
- Email: `admin@petcare.com`
- Password: `admin123`

---

## ⚙️ CẤU HÌNH HỆ THỐNG

### Kiểm tra file config.php
Mở file `src/config.php` và kiểm tra:

```php
<?php
$host = 'localhost';
$dbname = 'quan_ly_thu_cung';  // ✓ Đúng tên database
$username = 'root';             // ✓ Username mặc định
$password = '';                 // ✓ Password trống (mặc định XAMPP)
?>
```

### Kiểm tra file connect.php
File này đã được cấu hình sẵn, không cần chỉnh sửa.

---

## 🌐 CHẠY WEBSITE

### Truy cập website
1. Đảm bảo Apache và MySQL đang chạy trong XAMPP
2. Mở trình duyệt
3. Truy cập: **http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/**

### Các trang chính:

#### 🏠 Trang người dùng:
- **Trang chủ**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/index.php
- **Thú cưng**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/thucung.php
- **Dịch vụ**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/dichvu.php
- **Đặt lịch**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/datlich.php
- **Gửi thú cưng nuôi hộ**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/yeucau_nuoiho.php
- **Nhận thú cưng nuôi hộ**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/nhan_nuoiho.php
- **Blog**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/blog.php
- **Video**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/video.php

#### 👨‍💼 Trang Admin:
- **Dashboard**: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/admin/dashboard.php
  - Đăng nhập với: `admin@petcare.com` / `admin123`

---

## ✨ TÍNH NĂNG ĐÃ HOÀN THÀNH

### ✅ 1. Admin Dashboard
**File**: `admin/dashboard.php`

**Tính năng**:
- 📊 Thống kê tổng quan (thú cưng, người dùng, lịch hẹn, doanh thu)
- 📈 Biểu đồ phân tích (Chart.js):
  - Thú cưng theo loại (Doughnut Chart)
  - Dịch vụ phổ biến (Bar Chart)
- 📋 Bảng lịch hẹn gần đây
- 🎨 Giao diện đẹp với sidebar navigation

**Cách sử dụng**:
1. Đăng nhập với tài khoản admin
2. Xem tất cả thống kê trên dashboard
3. Click vào menu sidebar để quản lý các module khác

---

### ✅ 2. Hồ Sơ Thú Cưng Chi Tiết
**File**: `chitiet_thucung.php`

**Tính năng**:
- 📸 Hiển thị ảnh và thông tin cơ bản thú cưng
- 💉 Tab lịch tiêm phòng đầy đủ
- 🍖 Tab chế độ ăn uống
- ❤️ Tab nhật ký sức khỏe (timeline)
- 📅 Tab lịch hẹn
- ⭐ Tab đánh giá và nhận xét

**Cách sử dụng**:
1. Vào trang Thú cưng
2. Click vào thú cưng bất kỳ
3. Xem tất cả thông tin chi tiết qua các tab

---

### ✅ 3. Hệ Thống Nuôi Hộ

#### 📤 Gửi yêu cầu nuôi hộ
**File**: `yeucau_nuoiho.php`

**Tính năng**:
- 📝 Form gửi yêu cầu nuôi hộ
- 📋 Danh sách yêu cầu đã gửi
- 💰 Hiển thị giá và trạng thái
- ℹ️ Thông tin người nhận (sau khi ghép cặp)

**Cách sử dụng**:
1. Đăng nhập
2. Vào trang "Yêu cầu nuôi hộ"
3. Chọn thú cưng, ngày bắt đầu/kết thúc, giá tiền
4. Gửi yêu cầu

#### 📥 Nhận nuôi hộ
**File**: `nhan_nuoiho.php`

**Tính năng**:
- 📋 Danh sách yêu cầu chờ nhận
- 🤝 Nhận yêu cầu nuôi hộ
- 📞 Hiển thị thông tin liên hệ chủ thú cưng
- 💵 Tính toán thu nhập

**Cách sử dụng**:
1. Đăng nhập
2. Vào trang "Nhận nuôi hộ"
3. Xem danh sách yêu cầu
4. Click "Nhận nuôi hộ" cho yêu cầu phù hợp

---

### ✅ 4. Nhắc Lịch Tự Động
**File**: `cron/reminder_cron.php`

**Tính năng tự động**:
- 💉 Nhắc tiêm phòng (7 ngày trước)
- 📅 Nhắc lịch hẹn (hôm nay)
- 🏠 Nhắc lịch nuôi hộ (1 ngày trước)
- 🔔 Nhắc lịch tùy chỉnh
- 📧 Gửi email và thông báo trong hệ thống

**Cấu hình** (xem phần [Cấu hình nhắc lịch tự động](#cấu-hình-nhắc-lịch-tự-động))

---

### ✅ 5. Blog & Video

#### 📰 Blog
**Files**: `blog.php`, `blog_detail.php`

**Tính năng**:
- 📚 Danh sách bài viết theo danh mục
- 🎨 Giao diện đẹp với grid layout
- ⭐ Bài viết nổi bật
- 👁️ Đếm lượt xem
- 📝 Trang chi tiết bài viết
- 🔗 Bài viết liên quan

**Danh mục blog**:
- Chăm sóc
- Dinh dưỡng
- Huấn luyện
- Sức khỏe
- Hành vi

#### 🎥 Video hướng dẫn
**Files**: `video.php`, `video_detail.php`

**Tính năng**:
- 🎬 Danh sách video theo danh mục
- 🎨 Giao diện giống YouTube
- ▶️ Embedded YouTube player
- 🔥 Video nổi bật
- ⏱️ Hiển thị thời lượng
- 📊 Đếm lượt xem
- 🔗 Video liên quan

**Danh mục video**:
- Chăm sóc cơ bản
- Tắm rửa & Vệ sinh
- Huấn luyện
- Nấu ăn cho thú cưng
- Chăm sóc sức khỏe

---

## 📖 HƯỚNG DẪN SỬ DỤNG

### Đối với người dùng thường:

1. **Đăng ký tài khoản**:
   - Click "Đăng ký" trên trang chủ
   - Điền đầy đủ thông tin
   - Vai trò mặc định: Người dùng (0)

2. **Xem thú cưng**:
   - Vào menu "Thú cưng"
   - Lọc theo loại, giới tính, trạng thái
   - Click vào thú cưng để xem chi tiết

3. **Đặt lịch hẹn**:
   - Vào menu "Đặt lịch"
   - Chọn thú cưng, dịch vụ, ngày giờ
   - Gửi yêu cầu

4. **Gửi thú cưng nuôi hộ**:
   - Đăng nhập
   - Vào "Yêu cầu nuôi hộ"
   - Điền form và gửi

5. **Đọc blog & xem video**:
   - Vào menu "Blog" hoặc "Video"
   - Chọn danh mục
   - Click vào nội dung muốn xem

### Đối với Admin:

1. **Đăng nhập Admin**:
   - Email: `admin@petcare.com`
   - Password: `admin123`

2. **Xem Dashboard**:
   - Thống kê tổng quan
   - Biểu đồ phân tích
   - Lịch hẹn gần đây

3. **Quản lý dữ liệu**:
   - Người dùng
   - Thú cưng
   - Dịch vụ
   - Lịch hẹn
   - Yêu cầu nuôi hộ
   - Bài viết
   - Video
   - Đánh giá

---

## ⏰ CẤU HÌNH NHẮC LỊCH TỰ ĐỘNG

### Trên Windows (Task Scheduler):

1. **Mở Task Scheduler**:
   - Nhấn `Win + R`
   - Gõ `taskschd.msc`
   - Enter

2. **Tạo Basic Task**:
   - Click "Create Basic Task"
   - Name: "Pet Care Reminder"
   - Description: "Daily reminder for pet care"

3. **Trigger**:
   - Daily
   - Start: 08:00:00 (8 giờ sáng)
   - Recur every: 1 days

4. **Action**:
   - Start a program
   - Program/script: `C:\xampp\php\php.exe`
   - Arguments: `C:\xampp\htdocs\csn-da22ttd-chauthimyhuong-webbanhang\src\cron\reminder_cron.php`

5. **Finish**:
   - Click "Finish"

### Trên Linux/Mac (Cron Job):

```bash
# Mở crontab
crontab -e

# Thêm dòng sau (chạy mỗi ngày lúc 8h sáng)
0 8 * * * /usr/bin/php /path/to/src/cron/reminder_cron.php >> /path/to/src/cron/cron.log 2>&1
```

### Test thủ công:

```bash
# Windows
cd C:\xampp\htdocs\csn-da22ttd-chauthimyhuong-webbanhang\src\cron
C:\xampp\php\php.exe reminder_cron.php

# Linux/Mac
cd /path/to/src/cron
php reminder_cron.php
```

### Kiểm tra log:
- Email log: `src/cron/email_log.txt`
- Error log: `src/cron/error_log.txt`

---

## 🐛 TROUBLESHOOTING

### Lỗi: "Access denied for user 'root'@'localhost'"
**Giải pháp**:
1. Kiểm tra MySQL đang chạy trong XAMPP
2. Kiểm tra file `config.php` có đúng username/password không
3. Reset password MySQL trong phpMyAdmin

### Lỗi: "Table doesn't exist"
**Giải pháp**:
1. Import lại file `database_full.sql`
2. Kiểm tra tên database trong `config.php` là `quan_ly_thu_cung`

### Lỗi: "Cannot modify header information"
**Giải pháp**:
1. Không có ký tự gì trước `<?php`
2. Không có BOM trong file PHP
3. Không echo gì trước header()

### Trang chủ không hiển thị slideshow
**Giải pháp**:
1. Kiểm tra kết nối internet (ảnh từ Unsplash)
2. Xóa cache trình duyệt (Ctrl + F5)

### Admin Dashboard không load được biểu đồ
**Giải pháp**:
1. Kiểm tra kết nối internet (Chart.js từ CDN)
2. Import dữ liệu mẫu vào database
3. Kiểm tra console browser (F12) xem lỗi JavaScript

### Nhắc lịch không chạy
**Giải pháp**:
1. Kiểm tra Task Scheduler/Cron Job đã setup đúng chưa
2. Test thủ công bằng lệnh PHP CLI
3. Kiểm tra file log: `cron/email_log.txt` và `cron/error_log.txt`

---

## 📊 CẤU TRÚC DATABASE

### Các bảng chính:

1. **nguoi_dung** - Quản lý người dùng (User, Admin)
2. **thu_cung** - Thông tin thú cưng
3. **dich_vu** - Các dịch vụ chăm sóc
4. **lich_hen** - Lịch hẹn đặt dịch vụ
5. **lich_tiem_phong** - Lịch tiêm phòng
6. **che_do_an_uong** - Chế độ ăn uống
7. **nhat_ky_suc_khoe** - Nhật ký sức khỏe
8. **yeu_cau_nuoi_ho** - Yêu cầu nuôi hộ
9. **nhac_lich** - Nhắc lịch tùy chỉnh
10. **bai_viet** - Bài viết blog
11. **video_huong_dan** - Video hướng dẫn
12. **danh_gia** - Đánh giá dịch vụ
13. **hoa_don** - Hóa đơn thanh toán
14. **thong_bao** - Thông báo hệ thống
15. **tin_nhan** - Tin nhắn giữa người dùng
16. **nhat_ky_hoat_dong** - Log hoạt động

---

## 🎯 TÍNH NĂNG NÂNG CAO (Coming Soon)

- [ ] AI Chatbot tư vấn
- [ ] Tích hợp thanh toán online (VNPay, MoMo)
- [ ] Gửi email thực (PHPMailer)
- [ ] Upload ảnh thú cưng
- [ ] Export báo cáo PDF
- [ ] Dashboard nâng cao với nhiều biểu đồ hơn
- [ ] Mobile App (React Native)

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề:
1. Kiểm tra phần Troubleshooting ở trên
2. Xem log files trong thư mục `cron/`
3. Kiểm tra console trình duyệt (F12)

---

## 📝 GHI CHÚ

- **Database name**: `quan_ly_thu_cung`
- **Admin email**: admin@petcare.com
- **Admin password**: admin123
- **Cron job**: Chạy mỗi ngày lúc 8h sáng

---

🐾 **Chúc bạn sử dụng hệ thống thành công!** 🐾
