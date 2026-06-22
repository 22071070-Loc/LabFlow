<?php
require_once '../config/database.php';

try {
    $stmt = $pdo->query("SELECT * FROM students ORDER BY id DESC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching students: " . $e->getMessage());
    die("Có lỗi xảy ra khi tải danh sách sinh viên.");
}
?>

<!DOCTYPE html>
<html>
<head><title>Quản lý Sinh viên</title></head>
<body>
    <h2>Danh sách Sinh viên</h2>
    <a href="create.php">Thêm sinh viên mới</a>
    <br><br>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Email</th>
            <th>Hành động</th>
        </tr>
        <?php foreach ($students as $student): ?>
        <tr>
            <td><?= htmlspecialchars($student['id']) ?></td>
            <td><?= htmlspecialchars($student['name']) ?></td>
            <td><?= htmlspecialchars($student['email']) ?></td>
            <td>
                <a href="edit.php?id=<?= $student['id'] ?>">Sửa</a> | 
                <a href="delete.php?id=<?= $student['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa sinh viên này?');">Xóa</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>