<?php
$errors = $errors ?? [];
$message = $message ?? '';
$firstError = !empty($errors) ? array_values($errors)[0] : null;
?>
<div class="auth-page">
    <section class="auth-section">
        <div class="auth-card">
            <div class="auth-card__head">
                <h1 class="auth-card__title">Quên mật khẩu</h1>
                <p class="auth-card__subtitle">Nhập email để nhận liên kết khôi phục</p>
            </div>

            <?php if ($message): ?>
            <div class="auth-alert" style="background-color: var(--success-light, #e8f5e9); color: var(--success, #2e7d32); border: 1px solid var(--success, #2e7d32); border-radius: 8px; padding: 12px; margin-bottom: 20px; font-size: 0.9rem;">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= $message ?></span>
            </div>
            <?php endif; ?>

            <?php if ($firstError): ?>
            <div class="auth-alert auth-alert--error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?= e($firstError) ?></span>
            </div>
            <?php endif; ?>

            <form method="post" action="<?= url('auth/forgot') ?>" class="auth-form" novalidate>
                <?= csrf_field() ?>

                <div class="auth-field">
                    <label class="auth-label" for="email">Email đã đăng ký</label>
                    <div class="auth-input-wrap">
                        <i class="fa-regular fa-envelope auth-input-icon"></i>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="auth-input"
                            placeholder="nguyenvana@gmail.com"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="auth-btn-primary" style="margin-top: 10px;">
                    Gửi yêu cầu
                </button>
            </form>

            <div class="auth-footer" style="margin-top: 24px; text-align: center; font-size: 0.9rem; color: var(--text-muted);">
                <a href="<?= url('auth/login') ?>" class="auth-link-accent">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại đăng nhập
                </a>
            </div>
        </div>
    </section>
</div>
