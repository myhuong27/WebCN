<?php
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên Hệ - Fashion Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .contact-section {
            background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.8));
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 40px auto;
        }

        .contact-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5em;
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(45deg, #2c3e50, #3498db, #e74c3c);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            padding: 20px;
        }

        .contact-item {
            padding: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .contact-item:hover {
            transform: translateY(-5px);
        }

        .contact-label {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.2em;
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .contact-label i {
            color: #3498db;
            font-size: 1.4em;
        }

        .contact-value {
            font-family: 'Playfair Display', serif;
            font-size: 1.1em;
            color: #34495e;
            padding: 10px;
            border-left: 3px solid #3498db;
            margin-left: 25px;
        }

        .store-name {
            font-family: 'Dancing Script', cursive;
            font-size: 1.8em;
            color: #e74c3c;
            text-align: center;
            margin: 20px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .contact-info {
                grid-template-columns: 1fr;
            }
            
            .contact-title {
                font-size: 2em;
            }
            
            .contact-item {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="contact-section">
        <h2 class="contact-title">Thông Tin Liên Hệ</h2>
        
        <div class="contact-info">
            <div class="contact-item">
                <div class="contact-label">
                    <i class="fas fa-map-marker-alt"></i>
                    Địa Chỉ
                </div>
                <div class="contact-value">
                    Châu Thành, Trà Vinh
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-label">
                    <i class="fas fa-store"></i>
                    Chủ Cửa Hàng
                </div>
                <div class="contact-value store-name">
                    𝑀Ỹ 𝐻ƯƠ𝒩𝒢 𝒮𝒯𝒪𝑅𝐸
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-label">
                    <i class="fas fa-phone"></i>
                    Số Điện Thoại
                </div>
                <div class="contact-value">
                    <a href="tel:0379708918" style="text-decoration: none; color: inherit;">
                        0379708918
                    </a>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-label">
                    <i class="fas fa-envelope"></i>
                    Email
                </div>
                <div class="contact-value">
                    <a href="mailto:fashionstore@gmail.com" style="text-decoration: none; color: inherit;">
                        fashionstore@gmail.com
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php require_once '../includes/chat_widget.php'; ?>
</body>
</html>
