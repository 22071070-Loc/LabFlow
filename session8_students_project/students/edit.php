<?php
require_once '../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Không có ID sinh viên để sửa.");
}

$error = '';
$success = '';

// Lấy dữ liệu cũ hiển thị lên form
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Không tìm thấy sinh viên trong hệ thống.");
}

// Xử lý khi người dùng ấn nút Cập nhật
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    if (empty($name) || empty($email)) {
        $error = "Vui lòng nhập đầy đủ thông tin.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ.";
    } else {
        try {
            // Kiểm tra trùng email nhưng phải loại trừ ID của chính sinh viên đang sửa
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = ? AND id != ?");
            $checkStmt->execute([$email, $id]);
            
            if ($checkStmt->fetchColumn() > 0) {
                $error = "Email này đã được sử dụng bởi sinh viên khác.";
            } else {
                $updateStmt = $pdo->prepare("UPDATE students SET name = ?, email = ? WHERE id = ?");
                $updateStmt->execute([$name, $email, $id]);
                $success = "Cập nhật thành công!";
                // Cập nhật lại mảng $student để hiển thị dữ liệu mới lên form
                $student['name'] = $name;
                $student['email'] = $email;
            }
        } catch (PDOException $e) {
            error_log("Error updating student: " . $e->getMessage());
            $error = "Lỗi hệ thống khi cập nhật.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Sửa Sinh viên</title></head>
<body>
    <h2>Cập nhật thông tin Sinh viên</h2>

    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="POST">
        <label>Tên sinh viên:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" required><br><br>
        
        <label>Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required><br><br>
        
        <button type="submit">Cập nhật</button>
        <a href="index.php">Quay lại danh sách</a>
    </form>
</body>
</html>