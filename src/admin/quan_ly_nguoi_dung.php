<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 2) {
    header('Location: ../login_update.php');
    exit();
}

// Xử lý xóa người dùng
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM nguoi_dung WHERE id = ? AND vai_tro != 2");
        $stmt->execute([$delete_id]);
        $_SESSION['success'] = 'Đã xóa người dùng thành công!';
        header('Location: quan_ly_nguoi_dung.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Lấy danh sách người dùng
try {
    $stmt = $conn->query("SELECT * FROM nguoi_dung ORDER BY ngay_tao DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Người dùng - Admin</title>
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

        /* Sidebar */
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

        /* Main Content */
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

        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-admin {
            background: #667eea;
            color: white;
        }

        .badge-user {
            background: #28a745;
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

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background: #5568d3;
        }

        .actions {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="admin-header">
            <i class="fas fa-user-shield" style="font-size: 40px; margin-bottom: 10px;"></i>
            <h2>Admin Panel</h2>
            <p>Quản trị hệ thống</p>
        </div>
        <?php include 'includes/sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-users"></i> Quản lý Người dùng</h1>
            <p>Danh sách tất cả người dùng trong hệ thống</p>
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

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Vai trò</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['ho_ten']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['so_dien_thoai']); ?></td>
                                <td>
                                    <?php if ($user['vai_tro'] == 2): ?>
                                        <span class="badge badge-admin">Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-user">Người dùng</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['ngay_tao'])); ?></td>
                                <td>
                                    <div class="actions">
                                        <?php if ($user['vai_tro'] != 2): ?>
                                            <a href="?delete_id=<?php echo $user['id']; ?>" 
                                               class="btn btn-danger"
                                               onclick="return confirm('Bạn có chắc muốn xóa người dùng này?')">
                                                <i class="fas fa-trash"></i> Xóa
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                                <i class="fas fa-users" style="font-size: 50px; margin-bottom: 15px; display: block;"></i>
                                Chưa có người dùng nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
