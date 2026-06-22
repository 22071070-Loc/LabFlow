<?php
class DamageModel extends BaseModel
{
    public function markPaid(int $damageReportId): void
    {
        $damage = $this->find('damage_reports', $damageReportId);
        if (!$damage) throw new Exception('Damage report not found.');

        $stmt = $this->pdo->prepare('SELECT * FROM penalty_payments WHERE damage_report_id = :id ORDER BY id DESC LIMIT 1');
        $stmt->execute([':id' => $damageReportId]);
        $payment = $stmt->fetch();

        $this->pdo->beginTransaction();
        try {
            if ($payment) {
                $this->update('penalty_payments', (int)$payment['id'], [
                    'amount' => $damage['penalty_amount'],
                    'payment_status' => 'paid',
                    'paid_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $paidBy = Auth::id() ?? $damage['reported_by'];
                $this->insert('penalty_payments', [
                    'damage_report_id' => $damageReportId,
                    'paid_by' => $paidBy,
                    'amount' => $damage['penalty_amount'],
                    'payment_method' => 'cash',
                    'payment_status' => 'paid',
                    'paid_at' => date('Y-m-d H:i:s'),
                    'note' => 'Created by mark paid action.'
                ]);
            }
            $this->update('damage_reports', $damageReportId, ['status' => 'paid']);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
