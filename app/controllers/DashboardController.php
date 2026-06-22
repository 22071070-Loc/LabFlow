<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();
        $model = new BaseModel($this->pdo);

        $stats = [
            'Equipment' => $model->count('equipment'),
            'Available' => $this->countWhere('equipment', 'status', 'available'),
            'Borrowed' => $this->countWhere('equipment', 'status', 'borrowed'),
            'Pending Requests' => $this->countWhere('borrow_requests', 'status', 'pending'),
            'Damage Reports' => $model->count('damage_reports'),
            'Maintenance Plans' => $model->count('maintenance_schedules'),
        ];

        $stmt = $this->pdo->query("\n            SELECT br.id, u.full_name, e.asset_code, e.equipment_name, br.status, br.start_time, br.expected_return_time\n            FROM borrow_requests br\n            JOIN users u ON u.id = br.user_id\n            JOIN equipment e ON e.id = br.equipment_id\n            ORDER BY br.id DESC\n            LIMIT 8\n        ");
        $recentRequests = $stmt->fetchAll();

        $this->view('dashboard/index', compact('stats','recentRequests'));
    }

    private function countWhere(string $table, string $field, string $value): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE `$field` = :value");
        $stmt->execute([':value' => $value]);
        return (int)$stmt->fetchColumn();
    }
}
