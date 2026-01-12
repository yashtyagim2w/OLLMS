<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="OLLMS Admin Panel - RTO Management">
    <title><?= esc($pageTitle ?? 'Admin') ?> | OLLMS Admin</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom CSS -->
    <link href="/assets/css/main.css" rel="stylesheet">
    <link href="/assets/css/admin.css" rel="stylesheet">
</head>

<body>
    <div class="main-wrapper">
        <?= view('components/sidebar') ?>

        <div class="content-wrapper">
            <?= view('components/header', ['pageTitle' => $pageTitle ?? 'Admin Panel']) ?>

            <main class="main-content">
                <?= $this->renderSection('content') ?>
            </main>

            <?= view('components/footer') ?>
        </div>
    </div>

    <!-- Custom JS -->
    <script src="/assets/js/swal-helpers.js"></script>
    <script src="/assets/js/main.js"></script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>