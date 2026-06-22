<?php
require_once '../config/database.php';

try {
    // Câu lệnh JOIN lấy dữ liệu từ 3 bảng
    $sql = "SELECT e.id, 
                   s.name AS student_name, 
                   s.email, 
                   c.title AS course_title, 
                   e.enrolled_at 
            FROM enrollments e 
            JOIN students s ON e.student_id = s.id 
            JOIN courses c ON e.course_id = c.id 
            ORDER BY e.enrolled_at DESC";
            
    $stmt = $pdo->query($sql);
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage()); // Ghi log cho Dev
    die("Lỗi: Không thể lấy dữ liệu đăng ký học."); // Báo lỗi chung chung cho User
}
?>

<!DOCTYPE html>
<html>
<head><title>Quản lý Đăng ký (Enrollments)</title></head>
<body>
    <h2>Danh sách Sinh viên Đăng ký Khóa học</h2>
    <a href="create.php">Tạo đăng ký mới</a> <br><br>
    
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Tên Sinh viên</th>
            <th>Email</th>
            <th>Khóa học</th>
            <th>Ngày đăng ký</th>
            <th>Hành động</th>
        </tr>
        <?php foreach ($enrollments as $enrol): ?>
        <tr>
            <td><?= htmlspecialchars($enrol['id']) ?></td>
            <td><?= htmlspecialchars($enrol['student_name']) ?></td>
            <td><?= htmlspecialchars($enrol['email']) ?></td>
            <td><?= htmlspecialchars($enrol['course_title']) ?></td>
            <td><?= htmlspecialchars($enrol['enrolled_at']) ?></td>
            <td>
                <a href="delete.php?id=<?= $enrol['id'] ?>" onclick="return confirm('Hủy đăng ký khóa học này?');">Xóa</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    
    <br>
    <a href="../students/index.php">Chuyển đến Sinh viên</a> | 
    <a href="../courses/index.php">Chuyển đến Khóa học</a>
</body>
</html>