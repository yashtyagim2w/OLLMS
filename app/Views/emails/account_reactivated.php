<?php $theme = include APPPATH . 'Views/emails/theme.php'; ?>
<?= $this->extend('emails/layout') ?>

<?= $this->section('content') ?>
<h2 style="color: <?= $theme['success'] ?>; margin: 0 0 16px 0;">Account Reactivated</h2>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Hello <strong><?= esc($name) ?></strong>,
</p>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Good news! Your OLLMS account has been reactivated. You can now log in and access all platform features.
</p>

<div style="background: <?= $theme['bg_success'] ?>; border-left: 4px solid <?= $theme['success'] ?>; padding: 16px; margin: 24px 0;">
    <p style="margin: 0; color: <?= $theme['text_success'] ?>;">
        <strong>✓ Your account is active</strong><br>
        You have full access to all features and services.
    </p>
</div>

<div style="text-align: center; margin: 32px 0;">
    <a href="<?= base_url('login') ?>"
        style="display: inline-block; padding: 14px 32px; background: <?= $theme['success'] ?>; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;">
        Login Now
    </a>
</div>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Thank you for your patience. We're glad to have you back!
</p>
<?= $this->endSection() ?>