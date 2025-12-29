<?php
// Widget chat floating cho khách hàng
if (!isset($_SESSION['user_id'])) {
    return; // Không hiển thị nếu chưa đăng nhập
}
?>

<!-- Nút gọi tư vấn riêng -->
<div id="callButton" onclick="toggleCallForm()">
    <i class="fas fa-headset"></i>
</div>

<!-- Chat Widget -->
<div id="chatWidget">
    <div id="chatButton" onclick="toggleChat()">
        <i class="fas fa-comments"></i>
        <span id="unreadBadge" class="chat-badge" style="display: none;">0</span>
    </div>
    
    <div id="chatBox" style="display: none;">
        <div class="chat-header">
            <div class="chat-header-info">
                <i class="fas fa-headset"></i>
                <div>
                    <div class="chat-title">Hỗ trợ khách hàng</div>
                    <div class="chat-status">
                        <span class="status-dot"></span> Đang hoạt động
                    </div>
                </div>
            </div>
            <button onclick="toggleChat()" class="chat-close-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="chat-welcome">
                <i class="fas fa-robot"></i>
                <p>Xin chào! Chúng tôi có thể giúp gì cho bạn?</p>
            </div>
        </div>
        
        <!-- Form yêu cầu gọi điện -->
        <div class="call-request-form" id="callRequestForm">
            <button onclick="hideCallRequestForm()" class="btn-back-chat">
                <i class="fas fa-arrow-left"></i> Quay lại chat
            </button>
            <h3 style="margin-bottom: 15px; color: #667eea;">
                <i class="fas fa-phone-alt"></i> Yêu cầu tư vấn qua điện thoại
            </h3>
            <form id="formCallRequest" onsubmit="submitCallRequest(event)">
                <div class="form-group-call">
                    <label>Số điện thoại <span style="color: red;">*</span></label>
                    <input type="tel" name="so_dien_thoai" required placeholder="Nhập số điện thoại của bạn">
                </div>
                <div class="form-group-call">
                    <label>Chủ đề tư vấn</label>
                    <select name="chu_de">
                        <option value="Tư vấn dịch vụ">Tư vấn dịch vụ</option>
                        <option value="Tư vấn nuôi hộ">Tư vấn nuôi hộ thú cưng</option>
                        <option value="Tư vấn chăm sóc">Tư vấn chăm sóc</option>
                        <option value="Tư vấn giá">Tư vấn giá cả</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
                <div class="form-group-call">
                    <label>Thời gian mong muốn (tùy chọn)</label>
                    <input type="datetime-local" name="thoi_gian_mong_muon">
                </div>
                <div class="form-group-call">
                    <label>Nội dung cần tư vấn</label>
                    <textarea name="noi_dung" placeholder="Mô tả ngắn gọn về vấn đề bạn cần tư vấn..."></textarea>
                </div>
                <button type="submit" class="btn-submit-call">
                    <i class="fas fa-paper-plane"></i> Gửi yêu cầu
                </button>
            </form>
        </div>
        
        <div class="chat-input-container">
            <input type="text" id="chatInput" placeholder="Nhập tin nhắn..." onkeypress="handleChatKeypress(event)">
            <button onclick="sendMessage()" class="chat-send-btn">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
    
    <!-- Popup form yêu cầu gọi (riêng biệt) -->
    <div id="callPopup" style="display: none;">
        <div class="call-popup-content">
            <div class="call-popup-header">
                <h3><i class="fas fa-headset"></i> Yêu cầu gọi tư vấn</h3>
                <button onclick="toggleCallForm()" class="close-btn">&times;</button>
            </div>
            <form id="formCallRequest" onsubmit="submitCallRequest(event)">
                <div class="form-group-call">
                    <label>Số điện thoại <span style="color: red;">*</span></label>
                    <input type="tel" name="so_dien_thoai" required placeholder="Nhập số điện thoại của bạn">
                </div>
                <div class="form-group-call">
                    <label>Chủ đề tư vấn</label>
                    <select name="chu_de">
                        <option value="Tư vấn dịch vụ">Tư vấn dịch vụ</option>
                        <option value="Tư vấn nuôi hộ">Tư vấn nuôi hộ thú cưng</option>
                        <option value="Tư vấn chăm sóc">Tư vấn chăm sóc</option>
                        <option value="Tư vấn giá">Tư vấn giá cả</option>
                        <option value="Khác">Khác</option>
                    </select>
                </div>
                <div class="form-group-call">
                    <label>Thời gian mong muốn (tùy chọn)</label>
                    <input type="datetime-local" name="thoi_gian_mong_muon">
                </div>
                <div class="form-group-call">
                    <label>Nội dung cần tư vấn</label>
                    <textarea name="noi_dung" placeholder="Mô tả ngắn gọn về vấn đề bạn cần tư vấn..."></textarea>
                </div>
                <button type="submit" class="btn-submit-call">
                    <i class="fas fa-paper-plane"></i> Gửi yêu cầu
                </button>
            </form>
        </div>
    </div>
</div>

<style>
/* Nút gọi tư vấn - độc lập hoàn toàn */
#callButton {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(40, 167, 69, 0.4);
    transition: transform 0.3s;
    position: fixed;
    bottom: 100px;
    right: 20px;
    z-index: 10001;
    pointer-events: auto;
}

#callButton:hover {
    transform: scale(1.1);
}

#callButton i {
    font-size: 26px;
    pointer-events: none;
}

/* Chat widget */
#chatWidget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 10000;
    font-family: 'Segoe UI', sans-serif;
}

#chatButton {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
    transition: transform 0.3s;
    position: relative;
    pointer-events: auto;
}

#chatButton:hover {
    transform: scale(1.1);
}

#chatButton i {
    font-size: 24px;
    pointer-events: none;
}

.chat-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #f5576c;
    color: white;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    border: 2px solid white;
}

#chatBox {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    height: 500px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 40px rgba(0,0,0,0.16);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-header-info i {
    font-size: 24px;
}

.chat-title {
    font-weight: 600;
    font-size: 16px;
}

.chat-status {
    font-size: 12px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 5px;
}

.status-dot {
    width: 8px;
    height: 8px;
 

#callPopup {
    position: fixed;
    bottom: 180px;
    right: 20px;
    z-index: 10000;
    width: 400px;
    max-width: calc(100vw - 40px);
}

.call-popup-content {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 40px rgba(0,0,0,0.2);
    overflow: hidden;
}

.call-popup-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.call-popup-header h3 {
    margin: 0;
    font-size: 18px;
}

.call-popup-header .close-btn {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.call-popup-header .close-btn:hover {
    background: rgba(255,255,255,0.3);
}

#callPopup form {
    padding: 20px;
}   background: #4ade80;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.chat-call-btn,
.chat-close-btn {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
}

.chat-call-btn:hover,
.chat-close-btn:hover {
    background: rgba(255,255,255,0.3);
}

.call-request-form {
    padding: 20px;
    display: none;
}

.call-request-form.active {
    display: block;
}

.form-group-call {
    margin-bottom: 15px;
}

.form-group-call label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #333;
    font-size: 14px;
}

.form-group-call input,
.form-group-call textarea,
.form-group-call select {
    width: 100%;
    padding: 10px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
}

.form-group-call textarea {
    resize: vertical;
    min-height: 60px;
}

.btn-submit-call {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s;
}

.btn-submit-call:hover {
    transform: translateY(-2px);
}

.btn-back-chat {
    background: #f0f0f0;
    color: #666;
    padding: 8px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 15px;
    font-size: 14px;
}

.btn-back-chat:hover {
    background: #e0e0e0;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
    background: #f8f9fa;
}

.chat-welcome {
    text-align: center;
    padding: 30px 20px;
    color: #666;
}

.chat-welcome i {
    font-size: 48px;
    color: #667eea;
    margin-bottom: 15px;
}

.chat-message {
    margin-bottom: 15px;
    display: flex;
    gap: 10px;
}

.chat-message.user {
    flex-direction: row-reverse;
}

.chat-message .avatar {
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

.chat-message.admin .avatar {
    background: #764ba2;
}

.chat-message .message-content {
    max-width: 70%;
}

.chat-message .message-bubble {
    padding: 10px 15px;
    border-radius: 15px;
    background: white;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.chat-message.user .message-bubble {
    background: #667eea;
    color: white;
}

.chat-message .message-time {
    font-size: 11px;
    color: #999;
    margin-top: 5px;
    padding: 0 5px;
}

.chat-input-container {
    display: flex;
    padding: 15px;
    background: white;
    border-top: 1px solid #e0e0e0;
    gap: 10px;
}

#chatInput {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 25px;
    outline: none;
    font-size: 14px;
}

#chatInput:focus {
    border-color: #667eea;
}

.chat-send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s;
}

.chat-send-btn:hover {
    background: #5568d3;
}

@media (max-width: 480px) {
    #chatBox {
        width: calc(100vw - 40px);
        height: calc(100vh - 100px);
    }
}
</style>

<script>
let conversationId = null;
let lastMessageId = 0;
let chatInterval = null;

// Tự động detect đường dẫn API
const currentPath = window.location.pathname;
const apiPath = currentPath.includes('/pages/') ? '../api/chat_api.php' : 
                currentPath.includes('/user/') ? '../api/chat_api.php' : 
                'api/chat_api.php';

function toggleChat() {
    const chatBox = document.getElementById('chatBox');
    const isVisible = chatBox.style.display !== 'none';
    
    if (isVisible) {
        chatBox.style.display = 'none';
        if (chatInterval) {
            clearInterval(chatInterval);
            chatInterval = null;
        }
    } else {
        chatBox.style.display = 'flex';
        initChat();
    }
}

async function initChat() {
    if (!conversationId) {
        try {
            console.log('Đang khởi tạo chat...');
            const response = await fetch(apiPath + '?action=get_or_create_conversation');
            const data = await response.json();
            console.log('Kết quả khởi tạo:', data);
            
            if (data.success) {
                conversationId = data.conversation_id;
                console.log('Conversation ID:', conversationId);
                loadMessages();
                startPolling();
            } else {
                console.error('Lỗi khởi tạo chat:', data.message);
                alert('Không thể khởi tạo chat: ' + data.message);
            }
        } catch (error) {
            console.error('Lỗi fetch:', error);
            alert('Không thể kết nối server!');
        }
    } else {
        loadMessages();
        startPolling();
    }
}

function startPolling() {
    if (chatInterval) return;
    chatInterval = setInterval(loadMessages, 5000); // Poll mỗi 5 giây (đã giảm tần suất)
}

async function loadMessages() {
    if (!conversationId) {
        console.log('⚠️ Không có conversation ID');
        return;
    }
    
    try {
        const url = `${apiPath}?action=get_messages&conversation_id=${conversationId}&last_id=${lastMessageId}`;
        console.log('📡 Đang load messages từ:', url);
        
        const response = await fetch(url);
        const data = await response.json();
        
        console.log('📨 Response:', data);
        console.log('📊 Số tin nhắn:', data.messages ? data.messages.length : 0);
        
        if (data.success && data.messages && data.messages.length > 0) {
            const chatMessages = document.getElementById('chatMessages');
            
            // Xóa welcome message nếu có
            const welcomeMsg = chatMessages.querySelector('.chat-welcome');
            if (welcomeMsg) {
                console.log('🗑️ Xóa welcome message');
                welcomeMsg.remove();
            }
            
            data.messages.forEach((msg, index) => {
                console.log(`✉️ Tin nhắn ${index + 1}:`, msg);
                
                const messageDiv = document.createElement('div');
                messageDiv.className = `chat-message ${msg.is_admin ? 'admin' : 'user'}`;
                
                const initial = msg.ho_ten ? msg.ho_ten.charAt(0).toUpperCase() : 'U';
                const time = new Date(msg.created_at).toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'});
                
                messageDiv.innerHTML = `
                    <div class="avatar">${initial}</div>
                    <div class="message-content">
                        <div class="message-bubble">${escapeHtml(msg.message)}</div>
                        <div class="message-time">${time}</div>
                    </div>
                `;
                
                chatMessages.appendChild(messageDiv);
                lastMessageId = msg.id;
                console.log('✅ Đã thêm tin nhắn vào DOM, lastMessageId:', lastMessageId);
            });
            
            chatMessages.scrollTop = chatMessages.scrollHeight;
            console.log('⬇️ Scroll xuống bottom');
            
            // Reset unread badge
            document.getElementById('unreadBadge').style.display = 'none';
        } else {
            console.log('ℹ️ Không có tin nhắn mới hoặc API lỗi');
        }
    } catch (error) {
        console.error('❌ Lỗi load messages:', error);
    }
}

async function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (!message) {
        console.log('Tin nhắn trống');
        return;
    }
    
    if (!conversationId) {
        console.log('Chưa có conversation ID');
        await initChat();
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('conversation_id', conversationId);
        formData.append('message', message);
        
        console.log('Đang gửi tin nhắn:', message);
        
        const response = await fetch(apiPath + '?action=send_message', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        console.log('Kết quả gửi:', data);
        
        if (data.success) {
            input.value = '';
            // Load lại tin nhắn ngay lập tức để thấy tin vừa gửi
            setTimeout(() => loadMessages(), 100);
        } else {
            alert('Lỗi gửi tin nhắn: ' + data.message);
        }
    } catch (error) {
        console.error('Lỗi:', error);
        alert('Không thể gửi tin nhắn. Vui lòng kiểm tra kết nối!');
    }
}

function handleChatKeypress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Check unread messages
async function checkUnreadMessages() {
    const response = await fetch(apiPath + '?action=get_unread_count');
    const data = await response.json();
    
    if (data.success && data.count > 0) {
        const badge = document.getElementById('unreadBadge');
        badge.textContent = data.count;
        badge.style.display = 'flex';
    }
}

// Check unread mỗi 15 giây (đã giảm tần suất)
setInterval(checkUnreadMessages, 15000);
// Không tự động check khi load trang
// checkUnreadMessages();

// === CALL REQUEST FUNCTIONS ===
function showCallRequestForm() {
    document.getElementById('chatMessages').style.display = 'none';
    document.querySelector('.chat-input-container').style.display = 'none';
    document.getElementById('callRequestForm').classList.add('active');
}

function hideCallRequestForm() {
    document.getElementById('chatMessages').style.display = 'block';
    document.querySelector('.chat-input-container').style.display = 'flex';
    document.getElementById('callRequestForm').classList.remove('active');
}

async function submitCallRequest(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch(apiPath.replace('chat_api.php', 'call_api.php') + '?action=create_call_request', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('✅ ' + data.message + '\nChúng tôi sẽ liên hệ với bạn sớm nhất!');
            form.reset();
            hideCallRequestForm();
        } else {
            alert('❌ Lỗi: ' + data.message);
        }
    } catch (error) {
        console.error('Lỗi gửi yêu cầu:', error);
        alert('Không thể gửi yêu cầu. Vui lòng thử lại!');
    }
}

function toggleCallForm() {
    const popup = document.getElementById('callPopup');
    if (popup.style.display === 'none' || popup.style.display === '') {
        popup.style.display = 'block';
    } else {
        popup.style.display = 'none';
    }
}
</script>
