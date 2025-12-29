<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_update.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách lịch đặt của user
try {
    $stmt = $conn->prepare("SELECT dl.*, dv.ten_dich_vu, tc.ten_thu_cung, tc.loai_thu_cung, tc.hinh_anh
                           FROM dat_lich_dich_vu dl
                           LEFT JOIN dich_vu dv ON dl.dich_vu_id = dv.id
                           LEFT JOIN thu_cung tc ON dl.thu_cung_id = tc.id
                           WHERE dl.nguoi_dung_id = ?
                           ORDER BY dl.ngay_dat_lich DESC, dl.gio_dat_lich DESC");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

$status_text = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'da_xac_nhan' => 'Đã xác nhận',
    'dang_thuc_hien' => 'Đang thực hiện',
    'hoan_thanh' => 'Hoàn thành',
    'da_huy' => 'Đã hủy'
];

$status_colors = [
    'cho_xac_nhan' => '#ffc107',
    'da_xac_nhan' => '#17a2b8',
    'dang_thuc_hien' => '#007bff',
    'hoan_thanh' => '#28a745',
    'da_huy' => '#dc3545'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Đặt Dịch Vụ - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding-bottom: 50px;
        }

        .header {
            background: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-image {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #ff6b9d, #ffa07a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .brand-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .back-link {
            text-decoration: none;
            color: #667eea;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .back-link:hover {
            color: #764ba2;
            gap: 12px;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .page-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .bookings-grid {
            display: grid;
            gap: 20px;
        }

        .booking-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .booking-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .booking-code {
            font-size: 18px;
            font-weight: 600;
        }

        .booking-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
        }

        .booking-body {
            padding: 25px;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 25px;
        }

        .pet-image {
            width: 120px;
            height: 120px;
            border-radius: 10px;
            object-fit: cover;
        }

        .booking-details {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .detail-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-icon {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #ff6b9d, #ffa07a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 12px;
            color: #999;
            margin-bottom: 3px;
        }

        .detail-value {
            font-size: 15px;
            color: #333;
            font-weight: 500;
        }

        .service-name {
            font-size: 18px;
            color: #667eea;
            font-weight: 600;
        }

        .price-tag {
            font-size: 20px;
            color: #28a745;
            font-weight: bold;
        }

        .no-bookings {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
        }

        .no-bookings i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .no-bookings h3 {
            font-size: 24px;
            color: #666;
            margin-bottom: 10px;
        }

        .no-bookings p {
            color: #999;
            margin-bottom: 25px;
        }

        .btn-book-now {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-book-now:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        @media (max-width: 768px) {
            .booking-body {
                grid-template-columns: 1fr;
            }

            .pet-image {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="nav-container">
            <div class="logo-container">
                <div class="logo-image">
                    <i class="fas fa-paw"></i>
                </div>
                <div class="brand-name">Pet Care Center</div>
            </div>
            <a href="../index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-calendar-check"></i> Lịch Đặt Dịch Vụ</h1>
            <p>Quản lý các lịch hẹn dịch vụ của bạn</p>
        </div>

        <div class="bookings-grid">
            <?php if (count($bookings) > 0): ?>
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <div class="booking-header">
                            <div class="booking-code">
                                <i class="fas fa-ticket-alt"></i> <?php echo htmlspecialchars($booking['ma_dat_lich']); ?>
                            </div>
                            <div class="booking-status" style="background: <?php echo $status_colors[$booking['trang_thai']] ?? '#6c757d'; ?>">
                                <?php echo $status_text[$booking['trang_thai']] ?? 'Không xác định'; ?>
                            </div>
                        </div>

                        <div class="booking-body">
                            <?php if ($booking['hinh_anh']): ?>
                                <img src="<?php echo htmlspecialchars($booking['hinh_anh']); ?>" alt="Pet" class="pet-image">
                            <?php else: ?>
                                <img src="../images/image/default-pet.jpg" alt="Pet" class="pet-image">
                            <?php endif; ?>

                            <div class="booking-details">
                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="fas fa-concierge-bell"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Dịch vụ</div>
                                        <div class="service-name"><?php echo htmlspecialchars($booking['ten_dich_vu']); ?></div>
                                    </div>
                                </div>

                                <?php if ($booking['ten_thu_cung']): ?>
                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="fas fa-paw"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Thú cưng</div>
                                        <div class="detail-value">
                                            <?php echo htmlspecialchars($booking['ten_thu_cung']); ?> 
                                            (<?php echo htmlspecialchars($booking['loai_thu_cung']); ?>)
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="fas fa-calendar"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Ngày & giờ</div>
                                        <div class="detail-value">
                                            <?php echo date('d/m/Y', strtotime($booking['ngay_dat_lich'])); ?> 
                                            lúc <?php echo date('H:i', strtotime($booking['gio_dat_lich'])); ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Tổng tiền</div>
                                        <div class="price-tag"><?php echo number_format($booking['tong_tien'], 0, ',', '.'); ?> VNĐ</div>
                                    </div>
                                </div>

                                <?php if ($booking['ghi_chu']): ?>
                                <div class="detail-row">
                                    <div class="detail-icon">
                                        <i class="fas fa-sticky-note"></i>
                                    </div>
                                    <div class="detail-content">
                                        <div class="detail-label">Ghi chú</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($booking['ghi_chu']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-bookings">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Chưa có lịch đặt nào</h3>
                    <p>Bạn chưa đặt dịch vụ nào. Hãy khám phá các dịch vụ của chúng tôi!</p>
                    <a href="../pages/dichvu.php" class="btn-book-now">
                        <i class="fas fa-plus-circle"></i> Đặt dịch vụ ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php require_once '../includes/chat_widget.php'; ?>
</body>
</html>
