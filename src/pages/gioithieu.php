<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới Thiệu - Fashion Store | Thời Trang Cao Cấp</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(
                135deg,
                #d4cce6 0%,
                #e6d4d4 25%,
                #e6d4c0 50%,
                #d4e3f0 75%,
                #d4cce6 100%
            );
            background-attachment: fixed;
        }

        .about-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .about-header {
            text-align: center;
            margin-bottom: 50px;
            padding: 100px 20px;
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.8)),
                        url('images/about/header-bg.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .about-title {
            font-family: 'Playfair Display', serif;
            font-size: 4.5em;
            font-weight: 700;
            background: linear-gradient(45deg, #2c3e50, #3498db, #e74c3c);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            letter-spacing: 3px;
            animation: titleGlow 2s ease-in-out infinite alternate;
        }

        .about-subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.8em;
            font-weight: 300;
            color: #34495e;
            margin-top: 20px;
            letter-spacing: 2px;
            position: relative;
            display: inline-block;
            padding: 0 30px;
        }

        .about-subtitle::before,
        .about-subtitle::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 50px;
            height: 2px;
            background: linear-gradient(90deg, #3498db, transparent);
        }

        .about-subtitle::before {
            left: -30px;
        }

        .about-subtitle::after {
            right: -30px;
            background: linear-gradient(90deg, transparent, #3498db);
        }

        /* Thêm hiệu ứng animation */
        @keyframes titleGlow {
            from {
                text-shadow: 0 0 10px rgba(52, 152, 219, 0.2),
                            0 0 20px rgba(52, 152, 219, 0.1);
            }
            to {
                text-shadow: 0 0 15px rgba(52, 152, 219, 0.4),
                            0 0 30px rgba(52, 152, 219, 0.2);
            }
        }

        /* Thêm hiệu ứng hover */
        .about-header:hover .about-title {
            transform: scale(1.02);
            transition: transform 0.3s ease;
        }

        .about-header:hover .about-subtitle {
            color: #2c3e50;
            transition: color 0.3s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .about-header {
                padding: 60px 15px;
            }

            .about-title {
                font-size: 3em;
                letter-spacing: 2px;
            }

            .about-subtitle {
                font-size: 1.4em;
                padding: 0 20px;
            }

            .about-subtitle::before,
            .about-subtitle::after {
                width: 30px;
            }
        }

        .story-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 40px 0;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .story-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }

        .story-content {
            padding: 20px;
        }

        .story-title {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 20px;
        }

        .story-text {
            color: #666;
            line-height: 1.8;
        }

        .values-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin: 40px 0;
        }

        .value-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-10px);
        }

        .value-icon {
            font-size: 2.5em;
            color: #d4cce6;
            margin-bottom: 20px;
        }

        .value-title {
            font-size: 1.3em;
            color: #333;
            margin-bottom: 15px;
        }

        .value-text {
            color: #666;
            line-height: 1.6;
        }

        .about-link {
            text-align: center;
            margin: 20px 0;
        }

        .about-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            background: linear-gradient(135deg, #d4cce6, #e6d4d4);
            color: #333;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .about-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .about-button i {
            font-size: 1.2em;
        }

        .about-link-section {
            text-align: center;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.97);
            margin: 40px 0;
        }

        .about-link-btn {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            padding: 15px 30px;
            background: linear-gradient(135deg, #d4cce6, #e6d4d4);
            color: #333;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .about-link-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .about-link-btn .btn-text {
            font-size: 1.1em;
        }

        .about-link-btn i {
            transition: transform 0.3s ease;
        }

        .about-link-btn:hover i {
            transform: translateX(5px);
        }

        @media (max-width: 768px) {
            .story-section {
                grid-template-columns: 1fr;
            }

            .values-section {
                grid-template-columns: 1fr;
            }

            .about-title {
                font-size: 2.5em;
            }

            .story-image {
                height: 300px;
            }

            .about-link-btn {
                padding: 12px 25px;
            }
            
            .about-link-btn .btn-text {
                font-size: 1em;
            }
        }

        /* Thêm style cho timeline */
        .timeline-section {
            position: relative;
            padding: 40px 0;
            margin: 40px 0;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
        }

        .timeline {
            position: relative;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .timeline::after {
            content: '';
            position: absolute;
            width: 2px;
            background: #d4cce6;
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -1px;
        }

        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
        }

        .timeline-item:nth-child(odd) {
            left: 0;
        }

        .timeline-item:nth-child(even) {
            left: 50%;
        }

        .timeline-content {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* Style cho achievements section */
        .achievements-section {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            margin: 40px 0;
        }

        .achievement-item {
            text-align: center;
            padding: 20px;
        }

        .achievement-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .achievements-section {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .timeline::after {
                left: 31px;
            }
            
            .timeline-item {
                width: 100%;
                padding-left: 70px;
            }
            
            .timeline-item:nth-child(even) {
                left: 0;
            }
        }

        /* Thêm style cho main menu */
        .main-menu {
            background: linear-gradient(135deg, #d4cce6 0%, #e6d4d4 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1000;
        }

        .menu-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 40px;
        }

        .menu-item {
            font-family: 'Playfair Display', serif;
            color: #333;
            text-decoration: none;
            font-size: 1.1em;
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
        }

        .menu-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 2px;
            background: #333;
            transition: all 0.3s ease;
            transform: translateX(-50%);
        }

        .menu-item:hover::after,
        .menu-item.active::after {
            width: 70%;
        }

        @media (max-width: 768px) {
            .menu-container {
                gap: 20px;
                padding: 0 15px;
                flex-wrap: wrap;
            }

            .menu-item {
                font-size: 1em;
                padding: 6px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="about-container">
        <div class="about-header">
            <h1 class="about-title">FASHION STORE</h1>
            <p class="about-subtitle">Nơi Phong Cách Được Định Nghĩa</p>
        </div>

        <!-- Story Section -->
        <div class="story-section">
            <img src="images/image/h32.jpg" alt="Our Store" class="story-image">
            <div class="story-content">
                <h2 class="story-title">Hành Trình Của Chúng Tôi</h2>
                <p class="story-text">
                    Được thành lập vào năm 2020, Fashion Store là điểm đến lý tưởng cho những người yêu thích thời trang và phong cách sống hiện đại. Chúng tôi không chỉ đơn thuần là một cửa hàng thời trang, mà còn là nơi định hình phong cách và khơi dậy cảm hứng.
                </p>
                <p class="story-text">
                    Với sứ mệnh mang đến những bộ trang phục chất lượng cao cấp, thiết kế độc đáo và phù hợp với văn hóa Việt Nam, chúng tôi tự hào là thương hiệu được tin chọn của hàng nghìn khách hàng.
                </p>
            </div>
        </div>

        <!-- Achievements Section -->
        <div class="achievements-section">
            <div class="achievement-item">
                <div class="achievement-number">5000+</div>
                <p>Khách Hàng Hài Lòng</p>
            </div>
            <div class="achievement-item">
                <div class="achievement-number">1000+</div>
                <p>Sản Phẩm Độc Đáo</p>
            </div>
            <div class="achievement-item">
                <div class="achievement-number">10+</div>
                <p>Chi Nhánh</p>
            </div>
            <div class="achievement-item">
                <div class="achievement-number">50+</div>
                <p>Nhân Viên Tận Tâm</p>
            </div>
        </div>

        <!-- Values Section -->
        <div class="values-section">
            <div class="value-card">
                <i class="fas fa-heart value-icon"></i>
                <h3 class="value-title">Tận Tâm Phục Vụ</h3>
                <p class="value-text">Mỗi khách hàng đều là VIP. Chúng tôi cam kết mang đến trải nghiệm mua sắm tuyệt vời nhất.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-gem value-icon"></i>
                <h3 class="value-title">Chất Lượng Hàng Đầu</h3>
                <p class="value-text">Sử dụng các chất liệu cao cấp và quy trình sản xuất nghiêm ngặt để đảm bảo chất lượng tốt nhất.</p>
            </div>
            <div class="value-card">
                <i class="fas fa-star value-icon"></i>
                <h3 class="value-title">Sáng Tạo Không Ngừng</h3>
                <p class="value-text">Liên tục cập nhật xu hướng và sáng tạo những thiết kế độc đáo, phù hợp với thị hiếu.</p>
            </div>
        </div>
    </div>
<?php require_once '../includes/chat_widget.php'; ?>
</body>
</html>
