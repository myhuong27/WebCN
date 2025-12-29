<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_update.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pet_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy thông tin thú cưng
try {
    $stmt = $conn->prepare("SELECT tc.*, nd.ho_ten as chu_so_huu
                           FROM thu_cung tc
                           LEFT JOIN nguoi_dung nd ON tc.chu_so_huu_id = nd.id
                           WHERE tc.id = ?");
    $stmt->execute([$pet_id]);
    $pet = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pet) {
        die("Không tìm thấy thú cưng!");
    }
    
    // Lịch tiêm phòng
    $stmt = $conn->prepare("SELECT * FROM lich_tiem_phong WHERE thu_cung_id = ? ORDER BY ngay_tiem DESC");
    $stmt->execute([$pet_id]);
    $vaccinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Chế độ ăn uống
    $stmt = $conn->prepare("SELECT * FROM che_do_an_uong WHERE thu_cung_id = ? ORDER BY ngay_tao DESC");
    $stmt->execute([$pet_id]);
    $feeding_schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Nhật ký sức khỏe
    $stmt = $conn->prepare("SELECT * FROM nhat_ky_suc_khoe WHERE thu_cung_id = ? ORDER BY ngay_ghi DESC");
    $stmt->execute([$pet_id]);
    $health_diary = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lịch hẹn
    $stmt = $conn->prepare("SELECT lh.*, dv.ten_dich_vu 
                           FROM lich_hen lh
                           LEFT JOIN dich_vu dv ON lh.dich_vu_id = dv.id
                           WHERE lh.thu_cung_id = ? 
                           ORDER BY lh.ngay_hen DESC");
    $stmt->execute([$pet_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Đánh giá
    $stmt = $conn->prepare("SELECT dg.*, nd.ho_ten
                           FROM danh_gia dg
                           LEFT JOIN nguoi_dung nd ON dg.nguoi_dung_id = nd.id
                           WHERE dg.thu_cung_id = ? 
                           ORDER BY dg.ngay_danh_gia DESC");
    $stmt->execute([$pet_id]);
    $ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ: <?php echo htmlspecialchars($pet['ten_thu_cung']); ?> - Pet Care Center</title>
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
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .back-btn {
            padding: 10px 20px;
            background: #f0f0f0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }

        .back-btn:hover {
            background: #e0e0e0;
        }

        .header h1 {
            flex: 1;
            color: #333;
        }

        .pet-overview {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .pet-avatar {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .pet-avatar img {
            width: 250px;
            height: 250px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
            border: 5px solid #667eea;
        }

        .pet-avatar h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .pet-avatar p {
            color: #666;
            margin-bottom: 5px;
        }

        .pet-info-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-item label {
            display: block;
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .info-item .value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .tab-btn {
            padding: 12px 25px;
            border: none;
            background: #f0f0f0;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
            transition: all 0.3s;
        }

        .tab-btn:hover {
            background: #e0e0e0;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card h3 {
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding: 20px;
            border-left: 2px solid #667eea;
            margin-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 20px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #667eea;
        }

        .timeline-date {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .status-badge.active {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .rating {
            color: #ffc107;
        }

        .empty-state {
            text-align: center;
            padding: 50px;
            color: #999;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <h1><i class="fas fa-paw"></i> Hồ Sơ Thú Cưng Chi Tiết</h1>
        </div>

        <!-- Pet Overview -->
        <div class="pet-overview">
            <div class="pet-avatar">
                <img src="<?php echo htmlspecialchars($pet['hinh_anh'] ?? 'https://via.placeholder.com/250'); ?>" 
                     alt="<?php echo htmlspecialchars($pet['ten_thu_cung']); ?>">
                <h2><?php echo htmlspecialchars($pet['ten_thu_cung']); ?></h2>
                <p><strong><?php echo htmlspecialchars($pet['giong_loai']); ?></strong></p>
                <p><?php echo htmlspecialchars($pet['loai_thu_cung']); ?> • <?php echo ($pet['gioi_tinh'] == 0 ? 'Đực' : 'Cái'); ?></p>
                <p><i class="fas fa-user"></i> Chủ: <?php echo htmlspecialchars($pet['chu_so_huu']); ?></p>
                <br>
                <?php if ($pet['trang_thai'] == 1): ?>
                    <span class="status-badge active">Đang được chăm sóc</span>
                <?php else: ?>
                    <span class="status-badge inactive">Ngừng chăm sóc</span>
                <?php endif; ?>
            </div>

            <div class="pet-info-card">
                <h3><i class="fas fa-info-circle"></i> Thông Tin Cơ Bản</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Tuổi</label>
                        <div class="value"><?php echo htmlspecialchars($pet['tuoi']); ?> tuổi</div>
                    </div>
                    <div class="info-item">
                        <label>Cân nặng</label>
                        <div class="value"><?php echo htmlspecialchars($pet['can_nang']); ?> kg</div>
                    </div>
                    <div class="info-item">
                        <label>Màu sắc</label>
                        <div class="value"><?php echo htmlspecialchars($pet['mau_sac']); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Tình trạng sức khỏe</label>
                        <div class="value"><?php echo htmlspecialchars($pet['tinh_trang_suc_khoe']); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Ngày bắt đầu chăm sóc</label>
                        <div class="value"><?php echo date('d/m/Y', strtotime($pet['ngay_bat_dau_cham_soc'])); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Ghi chú đặc biệt</label>
                        <div class="value"><?php echo htmlspecialchars($pet['ghi_chu'] ?? 'Không có'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('vaccinations')">
                <i class="fas fa-syringe"></i> Lịch tiêm phòng
            </button>
            <button class="tab-btn" onclick="showTab('feeding')">
                <i class="fas fa-utensils"></i> Chế độ ăn uống
            </button>
            <button class="tab-btn" onclick="showTab('health')">
                <i class="fas fa-heartbeat"></i> Nhật ký sức khỏe
            </button>
            <button class="tab-btn" onclick="showTab('appointments')">
                <i class="fas fa-calendar-alt"></i> Lịch hẹn
            </button>
            <button class="tab-btn" onclick="showTab('ratings')">
                <i class="fas fa-star"></i> Đánh giá
            </button>
        </div>

        <!-- Vaccination Tab -->
        <div id="vaccinations" class="tab-content active">
            <div class="card">
                <h3><i class="fas fa-syringe"></i> Lịch Sử Tiêm Phòng</h3>
                <?php if (!empty($vaccinations)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Loại vắc xin</th>
                                <th>Ngày tiêm</th>
                                <th>Ngày nhắc tiêm lại</th>
                                <th>Nơi tiêm</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vaccinations as $vac): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($vac['loai_vac_xin']); ?></strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($vac['ngay_tiem'])); ?></td>
                                    <td><?php echo $vac['ngay_nhac_lai'] ? date('d/m/Y', strtotime($vac['ngay_nhac_lai'])) : 'Không'; ?></td>
                                    <td><?php echo htmlspecialchars($vac['noi_tiem']); ?></td>
                                    <td><?php echo htmlspecialchars($vac['ghi_chu'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-syringe"></i>
                        <p>Chưa có lịch sử tiêm phòng</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Feeding Tab -->
        <div id="feeding" class="tab-content">
            <div class="card">
                <h3><i class="fas fa-utensils"></i> Chế Độ Ăn Uống</h3>
                <?php if (!empty($feeding_schedule)): ?>
                    <div class="timeline">
                        <?php foreach ($feeding_schedule as $feed): ?>
                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <i class="fas fa-calendar"></i> 
                                    Từ <?php echo date('d/m/Y', strtotime($feed['ngay_tao'])); ?>
                                </div>
                                <div class="timeline-content">
                                    <p><strong>Loại thức ăn:</strong> <?php echo htmlspecialchars($feed['loai_thuc_an']); ?></p>
                                    <p><strong>Khẩu phần:</strong> <?php echo htmlspecialchars($feed['khau_phan']); ?></p>
                                    <p><strong>Số bữa/ngày:</strong> <?php echo htmlspecialchars($feed['so_bua_ngay']); ?> bữa</p>
                                    <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($feed['ghi_chu'] ?? 'Không có'); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-utensils"></i>
                        <p>Chưa có chế độ ăn uống</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Health Tab -->
        <div id="health" class="tab-content">
            <div class="card">
                <h3><i class="fas fa-heartbeat"></i> Nhật Ký Sức Khỏe</h3>
                <?php if (!empty($health_diary)): ?>
                    <div class="timeline">
                        <?php foreach ($health_diary as $health): ?>
                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <i class="fas fa-calendar"></i> 
                                    <?php echo date('d/m/Y H:i', strtotime($health['ngay_ghi'])); ?>
                                </div>
                                <div class="timeline-content">
                                    <p><strong>Cân nặng:</strong> <?php echo htmlspecialchars($health['can_nang']); ?> kg</p>
                                    <p><strong>Chiều cao:</strong> <?php echo htmlspecialchars($health['chieu_cao']); ?> cm</p>
                                    <p><strong>Nhiệt độ:</strong> <?php echo htmlspecialchars($health['nhiet_do']); ?>°C</p>
                                    <p><strong>Triệu chứng:</strong> <?php echo htmlspecialchars($health['trieu_chung'] ?? 'Không có'); ?></p>
                                    <p><strong>Chẩn đoán:</strong> <?php echo htmlspecialchars($health['chan_doan'] ?? 'Không có'); ?></p>
                                    <p><strong>Thuốc điều trị:</strong> <?php echo htmlspecialchars($health['thuoc_dieu_tri'] ?? 'Không có'); ?></p>
                                    <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($health['ghi_chu'] ?? 'Không có'); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-heartbeat"></i>
                        <p>Chưa có nhật ký sức khỏe</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Appointments Tab -->
        <div id="appointments" class="tab-content">
            <div class="card">
                <h3><i class="fas fa-calendar-alt"></i> Lịch Hẹn</h3>
                <?php if (!empty($appointments)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Mã lịch</th>
                                <th>Dịch vụ</th>
                                <th>Ngày hẹn</th>
                                <th>Giờ hẹn</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $apt): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($apt['ma_lich_hen']); ?></td>
                                    <td><?php echo htmlspecialchars($apt['ten_dich_vu']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($apt['ngay_hen'])); ?></td>
                                    <td><?php echo htmlspecialchars($apt['gio_hen']); ?></td>
                                    <td>
                                        <?php
                                        $status_class = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];
                                        $status_text = ['Chờ duyệt', 'Đã xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Đã hủy'];
                                        echo '<span class="status-badge ' . $status_class[$apt['trang_thai']] . '">' . $status_text[$apt['trang_thai']] . '</span>';
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($apt['ghi_chu'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-alt"></i>
                        <p>Chưa có lịch hẹn</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ratings Tab -->
        <div id="ratings" class="tab-content">
            <div class="card">
                <h3><i class="fas fa-star"></i> Đánh Giá & Nhận Xét</h3>
                <?php if (!empty($ratings)): ?>
                    <?php foreach ($ratings as $rating): ?>
                        <div style="border-bottom: 1px solid #eee; padding: 20px 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <strong><?php echo htmlspecialchars($rating['ho_ten']); ?></strong>
                                <span class="rating">
                                    <?php for($i = 0; $i < $rating['so_sao']; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                    <?php for($i = $rating['so_sao']; $i < 5; $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>
                                </span>
                            </div>
                            <p><?php echo htmlspecialchars($rating['noi_dung']); ?></p>
                            <small style="color: #999;">
                                <?php echo date('d/m/Y H:i', strtotime($rating['ngay_danh_gia'])); ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-star"></i>
                        <p>Chưa có đánh giá</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            const tabs = document.querySelectorAll('.tab-content');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Remove active class from all buttons
            const buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
    </script>
<?php require_once '../includes/chat_widget.php'; ?>
</body>
</html>
