<?php $theme = include APPPATH . 'Views/emails/theme.php'; ?>
<?= $this->extend('emails/layout') ?>
<?php $this->setData(['title' => 'Password Reset - OLLMS']) ?>

<?= $this->section('content') ?>
<h2 style="color: <?= $theme['primary'] ?>; margin-top: 0;">Password Reset Request</h2>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    You have requested to reset your password for the Online Learner's License Management System.
</p>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Click the button below to reset your password:
</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= esc($resetLink) ?>" style="background-color: <?= $theme['primary'] ?>; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: 600; display: inline-block;">Reset Password</a>
</div>

<p style="color: <?= $theme['text_muted'] ?>; font-size: 14px;">Or copy and paste this link into your browser:</p>
<p style="background: <?= $theme['bg_light'] ?>; padding: 10px; border-radius: 4px; word-break: break-all; font-size: 13px; color: <?= $theme['text_secondary'] ?>;"><?= esc($resetLink) ?></p>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    This link will expire in <strong>1 hour</strong>.
</p>

<p style="color: <?= $theme['text_muted'] ?>; font-size: 14px;">
    If you did not request this password reset, please ignore this email.
</p>
<?= $this->endSection() ?>