<?php $theme = include APPPATH . 'Views/emails/theme.php'; ?>
<?= $this->extend('emails/layout') ?>

<?= $this->section('content') ?>
<h2 style="color: <?= $theme['danger'] ?>; margin: 0 0 16px 0;">Account Deactivated</h2>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Hello <strong><?= esc($name) ?></strong>,
</p>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Your OLLMS account has been deactivated by an administrator. You will no longer be able to access the platform until your account is reactivated.
</p>

<div style="background: <?= $theme['bg_danger'] ?>; border-left: 4px solid <?= $theme['danger'] ?>; padding: 16px; margin: 24px 0;">
    <p style="margin: 0; color: <?= $theme['text_danger'] ?>;">
        <strong>What does this mean?</strong><br>
        You cannot log in or access any features of the platform while your account is deactivated.
    </p>
</div>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    If you believe this was done in error, please visit your nearest <strong>Regional Transport Office (RTO)</strong> with your valid ID for assistance.
</p>
<?= $this->endSection() ?>