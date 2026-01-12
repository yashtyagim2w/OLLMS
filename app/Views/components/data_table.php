<?php

/**
 * Data Table Component
 * Reusable table with headers and rows
 * 
 * Usage: <?= view('components/data_table', ['headers' => [...], 'rows' => [...], 'emptyMessage' => '...']) ?>
 * 
 * $headers format: ['User', 'Submitted', 'Status', 'Action']
 * $rows format: [
 *     ['John Doe', '2 hours ago', '<span class="badge badge-warning">Pending</span>', '<a href="#">Review</a>'],
 *     ...
 * ]
 */
$headers = $headers ?? [];
$rows = $rows ?? [];
$emptyMessage = $emptyMessage ?? 'No data available';
$tableClass = $tableClass ?? 'table';
?>
<div class="table-responsive">
    <table class="<?= $tableClass ?>">
        <?php if (!empty($headers)): ?>
            <thead>
                <tr>
                    <?php foreach ($headers as $header): ?>
                        <th><?= $header ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
        <?php endif; ?>
        <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="<?= count($headers) ?>" class="text-center text-muted py-4">
                        <?= $emptyMessage ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?= $cell ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>