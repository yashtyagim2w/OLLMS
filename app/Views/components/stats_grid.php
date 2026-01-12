<?php

/**
 * Stats Grid Component
 * Reusable stat cards for both User and Admin dashboards
 * 
 * Usage: <?= view('components/stats_grid', ['stats' => $statsArray]) ?>
 * 
 * $stats format:
 * [
 *     ['value' => '156', 'label' => 'Total Users', 'icon' => 'bi-people', 'color' => 'primary'],
 *     ...
 * ]
 */
$stats = $stats ?? [];
?>
<?php if (!empty($stats)): ?>
    <div class="stats-grid">
        <?php foreach ($stats as $stat): ?>
            <div class="stat-card">
                <div class="stat-icon <?= $stat['color'] ?? 'primary' ?>">
                    <i class="bi <?= $stat['icon'] ?? 'bi-info-circle' ?>"></i>
                </div>
                <div class="stat-content">
                    <h3><?= esc($stat['value']) ?></h3>
                    <p><?= esc($stat['label']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>