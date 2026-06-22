<?php
class MaintenanceSchedulesController extends CrudController
{
    public function __construct()
    {
        parent::__construct('maintenance_schedules');
    }

    public function start(): void
    {
        $this->authorize('start');
        $id = (int)($_GET['id'] ?? 0);
        try {
            (new MaintenanceModel($this->pdo))->startSchedule($id);
            $this->flash('success', 'Maintenance schedule started. Equipment status changed to maintenance.');
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('maintenance_schedules/index');
    }

    public function complete(): void
    {
        $this->authorize('complete');
        $id = (int)($_GET['id'] ?? 0);
        try {
            (new MaintenanceModel($this->pdo))->completeSchedule($id, Auth::id());
            $this->flash('success', 'Maintenance schedule completed and maintenance log created.');
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('maintenance_schedules/index');
    }
}
