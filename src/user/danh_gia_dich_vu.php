<?php
session_start();
require_once '../config/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_update.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý gửi đánh giá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO danh_gia (nguoi_dung_id, dat_lich_id, loai, so_sao, noi_dung) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $_POST['dat_lich_id'],
            $_POST['loai'],
            $_POST['so_sao'],
            $_POST['noi_dung']
        ]);
        $success = "Cảm ơn bạn đã đánh giá! Đánh giá của bạn rất quan trọng với chúng tôi.";
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách lịch đã hoàn thành của user (để đánh giá)
$completed_bookings = [];
try {
    $stmt = $conn->prepare("
        SELECT dl.*, dv.ten_dich_vu, dv.hinh_anh,
        (SELECT COUNT(*) FROM danh_gia WHERE dat_lich_id = dl.id AND nguoi_dung_id = ?) as da_danh_gia
        FROM dat_lich_dich_vu dl
        LEFT JOIN dich_vu dv ON dl.dich_vu_id = dv.id
        WHERE dl.nguoi_dung_id = ? AND dl.trang_thai = 2
        ORDER BY dl.ngay_dat_lich DESC
    ");
    $stmt->execute([$user_id, $user_id]);
    $completed_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Lỗi: " . $e->getMessage();
}

// Lấy đánh giá đã gửi
$my_reviews = [];
try {
    $stmt = $conn->prepare("
        SELECT dg.*, dv.ten_dich_vu, dv.hinh_anh, dl.ngay_dat_lich
        FROM danh_gia dg
        LEFT JOIN dat_lich_dich_vu dl ON dg.dat_lich_id = dl.id
        LEFT JOIN dich_vu dv ON dl.dich_vu_id = dv.id
        WHERE dg.nguoi_dung_id = ?
        ORDER BY dg.ngay_tao DESC
    ");
    $stmt->execute([$user_id]);
    $my_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Lỗi: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá dịch vụ - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .booking-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
            transition: all 0.3s;
        }

        .booking-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .booking-img {
            width: 100px;
            height: 100px;
            border-radius: 10px;
            object-fit: cover;
        }

        .booking-info {
            flex: 1;
        }

        .booking-info h3 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        .booking-info p {
            color: #666;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .btn-review {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-review:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-reviewed {
            background: #28a745;
            cursor: not-allowed;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            margin-bottom: 30px;
        }

        .modal-header h2 {
            font-size: 24px;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
        }

        .star-rating {
            display: flex;
            gap: 10px;
            font-size: 30px;
        }

        .star {
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }

        .star:hover,
        .star.active {
            color: #ffc107;
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
            min-height: 120px;
        }

        textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-close {
            background: #6c757d;
            padding: 10px 20px;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }

        .review-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .review-stars {
            color: #ffc107;
            font-size: 18px;
        }

        .review-date {
            color: #999;
            font-size: 13px;
        }

        .review-content {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .admin-reply {
            background: #f8f9fa;
            padding: 15px;
            border-left: 3px solid #667eea;
            border-radius: 8px;
            margin-top: 15px;
        }

        .admin-reply-label {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 5px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="user_dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Quay lại Dashboard
        </a>

        <div class="header">
            <h1><i class="fas fa-star"></i> Đánh giá Dịch vụ</h1>
            <p>Chia sẻ trải nghiệm của bạn về dịch vụ đã sử dụng</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Dịch vụ cần đánh giá -->
        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-clipboard-check"></i>
                Dịch vụ đã sử dụng
            </h2>

            <?php if (count($completed_bookings) > 0): ?>
                <?php foreach ($completed_bookings as $booking): ?>
                    <div class="booking-card">
                        <img src="../<?php echo htmlspecialchars($booking['hinh_anh'] ?? 'images/image/h1.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($booking['ten_dich_vu']); ?>" 
                             class="booking-img">
                        <div class="booking-info">
                            <h3><?php echo htmlspecialchars($booking['ten_dich_vu']); ?></h3>
                            <p><i class="fas fa-calendar"></i> Ngày sử dụng: <?php echo date('d/m/Y', strtotime($booking['ngay_dat_lich'])); ?></p>
                            <p><i class="fas fa-money-bill"></i> Giá: <?php echo number_format($booking['tong_tien']); ?>đ</p>
                        </div>
                        <?php if ($booking['da_danh_gia'] > 0): ?>
                            <button class="btn-review btn-reviewed" disabled>
                                <i class="fas fa-check"></i> Đã đánh giá
                            </button>
                        <?php else: ?>
                            <button class="btn-review" onclick="openReviewModal(<?php echo $booking['id']; ?>, '<?php echo htmlspecialchars($booking['ten_dich_vu']); ?>', 'dich_vu')">
                                <i class="fas fa-star"></i> Đánh giá ngay
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Bạn chưa có dịch vụ nào hoàn thành để đánh giá</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Đánh giá đã gửi -->
        <?php if (count($my_reviews) > 0): ?>
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-comments"></i>
                    Đánh giá của bạn
                </h2>

                <?php foreach ($my_reviews as $review): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <div>
                                <strong><?php echo htmlspecialchars($review['ten_dich_vu']); ?></strong>
                                <div class="review-stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $review['so_sao'] ? '' : '-o'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="review-date">
                                <?php echo date('d/m/Y H:i', strtotime($review['ngay_tao'])); ?>
                            </div>
                        </div>
                        <div class="review-content">
                            <?php echo nl2br(htmlspecialchars($review['noi_dung'])); ?>
                        </div>
                        <?php if ($review['phan_hoi_admin']): ?>
                            <div class="admin-reply">
                                <div class="admin-reply-label">
                                    <i class="fas fa-reply"></i> Phản hồi từ Pet Care Center:
                                </div>
                                <div><?php echo nl2br(htmlspecialchars($review['phan_hoi_admin'])); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal đánh giá -->
    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-star"></i> Đánh giá dịch vụ</h2>
                <p id="serviceName" style="color: #666; margin-top: 5px;"></p>
            </div>

            <form method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="dat_lich_id" id="datLichId">
                <input type="hidden" name="loai" id="loaiDanhGia">
                <input type="hidden" name="so_sao" id="soSaoInput" value="5">

                <div class="form-group">
                    <label>Đánh giá của bạn <span style="color: red;">*</span></label>
                    <div class="star-rating" id="starRating">
                        <i class="fas fa-star star active" data-value="1"></i>
                        <i class="fas fa-star star active" data-value="2"></i>
                        <i class="fas fa-star star active" data-value="3"></i>
                        <i class="fas fa-star star active" data-value="4"></i>
                        <i class="fas fa-star star active" data-value="5"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>Nhận xét chi tiết <span style="color: red;">*</span></label>
                    <textarea name="noi_dung" id="noiDung" placeholder="Chia sẻ trải nghiệm của bạn về dịch vụ..." required></textarea>
                </div>

                <button type="submit" name="submit_review" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Gửi đánh giá
                </button>
                <button type="button" class="btn-close" onclick="closeReviewModal()">
                    Đóng
                </button>
            </form>
        </div>
    </div>

    <script>
        function openReviewModal(datLichId, serviceName, loai) {
            document.getElementById('datLichId').value = datLichId;
            document.getElementById('serviceName').textContent = serviceName;
            document.getElementById('loaiDanhGia').value = loai;
            document.getElementById('reviewModal').classList.add('active');
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').classList.remove('active');
            document.getElementById('noiDung').value = '';
        }

        // Star rating
        const stars = document.querySelectorAll('.star');
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                document.getElementById('soSaoInput').value = value;
                
                stars.forEach(s => {
                    if (s.getAttribute('data-value') <= value) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });

        function validateForm() {
            const noiDung = document.getElementById('noiDung').value.trim();
            if (noiDung.length < 10) {
                alert('Vui lòng nhập nhận xét ít nhất 10 ký tự');
                return false;
            }
            return true;
        }

        // Close modal when clicking outside
        document.getElementById('reviewModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReviewModal();
            }
        });
    </script>
</body>
</html>
