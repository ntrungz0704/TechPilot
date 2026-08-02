<?php
declare(strict_types=1);

/**
 * Guest Checkout Regression Test — P0-1
 *
 * Xác minh rằng guest session có giỏ hàng hợp lệ có thể tải /checkout
 * mà không gặp lỗi, sau khi null guard được áp dụng vào CheckoutController.
 *
 * Yêu cầu:
 *   - PHP built-in server đang chạy tại BASE_URL (mặc định http://127.0.0.1:8000)
 *   - Database có ít nhất một sản phẩm status=active AND stock>0
 *   - session.save_handler = files, session.save_path là thư mục ghi được
 *
 * Chạy:
 *   php tests/GuestCheckoutRegressionTest.php
 *   php tests/GuestCheckoutRegressionTest.php http://127.0.0.1:8000
 */

date_default_timezone_set('Asia/Ho_Chi_Minh');
ob_start();

// ─── Config ──────────────────────────────────────────────────────────────────

$baseUrl = rtrim((string)($argv[1] ?? getenv('TECHPILOT_BASE_URL') ?: 'http://127.0.0.1:8000'), '/');

define('ROOT_PATH', dirname(__DIR__));
define('APP_ENV', 'testing');
define('APP_URL', $baseUrl);

require_once ROOT_PATH . '/config/database.php';

$results = ['passed' => 0, 'failed' => 0];

function pass(string $label): void
{
    global $results;
    $results['passed']++;
    echo "[PASS] $label\n";
}

function fail(string $label, string $reason): void
{
    global $results;
    $results['failed']++;
    echo "[FAIL] $label\n       $reason\n";
}

function blocked(string $label, string $reason): void
{
    global $results;
    $results['failed']++;
    echo "[BLOCKED] $label\n          $reason\n";
}

echo "============================================================\n";
echo "GUEST CHECKOUT REGRESSION TEST — P0-1\n";
echo "Base URL: $baseUrl\n";
echo "============================================================\n\n";

// ─── Precondition checks ─────────────────────────────────────────────────────

// 1. Server
$pingCh = curl_init($baseUrl . '/');
curl_setopt_array($pingCh, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5, CURLOPT_FOLLOWLOCATION => false]);
curl_exec($pingCh);
$pingCode = (int)curl_getinfo($pingCh, CURLINFO_HTTP_CODE);
curl_close($pingCh);

if ($pingCode === 0) {
    blocked('Server reachable', "Không kết nối được $baseUrl — khởi động server trước");
    goto summary;
}
pass('Server reachable');

// 2. DB connection
$db = Database::getConnection();
if ($db === null) {
    blocked('Database connection', 'Không thể kết nối database');
    goto summary;
}
pass('Database connection');

// 3. Valid product
$productId    = 0;
$noValidProduct = false;
$stmt = $db->query(
    "SELECT id FROM products WHERE status = 'active' AND stock > 0 ORDER BY id LIMIT 1"
);
$row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
if ($row !== false && !empty($row['id'])) {
    $productId = (int)$row['id'];
} else {
    $noValidProduct = true;
}

if ($noValidProduct) {
    blocked(
        'Valid product fixture',
        'BLOCKED — NO VALID PRODUCT FIXTURE: Không có sản phẩm nào status=active AND stock>0'
    );
    goto summary;
}
pass("Valid product found (id={$productId})");

// 4. Session handler
$sessionName      = (string)(ini_get('session.name') ?: 'PHPSESSID');
$sessionSavePath  = rtrim((string)(ini_get('session.save_path') ?: sys_get_temp_dir()), '/\\');

$handlerOk   = ((string)ini_get('session.save_handler') === 'files');
$pathWritable = is_writable($sessionSavePath);

if (!$handlerOk) {
    blocked('Session handler = files', 'session.save_handler không phải "files"');
    goto summary;
}
if (!$pathWritable) {
    blocked('Session save_path writable', "Không ghi được: $sessionSavePath");
    goto summary;
}
pass('Session handler and save_path OK');


// ─── Tạo và ghi test session ─────────────────────────────────────────────────

$testSessionId    = 'gcreg' . bin2hex(random_bytes(13));
$sessionFile      = $sessionSavePath . DIRECTORY_SEPARATOR . 'sess_' . $testSessionId;

// Register cleanup shutdown function
$cleanupCompleted = false;
register_shutdown_function(function () use (
    &$cleanupCompleted,
    $testSessionId,
    $sessionFile
): void {
    if ($cleanupCompleted) {
        return;
    }

    $cleanupCompleted = true;
    $success = cleanupGuestTestSession(
        $testSessionId,
        $sessionFile
    );

    if (!$success) {
        fwrite(
            STDERR,
            "[FAIL] Emergency test-session cleanup failed\n"
        );

        exit(1);
    }
});

function cleanupGuestTestSession(string $sessionId, string $sessionFile): bool {
    if ($sessionId === '') {
        return true;
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    session_id($sessionId);

    if (!session_start()) {
        return false;
    }

    $_SESSION = [];
    $destroyed = session_destroy();
    session_write_close();

    return $destroyed && !file_exists($sessionFile);
}

session_id($testSessionId);
session_start();
$_SESSION['cart'] = [
    $productId => ['product_id' => $productId, 'quantity' => 1],
];
unset($_SESSION['user']); // guest — không có user
session_write_close();

if (!file_exists($sessionFile)) {
    blocked('Guest session written', "Session file không tồn tại sau session_write_close()");
    goto summary;
}
pass('Guest session written via native PHP session API (no raw serialization)');


// ─── Baseline ────────────────────────────────────────────────────────────────

$ordersBefore = (int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$orderItemsBefore = (int)$db->query('SELECT COUNT(*) FROM order_items')->fetchColumn();
$stockBefore  = (int)$db->query(
    "SELECT stock FROM products WHERE id = {$productId}"
)->fetchColumn();

// ─── Helpers (sau session setup) ─────────────────────────────────────────────

/** GET request trả về ['status', 'body', 'location', 'error']. */
function httpGet(string $url, string $sessionId, string $sessionName): array
{
    $headers = [];
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_FOLLOWLOCATION  => false,
        CURLOPT_TIMEOUT         => 10,
        CURLOPT_COOKIE          => $sessionName . '=' . $sessionId,
        CURLOPT_HTTPHEADER      => ['User-Agent: TechPilot-GuestCheckoutTest/1.0'],
        CURLOPT_HEADERFUNCTION  => function ($ch, string $line) use (&$headers): int {
            $len = strlen($line);
            $trimmed = trim($line);
            if (str_contains($trimmed, ':')) {
                [$name, $value] = explode(':', $trimmed, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
            return $len;
        },
    ]);
    $body  = (string)curl_exec($ch);
    $error = (string)curl_error($ch);
    $code  = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status'   => $code,
        'body'     => $body,
        'location' => $headers['location'] ?? '',
        'error'    => $error,
    ];
}

// ─── Test chính: GET /checkout với guest session có cart ─────────────────────

$res = httpGet($baseUrl . '/checkout', $testSessionId, $sessionName);


// ─── Assertions ──────────────────────────────────────────────────────────────

$locationLower      = strtolower($res['location']);
$hasPhpDiagnostic   = preg_match(
    '/(?:Fatal error|Parse error|Warning:|Notice:|Deprecated:|Uncaught (?:Error|Exception)|Trying to access array offset)/i',
    $res['body']
) === 1;
$visibleText        = trim((string)preg_replace('/\s+/u', ' ', strip_tags($res['body'])));
$hasCheckoutContent = stripos($visibleText, 'Thanh to') !== false
    || stripos($visibleText, 'Đặt hàng') !== false
    || stripos($visibleText, 'checkout') !== false;

// HTTP 200 (bắt buộc — cart có item hợp lệ, không được redirect)
if ($res['error'] !== '') {
    fail('HTTP request succeeds (no cURL error)', 'cURL error: ' . $res['error']);
} elseif ($res['status'] === 200) {
    pass('GET /checkout returns HTTP 200');
} else {
    $detail = "HTTP {$res['status']}";
    if ($res['location'] !== '') {
        $detail .= ", Location: {$res['location']}";
    }
    fail('GET /checkout returns HTTP 200', $detail);
}

// Không redirect /auth/login
if (str_contains($locationLower, '/auth/login')) {
    fail('Not redirected to /auth/login', "Location: {$res['location']}");
} else {
    pass('Not redirected to /auth/login');
}

// Không redirect /cart (cart có item — không nên redirect về cart)
if ($res['status'] === 302 && str_contains($locationLower, '/cart')) {
    fail('Not redirected to /cart (cart has valid item)', "Location: {$res['location']}");
} else {
    pass('Not redirected to /cart');
}

// Không 4xx/5xx
if ($res['status'] >= 400) {
    fail('No HTTP 4xx/5xx error', "HTTP {$res['status']}");
} else {
    pass('No HTTP 4xx/5xx error');
}

// Không fatal/warning
if ($hasPhpDiagnostic) {
    fail('No PHP diagnostic in response body',
        'Fatal/Parse error/Warning/Notice/Deprecated/Uncaught/null offset detected');
} else {
    pass('No PHP diagnostic in response body');
}

// Có nội dung checkout
if ($res['status'] === 200) {
    if ($hasCheckoutContent) {
        pass('Checkout page content marker present');
    } else {
        fail('Checkout page content marker present',
            'Không thấy "Thanh toán", "Đặt hàng" hoặc "checkout" trong body'
        );
    }
}

// ─── Side-effect checks ───────────────────────────────────────────────────────

$ordersAfter = (int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$orderItemsAfter = (int)$db->query('SELECT COUNT(*) FROM order_items')->fetchColumn();
$stockAfter  = (int)$db->query(
    "SELECT stock FROM products WHERE id = {$productId}"
)->fetchColumn();

if ($ordersAfter === $ordersBefore) {
    pass('No new order created');
} else {
    fail('No new order created',
        "ORDER COUNT changed: {$ordersBefore} → {$ordersAfter}"
    );
}

if ($orderItemsAfter === $orderItemsBefore) {
    pass('No new order_item created');
} else {
    fail('No new order_item created',
        "ORDER_ITEMS COUNT changed: {$orderItemsBefore} → {$orderItemsAfter}"
    );
}

if ($stockAfter === $stockBefore) {
    pass('Stock unchanged');
} else {
    fail('Stock unchanged', "stock changed: {$stockBefore} → {$stockAfter} (id={$productId})");
}

// ─── Cleanup bình thường trước Summary ───────────────────────────────────────

if (isset($testSessionId) && isset($sessionFile)) {
    $cleanupSucceeded = cleanupGuestTestSession(
        $testSessionId,
        $sessionFile
    );

    $cleanupCompleted = true;

    if ($cleanupSucceeded) {
        pass('Test session cleaned up');
    } else {
        fail(
            'Test session cleaned up',
            'Test session cleanup failed'
        );
    }
}

// ─── Summary ─────────────────────────────────────────────────────────────────

summary:
echo "\n============================================================\n";
printf(
    "Guest Checkout Regression Results: %d passed, %d failed\n",
    $results['passed'],
    $results['failed']
);
echo "============================================================\n";

exit($results['failed'] > 0 ? 1 : 0);
