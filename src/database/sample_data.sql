-- Thêm dữ liệu mẫu cho bài viết
INSERT INTO `bai_viet` (`tieu_de`, `noi_dung`, `hinh_anh`, `loai_thu_cung`, `trang_thai`, `ngay_dang`) VALUES
('10 Điều Cần Biết Khi Nuôi Chó Lần Đầu', 
'Nuôi chó là một trách nhiệm lớn và đòi hỏi sự chuẩn bị kỹ lưỡng. Bạn cần tìm hiểu về giống chó phù hợp với không gian sống, thời gian chăm sóc, và khả năng tài chính của mình. Chó cần được tiêm phòng đầy đủ, ăn uống dinh dưỡng, tập luyện đều đặn và được khám sức khỏe định kỳ. Việc huấn luyện từ nhỏ giúp chó phát triển tốt về tính cách và hành vi. Đừng quên dành thời gian chơi đùa và tạo mối quan hệ gắn bó với người bạn bốn chân của mình.',
'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600',
'Chó',
1,
NOW()),

('Hướng Dẫn Chăm Sóc Mèo Con Từ 0-6 Tháng Tuổi',
'Mèo con trong giai đoạn 0-6 tháng tuổi cần được chăm sóc đặc biệt cẩn thận. Trong 4 tuần đầu, mèo con cần được cho bú sữa mẹ hoặc sữa công thức chuyên dụng. Từ tuần thứ 4, bạn có thể bắt đầu cho mèo con ăn thức ăn mềm. Tiêm phòng là rất quan trọng - mèo con cần tiêm vắc-xin ba mũi vào lúc 6, 8 và 12 tuần tuổi. Tắm rửa nên bắt đầu từ 8 tuần tuổi với dầu gội chuyên dụng cho mèo con. Đừng quên chuẩn bị khay vệ sinh và huấn luyện mèo sử dụng từ sớm.',
'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=600',
'Mèo',
1,
NOW()),

('Chế Độ Dinh Dưỡng Khoa Học Cho Thú Cưng',
'Dinh dưỡng đóng vai trò quyết định đến sức khỏe và tuổi thọ của thú cưng. Chó cần chế độ ăn giàu protein từ thịt, cá, trứng kết hợp với rau củ và carbohydrate. Mèo là động vật ăn thịt nên cần lượng protein cao hơn, đặc biệt là taurine. Tránh cho thú cưng ăn sô-cô-la, nho, hành tỏi, xương nhỏ có thể gây nghẹn. Nước sạch cần được thay mới hàng ngày. Chia nhỏ bữa ăn trong ngày giúp hệ tiêu hóa hoạt động tốt hơn. Nên tham khảo ý kiến bác sĩ thú y để có chế độ dinh dưỡng phù hợp với từng giai đoạn phát triển.',
'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=600',
'Chung',
1,
NOW());

-- Thêm dữ liệu mẫu cho video hướng dẫn
INSERT INTO `video_huong_dan` (`tieu_de`, `mo_ta`, `url_video`, `thumbnail`, `loai_thu_cung`, `thoi_luong`, `luot_xem`, `trang_thai`, `ngay_dang`) VALUES
('Cách Tắm Cho Chó Đúng Cách Tại Nhà',
'Video hướng dẫn chi tiết cách tắm cho chó tại nhà an toàn và hiệu quả. Từ chuẩn bị dụng cụ, nhiệt độ nước phù hợp, cách xoa dầu gội, xả sạch và sấy khô đúng kỹ thuật để chó không bị cảm lạnh.',
'https://www.youtube.com/embed/dQw4w9WgXcQ',
'https://images.unsplash.com/photo-1600369671738-ffcaf6c5f3dc?w=600',
'Chó',
'8:45',
1520,
1,
NOW()),

('Huấn Luyện Mèo Đi Vệ Sinh Đúng Chỗ',
'Hướng dẫn huấn luyện mèo sử dụng khay vệ sinh từ những ngày đầu tiên. Video bao gồm: chọn loại khay và cát vệ sinh phù hợp, đặt khay ở vị trí hợp lý, kỹ thuật huấn luyện từng bước và cách xử lý khi mèo không hợp tác.',
'https://www.youtube.com/embed/dQw4w9WgXcQ',
'https://images.unsplash.com/photo-1573865526739-10c1d3a1f0cc?w=600',
'Mèo',
'6:30',
2340,
1,
NOW()),

('Chăm Sóc Răng Miệng Cho Thú Cưng',
'Răng miệng khỏe mạnh là nền tảng sức khỏe tổng thể. Video hướng dẫn cách đánh răng cho chó mèo, chọn bàn chải và kem đánh răng chuyên dụng, tần suất vệ sinh và nhận biết các dấu hiệu bệnh răng miệng cần đưa thú cưng đến bác sĩ.',
'https://www.youtube.com/embed/dQw4w9WgXcQ',
'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=600',
'Chung',
'7:15',
980,
1,
NOW());

-- Thêm hình ảnh mẫu cho thú cưng (nếu bảng thu_cung chưa có dữ liệu)
-- Bạn có thể chạy các câu lệnh sau hoặc thêm dữ liệu qua phpMyAdmin

-- INSERT INTO `thu_cung` (`ten_thu_cung`, `loai`, `giong`, `tuoi`, `gioi_tinh`, `hinh_anh`, `mo_ta`, `trang_thai`) VALUES
-- ('Lucky', 'Chó', 'Golden Retriever', 2, 'Đực', 'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=400', 'Chú chó vàng hiền lành, thân thiện với trẻ em', 1),
-- ('Mimi', 'Mèo', 'Mèo Ba Tư', 1, 'Cái', 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400', 'Mèo Ba Tư lông dài, rất dễ thương', 1),
-- ('Buddy', 'Chó', 'Husky', 3, 'Đực', 'https://images.unsplash.com/photo-1568572933382-74d440642117?w=400', 'Husky Siberia năng động, thích chạy nhảy', 1),
-- ('Luna', 'Mèo', 'Mèo Munchkin', 1, 'Cái', 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=400', 'Mèo chân ngắn đáng yêu, rất thích chơi đùa', 1);
