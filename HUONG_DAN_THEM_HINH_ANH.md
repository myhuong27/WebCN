# 🎨 HƯỚNG DẪN THÊM HÌNH ẢNH, BÀI VIẾT VÀ VIDEO

## 📝 Bước 1: Import Dữ Liệu Mẫu

1. Mở **phpMyAdmin**: http://localhost/phpmyadmin
2. Chọn database `quan_ly_thu_cung`
3. Click tab **SQL**
4. Copy toàn bộ nội dung file `sample_data.sql`
5. Paste vào và click **Go**

## ✅ Kết Quả Sau Khi Import:

### 📰 **3 Bài Viết Mẫu:**
- "10 Điều Cần Biết Khi Nuôi Chó Lần Đầu"
- "Hướng Dẫn Chăm Sóc Mèo Con Từ 0-6 Tháng Tuổi"
- "Chế Độ Dinh Dưỡng Khoa Học Cho Thú Cưng"

### 🎥 **3 Video Hướng Dẫn:**
- "Cách Tắm Cho Chó Đúng Cách Tại Nhà" (8:45 phút, 1520 lượt xem)
- "Huấn Luyện Mèo Đi Vệ Sinh Đúng Chỗ" (6:30 phút, 2340 lượt xem)
- "Chăm Sóc Răng Miệng Cho Thú Cưng" (7:15 phút, 980 lượt xem)

## 🖼️ Bước 2: Thêm Hình Ảnh Cho Thú Cưng

### Cách 1: Dùng URL Hình Ảnh (Khuyến nghị)

```sql
-- Cập nhật hình ảnh cho thú cưng bằng URL từ Unsplash
UPDATE thu_cung SET hinh_anh = 'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=400' WHERE id = 1;
UPDATE thu_cung SET hinh_anh = 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400' WHERE id = 2;
UPDATE thu_cung SET hinh_anh = 'https://images.unsplash.com/photo-1568572933382-74d440642117?w=400' WHERE id = 3;
```

### Cách 2: Tạo Thú Cưng Mẫu Với Hình

```sql
INSERT INTO `thu_cung` (`ten_thu_cung`, `loai_thu_cung`, `giong`, `tuoi`, `gioi_tinh`, `hinh_anh`, `mo_ta`, `trang_thai`) VALUES
('Lucky', 'Chó', 'Golden Retriever', 2, 'Đực', 
 'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=400', 
 'Chú chó vàng hiền lành, thân thiện với trẻ em', 1),

('Mimi', 'Mèo', 'Mèo Ba Tư', 1, 'Cái', 
 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400', 
 'Mèo Ba Tư lông dài, rất dễ thương', 1),

('Buddy', 'Chó', 'Husky', 3, 'Đực', 
 'https://images.unsplash.com/photo-1568572933382-74d440642117?w=400', 
 'Husky Siberia năng động, thích chạy nhảy', 1),

('Luna', 'Mèo', 'Mèo Munchkin', 1, 'Cái', 
 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400', 
 'Mèo chân ngắn đáng yêu, rất thích chơi đùa', 1);
```

## 🎬 Bước 3: Xem Kết Quả

### Trang Chủ: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/index.php
- ✅ Section **Bài Viết Mới Nhất** với 3 bài viết
- ✅ Section **Video Hướng Dẫn Chăm Sóc** với 3 video
- ✅ Mỗi card có hình ảnh đẹp, hiệu ứng hover
- ✅ Nút "Xem tất cả bài viết" và "Xem tất cả video"

### Trang Thú Cưng: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/thucung.php
- ✅ Hiển thị hình ảnh thú cưng từ database
- ✅ Nếu không có hình → Tự động dùng placeholder đẹp

### Trang Blog: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/blog.php
- ✅ Danh sách đầy đủ bài viết

### Trang Video: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/video.php
- ✅ Danh sách đầy đủ video

## 💡 Nguồn Hình Ảnh Miễn Phí:

1. **Unsplash** - https://unsplash.com/s/photos/pets
   - Chất lượng cao, không cần attribution
   - Ví dụ: `https://images.unsplash.com/photo-[ID]?w=400`

2. **Pexels** - https://www.pexels.com/search/pets/
   - Miễn phí thương mại
   - Ví dụ: `https://images.pexels.com/photos/[ID]/pexels-photo-[ID].jpeg?w=400`

3. **Pixabay** - https://pixabay.com/images/search/pets/
   - Hơn 2 triệu hình ảnh miễn phí

## 🎨 Tính Năng Mới Đã Thêm:

### ✅ Trang Chủ:
- 📰 Section bài viết với grid layout responsive
- 🎥 Section video với thumbnail và play icon
- 🖼️ Hover effects mượt mà
- 📅 Hiển thị ngày đăng, lượt xem
- 🔗 Link đến chi tiết bài viết/video

### ✅ Trang Thú Cưng:
- 🖼️ Hỗ trợ hiển thị hình từ URL
- 📦 Tự động fallback sang placeholder nếu không có hình
- 🎨 Image placeholder đẹp từ Unsplash

## 🚀 Cách Thêm Bài Viết/Video Mới:

### Thêm Bài Viết:
```sql
INSERT INTO `bai_viet` (`tieu_de`, `noi_dung`, `hinh_anh`, `loai_thu_cung`, `trang_thai`, `ngay_dang`) 
VALUES ('Tiêu đề bài viết', 'Nội dung...', 'URL_hình_ảnh', 'Chó', 1, NOW());
```

### Thêm Video:
```sql
INSERT INTO `video_huong_dan` (`tieu_de`, `mo_ta`, `url_video`, `thumbnail`, `loai_thu_cung`, `thoi_luong`, `trang_thai`, `ngay_dang`) 
VALUES ('Tiêu đề video', 'Mô tả...', 'youtube_embed_url', 'URL_thumbnail', 'Mèo', '5:30', 1, NOW());
```

## 📝 Lưu Ý:
- Hình ảnh nên dùng URL thay vì upload file để dễ quản lý
- Kích thước khuyến nghị: 400x300px cho thú cưng, 600x400px cho bài viết/video
- Format: JPG hoặc PNG
- Nội dung bài viết nên từ 200-500 từ
- Video nên embed từ YouTube để tiết kiệm băng thông
