<?php
class MaintenanceModel extends BaseModel
{
    public function startSchedule(int $scheduleId): void
    {
        $schedule = $this->find('maintenance_schedules', $scheduleId);
        if (!$schedule) throw new Exception('Maintenance schedule not found.');
        if (!in_array($schedule['status'], ['planned','overdue'], true)) {
            throw new Exception('Only planned or overdue schedules can be started.');
        }
        $this->pdo->beginTransaction();
        try {
            $this->update('maintenance_schedules', $scheduleId, ['status' => 'in_progress']);
            $this->update('equipment', (int)$schedule['equipment_id'], ['status' => 'maintenance']);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function completeSchedule(int $scheduleId, int $technicianId): void
    {
        $schedule = $this->find('maintenance_schedules', $scheduleId);
        if (!$schedule) throw new Exception('Maintenance schedule not found.');
        if ($schedule['status'] === 'completed') throw new Exception('This schedule is already completed.');

        $nextDue = null;
        if (!empty($schedule['frequency_days'])) {
            $nextDue = date('Y-m-d', strtotime('+' . (int)$schedule['frequency_days'] . ' days'));
        }

        $this->pdo->beginTransaction();
        try {
            $this->insert('maintenance_logs', [
                'schedule_id' => $scheduleId,
                'equipment_id' => (int)$schedule['equipment_id'],
                'technician_id' => $technicianId,
                'performed_date' => date('Y-m-d'),
                'action_taken' => ucfirst($schedule['maintenance_type']) . ' completed. Device was inspected and tested.',
                'cost' => 0,
                'next_due_date' => $nextDue,
                'result_status' => 'passed'
            ]);
            $this->update('maintenance_schedules', $scheduleId, ['status' => 'completed']);
            $this->update('equipment', (int)$schedule['equipment_id'], ['status' => 'available']);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
