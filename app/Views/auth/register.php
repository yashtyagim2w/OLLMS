<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="bi bi-car-front-fill"></i>
        </div>
        <h1 class="auth-title">Create Account</h1>
        <p class="auth-subtitle">Online Learner's License Portal</p>
    </div>

    <div class="auth-body">
        <!-- Hidden elements for SWAL to pick up -->
        <?php if (session('error')): ?>
            <div id="session-error" style="display:none;"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <?php if (session('errors')): ?>
            <div id="session-error" style="display:none;"><?= esc(implode("\n", session('errors'))) ?></div>
        <?php endif; ?>

        <form action="<?= url_to('register') ?>" method="post" id="registerForm">
            <?= csrf_field() ?>
            <?php helper('validation'); ?>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" id="first_name" class="form-control"
                        placeholder="Enter first name" value="<?= old('first_name') ?>"
                        minlength="<?= NAME_MIN_LENGTH ?>" maxlength="<?= NAME_MAX_LENGTH ?>"
                        pattern="<?= get_name_pattern_html() ?>"
                        title="<?= get_validation_message('name') ?>"
                        oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')"
                        required>
                    <div id="firstNameError" class="invalid-feedback"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" id="last_name" class="form-control"
                        placeholder="Enter last name" value="<?= old('last_name') ?>"
                        minlength="<?= NAME_MIN_LENGTH ?>" maxlength="<?= NAME_MAX_LENGTH ?>"
                        pattern="<?= get_name_pattern_html() ?>"
                        title="<?= get_validation_message('name') ?>"
                        oninput="this.value = this.value.replace(/[^A-Za-z\s\-']/g, '')"
                        required>
                    <div id="lastNameError" class="invalid-feedback"></div>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control"
                    placeholder="Enter email address" value="<?= old('email') ?>"
                    maxlength="<?= EMAIL_MAX_LENGTH ?>"
                    pattern="<?= get_email_pattern_html() ?>"
                    title="<?= get_validation_message('email') ?>"
                    required>
                <div id="emailError" class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" name="dob" id="dob" class="form-control"
                        placeholder="DD/MM/YYYY" value="<?= old('dob') ?>"
                        data-min-date="<?= get_min_dob() ?>"
                        data-max-date="<?= get_max_dob() ?>"
                        readonly>
                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                </div>
                <div id="dobError" class="invalid-feedback"></div>
                <div class="form-text"><?= get_validation_message('dob') ?></div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="Create password (min <?= PASSWORD_MIN_LENGTH ?> characters)"
                        minlength="<?= PASSWORD_MIN_LENGTH ?>" maxlength="<?= PASSWORD_MAX_LENGTH ?>"
                        title="<?= get_validation_message('password') ?>"
                        required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div id="passwordError" class="invalid-feedback"></div>
            </div>

            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password_confirm" id="password_confirm" class="form-control"
                        placeholder="Confirm password"
                        minlength="<?= PASSWORD_MIN_LENGTH ?>" maxlength="<?= PASSWORD_MAX_LENGTH ?>"
                        required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirm')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div id="confirmPasswordError" class="invalid-feedback"></div>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="terms" required>
                <label class="form-check-label" for="terms">
                    I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                </label>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-person-plus me-2"></i>Create Account
                </button>
            </div>
        </form>

        <div class="text-center mt-4">
            Already have an account? <a href="<?= url_to('login') ?>">Sign In</a>
        </div>

        <div class="gov-notice">
            <i class="bi bi-shield-check"></i>
            <p>This is an official portal of the Ministry of Road Transport & Highways. Your data is protected under government security protocols.</p>
        </div>
    </div>
</div>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script type="module" src="/assets/js/auth/register.js"></script>
<?= $this->endSection() ?>
