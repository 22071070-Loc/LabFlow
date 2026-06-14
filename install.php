<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';
$done = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        installDatabaseIfNeeded();
        unset($_SESSION['user']);
        $done = true;
        flash('Database has been reset and sample data has been imported. Please login again.');
        redirect(baseUrl('auth/login.php'));
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$pageTitle = 'Install / Reset Database';
include __DIR__ . '/includes/header.php';
?>
<div class="card">
    <h2>Install or Reset Demo Database</h2>
    <p class="help">
        This action runs <code>database/lab_equipment_manager.sql</code>. It creates the database,
        drops existing demo tables, recreates them, and imports realistic sample data.
    </p>
    <?php if ($error): ?>
        <div class="alert error"><?= h($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <button class="btn danger" type="submit" data-confirm="Reset database and reload all sample data? Existing demo changes will be removed.">Reset Demo Database</button>
        <a class="btn secondary" href="<?= h(baseUrl('index.php')) ?>">Back to Dashboard</a>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
