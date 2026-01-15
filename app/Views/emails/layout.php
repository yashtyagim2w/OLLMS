<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'OLLMS' ?></title>
</head>

<?php
// Load shared theme configuration
$theme = include APPPATH . 'Views/emails/theme.php';
?>

<body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: <?= $theme['text_primary'] ?>; max-width: 600px; margin: 0 auto; padding: 20px; background-color: <?= $theme['bg_light'] ?>;">
    <!-- Header -->
    <div style="background: <?= $theme['primary'] ?>; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">OLLMS</h1>
        <p style="color: rgba(255,255,255,0.8); margin: 5px 0 0;">Online Learner's License Management System</p>
    </div>

    <!-- Content -->
    <div style="background: <?= $theme['bg_white'] ?>; padding: 30px; border: 1px solid <?= $theme['border'] ?>; border-top: none;">
        <?= $this->renderSection('content') ?>
    </div>

    <!-- RTO Office Information -->
    <div style="background: <?= $theme['bg_light'] ?>; padding: 20px; border: 1px solid <?= $theme['border'] ?>; border-top: none;">
        <p style="margin: 0 0 10px 0; color: <?= $theme['text_primary'] ?>; font-weight: 600; font-size: 14px;">
            Need Assistance?
        </p>
        <p style="margin: 0; color: <?= $theme['text_secondary'] ?>; font-size: 13px; line-height: 1.6;">
            For any queries or assistance, please visit your nearest <strong>Regional Transport Office (RTO)</strong>.<br>
            Carry a valid government-issued ID and your registration details when visiting.
        </p>
    </div>

    <!-- Footer -->
    <div style="background: <?= $theme['primary'] ?>; padding: 20px; text-align: center; border-radius: 0 0 8px 8px;">
        <p style="margin: 0; color: rgba(255,255,255,0.9); font-size: 13px;">
            Best regards,<br>
            <strong>OLLMS Team</strong><br>
            <span style="color: rgba(255,255,255,0.7);">Ministry of Road Transport & Highways</span>
        </p>
        <p style="margin: 15px 0 0; color: rgba(255,255,255,0.6); font-size: 11px;">
            This is an automated email. Please do not reply directly to this message.
        </p>
    </div>
</body>

</html>