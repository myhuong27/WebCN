<?php
session_start();
require_once '../config/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'create_call_request':
            $so_dien_thoai = trim($_POST['so_dien_thoai'] ?? '');
            $chu_de = trim($_POST['chu_de'] ?? 'Tư vấn dịch vụ');
            $noi_dung = trim($_POST['noi_dung'] ?? '');
            $thoi_gian_mong_muon = $_POST['thoi_gian_mong_muon'] ?? null;
            
            if (empty($so_dien_thoai)) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng nhập số điện thoại']);
                exit;
            }
            
            $stmt = $conn->prepare("
                INSERT INTO yeu_cau_goi_dien 
                (nguoi_yeu_cau_id, so_dien_thoai, chu_de, noi_dung, thoi_gian_mong_muon, trang_thai) 
                VALUES (?, ?, ?, ?, ?, 0)
            ");
            $stmt->execute([$user_id, $so_dien_thoai, $chu_de, $noi_dung, $thoi_gian_mong_muon]);
            
            echo json_encode(['success' => true, 'message' => 'Đã gửi yêu cầu tư vấn thành công!']);
            break;
            
        case 'get_my_requests':
            $stmt = $conn->prepare("
                SELECT * FROM yeu_cau_goi_dien 
                WHERE nguoi_yeu_cau_id = ? 
                ORDER BY ngay_tao DESC
            ");
            $stmt->execute([$user_id]);
            $requests = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'requests' => $requests]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
