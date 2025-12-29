<?php
/**
 * CAP NHAT HINH ANH DEP CHO THU CUNG
 * Truy cap: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/update_pet_images.php
 */

require_once '../config/connect.php';

echo "<h1>Cap Nhat Hinh Anh Thu Cung</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} img{max-width:300px;border-radius:10px;margin:10px 0;}</style>";

try {
    // Hinh anh dep tu Unsplash cho cac thu cung
    $pet_images = [
        // Cho Golden Retriever
        1 => 'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=800&q=80',
        
        // Meo Ba Tu
        2 => 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=800&q=80',
        
        // Cho Poodle
        3 => 'https://images.unsplash.com/photo-1623387641168-d9803ddd3f35?w=800&q=80',
        
        // Cho Corgi
        4 => 'https://images.unsplash.com/photo-1612536410073-a9720a5f1f8f?w=800&q=80',
        
        // Meo Anh Long Ngan
        5 => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=800&q=80',
        
        // Cho Husky
        6 => 'https://images.unsplash.com/photo-1568572933382-74d440642117?w=800&q=80',
        
        // Meo Xinh
        7 => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=800&q=80',
        
        // Cho Shiba
        8 => 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=800&q=80',
        
        // Meo Vang
        9 => 'https://images.unsplash.com/photo-1513360371669-4adf3dd7dff8?w=800&q=80',
        
        // Cho Beagle
        10 => 'https://images.unsplash.com/photo-1505628346881-b72b27e84530?w=800&q=80'
    ];
    
    echo "<h3>Dang cap nhat hinh anh...</h3>";
    
    $updated = 0;
    foreach ($pet_images as $pet_id => $image_url) {
        $sql = "UPDATE thu_cung SET hinh_anh = :hinh_anh WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            'hinh_anh' => $image_url,
            'id' => $pet_id
        ]);
        
        if ($result) {
            // Lay ten thu cung
            $sql_name = "SELECT ten_thu_cung, loai FROM thu_cung WHERE id = :id";
            $stmt_name = $conn->prepare($sql_name);
            $stmt_name->execute(['id' => $pet_id]);
            $pet = $stmt_name->fetch(PDO::FETCH_ASSOC);
            
            if ($pet) {
                echo "<div style='margin:15px 0;padding:15px;background:#f0f0f0;border-radius:8px;'>";
                echo "<p class='success'>✓ Da cap nhat: <b>{$pet['ten_thu_cung']}</b> ({$pet['loai']})</p>";
                echo "<img src='{$image_url}' alt='{$pet['ten_thu_cung']}'>";
                echo "</div>";
                $updated++;
            }
        }
    }
    
    echo "<hr><h2 class='success'>✓ HOAN TAT! Da cap nhat {$updated} thu cung</h2>";
    echo "<p><a href='index.php' style='background:purple;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>← Quay lai trang chu</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>✗ Loi: " . $e->getMessage() . "</p>";
}
?>
