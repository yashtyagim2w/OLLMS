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

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" name="first_name" id="first_name" class="form-control"
                        placeholder="Enter first name" value="<?= old('first_name') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" name="last_name" id="last_name" class="form-control"
                        placeholder="Enter last name" value="<?= old('last_name') ?>" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" class="form-control"
                    placeholder="Enter email address" value="<?= old('email') ?>" required>
            </div>

            <div class="mb-3">
                <label for="dob" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" name="dob" id="dob" class="form-control"
                        placeholder="DD/MM/YYYY" value="<?= old('dob') ?>" readonly required>
                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                </div>
                <div class="form-text">You must be at least 18 years old to apply</div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control"
                        placeholder="Create password (min 8 characters)" required minlength="8">
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-3">
                <label for="password_confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="password" name="password_confirm" id="password_confirm" class="form-control"
                        placeholder="Confirm password" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password_confirm')">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('registerForm');

        // Calculate max date (18 years ago from today)
        const today = new Date();
        const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());

        // Initialize Flatpickr date picker
        const dobPicker = flatpickr("#dob", {
            dateFormat: "d/m/Y",
            maxDate: maxDate,
            minDate: "01/01/1940",
            defaultDate: null,
            allowInput: false,
            disableMobile: true,
            monthSelectorType: "dropdown",
            yearSelectorType: "dropdown",
            onReady: function(selectedDates, dateStr, instance) {
                // Add clear button
                const clearBtn = document.createElement('button');
                clearBtn.type = 'button';
                clearBtn.className = 'flatpickr-clear';
                clearBtn.innerHTML = 'Clear';
                clearBtn.onclick = function() {
                    instance.clear();
                };
            }
        });

        // Validate age on form submit
        form.addEventListener('submit', function(e) {
            const dobValue = document.getElementById('dob').value;

            if (!dobValue) {
                e.preventDefault();
                SwalHelper.error('Date Required', 'Please select your date of birth');
                return false;
            }

            // Parse DD/MM/YYYY
            const parts = dobValue.split('/');
            const day = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10) - 1;
            const year = parseInt(parts[2], 10);
            const dob = new Date(year, month, day);

            // Calculate age
            let age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                age--;
            }

            if (age < 18) {
                e.preventDefault();
                SwalHelper.error('Age Requirement', 'You must be at least 18 years old to register');
                return false;
            }
        });
    });
</script>
<?= $this->endSection() ?>