<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$pdo = getPDO();
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT ms.*, e.status AS equipment_status FROM maintenance_schedules ms JOIN equipment e ON ms.equipment_id = e.id WHERE ms.id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $schedule = $stmt->fetch();
    if (!$schedule) throw new RuntimeException('Maintenance schedule not found.');
    if ($schedule['status'] === 'completed') throw new RuntimeException('This schedule is already completed.');

    $nextDue = $schedule['frequency_days'] ? date('Y-m-d', strtotime('+' . (int)$schedule['frequency_days'] . ' days')) : null;
    $pdo->prepare("UPDATE maintenance_schedules SET status = 'completed' WHERE id = ?")->execute([$id]);
    $pdo->prepare("UPDATE equipment SET status = 'available' WHERE id = ? AND status = 'maintenance'")->execute([$schedule['equipment_id']]);

    $stmt = $pdo->prepare("INSERT INTO maintenance_logs (schedule_id, equipment_id, technician_id, performed_date, action_taken, cost, next_due_date, result_status) VALUES (?, ?, ?, CURDATE(), ?, 0, ?, 'passed')");
    $stmt->execute([$id, $schedule['equipment_id'], currentUser()['id'], 'Completed scheduled ' . $schedule['maintenance_type'] . '. Basic function test passed.', $nextDue]);

    $pdo->commit();
    flash('Maintenance completed and a maintenance log was created.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash($e->getMessage(), 'error');
}
redirect(baseUrl('modules/index.php?table=maintenance_schedules'));
