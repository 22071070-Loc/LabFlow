<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        <?php
        $cssFile = BASE_PATH . '/public/assets/css/app.css';
        if (is_file($cssFile)) {
            readfile($cssFile);
        }
        ?>
    </style>
</head>
<body class="login-body">
    <section class="login-card">
        <div class="login-logo">L</div>
        <h1>Lab & Equipment Manager</h1>

        <?php if (!empty($_SESSION['login_error'])): ?>
            <div class="alert alert-danger"><?= e($_SESSION['login_error']); unset($_SESSION['login_error']); ?></div>
        <?php endif; ?>

        <form method="post" action="<?= url('auth/authenticate') ?>" class="form-stack">
            <label>Email / Student ID
                <input type="text" name="identifier" required value="<?= e($_SESSION['old_identifier'] ?? ''); unset($_SESSION['old_identifier']); ?>" placeholder="Admin/technician email or student ID">
            </label>
            <label>Password
                <input type="password" name="password" required>
            </label>
            <button class="btn btn-primary" type="submit">Login</button>
        </form>

        <div class="auth-links">
            <span>Do not have an account?</span>
            <a href="<?= url('auth/register') ?>">Create account</a>
        </div>
    </section>
</body>
</html>
