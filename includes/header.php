<?php
require_once __DIR__ . '/functions.php';
$user = currentUser();
$tables = appTables();
$pageTitle = $pageTitle ?? 'Lab Equipment Manager';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= h(baseUrl('assets/css/style.css')) ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-mark">LEM</div>
            <div>
                <strong>Lab Equipment</strong>
                <span>Manager</span>
            </div>
        </div>
        <nav>
            <a href="<?= h(baseUrl('index.php')) ?>">📊 Dashboard</a>
            <?php foreach ($tables as $name => $cfg): ?>
                <a href="<?= h(baseUrl('modules/index.php?table=' . $name)) ?>"><?= h($cfg['icon']) ?> <?= h($cfg['title']) ?></a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <main class="main">
        <header class="topbar">
            <div>
                <h1><?= h($pageTitle) ?></h1>
                <p>Basic demo website for INS3064 Lab & Equipment Manager project.</p>
            </div>
            <div class="user-box">
                <?php if ($user): ?>
                    <strong><?= h($user['full_name']) ?></strong>
                    <span><?= h($user['role']) ?></span>
                    <a class="btn small" href="<?= h(baseUrl('auth/logout.php')) ?>">Logout</a>
                <?php else: ?>
                    <a class="btn small" href="<?= h(baseUrl('auth/login.php')) ?>">Login</a>
                <?php endif; ?>
            </div>
        </header>
        <?php if ($f = flash()): ?>
            <div class="alert <?= h($f['type']) ?>"><?= h($f['message']) ?></div>
        <?php endif; ?>
