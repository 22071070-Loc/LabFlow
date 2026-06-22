<?php
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    // Validate dữ liệu đầu vào
    if (empty($name) || empty($email)) {
        $error = "Vui lòng nhập đầy đủ Tên và Email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Định dạng email không hợp lệ.";
    } else {
        try {
            // Kiểm tra email trùng lặp
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "Email này đã tồn tại trong hệ thống.";
            } else {
                // Thêm vào DB
                $insertStmt = $pdo->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
                $insertStmt->execute([$name, $email]);
                $success = "Thêm sinh viên thành công!";
            }
        } catch (PDOException $e) {
            error_log("Error inserting student: " . $e->getMessage());
            $error = "Lỗi hệ thống khi lưu dữ liệu.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Thêm Sinh viên</title></head>
<body>
    <h2>Thêm Sinh viên Mới</h2>
    
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="POST">
        <label>Tên sinh viên:</label><br>
        <input type="text" name="name" required><br><br>
        
        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>
        
        <button type="submit">Lưu thông tin</button>
        <a href="index.php">Quay lại danh sách</a>
    </form>
</body>
</html>