<?php
require_once '../config/database.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("Error deleting enrollment: " . $e->getMessage());
        die("Lỗi hệ thống khi xóa dữ liệu.");
    }
}

// Chuyển hướng về trang danh sách
header("Location: index.php");
exit();
?>