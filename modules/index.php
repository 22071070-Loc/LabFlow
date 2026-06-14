<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$table = $_GET['table'] ?? 'equipment';
$config = getTableConfig($table);
$pageTitle = $config['title'];
$rows = getRows($table, 'id DESC');
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <div class="toolbar">
        <div>
            <h2><?= h($config['icon'] . ' ' . $config['title']) ?></h2>
            <p class="help"><?= h($config['description']) ?></p>
        </div>
        <div class="actions">
            <a class="btn" href="<?= h(baseUrl('modules/create.php?table=' . $table)) ?>">+ Add New</a>
            <?php if ($table === 'borrow_requests'): ?>
                <a class="btn secondary" href="<?= h(baseUrl('modules/index.php?table=borrow_records')) ?>">Borrow Records</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <?php foreach ($config['list'] as $field): ?>
                        <th><?= h(labelFor($field)) ?></th>
                    <?php endforeach; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$rows): ?>
                    <tr><td colspan="<?= count($config['list']) + 1 ?>">No data available.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($config['list'] as $field): ?>
                            <td class="<?= in_array($field, ['purpose','description','note','action_taken','condition_out','condition_in'], true) ? 'cell-small' : '' ?>">
                                <?php
                                if (isset(foreignKeys()[$field])) {
                                    echo h(fkDisplay($field, $row[$field] ?? null));
                                } elseif ($field === 'status' || $field === 'role' || $field === 'result_status' || $field === 'severity' || $field === 'payment_status') {
                                    echo statusBadge($row[$field] ?? '');
                                } elseif (in_array($field, ['purchase_price','cost','repair_cost','penalty_amount','amount'], true)) {
                                    echo number_format((float)($row[$field] ?? 0), 0) . ' VND';
                                } else {
                                    echo h($row[$field] ?? '');
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <div class="actions">
                                <a class="btn small secondary" href="<?= h(baseUrl('modules/edit.php?table=' . $table . '&id=' . $row['id'])) ?>">Edit</a>
                                <a class="btn small danger" data-confirm="Delete this record?" href="<?= h(baseUrl('modules/delete.php?table=' . $table . '&id=' . $row['id'])) ?>">Delete</a>

                                <?php if ($table === 'borrow_requests'): ?>
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <a class="btn small success" href="<?= h(baseUrl('actions/borrow_approve.php?id=' . $row['id'])) ?>">Approve</a>
                                        <a class="btn small danger" href="<?= h(baseUrl('actions/borrow_reject.php?id=' . $row['id'])) ?>">Reject</a>
                                    <?php endif; ?>
                                    <?php if ($row['status'] === 'approved'): ?>
                                        <?php
                                        $stmtHasRecord = getPDO()->prepare('SELECT COUNT(*) AS c FROM borrow_records WHERE request_id = ?');
                                        $stmtHasRecord->execute([$row['id']]);
                                        $hasRecord = (int)$stmtHasRecord->fetch()['c'] > 0;
                                        ?>
                                        <?php if (!$hasRecord): ?>
                                            <a class="btn small warning" href="<?= h(baseUrl('actions/borrow_checkout.php?id=' . $row['id'])) ?>">Check-out</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($table === 'borrow_records' && $row['status'] === 'checked_out'): ?>
                                    <a class="btn small success" href="<?= h(baseUrl('actions/borrow_return.php?id=' . $row['id'] . '&condition=good')) ?>">Return Good</a>
                                    <a class="btn small danger" data-confirm="Mark as damaged and create damage report?" href="<?= h(baseUrl('actions/borrow_return.php?id=' . $row['id'] . '&condition=damaged')) ?>">Return Damaged</a>
                                <?php endif; ?>

                                <?php if ($table === 'maintenance_schedules'): ?>
                                    <?php if ($row['status'] === 'planned'): ?>
                                        <a class="btn small warning" href="<?= h(baseUrl('actions/maintenance_start.php?id=' . $row['id'])) ?>">Start</a>
                                    <?php endif; ?>
                                    <?php if (in_array($row['status'], ['planned', 'in_progress', 'overdue'], true)): ?>
                                        <a class="btn small success" href="<?= h(baseUrl('actions/maintenance_complete.php?id=' . $row['id'])) ?>">Complete</a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($table === 'damage_reports' && in_array($row['status'], ['reported', 'reviewing', 'charged'], true)): ?>
                                    <a class="btn small success" href="<?= h(baseUrl('actions/damage_mark_paid.php?id=' . $row['id'])) ?>">Mark Paid</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
