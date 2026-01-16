<?php $theme = include APPPATH . 'Views/emails/theme.php'; ?>
<?= $this->extend('emails/layout') ?>

<?= $this->section('content') ?>
<h2 style="color: <?= $theme['success'] ?>; margin: 0 0 16px 0;">Document Approved</h2>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Hello <strong><?= esc($name) ?></strong>,
</p>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Great news! Your identity document (Aadhaar) has been <strong>approved</strong> by our verification team.
</p>

<div style="background: <?= $theme['bg_success'] ?>; border-left: 4px solid <?= $theme['success'] ?>; padding: 16px; margin: 24px 0;">
    <p style="margin: 0; color: <?= $theme['text_success'] ?>;">
        <strong>What's next?</strong><br>
        You can now proceed to watch the training videos and take the learner's license test.
    </p>
</div>

<p style="color: <?= $theme['text_secondary'] ?>; font-size: 16px; line-height: 1.6;">
    Log in to your account to continue your learner's license journey.
</p>
<?= $this->endSection() ?>