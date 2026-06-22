<?php
class BorrowRecordsController extends CrudController
{
    public function __construct()
    {
        parent::__construct('borrow_records');
    }

    public function index(): void
    {
        $this->authorize('index');

        if (Auth::isStudent()) {
            $stmt = $this->pdo->prepare("
                SELECT br.*
                FROM borrow_records br
                JOIN borrow_requests rq ON rq.id = br.request_id
                WHERE rq.user_id = :user_id
                ORDER BY br.id DESC
            " );
            $stmt->execute([':user_id' => Auth::id()]);
            $rows = $stmt->fetchAll();
        } else {
            $rows = $this->model->getAll($this->schema);
        }

        $options = $this->loadRelationOptions();
        $this->view('crud/index', [
            'moduleKey' => $this->moduleKey,
            'schema' => $this->schema,
            'rows' => $rows,
            'options' => $options,
            'canCreate' => $this->can('create'),
            'canEdit' => $this->can('edit'),
            'canDelete' => $this->can('delete'),
        ]);
    }

    public function checkout(): void
    {
        $this->authorize('checkout');
        $requestId = (int)($_GET['request_id'] ?? 0);
        try {
            (new BorrowModel($this->pdo))->checkout($requestId, Auth::id());
            $this->flash('success', 'Equipment checked out successfully.');
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('borrow_requests/index');
    }

    public function returnGood(): void
    {
        $this->returnWithStatus('returned');
    }

    public function returnDamaged(): void
    {
        $this->returnWithStatus('damaged');
    }

    public function returnLost(): void
    {
        $this->returnWithStatus('lost');
    }

    private function returnWithStatus(string $status): void
    {
        $this->authorize('returnGood');
        $recordId = (int)($_GET['id'] ?? 0);
        try {
            (new BorrowModel($this->pdo))->returnRecord($recordId, Auth::id(), $status);
            $this->flash('success', 'Borrow record returned as ' . $status . '.');
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect('borrow_records/index');
    }
}
