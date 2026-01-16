<?php $theme = include APPPATH . 'Views/emails/theme.php'; ?>
<?= $this->extend('emails/layout') ?>

<?= $this->section('content') ?>
<h2 style="color: <?= $theme['primary'] ?>; margin: 0 0 16px 0;">Document Status Update</h2>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Hello <strong><?= esc($name) ?></strong>,
</p>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Your identity document status has been updated to: <strong><?= esc($status) ?></strong>
</p>

<div style="background: <?= $theme['bg_info'] ?>; border-left: 4px solid <?= $theme['info'] ?>; padding: 16px; margin: 24px 0;">
    <p style="margin: 0; color: <?= $theme['text_info'] ?>;">
        <strong>Note:</strong><br>
        Log in to your account to view more details about your document status.
    </p>
</div>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    If you have any questions, please visit your nearest <strong>Regional Transport Office (RTO)</strong>.
</p>
<?= $this->endSection() ?>