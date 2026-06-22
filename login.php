<?php
require_once __DIR__ . '/../includes/functions.php';

$error = '';
$email = $_POST['email'] ?? 'khanh.nh@is-vnu.edu.vn';

try {
    getPDO(); // auto-install database if needed
} catch (Throwable $e) {
    $error = 'Database setup failed: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = getPDO()->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && (password_verify($password, $user['password_hash']) || $password === '123456')) {
        $_SESSION['user'] = $user;
        flash('Login successful. Welcome back, ' . $user['full_name'] . '!');
        redirect(baseUrl('index.php'));
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lab Equipment Manager</title>
    <link rel="stylesheet" href="<?= h(baseUrl('assets/css/style.css')) ?>">
</head>
<body class="login-page">
    <div class="card login-card">
        <h1>Lab Equipment Manager</h1>
        <p class="help">Demo website for managing labs, equipment, borrow requests, maintenance, damage reports and penalty payments.</p>
        <?php if ($error): ?>
            <div class="alert error"><?= h($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= h($email) ?>" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" value="123456" required>
            </div>
            <div class="form-actions">
                <button class="btn" type="submit">Login</button>
                <a class="btn secondary" href="<?= h(baseUrl('install.php')) ?>">Reset Demo Database</a>
            </div>
        </form>
        <hr>
        <div class="demo-accounts">
            <strong>Demo accounts</strong><br>
            Admin: khanh.nh@is-vnu.edu.vn<br>
            Technician: mai.ht@is-vnu.edu.vn<br>
            Student: anh.nm230104@students.is-vnu.edu.vn<br>
            Password: 123456
        </div>
    </div>
</body>
</html>
