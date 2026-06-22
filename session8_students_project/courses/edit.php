<?php
require_once '../config/database.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Không có ID.");

$error = ''; $success = '';

$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) die("Không tìm thấy khóa học.");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($title)) {
        $error = "Tên khóa học không được để trống.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ? WHERE id = ?");
            $stmt->execute([$title, $description, $id]);
            $success = "Cập nhật thành công!";
            $course['title'] = $title;
            $course['description'] = $description;
        } catch (PDOException $e) {
            error_log("Error: " . $e->getMessage());
            $error = "Lỗi hệ thống khi cập nhật.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Sửa Khóa học</title></head>
<body>
    <h2>Sửa Khóa học</h2>
    <?php if ($error): ?><p style="color:red;"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color:green;"><?= $success ?></p><?php endif; ?>

    <form method="POST">
        <label>Tên khóa học:</label><br>
        <input type="text" name="title" value="<?= htmlspecialchars($course['title']) ?>" required><br><br>
        
        <label>Mô tả:</label><br>
        <textarea name="description" rows="4" cols="30"><?= htmlspecialchars($course['description'] ?? '') ?></textarea><br><br>
        
        <button type="submit">Cập nhật</button>
        <a href="index.php">Quay lại danh sách</a>
    </form>
</body>
</html>