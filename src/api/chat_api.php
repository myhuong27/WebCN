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
        case 'get_or_create_conversation':
            // Lấy hoặc tạo conversation
            $stmt = $conn->prepare("SELECT id FROM chat_conversations WHERE user_id = ? AND status != 0 ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$user_id]);
            $conversation = $stmt->fetch();
            
            if (!$conversation) {
                $stmt = $conn->prepare("INSERT INTO chat_conversations (user_id, status, last_message_at) VALUES (?, 1, NOW())");
                $stmt->execute([$user_id]);
                $conversation_id = $conn->lastInsertId();
            } else {
                $conversation_id = $conversation['id'];
            }
            
            echo json_encode(['success' => true, 'conversation_id' => $conversation_id]);
            break;
            
        case 'send_message':
            $conversation_id = $_POST['conversation_id'] ?? 0;
            $message = trim($_POST['message'] ?? '');
            
            if (empty($message)) {
                echo json_encode(['success' => false, 'message' => 'Tin nhắn trống']);
                exit;
            }
            
            // Kiểm tra quyền sở hữu conversation
            $stmt = $conn->prepare("SELECT user_id FROM chat_conversations WHERE id = ?");
            $stmt->execute([$conversation_id]);
            $conv = $stmt->fetch();
            
            if (!$conv || $conv['user_id'] != $user_id) {
                echo json_encode(['success' => false, 'message' => 'Không có quyền']);
                exit;
            }
            
            // Thêm tin nhắn
            $is_admin = $_SESSION['vai_tro'] == 2 ? 1 : 0;
            $stmt = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_id, message, is_admin, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$conversation_id, $user_id, $message, $is_admin]);
            
            // Cập nhật thời gian conversation
            $stmt = $conn->prepare("UPDATE chat_conversations SET last_message_at = NOW(), status = 1 WHERE id = ?");
            $stmt->execute([$conversation_id]);
            
            echo json_encode(['success' => true, 'message' => 'Gửi thành công']);
            break;
            
        case 'get_messages':
            $conversation_id = $_GET['conversation_id'] ?? 0;
            $last_id = $_GET['last_id'] ?? 0;
            
            // Nếu last_id = 0, lấy TẤT CẢ tin nhắn
            if ($last_id == 0) {
                $stmt = $conn->prepare("
                    SELECT cm.*, nd.ho_ten
                    FROM chat_messages cm
                    LEFT JOIN nguoi_dung nd ON cm.sender_id = nd.id
                    WHERE cm.conversation_id = ?
                    ORDER BY cm.created_at ASC
                ");
                $stmt->execute([$conversation_id]);
            } else {
                // Chỉ lấy tin nhắn mới hơn
                $stmt = $conn->prepare("
                    SELECT cm.*, nd.ho_ten
                    FROM chat_messages cm
                    LEFT JOIN nguoi_dung nd ON cm.sender_id = nd.id
                    WHERE cm.conversation_id = ? AND cm.id > ?
                    ORDER BY cm.created_at ASC
                ");
                $stmt->execute([$conversation_id, $last_id]);
            }
            
            $messages = $stmt->fetchAll();
            
            // Đánh dấu đã đọc nếu là admin đang xem
            if ($_SESSION['vai_tro'] == 2) {
                $stmt = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE conversation_id = ? AND is_admin = 0 AND is_read = 0");
                $stmt->execute([$conversation_id]);
            }
            
            echo json_encode(['success' => true, 'messages' => $messages]);
            break;
            
        case 'get_unread_count':
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM chat_messages cm
                JOIN chat_conversations cc ON cm.conversation_id = cc.id
                WHERE cc.user_id = ? AND cm.is_admin = 1 AND cm.is_read = 0
            ");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
            
            echo json_encode(['success' => true, 'count' => $result['count']]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
