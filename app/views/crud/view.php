<section class="panel narrow-panel">
    <div class="panel-header">
        <div><h2><?= e($schema['title']) ?> Detail</h2></div>
        <a class="btn" href="<?= url($moduleKey . '/index') ?>">Back</a>
    </div>
    <dl class="detail-list">
        <?php foreach ($row as $field => $value): ?>
            <dt><?= e(ucwords(str_replace('_',' ', $field))) ?></dt>
            <dd>
                <?php if (isset($options[$field]) && $value !== null): ?>
                    <?= e($options[$field][(string)$value] ?? ('#' . $value)) ?>
                <?php else: ?>
                    <?= e($value) ?>
                <?php endif; ?>
            </dd>
        <?php endforeach; ?>
    </dl>
</section>
