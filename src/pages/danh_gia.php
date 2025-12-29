<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_update.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$dat_lich_id = $_GET['dat_lich_id'] ?? null;
$yeu_cau_nuoi_ho_id = $_GET['yeu_cau_id'] ?? null;

// Kiểm tra xem đã đánh giá chưa
$da_danh_gia = false;
if ($dat_lich_id) {
    $stmt = $conn->prepare("SELECT id FROM danh_gia WHERE nguoi_dung_id = ? AND dat_lich_id = ?");
    $stmt->execute([$user_id, $dat_lich_id]);
    $da_danh_gia = $stmt->fetch() ? true : false;
}

// Xử lý gửi đánh giá
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $loai = $dat_lich_id ? 'dich_vu' : 'nuoi_ho';
        $stmt = $conn->prepare("INSERT INTO danh_gia (nguoi_dung_id, loai, dat_lich_id, yeu_cau_nuoi_ho_id, so_sao, noi_dung) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $loai,
            $dat_lich_id,
            $yeu_cau_nuoi_ho_id,
            $_POST['so_sao'],
            $_POST['noi_dung']
        ]);
        $success = "Cảm ơn bạn đã đánh giá! Đánh giá của bạn sẽ giúp chúng tôi cải thiện dịch vụ.";
        $da_danh_gia = true;
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Lấy thông tin dịch vụ/nuôi hộ
$info = null;
if ($dat_lich_id) {
    $stmt = $conn->prepare("SELECT dl.*, dv.ten_dich_vu FROM dat_lich_dich_vu dl
                           LEFT JOIN dich_vu dv ON dl.dich_vu_id = dv.id
                           WHERE dl.id = ? AND dl.nguoi_dung_id = ?");
    $stmt->execute([$dat_lich_id, $user_id]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá Dịch vụ - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .service-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .service-info h3 {
            color: #667eea;
            margin-bottom: 10px;
        }

        .star-rating {
            text-align: center;
            margin: 30px 0;
        }

        .star-rating .stars {
            font-size: 50px;
            cursor: pointer;
            display: inline-flex;
            gap: 10px;
        }

        .star-rating .stars i {
            color: #ddd;
            transition: all 0.2s;
        }

        .star-rating .stars i.active,
        .star-rating .stars i:hover {
            color: #ffc107;
            transform: scale(1.2);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            resize: vertical;
            min-height: 150px;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .thank-you {
            text-align: center;
            padding: 40px;
        }

        .thank-you i {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .thank-you h2 {
            color: #333;
            margin-bottom: 15px;
        }

        .thank-you p {
            color: #666;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-star"></i> Đánh giá Dịch vụ</h1>
            <p>Chia sẻ trải nghiệm của bạn với chúng tôi</p>
        </div>

        <div class="content">
            <?php if ($da_danh_gia): ?>
                <div class="thank-you">
                    <i class="fas fa-check-circle"></i>
                    <h2>Cảm ơn bạn đã đánh giá!</h2>
                    <p><?php echo $success ?? 'Bạn đã đánh giá dịch vụ này rồi.'; ?></p>
                    <a href="dichvu.php" class="btn-submit" style="display: inline-block; text-decoration: none; width: auto; padding: 15px 40px;">
                        <i class="fas fa-arrow-left"></i> Quay lại Dịch vụ
                    </a>
                </div>
            <?php else: ?>
                <?php if (isset($error)): ?>
                    <div class="alert error"><?php echo $error; ?></div>
                <?php endif; ?>

                <?php if ($info): ?>
                    <div class="service-info">
                        <h3><i class="fas fa-concierge-bell"></i> <?php echo htmlspecialchars($info['ten_dich_vu']); ?></h3>
                        <p>Ngày sử dụng: <?php echo date('d/m/Y', strtotime($info['ngay_dat_lich'])); ?></p>
                    </div>
                <?php endif; ?>

                <form method="POST" id="reviewForm">
                    <div class="star-rating">
                        <p style="margin-bottom: 15px; font-weight: 600; color: #333;">Bạn đánh giá thế nào về dịch vụ?</p>
                        <div class="stars" id="starRating">
                            <i class="fas fa-star" data-value="1"></i>
                            <i class="fas fa-star" data-value="2"></i>
                            <i class="fas fa-star" data-value="3"></i>
                            <i class="fas fa-star" data-value="4"></i>
                            <i class="fas fa-star" data-value="5"></i>
                        </div>
                        <input type="hidden" name="so_sao" id="soSao" required>
                    </div>

                    <div class="form-group">
                        <label>Chia sẻ trải nghiệm của bạn</label>
                        <textarea name="noi_dung" placeholder="Hãy chia sẻ cảm nhận của bạn về dịch vụ..." required></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Gửi đánh giá
                    </button>
                </form>

                <a href="dichvu.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const stars = document.querySelectorAll('#starRating i');
        const soSaoInput = document.getElementById('soSao');
        
        stars.forEach(star => {
            star.addEventListener('click', () => {
                const value = parseInt(star.getAttribute('data-value'));
                soSaoInput.value = value;
                
                stars.forEach((s, index) => {
                    if (index < value) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });

            star.addEventListener('mouseover', () => {
                const value = parseInt(star.getAttribute('data-value'));
                stars.forEach((s, index) => {
                    if (index < value) {
                        s.style.color = '#ffc107';
                    }
                });
            });

            star.addEventListener('mouseout', () => {
                const currentValue = parseInt(soSaoInput.value || 0);
                stars.forEach((s, index) => {
                    if (index < currentValue) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });

        document.getElementById('reviewForm')?.addEventListener('submit', (e) => {
            if (!soSaoInput.value) {
                e.preventDefault();
                alert('Vui lòng chọn số sao đánh giá!');
            }
        });
    </script>
</body>
</html>
