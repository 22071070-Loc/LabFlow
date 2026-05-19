<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$pdo = getPDO();
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT * FROM damage_reports WHERE id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $damage = $stmt->fetch();
    if (!$damage) throw new RuntimeException('Damage report not found.');

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) AS paid FROM penalty_payments WHERE damage_report_id = ?");
    $stmt->execute([$id]);
    $paid = (float)$stmt->fetch()['paid'];
    $remaining = max(0, (float)$damage['penalty_amount'] - $paid);

    if ($remaining > 0) {
        $stmt = $pdo->prepare("INSERT INTO penalty_payments (damage_report_id, paid_by, amount, payment_method, payment_status, paid_at, note) VALUES (?, ?, ?, 'cash', 'paid', NOW(), 'Auto-created payment when marking damage report as paid.')");
        $stmt->execute([$id, $damage['reported_by'], $remaining]);
    }

    $pdo->prepare("UPDATE penalty_payments SET payment_status = 'paid', paid_at = COALESCE(paid_at, NOW()) WHERE damage_report_id = ?")->execute([$id]);
    $pdo->prepare("UPDATE damage_reports SET status = 'paid' WHERE id = ?")->execute([$id]);

    $pdo->commit();
    flash('Damage report marked as paid. Penalty payment was updated.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash($e->getMessage(), 'error');
}
redirect(baseUrl('modules/index.php?table=damage_reports'));
