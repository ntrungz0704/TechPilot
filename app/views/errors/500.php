<?php
$homeUrl = defined('BASE_URL') && BASE_URL !== '' ? BASE_URL : '/';
if (function_exists('url')) {
    $homeUrl = url('');
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Lỗi máy chủ - TechPilot</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; display: flex; min-height: 100vh; align-items: center; justify-content: center; padding: 20px; text-align: center; }
        .error-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 48px 32px; max-width: 560px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.05); }
        .error-code { font-size: 72px; font-weight: 800; color: #ef4444; line-height: 1; margin-bottom: 16px; }
        .error-title { font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
        .error-desc { font-size: 15px; color: #64748b; line-height: 1.6; margin-bottom: 28px; }
        .debug-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; text-align: left; font-family: monospace; font-size: 13px; color: #991b1b; overflow-x: auto; margin-bottom: 24px; word-break: break-all; }
        .btn-home { display: inline-block; background: #0b63e5; color: #ffffff; text-decoration: none; padding: 12px 28px; font-weight: 700; font-size: 15px; border-radius: 8px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(11,99,229,0.25); }
        .btn-home:hover { background: #024ebb; transform: translateY(-1px); }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">500</div>
        <h1 class="error-title">Lỗi xử lý phía máy chủ</h1>
        <p class="error-desc">Hệ thống đang gặp sự cố gián đoạn tạm thời. Vui lòng thử lại sau hoặc bấm nút bên dưới để quay về trang chủ.</p>

        <?php if (isset($isDev) && $isDev && !empty($errorMessage)): ?>
            <div class="debug-box">
                <strong>Dev Debug:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($errorId)): ?>
                    <br><small style="color: #b91c1c;">Error ID: <?= htmlspecialchars($errorId, ENT_QUOTES, 'UTF-8') ?></small>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-home">Quay về trang chủ</a>
    </div>
</body>
</html>
