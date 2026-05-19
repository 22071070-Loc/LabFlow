<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
$id = (int)($_GET['id'] ?? 0);
try {
    $stmt = getPDO()->prepare("UPDATE borrow_requests SET status = 'rejected', approved_by = ?, approved_at = NOW(), note = CONCAT(COALESCE(note,''), '\nRejected by demo action.') WHERE id = ? AND status = 'pending'");
    $stmt->execute([currentUser()['id'], $id]);
    flash('Borrow request rejected.');
} catch (Throwable $e) {
    flash($e->getMessage(), 'error');
}
redirect(baseUrl('modules/index.php?table=borrow_requests'));
