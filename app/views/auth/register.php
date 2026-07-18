<?php
$errors = $errors ?? [];
$old = $old ?? ['full_name' => '', 'email' => '', 'phone' => ''];
$firstError = !empty($errors) ? array_values($errors)[0] : null;
?>
<div class="auth-page">

    <!-- ===== AUTH FORM CARD ===== -->
    <section class="auth-section">
        <div class="auth-card">

            <!-- Card Header -->
            <div class="auth-card__head auth-card__head--register">
                <div class="auth-card__head-icon">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div>
                    <h1 class="auth-card__title">Đăng ký tài khoản</h1>
                    <p class="auth-card__subtitle">
                        <i class="fa-solid fa-circle-check" style="color:#22C55E;"></i>
                        Tạo tài khoản trong <strong style="color: var(--primary);">1 phút</strong>
                    </p>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="auth-progress-bar">
                <div class="auth-progress-fill" id="registerProgress" style="width: 0%;"></div>
            </div>

            <!-- Server-side error alert -->
            <?php if ($firstError): ?>
            <div class="auth-alert auth-alert--error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= e($firstError) ?></span>
            </div>
            <?php endif; ?>

            <!-- Register Form -->
            <form method="post" action="<?= url('auth/register') ?>" class="auth-form" id="registerForm" novalidate>
                <?= csrf_field() ?>

                <!-- Full name -->
                <div class="auth-field" id="field-fullname">
                    <label class="auth-label" for="reg_full_name">Họ và tên</label>
                    <div class="auth-input-wrap">
                        <i class="fa-regular fa-user auth-input-icon"></i>
                        <input
                            type="text"
                            name="full_name"
                            id="reg_full_name"
                            class="auth-input"
                            placeholder="Nguyễn Văn An"
                            value="<?= e($old['full_name']) ?>"
                            autocomplete="name"
                            required
                        >
                        <span class="auth-valid-check" id="name-check" style="display:none;">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                    </div>
                </div>

                <!-- Email -->
                <div class="auth-field" id="field-regemail">
                    <label class="auth-label" for="reg_email">Email</label>
                    <div class="auth-input-wrap">
                        <i class="fa-regular fa-envelope auth-input-icon"></i>
                        <input
                            type="email"
                            name="email"
                            id="reg_email"
                            class="auth-input"
                            placeholder="vd: ban@example.com"
                            value="<?= e($old['email']) ?>"
                            autocomplete="email"
                            required
                        >
                    </div>
                    <p class="auth-field-hint">Chúng tôi sẽ gửi xác nhận đến email của bạn.</p>
                </div>

                <!-- Phone -->
                <div class="auth-field" id="field-phone">
                    <label class="auth-label" for="reg_phone">Số điện thoại</label>
                    <div class="auth-input-wrap">
                        <i class="fa-solid fa-phone auth-input-icon"></i>
                        <input
                            type="tel"
                            name="phone"
                            id="reg_phone"
                            class="auth-input"
                            placeholder="vd: 0901234567"
                            value="<?= e($old['phone']) ?>"
                            autocomplete="tel"
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="auth-field" id="field-regpassword">
                    <label class="auth-label" for="reg_password">Mật khẩu</label>
                    <div class="auth-input-wrap">
                        <i class="fa-solid fa-lock auth-input-icon"></i>
                        <input
                            type="password"
                            name="password"
                            id="reg_password"
                            class="auth-input auth-input--has-action"
                            placeholder="Nhập mật khẩu"
                            autocomplete="new-password"
                            required
                            minlength="8"
                        >
                        <button type="button" class="auth-eye-btn" data-target="reg_password" aria-label="Hiện/ẩn mật khẩu">
                            <i class="fa-regular fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="auth-field" id="field-confirm">
                    <label class="auth-label" for="reg_confirm">Xác nhận mật khẩu</label>
                    <div class="auth-input-wrap">
                        <i class="fa-solid fa-lock auth-input-icon"></i>
                        <input
                            type="password"
                            name="confirm_password"
                            id="reg_confirm"
                            class="auth-input auth-input--has-action"
                            placeholder="Nhập lại mật khẩu"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="auth-eye-btn" data-target="reg_confirm" aria-label="Hiện/ẩn mật khẩu">
                            <i class="fa-regular fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Password Strength -->
                <div class="auth-pw-strength" id="pwStrengthBox">
                    <div class="auth-pw-strength__label">
                        Độ mạnh mật khẩu: <span id="pwStrengthText">—</span>
                    </div>
                    <div class="auth-pw-strength__bar">
                        <div class="auth-pw-strength__fill" id="pwStrengthFill"></div>
                    </div>
                    <ul class="auth-pw-rules" id="pwRules">
                        <li class="auth-pw-rule" id="rule-len">
                            <i class="fa-solid fa-circle"></i> Ít nhất 8 ký tự
                        </li>
                        <li class="auth-pw-rule" id="rule-case">
                            <i class="fa-solid fa-circle"></i> Bao gồm chữ hoa và chữ thường
                        </li>
                        <li class="auth-pw-rule" id="rule-num">
                            <i class="fa-solid fa-circle"></i> Bao gồm số hoặc ký tự đặc biệt
                        </li>
                    </ul>
                </div>

                <!-- Terms checkbox -->
                <label class="auth-checkbox-label auth-checkbox-label--terms">
                    <input type="checkbox" name="agree_terms" id="agreeTerms" class="auth-checkbox" required>
                    <span>Tôi đồng ý với <a href="#" class="auth-link-accent">Điều khoản sử dụng</a> và <a href="#" class="auth-link-accent">Chính sách bảo mật</a></span>
                </label>

                <!-- Submit -->
                <button type="submit" class="auth-btn-primary" id="registerSubmitBtn">
                    Tạo tài khoản
                </button>
            </form>

            <!-- Login link -->
            <p class="auth-footer-text">Đã có tài khoản? <a href="<?= url('auth/login') ?>" class="auth-link-accent">Đăng nhập</a></p>
        </div>
    </section>

    <!-- Trust badges -->
    <div class="auth-trust-grid auth-trust-grid--3">
        <div class="auth-trust-item">
            <div class="auth-trust-icon auth-trust-icon--blue">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="auth-trust-body">
                <h4>Bảo mật</h4>
                <p>Thông tin của bạn luôn được bảo vệ</p>
            </div>
        </div>
        <div class="auth-trust-item">
            <div class="auth-trust-icon auth-trust-icon--green">
                <i class="fa-solid fa-user-lock"></i>
            </div>
            <div class="auth-trust-body">
                <h4>Không chia sẻ thông tin</h4>
                <p>Chúng tôi cam kết không chia sẻ với bên thứ ba</p>
            </div>
        </div>
        <div class="auth-trust-item">
            <div class="auth-trust-icon auth-trust-icon--cyan">
                <i class="fa-solid fa-headset"></i>
            </div>
            <div class="auth-trust-body">
                <h4>Hỗ trợ 24/7</h4>
                <p>Đội ngũ TechPilot luôn sẵn sàng hỗ trợ bạn</p>
            </div>
        </div>
    </div>

</div>

<!-- Register Page JS -->
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
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });

    // Full name validation green check
    var nameInput = document.getElementById('reg_full_name');
    var nameCheck = document.getElementById('name-check');
    if (nameInput && nameCheck) {
        nameInput.addEventListener('input', function() {
            var valid = this.value.trim().length >= 2;
            nameCheck.style.display = valid ? 'flex' : 'none';
            this.classList.toggle('auth-input--valid', valid);
        });
    }

    // Password strength
    var pwInput = document.getElementById('reg_password');
    var strText = document.getElementById('pwStrengthText');
    var strFill = document.getElementById('pwStrengthFill');
    var ruleLen = document.getElementById('rule-len');
    var ruleCase = document.getElementById('rule-case');
    var ruleNum = document.getElementById('rule-num');

    function setRule(el, pass) {
        if (!el) return;
        var icon = el.querySelector('i');
        if (pass) {
            icon.className = 'fa-solid fa-circle-check';
            el.classList.add('auth-pw-rule--pass');
        } else {
            icon.className = 'fa-solid fa-circle';
            el.classList.remove('auth-pw-rule--pass');
        }
    }

    function checkStrength(pw) {
        var score = 0;
        var hasLen = pw.length >= 8;
        var hasCase = /[A-Z]/.test(pw) && /[a-z]/.test(pw);
        var hasNum = /[0-9!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(pw);
        if (hasLen) score++;
        if (hasCase) score++;
        if (hasNum) score++;
        setRule(ruleLen, hasLen);
        setRule(ruleCase, hasCase);
        setRule(ruleNum, hasNum);

        if (!strText || !strFill) return;
        if (pw.length === 0) {
            strText.textContent = '—';
            strText.style.color = '#94A3B8';
            strFill.style.width = '0%';
            strFill.style.backgroundColor = '#E2E8F0';
        } else if (score === 1) {
            strText.textContent = 'Yếu';
            strText.style.color = '#EF4444';
            strFill.style.width = '33%';
            strFill.style.backgroundColor = '#EF4444';
        } else if (score === 2) {
            strText.textContent = 'Trung bình';
            strText.style.color = '#F59E0B';
            strFill.style.width = '66%';
            strFill.style.backgroundColor = '#F59E0B';
        } else {
            strText.textContent = 'Mạnh';
            strText.style.color = '#22C55E';
            strFill.style.width = '100%';
            strFill.style.backgroundColor = '#22C55E';
        }
    }

    if (pwInput) {
        pwInput.addEventListener('input', function() {
            checkStrength(this.value);
            updateProgress();
        });
    }

    // Progress bar based on filled fields
    function updateProgress() {
        var fields = ['reg_full_name', 'reg_email', 'reg_phone', 'reg_password', 'reg_confirm'];
        var filled = fields.filter(function(id) {
            var el = document.getElementById(id);
            return el && el.value.trim().length > 0;
        }).length;
        var terms = document.getElementById('agreeTerms');
        if (terms && terms.checked) filled++;
        var total = fields.length + 1;
        var pct = Math.round((filled / total) * 100);
        var bar = document.getElementById('registerProgress');
        if (bar) bar.style.width = pct + '%';
    }

    ['reg_full_name','reg_email','reg_phone','reg_password','reg_confirm','agreeTerms'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener(el.type === 'checkbox' ? 'change' : 'input', updateProgress);
    });

    // Client-side validation on submit
    var form = document.getElementById('registerForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            var pw = document.getElementById('reg_password');
            var conf = document.getElementById('reg_confirm');
            var terms = document.getElementById('agreeTerms');
            var ok = true;

            if (pw && pw.value.length < 8) {
                pw.classList.add('auth-input--error');
                ok = false;
            }
            if (pw && conf && pw.value !== conf.value) {
                conf.classList.add('auth-input--error');
                ok = false;
            }
            if (terms && !terms.checked) {
                terms.closest('.auth-checkbox-label').classList.add('auth-checkbox-label--error');
                ok = false;
            }
            if (!ok) e.preventDefault();
        });
    }
})();
</script>