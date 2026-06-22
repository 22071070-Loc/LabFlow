<?php
$host = 'localhost';
$dbname = 'school_db';
$username = 'root';    // Mặc định của XAMPP là root
$password = '';        // Mặc định của XAMPP không có mật khẩu

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Cấu hình để PDO ném ra ngoại lệ (Exception) khi gặp lỗi
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Ghi log lỗi vào file của server (dành cho developer)
    error_log("Database connection failed: " . $e->getMessage());
    // Hiển thị thông báo an toàn cho người dùng
    die("Lỗi: Không thể kết nối đến cơ sở dữ liệu.");
}
?>