<?php
session_start();
require_once '../config/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login_update.php');
    exit();
}

// Kiểm tra ID lịch hẹn
if (!isset($_GET['id'])) {
    header('Location: theo_doi_don_hang.php');
    exit();
}

$appointment_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Kiểm tra lịch hẹn có thuộc về user không
    $stmt = $conn->prepare("SELECT * FROM lich_hen WHERE id = ? AND nguoi_dung_id = ?");
    $stmt->execute([$appointment_id, $user_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        $_SESSION['error'] = 'Không tìm thấy lịch hẹn';
        header('Location: theo_doi_don_hang.php');
        exit();
    }
    
    // Chỉ cho phép hủy nếu đang ở trạng thái "Chờ duyệt"
    if ($appointment['trang_thai'] != 0) {
        $_SESSION['error'] = 'Không thể hủy lịch hẹn này';
        header('Location: theo_doi_don_hang.php');
        exit();
    }
    
    // Cập nhật trạng thái thành "Đã hủy"
    $stmt = $conn->prepare("UPDATE lich_hen SET trang_thai = 4 WHERE id = ?");
    $stmt->execute([$appointment_id]);
    
    $_SESSION['success'] = 'Đã hủy lịch hẹn thành công';
    
} catch(PDOException $e) {
    $_SESSION['error'] = 'Lỗi: ' . $e->getMessage();
}

header('Location: theo_doi_don_hang.php');
exit();
