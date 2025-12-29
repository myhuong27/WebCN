<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_update.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý thêm lịch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'them') {
    try {
        $stmt = $conn->prepare("INSERT INTO lich_cham_soc (thu_cung_id, nguoi_dung_id, loai_lich, tieu_de, mo_ta, ngay_thuc_hien, gio_thuc_hien, lap_lai, nhac_truoc, ghi_chu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['thu_cung_id'],
            $user_id,
            $_POST['loai_lich'],
            $_POST['tieu_de'],
            $_POST['mo_ta'],
            $_POST['ngay_thuc_hien'],
            $_POST['gio_thuc_hien'],
            $_POST['lap_lai'],
            $_POST['nhac_truoc'],
            $_POST['ghi_chu']
        ]);
        $success = "Đã thêm lịch thành công!";
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cap_nhat') {
    try {
        $stmt = $conn->prepare("UPDATE lich_cham_soc SET trang_thai = ? WHERE id = ? AND nguoi_dung_id = ?");
        $stmt->execute([$_POST['trang_thai'], $_POST['lich_id'], $user_id]);
        $success = "Đã cập nhật trạng thái!";
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách thú cưng (của user hoặc không có chủ)
$stmt = $conn->prepare("SELECT id, ten_thu_cung FROM thu_cung WHERE (chu_so_huu_id = ? OR chu_so_huu_id IS NULL) ORDER BY ten_thu_cung ASC");
$stmt->execute([$user_id]);
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách lịch
$stmt = $conn->prepare("SELECT lcs.*, tc.ten_thu_cung 
                        FROM lich_cham_soc lcs
                        JOIN thu_cung tc ON lcs.thu_cung_id = tc.id
                        WHERE lcs.nguoi_dung_id = ?
                        ORDER BY lcs.ngay_thuc_hien ASC, lcs.gio_thuc_hien ASC");
$stmt->execute([$user_id]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Chăm Sóc - Pet Care Center</title>
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
            background: #f5f6fa;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 30px;
        }

        .btn-add {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 30px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
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

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .schedule-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-left: 4px solid #3498db;
        }

        .schedule-card.tiem_phong { border-left-color: #e74c3c; }
        .schedule-card.tam { border-left-color: #3498db; }
        .schedule-card.cho_an { border-left-color: #f39c12; }
        .schedule-card.kham_suc_khoe { border-left-color: #2ecc71; }

        .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .schedule-type {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }

        .schedule-type.tiem_phong { background: #e74c3c; }
        .schedule-type.tam { background: #3498db; }
        .schedule-type.cho_an { background: #f39c12; }
        .schedule-type.kham_suc_khoe { background: #2ecc71; }
        .schedule-type.khac { background: #95a5a6; }

        .schedule-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 10px 0;
        }

        .schedule-info {
            color: #7f8c8d;
            font-size: 14px;
            margin: 5px 0;
        }

        .schedule-info i {
            width: 20px;
            color: #3498db;
        }

        .schedule-status {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #ecf0f1;
        }

        .status-select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
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

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-calendar-alt"></i> Lịch Chăm Sóc Thú Cưng</h1>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <button class="btn-add" onclick="document.getElementById('modalAdd').classList.add('show')">
            <i class="fas fa-plus"></i> Thêm Lịch Mới
        </button>
        <a href="../index.php" class="btn-add" style="background: #95a5a6; margin-left: 10px;">
            <i class="fas fa-home"></i> Về Trang Chủ
        </a>

        <div class="calendar-grid">
            <?php foreach ($schedules as $schedule): 
                $loai_lich_text = [
                    'tiem_phong' => 'Tiêm Phòng',
                    'tam' => 'Tắm Rửa',
                    'cho_an' => 'Cho Ăn',
                    'kham_suc_khoe' => 'Khám Sức Khỏe',
                    'khac' => 'Khác'
                ];
            ?>
            <div class="schedule-card <?php echo $schedule['loai_lich']; ?>">
                <div class="schedule-header">
                    <span class="schedule-type <?php echo $schedule['loai_lich']; ?>">
                        <?php echo $loai_lich_text[$schedule['loai_lich']] ?? 'Khác'; ?>
                    </span>
                </div>
                <h3 class="schedule-title"><?php echo htmlspecialchars($schedule['tieu_de']); ?></h3>
                <div class="schedule-info">
                    <i class="fas fa-paw"></i> <?php echo htmlspecialchars($schedule['ten_thu_cung']); ?>
                </div>
                <div class="schedule-info">
                    <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($schedule['ngay_thuc_hien'])); ?>
                    <?php if ($schedule['gio_thuc_hien']): ?>
                        - <?php echo date('H:i', strtotime($schedule['gio_thuc_hien'])); ?>
                    <?php endif; ?>
                </div>
                <?php if ($schedule['mo_ta']): ?>
                <div class="schedule-info">
                    <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($schedule['mo_ta']); ?>
                </div>
                <?php endif; ?>
                <?php if ($schedule['lap_lai'] != 'khong'): ?>
                <div class="schedule-info">
                    <i class="fas fa-redo"></i> Lặp lại: <?php echo str_replace('_', ' ', ucfirst($schedule['lap_lai'])); ?>
                </div>
                <?php endif; ?>
                <div class="schedule-status">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="cap_nhat">
                        <input type="hidden" name="lich_id" value="<?php echo $schedule['id']; ?>">
                        <select name="trang_thai" class="status-select" onchange="this.form.submit()">
                            <option value="cho_thuc_hien" <?php echo $schedule['trang_thai'] == 'cho_thuc_hien' ? 'selected' : ''; ?>>Chờ thực hiện</option>
                            <option value="hoan_thanh" <?php echo $schedule['trang_thai'] == 'hoan_thanh' ? 'selected' : ''; ?>>Hoàn thành</option>
                            <option value="bo_qua" <?php echo $schedule['trang_thai'] == 'bo_qua' ? 'selected' : ''; ?>>Bỏ qua</option>
                        </select>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($schedules)): ?>
            <div style="text-align: center; padding: 60px 20px; color: #95a5a6;">
                <i class="fas fa-calendar-times" style="font-size: 80px; margin-bottom: 20px;"></i>
                <h3>Chưa có lịch nào</h3>
                <p>Hãy thêm lịch chăm sóc cho thú cưng của bạn</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Thêm Lịch -->
    <div id="modalAdd" class="modal">
        <div class="modal-content">
            <h2>Thêm Lịch Chăm Sóc</h2>
            <form method="POST">
                <input type="hidden" name="action" value="them">
                
                <div class="form-group">
                    <label>Thú cưng *</label>
                    <select name="thu_cung_id" required>
                        <option value="">-- Chọn thú cưng --</option>
                        <?php foreach ($pets as $pet): ?>
                            <option value="<?php echo $pet['id']; ?>"><?php echo htmlspecialchars($pet['ten_thu_cung']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Loại lịch *</label>
                    <select name="loai_lich" required>
                        <option value="tiem_phong">Tiêm phòng</option>
                        <option value="tam">Tắm rửa</option>
                        <option value="cho_an">Cho ăn</option>
                        <option value="kham_suc_khoe">Khám sức khỏe</option>
                        <option value="khac">Khác</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tiêu đề *</label>
                    <input type="text" name="tieu_de" required placeholder="Vd: Tiêm phòng dại lần 2">
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="mo_ta" placeholder="Mô tả chi tiết về lịch này..."></textarea>
                </div>

                <div class="form-group">
                    <label>Ngày thực hiện *</label>
                    <input type="date" name="ngay_thuc_hien" required>
                </div>

                <div class="form-group">
                    <label>Giờ thực hiện</label>
                    <input type="time" name="gio_thuc_hien">
                </div>

                <div class="form-group">
                    <label>Lặp lại</label>
                    <select name="lap_lai">
                        <option value="khong">Không lặp lại</option>
                        <option value="hang_ngay">Hàng ngày</option>
                        <option value="hang_tuan">Hàng tuần</option>
                        <option value="hang_thang">Hàng tháng</option>
                        <option value="hang_nam">Hàng năm</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nhắc trước (số ngày)</label>
                    <input type="number" name="nhac_truoc" value="1" min="0" max="30">
                </div>

                <div class="form-group">
                    <label>Ghi chú</label>
                    <textarea name="ghi_chu" placeholder="Ghi chú thêm..."></textarea>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Thêm Lịch</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalAdd').classList.remove('show')">Hủy</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Đóng modal khi click bên ngoài
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
