<?php
header('Content-Type: application/json');
require_once '../config/connect.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$suggestions = [];

if (strlen($query) >= 2) {
    try {
        $search_param = "%{$query}%";
        
        // Lấy gợi ý từ thú cưng
        $stmt = $conn->prepare("SELECT DISTINCT ten_thu_cung as text, 'pet' as type 
                               FROM thu_cung 
                               WHERE ten_thu_cung LIKE ? 
                               LIMIT 3");
        $stmt->execute([$search_param]);
        $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Lấy gợi ý từ loại thú cưng
        $stmt = $conn->prepare("SELECT DISTINCT loai_thu_cung as text, 'category' as type 
                               FROM thu_cung 
                               WHERE loai_thu_cung LIKE ? 
                               LIMIT 3");
        $stmt->execute([$search_param]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Lấy gợi ý từ dịch vụ
        $stmt = $conn->prepare("SELECT DISTINCT ten_dich_vu as text, 'service' as type 
                               FROM dich_vu 
                               WHERE ten_dich_vu LIKE ? 
                               LIMIT 3");
        $stmt->execute([$search_param]);
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Lấy gợi ý từ bài viết
        $stmt = $conn->prepare("SELECT DISTINCT tieu_de as text, 'blog' as type 
                               FROM bai_viet 
                               WHERE tieu_de LIKE ? 
                               LIMIT 3");
        $stmt->execute([$search_param]);
        $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Merge và giới hạn tổng số suggestions
        $suggestions = array_merge($pets, $categories, $services, $blogs);
        $suggestions = array_slice($suggestions, 0, 8);
        
    } catch(PDOException $e) {
        // Silent error
    }
}

echo json_encode($suggestions);
