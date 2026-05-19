<?php
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$pageTitle = 'Dashboard';
$pdo = getPDO();

$stats = [
    ['label' => 'Total Equipment', 'value' => countTable('equipment'), 'icon' => '🔧'],
    ['label' => 'Available Equipment', 'value' => countTable('equipment', "status = 'available'"), 'icon' => '✅'],
    ['label' => 'Pending Requests', 'value' => countTable('borrow_requests', "status = 'pending'"), 'icon' => '📝'],
    ['label' => 'Damage Reports', 'value' => countTable('damage_reports'), 'icon' => '⚠️'],
];

$recentRequests = $pdo->query("\n    SELECT br.*, u.full_name AS requester, e.asset_code, e.equipment_name\n    FROM borrow_requests br\n    JOIN users u ON br.user_id = u.id\n    JOIN equipment e ON br.equipment_id = e.id\n    ORDER BY br.id DESC\n    LIMIT 6\n")->fetchAll();

$equipmentStatus = $pdo->query("SELECT status, COUNT(*) AS total FROM equipment GROUP BY status ORDER BY status")->fetchAll();
include __DIR__ . '/includes/header.php';
?>
<div class="grid cols-4">
    <?php foreach ($stats as $stat): ?>
        <div class="stat">
            <div class="label"><?= h($stat['icon'] . ' ' . $stat['label']) ?></div>
            <div class="num"><?= h($stat['value']) ?></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="grid cols-3" style="margin-top:18px;">
    <div class="card" style="grid-column: span 2;">
        <div class="toolbar">
            <div>
                <h2>Recent Borrow Requests</h2>
                <p class="help">Main demo workflow: create request → approve → checkout → return / damage report.</p>
            </div>
            <a class="btn" href="<?= h(baseUrl('modules/index.php?table=borrow_requests')) ?>">Open Module</a>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                <tr><th>ID</th><th>Requester</th><th>Equipment</th><th>Time</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php foreach ($recentRequests as $r): ?>
                    <tr>
                        <td>#<?= h($r['id']) ?></td>
                        <td><?= h($r['requester']) ?></td>
                        <td><?= h($r['asset_code'] . ' - ' . $r['equipment_name']) ?></td>
                        <td><?= h($r['start_time']) ?><br><span class="help">to <?= h($r['expected_return_time']) ?></span></td>
                        <td><?= statusBadge($r['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card">
        <h2>Equipment Status</h2>
        <p class="help">Overview of current equipment availability.</p>
        <?php foreach ($equipmentStatus as $row): ?>
            <p><?= statusBadge($row['status']) ?> <strong><?= h($row['total']) ?></strong></p>
        <?php endforeach; ?>
        <hr>
        <a class="btn secondary" href="<?= h(baseUrl('install.php')) ?>">Reset Demo Database</a>
    </div>
</div>

<div class="card">
    <h2>Demo Guide</h2>
    <ol class="help">
        <li>Open <strong>Equipment</strong> to view assets and their current status.</li>
        <li>Open <strong>Borrow Requests</strong>, create a request for an available device.</li>
        <li>Click <strong>Approve</strong>, then <strong>Check-out</strong> to create a borrow record.</li>
        <li>Open <strong>Borrow Records</strong> and return the device as normal or damaged.</li>
        <li>Open <strong>Damage Reports</strong> and <strong>Penalty Payments</strong> to view penalty workflow.</li>
    </ol>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
