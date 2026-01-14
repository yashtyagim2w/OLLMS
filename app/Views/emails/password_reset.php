<?= $this->extend('emails/layout') ?>
<?php $this->setData(['title' => 'Password Reset - OLLMS']) ?>

<?= $this->section('content') ?>
<h2 style="color: #1a3a5c; margin-top: 0;">Password Reset Request</h2>

<p>You have requested to reset your password for the Online Learner's License Management System.</p>

<p>Click the button below to reset your password:</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= esc($resetLink) ?>" style="background-color: #1a3a5c; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: 600; display: inline-block;">Reset Password</a>
</div>

<p style="color: #666; font-size: 14px;">Or copy and paste this link into your browser:</p>
<p style="background: #f5f7fa; padding: 10px; border-radius: 4px; word-break: break-all; font-size: 13px;"><?= esc($resetLink) ?></p>

<p>This link will expire in <strong>1 hour</strong>.</p>

<p style="color: #666; font-size: 14px;">If you did not request this password reset, please ignore this email.</p>
<?= $this->endSection() ?>