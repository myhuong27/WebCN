<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login_update.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý thêm thú cưng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pet'])) {
    $ten_thu_cung = $_POST['ten_thu_cung'];
    $loai = $_POST['loai'];
    $giong = $_POST['giong'];
    $tuoi = $_POST['tuoi'];
    $gioi_tinh = $_POST['gioi_tinh'];
    $mau_sac = $_POST['mau_sac'];
    $can_nang = $_POST['can_nang'];
    $tinh_trang_suc_khoe = $_POST['tinh_trang_suc_khoe'];
    $ghi_chu = $_POST['ghi_chu'];
    
    // Xử lý upload hình ảnh
    $hinh_anh = '../images/image/default-pet.jpg';
    if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
        $target_dir = "images/image/";
        $file_extension = strtolower(pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION));
        $new_filename = 'pet_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_file)) {
            $hinh_anh = '../' . $target_file;
        }
    }
    
    try {
        // Tạo mã thú cưng tự động (TC + timestamp + random)
        $ma_thu_cung = 'TC' . date('YmdHis') . rand(100, 999);
        
        $stmt = $conn->prepare("INSERT INTO thu_cung (ma_thu_cung, chu_so_huu_id, ten_thu_cung, loai_thu_cung, giong, tuoi, gioi_tinh, mau_sac, can_nang, tinh_trang_suc_khoe, ghi_chu, hinh_anh, trang_thai, ngay_tao) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
        $stmt->execute([$ma_thu_cung, $user_id, $ten_thu_cung, $loai, $giong, $tuoi, $gioi_tinh, $mau_sac, $can_nang, $tinh_trang_suc_khoe, $ghi_chu, $hinh_anh]);
        $_SESSION['success'] = 'Thêm thú cưng thành công!';
        header('Location: quan_ly_thucung_user.php');
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
        header('Location: quan_ly_thucung_user.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}

// Lấy danh sách thú cưng của user
try {
    $stmt = $conn->prepare("SELECT * FROM thu_cung WHERE chu_so_huu_id = ? ORDER BY ngay_tao DESC");
    $stmt->execute([$user_id]);
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Thú cưng - Pet Care Center</title>
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

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            font-size: 32px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-edit {
            background: #ffc107;
            color: #333;
        }

        .btn-edit:hover {
            background: #e0a800;
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

        .pets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .pet-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }

        .pet-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .pet-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .pet-content {
            padding: 20px;
        }

        .pet-name {
            font-size: 22px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
        }

        .pet-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 15px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .info-row i {
            width: 25px;
            color: #667eea;
        }

        .info-label {
            color: #666;
            min-width: 80px;
        }

        .info-value {
            color: #333;
            font-weight: 500;
        }

        .pet-actions {
            display: flex;
            gap: 10px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideUp 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .modal-header h2 {
            font-size: 28px;
            color: #333;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 30px;
            color: #999;
            cursor: pointer;
            transition: all 0.3s;
        }

        .close-btn:hover {
            color: #333;
            transform: rotate(90deg);
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
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

        .empty-state h3 {
            font-size: 24px;
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 30px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
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
            <a href="../index.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-paw"></i>
                Quản lý Thú cưng của tôi
            </h1>
            <button class="btn btn-primary" onclick="openModal()">
                <i class="fas fa-plus"></i> Thêm thú cưng
            </button>
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

        <?php if (!empty($pets)): ?>
            <div class="pets-grid">
                <?php foreach ($pets as $pet): ?>
                    <div class="pet-card">
                        <img src="<?php echo htmlspecialchars(str_replace('../', '', $pet['hinh_anh'])); ?>" 
                             alt="<?php echo htmlspecialchars($pet['ten_thu_cung']); ?>" 
                             class="pet-image"
                             onerror="this.src='images/image/default-pet.jpg'">
                        <div class="pet-content">
                            <h3 class="pet-name"><?php echo htmlspecialchars($pet['ten_thu_cung']); ?></h3>
                            <div class="pet-info">
                                <div class="info-row">
                                    <i class="fas fa-tag"></i>
                                    <span class="info-label">Loại:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($pet['loai_thu_cung'] ?? 'Chưa rõ'); ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-dna"></i>
                                    <span class="info-label">Giống:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($pet['giong'] ?? 'Chưa rõ'); ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-birthday-cake"></i>
                                    <span class="info-label">Tuổi:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($pet['tuoi']); ?> tuổi</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-venus-mars"></i>
                                    <span class="info-label">Giới tính:</span>
                                    <span class="info-value"><?php echo $pet['gioi_tinh'] == 'Đực' ? '♂️ Đực' : '♀️ Cái'; ?></span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-weight"></i>
                                    <span class="info-label">Cân nặng:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($pet['can_nang']); ?> kg</span>
                                </div>
                                <div class="info-row">
                                    <i class="fas fa-heartbeat"></i>
                                    <span class="info-label">Sức khỏe:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($pet['tinh_trang_suc_khoe']); ?></span>
                                </div>
                            </div>
                            <div class="pet-actions">
                                <a href="?delete_id=<?php echo $pet['id']; ?>" 
                                   class="btn btn-danger"
                                   onclick="return confirm('Bạn có chắc muốn xóa thú cưng này?')"
                                   style="flex: 1; justify-content: center;">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-paw"></i>
                <h3>Chưa có thú cưng nào</h3>
                <p>Hãy thêm thú cưng đầu tiên của bạn!</p>
                <button class="btn btn-primary" onclick="openModal()">
                    <i class="fas fa-plus"></i> Thêm thú cưng ngay
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal thêm thú cưng -->
    <div id="addPetModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Thêm thú cưng mới</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tên thú cưng *</label>
                    <input type="text" name="ten_thu_cung" required placeholder="VD: Milu, Bông...">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Loại *</label>
                        <select name="loai" required>
                            <option value="">Chọn loại</option>
                            <option value="Chó">Chó</option>
                            <option value="Mèo">Mèo</option>
                            <option value="Chim">Chim</option>
                            <option value="Thỏ">Thỏ</option>
                            <option value="Hamster">Hamster</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Giống *</label>
                        <input type="text" name="giong" required placeholder="VD: Golden, Anh lông ngắn...">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Tuổi *</label>
                        <input type="number" name="tuoi" required min="0" step="0.5" placeholder="VD: 2">
                    </div>
                    
                    <div class="form-group">
                        <label>Giới tính *</label>
                        <select name="gioi_tinh" required>
                            <option value="">Chọn giới tính</option>
                            <option value="Đực">Đực</option>
                            <option value="Cái">Cái</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Màu sắc</label>
                        <input type="text" name="mau_sac" placeholder="VD: Vàng, Trắng...">
                    </div>
                    
                    <div class="form-group">
                        <label>Cân nặng (kg) *</label>
                        <input type="number" name="can_nang" required min="0" step="0.1" placeholder="VD: 5.5">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Tình trạng sức khỏe</label>
                    <input type="text" name="tinh_trang_suc_khoe" placeholder="VD: Khỏe mạnh, Đang điều trị...">
                </div>
                
                <div class="form-group">
                    <label>Ghi chú</label>
                    <textarea name="ghi_chu" placeholder="Thông tin thêm về thú cưng..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Hình ảnh</label>
                    <input type="file" name="hinh_anh" accept="image/*">
                </div>
                
                <button type="submit" name="add_pet" class="btn btn-primary" style="width: 100%; justify-content: center;">
                    <i class="fas fa-save"></i> Lưu thú cưng
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('addPetModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('addPetModal').classList.remove('active');
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('addPetModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
<?php require_once '../includes/chat_widget.php'; ?>
</body>
</html>
