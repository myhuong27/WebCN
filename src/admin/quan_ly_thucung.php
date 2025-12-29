<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 2) {
    header('Location: ../login_update.php');
    exit();
}

// Xử lý cập nhật trạng thái thú cưng
if (isset($_POST['update_status'])) {
    $pet_id = $_POST['pet_id'];
    $new_status = $_POST['new_status'];
    try {
        $stmt = $conn->prepare("UPDATE thu_cung SET trang_thai = ? WHERE id = ?");
        $stmt->execute([$new_status, $pet_id]);
        $_SESSION['success'] = 'Cập nhật trạng thái thành công!';
        header('Location: quan_ly_thucung.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Xử lý xóa thú cưng
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM thu_cung WHERE id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['success'] = 'Đã xóa thú cưng thành công!';
        header('Location: quan_ly_thucung.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Lọc và tìm kiếm
$filter_status = $_GET['status'] ?? 'all';
$search = $_GET['search'] ?? '';

// Lấy danh sách thú cưng với bộ lọc
try {
    $query = "SELECT tc.*, nd.ho_ten as chu_so_huu, nd.email as email_chu 
              FROM thu_cung tc 
              LEFT JOIN nguoi_dung nd ON tc.chu_so_huu_id = nd.id 
              WHERE 1=1";
    
    $params = [];
    
    if ($filter_status !== 'all') {
        $query .= " AND tc.trang_thai = ?";
        $params[] = $filter_status;
    }
    
    if (!empty($search)) {
        $query .= " AND (tc.ten_thu_cung LIKE ? OR tc.loai_thu_cung LIKE ? OR nd.ho_ten LIKE ?)";
        $search_param = "%$search%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    $query .= " ORDER BY tc.id DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Thống kê - Tối ưu bằng 1 query duy nhất
    $stats_query = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN trang_thai = 'dang_cham_soc' THEN 1 ELSE 0 END) as dang_cham_soc,
        SUM(CASE WHEN trang_thai = 'da_tra' THEN 1 ELSE 0 END) as da_tra,
        SUM(CASE WHEN trang_thai = 'cho_tiep_nhan' THEN 1 ELSE 0 END) as cho_tiep_nhan
        FROM thu_cung";
    $stats_result = $conn->query($stats_query)->fetch(PDO::FETCH_ASSOC);
    
    $stats = [
        'total' => $stats_result['total'] ?? 0,
        'dang_cham_soc' => $stats_result['dang_cham_soc'] ?? 0,
        'da_tra' => $stats_result['da_tra'] ?? 0,
        'cho_tiep_nhan' => $stats_result['cho_tiep_nhan'] ?? 0
    ];
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Thú cưng - Admin</title>
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
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            color: #666;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .pet-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
        }

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-active {
            background: #28a745;
            color: white;
        }

        .badge-inactive {
            background: #6c757d;
            color: white;
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
            gap: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
        .stat-icon.active { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.returned { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-icon.waiting { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }

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

        .btn-primary:hover {
            opacity: 0.9;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            display: inline-block;
        }

        .status-dang-cham-soc {
            background: #d4edda;
            color: #155724;
        }

        .status-da-tra {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-cho-tiep-nhan {
            background: #fff3cd;
            color: #856404;
        }

        .pet-detail {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
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
            <h1><i class="fas fa-paw"></i> Quản lý Thú cưng</h1>
            <p>Quản lý tất cả thú cưng đang chăm sóc tại trung tâm</p>
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
                    <i class="fas fa-paw"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total']; ?></h3>
                    <p>Tổng thú cưng</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon active">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['dang_cham_soc']; ?></h3>
                    <p>Đang chăm sóc</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon returned">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['da_tra']; ?></h3>
                    <p>Đã trả về</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon waiting">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['cho_tiep_nhan']; ?></h3>
                    <p>Chờ tiếp nhận</p>
                </div>
            </div>
        </div>

        <!-- Bộ lọc -->
        <div class="filter-bar">
            <form method="GET" class="filter-group">
                <select name="status" onchange="this.form.submit()">
                    <option value="all" <?php echo $filter_status === 'all' ? 'selected' : ''; ?>>Tất cả trạng thái</option>
                    <option value="dang_cham_soc" <?php echo $filter_status === 'dang_cham_soc' ? 'selected' : ''; ?>>Đang chăm sóc</option>
                    <option value="da_tra" <?php echo $filter_status === 'da_tra' ? 'selected' : ''; ?>>Đã trả về</option>
                    <option value="cho_tiep_nhan" <?php echo $filter_status === 'cho_tiep_nhan' ? 'selected' : ''; ?>>Chờ tiếp nhận</option>
                </select>
                <input type="search" name="search" placeholder="Tìm kiếm theo tên thú cưng, loại, chủ..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <?php if (!empty($search) || $filter_status !== 'all'): ?>
                    <a href="quan_ly_thucung.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Thông tin thú cưng</th>
                        <th>Chủ sở hữu</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pets)): ?>
                        <?php foreach ($pets as $pet): ?>
                            <tr>
                                <td><strong>#<?php echo $pet['id']; ?></strong></td>
                                <td>
                                    <?php if (!empty($pet['hinh_anh'])): ?>
                                        <img src="<?php echo htmlspecialchars($pet['hinh_anh']); ?>" 
                                             alt="<?php echo htmlspecialchars($pet['ten_thu_cung']); ?>" 
                                             class="pet-image"
                                             onerror="this.src='https://via.placeholder.com/60?text=Pet'">
                                    <?php else: ?>
                                        <div style="width:60px;height:60px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;border-radius:8px;">
                                            <i class="fas fa-paw" style="color:#ccc;font-size:24px;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="font-size: 15px;"><?php echo htmlspecialchars($pet['ten_thu_cung']); ?></strong>
                                    <div class="pet-detail">
                                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($pet['loai_thu_cung']); ?>
                                        <?php if (!empty($pet['tuoi'])): ?>
                                            | <i class="fas fa-birthday-cake"></i> <?php echo $pet['tuoi']; ?> tuổi
                                        <?php endif; ?>
                                        <?php if (!empty($pet['can_nang'])): ?>
                                            | <i class="fas fa-weight"></i> <?php echo $pet['can_nang']; ?> kg
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($pet['chu_so_huu'])): ?>
                                        <strong><?php echo htmlspecialchars($pet['chu_so_huu']); ?></strong>
                                        <?php if (!empty($pet['email_chu'])): ?>
                                            <div class="pet-detail">
                                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($pet['email_chu']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span style="color: #999;">Chưa có chủ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $status_class = 'status-' . $pet['trang_thai'];
                                    $status_text = [
                                        'dang_cham_soc' => 'Đang chăm sóc',
                                        'da_tra' => 'Đã trả về',
                                        'cho_tiep_nhan' => 'Chờ tiếp nhận'
                                    ];
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text[$pet['trang_thai']] ?? $pet['trang_thai']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <!-- Dropdown thay đổi trạng thái -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                                            <select name="new_status" onchange="if(confirm('Thay đổi trạng thái?')) this.form.submit();" class="btn btn-sm btn-info" style="padding: 6px 10px;">
                                                <option value="">Đổi trạng thái</option>
                                                <option value="dang_cham_soc">Đang chăm sóc</option>
                                                <option value="da_tra">Đã trả về</option>
                                                <option value="cho_tiep_nhan">Chờ tiếp nhận</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                        <a href="?delete_id=<?php echo $pet['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Bạn có chắc muốn xóa thú cưng này?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #999;">
                                <i class="fas fa-paw" style="font-size: 60px; margin-bottom: 15px; display: block; opacity: 0.3;"></i>
                                <p style="font-size: 16px;">
                                    <?php if (!empty($search)): ?>
                                        Không tìm thấy thú cưng nào
                                    <?php else: ?>
                                        Chưa có thú cưng nào trong hệ thống
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
