<?php
session_start();
require_once '../config/connect.php';

$user_id = $_SESSION['user_id'] ?? null;

// Xử lý đặt lịch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dat_lich'])) {
    if (!$user_id) {
        header('Location: login_page.php');
        exit();
    }
    
    try {
        $ma_dat_lich = 'DL' . date('Ymd') . rand(1000, 9999);
        
        $stmt = $conn->prepare("INSERT INTO dat_lich_dich_vu (ma_dat_lich, nguoi_dung_id, dich_vu_id, thu_cung_id, ngay_dat_lich, gio_dat_lich, ghi_chu, tong_tien, phuong_thuc_thanh_toan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ma_dat_lich,
            $user_id,
            $_POST['dich_vu_id'],
            $_POST['thu_cung_id'] ?: null,
            $_POST['ngay_dat_lich'],
            $_POST['gio_dat_lich'],
            $_POST['ghi_chu'],
            $_POST['tong_tien'],
            $_POST['phuong_thuc_thanh_toan']
        ]);
        
        $dat_lich_id = $conn->lastInsertId();
        
        // Nếu thanh toán online
        if (in_array($_POST['phuong_thuc_thanh_toan'], ['momo', 'vnpay', 'zalopay'])) {
            $ma_thanh_toan = 'TT' . date('Ymd') . rand(1000, 9999);
            
            $stmt = $conn->prepare("INSERT INTO thanh_toan (ma_thanh_toan, nguoi_dung_id, dat_lich_id, so_tien, phuong_thuc) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $ma_thanh_toan,
                $user_id,
                $dat_lich_id,
                $_POST['tong_tien'],
                $_POST['phuong_thuc_thanh_toan']
            ]);
            
            header("Location: thanh_toan.php?ma=" . $ma_thanh_toan);
            exit();
        } else {
            $success = "Đặt lịch thành công! Mã đặt lịch: " . $ma_dat_lich;
        }
        
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Lấy danh sách thú cưng của user
$pets = [];
if ($user_id) {
    $stmt = $conn->prepare("SELECT id, ten_thu_cung, loai_thu_cung FROM thu_cung WHERE chu_so_huu_id = ? AND trang_thai = 1");
    $stmt->execute([$user_id]);
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy danh sách lịch đặt của user
$bookings = [];
if ($user_id) {
    $stmt = $conn->prepare("SELECT dl.*, dv.ten_dich_vu, tc.ten_thu_cung, tc.loai_thu_cung, tc.hinh_anh
                           FROM dat_lich_dich_vu dl
                           LEFT JOIN dich_vu dv ON dl.dich_vu_id = dv.id
                           LEFT JOIN thu_cung tc ON dl.thu_cung_id = tc.id
                           WHERE dl.nguoi_dung_id = ?
                           ORDER BY dl.ngay_dat_lich DESC, dl.gio_dat_lich DESC");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dịch Vụ - Pet Care Center</title>
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
            background: linear-gradient(135deg, #f5f9fc 0%, #fef5f8 50%, #fffef7 100%);
            background-attachment: fixed;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
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

        .nav-menu {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #ff6b9d;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 120px 20px 40px;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 8px;
            background: rgba(102, 126, 234, 0.1);
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateX(-5px);
        }
        
        .back-btn i {
            transition: transform 0.3s;
        }
        
        .back-btn:hover i {
            transform: translateX(-3px);
        }

        .page-title {
            text-align: center;
            font-size: 42px;
            margin-bottom: 20px;
            color: #333;
        }

        .page-subtitle {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-bottom: 50px;
        }

        /* Services Grid */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        .service-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .service-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 80px;
        }

        .service-content {
            padding: 30px;
        }

        .service-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .service-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .service-details {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .service-detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #555;
        }

        .service-detail-item i {
            color: #ff6b9d;
            width: 20px;
        }

        .service-price {
            font-size: 28px;
            font-weight: bold;
            color: #ff6b9d;
            margin-bottom: 20px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            width: 100%;
            text-align: center;
        }

        .btn-primary {
            background: #ff6b9d;
            color: white;
        }

        .btn-primary:hover {
            background: #ff4081;
            transform: scale(1.05);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 2000;
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

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .modal-header h2 {
            color: #2c3e50;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #95a5a6;
            cursor: pointer;
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
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 10px;
        }

        .payment-option {
            padding: 15px;
            border: 2px solid #ecf0f1;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .payment-option:hover {
            border-color: #3498db;
            background: #f0f8ff;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-option.selected {
            border-color: #3498db;
            background: #f0f8ff;
        }

        .payment-option i {
            font-size: 32px;
            margin-bottom: 8px;
            display: block;
        }

        .price-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .price-row:last-child {
            border-bottom: none;
            font-size: 20px;
            font-weight: 700;
            color: #e74c3c;
            margin-top: 10px;
        }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
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

        /* Footer */
        .footer {
            background: #333;
            color: white;
            padding: 40px 20px;
            margin-top: auto;
            text-align: center;
            position: relative;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="nav-container">
            <a href="../index.php" style="text-decoration: none; color: inherit;">
                <div class="logo-container">
                    <div class="logo-image">
                        <i class="fas fa-paw"></i>
                    </div>
                    <div>
                        <div class="brand-name">Pet Care Center</div>
                        <div class="brand-slogan">YÊU THƯƠNG - CHĂM SÓC - TẬN TÂM</div>
                    </div>
                </div>
            </a>
            
            <nav class="nav-menu">
                <a href="../index.php" class="nav-link">Trang chủ</a>
                <a href="dichvu.php" class="nav-link">Dịch vụ</a>
                <a href="gui_nuoiho.php" class="nav-link">Gửi nuôi hộ</a>
                <a href="../index.php#lien-he" class="nav-link">Liên hệ</a>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-dropdown">
                        <div class="user-trigger">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['ho_ten'] ?? $_SESSION['username'] ?? 'User'); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../auth/login_update.php" class="nav-link"><i class="fas fa-user"></i> Đăng nhập</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Back Button -->
        <a href="../index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Quay lại trang chủ
        </a>
        
        <h1 class="page-title">Dịch Vụ Của Chúng Tôi</h1>
        <p class="page-subtitle">Các dịch vụ chăm sóc thú cưng chuyên nghiệp và tận tâm</p>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Services Grid -->
        <div class="services-grid">
            <?php
            require_once '../config/connect.php';
            
            try {
                $sql = "SELECT * FROM dich_vu WHERE trang_thai = 1 ORDER BY id";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $icons = [
                    'DV001' => 'fa-home',
                    'DV002' => 'fa-shower',
                    'DV003' => 'fa-cut',
                    'DV004' => 'fa-stethoscope',
                    'DV005' => 'fa-graduation-cap',
                    'DV006' => 'fa-heart'
                ];
                
                if (count($services) > 0) {
                    foreach ($services as $service) {
                        $icon = isset($icons[$service['ma_dich_vu']]) ? $icons[$service['ma_dich_vu']] : 'fa-star';
                        ?>
                        <div class="service-card">
                            <div class="service-image">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>
                            <div class="service-content">
                                <h3 class="service-title"><?php echo htmlspecialchars($service['ten_dich_vu']); ?></h3>
                                <p class="service-description"><?php echo htmlspecialchars($service['mo_ta']); ?></p>
                                
                                <div class="service-details">
                                    <div class="service-detail-item">
                                        <i class="fas fa-clock"></i>
                                        <span>Thời gian: <?php echo htmlspecialchars($service['thoi_gian_thuc_hien']); ?> phút</span>
                                    </div>
                                    <div class="service-detail-item">
                                        <i class="fas fa-tag"></i>
                                        <span>Đơn vị tính: <?php echo htmlspecialchars($service['don_vi']); ?></span>
                                    </div>
                                </div>
                                
                                <div class="service-price">
                                    <?php echo number_format($service['gia_dich_vu'], 0, ',', '.'); ?>₫ / <?php echo htmlspecialchars($service['don_vi']); ?>
                                </div>
                                
                                <button onclick="openBookingModal(<?php echo htmlspecialchars(json_encode($service)); ?>)" class="btn btn-primary">
                                    <i class="fas fa-calendar-check"></i> Đặt lịch ngay
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div style="text-align: center; padding: 60px; grid-column: 1/-1;">
                            <i class="fas fa-exclamation-circle" style="font-size: 80px; color: #ddd; margin-bottom: 20px;"></i>
                            <h3>Chưa có dịch vụ nào</h3>
                          </div>';
                }
            } catch(PDOException $e) {
                echo '<div style="text-align: center; padding: 60px; grid-column: 1/-1;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 80px; color: #ff6b6b; margin-bottom: 20px;"></i>
                        <h3>Có lỗi xảy ra</h3>
                        <p>Không thể tải danh sách dịch vụ.</p>
                      </div>';
            }
            ?>
        </div>
    </div>

    <!-- Modal Đặt Lịch -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Đặt Lịch Dịch Vụ</h2>
                <button class="btn-close" onclick="closeModal()">&times;</button>
            </div>
            <?php if (!$user_id): ?>
                <p style="text-align: center; padding: 20px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 40px; color: #e74c3c; margin-bottom: 10px;"></i><br>
                    Bạn cần <a href="login_page.php" style="color: #3498db; font-weight: 600;">đăng nhập</a> để đặt lịch!
                </p>
            <?php else: ?>
                <form method="POST" id="bookingForm">
                    <input type="hidden" name="dat_lich" value="1">
                    <input type="hidden" name="dich_vu_id" id="dich_vu_id">
                    <input type="hidden" name="tong_tien" id="tong_tien">
                    
                    <div class="form-group">
                        <label>Dịch vụ đã chọn</label>
                        <input type="text" id="ten_dich_vu" readonly style="background: #f8f9fa;">
                    </div>

                    <div class="form-group">
                        <label>Chọn thú cưng *</label>
                        <select name="thu_cung_id" required>
                            <option value="">-- Chọn thú cưng --</option>
                            <?php foreach ($pets as $pet): ?>
                                <option value="<?php echo $pet['id']; ?>">
                                    <?php echo htmlspecialchars($pet['ten_thu_cung']) . ' (' . $pet['loai_thu_cung'] . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ngày đặt lịch *</label>
                        <input type="date" name="ngay_dat_lich" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Giờ đặt lịch *</label>
                        <input type="time" name="gio_dat_lich" required value="09:00">
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="ghi_chu" placeholder="Yêu cầu đặc biệt hoặc lưu ý..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Phương thức thanh toán *</label>
                        <div class="payment-methods">
                            <div class="payment-option" onclick="selectPayment('tien_mat', this)">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="tien_mat" id="tien_mat" required>
                                <label for="tien_mat">
                                    <i class="fas fa-money-bill-wave" style="color: #27ae60;"></i>
                                    <div>Tiền mặt</div>
                                </label>
                            </div>
                            <div class="payment-option" onclick="selectPayment('chuyen_khoan', this)">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="chuyen_khoan" id="chuyen_khoan">
                                <label for="chuyen_khoan">
                                    <i class="fas fa-university" style="color: #3498db;"></i>
                                    <div>Chuyển khoản</div>
                                </label>
                            </div>
                            <div class="payment-option" onclick="selectPayment('momo', this)">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="momo" id="momo">
                                <label for="momo">
                                    <i class="fas fa-wallet" style="color: #a50064;"></i>
                                    <div>MoMo</div>
                                </label>
                            </div>
                            <div class="payment-option" onclick="selectPayment('vnpay', this)">
                                <input type="radio" name="phuong_thuc_thanh_toan" value="vnpay" id="vnpay">
                                <label for="vnpay">
                                    <i class="fas fa-credit-card" style="color: #0088cc;"></i>
                                    <div>VNPay</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="price-summary">
                        <div class="price-row">
                            <span>Dịch vụ:</span>
                            <span id="service_name_summary"></span>
                        </div>
                        <div class="price-row">
                            <span>Giá:</span>
                            <span id="service_price_summary"></span>
                        </div>
                        <div class="price-row">
                            <span>Tổng thanh toán:</span>
                            <span id="total_price_summary"></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check-circle"></i> Xác Nhận Đặt Lịch
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function openBookingModal(service) {
            document.getElementById('dich_vu_id').value = service.id;
            document.getElementById('ten_dich_vu').value = service.ten_dich_vu;
            document.getElementById('tong_tien').value = service.gia_dich_vu;
            
            document.getElementById('service_name_summary').textContent = service.ten_dich_vu;
            document.getElementById('service_price_summary').textContent = formatCurrency(service.gia_dich_vu);
            document.getElementById('total_price_summary').textContent = formatCurrency(service.gia_dich_vu);
            
            document.getElementById('bookingModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('bookingModal').classList.remove('show');
            document.getElementById('bookingForm')?.reset();
        }

        function selectPayment(method, element) {
            document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById(method).checked = true;
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
        }

        // Đóng modal khi click bên ngoài
        document.getElementById('bookingModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

    <!-- Phần lịch đặt của user -->
    <?php if ($user_id && count($bookings) > 0): ?>
    <section style="max-width: 1200px; margin: 80px auto; padding: 0 20px;">
        <h2 style="font-size: 36px; text-align: center; margin-bottom: 50px; color: #333;">
            <i class="fas fa-calendar-check"></i> Lịch Đặt Của Bạn
        </h2>
        
        <div style="display: grid; gap: 25px;">
            <?php foreach ($bookings as $booking): 
                $status_colors = [
                    'cho_xac_nhan' => '#ffc107',
                    'da_xac_nhan' => '#17a2b8',
                    'dang_thuc_hien' => '#007bff',
                    'hoan_thanh' => '#28a745',
                    'da_huy' => '#dc3545'
                ];
                $status_text = [
                    'cho_xac_nhan' => 'Chờ xác nhận',
                    'da_xac_nhan' => 'Đã xác nhận',
                    'dang_thuc_hien' => 'Đang thực hiện',
                    'hoan_thanh' => 'Hoàn thành',
                    'da_huy' => 'Đã hủy'
                ];
            ?>
            <div style="background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: all 0.3s;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px 25px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 18px; font-weight: 600;">
                        <i class="fas fa-ticket-alt"></i> <?php echo htmlspecialchars($booking['ma_dat_lich']); ?>
                    </div>
                    <div style="padding: 6px 15px; border-radius: 20px; font-size: 13px; font-weight: 600; background: <?php echo $status_colors[$booking['trang_thai']] ?? '#6c757d'; ?>;">
                        <?php echo $status_text[$booking['trang_thai']] ?? 'Không xác định'; ?>
                    </div>
                </div>

                <div style="padding: 25px; display: grid; grid-template-columns: auto 1fr; gap: 25px;">
                    <?php if ($booking['hinh_anh']): ?>
                        <img src="<?php echo htmlspecialchars($booking['hinh_anh']); ?>" alt="Pet" style="width: 120px; height: 120px; border-radius: 10px; object-fit: cover;">
                    <?php else: ?>
                        <img src="../images/image/default-pet.jpg" alt="Pet" style="width: 120px; height: 120px; border-radius: 10px; object-fit: cover;">
                    <?php endif; ?>

                    <div style="display: grid; gap: 15px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff6b9d, #ffa07a); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-concierge-bell"></i>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #999;">Dịch vụ</div>
                                <div style="font-size: 18px; color: #667eea; font-weight: 600;"><?php echo htmlspecialchars($booking['ten_dich_vu']); ?></div>
                            </div>
                        </div>

                        <?php if ($booking['ten_thu_cung']): ?>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff6b9d, #ffa07a); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-paw"></i>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #999;">Thú cưng</div>
                                <div style="font-size: 15px; color: #333; font-weight: 500;">
                                    <?php echo htmlspecialchars($booking['ten_thu_cung']); ?> (<?php echo htmlspecialchars($booking['loai_thu_cung']); ?>)
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff6b9d, #ffa07a); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #999;">Ngày & giờ</div>
                                <div style="font-size: 15px; color: #333; font-weight: 500;">
                                    <?php echo date('d/m/Y', strtotime($booking['ngay_dat_lich'])); ?> lúc <?php echo date('H:i', strtotime($booking['gio_dat_lich'])); ?>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff6b9d, #ffa07a); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #999;">Tổng tiền</div>
                                <div style="font-size: 20px; color: #28a745; font-weight: bold;"><?php echo number_format($booking['tong_tien'], 0, ',', '.'); ?> VNĐ</div>
                            </div>
                        </div>

                        <?php if ($booking['ghi_chu']): ?>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #ff6b9d, #ffa07a); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <div>
                                <div style="font-size: 12px; color: #999;">Ghi chú</div>
                                <div style="font-size: 15px; color: #333; font-weight: 500;"><?php echo htmlspecialchars($booking['ghi_chu']); ?></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($booking['trang_thai'] == 'hoan_thanh'): ?>
                        <div style="margin-top: 15px;">
                            <a href="danh_gia.php?dat_lich_id=<?php echo $booking['id']; ?>" style="display: inline-block; padding: 10px 20px; background: linear-gradient(135deg, #ffc107, #ff9800); color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                                <i class="fas fa-star"></i> Đánh giá dịch vụ
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 Pet Care Center. All rights reserved.</p>
    </div>

<?php require_once '../includes/chat_widget.php'; ?>
</body>
</html>
