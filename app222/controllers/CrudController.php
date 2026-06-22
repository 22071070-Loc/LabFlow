<?php
class CrudController extends Controller
{
    protected string $moduleKey;
    protected array $schema;
    protected BaseModel $model;

    public function __construct(string $moduleKey)
    {
        parent::__construct();
        $this->moduleKey = $moduleKey;
        $this->schema = Schema::get($moduleKey);
        $this->model = new BaseModel($this->pdo);
    }

    protected function authorize(string $action): void
    {
        Auth::requireLogin();
        $roles = $this->schema['roles'][$action] ?? ['admin'];
        if (!in_array(Auth::role(), $roles, true)) {
            http_response_code(403);
            die('403 Forbidden - You do not have permission to access this module/action.');
        }
    }

    public function index(): void
    {
        $this->authorize('index');
        $where = $this->studentWhere();
        $rows = $this->model->getAll($this->schema, $where);
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

    public function viewAction(): void
    {
        $this->authorize('view');
        $id = (int)($_GET['id'] ?? 0);
        $row = $this->model->find($this->schema['table'], $id);
        if (!$row) die('Record not found.');
        $this->ensureOwnStudentRecord($row);
        $options = $this->loadRelationOptions();
        $this->view('crud/view', compact('row','options') + ['schema'=>$this->schema, 'moduleKey'=>$this->moduleKey]);
    }

    public function create(): void
    {
        $this->authorize('create');
        $row = [];
        $options = $this->loadRelationOptions();
        $mode = 'create';
        $this->view('crud/form', compact('row','options','mode') + ['schema'=>$this->schema, 'moduleKey'=>$this->moduleKey]);
    }

    public function store(): void
    {
        $this->authorize('store');
        $this->requirePost();
        try {
            $data = $this->collectData();
            $this->validateData($data);
            $this->beforeStore($data);
            $this->model->insert($this->schema['table'], $data);
            $this->flash('success', $this->schema['title'] . ' record created successfully.');
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect($this->moduleKey . '/index');
    }

    public function edit(): void
    {
        $this->authorize('edit');
        $id = (int)($_GET['id'] ?? 0);
        $row = $this->model->find($this->schema['table'], $id);
        if (!$row) die('Record not found.');
        $this->ensureOwnStudentRecord($row);
        $options = $this->loadRelationOptions();
        $mode = 'edit';
        $this->view('crud/form', compact('row','options','mode') + ['schema'=>$this->schema, 'moduleKey'=>$this->moduleKey]);
    }

    public function update(): void
    {
        $this->authorize('update');
        $this->requirePost();
        $id = (int)($_POST['id'] ?? 0);
        $row = $this->model->find($this->schema['table'], $id);
        if (!$row) die('Record not found.');
        $this->ensureOwnStudentRecord($row);
        try {
            $data = $this->collectData($id);
            $this->validateData($data, $id);
            $this->beforeUpdate($id, $data, $row);
            $this->model->update($this->schema['table'], $id, $data);
            $this->flash('success', $this->schema['title'] . ' record updated successfully.');
        } catch (Throwable $e) {
            $this->flash('danger', $e->getMessage());
        }
        $this->redirect($this->moduleKey . '/index');
    }

    public function delete(): void
    {
        $this->authorize('delete');
        $id = (int)($_GET['id'] ?? 0);
        try {
            $this->beforeDelete($id);
            $this->model->delete($this->schema['table'], $id);
            $this->flash('success', 'Record deleted successfully.');
        } catch (Throwable $e) {
            $this->flash('danger', 'Cannot delete this record. It may be referenced by another table. ' . $e->getMessage());
        }
        $this->redirect($this->moduleKey . '/index');
    }

    protected function can(string $action): bool
    {
        $roles = $this->schema['roles'][$action] ?? [];
        return Auth::check() && in_array(Auth::role(), $roles, true);
    }

    protected function collectData(?int $id = null): array
    {
        $data = [];
        foreach ($this->schema['fields'] as $field => $meta) {
            if (!empty($meta['virtual'])) {
                continue;
            }
            if (!empty($meta['admin_only']) && !Auth::isAdmin()) {
                continue;
            }
            if (!empty($meta['student_self']) && Auth::isStudent()) {
                $data[$field] = Auth::id();
                continue;
            }
            if (!empty($meta['default_current_user']) && empty($_POST[$field])) {
                $data[$field] = Auth::id();
                continue;
            }
            $value = $_POST[$field] ?? null;
            if (($meta['type'] ?? '') === 'datetime-local' && $value) {
                $value = str_replace('T', ' ', $value) . (strlen($value) === 16 ? ':00' : '');
            }
            if (in_array($field, ['student_code', 'code'], true) && is_string($value)) {
                $value = strtoupper(trim($value));
            }
            $data[$field] = $value === '' ? null : $value;
        }

        if ($this->moduleKey === 'users') {
            $password = $_POST['password'] ?? '';
            if ($password !== '') {
                $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            } elseif ($id === null) {
                $data['password_hash'] = password_hash('123456', PASSWORD_DEFAULT);
            }
            if (($data['role'] ?? '') !== 'student') {
                $data['student_code'] = null;
            }
        }

        return $data;
    }

    protected function validateData(array $data, ?int $id = null): void
    {
        foreach ($this->schema['fields'] as $field => $meta) {
            if (!empty($meta['virtual'])) continue;
            if (!empty($meta['admin_only']) && !Auth::isAdmin()) continue;
            if (!empty($meta['required']) && (empty($data[$field]) && $data[$field] !== '0')) {
                throw new Exception(($meta['label'] ?? $field) . ' is required.');
            }
        }

        if ($this->moduleKey === 'users') {
            if (($data['role'] ?? '') === 'student' && empty($data['student_code'])) {
                throw new Exception('Student ID is required for student accounts.');
            }
            if (($data['role'] ?? '') !== 'student') {
                $data['student_code'] = null;
            }
        }
        if ($this->moduleKey === 'users' && !empty($data['email']) && $this->model->exists('users', 'email', $data['email'], $id)) {
            throw new Exception('Email already exists.');
        }
        if ($this->moduleKey === 'users' && !empty($data['student_code']) && $this->model->exists('users', 'student_code', $data['student_code'], $id)) {
            throw new Exception('Student ID already exists.');
        }
        if ($this->moduleKey === 'departments' && !empty($data['department_code']) && $this->model->exists('departments', 'department_code', $data['department_code'], $id)) {
            throw new Exception('Department code already exists.');
        }
        if ($this->moduleKey === 'labs' && !empty($data['lab_code']) && $this->model->exists('labs', 'lab_code', $data['lab_code'], $id)) {
            throw new Exception('Lab code already exists.');
        }
        if ($this->moduleKey === 'equipment_categories' && !empty($data['category_name']) && $this->model->exists('equipment_categories', 'category_name', $data['category_name'], $id)) {
            throw new Exception('Category name already exists.');
        }
        if ($this->moduleKey === 'equipment' && !empty($data['asset_code']) && $this->model->exists('equipment', 'asset_code', $data['asset_code'], $id)) {
            throw new Exception('Asset code already exists.');
        }

        if ($this->moduleKey === 'borrow_requests') {
            if (!empty($data['expected_return_time']) && !empty($data['start_time']) && $data['expected_return_time'] <= $data['start_time']) {
                throw new Exception('Expected return time must be later than start time.');
            }
            $equipment = $this->model->find('equipment', (int)$data['equipment_id']);
            if (!$equipment || $equipment['status'] !== 'available') {
                // Admin can edit status fields, but creating a new request should follow the availability rule.
                if ($id === null) throw new Exception('This equipment is not available for borrowing.');
            }
            $borrowModel = new BorrowModel($this->pdo);
            if (!empty($data['equipment_id']) && !empty($data['start_time']) && !empty($data['expected_return_time']) &&
                $borrowModel->hasTimeConflict((int)$data['equipment_id'], $data['start_time'], $data['expected_return_time'], $id)) {
                throw new Exception('This equipment already has an overlapping pending or approved request.');
            }
        }
    }

    protected function beforeStore(array &$data): void
    {
        if ($this->moduleKey === 'borrow_requests' && Auth::isStudent()) {
            $data['user_id'] = Auth::id();
            $data['status'] = 'pending';
            unset($data['approved_by'], $data['approved_at']);
        }
    }

    protected function beforeUpdate(int $id, array &$data, array $old): void
    {
        if ($this->moduleKey === 'borrow_requests' && Auth::isStudent()) {
            if ($old['status'] !== 'pending') {
                throw new Exception('Students can only edit pending requests.');
            }
            $data['user_id'] = Auth::id();
            $data['status'] = 'pending';
            unset($data['approved_by'], $data['approved_at']);
        }
    }

    protected function beforeDelete(int $id): void
    {
        if ($this->moduleKey === 'equipment') {
            foreach (['borrow_requests'=>'equipment_id','maintenance_schedules'=>'equipment_id','maintenance_logs'=>'equipment_id','damage_reports'=>'equipment_id'] as $table => $field) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE `$field` = :id");
                $stmt->execute([':id' => $id]);
                if ((int)$stmt->fetchColumn() > 0) {
                    throw new Exception('Equipment is already referenced by ' . $table . '.');
                }
            }
        }
        if ($this->moduleKey === 'borrow_requests') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM borrow_records WHERE request_id = :id");
            $stmt->execute([':id' => $id]);
            if ((int)$stmt->fetchColumn() > 0) {
                throw new Exception('Cannot delete a request that already has a borrow record.');
            }
        }
    }

    protected function studentWhere(): ?array
    {
        if (!Auth::isStudent()) return null;
        if ($this->moduleKey === 'borrow_requests') return ['user_id' => Auth::id()];
        if ($this->moduleKey === 'penalty_payments') return ['paid_by' => Auth::id()];
        return null;
    }

    protected function ensureOwnStudentRecord(array $row): void
    {
        if (!Auth::isStudent()) return;
        if ($this->moduleKey === 'borrow_requests' && (int)$row['user_id'] !== Auth::id()) {
            die('403 Forbidden');
        }
        if ($this->moduleKey === 'penalty_payments' && (int)$row['paid_by'] !== Auth::id()) {
            die('403 Forbidden');
        }
    }

    protected function loadRelationOptions(): array
    {
        $options = [];
        foreach ($this->schema['fields'] as $field => $meta) {
            if (($meta['type'] ?? '') === 'relation') {
                $rel = $meta['relation'];
                $options[$field] = $this->model->relationOptions($rel['table'], $rel['label'], $rel['extra'] ?? null);
            }
        }
        return $options;
    }
}
