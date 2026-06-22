<?php
class BorrowRequestsController extends CrudController
{
    public function __construct()
    {
        parent::__construct('borrow_requests');
    }

    public function approve(): void
    {
        $this->authorize('approve');
        $id = (int)($_GET['id'] ?? 0);
        try {
            (new BorrowModel($this->pdo))->approveRequest($id, Auth::id());
            $this->flash('success', 'Borrow request approved.');
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('borrow_requests/index');
    }

    public function reject(): void
    {
        $this->authorize('reject');
        $id = (int)($_GET['id'] ?? 0);
        try {
            (new BorrowModel($this->pdo))->rejectRequest($id, Auth::id());
            $this->flash('success', 'Borrow request rejected.');
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('borrow_requests/index');
    }
}
