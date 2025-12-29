<?php
header('Content-Type: application/json');
require_once 'config/connect.php';

$keyword = $_GET['q'] ?? '';

if (strlen($keyword) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];

try {
    // Tìm bài viết
    $stmt = $conn->prepare("SELECT id, tieu_de, 'blog' as loai, hinh_anh 
                           FROM bai_viet 
                           WHERE trang_thai = 1 
                           AND (tieu_de LIKE ? OR noi_dung LIKE ?)
                           ORDER BY ngay_tao DESC
                           LIMIT 5");
    $searchTerm = "%$keyword%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($posts as $post) {
        $results[] = [
            'id' => $post['id'],
            'title' => $post['tieu_de'],
            'type' => 'blog',
            'icon' => 'newspaper',
            'url' => 'pages/blog_detail.php?id=' . $post['id']
        ];
    }
    
    // Tìm dịch vụ
    $stmt = $conn->prepare("SELECT id, ten_dich_vu, 'service' as loai 
                           FROM dich_vu 
                           WHERE trang_thai = 1 
                           AND (ten_dich_vu LIKE ? OR mo_ta LIKE ?)
                           LIMIT 5");
    $stmt->execute([$searchTerm, $searchTerm]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($services as $service) {
        $results[] = [
            'id' => $service['id'],
            'title' => $service['ten_dich_vu'],
            'type' => 'service',
            'icon' => 'concierge-bell',
            'url' => 'pages/dichvu.php#service-' . $service['id']
        ];
    }
    
} catch (PDOException $e) {
    // Không làm gì, trả về mảng rỗng
}

echo json_encode($results);
