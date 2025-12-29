<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập và quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 2) {
    header('Location: ../login_update.php');
    exit;
}

// Xử lý các action
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    try {
        if ($action === 'approve') {
            // Duyệt lịch hẹn
            $stmt = $conn->prepare("UPDATE lich_hen SET trang_thai = 1 WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Đã duyệt lịch hẹn thành công!";
        } elseif ($action === 'delete') {
            // Xóa lịch hẹn
            $stmt = $conn->prepare("DELETE FROM lich_hen WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = "Đã xóa lịch hẹn thành công!";
        }
        header('Location: dashboard.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Có lỗi xảy ra: " . $e->getMessage();
    }
}

// Thống kê tổng quan
try {
    // Tổng số thú cưng
    $stmt = $conn->query("SELECT COUNT(*) as total FROM thu_cung");
    $total_pets = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Tổng số người dùng
    $stmt = $conn->query("SELECT COUNT(*) as total FROM nguoi_dung");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Tổng số dịch vụ
    $stmt = $conn->query("SELECT COUNT(*) as total FROM dich_vu");
    $total_services = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Lịch hẹn chờ xử lý
    $stmt = $conn->query("SELECT COUNT(*) as total FROM lich_hen WHERE trang_thai = 0");
    $pending_appointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Yêu cầu nuôi hộ chờ duyệt
    $stmt = $conn->query("SELECT COUNT(*) as total FROM yeu_cau_nuoi_ho WHERE trang_thai = 0");
    $pending_requests = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Doanh thu tháng này (từ bảng thanh_toan)
    $stmt = $conn->query("SELECT SUM(so_tien) as total FROM thanh_toan 
                          WHERE trang_thai = 'thanh_cong'
                          AND MONTH(ngay_thanh_toan) = MONTH(CURRENT_DATE())
                          AND YEAR(ngay_thanh_toan) = YEAR(CURRENT_DATE())");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $monthly_revenue = $result['total'] ?? 0;
    
    // Lịch hẹn hôm nay
    $stmt = $conn->query("SELECT COUNT(*) as total FROM lich_hen WHERE ngay_hen = CURDATE()");
    $today_appointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Thống kê thú cưng theo loại
    $stmt = $conn->query("SELECT loai_thu_cung, COUNT(*) as so_luong 
                          FROM thu_cung 
                          GROUP BY loai_thu_cung");
    $pets_by_type = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Dịch vụ được đặt nhiều nhất
    $stmt = $conn->query("SELECT dv.ten_dich_vu, COUNT(*) as so_lan 
                          FROM lich_hen lh
                          JOIN dich_vu dv ON lh.dich_vu_id = dv.id
                          GROUP BY lh.dich_vu_id
                          ORDER BY so_lan DESC
                          LIMIT 5");
    $top_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lịch hẹn gần đây
    $stmt = $conn->query("SELECT lh.*, nd.ho_ten, tc.ten_thu_cung, dv.ten_dich_vu
                          FROM lich_hen lh
                          LEFT JOIN nguoi_dung nd ON lh.khach_hang_id = nd.id
                          LEFT JOIN thu_cung tc ON lh.thu_cung_id = tc.id
                          LEFT JOIN dich_vu dv ON lh.dich_vu_id = dv.id
                          ORDER BY lh.ngay_tao DESC
                          LIMIT 10");
    $recent_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $error_message = "Lỗi: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pet Care Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 14px;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .menu-item:hover {
            background: rgba(255,255,255,0.1);
            padding-left: 30px;
        }

        .menu-item.active {
            background: rgba(255,255,255,0.2);
            border-left: 4px solid #fff;
        }

        .menu-item i {
            font-size: 18px;
            width: 25px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar h1 {
            font-size: 28px;
            color: #333;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: bold;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-info h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-info .number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }

        .stat-icon.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.green { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); }
        .stat-icon.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.purple { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }

        /* Charts */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .chart-card h3 {
            margin-bottom: 20px;
            color: #333;
        }

        .chart-card canvas {
            max-height: 300px !important;
            height: 300px !important;
        }

        /* Recent Activities */
        .recent-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .recent-section h3 {
            margin-bottom: 20px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-badge.completed {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="admin-container">
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
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Dashboard Tổng Quan</h1>
                <div class="user-info">
                    <div>
                        <strong><?php echo htmlspecialchars($_SESSION['ho_ten']); ?></strong>
                        <p style="font-size: 12px; color: #666;">Admin</p>
                    </div>
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['ho_ten'], 0, 1)); ?>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Thú cưng đang chăm sóc</h3>
                        <div class="number"><?php echo $total_pets; ?></div>
                    </div>
                    <div class="stat-icon blue">
                        <i class="fas fa-paw"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Tổng người dùng</h3>
                        <div class="number"><?php echo $total_users; ?></div>
                    </div>
                    <div class="stat-icon green">
                        <i class="fas fa-users"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Lịch hẹn chờ duyệt</h3>
                        <div class="number"><?php echo $pending_appointments; ?></div>
                    </div>
                    <div class="stat-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Doanh thu tháng này</h3>
                        <div class="number"><?php echo number_format($monthly_revenue, 0, ',', '.'); ?>đ</div>
                    </div>
                    <div class="stat-icon purple">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Thú cưng theo loại</h3>
                    <canvas id="petTypeChart"></canvas>
                </div>

                <div class="chart-card">
                    <h3><i class="fas fa-chart-bar"></i> Dịch vụ phổ biến</h3>
                    <canvas id="serviceChart"></canvas>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="recent-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3><i class="fas fa-calendar-check"></i> Lịch hẹn gần đây</h3>
                    <a href="quan_ly_lichhen.php?action=add" class="btn btn-primary" style="background: #28a745;">
                        <i class="fas fa-plus"></i> Thêm lịch hẹn
                    </a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Khách hàng</th>
                            <th>Thú cưng</th>
                            <th>Dịch vụ</th>
                            <th>Ngày hẹn</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_appointments)): ?>
                            <?php foreach ($recent_appointments as $apt): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($apt['ma_lich_hen']); ?></td>
                                    <td><?php echo htmlspecialchars($apt['ho_ten'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($apt['ten_thu_cung'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($apt['ten_dich_vu'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($apt['ngay_hen'])); ?></td>
                                    <td>
                                        <?php
                                        $status_class = ['pending', 'confirmed', 'in-progress', 'completed', 'cancelled'];
                                        $status_text = ['Chờ duyệt', 'Đã xác nhận', 'Đang thực hiện', 'Hoàn thành', 'Đã hủy'];
                                        echo '<span class="status-badge ' . $status_class[$apt['trang_thai']] . '">' . $status_text[$apt['trang_thai']] . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($apt['trang_thai'] == 0): ?>
                                            <a href="dashboard.php?action=approve&id=<?php echo $apt['id']; ?>" 
                                               class="btn btn-primary" 
                                               style="background: #28a745; margin-right: 5px;"
                                               onclick="return confirm('Xác nhận duyệt lịch hẹn này?')">
                                                <i class="fas fa-check"></i> Duyệt
                                            </a>
                                        <?php endif; ?>
                                        <a href="dashboard.php?action=delete&id=<?php echo $apt['id']; ?>" 
                                           class="btn btn-primary" 
                                           style="background: #dc3545;"
                                           onclick="return confirm('Bạn có chắc muốn xóa lịch hẹn này?')">
                                            <i class="fas fa-trash"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px;">
                                    Chưa có lịch hẹn nào
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Chart for pet types
        const petTypeCtx = document.getElementById('petTypeChart').getContext('2d');
        const petTypeChart = new Chart(petTypeCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($pets_by_type ? array_column($pets_by_type, 'loai_thu_cung') : []); ?>,
                datasets: [{
                    data: <?php echo json_encode($pets_by_type ? array_column($pets_by_type, 'so_luong') : []); ?>,
                    backgroundColor: [
                        '#667eea',
                        '#764ba2',
                        '#f093fb',
                        '#4facfe',
                        '#56ab2f'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Chart for top services
        const serviceCtx = document.getElementById('serviceChart').getContext('2d');
        const serviceChart = new Chart(serviceCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($top_services, 'ten_dich_vu')); ?>,
                datasets: [{
                    label: 'Số lần đặt',
                    data: <?php echo json_encode(array_column($top_services, 'so_lan')); ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
