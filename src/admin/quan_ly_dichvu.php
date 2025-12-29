<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 2) {
    header('Location: ../login_update.php');
    exit();
}

// Xử lý thêm dịch vụ mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_service'])) {
    $ten_dich_vu = $_POST['ten_dich_vu'];
    $mo_ta = $_POST['mo_ta'];
    $gia_dich_vu = $_POST['gia_dich_vu'];
    $hinh_anh = $_POST['hinh_anh'];
    $trang_thai = isset($_POST['trang_thai']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("INSERT INTO dich_vu (ten_dich_vu, mo_ta, gia_dich_vu, hinh_anh, trang_thai, ngay_tao) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$ten_dich_vu, $mo_ta, $gia_dich_vu, $hinh_anh, $trang_thai]);
        $_SESSION['success'] = 'Đã thêm dịch vụ mới thành công!';
        header('Location: quan_ly_dichvu.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Xử lý xóa dịch vụ
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM dich_vu WHERE id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['success'] = 'Đã xóa dịch vụ thành công!';
        header('Location: quan_ly_dichvu.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Lấy danh sách dịch vụ
try {
    $stmt = $conn->query("SELECT * FROM dich_vu ORDER BY ngay_tao DESC");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Dịch vụ - Admin</title>
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

        .service-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
        }

        .price {
            font-weight: 600;
            color: #667eea;
            font-size: 16px;
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

        .description {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .add-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            overflow-y: auto;
        }

        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .modal-header h2 {
            color: #333;
            font-size: 24px;
        }

        .close {
            color: #999;
            font-size: 28px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .close:hover {
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
            <h1><i class="fas fa-concierge-bell"></i> Quản lý Dịch vụ</h1>
            <p>Danh sách tất cả dịch vụ trong hệ thống</p>
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

        <button class="add-btn" onclick="openModal()">
            <i class="fas fa-plus-circle"></i>
            Thêm dịch vụ mới
        </button>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Hình ảnh</th>
                        <th>Tên dịch vụ</th>
                        <th>Mô tả</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $service): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($service['hinh_anh']); ?>" 
                                         alt="<?php echo htmlspecialchars($service['ten_dich_vu']); ?>" 
                                         class="service-image">
                                </td>
                                <td><strong><?php echo htmlspecialchars($service['ten_dich_vu']); ?></strong></td>
                                <td>
                                    <div class="description" title="<?php echo htmlspecialchars($service['mo_ta']); ?>">
                                        <?php echo htmlspecialchars($service['mo_ta']); ?>
                                    </div>
                                </td>
                                <td><span class="price"><?php echo number_format($service['gia_dich_vu'] ?? 0, 0, ',', '.'); ?>₫</span></td>
                                <td>
                                    <?php if ($service['trang_thai'] == 1): ?>
                                        <span class="badge badge-active">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge badge-inactive">Không hoạt động</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?delete_id=<?php echo $service['id']; ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Bạn có chắc muốn xóa dịch vụ này?')">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                                <i class="fas fa-concierge-bell" style="font-size: 50px; margin-bottom: 15px; display: block;"></i>
                                Chưa có dịch vụ nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Thêm Dịch Vụ -->
    <div id="addServiceModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Thêm Dịch Vụ Mới</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="ten_dich_vu">Tên dịch vụ <span style="color: red;">*</span></label>
                    <input type="text" id="ten_dich_vu" name="ten_dich_vu" required placeholder="Nhập tên dịch vụ">
                </div>

                <div class="form-group">
                    <label for="mo_ta">Mô tả</label>
                    <textarea id="mo_ta" name="mo_ta" placeholder="Nhập mô tả chi tiết về dịch vụ"></textarea>
                </div>

                <div class="form-group">
                    <label for="gia_dich_vu">Giá dịch vụ (VNĐ) <span style="color: red;">*</span></label>
                    <input type="number" id="gia_dich_vu" name="gia_dich_vu" required min="0" step="1000" placeholder="100000">
                </div>

                <div class="form-group">
                    <label for="hinh_anh">URL hình ảnh <span style="color: red;">*</span></label>
                    <input type="text" id="hinh_anh" name="hinh_anh" required placeholder="../images/image/service.jpg">
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="trang_thai" name="trang_thai" checked>
                        <label for="trang_thai" style="margin: 0;">Kích hoạt dịch vụ</label>
                    </div>
                </div>

                <button type="submit" name="add_service" class="btn-submit">
                    <i class="fas fa-save"></i> Thêm Dịch Vụ
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('addServiceModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addServiceModal').style.display = 'none';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('addServiceModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
