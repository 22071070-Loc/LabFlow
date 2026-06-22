<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - <?= e(APP_NAME) ?></title>
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
    <?php $old = $_SESSION['register_old'] ?? []; ?>
    <section class="login-card register-card">
        <div class="login-logo">L</div>
        <h1>Create Account</h1>

        <?php if (!empty($_SESSION['register_error'])): ?>
            <div class="alert alert-danger"><?= e($_SESSION['register_error']); unset($_SESSION['register_error']); ?></div>
        <?php endif; ?>

        <form method="post" action="<?= url('auth/storeRegister') ?>" class="form-stack">
            <label>Full Name *
                <input type="text" name="full_name" required value="<?= e($old['full_name'] ?? '') ?>">
            </label>

            <label>Email *
                <input type="email" name="email" required value="<?= e($old['email'] ?? '') ?>">
            </label>

            <label>Role *
                <select name="role" id="role-select" required>
                    <option value="">-- Select role --</option>
                    <option value="student" <?= ($old['role'] ?? '') === 'student' ? 'selected' : '' ?>>Student</option>
                    <option value="technician" <?= ($old['role'] ?? '') === 'technician' ? 'selected' : '' ?>>Technician</option>
                </select>
            </label>

            <label id="student-code-row">Student ID *
                <input type="text" name="student_code" id="student-code-input" value="<?= e($old['student_code'] ?? '') ?>" placeholder="Example: 23070479">
            </label>

            <label>Department
                <select name="department_id">
                    <option value="">-- Select department --</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= e($department['id']) ?>" <?= (string)($old['department_id'] ?? '') === (string)$department['id'] ? 'selected' : '' ?>>
                            <?= e($department['department_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>Phone
                <input type="text" name="phone" value="<?= e($old['phone'] ?? '') ?>">
            </label>

            <label>Password *
                <input type="password" name="password" required minlength="6">
            </label>

            <label>Confirm Password *
                <input type="password" name="confirm_password" required minlength="6">
            </label>

            <button class="btn btn-primary" type="submit">Register</button>
        </form>

        <div class="auth-links">
            <span>Already have an account?</span>
            <a href="<?= url('auth/login') ?>">Login</a>
        </div>
    </section>
    <script>
        const roleSelect = document.getElementById('role-select');
        const studentCodeRow = document.getElementById('student-code-row');
        const studentCodeInput = document.getElementById('student-code-input');
        function toggleStudentCode() {
            const isStudent = roleSelect.value === 'student';
            studentCodeRow.style.display = isStudent ? 'block' : 'none';
            studentCodeInput.required = isStudent;
            if (!isStudent) studentCodeInput.value = '';
        }
        roleSelect.addEventListener('change', toggleStudentCode);
        toggleStudentCode();
    </script>
    <?php unset($_SESSION['register_old']); ?>
</body>
</html>
