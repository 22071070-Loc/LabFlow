<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$table = $_GET['table'] ?? 'equipment';
$config = getTableConfig($table);
$pageTitle = 'Add ' . $config['title'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = buildDataFromPost($table, false);
        $errors = validateBusinessRules($table, $data);
        if ($errors) {
            throw new RuntimeException(implode(' ', $errors));
        }
        tableInsert($table, $data);
        flash('Record created successfully.');
        redirect(baseUrl('modules/index.php?table=' . $table));
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <div class="toolbar">
        <div>
            <h2>Add <?= h($config['title']) ?></h2>
            <p class="help"><?= h($config['description']) ?></p>
        </div>
        <a class="btn secondary" href="<?= h(baseUrl('modules/index.php?table=' . $table)) ?>">Back</a>
    </div>
    <?php if ($error): ?><div class="alert error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
        <div class="form-grid">
            <?php foreach ($config['form'] as $field): ?>
                <?php renderField($table, $field, $_POST[$field] ?? null, false); ?>
            <?php endforeach; ?>
        </div>
        <div class="form-actions">
            <button class="btn" type="submit">Save</button>
            <a class="btn secondary" href="<?= h(baseUrl('modules/index.php?table=' . $table)) ?>">Cancel</a>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
