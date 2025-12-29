<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 2) {
    header('Location: ../auth/login_update.php');
    exit();
}

// Xử lý duyệt yêu cầu
if (isset($_POST['duyet_yeu_cau'])) {
    $yeu_cau_id = $_POST['yeu_cau_id'];
    $trang_thai = $_POST['trang_thai']; // 1: Duyệt, 4: Từ chối
    $ly_do = $_POST['ly_do_tu_choi'] ?? '';
    
    try {
        if ($trang_thai == 4) {
            $stmt = $conn->prepare("UPDATE yeu_cau_nuoi_ho SET trang_thai = ?, ly_do_tu_choi = ? WHERE id = ?");
            $stmt->execute([$trang_thai, $ly_do, $yeu_cau_id]);
        } else {
            $stmt = $conn->prepare("UPDATE yeu_cau_nuoi_ho SET trang_thai = ? WHERE id = ?");
            $stmt->execute([$trang_thai, $yeu_cau_id]);
        }
        $_SESSION['success'] = 'Cập nhật trạng thái thành công!';
    } catch(PDOException $e) {
        $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
    }
    header('Location: quan_ly_nuoiho.php');
    exit();
}

// Lọc theo trạng thái
$filter_status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Thống kê
$stmt = $conn->query("SELECT COUNT(*) as total FROM yeu_cau_nuoi_ho");
$total_requests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM yeu_cau_nuoi_ho WHERE trang_thai = 0");
$pending_requests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM yeu_cau_nuoi_ho WHERE trang_thai = 1");
$approved_requests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $conn->query("SELECT COUNT(*) as total FROM yeu_cau_nuoi_ho WHERE trang_thai = 4");
$rejected_requests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Lấy danh sách yêu cầu
$sql = "SELECT ych.*, 
        nd_gui.ho_ten as nguoi_gui, nd_gui.email as email_gui,
        nd_nhan.ho_ten as nguoi_nhan,
        tc.ten_thu_cung, tc.loai_thu_cung, tc.hinh_anh
        FROM yeu_cau_nuoi_ho ych
        LEFT JOIN nguoi_dung nd_gui ON ych.nguoi_gui_id = nd_gui.id
        LEFT JOIN nguoi_dung nd_nhan ON ych.nguoi_nhan_id = nd_nhan.id
        LEFT JOIN thu_cung tc ON ych.thu_cung_id = tc.id";

if ($filter_status !== 'all') {
    $sql .= " WHERE ych.trang_thai = " . intval($filter_status);
}

$sql .= " ORDER BY ych.ngay_tao DESC";

$stmt = $conn->query($sql);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_text = [
    0 => 'Chờ duyệt',
    1 => 'Đã xác nhận',
    2 => 'Đang nuôi',
    3 => 'Hoàn thành',
    4 => 'Từ chối',
    5 => 'Hủy'
];

$status_class = [
    0 => 'warning',
    1 => 'success',
    2 => 'info',
    3 => 'complete',
    4 => 'danger',
    5 => 'secondary'
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Yêu cầu Nuôi hộ - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f5f7fa;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 270px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 0 25px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
            text-align: center;
        }

        .sidebar-header h2 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 13px;
            opacity: 0.8;
        }

        .sidebar-menu {
            display: flex;
            flex-direction: column;
        }

        .menu-item {
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .menu-item:hover, .menu-item.active {
            background: rgba(255,255,255,0.1);
            border-left-color: white;
        }

        .menu-item i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            margin-left: 270px;
            flex: 1;
            padding: 30px;
        }

        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s;
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
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }

        .stat-icon.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.green { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); }
        .stat-icon.red { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .filter-buttons {
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
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }

        .filter-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
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

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .pet-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pet-avatar {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge.warning { background: #fff3cd; color: #856404; }
        .badge.success { background: #d4edda; color: #155724; }
        .badge.info { background: #d1ecf1; color: #0c5460; }
        .badge.complete { background: #d4edda; color: #155724; }
        .badge.danger { background: #f8d7da; color: #721c24; }
        .badge.secondary { background: #e2e3e5; color: #383d41; }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

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

        .btn-view {
            background: #4facfe;
            color: white;
        }

        .btn-approve {
            background: #56ab2f;
            color: white;
        }

        .btn-reject {
            background: #f5576c;
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
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
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

        .detail-group {
            margin-bottom: 20px;
        }

        .detail-group label {
            display: block;
            font-weight: 600;
            color: #666;
            margin-bottom: 8px;
        }

        .detail-group .value {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
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

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-paw"></i> Pet Care</h2>
            <p>Admin Panel</p>
        </div>
        <?php include 'includes/sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-heart"></i> Quản lý Yêu cầu Nuôi hộ</h1>
            <p>Duyệt và theo dõi các yêu cầu nuôi hộ thú cưng</p>
        </div>

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

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <h3>Tổng yêu cầu</h3>
                <div class="number"><?php echo $total_requests; ?></div>
            </div>
            <div class="stat-icon purple">
                <i class="fas fa-list"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Chờ duyệt</h3>
                <div class="number"><?php echo $pending_requests; ?></div>
            </div>
            <div class="stat-icon orange">
                <i class="fas fa-clock"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Đã duyệt</h3>
                <div class="number"><?php echo $approved_requests; ?></div>
            </div>
            <div class="stat-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <h3>Từ chối</h3>
                <div class="number"><?php echo $rejected_requests; ?></div>
            </div>
            <div class="stat-icon red">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-buttons">
            <a href="?status=all" class="filter-btn <?php echo $filter_status === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Tất cả
            </a>
            <a href="?status=0" class="filter-btn <?php echo $filter_status === '0' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Chờ duyệt
            </a>
            <a href="?status=1" class="filter-btn <?php echo $filter_status === '1' ? 'active' : ''; ?>">
                <i class="fas fa-check"></i> Đã xác nhận
            </a>
            <a href="?status=2" class="filter-btn <?php echo $filter_status === '2' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Đang nuôi
            </a>
            <a href="?status=3" class="filter-btn <?php echo $filter_status === '3' ? 'active' : ''; ?>">
                <i class="fas fa-check-double"></i> Hoàn thành
            </a>
            <a href="?status=4" class="filter-btn <?php echo $filter_status === '4' ? 'active' : ''; ?>">
                <i class="fas fa-times"></i> Từ chối
            </a>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="table-container">
        <?php if (count($requests) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Mã YC</th>
                        <th>Thú cưng</th>
                        <th>Người gửi</th>
                        <th>Thời gian</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($req['ma_yeu_cau']); ?></strong></td>
                            <td>
                                <div class="pet-info">
                                    <img src="../<?php echo htmlspecialchars($req['hinh_anh']); ?>" 
                                         alt="<?php echo htmlspecialchars($req['ten_thu_cung']); ?>" 
                                         class="pet-avatar">
                                    <div>
                                        <div><strong><?php echo htmlspecialchars($req['ten_thu_cung']); ?></strong></div>
                                        <small><?php echo htmlspecialchars($req['loai_thu_cung']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($req['nguoi_gui']); ?></div>
                                <small style="color: #999;"><?php echo htmlspecialchars($req['email_gui']); ?></small>
                            </td>
                            <td>
                                <div><?php echo date('d/m/Y', strtotime($req['ngay_bat_dau'])); ?></div>
                                <small>đến</small>
                                <div><?php echo date('d/m/Y', strtotime($req['ngay_ket_thuc'])); ?></div>
                            </td>
                            <td>
                                <strong style="color: #667eea;">
                                    <?php echo number_format($req['gia_nuoi_ho'], 0, ',', '.'); ?>₫
                                </strong>
                            </td>
                            <td>
                                <span class="badge <?php echo $status_class[$req['trang_thai']]; ?>">
                                    <?php echo $status_text[$req['trang_thai']]; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-view" onclick='viewDetail(<?php echo json_encode($req); ?>)'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($req['trang_thai'] == 0): ?>
                                        <button class="btn btn-approve" onclick="approveRequest(<?php echo $req['id']; ?>)">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-reject" onclick="rejectRequest(<?php echo $req['id']; ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Không có yêu cầu nào</h3>
                <p>Chưa có yêu cầu nuôi hộ nào trong hệ thống</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Detail Modal -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-info-circle"></i> Chi tiết yêu cầu</h2>
                <button class="close-modal" onclick="closeDetailModal()">&times;</button>
            </div>
            <div id="detailContent"></div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-times-circle"></i> Từ chối yêu cầu</h2>
                <button class="close-modal" onclick="closeRejectModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="yeu_cau_id" id="reject_id">
                <input type="hidden" name="trang_thai" value="4">
                
                <div class="form-group">
                    <label>Lý do từ chối *</label>
                    <textarea name="ly_do_tu_choi" required placeholder="Nhập lý do từ chối yêu cầu này..."></textarea>
                </div>

                <button type="submit" name="duyet_yeu_cau" class="btn btn-reject" style="width: 100%;">
                    <i class="fas fa-times"></i> Từ chối yêu cầu
                </button>
            </form>
        </div>
    </div>

    <!-- Approve Form (hidden) -->
    <form id="approveForm" method="POST" style="display: none;">
        <input type="hidden" name="yeu_cau_id" id="approve_id">
        <input type="hidden" name="trang_thai" value="1">
        <input type="hidden" name="duyet_yeu_cau" value="1">
    </form>

    <script>
        function viewDetail(req) {
            const statusText = {
                0: 'Chờ duyệt',
                1: 'Đã xác nhận',
                2: 'Đang nuôi',
                3: 'Hoàn thành',
                4: 'Từ chối',
                5: 'Hủy'
            };

            const content = `
                <div class="detail-group">
                    <label>Mã yêu cầu</label>
                    <div class="value">${req.ma_yeu_cau}</div>
                </div>
                <div class="detail-group">
                    <label>Thú cưng</label>
                    <div class="value">${req.ten_thu_cung} - ${req.loai_thu_cung}</div>
                </div>
                <div class="detail-group">
                    <label>Người gửi yêu cầu</label>
                    <div class="value">${req.nguoi_gui}<br><small>${req.email_gui}</small></div>
                </div>
                <div class="detail-group">
                    <label>Thời gian nuôi hộ</label>
                    <div class="value">
                        Từ ${new Date(req.ngay_bat_dau).toLocaleDateString('vi-VN')}<br>
                        Đến ${new Date(req.ngay_ket_thuc).toLocaleDateString('vi-VN')}
                    </div>
                </div>
                <div class="detail-group">
                    <label>Địa điểm</label>
                    <div class="value">${req.dia_diem || 'Chưa cập nhật'}</div>
                </div>
                <div class="detail-group">
                    <label>Yêu cầu đặc biệt</label>
                    <div class="value">${req.yeu_cau_dac_biet || 'Không có'}</div>
                </div>
                <div class="detail-group">
                    <label>Giá nuôi hộ</label>
                    <div class="value"><strong style="color: #667eea; font-size: 18px;">${new Intl.NumberFormat('vi-VN').format(req.gia_nuoi_ho)}₫</strong></div>
                </div>
                <div class="detail-group">
                    <label>Trạng thái</label>
                    <div class="value">${statusText[req.trang_thai]}</div>
                </div>
                ${req.ly_do_tu_choi ? `
                <div class="detail-group">
                    <label>Lý do từ chối</label>
                    <div class="value" style="background: #f8d7da; color: #721c24;">${req.ly_do_tu_choi}</div>
                </div>
                ` : ''}
                <div class="detail-group">
                    <label>Ngày tạo</label>
                    <div class="value">${new Date(req.ngay_tao).toLocaleString('vi-VN')}</div>
                </div>
            `;

            document.getElementById('detailContent').innerHTML = content;
            document.getElementById('detailModal').classList.add('active');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.remove('active');
        }

        function approveRequest(id) {
            if (confirm('Bạn có chắc chắn muốn duyệt yêu cầu này?')) {
                document.getElementById('approve_id').value = id;
                document.getElementById('approveForm').submit();
            }
        }

        function rejectRequest(id) {
            document.getElementById('reject_id').value = id;
            document.getElementById('rejectModal').classList.add('active');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.remove('active');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const detailModal = document.getElementById('detailModal');
            const rejectModal = document.getElementById('rejectModal');
            
            if (event.target === detailModal) {
                closeDetailModal();
            }
            if (event.target === rejectModal) {
                closeRejectModal();
            }
        }
    </script>
    </div>
</body>
</html>
