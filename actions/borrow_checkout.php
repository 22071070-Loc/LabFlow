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
    if ($request['status'] !== 'approved') throw new RuntimeException('Only approved requests can be checked out.');

    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM borrow_records WHERE request_id = ?");
    $stmt->execute([$id]);
    if ((int)$stmt->fetch()['c'] > 0) throw new RuntimeException('This request already has a borrow record.');

    if ($request['equipment_status'] !== 'available') throw new RuntimeException('Equipment is not available for checkout. Current status: ' . $request['equipment_status']);

    $stmt = $pdo->prepare("INSERT INTO borrow_records (request_id, checkout_by, checkout_time, expected_return_time, condition_out, used_hours, status, note) VALUES (?, ?, NOW(), ?, ?, 0, 'checked_out', ?)");
    $stmt->execute([$id, currentUser()['id'], $request['expected_return_time'], 'Equipment checked before checkout. Basic accessories are complete.', 'Created by quick checkout action.']);

    $stmt = $pdo->prepare("UPDATE equipment SET status = 'borrowed' WHERE id = ?");
    $stmt->execute([$request['equipment_id']]);

    $pdo->commit();
    flash('Checkout completed. Borrow record created and equipment status changed to borrowed.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash($e->getMessage(), 'error');
}
redirect(baseUrl('modules/index.php?table=borrow_requests'));
