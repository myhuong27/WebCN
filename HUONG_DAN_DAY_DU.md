# HỆ THỐNG QUẢN LÝ NUÔI HỘ VÀ CHĂM SÓC THÚ CƯNG - BẢN ĐẦY ĐỦ

## 🎯 TỔNG QUAN DỰ ÁN

Hệ thống website quản lý nuôi hộ và chăm sóc thú cưng toàn diện với đầy đủ tính năng:
- Quản lý thông tin thú cưng chi tiết
- Kết nối người gửi và nhận nuôi hộ
- Lịch chăm sóc và nhắc lịch tự động
- Tư vấn AI Chatbot
- Blog và video hướng dẫn
- Thanh toán trực tuyến
- Đánh giá và phản hồi
- Cộng đồng chia sẻ

## 📊 CẤU TRÚC DATABASE

### Các bảng chính đã tạo:

1. **nguoi_dung** - Quản lý tài khoản (Admin, Nhân viên, Khách hàng)
2. **thu_cung** - Thông tin thú cưng đầy đủ
3. **lich_tiem_phong** - Lịch tiêm chủng
4. **che_do_an_uong** - Chế độ dinh dưỡng
5. **nhat_ky_suc_khoe** - Nhật ký sức khỏe
6. **yeu_cau_nuoi_ho** - Quản lý yêu cầu gửi/nhận nuôi
7. **lich_hen** - Đặt lịch dịch vụ
8. **nhac_lich** - Hệ thống nhắc lịch tự động
9. **dich_vu** - Danh sách dịch vụ
10. **bai_viet** - Blog bài viết
11. **video_huong_dan** - Video hướng dẫn
12. **hoa_don** & **chi_tiet_hoa_don** - Quản lý thanh toán
13. **danh_gia** - Đánh giá dịch vụ
14. **binh_luan** - Bình luận bài viết
15. **thong_bao** - Thông báo người dùng
16. **lich_su_cham_soc** - Lịch sử chăm sóc

## 🚀 CÀI ĐẶT

### Bước 1: Import Database
```
1. Truy cập http://localhost/phpmyadmin
2. Tạo database: quan_ly_thu_cung
3. Import file: database_full.sql
```

### Bước 2: Cấu hình
File config đã được cấu hình sẵn:
- Host: localhost
- Username: root  
- Password: (để trống)
- Database: quan_ly_thu_cung

### Bước 3: Truy cập
```
http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/index.php
```

## 📁 CẤU TRÚC FILE

### Trang người dùng:
- `index.php` - Trang chủ động
- `thucung.php` - Danh sách thú cưng
- `chitiet_thucung.php` - Hồ sơ thú cưng chi tiết
- `dichvu.php` - Danh sách dịch vụ
- `datlich.php` - Đặt lịch dịch vụ
- `yeucau_nuoiho.php` - Gửi yêu cầu nuôi hộ
- `nhan_nuoiho.php` - Đăng ký nhận nuôi
- `lichhen_cua_toi.php` - Quản lý lịch hẹn
- `thucung_cua_toi.php` - Quản lý thú cưng của tôi
- `blog.php` - Blog bài viết
- `video.php` - Video hướng dẫn
- `chatbot.php` - Tư vấn AI
- `thanhtoan.php` - Thanh toán
- `profile.php` - Hồ sơ cá nhân

### Trang Admin:
- `admin/dashboard.php` - Tổng quan thống kê
- `admin/quan_ly_nguoi_dung.php` - Quản lý user
- `admin/quan_ly_thucung.php` - Quản lý thú cưng
- `admin/quan_ly_dichvu.php` - Quản lý dịch vụ
- `admin/quan_ly_lichhen.php` - Quản lý lịch hẹn
- `admin/quan_ly_yeucau.php` - Duyệt yêu cầu nuôi hộ
- `admin/quan_ly_baiviet.php` - Quản lý bài viết
- `admin/quan_ly_danhgia.php` - Quản lý đánh giá
- `admin/thong_ke.php` - Báo cáo thống kê

## ✨ TÍNH NĂNG CHI TIẾT

### 🐾 1. Quản lý thông tin thú cưng
- ✅ Thông tin cơ bản: tên, tuổi, giống, giới tính, cân nặng
- ✅ Lịch tiêm phòng chi tiết
- ✅ Chế độ ăn uống cụ thể
- ✅ Nhật ký sức khỏe
- ✅ Hình ảnh và đặc điểm riêng
- ✅ Lịch sử chăm sóc

### 🤝 2. Kết nối nuôi hộ
- ✅ Đăng ký gửi thú cưng nuôi hộ
- ✅ Tìm kiếm người nhận nuôi
- ✅ Xem đánh giá người nhận
- ✅ Admin duyệt yêu cầu
- ✅ Theo dõi trạng thái nuôi hộ

### 📅 3. Lịch chăm sóc & Nhắc lịch
- ✅ Nhắc lịch tiêm chủng
- ✅ Nhắc lịch khám sức khỏe
- ✅ Nhắc lịch tắm rửa
- ✅ Nhắc lịch cho ăn
- ✅ Lặp lại (hàng ngày/tuần/tháng/năm)
- ✅ Thông báo tự động

### 🤖 4. Chatbot AI Tư vấn
- ✅ Tư vấn chăm sóc thú cưng
- ✅ Gợi ý dinh dưỡng
- ✅ Tư vấn sức khỏe
- ✅ Trả lời câu hỏi thường gặp

### 📚 5. Bài viết & Video
- ✅ Blog kiến thức nuôi thú cưng
- ✅ Phân loại theo loài (chó, mèo, chim...)
- ✅ Video hướng dẫn chi tiết
- ✅ Bình luận và tương tác
- ✅ Tìm kiếm bài viết

### 🍖 6. Gợi ý thức ăn
- ✅ Theo giống loài
- ✅ Theo cân nặng
- ✅ Theo độ tuổi
- ✅ Danh sách thức ăn cấm

### 📆 7. Đặt lịch dịch vụ
- ✅ Chọn dịch vụ
- ✅ Chọn ngày giờ
- ✅ Admin xác nhận
- ✅ Theo dõi trạng thái
- ✅ Lịch sử dịch vụ

### 💳 8. Thanh toán trực tuyến
- ✅ Tiền mặt
- ✅ Chuyển khoản
- ✅ Thẻ tín dụng
- ✅ Ví điện tử
- ✅ Hóa đơn chi tiết

### ⭐ 9. Đánh giá & Phản hồi
- ✅ Đánh giá dịch vụ (1-5 sao)
- ✅ Đánh giá người nuôi hộ
- ✅ Đánh giá nhân viên
- ✅ Upload hình ảnh
- ✅ Admin quản lý đánh giá

### 👥 10. Cộng đồng
- ✅ Chia sẻ kinh nghiệm
- ✅ Bình luận bài viết
- ✅ Tương tác người dùng
- ✅ Upload hình ảnh thú cưng

## 🔐 PHÂN QUYỀN

### Admin (vai_tro = 2):
- ✅ Quản lý toàn bộ hệ thống
- ✅ Xem báo cáo thống kê
- ✅ Duyệt/từ chối yêu cầu
- ✅ Khóa/mở tài khoản
- ✅ Quản lý nội dung

### Nhân viên (vai_tro = 1):
- ✅ Xem lịch hẹn được giao
- ✅ Cập nhật tiến trình
- ✅ Ghi nhận chăm sóc
- ✅ Xem thông tin thú cưng

### Khách hàng (vai_tro = 0):
- ✅ Đăng ký/đăng nhập
- ✅ Quản lý thú cưng của mình
- ✅ Đặt lịch dịch vụ
- ✅ Gửi/nhận nuôi hộ
- ✅ Đánh giá dịch vụ
- ✅ Tham gia cộng đồng

## 📊 DASHBOARD ADMIN

### Thống kê tổng quan:
- Tổng số thú cưng đang chăm sóc
- Tổng số người dùng
- Doanh thu trong tháng
- Số lịch hẹn chưa xử lý
- Số yêu cầu nuôi hộ chờ duyệt

### Biểu đồ:
- Doanh thu theo tháng
- Số lượng thú cưng theo loại
- Dịch vụ được đặt nhiều nhất
- Đánh giá trung bình

## 🎨 GIAO DIỆN

- ✅ Responsive design
- ✅ Giao diện hiện đại, thân thiện
- ✅ Slideshow ảnh động
- ✅ Icons Font Awesome
- ✅ Màu sắc dễ nhìn
- ✅ UX/UI tối ưu

## 🔔 THÔNG BÁO

### Tự động gửi thông báo khi:
- Lịch hẹn được xác nhận
- Yêu cầu nuôi hộ được duyệt
- Đến thời gian tiêm chủng
- Đến thời gian khám sức khỏe
- Thanh toán thành công
- Có đánh giá mới

## 🔍 TÌM KIẾM & LỌC

- Tìm kiếm thú cưng theo: loại, giống, tuổi, giới tính
- Lọc dịch vụ theo: giá, loại
- Tìm bài viết theo: danh mục, từ khóa
- Lọc lịch hẹn theo: ngày, trạng thái

## 📱 TÍNH NĂNG NÂNG CAO (Tương lai)

- [ ] App mobile
- [ ] Tích hợp bản đồ Google Maps
- [ ] Live chat
- [ ] Camera giám sát trực tuyến
- [ ] Gọi video với bác sĩ thú y
- [ ] Mạng xã hội cho thú cưng

## 🛠️ CÔNG NGHỆ SỬ DỤNG

- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Icons**: Font Awesome 6.0
- **Charts**: Chart.js (cho thống kê)
- **AI**: Có thể tích hợp OpenAI API

## 📞 HỖ TRỢ

- Email: info@petcare.vn
- Hotline: 0123 456 789
- Website: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/

## 📝 GHI CHÚ

- Nhớ backup database thường xuyên
- Kiểm tra log errors trong PHP
- Test tất cả chức năng trước khi deploy
- Cập nhật thường xuyên để bảo mật

---
**Phát triển bởi Pet Care Team - 2024**
