<?php
/**
 * Script nhắc lịch tự động
 * Chạy script này bằng cron job hoặc task scheduler hàng ngày
 * 
 * Trên Windows:
 * - Mở Task Scheduler
 * - Tạo Basic Task mới
 * - Chạy: C:\xampp\php\php.exe
 * - Arguments: C:\xampp\htdocs\csn-da22ttd-chauthimyhuong-webbanhang\src\cron\reminder_cron.php
 * - Trigger: Daily lúc 8:00 AM
 * 
 * Trên Linux/Mac:
 * crontab -e
 * 0 8 * * * /usr/bin/php /path/to/reminder_cron.php
 */

// Đường dẫn tương đối
$root_path = dirname(__DIR__);
require_once $root_path\ . '/config/connect.php';

// Hàm gửi email (có thể tích hợp PHPMailer sau)
function sendEmail($to, $subject, $message) {
    // Tạm thời log vào file thay vì gửi email thực
    $log_message = "[" . date('Y-m-d H:i:s') . "] Email to: $to\nSubject: $subject\nMessage: $message\n\n";
    file_put_contents(__DIR__ . '/email_log.txt', $log_message, FILE_APPEND);
    return true;
}

// Hàm thêm thông báo vào database
function createNotification($conn, $user_id, $type, $title, $content) {
    try {
        $stmt = $conn->prepare("INSERT INTO thong_bao (nguoi_dung_id, loai_thong_bao, tieu_de, noi_dung, trang_thai) 
                               VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$user_id, $type, $title, $content]);
        return true;
    } catch(PDOException $e) {
        echo "Lỗi tạo thông báo: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "========================================\n";
echo "Bắt đầu kiểm tra nhắc lịch: " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n\n";

try {
    // 1. Kiểm tra lịch tiêm phòng sắp đến hạn (7 ngày trước)
    echo "1. Kiểm tra lịch tiêm phòng...\n";
    $stmt = $conn->query("SELECT ltp.*, tc.ten_thu_cung, nd.ho_ten, nd.email, nd.id as user_id
                          FROM lich_tiem_phong ltp
                          JOIN thu_cung tc ON ltp.thu_cung_id = tc.id
                          JOIN nguoi_dung nd ON tc.chu_so_huu_id = nd.id
                          WHERE ltp.ngay_nhac_lai IS NOT NULL 
                          AND ltp.ngay_nhac_lai BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                          AND NOT EXISTS (
                              SELECT 1 FROM thong_bao tb 
                              WHERE tb.nguoi_dung_id = nd.id 
                              AND tb.loai_thong_bao = 'tiem-phong'
                              AND DATE(tb.ngay_tao) = CURDATE()
                          )");
    
    $vaccination_reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Tìm thấy " . count($vaccination_reminders) . " lịch tiêm phòng cần nhắc\n";
    
    foreach ($vaccination_reminders as $vac) {
        $days_left = (strtotime($vac['ngay_nhac_lai']) - strtotime(date('Y-m-d'))) / (60*60*24);
        $subject = "Nhắc lịch tiêm phòng cho " . $vac['ten_thu_cung'];
        $message = "Xin chào " . $vac['ho_ten'] . ",\n\n";
        $message .= "Đây là thông báo nhắc lịch tiêm phòng cho thú cưng " . $vac['ten_thu_cung'] . " của bạn.\n";
        $message .= "Loại vắc xin: " . $vac['loai_vac_xin'] . "\n";
        $message .= "Ngày cần tiêm: " . date('d/m/Y', strtotime($vac['ngay_nhac_lai'])) . "\n";
        $message .= "Còn " . ceil($days_left) . " ngày nữa.\n\n";
        $message .= "Vui lòng đặt lịch hẹn sớm để đảm bảo sức khỏe cho thú cưng.\n\n";
        $message .= "Trân trọng,\nPet Care Center";
        
        sendEmail($vac['email'], $subject, $message);
        createNotification($conn, $vac['user_id'], 'tiem-phong', $subject, 
                          "Thú cưng " . $vac['ten_thu_cung'] . " cần tiêm phòng " . $vac['loai_vac_xin'] . 
                          " vào ngày " . date('d/m/Y', strtotime($vac['ngay_nhac_lai'])));
        
        echo "   ✓ Đã nhắc: " . $vac['ho_ten'] . " - " . $vac['ten_thu_cung'] . " - " . $vac['loai_vac_xin'] . "\n";
    }
    
    // 2. Kiểm tra lịch hẹn hôm nay
    echo "\n2. Kiểm tra lịch hẹn hôm nay...\n";
    $stmt = $conn->query("SELECT lh.*, nd.ho_ten, nd.email, nd.id as user_id, tc.ten_thu_cung, dv.ten_dich_vu
                          FROM lich_hen lh
                          JOIN nguoi_dung nd ON lh.khach_hang_id = nd.id
                          LEFT JOIN thu_cung tc ON lh.thu_cung_id = tc.id
                          LEFT JOIN dich_vu dv ON lh.dich_vu_id = dv.id
                          WHERE lh.ngay_hen = CURDATE()
                          AND lh.trang_thai IN (0, 1)
                          AND NOT EXISTS (
                              SELECT 1 FROM thong_bao tb 
                              WHERE tb.nguoi_dung_id = nd.id 
                              AND tb.loai_thong_bao = 'lich-hen'
                              AND DATE(tb.ngay_tao) = CURDATE()
                          )");
    
    $today_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Tìm thấy " . count($today_appointments) . " lịch hẹn hôm nay\n";
    
    foreach ($today_appointments as $apt) {
        $subject = "Nhắc lịch hẹn hôm nay - " . $apt['ten_dich_vu'];
        $message = "Xin chào " . $apt['ho_ten'] . ",\n\n";
        $message .= "Đây là lịch hẹn của bạn hôm nay:\n";
        $message .= "Thú cưng: " . ($apt['ten_thu_cung'] ?? 'N/A') . "\n";
        $message .= "Dịch vụ: " . $apt['ten_dich_vu'] . "\n";
        $message .= "Giờ hẹn: " . $apt['gio_hen'] . "\n";
        $message .= "Mã lịch hẹn: " . $apt['ma_lich_hen'] . "\n\n";
        $message .= "Vui lòng đến đúng giờ. Nếu có thay đổi, xin vui lòng liên hệ với chúng tôi.\n\n";
        $message .= "Trân trọng,\nPet Care Center";
        
        sendEmail($apt['email'], $subject, $message);
        createNotification($conn, $apt['user_id'], 'lich-hen', $subject,
                          "Lịch hẹn " . $apt['ten_dich_vu'] . " lúc " . $apt['gio_hen'] . " hôm nay");
        
        echo "   ✓ Đã nhắc: " . $apt['ho_ten'] . " - " . $apt['ten_dich_vu'] . " - " . $apt['gio_hen'] . "\n";
    }
    
    // 3. Kiểm tra nhắc lịch tùy chỉnh
    echo "\n3. Kiểm tra nhắc lịch tùy chỉnh...\n";
    $stmt = $conn->query("SELECT nl.*, nd.ho_ten, nd.email, nd.id as user_id
                          FROM nhac_lich nl
                          JOIN nguoi_dung nd ON nl.nguoi_dung_id = nd.id
                          WHERE nl.ngay_nhac = CURDATE()
                          AND nl.trang_thai = 1
                          AND nl.da_gui = 0");
    
    $custom_reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Tìm thấy " . count($custom_reminders) . " nhắc lịch tùy chỉnh\n";
    
    foreach ($custom_reminders as $reminder) {
        $subject = $reminder['tieu_de'];
        $message = "Xin chào " . $reminder['ho_ten'] . ",\n\n";
        $message .= $reminder['noi_dung'] . "\n\n";
        $message .= "Trân trọng,\nPet Care Center";
        
        sendEmail($reminder['email'], $subject, $message);
        createNotification($conn, $reminder['user_id'], 'nhac-lich', $subject, $reminder['noi_dung']);
        
        // Đánh dấu đã gửi
        $update = $conn->prepare("UPDATE nhac_lich SET da_gui = 1, ngay_gui = NOW() WHERE id = ?");
        $update->execute([$reminder['id']]);
        
        echo "   ✓ Đã nhắc: " . $reminder['ho_ten'] . " - " . $reminder['tieu_de'] . "\n";
    }
    
    // 4. Kiểm tra yêu cầu nuôi hộ sắp bắt đầu (1 ngày trước)
    echo "\n4. Kiểm tra yêu cầu nuôi hộ sắp bắt đầu...\n";
    $stmt = $conn->query("SELECT yc.*, tc.ten_thu_cung, 
                          nd_gui.ho_ten as nguoi_gui, nd_gui.email as email_gui, nd_gui.id as user_gui_id,
                          nd_nhan.ho_ten as nguoi_nhan, nd_nhan.email as email_nhan, nd_nhan.id as user_nhan_id
                          FROM yeu_cau_nuoi_ho yc
                          JOIN thu_cung tc ON yc.thu_cung_id = tc.id
                          JOIN nguoi_dung nd_gui ON yc.nguoi_gui_id = nd_gui.id
                          LEFT JOIN nguoi_dung nd_nhan ON yc.nguoi_nhan_id = nd_nhan.id
                          WHERE yc.ngay_bat_dau = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                          AND yc.trang_thai >= 1
                          AND NOT EXISTS (
                              SELECT 1 FROM thong_bao tb 
                              WHERE (tb.nguoi_dung_id = nd_gui.id OR tb.nguoi_dung_id = nd_nhan.id)
                              AND tb.loai_thong_bao = 'nuoi-ho'
                              AND DATE(tb.ngay_tao) = CURDATE()
                          )");
    
    $boarding_reminders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Tìm thấy " . count($boarding_reminders) . " yêu cầu nuôi hộ sắp bắt đầu\n";
    
    foreach ($boarding_reminders as $boarding) {
        // Nhắc người gửi
        $subject_gui = "Nhắc lịch gửi thú cưng nuôi hộ - " . $boarding['ten_thu_cung'];
        $message_gui = "Xin chào " . $boarding['nguoi_gui'] . ",\n\n";
        $message_gui .= "Nhắc nhở: Bạn có lịch gửi thú cưng " . $boarding['ten_thu_cung'] . " nuôi hộ vào ngày mai.\n";
        if ($boarding['nguoi_nhan']) {
            $message_gui .= "Người nhận: " . $boarding['nguoi_nhan'] . "\n";
        }
        $message_gui .= "Vui lòng chuẩn bị đầy đủ đồ dùng cần thiết cho thú cưng.\n\n";
        $message_gui .= "Trân trọng,\nPet Care Center";
        
        sendEmail($boarding['email_gui'], $subject_gui, $message_gui);
        createNotification($conn, $boarding['user_gui_id'], 'nuoi-ho', $subject_gui,
                          "Lịch gửi " . $boarding['ten_thu_cung'] . " nuôi hộ vào ngày mai");
        
        // Nhắc người nhận (nếu có)
        if ($boarding['nguoi_nhan']) {
            $subject_nhan = "Nhắc lịch nhận thú cưng nuôi hộ - " . $boarding['ten_thu_cung'];
            $message_nhan = "Xin chào " . $boarding['nguoi_nhan'] . ",\n\n";
            $message_nhan .= "Nhắc nhở: Bạn có lịch nhận thú cưng " . $boarding['ten_thu_cung'] . " nuôi hộ vào ngày mai.\n";
            $message_nhan .= "Người gửi: " . $boarding['nguoi_gui'] . "\n";
            $message_nhan .= "Vui lòng chuẩn bị nơi ở và đồ dùng cho thú cưng.\n\n";
            $message_nhan .= "Trân trọng,\nPet Care Center";
            
            sendEmail($boarding['email_nhan'], $subject_nhan, $message_nhan);
            createNotification($conn, $boarding['user_nhan_id'], 'nuoi-ho', $subject_nhan,
                              "Lịch nhận " . $boarding['ten_thu_cung'] . " nuôi hộ vào ngày mai");
        }
        
        echo "   ✓ Đã nhắc nuôi hộ: " . $boarding['ten_thu_cung'] . "\n";
    }
    
    echo "\n========================================\n";
    echo "Hoàn thành kiểm tra nhắc lịch: " . date('Y-m-d H:i:s') . "\n";
    echo "Tổng cộng: \n";
    echo "  - Tiêm phòng: " . count($vaccination_reminders) . " nhắc\n";
    echo "  - Lịch hẹn: " . count($today_appointments) . " nhắc\n";
    echo "  - Nhắc lịch tùy chỉnh: " . count($custom_reminders) . " nhắc\n";
    echo "  - Nuôi hộ: " . count($boarding_reminders) . " nhắc\n";
    echo "========================================\n";
    
} catch(PDOException $e) {
    echo "LỖI: " . $e->getMessage() . "\n";
    // Log lỗi vào file
    file_put_contents(__DIR__ . '/error_log.txt', 
                     "[" . date('Y-m-d H:i:s') . "] " . $e->getMessage() . "\n\n", 
                     FILE_APPEND);
}
