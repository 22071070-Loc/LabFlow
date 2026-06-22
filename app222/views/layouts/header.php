<?php $user = Auth::user(); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <style>
        <?php
        // Inline fallback: keeps the modern UI even if Laragon resolves asset paths differently.
        $cssFile = BASE_PATH . '/public/assets/css/app.css';
        if (is_file($cssFile)) {
            readfile($cssFile);
        }
        ?>
    </style>
</head>
<body>
<?php if (Auth::check()): ?>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">L</div>
            <div>
                <strong>Lab Manager</strong>
                <span>MVC PHP</span>
            </div>
        </div>
        <nav class="nav">
            <a href="<?= url('dashboard/index') ?>">Dashboard</a>
            <div class="nav-title">Master Data</div>
            <?php if (in_array(Auth::role(), ['admin','technician'], true)): ?>
                <a href="<?= url('departments/index') ?>">Departments</a>
                <?php if (Auth::isAdmin()): ?>
                    <a href="<?= url('users/index') ?>">Users</a>
                <?php endif; ?>
                <a href="<?= url('labs/index') ?>">Labs</a>
                <a href="<?= url('equipment_categories/index') ?>">Categories</a>
                <a href="<?= url('suppliers/index') ?>">Suppliers</a>
            <?php endif; ?>
            <a href="<?= url('equipment/index') ?>">Equipment</a>
            <div class="nav-title">Operation</div>
            <a href="<?= url('borrow_requests/index') ?>">Borrow Requests</a>
            <a href="<?= url('borrow_records/index') ?>">Borrow Records</a>
            <?php if (!Auth::isStudent()): ?>
                <a href="<?= url('maintenance_schedules/index') ?>">Maintenance Plans</a>
                <a href="<?= url('maintenance_logs/index') ?>">Maintenance Logs</a>
                <a href="<?= url('damage_reports/index') ?>">Damage Reports</a>
            <?php endif; ?>
            <a href="<?= url('penalty_payments/index') ?>">Penalty Payments</a>
        </nav>
    </aside>
    <main class="main">
        <header class="topbar">
            <div>
                <h1><?= e(APP_NAME) ?></h1>
            </div>
            <div class="user-pill">
                <span><?= e($user['full_name']) ?></span>
                <small><?= e(ucfirst($user['role'])) ?></small>
                <a href="<?= url('auth/logout') ?>">Logout</a>
            </div>
        </header>
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="flash-wrap">
                <?php foreach ($_SESSION['flash'] as $flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                <?php endforeach; unset($_SESSION['flash']); ?>
            </div>
        <?php endif; ?>
<?php endif; ?>
