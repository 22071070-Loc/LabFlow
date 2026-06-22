<?php
require_once '../config/database.php';

try {
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY id DESC");
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    die("Lỗi tải danh sách khóa học.");
}
?>
<!DOCTYPE html>
<html>
<head><title>Quản lý Khóa học</title></head>
<body>
    <h2>Danh sách Khóa học</h2>
    <a href="create.php">Thêm khóa học mới</a> <br><br>
    
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Tên khóa học (Title)</th>
            <th>Mô tả (Description)</th>
            <th>Hành động</th>
        </tr>
        <?php foreach ($courses as $course): ?>
        <tr>
            <td><?= htmlspecialchars($course['id']) ?></td>
            <td><?= htmlspecialchars($course['title']) ?></td>
            <td><?= htmlspecialchars($course['description'] ?? '') ?></td>
            <td>
                <a href="edit.php?id=<?= $course['id'] ?>">Sửa</a> | 
                <a href="delete.php?id=<?= $course['id'] ?>" onclick="return confirm('Xóa khóa học này?');">Xóa</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="../students/index.php">Quản lý Sinh viên</a> | 
    <a href="../enrollments/index.php">Quản lý Đăng ký</a>
</body>
</html>