<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['vai_tro'] != 2) {
    header('Location: ../auth/login_update.php');
    exit();
}

$admin_id = $_SESSION['user_id'];

// Lấy danh sách conversations
$selected_conversation = $_GET['conversation_id'] ?? null;

$stmt = $conn->query("
    SELECT 
        cc.*,
        nd.ho_ten,
        nd.email,
        (SELECT COUNT(*) FROM chat_messages WHERE conversation_id = cc.id AND is_admin = 0 AND is_read = 0) as unread_count,
        (SELECT message FROM chat_messages WHERE conversation_id = cc.id ORDER BY created_at DESC LIMIT 1) as last_message
    FROM chat_conversations cc
    JOIN nguoi_dung nd ON cc.user_id = nd.id
    ORDER BY cc.last_message_at DESC
");
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy messages của conversation được chọn
$messages = [];
if ($selected_conversation) {
    $stmt = $conn->prepare("
        SELECT cm.*, nd.ho_ten
        FROM chat_messages cm
        JOIN nguoi_dung nd ON cm.sender_id = nd.id
        WHERE cm.conversation_id = ?
        ORDER BY cm.created_at ASC
    ");
    $stmt->execute([$selected_conversation]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Đánh dấu đã đọc
    $stmt = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE conversation_id = ? AND is_admin = 0");
    $stmt->execute([$selected_conversation]);
}

// Xử lý gửi tin nhắn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $conversation_id = $_POST['conversation_id'];
    $message = trim($_POST['message']);
    
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_id, message, is_admin, created_at) VALUES (?, ?, ?, 1, NOW())");
        $stmt->execute([$conversation_id, $admin_id, $message]);
        
        $stmt = $conn->prepare("UPDATE chat_conversations SET last_message_at = NOW() WHERE id = ?");
        $stmt->execute([$conversation_id]);
        
        header("Location: quan_ly_chat.php?conversation_id=" . $conversation_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chat - Admin</title>
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
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            display: flex;
            height: 100vh;
        }

        .conversations-sidebar {
            width: 350px;
            background: white;
            border-right: 1px solid #e0e0e0;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
        }

        .sidebar-header h2 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 13px;
            opacity: 0.9;
        }

        .nav-links {
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .nav-links a {
            color: #667eea;
            text-decoration: none;
            margin-right: 15px;
            font-size: 14px;
        }

        .nav-links a:hover {
            text-decoration: underline;
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
        }

        .conversation-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background 0.2s;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .conversation-item:hover {
            background: #f8f9fa;
        }

        .conversation-item.active {
            background: #e8eaf6;
            border-left: 3px solid #667eea;
        }

        .conversation-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            flex-shrink: 0;
        }

        .conversation-info {
            flex: 1;
            min-width: 0;
        }

        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .conversation-name {
            font-weight: 600;
            font-size: 15px;
            color: #333;
        }

        .conversation-time {
            font-size: 12px;
            color: #999;
        }

        .conversation-preview {
            font-size: 13px;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .unread-badge {
            background: #f5576c;
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 10px;
        }

        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            background: white;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
        }

        .chat-user-details h3 {
            font-size: 18px;
            color: #333;
        }

        .chat-user-details p {
            font-size: 13px;
            color: #666;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }

        .message {
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
        }

        .message.admin {
            flex-direction: row-reverse;
        }

        .message-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .message.admin .message-avatar {
            background: #764ba2;
        }

        .message-content {
            max-width: 60%;
        }

        .message-bubble {
            padding: 12px 16px;
            border-radius: 15px;
            background: white;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .message.admin .message-bubble {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .message-time {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
            padding: 0 5px;
        }

        .chat-input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .chat-input-form {
            display: flex;
            gap: 10px;
        }

        .chat-input-form textarea {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            resize: none;
            outline: none;
            font-family: inherit;
            font-size: 14px;
            min-height: 45px;
            max-height: 150px;
        }

        .chat-input-form textarea:focus {
            border-color: #667eea;
        }

        .send-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }

        .send-btn:hover {
            transform: scale(1.05);
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="conversations-sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-comments"></i> Chat Support</h2>
                <p>Quản lý hỗ trợ khách hàng</p>
            </div>
            
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="quan_ly_cuoc_goi.php"><i class="fas fa-phone-volume"></i> Cuộc gọi</a>
                <a href="../index.php"><i class="fas fa-arrow-left"></i> Trang chủ</a>
            </div>

            <div class="conversations-list">
                <?php foreach ($conversations as $conv): ?>
                    <div class="conversation-item <?php echo $selected_conversation == $conv['id'] ? 'active' : ''; ?>"
                         onclick="location.href='quan_ly_chat.php?conversation_id=<?php echo $conv['id']; ?>'">
                        <div class="conversation-avatar">
                            <?php echo strtoupper(substr($conv['ho_ten'], 0, 1)); ?>
                        </div>
                        <div class="conversation-info">
                            <div class="conversation-header">
                                <span class="conversation-name"><?php echo htmlspecialchars($conv['ho_ten']); ?></span>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="unread-badge"><?php echo $conv['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="conversation-time">
                                <?php echo date('d/m/Y H:i', strtotime($conv['last_message_at'])); ?>
                            </div>
                            <div class="conversation-preview">
                                <?php echo htmlspecialchars(substr($conv['last_message'] ?? 'Chưa có tin nhắn', 0, 50)); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($conversations)): ?>
                    <div style="padding: 40px 20px; text-align: center; color: #999;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                        <p>Chưa có tin nhắn nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <?php if ($selected_conversation && !empty($messages)): ?>
                <?php
                // Lấy thông tin user
                $stmt = $conn->prepare("SELECT nd.* FROM chat_conversations cc JOIN nguoi_dung nd ON cc.user_id = nd.id WHERE cc.id = ?");
                $stmt->execute([$selected_conversation]);
                $chat_user = $stmt->fetch();
                ?>

                <div class="chat-header">
                    <div class="chat-user-info">
                        <div class="chat-user-avatar">
                            <?php echo strtoupper(substr($chat_user['ho_ten'], 0, 1)); ?>
                        </div>
                        <div class="chat-user-details">
                            <h3><?php echo htmlspecialchars($chat_user['ho_ten']); ?></h3>
                            <p><?php echo htmlspecialchars($chat_user['email']); ?></p>
                        </div>
                    </div>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($messages as $msg): ?>
                        <div class="message <?php echo $msg['is_admin'] ? 'admin' : 'user'; ?>">
                            <div class="message-avatar">
                                <?php echo strtoupper(substr($msg['ho_ten'], 0, 1)); ?>
                            </div>
                            <div class="message-content">
                                <div class="message-bubble">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </div>
                                <div class="message-time">
                                    <?php echo date('H:i d/m/Y', strtotime($msg['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="chat-input-area">
                    <form method="POST" class="chat-input-form" onsubmit="sendMessageAjax(event)">
                        <input type="hidden" name="conversation_id" value="<?php echo $selected_conversation; ?>">
                        <textarea name="message" id="messageInput" placeholder="Nhập tin nhắn..." rows="1" 
                                  oninput="autoResize(this)" onkeydown="handleKeyDown(event)"></textarea>
                        <button type="submit" name="send_message" class="send-btn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>Chọn một cuộc hội thoại</h3>
                    <p>Chọn một tin nhắn từ danh sách bên trái để bắt đầu</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto scroll to bottom
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Auto resize textarea
        function autoResize(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }

        // Handle Enter key
        function handleKeyDown(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                event.target.form.submit();
            }
        }

        // Validate message
        function validateMessage() {
            const message = document.getElementById('messageInput').value.trim();
            return message.length > 0;
        }

        // Send message via AJAX
        function sendMessageAjax(event) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message) return false;
            
            // Disable button
            const sendBtn = form.querySelector('.send-btn');
            sendBtn.disabled = true;
            
            fetch('quan_ly_chat.php?conversation_id=<?php echo $selected_conversation; ?>', {
                method: 'POST',
                body: formData
            })
            .then(() => {
                messageInput.value = '';
                messageInput.style.height = 'auto';
                sendBtn.disabled = false;
                
                // Reload page để hiển thị tin nhắn mới
                window.location.reload();
            })
            .catch(error => {
                console.error('Lỗi gửi tin nhắn:', error);
                sendBtn.disabled = false;
                alert('Không thể gửi tin nhắn!');
            });
        }

        // Auto scroll to bottom
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Auto refresh đã tắt để tránh load liên tục
        // Bấm F5 để refresh thủ công
    </script>
</body>
</html>
