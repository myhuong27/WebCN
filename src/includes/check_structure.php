<?php
/**
 * KIEM TRA CAU TRUC DATABASE
 * Truy cap: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/check_structure.php
 */

require_once '../config/connect.php';

echo "<h1>Kiem Tra Cau Truc Database</h1>";
echo "<style>
    body{font-family:Arial;padding:20px;background:#f5f5f5;} 
    table{border-collapse:collapse;width:100%;margin:20px 0;background:white;}
    th,td{border:1px solid #ddd;padding:12px;text-align:left;}
    th{background:#667eea;color:white;}
    h2{color:#333;margin-top:30px;}
    .error{color:red;padding:10px;background:#fee;border-left:4px solid red;}
    .success{color:green;padding:10px;background:#e8f5e9;border-left:4px solid green;}
</style>";

try {
    // 1. Kiem tra bang bai_viet
    echo "<h2>1. Bang BAI_VIET:</h2>";
    $stmt = $conn->query("DESCRIBE bai_viet");
    $columns_bv = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columns_bv) > 0) {
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns_bv as $col) {
            echo "<tr>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>Bang bai_viet khong ton tai!</p>";
    }
    
    // 2. Kiem tra bang video_huong_dan
    echo "<h2>2. Bang VIDEO_HUONG_DAN:</h2>";
    $stmt = $conn->query("DESCRIBE video_huong_dan");
    $columns_video = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columns_video) > 0) {
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns_video as $col) {
            echo "<tr>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>Bang video_huong_dan khong ton tai!</p>";
    }
    
    // 3. Kiem tra bang thu_cung
    echo "<h2>3. Bang THU_CUNG:</h2>";
    $stmt = $conn->query("DESCRIBE thu_cung");
    $columns_tc = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columns_tc) > 0) {
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns_tc as $col) {
            echo "<tr>";
            echo "<td><strong>{$col['Field']}</strong></td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h2>KET LUAN:</h2>";
    
    // Kiem tra cac cot can thiet
    $bv_fields = array_column($columns_bv, 'Field');
    $video_fields = array_column($columns_video, 'Field');
    
    echo "<h3>Bai Viet - Cac cot hien tai:</h3>";
    echo "<p>" . implode(", ", $bv_fields) . "</p>";
    
    $required_bv = ['id', 'tieu_de', 'noi_dung', 'hinh_anh', 'trang_thai', 'ngay_dang'];
    $missing_bv = array_diff($required_bv, $bv_fields);
    
    if (count($missing_bv) > 0) {
        echo "<p class='error'>Thieu cac cot: " . implode(", ", $missing_bv) . "</p>";
    } else {
        echo "<p class='success'>✓ Cac cot co ban da day du!</p>";
    }
    
    if (in_array('loai_thu_cung', $bv_fields)) {
        echo "<p class='success'>✓ Co cot 'loai_thu_cung'</p>";
    } else {
        echo "<p class='error'>✗ KHONG co cot 'loai_thu_cung' - CAN THEM VAO!</p>";
        echo "<div style='background:#fffbea;padding:15px;border-left:4px solid #ff9800;margin:10px 0;'>";
        echo "<strong>Giai phap:</strong><br>";
        echo "Chay cau lenh SQL sau trong phpMyAdmin:<br><br>";
        echo "<code style='background:#f5f5f5;padding:10px;display:block;'>ALTER TABLE bai_viet ADD COLUMN loai_thu_cung VARCHAR(50) DEFAULT 'Chung' AFTER hinh_anh;</code>";
        echo "</div>";
    }
    
    echo "<h3>Video - Cac cot hien tai:</h3>";
    echo "<p>" . implode(", ", $video_fields) . "</p>";
    
} catch(PDOException $e) {
    echo "<p class='error'>LOI: " . $e->getMessage() . "</p>";
}
?>
