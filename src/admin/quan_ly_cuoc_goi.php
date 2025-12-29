<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 2) {
    header('Location: ../auth/login_update.php');
    exit();
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = $_POST['call_id'];
    $trang_thai = $_POST['trang_thai'];
    $ghi_chu = $_POST['ghi_chu_admin'] ?? '';
    
    $stmt = $conn->prepare("UPDATE yeu_cau_goi_dien SET trang_thai = ?, ghi_chu_admin = ?, nguoi_xu_ly_id = ?, thoi_gian_goi = NOW() WHERE id = ?");
    $stmt->execute([$trang_thai, $ghi_chu, $_SESSION['user_id'], $id]);
    
    $_SESSION['success'] = 'Cập nhật trạng thái thành công!';
    header('Location: quan_ly_cuoc_goi.php');
    exit();
}

// Lọc theo trạng thái
$filter = $_GET['filter'] ?? 'all';

$sql = "SELECT ygd.*, nd.ho_ten, nd.email 
        FROM yeu_cau_goi_dien ygd
        JOIN nguoi_dung nd ON ygd.nguoi_yeu_cau_id = nd.id";

if ($filter !== 'all') {
    $sql .= " WHERE ygd.trang_thai = " . intval($filter);
}

$sql .= " ORDER BY ygd.ngay_tao DESC";

$stmt = $conn->query($sql);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Thống kê
$stats = [
    'total' => $conn->query("SELECT COUNT(*) FROM yeu_cau_goi_dien")->fetchColumn(),
    'pending' => $conn->query("SELECT COUNT(*) FROM yeu_cau_goi_dien WHERE trang_thai = 0")->fetchColumn(),
    'called' => $conn->query("SELECT COUNT(*) FROM yeu_cau_goi_dien WHERE trang_thai = 1")->fetchColumn(),
    'failed' => $conn->query("SELECT COUNT(*) FROM yeu_cau_goi_dien WHERE trang_thai = 2")->fetchColumn(),
];

$status_text = [
    0 => 'Chờ gọi',
    1 => 'Đã gọi',
    2 => 'Không liên lạc được',
    3 => 'Đã hủy'
];

$status_class = [
    0 => 'warning',
    1 => 'success',
    2 => 'danger',
    3 => 'secondary'
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý yêu cầu tư vấn - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: #f5f7fa;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .nav-links {
            margin-top: 15px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
            opacity: 0.9;
        }

        .nav-links a:hover {
            opacity: 1;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            color: #333;
        }

        .filter-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 15px;
            text-align: left;
            background: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #e0e0e0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge.warning { background: #fff3cd; color: #856404; }
        .badge.success { background: #d4edda; color: #155724; }
        .badge.danger { background: #f8d7da; color: #721c24; }
        .badge.secondary { background: #e2e3e5; color: #383d41; }

        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-call {
            background: #28a745;
            color: white;
        }

        .btn-view {
            background: #4facfe;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
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
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-headset"></i> Quản lý yêu cầu tư vấn</h1>
        <p>Quản lý yêu cầu gọi điện tư vấn từ khách hàng</p>
        <div class="nav-links">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="quan_ly_chat.php"><i class="fas fa-comments"></i> Chat</a>
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Trang chủ</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Tổng yêu cầu</h3>
            <div class="number"><?php echo $stats['total']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Chờ gọi</h3>
            <div class="number" style="color: #ff9800;"><?php echo $stats['pending']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Đã gọi</h3>
            <div class="number" style="color: #28a745;"><?php echo $stats['called']; ?></div>
        </div>
        <div class="stat-card">
            <h3>Không liên lạc được</h3>
            <div class="number" style="color: #dc3545;"><?php echo $stats['failed']; ?></div>
        </div>
    </div>

    <div class="filter-section">
        <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
            <i class="fas fa-list"></i> Tất cả
        </a>
        <a href="?filter=0" class="filter-btn <?php echo $filter === '0' ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i> Chờ gọi
        </a>
        <a href="?filter=1" class="filter-btn <?php echo $filter === '1' ? 'active' : ''; ?>">
            <i class="fas fa-check"></i> Đã gọi
        </a>
        <a href="?filter=2" class="filter-btn <?php echo $filter === '2' ? 'active' : ''; ?>">
            <i class="fas fa-times"></i> Không liên lạc được
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Chủ đề</th>
                    <th>Thời gian mong muốn</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><strong>#<?php echo $req['id']; ?></strong></td>
                        <td>
                            <div><?php echo htmlspecialchars($req['ho_ten']); ?></div>
                            <small style="color: #999;"><?php echo htmlspecialchars($req['email']); ?></small>
                        </td>
                        <td>
                            <a href="tel:<?php echo $req['so_dien_thoai']; ?>" style="color: #667eea; text-decoration: none;">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($req['so_dien_thoai']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($req['chu_de']); ?></td>
                        <td>
                            <?php 
                            if ($req['thoi_gian_mong_muon']) {
                                echo date('d/m/Y H:i', strtotime($req['thoi_gian_mong_muon']));
                            } else {
                                echo '<span style="color: #999;">Càng sớm càng tốt</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $status_class[$req['trang_thai']]; ?>">
                                <?php echo $status_text[$req['trang_thai']]; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-call" onclick="updateStatus(<?php echo $req['id']; ?>, '<?php echo htmlspecialchars($req['ho_ten']); ?>', '<?php echo $req['so_dien_thoai']; ?>')">
                                <i class="fas fa-phone-alt"></i> Xử lý
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Update Modal -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-phone-alt"></i> Cập nhật trạng thái</h2>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
            </div>
            <div id="modalContent"></div>
        </div>
    </div>

    <script>
        function updateStatus(id, name, phone) {
            const content = `
                <p><strong>Khách hàng:</strong> ${name}</p>
                <p><strong>Số điện thoại:</strong> <a href="tel:${phone}">${phone}</a></p>
                <form method="POST">
                    <input type="hidden" name="call_id" value="${id}">
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="trang_thai" required>
                            <option value="1">Đã gọi thành công</option>
                            <option value="2">Không liên lạc được</option>
                            <option value="3">Khách hàng hủy</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="ghi_chu_admin" rows="3" placeholder="Ghi chú về cuộc gọi..."></textarea>
                    </div>
                    <button type="submit" name="update_status" class="btn btn-call" style="width: 100%;">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                </form>
            `;
            
            document.getElementById('modalContent').innerHTML = content;
            document.getElementById('updateModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('updateModal').classList.remove('active');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('updateModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
