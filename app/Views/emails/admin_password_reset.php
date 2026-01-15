<?php $theme = include APPPATH . 'Views/emails/theme.php'; ?>
<?= $this->extend('emails/layout') ?>

<?= $this->section('content') ?>
<h2 style="color: <?= $theme['primary'] ?>; margin: 0 0 16px 0;">Password Reset</h2>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Hello <strong><?= esc($name) ?></strong>,
</p>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Your password has been reset by an administrator. Please use the following temporary password to log in:
</p>

<div style="background: <?= $theme['bg_light'] ?>; border: 2px dashed <?= $theme['primary'] ?>; padding: 24px; margin: 24px 0; text-align: center;">
    <p style="margin: 0 0 8px 0; color: <?= $theme['text_muted'] ?>; font-size: 14px;">Your New Password</p>
    <p style="margin: 0; font-size: 24px; font-weight: bold; color: <?= $theme['primary'] ?>; letter-spacing: 2px;">
        <?= esc($password) ?>
    </p>
</div>

<div style="background: <?= $theme['bg_warning'] ?>; border-left: 4px solid <?= $theme['warning'] ?>; padding: 16px; margin: 24px 0;">
    <p style="margin: 0; color: <?= $theme['text_warning'] ?>;">
        <strong>⚠️ Important:</strong> For your security, please change this password immediately after logging in.
    </p>
</div>

<div style="text-align: center; margin: 32px 0;">
    <a href="<?= base_url('login') ?>"
        style="display: inline-block; padding: 14px 32px; background: <?= $theme['primary'] ?>; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;">
        Login Now
    </a>
</div>

<p style="color: <?= $theme['text_muted'] ?>; font-size: 14px; line-height: 1.6;">
    If you did not request this password reset, please visit your nearest <strong>Regional Transport Office (RTO)</strong> with your valid ID immediately.
</p>
<?= $this->endSection() ?>