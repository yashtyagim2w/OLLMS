<?php $theme = include APPPATH . 'Views/emails/theme.php'; ?>
<?= $this->extend('emails/layout') ?>

<?= $this->section('content') ?>
<h2 style="color: <?= $theme['primary'] ?>; margin: 0 0 16px 0;">Profile Updated</h2>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Hello <strong><?= esc($name) ?></strong>,
</p>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Your profile information has been updated by an administrator.
</p>

<div style="background: <?= $theme['bg_info'] ?>; border-left: 4px solid <?= $theme['primary'] ?>; padding: 16px; margin: 24px 0;">
    <p style="margin: 0; color: <?= $theme['text_secondary'] ?>;">
        <strong>What changed?</strong><br>
        Your personal information (name, date of birth, or other details) may have been updated.
    </p>
</div>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    You can view your current profile by logging into your account.
</p>

<div style="text-align: center; margin: 32px 0;">
    <a href="<?= base_url('profile') ?>"
        style="display: inline-block; padding: 14px 32px; background: <?= $theme['primary'] ?>; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600;">
        View My Profile
    </a>
</div>

<p style="color: <?= $theme['text_muted'] ?>; font-size: 14px; line-height: 1.6;">
    If you did not request this change, please visit your nearest <strong>Regional Transport Office (RTO)</strong> with your valid ID.
</p>
<?= $this->endSection() ?>