<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$pdo = getPDO();
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT * FROM maintenance_schedules WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $schedule = $stmt->fetch();
    if (!$schedule) throw new RuntimeException('Maintenance schedule not found.');
    if ($schedule['status'] !== 'planned') throw new RuntimeException('Only planned schedules can be started.');

    $pdo->prepare("UPDATE maintenance_schedules SET status = 'in_progress' WHERE id = ?")->execute([$id]);
    $pdo->prepare("UPDATE equipment SET status = 'maintenance' WHERE id = ?")->execute([$schedule['equipment_id']]);

    $pdo->commit();
    flash('Maintenance started. Equipment status changed to maintenance.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash($e->getMessage(), 'error');
}
redirect(baseUrl('modules/index.php?table=maintenance_schedules'));
