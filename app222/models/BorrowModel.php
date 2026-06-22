<?php
class BorrowModel extends BaseModel
{
    public function hasTimeConflict(int $equipmentId, string $startTime, string $endTime, ?int $excludeRequestId = null): bool
    {
        $sql = "
            SELECT COUNT(*)
            FROM borrow_requests
            WHERE equipment_id = :equipment_id
              AND status IN ('pending', 'approved')
              AND NOT (expected_return_time <= :start_time OR start_time >= :end_time)
        ";
        $params = [
            ':equipment_id' => $equipmentId,
            ':start_time' => $startTime,
            ':end_time' => $endTime,
        ];
        if ($excludeRequestId) {
            $sql .= " AND id <> :id";
            $params[':id'] = $excludeRequestId;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function approveRequest(int $requestId, int $approvedBy): void
    {
        $request = $this->find('borrow_requests', $requestId);
        if (!$request) throw new Exception('Borrow request not found.');
        if ($request['status'] !== 'pending') throw new Exception('Only pending requests can be approved.');

        $equipment = $this->find('equipment', (int)$request['equipment_id']);
        if (!$equipment || $equipment['status'] !== 'available') {
            throw new Exception('This equipment is not available for borrowing.');
        }

        if ($this->hasTimeConflict((int)$request['equipment_id'], $request['start_time'], $request['expected_return_time'], $requestId)) {
            throw new Exception('This equipment already has an overlapping pending or approved request.');
        }

        $this->update('borrow_requests', $requestId, [
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function rejectRequest(int $requestId, int $approvedBy): void
    {
        $request = $this->find('borrow_requests', $requestId);
        if (!$request) throw new Exception('Borrow request not found.');
        if (!in_array($request['status'], ['pending','approved'], true)) throw new Exception('This request cannot be rejected.');

        $this->update('borrow_requests', $requestId, [
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function checkout(int $requestId, int $technicianId): void
    {
        $request = $this->find('borrow_requests', $requestId);
        if (!$request) throw new Exception('Borrow request not found.');
        if ($request['status'] !== 'approved') throw new Exception('Only approved requests can be checked out.');

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM borrow_records WHERE request_id = :request_id');
        $stmt->execute([':request_id' => $requestId]);
        if ((int)$stmt->fetchColumn() > 0) throw new Exception('This request already has a borrow record.');

        $equipment = $this->find('equipment', (int)$request['equipment_id']);
        if (!$equipment || $equipment['status'] !== 'available') {
            throw new Exception('This equipment is not available for checkout.');
        }

        $this->pdo->beginTransaction();
        try {
            $this->insert('borrow_records', [
                'request_id' => $requestId,
                'checkout_by' => $technicianId,
                'checkout_time' => date('Y-m-d H:i:s'),
                'expected_return_time' => $request['expected_return_time'],
                'condition_out' => 'Checked before handover. Device is usable.',
                'used_hours' => 0,
                'status' => 'checked_out',
                'note' => 'Created by checkout action.'
            ]);
            $this->update('equipment', (int)$request['equipment_id'], ['status' => 'borrowed']);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function returnRecord(int $recordId, int $technicianId, string $returnStatus): void
    {
        $record = $this->find('borrow_records', $recordId);
        if (!$record) throw new Exception('Borrow record not found.');
        if ($record['status'] !== 'checked_out' && $record['status'] !== 'overdue') {
            throw new Exception('Only checked-out or overdue records can be returned.');
        }
        $request = $this->find('borrow_requests', (int)$record['request_id']);
        if (!$request) throw new Exception('Related request not found.');

        $checkout = strtotime($record['checkout_time']);
        $now = time();
        $usedHours = max(0, round(($now - $checkout) / 3600, 2));

        $this->pdo->beginTransaction();
        try {
            $condition = $returnStatus === 'returned'
                ? 'Returned in good working condition.'
                : 'Returned with issue. Damage/lost report was generated by the system.';

            $this->update('borrow_records', $recordId, [
                'checkin_by' => $technicianId,
                'checkin_time' => date('Y-m-d H:i:s'),
                'condition_in' => $condition,
                'used_hours' => $usedHours,
                'status' => $returnStatus
            ]);

            $equipmentStatus = $returnStatus === 'returned' ? 'available' : 'damaged';
            $this->update('equipment', (int)$request['equipment_id'], [
                'status' => $equipmentStatus,
                'total_used_hours' => (float)$this->find('equipment', (int)$request['equipment_id'])['total_used_hours'] + $usedHours
            ]);

            $this->update('borrow_requests', (int)$request['id'], ['status' => 'completed']);

            if (in_array($returnStatus, ['damaged', 'lost'], true)) {
                $equipment = $this->find('equipment', (int)$request['equipment_id']);
                $repairCost = $returnStatus === 'lost' ? (float)$equipment['purchase_price'] : round((float)$equipment['purchase_price'] * 0.15, 2);
                $penalty = $returnStatus === 'lost' ? (float)$equipment['purchase_price'] : round((float)$equipment['purchase_price'] * 0.10, 2);
                $damageId = $this->insert('damage_reports', [
                    'record_id' => $recordId,
                    'equipment_id' => (int)$request['equipment_id'],
                    'reported_by' => $technicianId,
                    'severity' => $returnStatus === 'lost' ? 'critical' : 'medium',
                    'description' => $returnStatus === 'lost' ? 'Equipment was reported lost during return processing.' : 'Equipment was returned damaged during check-in.',
                    'repair_cost' => $repairCost,
                    'penalty_amount' => $penalty,
                    'status' => 'charged',
                    'reported_at' => date('Y-m-d H:i:s')
                ]);

                $this->insert('penalty_payments', [
                    'damage_report_id' => $damageId,
                    'paid_by' => (int)$request['user_id'],
                    'amount' => $penalty,
                    'payment_method' => 'cash',
                    'payment_status' => 'unpaid',
                    'note' => 'Auto-generated after damaged/lost return.'
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
