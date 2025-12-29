<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_update.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy danh sách lịch hẹn của user
try {
    $stmt = $conn->prepare("SELECT lh.*, dv.ten_dich_vu, dv.gia, tc.ten_thu_cung, tt.trang_thai as tt_thanh_toan, tt.so_tien
                           FROM lich_hen lh
                           LEFT JOIN dich_vu dv ON lh.dich_vu_id = dv.id
                           LEFT JOIN thu_cung tc ON lh.thu_cung_id = tc.id
                           LEFT JOIN dat_lich_dich_vu dl ON lh.id = dl.lich_hen_id
                           LEFT JOIN thanh_toan tt ON dl.id = tt.dat_lich_id
                           WHERE lh.nguoi_dung_id = ?
                           ORDER BY lh.ngay_hen DESC, lh.gio_hen DESC");
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theo dõi đơn hàng - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f5f7fa;
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
            gap: 12px;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 36px;
            color: #333;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 16px;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .tab {
            padding: 10px 20px;
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .tab:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: #667eea;
        }

        .order-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }

        .order-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .order-id {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .order-date {
            color: #999;
            font-size: 14px;
            margin-top: 5px;
        }

        .order-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 14px;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1ecf1; color: #0c5460; }
        .status-inprogress { background: #cce5ff; color: #004085; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }

        .order-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .order-info {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-row i {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            border-radius: 50%;
            color: #667eea;
            font-size: 14px;
        }

        .info-label {
            font-weight: 500;
            color: #666;
            min-width: 100px;
        }

        .info-value {
            color: #333;
            font-weight: 500;
        }

        .payment-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }

        .payment-section h4 {
            margin-bottom: 10px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .payment-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 500;
        }

        .payment-pending { background: #fff3cd; color: #856404; }
        .payment-success { background: #d4edda; color: #155724; }
        .payment-failed { background: #f8d7da; color: #721c24; }

        .price {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-top: 10px;
        }

        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
        }

        .empty-state i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .order-body {
                grid-template-columns: 1fr;
            }

            .order-actions {
                flex-direction: column;
            }
        }

        .timeline {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }

        .timeline-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .timeline-item {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            position: relative;
        }

        .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 35px;
            bottom: -15px;
            width: 2px;
            background: #e0e0e0;
        }

        .timeline-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .timeline-icon.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .timeline-icon.inactive {
            background: #e0e0e0;
            color: #999;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-label {
            font-weight: 500;
            color: #333;
        }

        .timeline-time {
            font-size: 13px;
            color: #999;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="nav-container">
            <div class="logo-container">
                <div class="logo-image">
                    <i class="fas fa-paw"></i>
                </div>
                <div>
                    <div class="brand-name">Pet Care Center</div>
                </div>
            </div>
            <a href="../index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-clipboard-list"></i> Theo Dõi Đơn Hàng</h1>
            <p>Quản lý và theo dõi tất cả lịch hẹn của bạn</p>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <div class="tab active" onclick="filterOrders('all')">
                <i class="fas fa-list"></i> Tất cả
            </div>
            <div class="tab" onclick="filterOrders('pending')">
                <i class="fas fa-clock"></i> Chờ duyệt
            </div>
            <div class="tab" onclick="filterOrders('confirmed')">
                <i class="fas fa-check-circle"></i> Đã xác nhận
            </div>
            <div class="tab" onclick="filterOrders('completed')">
                <i class="fas fa-check-double"></i> Hoàn thành
            </div>
            <div class="tab" onclick="filterOrders('cancelled')">
                <i class="fas fa-times-circle"></i> Đã hủy
            </div>
        </div>

        <!-- Order List -->
        <div class="order-list">
            <?php if (!empty($appointments)): ?>
                <?php 
                $status_map = [0 => 'pending', 1 => 'confirmed', 2 => 'inprogress', 3 => 'completed', 4 => 'cancelled'];
                $status_text = [0 => 'Chờ duyệt', 1 => 'Đã xác nhận', 2 => 'Đang thực hiện', 3 => 'Hoàn thành', 4 => 'Đã hủy'];
                $status_icon = [0 => 'clock', 1 => 'check-circle', 2 => 'spinner', 3 => 'check-double', 4 => 'times-circle'];
                
                foreach ($appointments as $apt): 
                    $status = $status_map[$apt['trang_thai']];
                ?>
                <div class="order-card" data-status="<?php echo $status; ?>">
                    <div class="order-header">
                        <div>
                            <div class="order-id">
                                <i class="fas fa-hashtag"></i> <?php echo $apt['ma_lich_hen']; ?>
                            </div>
                            <div class="order-date">
                                Đặt lịch: <?php echo date('d/m/Y H:i', strtotime($apt['ngay_tao'])); ?>
                            </div>
                        </div>
                        <div class="order-status status-<?php echo $status; ?>">
                            <i class="fas fa-<?php echo $status_icon[$apt['trang_thai']]; ?>"></i>
                            <?php echo $status_text[$apt['trang_thai']]; ?>
                        </div>
                    </div>

                    <div class="order-body">
                        <div class="order-info">
                            <div class="info-row">
                                <i class="fas fa-concierge-bell"></i>
                                <span class="info-label">Dịch vụ:</span>
                                <span class="info-value"><?php echo htmlspecialchars($apt['ten_dich_vu'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-paw"></i>
                                <span class="info-label">Thú cưng:</span>
                                <span class="info-value"><?php echo htmlspecialchars($apt['ten_thu_cung'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-calendar-alt"></i>
                                <span class="info-label">Ngày hẹn:</span>
                                <span class="info-value"><?php echo date('d/m/Y', strtotime($apt['ngay_hen'])); ?></span>
                            </div>
                            <div class="info-row">
                                <i class="fas fa-clock"></i>
                                <span class="info-label">Giờ:</span>
                                <span class="info-value"><?php echo date('H:i', strtotime($apt['gio_hen'])); ?></span>
                            </div>
                        </div>

                        <div class="payment-section">
                            <h4><i class="fas fa-credit-card"></i> Thanh toán</h4>
                            <?php if (!empty($apt['tt_thanh_toan'])): ?>
                                <div class="payment-status payment-<?php echo $apt['tt_thanh_toan']; ?>">
                                    <i class="fas fa-<?php echo $apt['tt_thanh_toan'] == 'thanh_cong' ? 'check-circle' : 'clock'; ?>"></i>
                                    <?php echo $apt['tt_thanh_toan'] == 'thanh_cong' ? 'Đã thanh toán' : 'Chưa thanh toán'; ?>
                                </div>
                                <div class="price"><?php echo number_format($apt['so_tien'] ?? $apt['gia']); ?>đ</div>
                            <?php else: ?>
                                <div class="payment-status payment-pending">
                                    <i class="fas fa-clock"></i> Chưa thanh toán
                                </div>
                                <div class="price"><?php echo number_format($apt['gia']); ?>đ</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="timeline">
                        <div class="timeline-title">Tiến trình</div>
                        <div class="timeline-item">
                            <div class="timeline-icon <?php echo $apt['trang_thai'] >= 0 ? 'active' : 'inactive'; ?>">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-label">Đặt lịch thành công</div>
                                <div class="timeline-time"><?php echo date('d/m/Y H:i', strtotime($apt['ngay_tao'])); ?></div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon <?php echo $apt['trang_thai'] >= 1 ? 'active' : 'inactive'; ?>">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-label">Đã xác nhận</div>
                                <div class="timeline-time"><?php echo $apt['trang_thai'] >= 1 ? 'Đã xác nhận' : 'Chờ xác nhận'; ?></div>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-icon <?php echo $apt['trang_thai'] >= 3 ? 'active' : 'inactive'; ?>">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-label">Hoàn thành</div>
                                <div class="timeline-time"><?php echo $apt['trang_thai'] >= 3 ? 'Đã hoàn thành' : 'Chưa hoàn thành'; ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="order-actions">
                        <?php if ($apt['trang_thai'] == 0): ?>
                            <button class="btn btn-danger" onclick="if(confirm('Bạn có chắc muốn hủy lịch hẹn này?')) window.location.href='cancel_appointment.php?id=<?php echo $apt['id']; ?>'">
                                <i class="fas fa-times"></i> Hủy lịch
                            </button>
                        <?php endif; ?>
                        <a href="dichvu.php" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Đặt lại
                        </a>
                        <?php if ($apt['trang_thai'] == 3 && empty($apt['tt_thanh_toan'])): ?>
                            <button class="btn btn-primary">
                                <i class="fas fa-credit-card"></i> Thanh toán
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Chưa có đơn hàng nào</h3>
                    <p>Bạn chưa có lịch hẹn nào. Hãy đặt lịch dịch vụ ngay!</p>
                    <a href="dichvu.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Đặt lịch ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterOrders(status) {
            // Update active tab
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.closest('.tab').classList.add('active');

            // Filter orders
            document.querySelectorAll('.order-card').forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
