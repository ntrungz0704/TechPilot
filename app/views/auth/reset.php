<?php
$errors = $errors ?? [];
$token = $token ?? '';
$firstError = !empty($errors) ? array_values($errors)[0] : null;
?>
<div class="auth-page">
    <section class="auth-section">
        <div class="auth-card">
            <div class="auth-card__head">
                <h1 class="auth-card__title">Đặt lại mật khẩu</h1>
                <p class="auth-card__subtitle">Vui lòng nhập mật khẩu mới cho tài khoản của bạn</p>
            </div>

            <?php if ($firstError): ?>
            <div class="auth-alert auth-alert--error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= e($firstError) ?></span>
            </div>
            <?php endif; ?>

            <form method="post" action="<?= url('auth/reset?token=' . urlencode($token)) ?>" class="auth-form" novalidate>
                <?= csrf_field() ?>

                <div class="auth-field" id="field-password">
                    <label class="auth-label" for="password">Mật khẩu mới</label>
                    <div class="auth-input-wrap">
                        <i class="fa-solid fa-lock auth-input-icon"></i>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="auth-input auth-input--has-action"
                            placeholder="••••••••"
                            required
                            minlength="8"
                        >
                        <button type="button" class="auth-eye-btn" data-target="password" aria-label="Hiện/ẩn mật khẩu">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-field" id="field-confirm-password">
                    <label class="auth-label" for="confirm_password">Xác nhận mật khẩu mới</label>
                    <div class="auth-input-wrap">
                        <i class="fa-solid fa-lock auth-input-icon"></i>
                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            class="auth-input auth-input--has-action"
                            placeholder="••••••••"
                            required
                            minlength="8"
                        >
                        <button type="button" class="auth-eye-btn" data-target="confirm_password" aria-label="Hiện/ẩn mật khẩu">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="auth-btn-primary" style="margin-top: 10px;">
                    Lưu mật khẩu mới
                </button>
            </form>
        </div>
    </section>
</div>
