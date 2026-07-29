<?php
$pageTitle = '500 Server Error - Lỗi máy chủ';
if (file_exists(ROOT_PATH . '/app/views/layouts/header.php')) {
    require_once ROOT_PATH . '/app/views/layouts/header.php';
}
?>
<div class="container" style="padding: 80px 20px; text-align: center;">
    <h1 style="font-size: 72px; color: #dc2626; margin-bottom: 10px;">500</h1>
    <h2 style="font-size: 24px; color: #1f2937; margin-bottom: 15px;">Lỗi xử lý phía máy chủ</h2>
    <p style="color: #4b5563; max-width: 500px; margin: 0 auto 30px;">Hệ thống đang gặp gián đoạn tạm thời. Vui lòng quay lại sau ít phút.</p>
    <?php if (isset($isDev) && $isDev && isset($errorMessage)): ?>
        <div style="background: #fee2e2; border: 1px solid #fca5a5; padding: 15px; border-radius: 6px; text-align: left; max-width: 700px; margin: 0 auto 30px; font-family: monospace; font-size: 13px; color: #991b1b; overflow-x: auto;">
            <strong>Dev Debug:</strong> <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>" class="btn btn-primary" style="padding: 10px 24px; text-decoration: none; border-radius: 6px; background: #2563eb; color: #fff; display: inline-block;">Quay về trang chủ</a>
</div>
<?php
if (file_exists(ROOT_PATH . '/app/views/layouts/footer.php')) {
    require_once ROOT_PATH . '/app/views/layouts/footer.php';
}
?>
