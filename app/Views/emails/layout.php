<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'OLLMS' ?></title>
</head>

<body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f5f5f5;">
    <!-- Header -->
    <div style="background: #1a3a5c; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">OLLMS</h1>
        <p style="color: rgba(255,255,255,0.8); margin: 5px 0 0;">Online Learner's License Management System</p>
    </div>

    <!-- Content -->
    <div style="background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none;">
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Footer -->
    <div style="background: #f5f7fa; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; border: 1px solid #e0e0e0; border-top: none;">
        <p style="margin: 0; color: #666; font-size: 13px;">
            Best regards,<br>
            <strong>OLLMS Team</strong><br>
            Ministry of Road Transport & Highways
        </p>
        <p style="margin: 15px 0 0; color: #999; font-size: 11px;">
            This is an automated email. Please do not reply directly to this message.
        </p>
    </div>
</body>

</html>