<?= $this->extend('emails/layout') ?>
<?php $this->setData(['title' => 'Email Verification - OLLMS']) ?>

<?= $this->section('content') ?>
<h2 style="color: #1a3a5c; margin-top: 0;">Email Verification</h2>

<p>Your verification code for the Online Learner's License Management System is:</p>

<div style="text-align: center; margin: 30px 0;">
    <span style="font-size: 36px; letter-spacing: 10px; color: #1a3a5c; font-weight: bold; padding: 20px 30px; background: #f5f7fa; border-radius: 8px; display: inline-block; border: 2px dashed #d4a84b;"><?= esc($otp) ?></span>
</div>

<p>This code will expire in <strong>10 minutes</strong>.</p>

<p style="color: #666; font-size: 14px;">If you did not request this code, please ignore this email.</p>
<?= $this->endSection() ?>