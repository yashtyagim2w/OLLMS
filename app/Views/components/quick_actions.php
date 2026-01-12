<?php

/**
 * Quick Actions Component
 * Reusable action buttons grid
 * 
 * Usage: <?= view('components/quick_actions', ['actions' => $actionsArray]) ?>
 * 
 * $actions format:
 * [
 *     ['url' => '/videos', 'icon' => 'bi-play-circle', 'label' => 'Continue Learning'],
 *     ...
 * ]
 */
$actions = $actions ?? [];
$columns = $columns ?? 3; // Default 3 columns
?>
<?php if (!empty($actions)): ?>
    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-lightning me-2"></i>Quick Actions</h3>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($actions as $action): ?>
                    <div class="col-md-<?= 12 / $columns ?>">
                        <a href="<?= $action['url'] ?>" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi <?= $action['icon'] ?> d-block mb-2" style="font-size: 24px;"></i>
                            <?= esc($action['label']) ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>