<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 2) {
    header('Location: ../login_update.php');
    exit();
}

// Xử lý thêm bài viết mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_post'])) {
    $tieu_de = $_POST['tieu_de'];
    $tom_tat = $_POST['tom_tat'];
    $noi_dung = $_POST['noi_dung'];
    $hinh_anh = $_POST['hinh_anh'];
    $trang_thai = isset($_POST['trang_thai']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("INSERT INTO bai_viet (tieu_de, tom_tat, noi_dung, hinh_anh, trang_thai, ngay_tao) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$tieu_de, $tom_tat, $noi_dung, $hinh_anh, $trang_thai]);
        $_SESSION['success'] = 'Đã thêm bài viết mới thành công!';
        header('Location: quan_ly_baiviet.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Xử lý xóa bài viết
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM bai_viet WHERE id = ?");
        $stmt->execute([$delete_id]);
        $_SESSION['success'] = 'Đã xóa bài viết thành công!';
        header('Location: quan_ly_baiviet.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Lấy danh sách bài viết
try {
    $stmt = $conn->query("SELECT * FROM bai_viet ORDER BY ngay_tao DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bài viết - Admin</title>
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

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .post-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .post-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .post-content {
            padding: 20px;
        }

        .post-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .post-excerpt {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
            font-size: 13px;
            color: #999;
        }

        .post-date {
            display: flex;
            align-items: center;
            gap: 5px;
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

        .post-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
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
            flex: 1;
            justify-content: center;
        }

        .btn-view {
            background: #667eea;
            color: white;
        }

        .btn-view:hover {
            background: #5568d3;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
        }

        .empty-state i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .add-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            margin-bottom: 25px;
            cursor: pointer;
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
            max-width: 700px;
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
            min-height: 120px;
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
            <h1><i class="fas fa-newspaper"></i> Quản lý Bài viết</h1>
            <p>Danh sách tất cả bài viết trong hệ thống</p>
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
            Thêm bài viết mới
        </button>

        <?php if (!empty($posts)): ?>
            <div class="posts-grid">
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <img src="<?php echo htmlspecialchars($post['hinh_anh']); ?>" 
                             alt="<?php echo htmlspecialchars($post['tieu_de']); ?>" 
                             class="post-image">
                        <div class="post-content">
                            <h3 class="post-title"><?php echo htmlspecialchars($post['tieu_de']); ?></h3>
                            <p class="post-excerpt"><?php echo htmlspecialchars($post['tom_tat']); ?></p>
                            <div class="post-meta">
                                <div class="post-date">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y', strtotime($post['ngay_tao'])); ?>
                                </div>
                                <?php if ($post['trang_thai'] == 1): ?>
                                    <span class="badge badge-active">Hiển thị</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive">Ẩn</span>
                                <?php endif; ?>
                            </div>
                            <div class="post-actions">
                                <a href="../pages/blog_detail.php?id=<?php echo $post['id']; ?>" class="btn btn-view" target="_blank">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <a href="?delete_id=<?php echo $post['id']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Bạn có chắc muốn xóa bài viết này?')">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-newspaper"></i>
                <h3>Chưa có bài viết nào</h3>
                <p>Không có bài viết nào trong hệ thống</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Thêm Bài Viết -->
    <div id="addPostModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Thêm Bài Viết Mới</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="tieu_de">Tiêu đề <span style="color: red;">*</span></label>
                    <input type="text" id="tieu_de" name="tieu_de" required placeholder="Nhập tiêu đề bài viết">
                </div>

                <div class="form-group">
                    <label for="tom_tat">Tóm tắt <span style="color: red;">*</span></label>
                    <textarea id="tom_tat" name="tom_tat" required placeholder="Nhập tóm tắt ngắn gọn về bài viết"></textarea>
                </div>

                <div class="form-group">
                    <label for="noi_dung">Nội dung <span style="color: red;">*</span></label>
                    <textarea id="noi_dung" name="noi_dung" required placeholder="Nhập nội dung chi tiết bài viết" style="min-height: 200px;"></textarea>
                </div>

                <div class="form-group">
                    <label for="hinh_anh">URL hình ảnh <span style="color: red;">*</span></label>
                    <input type="text" id="hinh_anh" name="hinh_anh" required placeholder="../images/image/blog.jpg">
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="trang_thai" name="trang_thai" checked>
                        <label for="trang_thai" style="margin: 0;">Hiển thị bài viết</label>
                    </div>
                </div>

                <button type="submit" name="add_post" class="btn-submit">
                    <i class="fas fa-save"></i> Thêm Bài Viết
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('addPostModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addPostModal').style.display = 'none';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('addPostModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
