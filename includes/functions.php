<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/schema.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!currentUser()) {
        redirect(baseUrl('auth/login.php'));
    }
}

function baseUrl(string $path = ''): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $markers = ['/modules/', '/actions/', '/auth/'];
    $base = '';
    foreach ($markers as $marker) {
        $pos = strpos($script, $marker);
        if ($pos !== false) {
            $base = substr($script, 0, $pos);
            break;
        }
    }
    if ($base === '') {
        $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
        $base = ($dir === '/' || $dir === '.') ? '' : $dir;
    }
    return $base . '/' . ltrim($path, '/');
}

function getTableConfig(string $table): array
{
    $tables = appTables();
    if (!isset($tables[$table])) {
        http_response_code(404);
        die('Unknown table.');
    }
    return $tables[$table];
}

function labelFor(string $field): string
{
    $labels = fieldLabels();
    return $labels[$field] ?? ucwords(str_replace('_', ' ', $field));
}

function getRows(string $table, string $orderBy = 'id DESC'): array
{
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY $orderBy");
    return $stmt->fetchAll();
}

function getRow(string $table, int $id): ?array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function fkOptions(string $field): array
{
    $fks = foreignKeys();
    if (!isset($fks[$field])) {
        return [];
    }
    $fk = $fks[$field];
    $table = $fk['table'];
    $display = $fk['display'];
    $rows = getRows($table, 'id ASC');
    $options = [];
    foreach ($rows as $row) {
        $parts = [];
        foreach ($display as $col) {
            if (array_key_exists($col, $row)) {
                $parts[] = $row[$col];
            }
        }
        $options[$row['id']] = '#' . $row['id'] . ' - ' . implode(' | ', array_filter(array_map('strval', $parts)));
    }
    return $options;
}

function fkDisplay(string $field, $id): string
{
    if ($id === null || $id === '') {
        return '-';
    }
    $fks = foreignKeys();
    if (!isset($fks[$field])) {
        return (string)$id;
    }
    $row = getRow($fks[$field]['table'], (int)$id);
    if (!$row) {
        return '#' . $id;
    }
    $parts = [];
    foreach ($fks[$field]['display'] as $col) {
        if (array_key_exists($col, $row)) {
            $parts[] = $row[$col];
        }
    }
    return '#' . $id . ' - ' . implode(' | ', array_filter(array_map('strval', $parts)));
}

function fieldType(string $field): string
{
    if (str_ends_with($field, '_at') || str_ends_with($field, '_time')) return 'datetime-local';
    if (str_ends_with($field, '_date')) return 'date';
    if (in_array($field, ['capacity', 'maintenance_cycle_days', 'calibration_cycle_days', 'frequency_days', 'usage_hour_interval'], true)) return 'number';
    if (in_array($field, ['purchase_price', 'total_used_hours', 'used_hours', 'cost', 'repair_cost', 'penalty_amount', 'amount'], true)) return 'number-step';
    if (in_array($field, ['description', 'purpose', 'note', 'condition_out', 'condition_in', 'action_taken', 'address'], true)) return 'textarea';
    if ($field === 'password') return 'password';
    return 'text';
}

function normalizeDateTimeForInput($value): string
{
    if (!$value) return '';
    $time = strtotime((string)$value);
    return $time ? date('Y-m-d\TH:i', $time) : (string)$value;
}

function normalizeValueFromPost(string $field, $value)
{
    if ($value === '') {
        return null;
    }
    if (fieldType($field) === 'datetime-local') {
        return str_replace('T', ' ', (string)$value) . (strlen((string)$value) === 16 ? ':00' : '');
    }
    return $value;
}

function renderField(string $table, string $field, $value = null, bool $editing = false): void
{
    $config = getTableConfig($table);
    $required = in_array($field, $config['required'] ?? [], true) ? 'required' : '';
    $type = fieldType($field);
    $enumValues = enumOptionsFor($table, $field);
    $fks = foreignKeys();

    echo '<div class="form-group">';
    echo '<label for="' . h($field) . '">' . h(labelFor($field)) . '</label>';

    if ($field === 'password') {
        echo '<input type="password" id="password" name="password" placeholder="Leave blank to keep current password" ' . (!$editing ? 'required' : '') . '>';
    } elseif (isset($fks[$field])) {
        echo '<select id="' . h($field) . '" name="' . h($field) . '" ' . $required . '>';
        echo '<option value="">-- Select --</option>';
        foreach (fkOptions($field) as $id => $label) {
            $selected = ((string)$id === (string)$value) ? 'selected' : '';
            echo '<option value="' . h($id) . '" ' . $selected . '>' . h($label) . '</option>';
        }
        echo '</select>';
    } elseif ($enumValues) {
        echo '<select id="' . h($field) . '" name="' . h($field) . '" ' . $required . '>';
        echo '<option value="">-- Select --</option>';
        foreach ($enumValues as $option) {
            $selected = ((string)$option === (string)$value) ? 'selected' : '';
            echo '<option value="' . h($option) . '" ' . $selected . '>' . h($option) . '</option>';
        }
        echo '</select>';
    } elseif ($type === 'textarea') {
        echo '<textarea id="' . h($field) . '" name="' . h($field) . '" rows="4" ' . $required . '>' . h($value) . '</textarea>';
    } elseif ($type === 'datetime-local') {
        echo '<input type="datetime-local" id="' . h($field) . '" name="' . h($field) . '" value="' . h(normalizeDateTimeForInput($value)) . '" ' . $required . '>';
    } elseif ($type === 'number-step') {
        echo '<input type="number" step="0.01" id="' . h($field) . '" name="' . h($field) . '" value="' . h($value) . '" ' . $required . '>';
    } else {
        $htmlType = $type === 'number' || $type === 'date' ? $type : 'text';
        echo '<input type="' . h($htmlType) . '" id="' . h($field) . '" name="' . h($field) . '" value="' . h($value) . '" ' . $required . '>';
    }

    echo '</div>';
}

function tableInsert(string $table, array $data): int
{
    $pdo = getPDO();
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($columns), '?');
    $sql = 'INSERT INTO `' . $table . '` (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', $placeholders) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
    return (int)$pdo->lastInsertId();
}

function tableUpdate(string $table, int $id, array $data): void
{
    $pdo = getPDO();
    $set = [];
    foreach (array_keys($data) as $col) {
        $set[] = '`' . $col . '` = ?';
    }
    $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $set) . ' WHERE id = ?';
    $values = array_values($data);
    $values[] = $id;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
}

function tableDelete(string $table, int $id): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
    $stmt->execute([$id]);
}

function buildDataFromPost(string $table, bool $editing = false): array
{
    $config = getTableConfig($table);
    $data = [];
    foreach ($config['form'] as $field) {
        if ($field === 'password') {
            $password = trim($_POST['password'] ?? '');
            if ($password !== '') {
                $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            } elseif (!$editing) {
                $data['password_hash'] = password_hash('123456', PASSWORD_DEFAULT);
            }
            continue;
        }
        if (array_key_exists($field, $_POST)) {
            $data[$field] = normalizeValueFromPost($field, $_POST[$field]);
        }
    }
    return $data;
}

function statusBadge($status): string
{
    if ($status === null || $status === '') return '-';
    $class = 'badge';
    $map = [
        'available' => 'success', 'active' => 'success', 'approved' => 'success', 'returned' => 'success', 'completed' => 'success', 'paid' => 'success', 'passed' => 'success',
        'pending' => 'warning', 'planned' => 'warning', 'in_progress' => 'warning', 'partial' => 'warning', 'reviewing' => 'warning', 'charged' => 'warning', 'needs_repair' => 'warning',
        'borrowed' => 'info', 'maintenance' => 'info', 'checked_out' => 'info',
        'damaged' => 'danger', 'retired' => 'danger', 'rejected' => 'danger', 'lost' => 'danger', 'failed' => 'danger', 'overdue' => 'danger', 'inactive' => 'danger',
        'cancelled' => 'muted', 'closed' => 'muted', 'unpaid' => 'muted', 'reported' => 'muted',
    ];
    $class .= ' ' . ($map[$status] ?? 'muted');
    return '<span class="' . $class . '">' . h($status) . '</span>';
}

function countTable(string $table, string $where = '1=1'): int
{
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM `$table` WHERE $where");
    return (int)$stmt->fetch()['c'];
}

function userCanManage(): bool
{
    $user = currentUser();
    return $user && in_array($user['role'], ['admin', 'technician'], true);
}

function validateBusinessRules(string $table, array $data, ?int $id = null): array
{
    $errors = [];
    $pdo = getPDO();

    if ($table === 'borrow_requests') {
        if (!empty($data['start_time']) && !empty($data['expected_return_time']) && strtotime($data['expected_return_time']) <= strtotime($data['start_time'])) {
            $errors[] = 'Expected return time must be later than start time.';
        }

        if (!empty($data['equipment_id']) && ($data['status'] ?? 'pending') !== 'rejected' && ($data['status'] ?? 'pending') !== 'cancelled') {
            $stmt = $pdo->prepare("SELECT status FROM equipment WHERE id = ?");
            $stmt->execute([$data['equipment_id']]);
            $eq = $stmt->fetch();
            if ($eq && $eq['status'] !== 'available' && $id === null) {
                $errors[] = 'This equipment is not available. Current status: ' . $eq['status'];
            }

            if (!empty($data['start_time']) && !empty($data['expected_return_time'])) {
                $sql = "SELECT COUNT(*) AS c FROM borrow_requests\n                        WHERE equipment_id = ?\n                        AND status IN ('pending','approved')\n                        AND NOT (expected_return_time <= ? OR start_time >= ?)";
                $params = [$data['equipment_id'], $data['start_time'], $data['expected_return_time']];
                if ($id !== null) {
                    $sql .= " AND id <> ?";
                    $params[] = $id;
                }
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                if ((int)$stmt->fetch()['c'] > 0) {
                    $errors[] = 'There is already a pending/approved request for this equipment during the selected time.';
                }
            }
        }
    }

    if ($table === 'borrow_records') {
        if (!empty($data['checkin_time']) && !empty($data['checkout_time']) && strtotime($data['checkin_time']) < strtotime($data['checkout_time'])) {
            $errors[] = 'Check-in time cannot be earlier than checkout time.';
        }
    }

    return $errors;
}
