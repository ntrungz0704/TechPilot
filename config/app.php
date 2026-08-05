<?php

/**
 * Cấu hình chung ứng dụng
 */

// Thiết lập Múi giờ mặc định Hà Nội / Việt Nam (UTC+7)
date_default_timezone_set('Asia/Ho_Chi_Minh');

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Tự động nạp Class từ app/core, app/models, app/services
spl_autoload_register(function ($class) {
    $dirs = [
        ROOT_PATH . '/app/core/',
        ROOT_PATH . '/app/models/',
        ROOT_PATH . '/app/services/'
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ── .env parser (robust: xử lý quote, dấu "=" trong value, inline comment) ──
$envPath = dirname(__DIR__) . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Bỏ qua dòng trống hoặc comment
        if ($line === '' || $line[0] === '#') continue;

        // Tìm vị trí dấu "=" đầu tiên — phần trước là key, phần sau là value
        $eqPos = strpos($line, '=');
        if ($eqPos === false) continue;

        $name  = trim(substr($line, 0, $eqPos));
        $value = substr($line, $eqPos + 1);

        // Bỏ giá trị null/empty
        if ($value === false) $value = '';
        $value = trim($value);

        // Xử lý quoted values: "value" hoặc 'value'
        $len = strlen($value);
        if ($len >= 2) {
            $first = $value[0];
            $last  = $value[$len - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            } else {
                // Xóa inline comment (chỉ khi value KHÔNG nằm trong quote)
                // VD: APP_URL=http://localhost # comment → lấy "http://localhost"
                $commentPos = strpos($value, ' #');
                if ($commentPos !== false) {
                    $value = rtrim(substr($value, 0, $commentPos));
                }
            }
        }

        if ($name !== '') {
            if ($name === 'APP_ENV' && (getenv('APP_ENV') === 'test' || ($_SERVER['APP_ENV'] ?? '') === 'test')) {
                // Preserve test environment set by test runners
                $_ENV['APP_ENV'] = 'test';
                $_SERVER['APP_ENV'] = 'test';
            } else {
                putenv("{$name}={$value}");
                $_ENV[$name]    = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// ── Session cookie security ─────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');

    // Cảnh báo nếu production chạy qua HTTP — không silent fail
    $envForSession = strtolower(trim((string)(getenv('APP_ENV') ?: 'production')));
    if ($envForSession === 'production' && !$isSecure) {
        error_log(
            '[TechPilot SECURITY WARNING] APP_ENV=production nhưng KHÔNG phát hiện HTTPS. '
            . 'Session cookie KHÔNG có secure flag. Nếu đây là server thật, '
            . 'cần cấu hình HTTPS hoặc reverse proxy truyền header X-Forwarded-Proto.'
        );
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isSecure,
        'httponly'  => true,
        'samesite' => 'Lax'
    ]);
    $savePath = @ini_get('session.save_path');
    if (empty($savePath) || !@is_dir($savePath) || !@is_writable($savePath)) {
        @ini_set('session.save_path', sys_get_temp_dir());
    }
    @session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Đường dẫn gốc của website, tự động nhận theo thư mục public đang chạy
// Ví dụ với XAMPP: /techpilot/public hoặc /public
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = str_replace('\\', '/', $scriptDir);
$scriptDir = rtrim($scriptDir, '/');

if ($scriptDir === '/' || $scriptDir === '\\') {
    $scriptDir = '';
}

// Nếu truy cập từ root (không qua thư mục public) trong môi trường Apache/XAMPP/Laragon,
// ta cần bổ sung /public vào BASE_URL để các assets và link chạy đúng.
if ($scriptDir !== '' && substr($scriptDir, -7) !== '/public' && $scriptDir !== '/public') {
    if (is_dir(dirname(__DIR__) . '/public')) {
        $scriptDir .= '/public';
    }
}

define('BASE_URL', $scriptDir);

define('APP_NAME', 'TechPilot');

if (!defined('APP_URL')) {
    define('APP_URL', rtrim((string)(getenv('APP_URL') ?: ''), '/'));
}

// Đường dẫn tuyệt đối tới thư mục gốc dự án
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Chuẩn hóa môi trường APP_ENV (mặc định production nếu thiếu hoặc không hợp lệ)
$rawEnv = strtolower(trim((string)(getenv('APP_ENV') ?: 'production')));
$appEnv = in_array($rawEnv, ['development', 'testing', 'test', 'production'], true) ? $rawEnv : 'production';
if (!defined('APP_ENV')) {
    define('APP_ENV', $appEnv);
}

// Bật/tắt display_errors dựa theo môi trường
error_reporting(E_ALL);
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
