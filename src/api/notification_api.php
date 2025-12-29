<?php
session_start();
require_once '../config/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'get_notifications':
            // Lấy danh sách thông báo
            $stmt = $conn->prepare("
                SELECT * FROM thong_bao 
                WHERE nguoi_nhan_id = ? 
                ORDER BY ngay_tao DESC 
                LIMIT 10
            ");
            $stmt->execute([$user_id]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'notifications' => $notifications
            ]);
            break;
            
        case 'get_unread_count':
            // Đếm số thông báo chưa đọc
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM thong_bao 
                WHERE nguoi_nhan_id = ? AND da_doc = 0
            ");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'count' => (int)$result['count']
            ]);
            break;
            
        case 'mark_as_read':
            // Đánh dấu đã đọc
            $notification_id = $_POST['notification_id'] ?? null;
            
            if ($notification_id) {
                // Đánh dấu 1 thông báo
                $stmt = $conn->prepare("
                    UPDATE thong_bao 
                    SET da_doc = 1 
                    WHERE id = ? AND nguoi_nhan_id = ?
                ");
                $stmt->execute([$notification_id, $user_id]);
            } else {
                // Đánh dấu tất cả
                $stmt = $conn->prepare("
                    UPDATE thong_bao 
                    SET da_doc = 1 
                    WHERE nguoi_nhan_id = ?
                ");
                $stmt->execute([$user_id]);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã đánh dấu đã đọc'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
