<?php
session_start();
require_once '../config/connect.php';
require_once '../includes/chat_widget.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login_update.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy tab hiện tại
$current_tab = $_GET['tab'] ?? 'dashboard';

// Xử lý thêm thú cưng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pet'])) {
    $ten_thu_cung = trim($_POST['ten_thu_cung']);
    $loai_thu_cung = trim($_POST['loai_thu_cung']);
    $giong = trim($_POST['giong'] ?? '');
    $tuoi = intval($_POST['tuoi'] ?? 0);
    $gioi_tinh = trim($_POST['gioi_tinh'] ?? '');
    $mau_sac = trim($_POST['mau_sac'] ?? '');
    $can_nang = floatval($_POST['can_nang'] ?? 0);
    $tinh_trang_suc_khoe = trim($_POST['tinh_trang_suc_khoe'] ?? '');
    $ghi_chu = trim($_POST['ghi_chu'] ?? '');
    
    // Xử lý upload hình ảnh
    $hinh_anh = 'images/image/default-pet.jpg';
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $target_dir = "images/image/";
        $file_extension = strtolower(pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION));
        $new_filename = 'pet_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_file)) {
            $hinh_anh = $target_file;
        }
    }
    
    try {
        // Tạo mã thú cưng tự động
        $stmt = $conn->query("SELECT MAX(id) as max_id FROM thu_cung");
        $max_id = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'] ?? 0;
        $ma_thu_cung = 'TC' . str_pad($max_id + 1, 3, '0', STR_PAD_LEFT);
        
        $stmt = $conn->prepare("INSERT INTO thu_cung (ma_thu_cung, chu_so_huu_id, ten_thu_cung, loai_thu_cung, giong, tuoi, gioi_tinh, mau_sac, can_nang, tinh_trang_suc_khoe, ghi_chu, hinh_anh, trang_thai, ngay_tiep_nhan) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, CURDATE())");
        $stmt->execute([$ma_thu_cung, $user_id, $ten_thu_cung, $loai_thu_cung, $giong, $tuoi, $gioi_tinh, $mau_sac, $can_nang, $tinh_trang_suc_khoe, $ghi_chu, $hinh_anh]);
        $_SESSION['success'] = 'Thêm thú cưng thành công!';
        header('Location: user_dashboard.php?tab=pets');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Xử lý xóa thú cưng
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM thu_cung WHERE id = ? AND chu_so_huu_id = ?");
        $stmt->execute([$delete_id, $user_id]);
        $_SESSION['success'] = 'Đã xóa thú cưng thành công!';
        header('Location: user_dashboard.php?tab=pets');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Xử lý sửa thú cưng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_pet'])) {
    $pet_id = intval($_POST['pet_id']);
    $ten_thu_cung = trim($_POST['ten_thu_cung']);
    $loai_thu_cung = trim($_POST['loai_thu_cung']);
    $giong = trim($_POST['giong'] ?? '');
    $tuoi = intval($_POST['tuoi'] ?? 0);
    $gioi_tinh = trim($_POST['gioi_tinh'] ?? '');
    $mau_sac = trim($_POST['mau_sac'] ?? '');
    $can_nang = floatval($_POST['can_nang'] ?? 0);
    $tinh_trang_suc_khoe = trim($_POST['tinh_trang_suc_khoe'] ?? '');
    $ghi_chu = trim($_POST['ghi_chu'] ?? '');
    
    // Xử lý upload hình ảnh mới (nếu có)
    $hinh_anh = $_POST['current_image'];
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $target_dir = "images/image/";
        $file_extension = strtolower(pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION));
        $new_filename = 'pet_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_file)) {
            $hinh_anh = $target_file;
        }
    }
    
    try {
        $stmt = $conn->prepare("UPDATE thu_cung SET ten_thu_cung = ?, loai_thu_cung = ?, giong = ?, tuoi = ?, gioi_tinh = ?, mau_sac = ?, can_nang = ?, tinh_trang_suc_khoe = ?, ghi_chu = ?, hinh_anh = ? WHERE id = ? AND chu_so_huu_id = ?");
        $stmt->execute([$ten_thu_cung, $loai_thu_cung, $giong, $tuoi, $gioi_tinh, $mau_sac, $can_nang, $tinh_trang_suc_khoe, $ghi_chu, $hinh_anh, $pet_id, $user_id]);
        $_SESSION['success'] = 'Cập nhật thông tin thú cưng thành công!';
        header('Location: user_dashboard.php?tab=pets');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Thống kê
try {
    // Đếm số thú cưng (sửa nguoi_dung_id -> chu_so_huu_id)
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM thu_cung WHERE chu_so_huu_id = ?");
    $stmt->execute([$user_id]);
    $total_pets = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Đếm số lịch hẹn
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM dat_lich_dich_vu WHERE nguoi_dung_id = ?");
    $stmt->execute([$user_id]);
    $total_appointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Đếm lịch hẹn đang chờ
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM dat_lich_dich_vu WHERE nguoi_dung_id = ? AND trang_thai = 'cho_xac_nhan'");
    $stmt->execute([$user_id]);
    $pending_appointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Lấy lịch hẹn gần nhất
    $stmt = $conn->prepare("SELECT dl.*, dv.ten_dich_vu, tc.ten_thu_cung 
                           FROM dat_lich_dich_vu dl
                           LEFT JOIN dich_vu dv ON dl.dich_vu_id = dv.id
                           LEFT JOIN thu_cung tc ON dl.thu_cung_id = tc.id
                           WHERE dl.nguoi_dung_id = ?
                           ORDER BY dl.ngay_hen DESC, dl.gio_hen DESC
                           LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy TẤT CẢ thú cưng của user (không limit)
    $stmt = $conn->prepare("SELECT * FROM thu_cung WHERE chu_so_huu_id = ? ORDER BY ngay_tao DESC");
    $stmt->execute([$user_id]);
    $all_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lấy 6 thú cưng mới nhất cho dashboard
    $pets = array_slice($all_pets, 0, 6);
    
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pet Care Center</title>
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

        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            border-radius: 20px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .welcome-section h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .welcome-section p {
            font-size: 18px;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        }

        .stat-info h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-info .number {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
        }

        .stat-icon.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.blue { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-icon.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }

        /* Tab Navigation */
        .tab-navigation {
            background: white;
            padding: 0;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .tab-links {
            display: flex;
            border-bottom: 2px solid #f0f0f0;
        }

        .tab-link {
            flex: 1;
            padding: 20px;
            text-align: center;
            background: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            color: #666;
            transition: all 0.3s;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .tab-link:hover {
            background: #f8f9fa;
            color: #667eea;
        }

        .tab-link.active {
            color: #667eea;
            background: linear-gradient(to bottom, #fff 0%, #f0f0ff 100%);
            border-bottom: 3px solid #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Pet Management Styles */
        .pets-management {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .pets-table-container {
            overflow-x: auto;
            margin-top: 20px;
        }

        .pets-table {
            width: 100%;
            border-collapse: collapse;
        }

        .pets-table th,
        .pets-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .pets-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .pets-table tr:hover {
            background: #f8f9fa;
        }

        .pet-image-small {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-edit {
            background: #4facfe;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-edit:hover {
            background: #3b8cce;
        }

        .btn-delete {
            background: #f5576c;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-delete:hover {
            background: #d4465a;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            font-size: 24px;
            color: #333;
        }

        .close-modal {
            font-size: 28px;
            color: #999;
            cursor: pointer;
            background: none;
            border: none;
        }

        .close-modal:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
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

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-header h2 {
            font-size: 24px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
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
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-outline {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
        }

        .appointments-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .appointment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .appointment-item:hover {
            background: #e9ecef;
        }

        .appointment-info {
            flex: 1;
        }

        .appointment-service {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .appointment-details {
            font-size: 14px;
            color: #666;
            display: flex;
            gap: 15px;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-confirmed { background: #d1ecf1; color: #0c5460; }
        .badge-completed { background: #d4edda; color: #155724; }

        .pets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .pet-card {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            transition: all 0.3s;
        }

        .pet-card:hover {
            transform: translateY(-5px);
            background: #e9ecef;
        }

        .pet-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            border: 4px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .pet-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .pet-breed {
            font-size: 14px;
            color: #666;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state i {
            font-size: 60px;
            margin-bottom: 15px;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .action-card {
            padding: 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            text-align: center;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .action-card i {
            font-size: 36px;
            margin-bottom: 10px;
        }

        .action-card h3 {
            font-size: 16px;
            margin: 0;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
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
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Trang chủ
            </a>
        </div>
    </div>

    <div class="container">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="welcome-section">
            <h1><i class="fas fa-hand-sparkles"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['ho_ten']); ?>!</h1>
            <p>Chào mừng bạn đến với trang quản lý của bạn</p>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <div class="tab-links">
                <a href="?tab=dashboard" class="tab-link <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-th-large"></i> Tổng quan
                </a>
                <a href="?tab=pets" class="tab-link <?php echo $current_tab === 'pets' ? 'active' : ''; ?>">
                    <i class="fas fa-paw"></i> Quản lý thú cưng
                </a>
                <a href="?tab=appointments" class="tab-link <?php echo $current_tab === 'appointments' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i> Lịch hẹn
                </a>
            </div>
        </div>

        <!-- Tab Contents -->
        
        <!-- Dashboard Tab -->
        <div class="tab-content <?php echo $current_tab === 'dashboard' ? 'active' : ''; ?>">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h3>Thú cưng</h3>
                    <div class="number"><?php echo $total_pets; ?></div>
                </div>
                <div class="stat-icon purple">
                    <i class="fas fa-paw"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>Lịch hẹn</h3>
                    <div class="number"><?php echo $total_appointments; ?></div>
                </div>
                <div class="stat-icon blue">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <h3>Chờ duyệt</h3>
                    <div class="number"><?php echo $pending_appointments; ?></div>
                </div>
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-bolt"></i> Thao tác nhanh</h2>
            </div>
            <div class="quick-actions">
                <a href="?tab=pets" class="action-card">
                    <i class="fas fa-paw"></i>
                    <h3>Quản lý thú cưng</h3>
                </a>
                <a href="dichvu.php" class="action-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-concierge-bell"></i>
                    <h3>Đặt dịch vụ</h3>
                </a>
                <a href="theo_doi_don_hang.php" class="action-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Theo dõi đơn hàng</h3>
                </a>
                <a href="lich_cham_soc.php" class="action-card" style="background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%);">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Lịch chăm sóc</h3>
                </a>
            </div>
        </div>

        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-calendar-alt"></i> Lịch hẹn gần đây</h2>
                <a href="theo_doi_don_hang.php" class="btn btn-outline">
                    <i class="fas fa-eye"></i> Xem tất cả
                </a>
            </div>
            <?php if (!empty($recent_appointments)): ?>
                <div class="appointments-list">
                    <?php 
                    $status_text = [
                        'cho_xac_nhan' => 'Chờ duyệt',
                        'da_xac_nhan' => 'Đã xác nhận',
                        'hoan_thanh' => 'Hoàn thành',
                        'da_huy' => 'Đã hủy'
                    ];
                    $status_class = [
                        'cho_xac_nhan' => 'pending',
                        'da_xac_nhan' => 'confirmed',
                        'hoan_thanh' => 'completed',
                        'da_huy' => 'cancelled'
                    ];
                    foreach ($recent_appointments as $apt): 
                    ?>
                        <div class="appointment-item">
                            <div class="appointment-info">
                                <div class="appointment-service"><?php echo htmlspecialchars($apt['ten_dich_vu'] ?? 'Dịch vụ'); ?></div>
                                <div class="appointment-details">
                                    <span><i class="fas fa-paw"></i> <?php echo htmlspecialchars($apt['ten_thu_cung'] ?? 'N/A'); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($apt['ngay_hen'])); ?></span>
                                    <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($apt['gio_hen'])); ?></span>
                                </div>
                            </div>
                            <span class="badge badge-<?php echo $status_class[$apt['trang_thai']] ?? 'pending'; ?>">
                                <?php echo $status_text[$apt['trang_thai']] ?? 'Chờ duyệt'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>Chưa có lịch hẹn nào</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-paw"></i> Thú cưng của bạn</h2>
                <a href="?tab=pets" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm thú cưng
                </a>
            </div>
            <?php if (!empty($pets)): ?>
                <div class="pets-grid">
                    <?php foreach ($pets as $pet): ?>
                        <div class="pet-card">
                            <img src="<?php echo htmlspecialchars($pet['hinh_anh']); ?>" 
                                 alt="<?php echo htmlspecialchars($pet['ten_thu_cung']); ?>" 
                                 class="pet-avatar">
                            <div class="pet-name"><?php echo htmlspecialchars($pet['ten_thu_cung']); ?></div>
                            <div class="pet-breed"><?php echo htmlspecialchars($pet['loai_thu_cung'] ?? 'N/A'); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-paw"></i>
                    <p>Chưa có thú cưng nào. Hãy thêm thú cưng đầu tiên của bạn!</p>
                    <a href="?tab=pets" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-plus"></i> Thêm thú cưng ngay
                    </a>
                </div>
            <?php endif; ?>
        </div>
        </div>

        <!-- Pets Management Tab -->
        <div class="tab-content <?php echo $current_tab === 'pets' ? 'active' : ''; ?>">
            <div class="pets-management">
                <div class="section-header">
                    <h2><i class="fas fa-paw"></i> Quản lý thú cưng của bạn</h2>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Thêm thú cưng mới
                    </button>
                </div>

                <?php if (!empty($all_pets)): ?>
                    <div class="pets-table-container">
                        <table class="pets-table">
                            <thead>
                                <tr>
                                    <th>Hình ảnh</th>
                                    <th>Tên</th>
                                    <th>Loại</th>
                                    <th>Giống</th>
                                    <th>Tuổi</th>
                                    <th>Cân nặng</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_pets as $pet): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo htmlspecialchars($pet['hinh_anh']); ?>" 
                                                 alt="<?php echo htmlspecialchars($pet['ten_thu_cung']); ?>" 
                                                 class="pet-image-small">
                                        </td>
                                        <td><?php echo htmlspecialchars($pet['ten_thu_cung']); ?></td>
                                        <td><?php echo htmlspecialchars($pet['loai_thu_cung'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($pet['giong'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($pet['tuoi'] ?? 'N/A'); ?> tuổi</td>
                                        <td><?php echo htmlspecialchars($pet['can_nang'] ?? 'N/A'); ?> kg</td>
                                        <td>
                                            <?php
                                            $trang_thai_text = [0 => 'Không còn', 1 => 'Đang chăm sóc', 2 => 'Đã trả về'];
                                            echo $trang_thai_text[$pet['trang_thai']] ?? 'N/A';
                                            ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-edit" onclick='openEditModal(<?php echo json_encode($pet); ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-delete" onclick="deletePet(<?php echo $pet['id']; ?>, '<?php echo htmlspecialchars($pet['ten_thu_cung']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-paw"></i>
                        <p>Chưa có thú cưng nào. Hãy thêm thú cưng đầu tiên của bạn!</p>
                        <button class="btn btn-primary" style="margin-top: 20px;" onclick="openAddModal()">
                            <i class="fas fa-plus"></i> Thêm thú cưng ngay
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Appointments Tab -->
        <div class="tab-content <?php echo $current_tab === 'appointments' ? 'active' : ''; ?>">
            <div class="section">
                <div class="section-header">
                    <h2><i class="fas fa-calendar-alt"></i> Lịch hẹn của tôi</h2>
                    <a href="dichvu.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Đặt lịch mới
                    </a>
                </div>
                <?php if (!empty($recent_appointments)): ?>
                    <div class="appointments-list">
                        <?php 
                        $status_text = [
                            'cho_xac_nhan' => 'Chờ duyệt',
                            'da_xac_nhan' => 'Đã xác nhận',
                            'hoan_thanh' => 'Hoàn thành',
                            'da_huy' => 'Đã hủy'
                        ];
                        $status_class = [
                            'cho_xac_nhan' => 'pending',
                            'da_xac_nhan' => 'confirmed',
                            'hoan_thanh' => 'completed',
                            'da_huy' => 'cancelled'
                        ];
                        foreach ($recent_appointments as $apt): 
                        ?>
                            <div class="appointment-item">
                                <div class="appointment-info">
                                    <div class="appointment-service"><?php echo htmlspecialchars($apt['ten_dich_vu'] ?? 'Dịch vụ'); ?></div>
                                    <div class="appointment-details">
                                        <span><i class="fas fa-paw"></i> <?php echo htmlspecialchars($apt['ten_thu_cung'] ?? 'N/A'); ?></span>
                                        <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($apt['ngay_hen'])); ?></span>
                                        <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($apt['gio_hen'])); ?></span>
                                    </div>
                                </div>
                                <span class="badge badge-<?php echo $status_class[$apt['trang_thai']] ?? 'pending'; ?>">
                                    <?php echo $status_text[$apt['trang_thai']] ?? 'Chờ duyệt'; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="theo_doi_don_hang.php" class="btn btn-outline">
                            <i class="fas fa-eye"></i> Xem tất cả lịch hẹn
                        </a>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>Chưa có lịch hẹn nào</p>
                        <a href="dichvu.php" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Đặt lịch ngay
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Pet Modal -->
    <div id="addPetModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-paw"></i> Thêm thú cưng mới</h2>
                <button class="close-modal" onclick="closeAddModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label><i class="fas fa-signature"></i> Tên thú cưng *</label>
                    <input type="text" name="ten_thu_cung" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-dog"></i> Loại *</label>
                        <select name="loai_thu_cung" required>
                            <option value="">-- Chọn loại --</option>
                            <option value="Chó">Chó</option>
                            <option value="Mèo">Mèo</option>
                            <option value="Chim">Chim</option>
                            <option value="Cá">Cá</option>
                            <option value="Hamster">Hamster</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-dna"></i> Giống</label>
                        <input type="text" name="giong">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-birthday-cake"></i> Tuổi</label>
                        <input type="number" name="tuoi" min="0" step="1">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Giới tính</label>
                        <select name="gioi_tinh">
                            <option value="">-- Chọn --</option>
                            <option value="Đực">Đực</option>
                            <option value="Cái">Cái</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-weight"></i> Cân nặng (kg)</label>
                        <input type="number" name="can_nang" min="0" step="0.1">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-palette"></i> Màu sắc</label>
                        <input type="text" name="mau_sac">
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-heartbeat"></i> Tình trạng sức khỏe</label>
                    <textarea name="tinh_trang_suc_khoe"></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-sticky-note"></i> Ghi chú</label>
                    <textarea name="ghi_chu"></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-image"></i> Hình ảnh</label>
                    <input type="file" name="hinh_anh" accept="image/*">
                </div>

                <button type="submit" name="add_pet" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-plus"></i> Thêm thú cưng
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Pet Modal -->
    <div id="editPetModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Chỉnh sửa thông tin thú cưng</h2>
                <button class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="pet_id" id="edit_pet_id">
                <input type="hidden" name="current_image" id="edit_current_image">
                
                <div class="form-group">
                    <label><i class="fas fa-signature"></i> Tên thú cưng *</label>
                    <input type="text" name="ten_thu_cung" id="edit_ten_thu_cung" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-dog"></i> Loại *</label>
                        <select name="loai_thu_cung" id="edit_loai_thu_cung" required>
                            <option value="">-- Chọn loại --</option>
                            <option value="Chó">Chó</option>
                            <option value="Mèo">Mèo</option>
                            <option value="Chim">Chim</option>
                            <option value="Cá">Cá</option>
                            <option value="Hamster">Hamster</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-dna"></i> Giống</label>
                        <input type="text" name="giong" id="edit_giong">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-birthday-cake"></i> Tuổi</label>
                        <input type="number" name="tuoi" id="edit_tuoi" min="0" step="1">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Giới tính</label>
                        <select name="gioi_tinh" id="edit_gioi_tinh">
                            <option value="">-- Chọn --</option>
                            <option value="Đực">Đực</option>
                            <option value="Cái">Cái</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-weight"></i> Cân nặng (kg)</label>
                        <input type="number" name="can_nang" id="edit_can_nang" min="0" step="0.1">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-palette"></i> Màu sắc</label>
                        <input type="text" name="mau_sac" id="edit_mau_sac">
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-heartbeat"></i> Tình trạng sức khỏe</label>
                    <textarea name="tinh_trang_suc_khoe" id="edit_tinh_trang_suc_khoe"></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-sticky-note"></i> Ghi chú</label>
                    <textarea name="ghi_chu" id="edit_ghi_chu"></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-image"></i> Hình ảnh mới (để trống nếu không đổi)</label>
                    <input type="file" name="hinh_anh" accept="image/*">
                </div>

                <button type="submit" name="edit_pet" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addPetModal').classList.add('active');
        }

        function closeAddModal() {
            document.getElementById('addPetModal').classList.remove('active');
        }

        function openEditModal(pet) {
            document.getElementById('edit_pet_id').value = pet.id;
            document.getElementById('edit_current_image').value = pet.hinh_anh;
            document.getElementById('edit_ten_thu_cung').value = pet.ten_thu_cung;
            document.getElementById('edit_loai_thu_cung').value = pet.loai_thu_cung || '';
            document.getElementById('edit_giong').value = pet.giong || '';
            document.getElementById('edit_tuoi').value = pet.tuoi || '';
            document.getElementById('edit_gioi_tinh').value = pet.gioi_tinh || '';
            document.getElementById('edit_can_nang').value = pet.can_nang || '';
            document.getElementById('edit_mau_sac').value = pet.mau_sac || '';
            document.getElementById('edit_tinh_trang_suc_khoe').value = pet.tinh_trang_suc_khoe || '';
            document.getElementById('edit_ghi_chu').value = pet.ghi_chu || '';
            
            document.getElementById('editPetModal').classList.add('active');
        }

        function closeEditModal() {
            document.getElementById('editPetModal').classList.remove('active');
        }

        function deletePet(id, name) {
            if (confirm(`Bạn có chắc chắn muốn xóa thú cưng "${name}"?`)) {
                window.location.href = `?delete_id=${id}&tab=pets`;
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addPetModal');
            const editModal = document.getElementById('editPetModal');
            
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        }
    </script>
</html>
