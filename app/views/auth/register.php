<?php
$errors = $errors ?? [];
$old = $old ?? ['full_name' => '', 'email' => '', 'phone' => ''];
?>
<section class="auth-section container">
    <div class="auth-card">
        <div class="auth-card__brand">
            <img src="<?= url('assets/images/logo.png') ?>" alt="TechPilot">
        </div>
        <h1>Tạo tài khoản mới</h1>
        <p class="auth-card__sub">Đăng ký để nhận ưu đãi dành riêng cho thành viên</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert--error">
                <?php foreach ($errors as $err): ?>
                    <p><i class="fa-solid fa-circle-exclamation"></i> <?= e($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url('auth/register') ?>" class="auth-form" novalidate>
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="full_name">Họ và tên</label>
                <div class="input-icon">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="full_name" id="full_name" placeholder="Nguyễn Văn A" value="<?= e($old['full_name']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-icon">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" id="email" placeholder="ban@email.com" value="<?= e($old['email']) ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <div class="input-icon">
                    <i class="fa-solid fa-phone"></i>
                    <input type="tel" name="phone" id="phone" placeholder="09xxxxxxxx" value="<?= e($old['phone']) ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="input-icon">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Tối thiểu 6 ký tự" required>
                    <button type="button" class="toggle-password" data-target="password"><i class="fa-regular fa-eye"></i></button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Nhập lại mật khẩu</label>
                <div class="input-icon">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Nhập lại mật khẩu" required>
                    <button type="button" class="toggle-password" data-target="confirm_password"><i class="fa-regular fa-eye"></i></button>
                </div>
            </div>

            <label class="checkbox agree-terms" style="display: flex; align-items: flex-start; gap: 8px; font-size: 13.5px; line-height: 1.5; margin-bottom: 20px;">
                <input type="checkbox" required style="margin-top: 3px;">
                <span>Tôi đồng ý với<br><a href="#" style="color: var(--primary); font-weight: 600; text-decoration: none;">Điều khoản dịch vụ</a> và <a href="#" style="color: var(--primary); font-weight: 600; text-decoration: none;">Chính sách bảo mật</a></span>
            </label>

            <button type="submit" class="btn btn--block">Đăng ký</button>
        </form>

        <p class="auth-card__footer">Đã có tài khoản? <a href="<?= url('auth/login') ?>">Đăng nhập</a></p>
    </div>
</section>