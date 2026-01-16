<?php $theme = include APPPATH . 'Views/emails/theme.php'; ?>
<?= $this->extend('emails/layout') ?>
<?php $this->setData(['title' => 'Email Verification - OLLMS']) ?>

<?= $this->section('content') ?>
<h2 style="color: <?= $theme['primary'] ?>; margin-top: 0;">Email Verification</h2>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Your verification code for the Online Learner's License Management System is:
</p>

<div style="text-align: center; margin: 30px 0;">
    <span style="font-size: 36px; letter-spacing: 10px; color: <?= $theme['primary'] ?>; font-weight: bold; padding: 20px 30px; background: <?= $theme['bg_light'] ?>; border-radius: 8px; display: inline-block; border: 2px dashed <?= $theme['secondary'] ?>;"><?= esc($otp) ?></span>
</div>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    This code will expire in <strong>10 minutes</strong>.
</p>

<p style="color: <?= $theme['text_muted'] ?>; font-size: 14px;">
    If you did not request this code, please ignore this email.
</p>
<?= $this->endSection() ?>