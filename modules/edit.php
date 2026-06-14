<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$table = $_GET['table'] ?? 'equipment';
$id = (int)($_GET['id'] ?? 0);
$config = getTableConfig($table);
$row = getRow($table, $id);
if (!$row) { flash('Record not found.', 'error'); redirect(baseUrl('modules/index.php?table=' . $table)); }
$pageTitle = 'Edit ' . $config['title'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = buildDataFromPost($table, true);
        $errors = validateBusinessRules($table, $data, $id);
        if ($errors) {
            throw new RuntimeException(implode(' ', $errors));
        }
        tableUpdate($table, $id, $data);
        flash('Record updated successfully.');
        redirect(baseUrl('modules/index.php?table=' . $table));
    } catch (Throwable $e) {
        $error = $e->getMessage();
        $row = array_merge($row, $_POST);
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <div class="toolbar">
        <div>
            <h2>Edit <?= h($config['title']) ?> #<?= h($id) ?></h2>
            <p class="help"><?= h($config['description']) ?></p>
        </div>
        <a class="btn secondary" href="<?= h(baseUrl('modules/index.php?table=' . $table)) ?>">Back</a>
    </div>
    <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <?php foreach ($config['form'] as $field): ?>
                <?php renderField($table, $field, $row[$field] ?? null, true); ?>
            <?php endforeach; ?>
        </div>
        <div class="form-actions">
            <button class="btn" type="submit">Update</button>
            <a class="btn secondary" href="<?= h(baseUrl('modules/index.php?table=' . $table)) ?>">Cancel</a>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
