<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_update.php');
    exit();
}

// Xử lý kiểm tra mã giảm giá (AJAX)
if (isset($_GET['action']) && $_GET['action'] === 'check_voucher' && isset($_GET['code'])) {
    header('Content-Type: application/json');
    
    $voucher_code = strtoupper(trim($_GET['code']));
    $service_price = floatval($_GET['price'] ?? 0);
    
    // Danh sách voucher (giống trong voucher.php)
    $vouchers = [
        'PETCARE10' => ['discount' => 10, 'type' => 'percent', 'min_order' => 200000, 'max_discount' => 100000],
        'SPA50K' => ['discount' => 50000, 'type' => 'fixed', 'min_order' => 300000, 'max_discount' => 50000],
        'VACCINE20' => ['discount' => 20, 'type' => 'percent', 'min_order' => 100000, 'max_discount' => 150000],
        'COMBO30' => ['discount' => 30, 'type' => 'percent', 'min_order' => 500000, 'max_discount' => 300000],
        'REFER100K' => ['discount' => 100000, 'type' => 'fixed', 'min_order' => 0, 'max_discount' => 100000],
    ];
    
    if (isset($vouchers[$voucher_code])) {
        $voucher = $vouchers[$voucher_code];
        
        // Kiểm tra đơn tối thiểu
        if ($service_price < $voucher['min_order']) {
            echo json_encode([
                'success' => false,
                'message' => 'Đơn hàng tối thiểu ' . number_format($voucher['min_order'], 0, ',', '.') . '₫'
            ]);
            exit();
        }
        
        // Tính giảm giá
        if ($voucher['type'] === 'percent') {
            $discount_amount = ($service_price * $voucher['discount']) / 100;
            if ($discount_amount > $voucher['max_discount']) {
                $discount_amount = $voucher['max_discount'];
            }
        } else {
            $discount_amount = $voucher['discount'];
        }
        
        $final_price = $service_price - $discount_amount;
        if ($final_price < 0) $final_price = 0;
        
        echo json_encode([
            'success' => true,
            'message' => 'Áp dụng mã thành công!',
            'discount_amount' => $discount_amount,
            'final_price' => $final_price,
            'voucher_info' => $voucher
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn!'
        ]);
    }
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý đặt lịch
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dat_lich'])) {
    try {
        // Tạo mã đặt lịch
        $ma_dat_lich = 'DL' . date('Ymd') . rand(1000, 9999);
        
        // Lấy thông tin voucher nếu có
        $voucher_code = !empty($_POST['voucher_code']) ? strtoupper(trim($_POST['voucher_code'])) : null;
        $discount_amount = floatval($_POST['discount_amount'] ?? 0);
        $ghi_chu = $_POST['ghi_chu'];
        
        // Thêm thông tin voucher vào ghi chú nếu có
        if ($voucher_code && $discount_amount > 0) {
            $ghi_chu .= "\n[Voucher: " . $voucher_code . " - Giảm " . number_format($discount_amount, 0, ',', '.') . "₫]";
        }
        
        // Insert đặt lịch
        $stmt = $conn->prepare("INSERT INTO dat_lich_dich_vu (ma_dat_lich, nguoi_dung_id, dich_vu_id, thu_cung_id, ngay_dat_lich, gio_dat_lich, ghi_chu, tong_tien, phuong_thuc_thanh_toan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $ma_dat_lich,
            $user_id,
            $_POST['dich_vu_id'],
            $_POST['thu_cung_id'] ?: null,
            $_POST['ngay_dat_lich'],
            $_POST['gio_dat_lich'],
            $ghi_chu,
            $_POST['tong_tien'],
            $_POST['phuong_thuc_thanh_toan']
        ]);
        
        $dat_lich_id = $conn->lastInsertId();
        
        // Nếu thanh toán online
        if (in_array($_POST['phuong_thuc_thanh_toan'], ['momo', 'vnpay', 'zalopay'])) {
            // Tạo mã thanh toán
            $ma_thanh_toan = 'TT' . date('Ymd') . rand(1000, 9999);
            
            $stmt = $conn->prepare("INSERT INTO thanh_toan (ma_thanh_toan, nguoi_dung_id, dat_lich_id, so_tien, phuong_thuc) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $ma_thanh_toan,
                $user_id,
                $dat_lich_id,
                $_POST['tong_tien'],
                $_POST['phuong_thuc_thanh_toan']
            ]);
            
            // Chuyển đến trang thanh toán
            header("Location: thanh_toan.php?ma=" . $ma_thanh_toan);
            exit();
        } else {
            $success = "Đặt lịch thành công! Mã đặt lịch: " . $ma_dat_lich;
        }
        
    } catch (PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý thêm thú cưng nhanh (AJAX)
if (isset($_POST['quick_add_pet'])) {
    header('Content-Type: application/json');
    try {
        // Tạo mã thú cưng tự động
        $stmt = $conn->query("SELECT MAX(id) as max_id FROM thu_cung");
        $max_id = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'] ?? 0;
        $ma_thu_cung = 'TC' . str_pad($max_id + 1, 3, '0', STR_PAD_LEFT);
        
        // INSERT với trang_thai = 1 (số, không phải chuỗi)
        $stmt = $conn->prepare("INSERT INTO thu_cung (ma_thu_cung, chu_so_huu_id, ten_thu_cung, loai_thu_cung, tuoi, can_nang, trang_thai, ngay_tiep_nhan) VALUES (?, ?, ?, ?, ?, ?, 1, CURDATE())");
        $stmt->execute([
            $ma_thu_cung,
            $user_id,
            $_POST['ten_thu_cung'],
            $_POST['loai_thu_cung'],
            $_POST['tuoi'] ?: null,
            $_POST['can_nang'] ?: null
        ]);
        
        $new_pet_id = $conn->lastInsertId();
        
        // Lấy thông tin thú cưng vừa thêm
        $stmt = $conn->prepare("SELECT id, ten_thu_cung, loai_thu_cung, tuoi FROM thu_cung WHERE id = ?");
        $stmt->execute([$new_pet_id]);
        $new_pet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Thêm thú cưng thành công!',
            'pet' => $new_pet
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi: ' . $e->getMessage()
        ]);
    }
    exit();
}

// Lấy danh sách dịch vụ
$stmt = $conn->query("SELECT * FROM dich_vu WHERE trang_thai = 1 ORDER BY gia_dich_vu ASC");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách thú cưng của user (lấy tất cả thú cưng của user hoặc không có chủ)
$stmt = $conn->prepare("SELECT id, ten_thu_cung, loai_thu_cung, can_nang, tuoi FROM thu_cung WHERE (chu_so_huu_id = ? OR chu_so_huu_id IS NULL) ORDER BY ten_thu_cung ASC");
$stmt->execute([$user_id]);
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lịch & Thanh Toán - Pet Care Center</title>
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
            background: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #7f8c8d;
            margin-bottom: 40px;
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

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .service-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .service-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .service-content {
            padding: 20px;
        }

        .service-name {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .service-desc {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.6;
        }

        .service-price {
            font-size: 24px;
            font-weight: 700;
            color: #e74c3c;
            margin-bottom: 10px;
        }

        .service-info {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 13px;
            color: #95a5a6;
        }

        .btn-book {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .btn-book:hover {
            transform: translateY(-2px);
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
            padding: 0;
            width: 30px;
            height: 30px;
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

        .payment-option input[type="radio"]:checked + label {
            color: #3498db;
            font-weight: 600;
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

        .btn-apply-voucher {
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.3s;
        }

        .btn-apply-voucher:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .voucher-success {
            color: #27ae60;
            font-weight: 500;
            padding: 8px 12px;
            background: #d4edda;
            border-radius: 6px;
            border-left: 3px solid #27ae60;
        }

        .voucher-error {
            color: #e74c3c;
            font-weight: 500;
            padding: 8px 12px;
            background: #f8d7da;
            border-radius: 6px;
            border-left: 3px solid #e74c3c;
        }

        .discount-row {
            color: #27ae60;
            font-weight: 600;
        }

        .btn-add-pet {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-add-pet:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .pet-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .pet-form-grid .form-group {
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .pet-form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-calendar-check"></i> Đặt Lịch Dịch Vụ</h1>
        <p class="subtitle">Chọn dịch vụ phù hợp cho thú cưng của bạn</p>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                <a href="../index.php" style="margin-left: 15px; color: #155724; font-weight: 600;">Về trang chủ</a>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="services-grid">
            <?php foreach ($services as $service): ?>
            <div class="service-card" onclick="openBookingModal(<?php echo htmlspecialchars(json_encode($service)); ?>)">
                <img src="https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=400" alt="<?php echo htmlspecialchars($service['ten_dich_vu']); ?>">
                <div class="service-content">
                    <h3 class="service-name"><?php echo htmlspecialchars($service['ten_dich_vu']); ?></h3>
                    <p class="service-desc"><?php echo htmlspecialchars($service['mo_ta']); ?></p>
                    <div class="service-info">
                        <span><i class="fas fa-clock"></i> <?php echo $service['thoi_gian_thuc_hien']; ?> phút</span>
                        <span><i class="fas fa-tag"></i> <?php echo $service['don_vi']; ?></span>
                    </div>
                    <div class="service-price"><?php echo number_format($service['gia_dich_vu'], 0, ',', '.'); ?>₫</div>
                    <button class="btn-book">Đặt Lịch Ngay</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal Đặt Lịch -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Đặt Lịch Dịch Vụ</h2>
                <button class="btn-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" id="bookingForm">
                <input type="hidden" name="dat_lich" value="1">
                <input type="hidden" name="dich_vu_id" id="dich_vu_id">
                <input type="hidden" name="tong_tien" id="tong_tien">
                
                <div class="form-group">
                    <label>Dịch vụ đã chọn</label>
                    <input type="text" id="ten_dich_vu" readonly style="background: #f8f9fa;">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-paw"></i> Chọn thú cưng *</label>
                    <select name="thu_cung_id" id="pet_select" required>
                        <option value="">-- Chọn thú cưng --</option>
                        <?php if (count($pets) > 0): ?>
                            <?php foreach ($pets as $pet): ?>
                                <option value="<?php echo $pet['id']; ?>">
                                    <?php 
                                    echo htmlspecialchars($pet['ten_thu_cung']) . ' - ' . $pet['loai_thu_cung'];
                                    if (isset($pet['tuoi']) && $pet['tuoi']) echo ' (' . $pet['tuoi'] . ' tuổi)';
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Bạn chưa có thú cưng nào</option>
                        <?php endif; ?>
                    </select>
                    <div style="margin-top: 10px; display: flex; gap: 10px; align-items: center;">
                        <button type="button" class="btn-add-pet" onclick="openAddPetModal()">
                            <i class="fas fa-plus-circle"></i> Thêm thú cưng mới
                        </button>
                        <a href="quan_ly_thucung_user.php" target="_blank" style="color: #667eea; font-size: 14px; text-decoration: none;">
                            <i class="fas fa-external-link-alt"></i> Quản lý thú cưng
                        </a>
                    </div>
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

                <!-- Mã giảm giá -->
                <div class="form-group">
                    <label><i class="fas fa-ticket-alt"></i> Mã giảm giá</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="voucher_code" placeholder="Nhập mã giảm giá" style="flex: 1;">
                        <button type="button" class="btn-apply-voucher" onclick="applyVoucher()">
                            <i class="fas fa-tag"></i> Áp dụng
                        </button>
                    </div>
                    <input type="hidden" name="voucher_code" id="voucher_code_hidden">
                    <input type="hidden" name="discount_amount" id="discount_amount" value="0">
                    <div id="voucher_message" style="margin-top: 8px; font-size: 14px;"></div>
                    <div style="margin-top: 8px;">
                        <a href="voucher.php" target="_blank" style="color: #3498db; font-size: 14px;">
                            <i class="fas fa-gifts"></i> Xem tất cả mã giảm giá
                        </a>
                    </div>
                </div>

                <div class="form-group">
                    <label>Phương thức thanh toán *</label>
                    <div class="payment-methods">
                        <div class="payment-option" onclick="selectPayment('tien_mat', this)">
                            <input type="radio" name="phuong_thuc_thanh_toan" value="tien_mat" id="tien_mat">
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
                    <div class="price-row discount-row" id="discount_row" style="display: none;">
                        <span><i class="fas fa-tag"></i> Giảm giá:</span>
                        <span id="discount_summary">-0₫</span>
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
        </div>
    </div>

    <!-- Modal Thêm thú cưng nhanh -->
    <div id="addPetModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-paw"></i> Thêm Thú Cưng Mới</h2>
                <button class="btn-close" onclick="closeAddPetModal()">&times;</button>
            </div>
            <form id="addPetForm" onsubmit="submitAddPet(event)">
                <div class="pet-form-grid">
                    <div class="form-group">
                        <label>Tên thú cưng *</label>
                        <input type="text" name="ten_thu_cung" required placeholder="VD: Mèo Miu">
                    </div>
                    <div class="form-group">
                        <label>Loại *</label>
                        <select name="loai_thu_cung" required>
                            <option value="">-- Chọn loại --</option>
                            <option value="Chó">Chó</option>
                            <option value="Mèo">Mèo</option>
                            <option value="Chim">Chim</option>
                            <option value="Hamster">Hamster</option>
                            <option value="Thỏ">Thỏ</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Tuổi</label>
                        <input type="number" name="tuoi" min="0" max="30" placeholder="VD: 2">
                    </div>
                    <div class="form-group">
                        <label>Cân nặng (kg)</label>
                        <input type="number" name="can_nang" min="0" max="100" step="0.1" placeholder="VD: 5.5">
                    </div>
                </div>
                <div id="add_pet_message" style="margin-top: 15px;"></div>
                <button type="submit" class="btn-submit" style="margin-top: 20px;">
                    <i class="fas fa-plus-circle"></i> Thêm thú cưng
                </button>
            </form>
        </div>
    </div>

    <script>
        let currentServicePrice = 0;
        let currentDiscountAmount = 0;

        function openBookingModal(service) {
            currentServicePrice = parseFloat(service.gia_dich_vu);
            currentDiscountAmount = 0;
            
            document.getElementById('dich_vu_id').value = service.id;
            document.getElementById('ten_dich_vu').value = service.ten_dich_vu;
            document.getElementById('tong_tien').value = service.gia_dich_vu;
            
            document.getElementById('service_name_summary').textContent = service.ten_dich_vu;
            document.getElementById('service_price_summary').textContent = formatCurrency(service.gia_dich_vu);
            document.getElementById('total_price_summary').textContent = formatCurrency(service.gia_dich_vu);
            
            // Reset voucher
            document.getElementById('voucher_code').value = '';
            document.getElementById('voucher_code_hidden').value = '';
            document.getElementById('discount_amount').value = '0';
            document.getElementById('voucher_message').innerHTML = '';
            document.getElementById('discount_row').style.display = 'none';
            
            document.getElementById('bookingModal').classList.add('show');
        }

        async function applyVoucher() {
            const voucherCode = document.getElementById('voucher_code').value.trim();
            const messageDiv = document.getElementById('voucher_message');
            
            if (!voucherCode) {
                messageDiv.innerHTML = '<span class="voucher-error"><i class="fas fa-exclamation-circle"></i> Vui lòng nhập mã giảm giá</span>';
                return;
            }
            
            messageDiv.innerHTML = '<span style="color: #3498db;"><i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...</span>';
            
            try {
                const response = await fetch(`dat_lich_thanh_toan.php?action=check_voucher&code=${encodeURIComponent(voucherCode)}&price=${currentServicePrice}`);
                const data = await response.json();
                
                if (data.success) {
                    currentDiscountAmount = data.discount_amount;
                    
                    // Cập nhật UI
                    messageDiv.innerHTML = `<span class="voucher-success"><i class="fas fa-check-circle"></i> ${data.message}</span>`;
                    
                    // Hiển thị dòng giảm giá
                    document.getElementById('discount_row').style.display = 'flex';
                    document.getElementById('discount_summary').textContent = '-' + formatCurrency(data.discount_amount);
                    
                    // Cập nhật tổng tiền
                    document.getElementById('total_price_summary').textContent = formatCurrency(data.final_price);
                    document.getElementById('tong_tien').value = data.final_price;
                    
                    // Lưu mã voucher
                    document.getElementById('voucher_code_hidden').value = voucherCode.toUpperCase();
                    document.getElementById('discount_amount').value = data.discount_amount;
                } else {
                    messageDiv.innerHTML = `<span class="voucher-error"><i class="fas fa-times-circle"></i> ${data.message}</span>`;
                    resetVoucher();
                }
            } catch (error) {
                messageDiv.innerHTML = '<span class="voucher-error"><i class="fas fa-exclamation-triangle"></i> Lỗi kết nối. Vui lòng thử lại!</span>';
                console.error('Error:', error);
            }
        }

        function resetVoucher() {
            currentDiscountAmount = 0;
            document.getElementById('discount_row').style.display = 'none';
            document.getElementById('total_price_summary').textContent = formatCurrency(currentServicePrice);
            document.getElementById('tong_tien').value = currentServicePrice;
            document.getElementById('voucher_code_hidden').value = '';
            document.getElementById('discount_amount').value = '0';
        }

        function closeModal() {
            document.getElementById('bookingModal').classList.remove('show');
            document.getElementById('bookingForm').reset();
            resetVoucher();
        }

        // Cho phép nhấn Enter để áp mã giảm giá
        document.addEventListener('DOMContentLoaded', function() {
            const voucherInput = document.getElementById('voucher_code');
            if (voucherInput) {
                voucherInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        applyVoucher();
                    }
                });
            }
        });

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

        // Thêm thú cưng nhanh
        function openAddPetModal() {
            document.getElementById('addPetModal').classList.add('show');
        }

        function closeAddPetModal() {
            document.getElementById('addPetModal').classList.remove('show');
            document.getElementById('addPetForm').reset();
            document.getElementById('add_pet_message').innerHTML = '';
        }

        async function submitAddPet(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            formData.append('quick_add_pet', '1');
            
            const messageDiv = document.getElementById('add_pet_message');
            messageDiv.innerHTML = '<span style="color: #3498db;"><i class="fas fa-spinner fa-spin"></i> Đang thêm...</span>';
            
            try {
                const response = await fetch('dat_lich_thanh_toan.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    messageDiv.innerHTML = '<span style="color: #27ae60;"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
                    
                    // Thêm option mới vào select
                    const petSelect = document.getElementById('pet_select');
                    const newOption = document.createElement('option');
                    newOption.value = data.pet.id;
                    newOption.text = data.pet.ten_thu_cung + ' - ' + data.pet.loai_thu_cung;
                    if (data.pet.tuoi) {
                        newOption.text += ' (' + data.pet.tuoi + ' tuổi)';
                    }
                    petSelect.add(newOption);
                    
                    // Chọn thú cưng vừa thêm
                    petSelect.value = data.pet.id;
                    
                    // Xóa option "Bạn chưa có thú cưng nào" nếu có
                    const emptyOption = petSelect.querySelector('option[disabled]');
                    if (emptyOption) {
                        emptyOption.remove();
                    }
                    
                    // Đóng modal sau 1.5s
                    setTimeout(() => {
                        closeAddPetModal();
                    }, 1500);
                } else {
                    messageDiv.innerHTML = '<span style="color: #e74c3c;"><i class="fas fa-times-circle"></i> ' + data.message + '</span>';
                }
            } catch (error) {
                messageDiv.innerHTML = '<span style="color: #e74c3c;"><i class="fas fa-exclamation-triangle"></i> Lỗi kết nối!</span>';
                console.error('Error:', error);
            }
        }

        // Đóng modal thêm thú cưng khi click bên ngoài
        document.getElementById('addPetModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddPetModal();
            }
        });
    </script>
</body>
</html>
