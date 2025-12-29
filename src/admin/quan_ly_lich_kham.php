<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 2) {
    header('Location: ../index.php');
    exit();
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'cap_nhat_trang_thai') {
            $stmt = $conn->prepare("UPDATE lich_kham SET trang_thai = ? WHERE id = ?");
            $stmt->execute([$_POST['trang_thai'], $_POST['lich_id']]);
            $success = "Đã cập nhật trạng thái!";
        } elseif ($_POST['action'] === 'cap_nhat_chi_tiet') {
            $stmt = $conn->prepare("UPDATE lich_kham SET chan_doan = ?, don_thuoc = ?, chi_phi = ? WHERE id = ?");
            $stmt->execute([
                $_POST['chan_doan'],
                $_POST['don_thuoc'],
                $_POST['chi_phi'],
                $_POST['lich_id']
            ]);
            $success = "Đã cập nhật thông tin khám bệnh!";
        }
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách lịch khám
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT lk.*, tc.ten_thu_cung, tc.loai_thu_cung, tc.hinh_anh, nd.ho_ten
        FROM lich_kham lk
        LEFT JOIN thu_cung tc ON lk.thu_cung_id = tc.id
        LEFT JOIN nguoi_dung nd ON lk.nguoi_dung_id = nd.id";

if ($filter !== 'all') {
    $sql .= " WHERE lk.trang_thai = :trang_thai";
}

$sql .= " ORDER BY lk.ngay_kham DESC, lk.gio_kham DESC";

$stmt = $conn->prepare($sql);
if ($filter !== 'all') {
    $stmt->bindParam(':trang_thai', $filter);
}
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Thống kê
$stats = [
    'total' => count($appointments),
    'cho_xac_nhan' => 0,
    'da_xac_nhan' => 0,
    'dang_kham' => 0,
    'hoan_thanh' => 0
];

$stmt = $conn->query("SELECT trang_thai, COUNT(*) as count FROM lich_kham GROUP BY trang_thai");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $stats[$row['trang_thai']] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Lịch Khám - Admin</title>
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
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
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

        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .filter-btn {
            padding: 8px 15px;
            margin-right: 10px;
            background: #f0f0f0;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .appointments-grid {
            display: grid;
            gap: 20px;
        }

        .appointment-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }

        .appointment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .appointment-body {
            padding: 25px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #ff6b9d, #ffa07a);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .action-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-submit {
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-status {
            padding: 8px 15px;
            margin: 5px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            color: white;
        }

        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
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
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-stethoscope"></i> Quản lý Lịch Khám</h1>
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
            <div class="stat-card" onclick="window.location.href='?filter=all'">
                <h3>Tổng lịch khám</h3>
                <div class="number"><?php echo $stats['total']; ?></div>
            </div>
            <div class="stat-card" onclick="window.location.href='?filter=cho_xac_nhan'">
                <h3>Chờ xác nhận</h3>
                <div class="number" style="color: #ffc107;"><?php echo $stats['cho_xac_nhan']; ?></div>
            </div>
            <div class="stat-card" onclick="window.location.href='?filter=da_xac_nhan'">
                <h3>Đã xác nhận</h3>
                <div class="number" style="color: #17a2b8;"><?php echo $stats['da_xac_nhan']; ?></div>
            </div>
            <div class="stat-card" onclick="window.location.href='?filter=hoan_thanh'">
                <h3>Hoàn thành</h3>
                <div class="number" style="color: #28a745;"><?php echo $stats['hoan_thanh']; ?></div>
            </div>
        </div>

        <div class="appointments-grid">
            <?php foreach ($appointments as $appt): 
                $status_colors = [
                    'cho_xac_nhan' => '#ffc107',
                    'da_xac_nhan' => '#17a2b8',
                    'dang_kham' => '#007bff',
                    'hoan_thanh' => '#28a745',
                    'da_huy' => '#dc3545'
                ];
                $status_text = [
                    'cho_xac_nhan' => 'Chờ xác nhận',
                    'da_xac_nhan' => 'Đã xác nhận',
                    'dang_kham' => 'Đang khám',
                    'hoan_thanh' => 'Hoàn thành',
                    'da_huy' => 'Đã hủy'
                ];
            ?>
            <div class="appointment-card">
                <div class="appointment-header">
                    <div>
                        <div style="font-size: 18px; font-weight: 600;">
                            <i class="fas fa-ticket-alt"></i> <?php echo htmlspecialchars($appt['ma_lich_kham']); ?>
                        </div>
                        <div style="font-size: 13px; opacity: 0.9; margin-top: 5px;">
                            Khách hàng: <?php echo htmlspecialchars($appt['ho_ten'] ?? 'Chưa có tên'); ?>
                        </div>
                    </div>
                    <span class="status-badge" style="background: <?php echo $status_colors[$appt['trang_thai']]; ?>;">
                        <?php echo $status_text[$appt['trang_thai']]; ?>
                    </span>
                </div>

                <div class="appointment-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-paw"></i></div>
                            <div>
                                <small style="color: #999;">Thú cưng</small><br>
                                <strong><?php echo htmlspecialchars($appt['ten_thu_cung']); ?></strong>
                                <small>(<?php echo htmlspecialchars($appt['loai_thu_cung']); ?>)</small>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-calendar"></i></div>
                            <div>
                                <small style="color: #999;">Ngày khám</small><br>
                                <strong><?php echo date('d/m/Y', strtotime($appt['ngay_kham'])); ?></strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-clock"></i></div>
                            <div>
                                <small style="color: #999;">Giờ khám</small><br>
                                <strong><?php echo date('H:i', strtotime($appt['gio_kham'])); ?></strong>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-icon"><i class="fas fa-notes-medical"></i></div>
                            <div>
                                <small style="color: #999;">Lý do</small><br>
                                <strong><?php echo htmlspecialchars($appt['ly_do']); ?></strong>
                            </div>
                        </div>
                    </div>

                    <?php if ($appt['trieu_chung']): ?>
                        <div style="margin: 15px 0; padding: 15px; background: #fff3cd; border-radius: 8px;">
                            <strong><i class="fas fa-heartbeat"></i> Triệu chứng:</strong>
                            <p><?php echo nl2br(htmlspecialchars($appt['trieu_chung'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Cập nhật trạng thái -->
                    <div class="action-section">
                        <strong style="color: #667eea;"><i class="fas fa-tasks"></i> Quản lý trạng thái:</strong>
                        <form method="POST" style="margin-top: 10px;">
                            <input type="hidden" name="action" value="cap_nhat_trang_thai">
                            <input type="hidden" name="lich_id" value="<?php echo $appt['id']; ?>">
                            
                            <button type="submit" name="trang_thai" value="da_xac_nhan" class="btn-status" style="background: #17a2b8;">
                                Xác nhận
                            </button>
                            <button type="submit" name="trang_thai" value="dang_kham" class="btn-status" style="background: #007bff;">
                                Đang khám
                            </button>
                            <button type="submit" name="trang_thai" value="hoan_thanh" class="btn-status" style="background: #28a745;">
                                Hoàn thành
                            </button>
                            <button type="submit" name="trang_thai" value="da_huy" class="btn-status" style="background: #dc3545;">
                                Hủy lịch
                            </button>
                        </form>
                    </div>

                    <!-- Form cập nhật chi tiết -->
                    <div class="action-section" style="margin-top: 15px;">
                        <strong style="color: #667eea;"><i class="fas fa-file-medical"></i> Kết quả khám:</strong>
                        <form method="POST" style="margin-top: 15px;">
                            <input type="hidden" name="action" value="cap_nhat_chi_tiet">
                            <input type="hidden" name="lich_id" value="<?php echo $appt['id']; ?>">
                            
                            <div class="form-group">
                                <label>Chẩn đoán:</label>
                                <textarea name="chan_doan"><?php echo htmlspecialchars($appt['chan_doan'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Đơn thuốc:</label>
                                <textarea name="don_thuoc"><?php echo htmlspecialchars($appt['don_thuoc'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Chi phí (VNĐ):</label>
                                <input type="number" name="chi_phi" value="<?php echo $appt['chi_phi'] ?? ''; ?>" step="1000">
                            </div>

                            <button type="submit" class="btn-submit">
                                <i class="fas fa-save"></i> Lưu kết quả
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if (count($appointments) == 0): ?>
                <div style="text-align: center; padding: 60px; background: white; border-radius: 15px;">
                    <i class="fas fa-calendar-times" style="font-size: 80px; color: #ddd;"></i>
                    <h3 style="margin-top: 20px; color: #666;">Chưa có lịch khám nào</h3>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
