<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$pdo = getPDO();
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT br.*, e.status AS equipment_status FROM borrow_requests br JOIN equipment e ON br.equipment_id = e.id WHERE br.id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $request = $stmt->fetch();
    if (!$request) throw new RuntimeException('Borrow request not found.');
    if ($request['status'] !== 'pending') throw new RuntimeException('Only pending requests can be approved.');
    if ($request['equipment_status'] !== 'available') throw new RuntimeException('Equipment is not available. Current status: ' . $request['equipment_status']);

    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM borrow_requests\n        WHERE equipment_id = ? AND id <> ? AND status IN ('pending','approved')\n        AND NOT (expected_return_time <= ? OR start_time >= ?)");
    $stmt->execute([$request['equipment_id'], $id, $request['start_time'], $request['expected_return_time']]);
    if ((int)$stmt->fetch()['c'] > 0) {
        throw new RuntimeException('Cannot approve because another request overlaps this borrowing time.');
    }

    $approverId = currentUser()['id'];
    $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
    $stmt->execute([$approverId, $id]);
    $pdo->commit();
    flash('Borrow request approved successfully.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash($e->getMessage(), 'error');
}
redirect(baseUrl('modules/index.php?table=borrow_requests'));
