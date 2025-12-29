# Hệ Thống Quản Lý Nuôi Hộ và Chăm Sóc Thú Cưng

## Mô tả dự án
Website quản lý dịch vụ nuôi hộ và chăm sóc thú cưng gia đình. Hệ thống cho phép khách hàng xem thú cưng đang được chăm sóc, đặt lịch các dịch vụ chăm sóc, và quản lý toàn bộ quy trình.

## Tính năng chính

### Cho khách hàng:
- Xem danh sách thú cưng đang được chăm sóc
- Xem chi tiết thông tin từng thú cưng
- Xem danh sách dịch vụ chăm sóc
- Đặt lịch hẹn cho các dịch vụ
- Đăng ký/Đăng nhập tài khoản
- Xem lịch sử chăm sóc

### Cho quản trị viên:
- Quản lý thú cưng (thêm, sửa, xóa)
- Quản lý dịch vụ
- Quản lý lịch hẹn
- Quản lý khách hàng
- Xem báo cáo doanh thu
- Quản lý hóa đơn

## Cài đặt

### 1. Yêu cầu hệ thống
- XAMPP (Apache + MySQL + PHP)
- Trình duyệt web hiện đại
- PHP 7.4 trở lên
- MySQL 5.7 trở lên

### 2. Các bước cài đặt

#### Bước 1: Khởi động XAMPP
- Mở XAMPP Control Panel
- Start **Apache**
- Start **MySQL**

#### Bước 2: Tạo và Import Database
1. Truy cập: `http://localhost/phpmyadmin`
2. Tạo database mới tên: **`quan_ly_thu_cung`**
3. Import file SQL:
   - Chọn database `quan_ly_thu_cung`
   - Click tab **Import**
   - Chọn file `quan_ly_thu_cung.sql` từ thư mục `src`
   - Click **Go**

#### Bước 3: Cấu hình kết nối
File `config.php` và `connect.php` đã được cấu hình sẵn:
```php
Host: localhost
Username: root
Password: (để trống)
Database: quan_ly_thu_cung
```

#### Bước 4: Truy cập website
Mở trình duyệt và truy cập:
```
http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/index.html
```

## Cấu trúc Database

### Các bảng chính:

1. **nguoi_dung** - Quản lý người dùng (khách hàng, nhân viên, admin)
2. **thu_cung** - Thông tin thú cưng đang được chăm sóc
3. **dich_vu** - Danh sách dịch vụ chăm sóc
4. **lich_hen** - Lịch hẹn đặt dịch vụ
5. **hoa_don** - Hóa đơn thanh toán
6. **chi_tiet_hoa_don** - Chi tiết các dịch vụ trong hóa đơn
7. **lich_su_cham_soc** - Lịch sử chăm sóc thú cưng

## Các trang chính

### Trang khách hàng:
- `index.html` - Trang chủ
- `thucung.php` - Danh sách thú cưng
- `dichvu.php` - Danh sách dịch vụ
- `datlich.php` - Đặt lịch hẹn
- `login_page.php` - Đăng nhập
- `register.php` - Đăng ký

### Trang quản trị:
- `QTVindex.php` - Trang quản trị chính
- Quản lý thú cưng
- Quản lý dịch vụ
- Quản lý lịch hẹn
- Báo cáo thống kê

## Dữ liệu mẫu

Database đã có sẵn dữ liệu mẫu:

### Thú cưng:
- Milu - Chó Golden Retriever
- Bi - Mèo Ba Tư
- Cún - Chó Poodle
- Kitty - Mèo Munchkin

### Dịch vụ:
- Nuôi hộ theo ngày: 100,000đ
- Tắm và vệ sinh: 150,000đ
- Cắt tỉa lông: 200,000đ
- Khám sức khỏe: 250,000đ
- Huấn luyện: 500,000đ
- Chăm sóc đặc biệt: 300,000đ

## Tính năng nổi bật

✅ Giao diện thân thiện, dễ sử dụng
✅ Responsive - tương thích mọi thiết bị
✅ Hệ thống đặt lịch trực tuyến
✅ Quản lý thông tin thú cưng chi tiết
✅ Đa dạng dịch vụ chăm sóc
✅ Hệ thống báo cáo thống kê

## Liên hệ hỗ trợ

- Email: info@petcare.vn
- Hotline: 0123 456 789
- Địa chỉ: 123 Đường ABC, TP.HCM

## Ghi chú

- Đảm bảo XAMPP đang chạy khi sử dụng website
- Database phải được import thành công
- Nếu gặp lỗi, kiểm tra lại cấu hình trong file `config.php` và `connect.php`

---
**Developed with ❤️ by Pet Care Team**
