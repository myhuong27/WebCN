<?php
session_start();
require_once '../config/connect.php';

// Xử lý thêm thú cưng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pet'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Bạn cần đăng nhập để thêm thú cưng!';
        header('Location: ../auth/login_update.php');
        exit();
    }
    
    $ten_thu_cung = $_POST['ten_thu_cung'];
    $loai_thu_cung = $_POST['loai_thu_cung'];
    $giong = $_POST['giong'];
    $tuoi = $_POST['tuoi'];
    $gioi_tinh = $_POST['gioi_tinh'];
    $can_nang = $_POST['can_nang'];
    $mau_sac = $_POST['mau_sac'];
    $tinh_trang_suc_khoe = $_POST['tinh_trang_suc_khoe'];
    $ghi_chu = $_POST['mo_ta'] ?? '';
    $hinh_anh = $_POST['hinh_anh'];
    $chu_so_huu_id = $_SESSION['user_id'];
    
    try {
        // Tạo mã thú cưng tự động
        $stmt = $conn->query("SELECT MAX(CAST(SUBSTRING(ma_thu_cung, 3) AS UNSIGNED)) as max_id FROM thu_cung WHERE ma_thu_cung LIKE 'TC%'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_id = ($result['max_id'] ?? 0) + 1;
        $ma_thu_cung = 'TC' . str_pad($next_id, 3, '0', STR_PAD_LEFT);
        
        // Gộp giống vào tên hoặc ghi chú nếu cột giong không tồn tại
        $ten_full = $ten_thu_cung . ' (' . $giong . ')';
        
        $stmt = $conn->prepare("INSERT INTO thu_cung (ma_thu_cung, ten_thu_cung, loai_thu_cung, tuoi, gioi_tinh, can_nang, mau_sac, tinh_trang_suc_khoe, ghi_chu, hinh_anh, chu_so_huu_id, trang_thai, ngay_tao, ngay_tiep_nhan) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");
        $stmt->execute([$ma_thu_cung, $ten_full, $loai_thu_cung, $tuoi, $gioi_tinh, $can_nang, $mau_sac, $tinh_trang_suc_khoe, $ghi_chu, $hinh_anh, $chu_so_huu_id]);
        
        $_SESSION['success'] = 'Đã thêm thú cưng thành công!';
        header('Location: thucung.php');
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thú Cưng Đang Chăm Sóc - Pet Care Center</title>
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

        .brand-slogan {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .nav-menu {
            display: flex;
            gap: 30px;
            align-items: center;
            justify-content: center;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            padding: 10px 0;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nav-link i {
            font-size: 14px;
        }

        .nav-link:hover {
            color: #ff6b9d;
        }

        /* Search & Notification */
        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 8px 40px 8px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            width: 200px;
            font-size: 14px;
        }

        .search-box button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #ff6b9d 0%, #ffa07a 100%);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            cursor: pointer;
        }

        .notification-bell {
            position: relative;
            cursor: pointer;
            padding: 8px;
        }

        .notification-bell i {
            font-size: 22px;
            color: #ff6b9d;
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #e74c3c;
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .user-dropdown {
            position: relative;
        }

        .user-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 25px;
            cursor: pointer;
        }

        .user-trigger i {
            font-size: 18px;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 120px 20px 40px;
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

        .add-pet-btn {
            background: linear-gradient(135deg, #ff6b9d 0%, #ffa07a 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 107, 157, 0.3);
        }

        .add-pet-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 157, 0.4);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
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

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
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
            animation: modalSlide 0.3s ease;
        }

        @keyframes modalSlide {
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ff6b9d;
            box-shadow: 0 0 0 3px rgba(255, 107, 157, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #ff6b9d 0%, #ffa07a 100%);
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
            box-shadow: 0 5px 15px rgba(255, 107, 157, 0.4);
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-group label {
            font-weight: 500;
            color: #555;
        }

        .filter-group select {
            padding: 8px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .filter-group select:focus {
            border-color: #ff6b9d;
        }

        /* Pets Grid */
        .pets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .pet-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .pet-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .pet-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            background: #f0f0f0;
        }

        .pet-info {
            padding: 20px;
        }

        .pet-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .pet-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
            color: #666;
            margin-bottom: 15px;
        }

        .pet-detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pet-detail-item i {
            width: 20px;
            color: #ff6b9d;
        }

        .pet-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-1 {
            background: #4caf50;
            color: white;
        }

        .status-2 {
            background: #2196f3;
            color: white;
        }

        .pet-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #ff6b9d;
            color: white;
        }

        .btn-primary:hover {
            background: #ff4081;
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #ddd;
        }

        /* No results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-results i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
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
                <a href="thucung.php" class="nav-link">Thú cưng</a>
                <a href="dichvu.php" class="nav-link">Dịch vụ</a>
                <a href="gui_nuoiho.php" class="nav-link">Gửi nuôi hộ</a>
                <a href="../index.php#lien-he" class="nav-link">Liên hệ</a>
                
                <!-- Search Box -->
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Tìm kiếm..." autocomplete="off" />
                    <button type="button" onclick="window.location.href='search.php'">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Notification Bell -->
                    <div class="notification-bell" onclick="alert('Chức năng thông báo đang phát triển')">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    
                    <div class="user-dropdown">
                        <div class="user-trigger">
                            <i class="fas fa-user-circle"></i>
                            <span><?php echo htmlspecialchars($_SESSION['ho_ten']); ?></span>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div>
                <h1 class="page-title" style="margin-bottom: 5px;">Thú Cưng Đang Chăm Sóc</h1>
                <p class="page-subtitle">Những bé cưng đang được chăm sóc tại trung tâm của chúng tôi</p>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <button class="add-pet-btn" onclick="openAddPetModal()">
                    <i class="fas fa-plus-circle"></i> Thêm thú cưng của bạn
                </button>
            <?php endif; ?>
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

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="filter-group">
                <label for="loai">Loại:</label>
                <select id="loai">
                    <option value="">Tất cả</option>
                    <option value="Chó">Chó</option>
                    <option value="Mèo">Mèo</option>
                    <option value="Chim">Chim</option>
                    <option value="Cá">Cá</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="gioi-tinh">Giới tính:</label>
                <select id="gioi-tinh">
                    <option value="">Tất cả</option>
                    <option value="Đực">Đực</option>
                    <option value="Cái">Cái</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="trang-thai">Trạng thái:</label>
                <select id="trang-thai">
                    <option value="">Tất cả</option>
                    <option value="1">Đang chăm sóc</option>
                    <option value="2">Đã trả về</option>
                </select>
            </div>
        </div>

        <!-- Pets Grid -->
        <div class="pets-grid">
            <?php
            require_once '../config/connect.php';
            
            try {
                // Lấy thú cưng của user đang đăng nhập
                $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                $sql = "SELECT * FROM thu_cung WHERE chu_so_huu_id = ? AND trang_thai = 1 ORDER BY ngay_tao DESC";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_id]);
                $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($pets) > 0) {
                    foreach ($pets as $pet) {
                        $status_class = $pet['trang_thai'] == 1 ? 'status-1' : 'status-2';
                        $status_text = $pet['trang_thai'] == 1 ? 'Đang chăm sóc' : 'Đã trả về';
                        
                        // Xử lý hình ảnh - ưu tiên URL, sau đó file local, cuối cùng placeholder
                        $image = 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400&h=300&fit=crop';
                        if (!empty($pet['hinh_anh'])) {
                            if (filter_var($pet['hinh_anh'], FILTER_VALIDATE_URL)) {
                                $image = $pet['hinh_anh'];
                            } elseif (file_exists('uploads/' . $pet['hinh_anh'])) {
                                $image = 'uploads/' . $pet['hinh_anh'];
                            }
                        }
                        ?>
                        <div class="pet-card">
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($pet['ten_thu_cung']); ?>" class="pet-image">
                            <div class="pet-info">
                                <h3 class="pet-name"><?php echo htmlspecialchars($pet['ten_thu_cung']); ?></h3>
                                <div class="pet-details">
                                    <div class="pet-detail-item">
                                        <i class="fas fa-tag"></i>
                                        <span><?php echo htmlspecialchars($pet['loai_thu_cung']); ?> - <?php echo htmlspecialchars($pet['giong']); ?></span>
                                    </div>
                                    <div class="pet-detail-item">
                                        <i class="fas fa-birthday-cake"></i>
                                        <span><?php echo htmlspecialchars($pet['tuoi']); ?> tuổi - <?php echo htmlspecialchars($pet['gioi_tinh']); ?></span>
                                    </div>
                                    <div class="pet-detail-item">
                                        <i class="fas fa-weight"></i>
                                        <span><?php echo htmlspecialchars($pet['can_nang']); ?> kg</span>
                                    </div>
                                    <div class="pet-detail-item">
                                        <i class="fas fa-palette"></i>
                                        <span><?php echo htmlspecialchars($pet['mau_sac']); ?></span>
                                    </div>
                                    <div class="pet-detail-item">
                                        <i class="fas fa-heartbeat"></i>
                                        <span><?php echo htmlspecialchars($pet['tinh_trang_suc_khoe']); ?></span>
                                    </div>
                                </div>
                                <span class="pet-status <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                <?php if (!empty($pet['ghi_chu'])) { ?>
                                    <p style="margin-top: 15px; color: #666; font-size: 14px;">
                                        <i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars($pet['ghi_chu']); ?>
                                    </p>
                                <?php } ?>
                                <div class="pet-actions">
                                    <a href="chitiet_thucung.php?id=<?php echo $pet['id']; ?>" class="btn btn-primary">Xem chi tiết</a>
                                    <a href="../booking/datlich.php?pet_id=<?php echo $pet['id']; ?>" class="btn btn-secondary">Đặt lịch</a>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="no-results">
                            <i class="fas fa-paw"></i>
                            <h3>Chưa có thú cưng nào</h3>
                            <p>Hiện tại chưa có thú cưng nào đang được chăm sóc tại trung tâm.</p>
                          </div>';
                }
            } catch(PDOException $e) {
                echo '<div class="no-results">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Có lỗi xảy ra</h3>
                        <p>Không thể tải danh sách thú cưng. Vui lòng thử lại sau.</p>
                      </div>';
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2025 Pet Care Center. All rights reserved.</p>
    </div>

    <!-- Modal Thêm Thú Cưng -->
    <div id="addPetModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Thêm Thú Cưng Của Bạn</h2>
                <span class="close" onclick="closeAddPetModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="ten_thu_cung">Tên thú cưng <span style="color: red;">*</span></label>
                    <input type="text" id="ten_thu_cung" name="ten_thu_cung" required placeholder="Nhập tên thú cưng">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="loai_thu_cung">Loại <span style="color: red;">*</span></label>
                        <select id="loai_thu_cung" name="loai_thu_cung" required>
                            <option value="">Chọn loại</option>
                            <option value="Chó">Chó</option>
                            <option value="Mèo">Mèo</option>
                            <option value="Chim">Chim</option>
                            <option value="Cá">Cá</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="giong">Giống <span style="color: red;">*</span></label>
                        <input type="text" id="giong" name="giong" required placeholder="Ví dụ: Golden Retriever">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tuoi">Tuổi <span style="color: red;">*</span></label>
                        <input type="number" id="tuoi" name="tuoi" required min="0" step="0.1" placeholder="Ví dụ: 2.5">
                    </div>
                    <div class="form-group">
                        <label for="gioi_tinh">Giới tính <span style="color: red;">*</span></label>
                        <select id="gioi_tinh" name="gioi_tinh" required>
                            <option value="">Chọn giới tính</option>
                            <option value="Đực">Đực</option>
                            <option value="Cái">Cái</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="can_nang">Cân nặng (kg) <span style="color: red;">*</span></label>
                        <input type="number" id="can_nang" name="can_nang" required min="0" step="0.1" placeholder="Ví dụ: 15.5">
                    </div>
                    <div class="form-group">
                        <label for="mau_sac">Màu sắc <span style="color: red;">*</span></label>
                        <input type="text" id="mau_sac" name="mau_sac" required placeholder="Ví dụ: Vàng">
                    </div>
                </div>

                <div class="form-group">
                    <label for="tinh_trang_suc_khoe">Tình trạng sức khỏe <span style="color: red;">*</span></label>
                    <input type="text" id="tinh_trang_suc_khoe" name="tinh_trang_suc_khoe" required placeholder="Ví dụ: Khỏe mạnh, đầy đủ tiêm chủng">
                </div>

                <div class="form-group">
                    <label for="mo_ta">Mô tả / Tính cách</label>
                    <textarea id="mo_ta" name="mo_ta" placeholder="Nhập mô tả hoặc tính cách của thú cưng"></textarea>
                </div>

                <div class="form-group">
                    <label for="hinh_anh">URL hình ảnh <span style="color: red;">*</span></label>
                    <input type="text" id="hinh_anh" name="hinh_anh" required placeholder="https://example.com/image.jpg">
                </div>

                <button type="submit" name="add_pet" class="btn-submit">
                    <i class="fas fa-save"></i> Thêm Thú Cưng
                </button>
            </form>
        </div>
    </div>

    <script>
        function openAddPetModal() {
            document.getElementById('addPetModal').style.display = 'block';
        }

        function closeAddPetModal() {
            document.getElementById('addPetModal').style.display = 'none';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('addPetModal');
            if (event.target == modal) {
                closeAddPetModal();
            }
        }

        // Filter functionality
        const filterInputs = document.querySelectorAll('.filter-section select');
        filterInputs.forEach(input => {
            input.addEventListener('change', filterPets);
        });

        function filterPets() {
            const loai = document.getElementById('loai').value;
            const gioiTinh = document.getElementById('gioi-tinh').value;
            const trangThai = document.getElementById('trang-thai').value;
            
            const petCards = document.querySelectorAll('.pet-card');
            
            petCards.forEach(card => {
                const petInfo = card.textContent;
                let showCard = true;
                
                if (loai && !petInfo.includes(loai)) {
                    showCard = false;
                }
                
                if (gioiTinh && !petInfo.includes(gioiTinh)) {
                    showCard = false;
                }
                
                card.style.display = showCard ? 'block' : 'none';
            });
        }
        
        function openAddPetModal() {
            document.getElementById('addPetModal').style.display = 'block';
        }

        function closeAddPetModal() {
            document.getElementById('addPetModal').style.display = 'none';
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('addPetModal');
            if (event.target == modal) {
                closeAddPetModal();
            }
        }
    </script>
    
    <!-- Modal Thêm Thú Cưng -->
    <div id="addPetModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Thêm Thú Cưng Của Bạn</h2>
                <span class="close" onclick="closeAddPetModal()">&times;</span>
            </div>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="ten_thu_cung">Tên thú cưng <span style="color: red;">*</span></label>
                    <input type="text" id="ten_thu_cung" name="ten_thu_cung" required placeholder="Nhập tên thú cưng">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="loai_thu_cung">Loại <span style="color: red;">*</span></label>
                        <select id="loai_thu_cung" name="loai_thu_cung" required>
                            <option value="">Chọn loại</option>
                            <option value="Chó">Chó</option>
                            <option value="Mèo">Mèo</option>
                            <option value="Chim">Chim</option>
                            <option value="Cá">Cá</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="giong">Giống <span style="color: red;">*</span></label>
                        <input type="text" id="giong" name="giong" required placeholder="Ví dụ: Golden Retriever">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="tuoi">Tuổi <span style="color: red;">*</span></label>
                        <input type="number" id="tuoi" name="tuoi" required min="0" step="0.1" placeholder="Ví dụ: 2.5">
                    </div>
                    <div class="form-group">
                        <label for="gioi_tinh">Giới tính <span style="color: red;">*</span></label>
                        <select id="gioi_tinh" name="gioi_tinh" required>
                            <option value="">Chọn giới tính</option>
                            <option value="Đực">Đực</option>
                            <option value="Cái">Cái</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="can_nang">Cân nặng (kg) <span style="color: red;">*</span></label>
                        <input type="number" id="can_nang" name="can_nang" required min="0" step="0.1" placeholder="Ví dụ: 15.5">
                    </div>
                    <div class="form-group">
                        <label for="mau_sac">Màu sắc <span style="color: red;">*</span></label>
                        <input type="text" id="mau_sac" name="mau_sac" required placeholder="Ví dụ: Vàng">
                    </div>
                </div>

                <div class="form-group">
                    <label for="tinh_trang_suc_khoe">Tình trạng sức khỏe <span style="color: red;">*</span></label>
                    <input type="text" id="tinh_trang_suc_khoe" name="tinh_trang_suc_khoe" required placeholder="Ví dụ: Khỏe mạnh, đầy đủ tiêm chủng">
                </div>

                <div class="form-group">
                    <label for="mo_ta">Mô tả / Tính cách</label>
                    <textarea id="mo_ta" name="mo_ta" placeholder="Nhập mô tả hoặc tính cách của thú cưng"></textarea>
                </div>

                <div class="form-group">
                    <label for="hinh_anh">URL hình ảnh <span style="color: red;">*</span></label>
                    <input type="text" id="hinh_anh" name="hinh_anh" required placeholder="https://example.com/image.jpg">
                </div>

                <button type="submit" name="add_pet" class="btn-submit">
                    <i class="fas fa-save"></i> Thêm Thú Cưng
                </button>
            </form>
        </div>
    </div>
<?php require_once '../includes/chat_widget.php'; ?>
</body>
</html>
