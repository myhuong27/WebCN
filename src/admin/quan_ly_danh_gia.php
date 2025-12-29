<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 2) {
    header('Location: ../index.php');
    exit();
}

// Xử lý phản hồi đánh giá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'phan_hoi') {
    try {
        $stmt = $conn->prepare("UPDATE danh_gia SET phan_hoi_admin = ?, ngay_phan_hoi = NOW() WHERE id = ?");
        $stmt->execute([$_POST['phan_hoi'], $_POST['danh_gia_id']]);
        $success = "Đã phản hồi đánh giá thành công!";
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách đánh giá
try {
    $stmt = $conn->query("SELECT dg.*, nd.ho_ten, nd.email,
                         CASE 
                            WHEN dg.loai = 'dich_vu' THEN dv.ten_dich_vu
                            WHEN dg.loai = 'nuoi_ho' THEN 'Dịch vụ nuôi hộ'
                         END as ten_dich_vu
                         FROM danh_gia dg
                         LEFT JOIN nguoi_dung nd ON dg.nguoi_dung_id = nd.id
                         LEFT JOIN dat_lich_dich_vu dl ON dg.dat_lich_id = dl.id
                         LEFT JOIN dich_vu dv ON dl.dich_vu_id = dv.id
                         ORDER BY dg.ngay_tao DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đánh giá - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 32px;
        }

        .btn-back {
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.3);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }

        .reviews-grid {
            display: grid;
            gap: 20px;
        }

        .review-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }

        .review-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 24px;
        }

        .stars {
            color: #ffc107;
            font-size: 20px;
        }

        .review-body {
            padding: 25px;
        }

        .service-name {
            font-size: 16px;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .review-content {
            color: #333;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .review-date {
            font-size: 13px;
            color: #999;
        }

        .response-section {
            background: #f8f9fa;
            padding: 20px;
            border-top: 2px solid #e0e0e0;
        }

        .response-content {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
            margin-bottom: 10px;
        }

        .response-form textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
            margin-bottom: 10px;
        }

        .btn-submit {
            padding: 10px 25px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-star"></i> Quản lý Đánh giá</h1>
            <a href="dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại Dashboard
            </a>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-card">
                <h3>Tổng đánh giá</h3>
                <div class="number"><?php echo count($reviews); ?></div>
            </div>
            <div class="stat-card">
                <h3>Chưa phản hồi</h3>
                <div class="number"><?php echo count(array_filter($reviews, fn($r) => empty($r['phan_hoi_admin']))); ?></div>
            </div>
            <div class="stat-card">
                <h3>Đánh giá trung bình</h3>
                <div class="number">
                    <?php 
                    $avg = count($reviews) > 0 ? array_sum(array_column($reviews, 'so_sao')) / count($reviews) : 0;
                    echo number_format($avg, 1); 
                    ?> <i class="fas fa-star" style="font-size: 20px; color: #ffc107;"></i>
                </div>
            </div>
        </div>

        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="user-info">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600;"><?php echo htmlspecialchars($review['ho_ten'] ?? $review['username']); ?></div>
                                <div style="font-size: 13px; opacity: 0.9;"><?php echo date('d/m/Y H:i', strtotime($review['ngay_tao'])); ?></div>
                            </div>
                        </div>
                        <div class="stars">
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star<?php echo $i <= $review['so_sao'] ? '' : '-o'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="review-body">
                        <div class="service-name">
                            <i class="fas fa-<?php echo $review['loai'] == 'dich_vu' ? 'concierge-bell' : 'home'; ?>"></i>
                            <?php echo htmlspecialchars($review['ten_dich_vu']); ?>
                        </div>
                        <div class="review-content"><?php echo htmlspecialchars($review['noi_dung']); ?></div>
                    </div>

                    <div class="response-section">
                        <?php if ($review['phan_hoi_admin']): ?>
                            <div class="response-content">
                                <div style="font-weight: 600; color: #28a745; margin-bottom: 8px;">
                                    <i class="fas fa-reply"></i> Phản hồi từ Admin
                                </div>
                                <div><?php echo nl2br(htmlspecialchars($review['phan_hoi_admin'])); ?></div>
                                <div class="review-date" style="margin-top: 10px;">
                                    <?php echo date('d/m/Y H:i', strtotime($review['ngay_phan_hoi'])); ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="response-form">
                                <input type="hidden" name="action" value="phan_hoi">
                                <input type="hidden" name="danh_gia_id" value="<?php echo $review['id']; ?>">
                                <textarea name="phan_hoi" placeholder="Nhập phản hồi của bạn..." required></textarea>
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-paper-plane"></i> Gửi phản hồi
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (count($reviews) == 0): ?>
                <div style="text-align: center; padding: 60px; background: white; border-radius: 15px;">
                    <i class="fas fa-star" style="font-size: 80px; color: #ddd;"></i>
                    <h3 style="margin-top: 20px; color: #666;">Chưa có đánh giá nào</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
