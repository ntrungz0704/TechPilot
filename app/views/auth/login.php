<?php
$errors = $errors ?? [];
$old = $old ?? ['email' => ''];
?>
<section class="auth-section container">
    <div class="auth-card">
        <div class="auth-card__brand">
            <img src="<?= url('assets/images/logo.png') ?>" alt="TechPilot">
        </div>
        <h1>Đăng nhập tài khoản</h1>
        <p class="auth-card__sub">Chào mừng bạn quay lại với TechPilot</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <?php foreach ($errors as $err): ?>
                    <p><i class="fa-solid fa-circle-exclamation"></i> <?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url('auth/login') ?>" class="auth-form" novalidate>
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-icon">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" id="email" placeholder="ban@email.com" value="<?= e($old['email']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="input-icon">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Nhập mật khẩu" required>
                    <button type="button" class="toggle-password" data-target="password"><i class="fa-regular fa-eye"></i></button>
                </div>
            </div>

            <div class="form-row">
                <label class="checkbox"><input type="checkbox" name="remember"> Ghi nhớ đăng nhập</label>
                <a href="#" class="forgot-link">Quên mật khẩu?</a>
            </div>

            <button type="submit" class="btn btn--block">Đăng nhập</button>
        </form>

        <div class="auth-divider"><span>hoặc</span></div>

        <div class="social-login">
            <button type="button" class="btn btn--outline btn--block"><i class="fa-brands fa-google"></i> Đăng nhập với Google</button>
            <button type="button" class="btn btn--outline btn--block"><i class="fa-brands fa-facebook-f"></i> Đăng nhập với Facebook</button>
        </div>

        <p class="auth-card__footer">Chưa có tài khoản? <a href="<?= url('auth/register') ?>">Đăng ký ngay</a></p>
    </div>
</section>