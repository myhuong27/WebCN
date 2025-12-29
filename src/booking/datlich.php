<?php
session_start();

// Redirect sang trang đặt lịch mới có thanh toán
header('Location: dat_lich_thanh_toan.php');
exit();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lịch Chăm Sóc - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0f7fa 0%, #fce4ec 50%, #fff9c4 100%);
            background-attachment: fixed;
        }

        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-title {
            text-align: center;
            font-size: 36px;
            margin-bottom: 40px;
            color: #333;
        }

        .form-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .form-group label .required {
            color: #ff6b9d;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b9d;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .service-selection {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }

        .service-item {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .service-item:hover {
            border-color: #ff6b9d;
            background: #fff;
        }

        .service-info h4 {
            margin-bottom: 5px;
            color: #333;
        }

        .service-info p {
            color: #666;
            font-size: 14px;
        }

        .service-price {
            font-size: 20px;
            font-weight: bold;
            color: #ff6b9d;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #ff6b9d;
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            background: #ff4081;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #ddd;
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
            margin-top: 60px;
            text-align: center;
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
                <div class="brand-name">Pet Care Center</div>
            </div>
            <nav class="nav-menu">
                <a href="../index.php" class="nav-link">Trang chủ</a>
                <a href="thucung.php" class="nav-link">Thú cưng</a>
                <a href="dichvu.php" class="nav-link">Dịch vụ</a>
                <a href="datlich.php" class="nav-link"><i class="fas fa-calendar-check"></i> Lịch hẹn</a>
                <a href="voucher.php" class="nav-link"><i class="fas fa-ticket-alt"></i> Ưu đãi</a>
                <a href="../index.php#lien-he" class="nav-link">Liên hệ</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="nav-link"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['ho_ten']); ?></a>
                    <a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="login_update.php" class="nav-link"><i class="fas fa-user"></i></a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="page-title"><i class="fas fa-calendar-alt"></i> Đặt Lịch Chăm Sóc Thú Cưng</h1>

        <?php
        $success_message = '';
        $error_message = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $ma_lich_hen = 'LH' . date('YmdHis');
                $nguoi_dung_id = $_SESSION['user_id'];
                $thu_cung_id = $_POST['thu_cung_id'];
                $dich_vu_id = $_POST['dich_vu_id'];
                $ten_khach_hang = $_POST['ten_khach_hang'];
                $so_dien_thoai = $_POST['so_dien_thoai'];
                $email = $_POST['email'];
                $ngay_hen = $_POST['ngay_hen'];
                $gio_hen = $_POST['gio_hen'];
                $ghi_chu = $_POST['ghi_chu'];

                $sql = "INSERT INTO lich_hen (ma_lich_hen, nguoi_dung_id, thu_cung_id, dich_vu_id, ngay_hen, gio_hen, ghi_chu, trang_thai) 
                        VALUES (:ma_lich_hen, :nguoi_dung_id, :thu_cung_id, :dich_vu_id, :ngay_hen, :gio_hen, :ghi_chu, 0)";
                
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':ma_lich_hen' => $ma_lich_hen,
                    ':nguoi_dung_id' => $nguoi_dung_id,
                    ':thu_cung_id' => $thu_cung_id,
                    ':dich_vu_id' => $dich_vu_id,
                    ':ngay_hen' => $ngay_hen,
                    ':gio_hen' => $gio_hen,
                    ':ghi_chu' => $ghi_chu
                ]);

                $success_message = "Đặt lịch thành công! Mã lịch hẹn của bạn là: <strong>$ma_lich_hen</strong>. Chúng tôi sẽ liên hệ với bạn sớm.";
            } catch(PDOException $e) {
                $error_message = "Có lỗi xảy ra: " . $e->getMessage();
            }
        }

        // Lấy danh sách thú cưng của user đang đăng nhập
        $user_id = $_SESSION['user_id'];
        $sql_pets = "SELECT * FROM thu_cung WHERE chu_so_huu_id = ? AND trang_thai = 1";
        $stmt_pets = $conn->prepare($sql_pets);
        $stmt_pets->execute([$user_id]);
        $pets = $stmt_pets->fetchAll(PDO::FETCH_ASSOC);

        // Lấy danh sách dịch vụ
        $sql_services = "SELECT * FROM dich_vu WHERE trang_thai = 1";
        $stmt_services = $conn->prepare($sql_services);
        $stmt_services->execute();
        $services = $stmt_services->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Chọn thú cưng <span class="required">*</span></label>
                    <select name="thu_cung_id" required>
                        <option value="">-- Chọn thú cưng --</option>
                        <?php foreach ($pets as $pet): ?>
                            <option value="<?php echo $pet['id']; ?>">
                                <?php echo htmlspecialchars($pet['ten_thu_cung']); ?> - 
                                <?php echo htmlspecialchars($pet['loai_thu_cung']); ?> 
                                (<?php echo htmlspecialchars($pet['ma_thu_cung']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Chọn dịch vụ <span class="required">*</span></label>
                    <select name="dich_vu_id" required>
                        <option value="">-- Chọn dịch vụ --</option>
                        <?php foreach ($services as $service): ?>
                            <option value="<?php echo $service['id']; ?>" data-price="<?php echo $service['gia_dich_vu']; ?>">
                                <?php echo htmlspecialchars($service['ten_dich_vu']); ?> - 
                                <?php echo number_format($service['gia_dich_vu'], 0, ',', '.'); ?>đ
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Họ tên khách hàng <span class="required">*</span></label>
                        <input type="text" name="ten_khach_hang" required placeholder="Nhập họ tên">
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại <span class="required">*</span></label>
                        <input type="tel" name="so_dien_thoai" required placeholder="Nhập số điện thoại">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="Nhập email (không bắt buộc)">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Ngày hẹn <span class="required">*</span></label>
                        <input type="date" name="ngay_hen" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label>Giờ hẹn <span class="required">*</span></label>
                        <input type="time" name="gio_hen" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ghi chú</label>
                    <textarea name="ghi_chu" placeholder="Nhập ghi chú hoặc yêu cầu đặc biệt..."></textarea>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Đặt lịch ngay
                    </button>
                    <a href="dichvu.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 Pet Care Center. All rights reserved.</p>
    </div>
</body>
</html>
