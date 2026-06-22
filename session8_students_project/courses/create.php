<?php
require_once '../config/database.php';

$error = ''; $success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($title)) {
        $error = "Tên khóa học không được để trống.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO courses (title, description) VALUES (?, ?)");
            $stmt->execute([$title, $description]);
            $success = "Thêm khóa học thành công!";
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
            $error = "Lỗi hệ thống khi lưu dữ liệu.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Thêm Khóa học</title></head>
<body>
    <h2>Thêm Khóa học Mới</h2>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="POST">
        <label>Tên khóa học:</label><br>
        <input type="text" name="title" required><br><br>
        
        <label>Mô tả:</label><br>
        <textarea name="description" rows="4" cols="30"></textarea><br><br>
        
        <button type="submit">Lưu</button>
        <a href="index.php">Quay lại danh sách</a>
    </form>
</body>
</html>