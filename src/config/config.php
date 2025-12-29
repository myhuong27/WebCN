<?php
// Cấu hình kết nối database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'quan_ly_thu_cung'); // Database quản lý nuôi hộ và chăm sóc thú cưng

// Tạo kết nối
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Kiểm tra kết nối
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }
    
    // Đặt charset là utf8mb4
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}

// Cấu hình chung
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bật hiển thị lỗi trong môi trường phát triển
error_reporting(E_ALL);
ini_set('display_errors', 1);
?> 