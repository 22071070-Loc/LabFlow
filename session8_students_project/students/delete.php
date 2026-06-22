<?php
require_once '../config/database.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("Error deleting student: " . $e->getMessage());
        // Lỗi thường gặp: dính khóa ngoại (Foreign Key) khi sinh viên đã đăng ký khóa học
        die("Không thể xóa. Có thể sinh viên này đang có dữ liệu liên kết.");
    }
}

// Xóa xong tự động chuyển hướng về trang chủ
header("Location: index.php");
exit();
?>