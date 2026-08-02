<?php
declare(strict_types=1);

/**
 * Authenticated Purchase Flow Test — P0-1B
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
echo "AUTHENTICATED PURCHASE FLOW TEST — P0-1B\n";
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
$stmt = $db->query(
    "SELECT id FROM products WHERE status = 'active' AND stock > 0 ORDER BY id LIMIT 1"
);
$row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
if ($row !== false && !empty($row['id'])) {
    $productId = (int)$row['id'];
} else {
    blocked(
        'Valid product fixture',
        'BLOCKED — NO VALID PRODUCT FIXTURE: Không có sản phẩm nào status=active AND stock>0'
    );
    goto summary;
}
pass("Valid product found (id={$productId})");

// 4. Valid user
$userId = 0;
$stmtUser = $db->query("SELECT id FROM users WHERE status = 'active' ORDER BY id LIMIT 1");
$rowUser = $stmtUser ? $stmtUser->fetch(\PDO::FETCH_ASSOC) : false;
if ($rowUser !== false && !empty($rowUser['id'])) {
    $userId = (int)$rowUser['id'];
} else {
    blocked(
        'Valid user fixture',
        'BLOCKED — NO ACTIVE USER FIXTURE: Không có user nào status=active'
    );
    goto summary;
}
pass("Valid user found (id={$userId})");


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

// ─── Test Session Setup ──────────────────────────────────────────────────────

$testSessionId    = 'authflow' . bin2hex(random_bytes(13));
$sessionFile      = $sessionSavePath . DIRECTORY_SEPARATOR . 'sess_' . $testSessionId;

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
    $success = cleanupTestSession(
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

function cleanupTestSession(string $sessionId, string $sessionFile): bool {
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

function writeSessionData(string $sessionId, array $data): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    session_id($sessionId);
    session_start();
    $_SESSION = $data;
    session_write_close();
}

$csrfToken = bin2hex(random_bytes(32));
$submitToken = bin2hex(random_bytes(16));

$guestSessionData = [
    'cart' => [
        $productId => ['product_id' => $productId, 'quantity' => 1]
    ],
    'csrf_token' => $csrfToken,
    'submit_token' => $submitToken,
    'last_order' => ['fake' => 'order'] // for Scenario D
];

$authSessionData = $guestSessionData;
$authSessionData['user'] = ['id' => $userId];

// Write Guest Session
writeSessionData($testSessionId, $guestSessionData);

if (!file_exists($sessionFile)) {
    blocked('Guest session written', "Session file không tồn tại sau session_write_close()");
    goto summary;
}


// ─── Helpers ─────────────────────────────────────────────────────────────

function httpRequest(string $method, string $url, string $sessionId, string $sessionName, array $postData = []): array
{
    $headers = [];
    $ch = curl_init($url);
    $options = [
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_FOLLOWLOCATION  => false,
        CURLOPT_TIMEOUT         => 10,
        CURLOPT_COOKIE          => $sessionName . '=' . $sessionId,
        CURLOPT_HTTPHEADER      => ['User-Agent: TechPilot-AuthFlowTest/1.0'],
        CURLOPT_HEADERFUNCTION  => function ($ch, string $line) use (&$headers): int {
            $len = strlen($line);
            $trimmed = trim($line);
            if (str_contains($trimmed, ':')) {
                [$name, $value] = explode(':', $trimmed, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
            return $len;
        },
    ];
    if (strtoupper($method) === 'POST') {
        $options[CURLOPT_POST] = true;
        if (!empty($postData)) {
            $options[CURLOPT_POSTFIELDS] = http_build_query($postData);
        }
    }
    curl_setopt_array($ch, $options);
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

function hasPhpDiagnostic(string $body): bool {
    return preg_match(
        '/(?:Fatal error|Parse error|Warning:|Notice:|Deprecated:|Uncaught (?:Error|Exception)|Trying to access array offset)/i',
        $body
    ) === 1;
}

function getSystemMetrics(PDO $db, int $productId): array {
    return [
        'orders' => (int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
        'order_items' => (int)$db->query('SELECT COUNT(*) FROM order_items')->fetchColumn(),
        'stock' => (int)$db->query("SELECT stock FROM products WHERE id = {$productId}")->fetchColumn()
    ];
}

$metricsBefore = getSystemMetrics($db, $productId);


// ─── SCENARIO A: Guest GET checkout bị chặn ──────────────────────────────────
echo "\n--- Scenario A: Guest GET checkout ---\n";
$resA = httpRequest('GET', $baseUrl . '/checkout', $testSessionId, $sessionName);
$locA = strtolower($resA['location']);
$pathA = parse_url($locA, PHP_URL_PATH);
$queryA = parse_url($locA, PHP_URL_QUERY);

if ($resA['status'] === 302 && trim((string)$pathA, '/') === 'auth/login' && str_contains((string)$queryA, 'redirect')) {
    pass('Guest GET /checkout redirect login');
} else {
    fail('Guest GET /checkout redirect login', "HTTP {$resA['status']}, Location: {$resA['location']}");
}

// ─── SCENARIO B: Guest POST submit không tạo dữ liệu ─────────────────────────
echo "\n--- Scenario B: Guest POST checkout submit ---\n";
$resB = httpRequest('POST', $baseUrl . '/checkout/submit', $testSessionId, $sessionName, [
    'csrf_token' => $csrfToken,
    'submit_token' => $submitToken,
    'customer_name' => 'Test Guest',
    'phone' => '0912345678',
    'address' => 'Test Address',
    'payment_method' => 'COD'
]);
$locB = strtolower($resB['location']);
$pathB = parse_url($locB, PHP_URL_PATH);
if ($resB['status'] === 302 && trim((string)$pathB, '/') === 'auth/login') {
    pass('Guest POST /checkout/submit redirect login');
} else {
    fail('Guest POST /checkout/submit redirect login', "HTTP {$resB['status']}, Location: {$resB['location']}");
}

$metricsAfterB = getSystemMetrics($db, $productId);
if ($metricsAfterB['orders'] === $metricsBefore['orders']) {
    pass('Guest submit: no order');
} else {
    fail('Guest submit: no order', 'Order count changed');
}
if ($metricsAfterB['order_items'] === $metricsBefore['order_items']) {
    pass('Guest submit: no order_item');
} else {
    fail('Guest submit: no order_item', 'Order items count changed');
}
if ($metricsAfterB['stock'] === $metricsBefore['stock']) {
    pass('Guest submit: stock unchanged');
} else {
    fail('Guest submit: stock unchanged', 'Stock changed');
}

// ─── SCENARIO C: Guest coupon endpoints trả JSON 401 ─────────────────────────
echo "\n--- Scenario C: Guest coupon endpoints ---\n";
$resC1 = httpRequest('POST', $baseUrl . '/checkout/apply_coupon', $testSessionId, $sessionName, ['csrf_token' => $csrfToken, 'coupon_code' => 'FAKE']);
$isJson1 = str_contains(strtolower((string)($resC1['headers']['content-type'] ?? $resC1['body'])), 'json') || ($resC1['body'] && json_decode($resC1['body']) !== null);
$json1 = json_decode($resC1['body'], true);

if ($resC1['status'] === 401 && $isJson1 && isset($json1['success']) && $json1['success'] === false) {
    pass('Guest apply coupon returns HTTP 401 JSON');
} else {
    fail('Guest apply coupon returns HTTP 401 JSON', "HTTP {$resC1['status']}, Body: " . substr($resC1['body'], 0, 100));
}

$resC2 = httpRequest('POST', $baseUrl . '/checkout/remove_coupon', $testSessionId, $sessionName, ['csrf_token' => $csrfToken]);
$isJson2 = str_contains(strtolower((string)($resC2['headers']['content-type'] ?? $resC2['body'])), 'json') || ($resC2['body'] && json_decode($resC2['body']) !== null);
$json2 = json_decode($resC2['body'], true);

if ($resC2['status'] === 401 && $isJson2 && isset($json2['success']) && $json2['success'] === false) {
    pass('Guest remove coupon returns HTTP 401 JSON');
} else {
    fail('Guest remove coupon returns HTTP 401 JSON', "HTTP {$resC2['status']}, Body: " . substr($resC2['body'], 0, 100));
}

// ─── SCENARIO D: Guest success page bị chặn ──────────────────────────────────
echo "\n--- Scenario D: Guest GET success page ---\n";
$resD = httpRequest('GET', $baseUrl . '/checkout/success', $testSessionId, $sessionName);
$locD = strtolower($resD['location']);
$pathD = parse_url($locD, PHP_URL_PATH);
if ($resD['status'] === 302 && trim((string)$pathD, '/') === 'auth/login') {
    pass('Guest GET /checkout/success redirect login');
} else {
    fail('Guest GET /checkout/success redirect login', "HTTP {$resD['status']}, Location: {$resD['location']}");
}

// ─── SCENARIO E: Authenticated user GET checkout thành công ──────────────────
echo "\n--- Scenario E: Authenticated GET checkout ---\n";
writeSessionData($testSessionId, $authSessionData);
$resE = httpRequest('GET', $baseUrl . '/checkout', $testSessionId, $sessionName);

if ($resE['status'] === 200) {
    pass('Authenticated GET /checkout returns HTTP 200');
} else {
    fail('Authenticated GET /checkout returns HTTP 200', "HTTP {$resE['status']}");
}

$visibleText = trim((string)preg_replace('/\s+/u', ' ', strip_tags($resE['body'])));
$hasCheckoutContent = stripos($visibleText, 'Thanh to') !== false
    || stripos($visibleText, 'Đặt hàng') !== false
    || stripos($visibleText, 'checkout') !== false;

if ($hasCheckoutContent) {
    pass('Authenticated checkout marker present');
} else {
    fail('Authenticated checkout marker present', 'Missing checkout text marker');
}

if (!hasPhpDiagnostic($resE['body'])) {
    pass('No PHP diagnostic in authenticated response');
} else {
    fail('No PHP diagnostic in authenticated response', 'Found PHP diagnostic in body');
}


// ─── Cleanup ─────────────────────────────────────────────────────────────────

if (isset($testSessionId) && isset($sessionFile)) {
    $cleanupSucceeded = cleanupTestSession(
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
    "Authenticated Purchase Flow Results: %d passed, %d failed\n",
    $results['passed'],
    $results['failed']
);
echo "============================================================\n";

exit($results['failed'] > 0 ? 1 : 0);
