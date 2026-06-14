<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$table = $_GET['table'] ?? 'equipment';
$id = (int)($_GET['id'] ?? 0);
getTableConfig($table);
try {
    tableDelete($table, $id);
    flash('Record deleted successfully.');
} catch (Throwable $e) {
    flash('Cannot delete this record because it may be referenced by another table. Details: ' . $e->getMessage(), 'error');
}
redirect(baseUrl('modules/index.php?table=' . $table));
