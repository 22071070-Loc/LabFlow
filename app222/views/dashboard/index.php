<section class="cards-grid">
    <?php foreach ($stats as $label => $value): ?>
        <article class="stat-card">
            <span><?= e($label) ?></span>
            <strong><?= e($value) ?></strong>
        </article>
    <?php endforeach; ?>
</section>

<section class="panel">
    <div class="panel-header">
        <div>
            <h2>Recent Borrow Requests</h2>
        </div>
        <a class="btn btn-primary" href="<?= url('borrow_requests/create') ?>">New Request</a>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th><th>Borrower</th><th>Equipment</th><th>Status</th><th>Start</th><th>Expected Return</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentRequests as $r): ?>
                    <tr>
                        <td>#<?= e($r['id']) ?></td>
                        <td><?= e($r['full_name']) ?></td>
                        <td><?= e($r['asset_code']) ?> - <?= e($r['equipment_name']) ?></td>
                        <td><span class="badge badge-<?= e($r['status']) ?>"><?= e($r['status']) ?></span></td>
                        <td><?= e($r['start_time']) ?></td>
                        <td><?= e($r['expected_return_time']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
