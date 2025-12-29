<?php
/**
 * IMPORT DU LIEU MAU - Chay file nay de them bai viet va video
 * Truy cap: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/import_data.php
 */

require_once '../config/connect.php';

echo "<h1>Import Du Lieu Mau</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;}</style>";

try {
    // 1. Xoa du lieu cu
    echo "<h3>Buoc 1: Xoa du lieu cu...</h3>";
    $conn->exec("DELETE FROM bai_viet");
    $conn->exec("DELETE FROM video_huong_dan");
    echo "<p class='success'>✓ Da xoa du lieu cu</p>";
    
    // 2. Them bai viet
    echo "<h3>Buoc 2: Them 3 bai viet...</h3>";
    
    $bai_viet = [
        [
            'tieu_de' => '10 Dieu Can Biet Khi Nuoi Cho Lan Dau',
            'noi_dung' => 'Nuoi cho la mot trach nhiem lon va doi hoi su chuan bi ky luong. Ban can tim hieu ve giong cho phu hop voi khong gian song, thoi gian cham soc, va kha nang tai chinh cua minh. Cho can duoc tiem phong day du, an uong dinh duong, tap luyen deu dan va duoc kham suc khoe dinh ky.',
            'hinh_anh' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=600',
            'loai_thu_cung' => 'Cho'
        ],
        [
            'tieu_de' => 'Huong Dan Cham Soc Meo Con Tu 0-6 Thang Tuoi',
            'noi_dung' => 'Meo con trong giai doan 0-6 thang tuoi can duoc cham soc dac biet can than. Trong 4 tuan dau, meo con can duoc cho bu sua me hoac sua cong thuc chuyen dung. Tiem phong la rat quan trong - meo con can tiem vac-xin ba mui vao luc 6, 8 va 12 tuan tuoi.',
            'hinh_anh' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=600',
            'loai_thu_cung' => 'Meo'
        ],
        [
            'tieu_de' => 'Che Do Dinh Duong Khoa Hoc Cho Thu Cung',
            'noi_dung' => 'Dinh duong dong vai tro quyet dinh den suc khoe va tuoi tho cua thu cung. Cho can che do an giau protein tu thit, ca, trung ket hop voi rau cu va carbohydrate. Meo la dong vat an thit nen can luong protein cao hon.',
            'hinh_anh' => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=600',
            'loai_thu_cung' => 'Chung'
        ]
    ];
    
    foreach ($bai_viet as $bv) {
        $sql = "INSERT INTO bai_viet (tieu_de, noi_dung, hinh_anh, loai_thu_cung, trang_thai, ngay_tao) 
                VALUES (:tieu_de, :noi_dung, :hinh_anh, :loai_thu_cung, 1, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute($bv);
        echo "<p class='success'>✓ Da them: {$bv['tieu_de']}</p>";
    }
    
    // 3. Them video
    echo "<h3>Buoc 3: Them 3 video...</h3>";
    
    $videos = [
        [
            'tieu_de' => 'Cach Tam Cho Cho Dung Cach Tai Nha',
            'mo_ta' => 'Video huong dan chi tiet cach tam cho cho tai nha an toan va hieu qua.',
            'url_video' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'hinh_anh_thumbnail' => 'https://images.unsplash.com/photo-1600369671738-ffcaf6c5f3dc?w=600',
            'loai_thu_cung' => 'Cho',
            'thoi_luong' => '8:45',
            'luot_xem' => 1520
        ],
        [
            'tieu_de' => 'Huan Luyen Meo Di Ve Sinh Dung Cho',
            'mo_ta' => 'Huong dan huan luyen meo su dung khay ve sinh tu nhung ngay dau tien.',
            'url_video' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'hinh_anh_thumbnail' => 'https://images.unsplash.com/photo-1573865526739-10c1d3a1f0cc?w=600',
            'loai_thu_cung' => 'Meo',
            'thoi_luong' => '6:30',
            'luot_xem' => 2340
        ],
        [
            'tieu_de' => 'Cham Soc Rang Mieng Cho Thu Cung',
            'mo_ta' => 'Rang mieng khoe manh la nen tang suc khoe tong the.',
            'url_video' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            'hinh_anh_thumbnail' => 'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=600',
            'loai_thu_cung' => 'Chung',
            'thoi_luong' => '7:15',
            'luot_xem' => 980
        ]
    ];
    
    foreach ($videos as $v) {
        $sql = "INSERT INTO video_huong_dan (tieu_de, mo_ta, url_video, hinh_anh_thumbnail, loai_thu_cung, thoi_luong, luot_xem, trang_thai, ngay_tao) 
                VALUES (:tieu_de, :mo_ta, :url_video, :hinh_anh_thumbnail, :loai_thu_cung, :thoi_luong, :luot_xem, 1, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute($v);
        echo "<p class='success'>✓ Da them: {$v['tieu_de']}</p>";
    }
    
    // 4. Cap nhat hinh anh thu cung
    echo "<h3>Buoc 4: Cap nhat hinh anh thu cung...</h3>";
    $conn->exec("UPDATE thu_cung SET hinh_anh = 'https://images.unsplash.com/photo-1633722715463-d30f4f325e24?w=400' WHERE id = 1");
    $conn->exec("UPDATE thu_cung SET hinh_anh = 'https://images.unsplash.com/photo-1518791841217-8f162f1e1131?w=400' WHERE id = 2");
    $conn->exec("UPDATE thu_cung SET hinh_anh = 'https://images.unsplash.com/photo-1568572933382-74d440642117?w=400' WHERE id = 3");
    echo "<p class='success'>✓ Da cap nhat hinh anh thu cung</p>";
    
    // Thong ke
    echo "<hr><h2 class='success'>✓ HOAN TAT!</h2>";
    
    $count_bv = $conn->query("SELECT COUNT(*) FROM bai_viet")->fetchColumn();
    $count_video = $conn->query("SELECT COUNT(*) FROM video_huong_dan")->fetchColumn();
    
    echo "<p><strong>Tong bai viet:</strong> $count_bv</p>";
    echo "<p><strong>Tong video:</strong> $count_video</p>";
    
    echo "<hr>";
    echo "<p><a href='index.php' style='background:#667eea;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>→ Xem Trang Chu</a></p>";
    echo "<p><a href='blog.php' style='background:#4caf50;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>→ Xem Bai Viet</a></p>";
    echo "<p><a href='video.php' style='background:#ff9800;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>→ Xem Video</a></p>";
    
} catch(PDOException $e) {
    echo "<p class='error'>✗ LOI: " . $e->getMessage() . "</p>";
    echo "<p>Chi tiet: <pre>" . $e->getTraceAsString() . "</pre></p>";
}
?>
