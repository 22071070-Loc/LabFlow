<section class="panel narrow-panel">
    <div class="panel-header">
        <div>
            <h2><?= $mode === 'create' ? 'Add New' : 'Edit' ?> <?= e($schema['title']) ?></h2>
        </div>
        <a class="btn" href="<?= url($moduleKey . '/index') ?>">Back</a>
    </div>

    <form method="post" action="<?= url($moduleKey . '/' . ($mode === 'create' ? 'store' : 'update')) ?>" class="form-grid">
        <?php if ($mode === 'edit'): ?>
            <input type="hidden" name="id" value="<?= e($row['id']) ?>">
        <?php endif; ?>

        <?php foreach ($schema['fields'] as $field => $meta): ?>
            <?php if (!empty($meta['admin_only']) && !Auth::isAdmin()) continue; ?>
            <?php if (!empty($meta['student_self']) && Auth::isStudent()) continue; ?>

            <?php
                $type = $meta['type'] ?? 'text';
                $value = $row[$field] ?? '';
                if ($type === 'datetime-local' && $value) {
                    $value = str_replace(' ', 'T', substr($value, 0, 16));
                }
                if (!empty($meta['default_current_user']) && $mode === 'create') {
                    $value = Auth::id();
                }
            ?>

            <label class="field <?= $type === 'textarea' ? 'field-full' : '' ?>">
                <span><?= e($meta['label'] ?? $field) ?><?= !empty($meta['required']) ? ' *' : '' ?></span>

                <?php if ($type === 'textarea'): ?>
                    <textarea name="<?= e($field) ?>" <?= !empty($meta['required']) ? 'required' : '' ?>><?= e($value) ?></textarea>
                <?php elseif ($type === 'select'): ?>
                    <select name="<?= e($field) ?>" <?= !empty($meta['required']) ? 'required' : '' ?>>
                        <option value="">-- Select --</option>
                        <?php foreach (($meta['options'] ?? []) as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= (string)$value === (string)$key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($type === 'relation'): ?>
                    <select name="<?= e($field) ?>" <?= !empty($meta['required']) ? 'required' : '' ?>>
                        <option value="">-- Select --</option>
                        <?php foreach (($options[$field] ?? []) as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= (string)$value === (string)$key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="<?= e($type) ?>" name="<?= e($field) ?>" value="<?= e($value) ?>" <?= !empty($meta['required']) ? 'required' : '' ?> step="<?= e($meta['step'] ?? '') ?>">
                <?php endif; ?>

                <?php if (!empty($meta['help'])): ?>
                    <small><?= e($meta['help']) ?></small>
                <?php endif; ?>
            </label>
        <?php endforeach; ?>

        <div class="form-actions field-full">
            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn" href="<?= url($moduleKey . '/index') ?>">Cancel</a>
        </div>
    </form>
</section>
