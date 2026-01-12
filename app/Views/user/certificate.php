<?= $this->extend('layouts/main') ?>
<?php $this->setData(['showSidebar' => true, 'pageTitle' => 'My Certificate', 'verificationStatus' => 'APPROVED']) ?>

<?= $this->section('content') ?>
<div class="page-header">
    <nav class="breadcrumb">
        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Certificate</li>
    </nav>
    <h1 class="page-title">Learner's License Certificate</h1>
    <p class="page-subtitle">Download your official certificate</p>
</div>

<?php $hasCertificate = $hasCertificate ?? true; ?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <?php if ($hasCertificate): ?>
            <!-- Certificate Preview -->
            <div class="certificate-preview mb-4">
                <div class="certificate-logo">
                    <i class="bi bi-car-front-fill" style="font-size: 48px; color: var(--primary-color);"></i>
                </div>

                <div class="certificate-title">Government of India</div>
                <div class="certificate-subtitle">Ministry of Road Transport & Highways</div>

                <h2 style="color: var(--primary-color); margin: 30px 0;">LEARNER'S LICENSE</h2>
                <p style="font-size: 18px; color: var(--gray-600);">This is to certify that</p>

                <div class="certificate-name"><?= esc(($firstName ?? 'John') . ' ' . ($lastName ?? 'Doe')) ?></div>

                <p class="certificate-text">
                    has successfully completed the Learner's License Training Program and passed the Online Test
                    conducted by the Regional Transport Office. This certificate is valid for a period of 6 months
                    from the date of issue for the purpose of obtaining a Learner's License.
                </p>

                <div class="row mt-5">
                    <div class="col-6 text-start">
                        <p class="mb-1"><strong>Certificate No:</strong></p>
                        <p class="certificate-number"><?= esc($certificateNumber ?? 'LL-2026-0012345') ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <p class="mb-1"><strong>Issue Date:</strong></p>
                        <p><?= date('F d, Y', strtotime($issueDate ?? 'now')) ?></p>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <img src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='50'%3E%3Crect fill='%231a3a5c' width='150' height='50'/%3E%3Ctext fill='white' font-family='serif' font-size='14' x='50%25' y='50%25' dominant-baseline='middle' text-anchor='middle'%3EOfficial Seal%3C/text%3E%3C/svg%3E" alt="Official Seal" style="opacity: 0.5;">
                </div>
            </div>

            <!-- Download Actions -->
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-1">Download Certificate</h5>
                            <p class="text-muted mb-0">Get your official Learner's License certificate</p>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <a href="/certificate/download?format=pdf" class="btn btn-primary me-2">
                                <i class="bi bi-file-earmark-pdf"></i> Download PDF
                            </a>
                            <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                                <i class="bi bi-printer"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="alert alert-info mt-4">
                <i class="bi bi-info-circle"></i>
                <div>
                    <strong>Next Steps</strong>
                    <ul class="mb-0 mt-2">
                        <li>Print this certificate</li>
                        <li>Visit your nearest RTO within 6 months</li>
                        <li>Carry original documents (Aadhar, Age Proof, Address Proof)</li>
                        <li>Pay the applicable fees at RTO</li>
                        <li>Collect your Learner's License</li>
                    </ul>
                </div>
            </div>

        <?php else: ?>
            <!-- No Certificate -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-award" style="font-size: 80px; color: var(--gray-300);"></i>
                    <h3 class="mt-4">No Certificate Available</h3>
                    <p class="text-muted mb-4">You need to pass the test to receive your certificate.</p>
                    <a href="/test-instructions" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> Take Test
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>