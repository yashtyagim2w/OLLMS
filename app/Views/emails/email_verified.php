<?= $this->extend('emails/layout') ?>
<?php $this->setData(['title' => 'Email Verified - OLLMS']) ?>

<?= $this->section('content') ?>
<h2 style="color: #1a3a5c; margin-top: 0;">Congratulations, <?= esc($firstName) ?>!</h2>

<p>Your email has been <strong>successfully verified</strong>.</p>

<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin: 20px 0;">
    <p style="margin: 0; color: #155724;"><strong>✓ Step 1 Complete:</strong> Email verification successful</p>
</div>

<p><strong>Next Step:</strong> Upload your identity document (Aadhaar card) to proceed with your application.</p>

<div style="text-align: center; margin: 30px 0;">
    <a href="<?= site_url('identity-upload') ?>" style="background-color: #1a3a5c; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: 600; display: inline-block;">Upload Identity Document</a>
</div>

<p style="color: #666; font-size: 14px;">Once your document is approved, you'll be able to access the training videos and take the learner's license test.</p>
<?= $this->endSection() ?>