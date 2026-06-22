<section class="panel">
    <div class="panel-header">
        <div>
            <h2><?= e($schema['title']) ?></h2>
        </div>
        <?php if ($canCreate): ?>
            <a class="btn btn-primary" href="<?= url($moduleKey . '/create') ?>">+ Add New</a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <?php foreach ($schema['list'] as $col): ?>
                        <th><?= e(ucwords(str_replace('_',' ', $col))) ?></th>
                    <?php endforeach; ?>
                    <th class="actions-col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="<?= count($schema['list']) + 1 ?>" class="empty">No records found.</td></tr>
                <?php endif; ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($schema['list'] as $col): ?>
                            <td>
                                <?php
                                    $value = $row[$col] ?? '';
                                    if (isset($options[$col]) && $value !== null) {
                                        echo e($options[$col][(string)$value] ?? ('#' . $value));
                                    } elseif ($col === 'status' || str_ends_with($col, '_status') || $col === 'role') {
                                        echo '<span class="badge badge-' . e((string)$value) . '">' . e((string)$value) . '</span>';
                                    } else {
                                        echo e(mb_strimwidth((string)$value, 0, 70, '...'));
                                    }
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="actions-cell"><div class="actions">
                            <?php if ($canEdit): ?>
                                <a class="btn btn-sm" href="<?= url($moduleKey . '/edit', ['id'=>$row['id']]) ?>">Edit</a>
                            <?php endif; ?>
                            <?php if ($canDelete): ?>
                                <a class="btn btn-sm btn-danger" onclick="return confirm('Delete this record?')" href="<?= url($moduleKey . '/delete', ['id'=>$row['id']]) ?>">Delete</a>
                            <?php endif; ?>

                            <?php if ($moduleKey === 'borrow_requests' && in_array(Auth::role(), ['admin','technician'], true) && $row['status'] === 'pending'): ?>
                                <a class="btn btn-sm btn-success" href="<?= url('borrow_requests/approve', ['id'=>$row['id']]) ?>">Approve</a>
                                <a class="btn btn-sm btn-warning" href="<?= url('borrow_requests/reject', ['id'=>$row['id']]) ?>">Reject</a>
                            <?php endif; ?>
                            <?php if ($moduleKey === 'borrow_requests' && in_array(Auth::role(), ['admin','technician'], true) && $row['status'] === 'approved'): ?>
                                <a class="btn btn-sm btn-primary" href="<?= url('borrow_records/checkout', ['request_id'=>$row['id']]) ?>">Check-out</a>
                            <?php endif; ?>

                            <?php if ($moduleKey === 'borrow_records' && in_array(Auth::role(), ['admin','technician'], true) && in_array($row['status'], ['checked_out','overdue'], true)): ?>
                                <a class="btn btn-sm btn-success" href="<?= url('borrow_records/returnGood', ['id'=>$row['id']]) ?>">Return Good</a>
                                <a class="btn btn-sm btn-warning" href="<?= url('borrow_records/returnDamaged', ['id'=>$row['id']]) ?>">Return Damaged</a>
                                <a class="btn btn-sm btn-danger" href="<?= url('borrow_records/returnLost', ['id'=>$row['id']]) ?>">Lost</a>
                            <?php endif; ?>

                            <?php if ($moduleKey === 'maintenance_schedules' && in_array(Auth::role(), ['admin','technician'], true)): ?>
                                <?php if (in_array($row['status'], ['planned','overdue'], true)): ?>
                                    <a class="btn btn-sm btn-warning" href="<?= url('maintenance_schedules/start', ['id'=>$row['id']]) ?>">Start</a>
                                <?php endif; ?>
                                <?php if ($row['status'] === 'in_progress'): ?>
                                    <a class="btn btn-sm btn-success" href="<?= url('maintenance_schedules/complete', ['id'=>$row['id']]) ?>">Complete</a>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if ($moduleKey === 'damage_reports' && in_array(Auth::role(), ['admin','technician'], true) && !in_array($row['status'], ['paid','closed'], true)): ?>
                                <a class="btn btn-sm btn-success" href="<?= url('damage_reports/markPaid', ['id'=>$row['id']]) ?>">Mark Paid</a>
                            <?php endif; ?>
                        </div></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
