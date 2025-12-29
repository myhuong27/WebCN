<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_update.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý đặt lịch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'dat_lich') {
    try {
        $ma_lich_kham = 'LK' . date('Ymd') . rand(1000, 9999);
        
        $stmt = $conn->prepare("INSERT INTO lich_kham (ma_lich_kham, nguoi_dung_id, thu_cung_id, ngay_kham, gio_kham, ly_do, trieu_chung, ghi_chu) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ma_lich_kham,
            $user_id,
            $_POST['thu_cung_id'],
            $_POST['ngay_kham'],
            $_POST['gio_kham'],
            $_POST['ly_do'],
            $_POST['trieu_chung'],
            $_POST['ghi_chu']
        ]);
        
        $success = "Đặt lịch khám thành công! Mã lịch: " . $ma_lich_kham;
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách thú cưng
$stmt = $conn->prepare("SELECT id, ten_thu_cung, loai_thu_cung FROM thu_cung WHERE chu_so_huu_id = ? AND trang_thai = 1");
$stmt->execute([$user_id]);
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy lịch khám của user
$stmt = $conn->prepare("SELECT lk.*, tc.ten_thu_cung, tc.loai_thu_cung, tc.hinh_anh
                       FROM lich_kham lk
                       LEFT JOIN thu_cung tc ON lk.thu_cung_id = tc.id
                       WHERE lk.nguoi_dung_id = ?
                       ORDER BY lk.ngay_kham DESC, lk.gio_kham DESC");
$stmt->execute([$user_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_text = [
    'cho_xac_nhan' => 'Chờ xác nhận',
    'da_xac_nhan' => 'Đã xác nhận',
    'dang_kham' => 'Đang khám',
    'hoan_thanh' => 'Hoàn thành',
    'da_huy' => 'Đã hủy'
];

$status_colors = [
    'cho_xac_nhan' => '#ffc107',
    'da_xac_nhan' => '#17a2b8',
    'dang_kham' => '#007bff',
    'hoan_thanh' => '#28a745',
    'da_huy' => '#dc3545'
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lịch Khám - Pet Care Center</title>
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
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }

        .header h1 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
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

        .form-group label .required {
            color: #e74c3c;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group select:focus,
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

        .appointments-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .appointments-section h2 {
            color: #333;
            margin-bottom: 25px;
        }

        .appointment-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }

        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .appointment-code {
            font-weight: 600;
            color: #667eea;
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            color: white;
        }

        .appointment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .detail-icon {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
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

        .btn-back {
            display: inline-block;
            padding: 10px 20px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Quay lại Trang chủ
        </a>

        <div class="header">
            <h1><i class="fas fa-stethoscope"></i> Đặt Lịch Khám Bệnh</h1>
            <p>Đặt lịch hẹn khám bệnh cho thú cưng của bạn</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert error"><i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h2 style="margin-bottom: 25px; color: #333;"><i class="fas fa-calendar-plus"></i> Đặt Lịch Mới</h2>
            <form method="POST">
                <input type="hidden" name="action" value="dat_lich">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Chọn thú cưng <span class="required">*</span></label>
                        <select name="thu_cung_id" required>
                            <option value="">-- Chọn thú cưng --</option>
                            <?php foreach ($pets as $pet): ?>
                                <option value="<?php echo $pet['id']; ?>">
                                    <?php echo htmlspecialchars($pet['ten_thu_cung']); ?> (<?php echo htmlspecialchars($pet['loai_thu_cung']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ngày khám <span class="required">*</span></label>
                        <input type="date" name="ngay_kham" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Giờ khám <span class="required">*</span></label>
                        <input type="time" name="gio_kham" required value="09:00">
                    </div>

                    <div class="form-group">
                        <label>Lý do khám <span class="required">*</span></label>
                        <select name="ly_do" required>
                            <option value="">-- Chọn lý do --</option>
                            <option value="Khám định kỳ">Khám định kỳ</option>
                            <option value="Tiêm phòng">Tiêm phòng</option>
                            <option value="Ốm đau">Ốm đau</option>
                            <option value="Tai nạn">Tai nạn</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Triệu chứng</label>
                    <textarea name="trieu_chung" placeholder="Mô tả triệu chứng hoặc tình trạng của thú cưng..."></textarea>
                </div>

                <div class="form-group">
                    <label>Ghi chú</label>
                    <textarea name="ghi_chu" placeholder="Ghi chú thêm..."></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-calendar-check"></i> Đặt Lịch Khám
                </button>
            </form>
        </div>

        <div class="appointments-section">
            <h2><i class="fas fa-history"></i> Lịch Sử Khám Bệnh</h2>
            <?php if (count($appointments) > 0): ?>
                <?php foreach ($appointments as $appt): ?>
                    <div class="appointment-card">
                        <div class="appointment-header">
                            <span class="appointment-code">
                                <i class="fas fa-ticket-alt"></i> <?php echo htmlspecialchars($appt['ma_lich_kham']); ?>
                            </span>
                            <span class="status-badge" style="background: <?php echo $status_colors[$appt['trang_thai']]; ?>;">
                                <?php echo $status_text[$appt['trang_thai']]; ?>
                            </span>
                        </div>

                        <div class="appointment-details">
                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-paw"></i></div>
                                <div>
                                    <small style="color: #999;">Thú cưng</small><br>
                                    <strong><?php echo htmlspecialchars($appt['ten_thu_cung']); ?></strong>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-calendar"></i></div>
                                <div>
                                    <small style="color: #999;">Ngày khám</small><br>
                                    <strong><?php echo date('d/m/Y', strtotime($appt['ngay_kham'])); ?></strong>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-clock"></i></div>
                                <div>
                                    <small style="color: #999;">Giờ khám</small><br>
                                    <strong><?php echo date('H:i', strtotime($appt['gio_kham'])); ?></strong>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-icon"><i class="fas fa-notes-medical"></i></div>
                                <div>
                                    <small style="color: #999;">Lý do</small><br>
                                    <strong><?php echo htmlspecialchars($appt['ly_do']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <?php if ($appt['chan_doan']): ?>
                            <div style="margin-top: 15px; padding: 15px; background: white; border-radius: 8px;">
                                <strong style="color: #667eea;"><i class="fas fa-diagnoses"></i> Chẩn đoán:</strong>
                                <p><?php echo nl2br(htmlspecialchars($appt['chan_doan'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($appt['don_thuoc']): ?>
                            <div style="margin-top: 10px; padding: 15px; background: white; border-radius: 8px;">
                                <strong style="color: #28a745;"><i class="fas fa-prescription"></i> Đơn thuốc:</strong>
                                <p><?php echo nl2br(htmlspecialchars($appt['don_thuoc'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($appt['chi_phi']): ?>
                            <div style="margin-top: 10px; text-align: right;">
                                <strong style="color: #e74c3c; font-size: 18px;">
                                    <i class="fas fa-money-bill-wave"></i> Chi phí: <?php echo number_format($appt['chi_phi'], 0, ',', '.'); ?> VNĐ
                                </strong>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 40px;">Chưa có lịch khám nào</p>
            <?php endif; ?>
        </div>
    </div>
<?php require_once '../includes/chat_widget.php'; ?>
</body>
</html>
