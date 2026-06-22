<?php
require_once '../config/database.php';

$error = ''; $success = '';

// Lấy danh sách Sinh viên và Khóa học để đổ vào Dropdown
try {
    $students = $pdo->query("SELECT id, name, email FROM students")->fetchAll(PDO::FETCH_ASSOC);
    $courses = $pdo->query("SELECT id, title FROM courses")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Load form error: " . $e->getMessage());
    die("Lỗi hệ thống khi tải form đăng ký.");
}

// Xử lý khi Submit form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    if (empty($student_id) || empty($course_id)) {
        $error = "Vui lòng chọn cả Sinh viên và Khóa học.";
    } else {
        try {
            // Kiểm tra xem sinh viên đã đăng ký khóa học này chưa
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND course_id = ?");
            $checkStmt->execute([$student_id, $course_id]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $error = "Sinh viên này đã đăng ký khóa học này rồi! (Không được trùng lặp)";
            } else {
                // Thêm mới đăng ký
                $insertStmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
                $insertStmt->execute([$student_id, $course_id]);
                $success = "Đăng ký thành công!";
            }
        } catch (PDOException $e) {
            error_log("Enrollment error: " . $e->getMessage());
            $error = "Hệ thống đang gặp lỗi. Vui lòng thử lại.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Tạo Đăng ký mới</title></head>
<body>
    <h2>Tạo Đăng ký Môn học (Enrollment)</h2>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="POST">
        <label>Chọn Sinh viên:</label><br>
        <select name="student_id" required>
            <option value="">-- Chọn sinh viên --</option>
            <?php foreach ($students as $student): ?>
                <option value="<?= $student['id'] ?>">
                    <?= htmlspecialchars($student['name'] . ' (' . $student['email'] . ')') ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>
        
        <label>Chọn Khóa học:</label><br>
        <select name="course_id" required>
            <option value="">-- Chọn khóa học --</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= $course['id'] ?>">
                    <?= htmlspecialchars($course['title']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>
        
        <button type="submit">Ghi danh</button>
        <a href="index.php">Quay lại danh sách</a>
    </form>
</body>
</html>