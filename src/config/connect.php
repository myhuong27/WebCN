<?php
try {
    // Thông tin kết nối MySQL
    $host = 'localhost';
    $dbname = 'quan_ly_thu_cung';
    $username = 'root';
    $password = '';

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ];
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
    
    
} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    die("Lỗi kết nối: " . $e->getMessage());
}
?>