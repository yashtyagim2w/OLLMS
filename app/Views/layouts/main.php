<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Online Learner's License Management System - Ministry of Road Transport">
    <title><?= esc($pageTitle ?? 'OLLMS') ?> | Learner's License Portal</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="/assets/css/main.css" rel="stylesheet">
    <?php if (isset($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link href="<?= $css ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <div class="main-wrapper">
        <?php if (isset($showSidebar) && $showSidebar): ?>
            <?= view('components/sidebar', ['verificationStatus' => $verificationStatus ?? 'PENDING']) ?>
            <div class="sidebar-overlay"></div>
        <?php endif; ?>

        <div class="content-wrapper <?= !isset($showSidebar) || !$showSidebar ? 'full-width' : '' ?>">
            <?= view('components/header', ['pageTitle' => $pageTitle ?? 'OLLMS', 'showSidebar' => $showSidebar ?? false]) ?>

            <main class="main-content">
                <?= $this->renderSection('content') ?>
            </main>

            <?= view('components/footer') ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Axios for AJAX calls -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script src="/assets/js/swal-helpers.js"></script>
    <script src="/assets/js/main.js"></script>
    <script src="/assets/js/form-helpers.js"></script>

    <!-- Validation Patterns from PHP (Single Source of Truth) -->
    <?php helper('validation'); ?>
    <script>
        window.ValidationPatterns = {
            // Regex patterns
            email: /<?= get_email_pattern_html() ?>/,
            name: /<?= get_name_pattern_html() ?>/,
            aadhar: /<?= get_aadhaar_pattern_html() ?>/,
            password: /<?= get_password_pattern_js() ?>/,

            // Field lengths (from database schema)
            nameMinLength: <?= NAME_MIN_LENGTH ?>,
            nameMaxLength: <?= NAME_MAX_LENGTH ?>,
            emailMaxLength: <?= EMAIL_MAX_LENGTH ?>,
            aadharLength: <?= AADHAR_LENGTH ?>,
            passwordMinLength: <?= PASSWORD_MIN_LENGTH ?>,
            passwordMaxLength: <?= PASSWORD_MAX_LENGTH ?>,
            otpLength: <?= OTP_LENGTH ?>,
            minAge: <?= MIN_AGE ?>,

            // Error messages
            messages: {
                email: '<?= get_validation_message('email') ?>',
                name: '<?= get_validation_message('name') ?>',
                aadhar: '<?= get_validation_message('aadhaar') ?>',
                password: '<?= get_validation_message('password') ?>',
                dob: '<?= get_validation_message('dob') ?>'
            }
        };
    </script>

    <?php if (isset($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <?= $this->renderSection('scripts') ?>
</body>

</html>