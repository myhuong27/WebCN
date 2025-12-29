# HƯỚNG DẪN CÀI ĐẶT HỆ THỐNG CHAT SUPPORT

## 📋 Tổng quan
Hệ thống chat realtime cho phép khách hàng chat trực tiếp với Admin để được hỗ trợ.

## 🗂️ Cấu trúc files đã tạo:

```
src/
├── database/
│   └── create_chat_support.sql         # Script tạo bảng database
├── api/
│   └── chat_api.php                    # API xử lý tin nhắn
├── includes/
│   └── chat_widget.php                 # Widget chat floating cho user
├── admin/
│   └── quan_ly_chat.php                # Giao diện quản lý chat admin
└── user/
    ├── user_dashboard.php              # Đã thêm chat widget
    ├── profile.php                     # Đã thêm chat widget
    ├── quan_ly_thucung_user.php       # Đã thêm chat widget
    └── lich_dat_dich_vu.php           # Đã thêm chat widget
```

## 📦 BƯỚC 1: Import Database

1. Mở phpMyAdmin: http://localhost/phpmyadmin
2. Chọn database `quan_ly_thu_cung`
3. Vào tab "SQL"
4. Copy toàn bộ nội dung file `src/database/create_chat_support.sql`
5. Paste và click "Go"

**Hoặc chạy lệnh:**
```bash
mysql -u root -p quan_ly_thu_cung < src/database/create_chat_support.sql
```

## ✅ BƯỚC 2: Kiểm tra cấu trúc database

Sau khi import, kiểm tra 2 bảng mới:
- `chat_conversations` - Lưu các cuộc hội thoại
- `chat_messages` - Lưu tin nhắn

## 🚀 BƯỚC 3: Test hệ thống

### Phía Khách hàng:
1. Đăng nhập với tài khoản khách hàng (vai_tro = 0)
2. Vào trang User Dashboard: http://localhost/WCN/src/user/user_dashboard.php
3. Thấy nút chat màu tím góc dưới bên phải
4. Click vào nút chat để mở popup
5. Gửi tin nhắn thử nghiệm

### Phía Admin:
1. Đăng nhập với tài khoản admin (vai_tro = 2)
2. Vào trang Quản lý Chat: http://localhost/WCN/src/admin/quan_ly_chat.php
3. Thấy danh sách hội thoại bên trái
4. Click vào cuộc hội thoại để xem tin nhắn
5. Trả lời tin nhắn của khách hàng

## 🎯 Tính năng

### Khách hàng:
✅ Chat popup floating ở mọi trang user
✅ Gửi tin nhắn realtime
✅ Nhận thông báo tin nhắn mới (badge đỏ)
✅ Xem lịch sử chat
✅ Auto-refresh tin nhắn mỗi 3 giây

### Admin:
✅ Xem tất cả cuộc hội thoại
✅ Hiển thị số tin nhắn chưa đọc
✅ Trả lời tin nhắn khách hàng
✅ Xem thông tin chi tiết khách hàng
✅ Auto-refresh danh sách mỗi 5 giây

## 🔧 Tùy chỉnh

### Thay đổi thời gian polling:
**File:** `src/includes/chat_widget.php`
```javascript
// Dòng 194 - Polling tin nhắn (mặc định 3 giây)
chatInterval = setInterval(loadMessages, 3000);

// Dòng 282 - Check tin nhắn chưa đọc (mặc định 10 giây)
setInterval(checkUnreadMessages, 10000);
```

**File:** `src/admin/quan_ly_chat.php`
```javascript
// Dòng 338 - Auto refresh admin (mặc định 5 giây)
setInterval(() => {
    location.reload();
}, 5000);
```

### Thay đổi màu sắc:
Tìm và sửa trong CSS:
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

## 📱 Responsive
- Desktop: Chat popup 380px
- Mobile: Fullscreen (calc(100vw - 40px))

## 🔐 Bảo mật
✅ Session check trước mỗi API call
✅ Validate quyền sở hữu conversation
✅ Escape HTML để tránh XSS
✅ Prepared statements chống SQL injection

## 🐛 Troubleshooting

### Lỗi: "Chưa đăng nhập"
- Đảm bảo đã đăng nhập trước khi sử dụng chat
- Kiểm tra `$_SESSION['user_id']` tồn tại

### Lỗi: Database connection
- Kiểm tra file `config/connect.php`
- Đảm bảo database `quan_ly_thu_cung` tồn tại
- Kiểm tra MySQL đang chạy

### Chat không hiển thị:
- Clear cache trình duyệt (Ctrl + F5)
- Kiểm tra Console browser (F12) xem có lỗi JS không
- Đảm bảo file `includes/chat_widget.php` được include

### Tin nhắn không gửi được:
- Kiểm tra đường dẫn API: `api/chat_api.php`
- Kiểm tra Network tab trong DevTools
- Xem lỗi trong file log của Apache/PHP

## 📊 Truy vấn SQL hữu ích

```sql
-- Xem tất cả conversations
SELECT * FROM chat_conversations ORDER BY last_message_at DESC;

-- Xem tin nhắn của 1 conversation
SELECT * FROM chat_messages WHERE conversation_id = 1 ORDER BY created_at;

-- Đếm tin nhắn chưa đọc
SELECT COUNT(*) FROM chat_messages WHERE is_admin = 0 AND is_read = 0;

-- Xóa tất cả chat (reset)
TRUNCATE TABLE chat_messages;
TRUNCATE TABLE chat_conversations;
```

## 🎨 Nâng cấp trong tương lai

- [ ] Upload hình ảnh trong chat
- [ ] Gửi file đính kèm
- [ ] Typing indicator (đang gõ...)
- [ ] Push notification
- [ ] WebSocket cho realtime thực sự
- [ ] Bot tự động trả lời
- [ ] Export lịch sử chat

## 📞 Support
Nếu có vấn đề, kiểm tra:
1. Console log (F12)
2. Network requests
3. PHP error log
4. MySQL error log

---
**Hoàn tất!** Hệ thống chat support đã sẵn sàng sử dụng! 🎉
