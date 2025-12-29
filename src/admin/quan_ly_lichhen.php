<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 2) {
    header('Location: ../login_update.php');
    exit();
}

// Xử lý cập nhật trạng thái lịch hẹn
if (isset($_POST['update_status'])) {
    $id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];
    try {
        $stmt = $conn->prepare("UPDATE dat_lich_dich_vu SET trang_thai = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        $_SESSION['success'] = 'Cập nhật trạng thái thành công!';
        header('Location: quan_ly_lichhen.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Xử lý xóa lịch hẹn
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM dat_lich_dich_vu WHERE id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['success'] = 'Đã xóa lịch hẹn thành công!';
        header('Location: quan_ly_lichhen.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Lọc theo trạng thái
$filter_status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Lấy danh sách lịch hẹn từ bảng dat_lich_dich_vu
try {
    $query = "SELECT dl.*, 
              nd.ho_ten, nd.so_dien_thoai, nd.email,
              dv.ten_dich_vu, dv.gia_dich_vu,
              tc.ten_thu_cung, tc.loai_thu_cung
              FROM dat_lich_dich_vu dl
              LEFT JOIN nguoi_dung nd ON dl.nguoi_dung_id = nd.id
              LEFT JOIN dich_vu dv ON dl.dich_vu_id = dv.id
              LEFT JOIN thu_cung tc ON dl.thu_cung_id = tc.id
              WHERE 1=1";
    
    $params = [];
    
    if ($filter_status !== 'all') {
        $query .= " AND dl.trang_thai = ?";
        $params[] = $filter_status;
    }
    
    if (!empty($search)) {
        $query .= " AND (dl.ma_dat_lich LIKE ? OR nd.ho_ten LIKE ? OR dv.ten_dich_vu LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $query .= " ORDER BY dl.ngay_dat_lich DESC, dl.gio_dat_lich DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Thống kê
    $stats = [
        'total' => $conn->query("SELECT COUNT(*) FROM dat_lich_dich_vu")->fetchColumn(),
        'cho_xac_nhan' => $conn->query("SELECT COUNT(*) FROM dat_lich_dich_vu WHERE trang_thai = 'cho_xac_nhan'")->fetchColumn(),
        'da_xac_nhan' => $conn->query("SELECT COUNT(*) FROM dat_lich_dich_vu WHERE trang_thai = 'da_xac_nhan'")->fetchColumn(),
        'hoan_thanh' => $conn->query("SELECT COUNT(*) FROM dat_lich_dich_vu WHERE trang_thai = 'hoan_thanh'")->fetchColumn(),
        'da_huy' => $conn->query("SELECT COUNT(*) FROM dat_lich_dich_vu WHERE trang_thai = 'da_huy'")->fetchColumn()
    ];
} catch(PDOException $e) {
    $error = $e->getMessage();
    $appointments = [];
    $stats = ['total' => 0, 'cho_xac_nhan' => 0, 'da_xac_nhan' => 0, 'hoan_thanh' => 0, 'da_huy' => 0];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Lịch hẹn - Admin</title>
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
            display: flex;
            background: #f5f7fa;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: white;
            position: fixed;
        }

        .admin-header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 30px;
        }

        .admin-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .menu-item.active {
            background: rgba(255,255,255,0.2);
        }

        .menu-item i {
            font-size: 18px;
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
        }

        .page-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
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

        .table-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            color: #666;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }

        .badge-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .badge-completed {
            background: #d4edda;
            color: #155724;
        }

        .badge-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .actions {
            display: flex;
            gap: 5px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .stat-icon.total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.pending { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .stat-icon.confirmed { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-icon.completed { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
        .stat-icon.cancelled { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }

        .stat-info h3 {
            font-size: 28px;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #777;
            font-size: 14px;
        }

        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group select,
        .filter-group input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .filter-group select {
            min-width: 200px;
        }

        .filter-group input[type="search"] {
            flex: 1;
            min-width: 250px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
        }

        .booking-detail {
            font-size: 12px;
            color: #999;
            margin-top: 3px;
        }

        .status-cho-xac-nhan {
            background: #fff3cd;
            color: #856404;
        }

        .status-da-xac-nhan {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-hoan-thanh {
            background: #d4edda;
            color: #155724;
        }

        .status-da-huy {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="admin-header">
            <i class="fas fa-user-shield" style="font-size: 40px; margin-bottom: 10px;"></i>
            <h2>Admin Panel</h2>
            <p>Quản trị hệ thống</p>
        </div>
        <?php include 'includes/sidebar.php'; ?>
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-calendar-alt"></i> Quản lý Lịch hẹn</h1>
            <p>Quản lý tất cả lịch đặt dịch vụ của khách hàng</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Thống kê -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Tổng lịch hẹn</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['cho_xac_nhan']; ?></h3>
                    <p>Chờ xác nhận</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon confirmed">
                    <i class="fas fa-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['da_xac_nhan']; ?></h3>
                    <p>Đã xác nhận</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon completed">
                    <i class="fas fa-check-double"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['hoan_thanh']; ?></h3>
                    <p>Hoàn thành</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon cancelled">
                    <i class="fas fa-times"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['da_huy']; ?></h3>
                    <p>Đã hủy</p>
                </div>
            </div>
        </div>

        <!-- Bộ lọc -->
        <div class="filter-bar">
            <form method="GET" class="filter-group">
                <select name="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                    <option value="cho_xac_nhan" <?php echo $filter_status === 'cho_xac_nhan' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                    <option value="da_xac_nhan" <?php echo $filter_status === 'da_xac_nhan' ? 'selected' : ''; ?>>Đã xác nhận</option>
                    <option value="hoan_thanh" <?php echo $filter_status === 'hoan_thanh' ? 'selected' : ''; ?>>Hoàn thành</option>
                    <option value="da_huy" <?php echo $filter_status === 'da_huy' ? 'selected' : ''; ?>>Đã hủy</option>
                </select>
                <input type="search" name="search" placeholder="Tìm theo mã lịch, tên khách, dịch vụ..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <?php if (!empty($search) || $filter_status !== 'all'): ?>
                    <a href="quan_ly_lichhen.php" class="btn" style="background:#6c757d;color:white;">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Mã đặt lịch</th>
                        <th>Khách hàng</th>
                        <th>Dịch vụ</th>
                        <th>Thú cưng</th>
                        <th>Ngày & Giờ</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($appointments)): ?>
                        <?php 
                        $status_labels = [
                            'cho_xac_nhan' => 'Chờ xác nhận',
                            'da_xac_nhan' => 'Đã xác nhận',
                            'hoan_thanh' => 'Hoàn thành',
                            'da_huy' => 'Đã hủy'
                        ];
                        
                        foreach ($appointments as $apt): 
                        ?>
                            <tr>
                                <td>
                                    <strong style="color:#667eea;"><?php echo htmlspecialchars($apt['ma_dat_lich'] ?? 'N/A'); ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($apt['ho_ten'] ?? 'N/A'); ?></strong>
                                    <?php if (!empty($apt['so_dien_thoai'])): ?>
                                        <div class="booking-detail">
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($apt['so_dien_thoai']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($apt['ten_dich_vu'] ?? 'N/A'); ?>
                                    <?php if (!empty($apt['gia_dich_vu'])): ?>
                                        <div class="booking-detail">
                                            <i class="fas fa-tag"></i> <?php echo number_format($apt['gia_dich_vu'], 0, ',', '.'); ?>₫
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($apt['ten_thu_cung'])): ?>
                                        <strong><?php echo htmlspecialchars($apt['ten_thu_cung']); ?></strong>
                                        <?php if (!empty($apt['loai_thu_cung'])): ?>
                                            <div class="booking-detail"><?php echo htmlspecialchars($apt['loai_thu_cung']); ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color:#999;">Chưa chọn</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo date('d/m/Y', strtotime($apt['ngay_dat_lich'])); ?></strong>
                                    <div class="booking-detail">
                                        <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($apt['gio_dat_lich'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $payment_method = [
                                        'tien_mat' => '💵 Tiền mặt',
                                        'chuyen_khoan' => '🏦 Chuyển khoản',
                                        'momo' => '📱 MoMo',
                                        'vnpay' => '💳 VNPay'
                                    ];
                                    echo $payment_method[$apt['phuong_thuc_thanh_toan']] ?? $apt['phuong_thuc_thanh_toan'];
                                    ?>
                                    <div class="booking-detail">
                                        <?php echo number_format($apt['tong_tien'], 0, ',', '.'); ?>₫
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $status = $apt['trang_thai'] ?? 'cho_xac_nhan';
                                    $status_class = 'status-' . $status;
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>">
                                        <?php echo $status_labels[$status] ?? $status; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="booking_id" value="<?php echo $apt['id']; ?>">
                                            <select name="new_status" onchange="if(confirm('Thay đổi trạng thái?')) this.form.submit();" 
                                                    class="btn btn-sm" style="background:#17a2b8;color:white;border:none;">
                                                <option value="">Đổi trạng thái</option>
                                                <option value="cho_xac_nhan">Chờ xác nhận</option>
                                                <option value="da_xac_nhan">Đã xác nhận</option>
                                                <option value="hoan_thanh">Hoàn thành</option>
                                                <option value="da_huy">Đã hủy</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                        <a href="?delete_id=<?php echo $apt['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Bạn có chắc muốn xóa lịch hẹn này?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-calendar-times" style="font-size: 60px; margin-bottom: 15px; display: block; opacity: 0.3;"></i>
                                <p style="font-size: 16px;">
                                    <?php if (!empty($search)): ?>
                                        Không tìm thấy lịch hẹn nào
                                    <?php else: ?>
                                        Chưa có lịch hẹn nào trong hệ thống
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
