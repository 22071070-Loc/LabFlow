<?php
class DamageReportsController extends CrudController
{
    public function __construct()
    {
        parent::__construct('damage_reports');
    }

    public function markPaid(): void
    {
        $this->authorize('markPaid');
        $id = (int)($_GET['id'] ?? 0);
        try {
            (new DamageModel($this->pdo))->markPaid($id);
            $this->flash('success', 'Damage report marked as paid and payment record updated.');
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('damage_reports/index');
    }
}
