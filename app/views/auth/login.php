<?php
$errors = $errors ?? [];
$old = $old ?? ['email' => ''];
$firstError = !empty($errors) ? array_values($errors)[0] : null;
?>
<div class="auth-page">

    <!-- ===== AUTH FORM CARD ===== -->
    <section class="auth-section">
        <div class="auth-card">

            <!-- Card Header -->
            <div class="auth-card__head">
                <h1 class="auth-card__title">Đăng nhập</h1>
                <p class="auth-card__subtitle">Chào mừng bạn quay lại TechPilot</p>
            </div>

            <!-- Server-side error alert -->
            <?php if ($firstError): ?>
            <div class="auth-alert auth-alert--error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= e($firstError) ?></span>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="post" action="<?= url('auth/login') ?>" class="auth-form" id="loginForm" novalidate>
                <?= csrf_field() ?>

                <!-- Email / Phone -->
                <div class="auth-field" id="field-email">
                    <label class="auth-label" for="login_email">Email hoặc số điện thoại</label>
                    <div class="auth-input-wrap">
                        <i class="fa-regular fa-envelope auth-input-icon"></i>
                        <input
                            type="text"
                            name="email"
                            id="login_email"
                            class="auth-input"
                            placeholder="nguyenvana@gmail.com"
                            value="<?= e($old['email']) ?>"
                            autocomplete="username"
                            required
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="auth-field" id="field-password">
                    <label class="auth-label" for="login_password">Mật khẩu</label>
                    <div class="auth-input-wrap">
                        <i class="fa-solid fa-lock auth-input-icon"></i>
                        <input
                            type="password"
                            name="password"
                            id="login_password"
                            class="auth-input auth-input--has-action"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                            minlength="6"
                        >
                        <button type="button" class="auth-eye-btn" data-target="login_password" aria-label="Hiện/ẩn mật khẩu">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <p class="auth-field-error" id="password-hint" style="display:none;">
                        <i class="fa-solid fa-circle-exclamation"></i> Mật khẩu phải có ít nhất 6 ký tự
                    </p>
                </div>

                <!-- Remember + Forgot -->
                <div class="auth-form-row">
                    <label class="auth-checkbox-label">
                        <input type="checkbox" name="remember" class="auth-checkbox"> Ghi nhớ đăng nhập
                    </label>
                    <a href="<?= url('auth/forgot') ?>" class="auth-link-accent">Quên mật khẩu?</a>
                </div>

                <!-- Submit -->
                <button type="submit" class="auth-btn-primary" id="loginSubmitBtn">
                    Đăng nhập
                </button>
            </form>

            <!-- Divider -->
            <div class="auth-divider-line"><span>hoặc</span></div>

            <!-- Google Login -->
            <button type="button" class="auth-btn-google">
                <svg width="20" height="20" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.35-8.16 2.35-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Tiếp tục với Google
            </button>

            <!-- Register link -->
            <p class="auth-footer-text">Chưa có tài khoản? <a href="<?= url('auth/register') ?>" class="auth-link-accent">Đăng ký ngay</a></p>
        </div>
    </section>

    <!-- Trust badges -->
    <div class="auth-trust-grid">
        <div class="auth-trust-item">
            <div class="auth-trust-icon auth-trust-icon--blue">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="auth-trust-body">
                <h4>Bảo mật thông tin</h4>
                <p>Cam kết bảo mật tuyệt đối thông tin của bạn</p>
            </div>
        </div>
        <div class="auth-trust-item">
            <div class="auth-trust-icon auth-trust-icon--cyan">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div class="auth-trust-body">
                <h4>Hỗ trợ 24/7</h4>
                <p>Đội ngũ kỹ thuật luôn sẵn sàng hỗ trợ bạn</p>
            </div>
        </div>
    </div>

</div>

<!-- Auth Page JS -->
<script>
(function() {
    // Toggle password visibility
    document.querySelectorAll('.auth-eye-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var targetId = this.dataset.target;
            var input = document.getElementById(targetId);
            var icon = this.querySelector('i');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Client-side validation
    var form = document.getElementById('loginForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            var pw = document.getElementById('login_password');
            var hint = document.getElementById('password-hint');
            var field = document.getElementById('field-password');
            if (pw && pw.value.length > 0 && pw.value.length < 6) {
                e.preventDefault();
                hint.style.display = 'flex';
                field.classList.add('auth-field--error');
                pw.classList.add('auth-input--error');
                pw.focus();
            } else {
                if (hint) hint.style.display = 'none';
                if (field) field.classList.remove('auth-field--error');
            }
        });
        var pwInput = document.getElementById('login_password');
        if (pwInput) {
            pwInput.addEventListener('input', function() {
                var hint = document.getElementById('password-hint');
                var field = document.getElementById('field-password');
                if (this.value.length >= 6) {
                    if (hint) hint.style.display = 'none';
                    this.classList.remove('auth-input--error');
                    if (field) field.classList.remove('auth-field--error');
                }
            });
        }
    }
})();
</script>