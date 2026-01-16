<?php $theme = include APPPATH . 'Views/emails/theme.php'; ?>
<?= $this->extend('emails/layout') ?>

<?= $this->section('content') ?>
<h2 style="color: <?= $theme['danger'] ?>; margin: 0 0 16px 0;">Document Rejected</h2>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Hello <strong><?= esc($name) ?></strong>,
</p>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Unfortunately, your identity document (Aadhaar) has been <strong>rejected</strong> by our verification team.
</p>

<?php if (!empty($remarks)): ?>
    <div style="background: <?= $theme['bg_danger'] ?>; border-left: 4px solid <?= $theme['danger'] ?>; padding: 16px; margin: 24px 0;">
        <p style="margin: 0; color: <?= $theme['text_danger'] ?>;">
            <strong>Reason for Rejection:</strong><br>
            <?= esc($remarks) ?>
        </p>
    </div>
<?php endif; ?>

<div style="background: <?= $theme['bg_warning'] ?? '#fff9e6' ?>; border-left: 4px solid <?= $theme['warning'] ?? '#f0ad4e' ?>; padding: 16px; margin: 24px 0;">
    <p style="margin: 0; color: <?= $theme['text_secondary'] ?>;">
        <strong>What should you do?</strong><br>
        Please log in to your account and upload a clear, valid Aadhaar document for verification.
    </p>
</div>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Make sure your document is:
</p>
<ul style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.8;">
    <li>Clear and readable</li>
    <li>Not expired</li>
    <li>Contains your correct details</li>
</ul>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    If you need assistance, please visit your nearest <strong>Regional Transport Office (RTO)</strong>.
</p>
<?= $this->endSection() ?>