<?php
$pageTitle = '404 Not Found - Không tìm thấy trang';
if (file_exists(ROOT_PATH . '/app/views/layouts/header.php')) {
    require_once ROOT_PATH . '/app/views/layouts/header.php';
}
?>
<div class="container" style="padding: 80px 20px; text-align: center;">
    <h1 style="font-size: 72px; color: #2563eb; margin-bottom: 10px;">404</h1>
    <h2 style="font-size: 24px; color: #1f2937; margin-bottom: 15px;">Không tìm thấy trang</h2>
    <p style="color: #4b5563; max-width: 500px; margin: 0 auto 30px;">Đường dẫn bạn yêu cầu không tồn tại hoặc đã được di chuyển sang địa chỉ mới.</p>
    <a href="<?= BASE_URL ?>" class="btn btn-primary" style="padding: 10px 24px; text-decoration: none; border-radius: 6px; background: #2563eb; color: #fff; display: inline-block;">Quay về trang chủ</a>
</div>
<?php
if (file_exists(ROOT_PATH . '/app/views/layouts/footer.php')) {
    require_once ROOT_PATH . '/app/views/layouts/footer.php';
}
?>
