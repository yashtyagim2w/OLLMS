<?= $this->extend('emails/layout') ?>
<?php $this->setData(['title' => 'Welcome to OLLMS']) ?>

<?= $this->section('content') ?>
<h2 style="color: #1a3a5c; margin-top: 0;">Hello <?= esc($firstName) ?>!</h2>

<p>Thank you for registering with the <strong>Online Learner's License Management System</strong>.</p>

<p>Your account has been created successfully. Here's what you need to do next:</p>

<div style="background: #f5f7fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <ol style="margin: 0; padding-left: 20px;">
        <li style="margin-bottom: 10px;"><strong>Verify your email</strong> - Use the OTP sent to your email</li>
        <li style="margin-bottom: 10px;"><strong>Upload identity document</strong> - Aadhaar card required</li>
        <li style="margin-bottom: 10px;"><strong>Wait for approval</strong> - Our team will verify your documents</li>
        <li><strong>Start learning!</strong> - Access training videos and take the test</li>
    </ol>
</div>

<p><strong>Account Details:</strong></p>
<ul style="list-style: none; padding: 0; background: #f5f7fa; padding: 15px 20px; border-radius: 8px;">
    <li><strong>Email:</strong> <?= esc($email) ?></li>
    <li><strong>Registered:</strong> <?= date('d M Y, h:i A') ?></li>
</ul>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= site_url('verify-otp') ?>" style="background-color: #1a3a5c; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: 600; display: inline-block;">Verify Email Now</a>
</div>
<?= $this->endSection() ?>