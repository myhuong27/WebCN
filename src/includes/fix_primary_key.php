<?php
/**
 * SUA LOI PRIMARY KEY - Chay file nay truoc khi import du lieu
 * Truy cap: http://localhost/csn-da22ttd-chauthimyhuong-webbanhang/src/fix_primary_key.php
 */

require_once '../config/connect.php';

echo "<h1>Sua Loi Primary Key</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

try {
    // Buoc 1: Xoa toan bo du lieu
    echo "<h3>Buoc 1: Xoa du lieu cu...</h3>";
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
    $conn->exec("TRUNCATE TABLE bai_viet");
    $conn->exec("TRUNCATE TABLE video_huong_dan");
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p class='success'>✓ Da xoa sach du lieu cu</p>";
    
    // Buoc 2: Kiem tra va sua cot id
    echo "<h3>Buoc 2: Kiem tra AUTO_INCREMENT...</h3>";
    
    // Kiem tra bai_viet
    $result = $conn->query("SHOW COLUMNS FROM bai_viet WHERE Field = 'id'");
    $column = $result->fetch(PDO::FETCH_ASSOC);
    
    if (strpos($column['Extra'], 'auto_increment') === false) {
        echo "<p class='info'>Dang sua cot id trong bai_viet...</p>";
        // Xoa PRIMARY KEY cu truoc
        $conn->exec("ALTER TABLE bai_viet DROP PRIMARY KEY");
        // Them lai voi AUTO_INCREMENT
        $conn->exec("ALTER TABLE bai_viet ADD PRIMARY KEY (id)");
        $conn->exec("ALTER TABLE bai_viet MODIFY id INT AUTO_INCREMENT");
        echo "<p class='success'>✓ Da sua bai_viet</p>";
    } else {
        echo "<p class='success'>✓ bai_viet da co AUTO_INCREMENT</p>";
    }
    
    // Kiem tra video_huong_dan
    $result = $conn->query("SHOW COLUMNS FROM video_huong_dan WHERE Field = 'id'");
    $column = $result->fetch(PDO::FETCH_ASSOC);
    
    if (strpos($column['Extra'], 'auto_increment') === false) {
        echo "<p class='info'>Dang sua cot id trong video_huong_dan...</p>";
        // Xoa PRIMARY KEY cu truoc
        $conn->exec("ALTER TABLE video_huong_dan DROP PRIMARY KEY");
        // Them lai voi AUTO_INCREMENT
        $conn->exec("ALTER TABLE video_huong_dan ADD PRIMARY KEY (id)");
        $conn->exec("ALTER TABLE video_huong_dan MODIFY id INT AUTO_INCREMENT");
        echo "<p class='success'>✓ Da sua video_huong_dan</p>";
    } else {
        echo "<p class='success'>✓ video_huong_dan da co AUTO_INCREMENT</p>";
    }
    
    // Buoc 3: Reset AUTO_INCREMENT
    echo "<h3>Buoc 3: Reset AUTO_INCREMENT...</h3>";
    $conn->exec("ALTER TABLE bai_viet AUTO_INCREMENT = 1");
    $conn->exec("ALTER TABLE video_huong_dan AUTO_INCREMENT = 1");
    echo "<p class='success'>✓ Da reset AUTO_INCREMENT ve 1</p>";
    
    // Buoc 4: Kiem tra them cot loai_thu_cung
    echo "<h3>Buoc 4: Kiem tra cot loai_thu_cung...</h3>";
    
    $result = $conn->query("SHOW COLUMNS FROM bai_viet WHERE Field = 'loai_thu_cung'");
    if ($result->rowCount() == 0) {
        echo "<p class='info'>Dang them cot loai_thu_cung vao bai_viet...</p>";
        $conn->exec("ALTER TABLE bai_viet ADD COLUMN loai_thu_cung VARCHAR(50) DEFAULT 'Chung' AFTER hinh_anh");
        echo "<p class='success'>✓ Da them cot loai_thu_cung vao bai_viet</p>";
    } else {
        echo "<p class='success'>✓ bai_viet da co cot loai_thu_cung</p>";
    }
    
    $result = $conn->query("SHOW COLUMNS FROM video_huong_dan WHERE Field = 'loai_thu_cung'");
    if ($result->rowCount() == 0) {
        echo "<p class='info'>Dang them cot loai_thu_cung vao video_huong_dan...</p>";
        $conn->exec("ALTER TABLE video_huong_dan ADD COLUMN loai_thu_cung VARCHAR(50) DEFAULT 'Chung' AFTER hinh_anh_thumbnail");
        echo "<p class='success'>✓ Da them cot loai_thu_cung vao video_huong_dan</p>";
    } else {
        echo "<p class='success'>✓ video_huong_dan da co cot loai_thu_cung</p>";
    }
    
    echo "<hr><h2 class='success'>✓ HOAN TAT! Bay gio chay import_data.php de them du lieu</h2>";
    echo "<p><a href='import_data.php' style='background:purple;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>▶ Chay Import Du Lieu</a></p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>✗ Loi: " . $e->getMessage() . "</p>";
    echo "<p>Chi tiet: <pre>" . $e->getTraceAsString() . "</pre></p>";
}
?>
