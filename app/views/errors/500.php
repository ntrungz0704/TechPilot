<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Server Error - TechPilot</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; display: flex; min-height: 100vh; align-items: center; justify-content: center; padding: 20px; text-align: center; }
        .error-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 48px 32px; max-width: 560px; width: 100%; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05); }
        .error-code { font-size: 72px; font-weight: 800; color: #ef4444; line-height: 1; margin-bottom: 16px; }
        .error-title { font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 12px; }
        .error-desc { font-size: 15px; color: #64748b; line-height: 1.6; margin-bottom: 28px; }
        .debug-box { background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; text-align: left; font-family: monospace; font-size: 13px; color: #991b1b; overflow-x: auto; margin-bottom: 24px; word-break: break-all; }
        .btn-home { display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 28px; font-weight: 600; font-size: 15px; border-radius: 8px; transition: background 0.2s; }
        .btn-home:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code">500</div>
        <h1 class="error-title">Lỗi xử lý phía máy chủ</h1>
        <p class="error-desc">Hệ thống đang gặp sự cố gián đoạn tạm thời. Vui lòng thử lại sau hoặc quay về trang chủ.</p>

        <?php if (isset($isDev) && $isDev && !empty($errorMessage)): ?>
            <div class="debug-box">
                <strong>Dev Debug:</strong> <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
                <?php if (!empty($errorId)): ?>
                    <br><small style="color: #b91c1c;">Error ID: <?= htmlspecialchars($errorId, ENT_QUOTES, 'UTF-8') ?></small>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn-home">Quay về trang chủ</a>
    </div>
</body>
</html>
