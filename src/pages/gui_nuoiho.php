<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_update.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Xử lý gửi yêu cầu nuôi hộ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'send_request') {
    try {
        $thu_cung_id = $_POST['thu_cung_id'];
        $ngay_bat_dau = $_POST['ngay_bat_dau'];
        $ngay_ket_thuc = $_POST['ngay_ket_thuc'];
        $dia_diem = $_POST['dia_diem'];
        $yeu_cau_dac_biet = $_POST['yeu_cau_dac_biet'];
        $gia_tien = $_POST['gia_tien'];
        
        // Tạo mã yêu cầu tự động
        $stmt = $conn->query("SELECT COUNT(*) as total FROM yeu_cau_nuoi_ho");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] + 1;
        $ma_yeu_cau = 'YC' . str_pad($count, 3, '0', STR_PAD_LEFT);
        
        $stmt = $conn->prepare("INSERT INTO yeu_cau_nuoi_ho 
                               (ma_yeu_cau, nguoi_gui_id, thu_cung_id, ngay_bat_dau, ngay_ket_thuc, dia_diem, yeu_cau_dac_biet, gia_tien, trang_thai) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$ma_yeu_cau, $user_id, $thu_cung_id, $ngay_bat_dau, $ngay_ket_thuc, $dia_diem, $yeu_cau_dac_biet, $gia_tien]);
        
        $message = '<div class="alert success">Gửi yêu cầu nuôi hộ thành công! Admin sẽ duyệt yêu cầu của bạn sớm nhất.</div>';
    } catch(PDOException $e) {
        $message = '<div class="alert error">Lỗi: ' . $e->getMessage() . '</div>';
    }
}

// Lấy danh sách thú cưng của người dùng
try {
    $stmt = $conn->prepare("SELECT * FROM thu_cung WHERE chu_so_huu_id = ? AND trang_thai = 1");
    $stmt->execute([$user_id]);
    $my_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy các yêu cầu đã gửi
    $stmt = $conn->prepare("SELECT yc.*, tc.ten_thu_cung, tc.loai_thu_cung, tc.hinh_anh
                           FROM yeu_cau_nuoi_ho yc
                           LEFT JOIN thu_cung tc ON yc.thu_cung_id = tc.id
                           WHERE yc.nguoi_gui_id = ?
                           ORDER BY yc.ngay_tao DESC");
    $stmt->execute([$user_id]);
    $my_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

$status_text = [
    0 => 'Chờ duyệt',
    1 => 'Đã duyệt',
    2 => 'Đang nuôi',
    3 => 'Hoàn thành',
    4 => 'Từ chối',
    5 => 'Hủy'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gửi Yêu Cầu Nuôi Hộ - Pet Care Center</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 18px;
            opacity: 0.9;
        }

        .back-link {
            display: inline-block;
            color: white;
            text-decoration: none;
            margin-top: 15px;
            opacity: 0.9;
            transition: opacity 0.3s;
        }

        .back-link:hover {
            opacity: 1;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
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

        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .section h2 {
            color: #333;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
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
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .requests-list {
            display: grid;
            gap: 20px;
        }

        .request-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .request-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }

        .request-info h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .request-info p {
            color: #666;
            font-size: 14px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.approved {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .request-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .detail-item {
            font-size: 14px;
        }

        .detail-item strong {
            color: #667eea;
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-heart"></i> Gửi Yêu Cầu Nuôi Hộ</h1>
            <p>Gửi thú cưng của bạn đến dịch vụ nuôi hộ chuyên nghiệp</p>
            <a href="../index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Về trang chủ
            </a>
        </div>

        <?php if ($message) echo $message; ?>

        <!-- Form gửi yêu cầu -->
        <div class="section">
            <h2><i class="fas fa-paper-plane"></i> Gửi Yêu Cầu Mới</h2>
            
            <?php if (empty($my_pets)): ?>
                <div class="alert error">
                    <p><strong>Lưu ý:</strong> Bạn chưa có thú cưng nào. Vui lòng thêm thú cưng trước khi gửi yêu cầu nuôi hộ.</p>
                    <a href="../user/quan_ly_thucung_user.php" style="color: #721c24; text-decoration: underline;">
                        <i class="fas fa-plus"></i> Thêm thú cưng ngay
                    </a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="action" value="send_request">
                    
                    <div class="form-group">
                        <label><i class="fas fa-paw"></i> Chọn thú cưng *</label>
                        <select name="thu_cung_id" required>
                            <option value="">-- Chọn thú cưng --</option>
                            <?php foreach ($my_pets as $pet): ?>
                                <option value="<?php echo $pet['id']; ?>">
                                    <?php echo htmlspecialchars($pet['ten_thu_cung']); ?> - 
                                    <?php echo htmlspecialchars($pet['loai_thu_cung']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar-start"></i> Ngày bắt đầu *</label>
                        <input type="date" name="ngay_bat_dau" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-calendar-end"></i> Ngày kết thúc *</label>
                        <input type="date" name="ngay_ket_thuc" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Địa điểm nhận/trả thú cưng</label>
                        <input type="text" name="dia_diem" placeholder="VD: Quận 1, TP.HCM">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-comment"></i> Yêu cầu đặc biệt (nếu có)</label>
                        <textarea name="yeu_cau_dac_biet" placeholder="VD: Cho ăn 3 bữa/ngày, dắt đi dạo 2 lần/ngày..."></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> Giá tiền (VNĐ/ngày) *</label>
                        <input type="number" name="gia_tien" required min="0" step="10000" placeholder="VD: 200000">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi yêu cầu
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <!-- Danh sách yêu cầu đã gửi -->
        <div class="section">
            <h2><i class="fas fa-list"></i> Yêu Cầu Đã Gửi</h2>
            
            <?php if (empty($my_requests)): ?>
                <p style="text-align: center; color: #666; padding: 30px 0;">
                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                    Bạn chưa gửi yêu cầu nào.
                </p>
            <?php else: ?>
                <div class="requests-list">
                    <?php foreach ($my_requests as $req): ?>
                        <div class="request-item">
                            <div class="request-header">
                                <div class="request-info">
                                    <h3><?php echo htmlspecialchars($req['ten_thu_cung']); ?></h3>
                                    <p><?php echo htmlspecialchars($req['loai_thu_cung']); ?> - Mã: <?php echo $req['ma_yeu_cau']; ?></p>
                                </div>
                                <span class="status-badge <?php 
                                    echo $req['trang_thai'] == 0 ? 'pending' : 
                                        ($req['trang_thai'] == 4 ? 'rejected' : 'approved'); 
                                ?>">
                                    <?php echo $status_text[$req['trang_thai']]; ?>
                                </span>
                            </div>
                            
                            <div class="request-details">
                                <div class="detail-item">
                                    <strong>Thời gian:</strong>
                                    <?php echo date('d/m/Y', strtotime($req['ngay_bat_dau'])); ?> - 
                                    <?php echo date('d/m/Y', strtotime($req['ngay_ket_thuc'])); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Địa điểm:</strong>
                                    <?php echo htmlspecialchars($req['dia_diem'] ?? 'Chưa xác định'); ?>
                                </div>
                                <div class="detail-item">
                                    <strong>Giá tiền:</strong>
                                    <?php echo number_format($req['gia_tien'], 0, ',', '.'); ?>đ/ngày
                                </div>
                            </div>
                            
                            <?php if ($req['yeu_cau_dac_biet']): ?>
                                <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                                    <strong>Yêu cầu đặc biệt:</strong>
                                    <p style="margin-top: 5px;"><?php echo nl2br(htmlspecialchars($req['yeu_cau_dac_biet'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php require_once '../includes/chat_widget.php'; ?>
</body>
</html>
