<?php
session_start();
require_once '../config/connect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['ma'])) {
    header('Location: ../index.php');
    exit();
}

$ma_thanh_toan = $_GET['ma'];
$user_id = $_SESSION['user_id'];

// Lấy thông tin thanh toán
$stmt = $conn->prepare("SELECT tt.*, dl.ten_dich_vu, dl.ngay_dat_lich, dl.gio_dat_lich 
                        FROM thanh_toan tt
                        LEFT JOIN (
                            SELECT ddl.id, ddl.ngay_dat_lich, ddl.gio_dat_lich, dv.ten_dich_vu
                            FROM dat_lich_dich_vu ddl
                            JOIN dich_vu dv ON ddl.dich_vu_id = dv.id
                        ) dl ON tt.dat_lich_id = dl.id
                        WHERE tt.ma_thanh_toan = ? AND tt.nguoi_dung_id = ?");
$stmt->execute([$ma_thanh_toan, $user_id]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    die("Không tìm thấy thông tin thanh toán!");
}

// Xử lý xác nhận thanh toán (giả lập)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['xac_nhan'])) {
    try {
        $ma_giao_dich = 'GD' . date('YmdHis') . rand(100, 999);
        
        // Cập nhật trạng thái thanh toán
        $stmt = $conn->prepare("UPDATE thanh_toan SET trang_thai = 'thanh_cong', ma_giao_dich = ?, ngay_thanh_toan = NOW() WHERE id = ?");
        $stmt->execute([$ma_giao_dich, $payment['id']]);
        
        // Cập nhật trạng thái đặt lịch
        if ($payment['dat_lich_id']) {
            $stmt = $conn->prepare("UPDATE dat_lich_dich_vu SET trang_thai_thanh_toan = 'da_thanh_toan' WHERE id = ?");
            $stmt->execute([$payment['dat_lich_id']]);
        }
        
        $success = true;
    } catch (PDOException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .payment-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .payment-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .payment-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 40px;
        }

        h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .payment-method {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
            color: #667eea;
            font-size: 18px;
        }

        .payment-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #7f8c8d;
            font-weight: 500;
        }

        .info-value {
            color: #2c3e50;
            font-weight: 600;
        }

        .amount {
            text-align: center;
            margin: 30px 0;
        }

        .amount-label {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .amount-value {
            font-size: 48px;
            font-weight: 700;
            color: #e74c3c;
        }

        .qr-code {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .qr-placeholder {
            width: 200px;
            height: 200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #dee2e6;
            color: #95a5a6;
            font-size: 14px;
        }

        .btn-confirm {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            margin-top: 20px;
            transition: transform 0.2s;
        }

        .btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        .success-message {
            text-align: center;
            padding: 40px 20px;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 50px;
        }

        .btn-home {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            font-weight: 600;
        }

        .note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-size: 14px;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <?php if (isset($success) && $success): ?>
            <div class="success-message">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Thanh Toán Thành Công!</h1>
                <p style="color: #7f8c8d; margin: 20px 0;">Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.</p>
                <div class="payment-info">
                    <div class="info-row">
                        <span class="info-label">Mã thanh toán:</span>
                        <span class="info-value"><?php echo htmlspecialchars($ma_thanh_toan); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Số tiền:</span>
                        <span class="info-value"><?php echo number_format($payment['so_tien'], 0, ',', '.'); ?>₫</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phương thức:</span>
                        <span class="info-value"><?php echo strtoupper($payment['phuong_thuc']); ?></span>
                    </div>
                </div>
                <a href="../index.php" class="btn-home">
                    <i class="fas fa-home"></i> Về Trang Chủ
                </a>
            </div>
        <?php else: ?>
            <div class="payment-header">
                <div class="payment-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h1>Thanh Toán Online</h1>
                <p style="color: #7f8c8d;">Quét mã QR để thanh toán</p>
            </div>

            <div class="payment-method">
                <?php 
                    $methods = [
                        'momo' => 'MoMo',
                        'vnpay' => 'VNPay',
                        'zalopay' => 'ZaloPay'
                    ];
                    echo $methods[$payment['phuong_thuc']] ?? strtoupper($payment['phuong_thuc']);
                ?>
            </div>

            <div class="payment-info">
                <div class="info-row">
                    <span class="info-label">Mã thanh toán:</span>
                    <span class="info-value"><?php echo htmlspecialchars($ma_thanh_toan); ?></span>
                </div>
                <?php if ($payment['ten_dich_vu']): ?>
                <div class="info-row">
                    <span class="info-label">Dịch vụ:</span>
                    <span class="info-value"><?php echo htmlspecialchars($payment['ten_dich_vu']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày hẹn:</span>
                    <span class="info-value">
                        <?php echo date('d/m/Y', strtotime($payment['ngay_dat_lich'])); ?> 
                        - <?php echo date('H:i', strtotime($payment['gio_dat_lich'])); ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <div class="amount">
                <div class="amount-label">Số tiền thanh toán</div>
                <div class="amount-value"><?php echo number_format($payment['so_tien'], 0, ',', '.'); ?>₫</div>
            </div>

            <div class="qr-code">
                <div class="qr-placeholder">
                    <div>
                        <i class="fas fa-qrcode" style="font-size: 60px; color: #dee2e6; margin-bottom: 10px;"></i>
                        <p>Mã QR thanh toán</p>
                        <small>(Demo - Chưa tích hợp thực tế)</small>
                    </div>
                </div>
            </div>

            <form method="POST">
                <button type="submit" name="xac_nhan" class="btn-confirm">
                    <i class="fas fa-check-circle"></i> Xác Nhận Đã Thanh Toán
                </button>
            </form>

            <div class="note">
                <i class="fas fa-info-circle"></i> 
                <strong>Lưu ý:</strong> Đây là trang thanh toán demo. Trong thực tế sẽ tích hợp API thanh toán của MoMo/VNPay/ZaloPay.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
