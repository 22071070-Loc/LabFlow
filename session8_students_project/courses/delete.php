<?php
require_once '../config/database.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("Error deleting: " . $e->getMessage());
        die("Không thể xóa. Khóa học này có thể đang có sinh viên đăng ký.");
    }
}
header("Location: index.php");
exit();
?>