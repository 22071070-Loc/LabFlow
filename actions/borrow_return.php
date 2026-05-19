<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
$condition = $_GET['condition'] ?? 'good';
$pdo = getPDO();
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT brc.*, br.equipment_id, br.id AS request_id, e.purchase_price FROM borrow_records brc JOIN borrow_requests br ON brc.request_id = br.id JOIN equipment e ON br.equipment_id = e.id WHERE brc.id = ? FOR UPDATE");
    $stmt->execute([$id]);
    $record = $stmt->fetch();
    if (!$record) throw new RuntimeException('Borrow record not found.');
    if ($record['status'] !== 'checked_out') throw new RuntimeException('Only checked-out records can be returned.');

    $hours = max(0.25, round((time() - strtotime($record['checkout_time'])) / 3600, 2));
    $userId = currentUser()['id'];

    if ($condition === 'damaged') {
        $status = 'damaged';
        $equipmentStatus = 'damaged';
        $conditionIn = 'Returned with visible damage. Technician inspection is required.';
    } elseif ($condition === 'lost') {
        $status = 'lost';
        $equipmentStatus = 'damaged';
        $conditionIn = 'Equipment was reported lost during check-in.';
    } else {
        $status = 'returned';
        $equipmentStatus = 'available';
        $conditionIn = 'Returned in good condition. Basic function test passed.';
    }

    $stmt = $pdo->prepare("UPDATE borrow_records SET checkin_by = ?, checkin_time = NOW(), condition_in = ?, used_hours = ?, status = ? WHERE id = ?");
    $stmt->execute([$userId, $conditionIn, $hours, $status, $id]);

    $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'completed' WHERE id = ?");
    $stmt->execute([$record['request_id']]);

    $stmt = $pdo->prepare("UPDATE equipment SET status = ?, total_used_hours = total_used_hours + ? WHERE id = ?");
    $stmt->execute([$equipmentStatus, $hours, $record['equipment_id']]);

    if ($condition === 'damaged' || $condition === 'lost') {
        $repairCost = $condition === 'lost' ? (float)$record['purchase_price'] : max(150000, round((float)$record['purchase_price'] * 0.08, -3));
        $penalty = $condition === 'lost' ? (float)$record['purchase_price'] : round($repairCost * 0.8, -3);
        $severity = $condition === 'lost' ? 'critical' : 'medium';
        $description = $condition === 'lost'
            ? 'Equipment was reported lost during return process. Full replacement penalty is required.'
            : 'Equipment was returned with damage. A technician must inspect and repair before next borrowing.';
        $stmt = $pdo->prepare("INSERT INTO damage_reports (record_id, equipment_id, reported_by, severity, description, repair_cost, penalty_amount, status, reported_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'charged', NOW())");
        $stmt->execute([$id, $record['equipment_id'], $userId, $severity, $description, $repairCost, $penalty]);
    }

    $pdo->commit();
    flash('Check-in completed. Equipment status and related records have been updated.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash($e->getMessage(), 'error');
}
redirect(baseUrl('modules/index.php?table=borrow_records'));
