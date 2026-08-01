<?php

/**
 * VNPay environment security regression tests.
 *
 * The configuration cases run in isolated PHP processes because APP_ENV is a
 * constant and must not leak between scenarios.
 */

$rootPath = dirname(__DIR__);
$passed = 0;
$failed = 0;

function assertSecurity(bool $condition, string $message): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "[PASS] {$message}\n";
        return;
    }

    $failed++;
    echo "[FAIL] {$message}\n";
}

function loadVnpayScenario(string $appEnv, string $tmnCode = '', string $hashSecret = ''): array
{
    global $rootPath;

    $code = <<<'PHP'
$appEnv = (string)($argv[1] ?? 'production');
$tmnCode = (string)($argv[2] ?? '');
$hashSecret = (string)($argv[3] ?? '');
$configPath = (string)($argv[4] ?? '');
$servicePath = (string)($argv[5] ?? '');

define('APP_ENV', $appEnv);
putenv('APP_URL=http://127.0.0.1:8000');
putenv('VNPAY_TMN_CODE=' . $tmnCode);
putenv('VNPAY_HASH_SECRET=' . $hashSecret);
putenv('VNPAY_RETURN_URL=');
putenv('VNPAY_IPN_URL=');

$config = require $configPath;
require_once $servicePath;
$service = new VnpayService();

$signatureValid = false;
$tamperedRejected = false;
if ($service->isConfigured()) {
    $params = [
        'vnp_Amount' => 12500000,
        'vnp_ResponseCode' => '00',
        'vnp_TmnCode' => (string)($config['tmn_code'] ?? ''),
        'vnp_TransactionStatus' => '00',
        'vnp_TxnRef' => 'SECURITY-TEST-ORDER',
    ];
    ksort($params);
    $hashParts = [];
    foreach ($params as $key => $value) {
        $hashParts[] = urlencode($key) . '=' . urlencode((string)$value);
    }
    $params['vnp_SecureHash'] = hash_hmac(
        'sha512',
        implode('&', $hashParts),
        (string)($config['hash_secret'] ?? '')
    );

    $signatureValid = $service->verifyResponse($params);
    $params['vnp_Amount'] = 12500001;
    $tamperedRejected = !$service->verifyResponse($params);
}

echo json_encode([
    'mode' => (string)($config['mode'] ?? ''),
    'simulator_enabled' => (bool)($config['simulator_enabled'] ?? false),
    'tmn_code' => (string)($config['tmn_code'] ?? ''),
    'hash_secret' => (string)($config['hash_secret'] ?? ''),
    'simulator_hash_secret' => (string)($config['simulator_hash_secret'] ?? ''),
    'payment_url' => (string)($config['payment_url'] ?? ''),
    'configured' => $service->isConfigured(),
    'signature_valid' => $signatureValid,
    'tampered_rejected' => $tamperedRejected,
], JSON_UNESCAPED_SLASHES);
PHP;

    $command = [
        PHP_BINARY,
        '-d',
        'display_errors=0',
        '-r',
        $code,
        $appEnv,
        $tmnCode,
        $hashSecret,
        $rootPath . '/config/vnpay.php',
        $rootPath . '/app/services/VnpayService.php',
    ];

    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($command, $descriptors, $pipes, $rootPath, null, ['bypass_shell' => true]);
    if (!is_resource($process)) {
        throw new RuntimeException('Không thể khởi chạy PHP subprocess cho VNPay test.');
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        throw new RuntimeException('VNPay subprocess thất bại: ' . trim($stderr));
    }

    $result = json_decode($stdout, true);
    if (!is_array($result)) {
        throw new RuntimeException('VNPay subprocess trả JSON không hợp lệ: ' . trim($stdout));
    }

    return $result;
}

echo "========================================================\n";
echo "=== TECHPILOT VNPAY ENVIRONMENT SECURITY TEST SUITE ===\n";
echo "========================================================\n\n";

$productionDisabled = loadVnpayScenario('production');
assertSecurity($productionDisabled['mode'] === 'disabled', 'Production thiếu credential chuyển sang mode disabled');
assertSecurity($productionDisabled['simulator_enabled'] === false, 'Production không bao giờ bật simulator');
assertSecurity($productionDisabled['configured'] === false, 'Production thiếu credential không được xem là đã cấu hình VNPay');
assertSecurity($productionDisabled['hash_secret'] === '', 'Production không dùng secret demo fallback');
assertSecurity($productionDisabled['payment_url'] === '', 'Production thiếu credential không có payment URL giả');

$productionGateway = loadVnpayScenario('production', 'REAL0001', 'real-production-secret');
assertSecurity($productionGateway['mode'] === 'gateway', 'Production đủ credential chuyển sang gateway mode');
assertSecurity($productionGateway['simulator_enabled'] === false, 'Production gateway vẫn không bật simulator');
assertSecurity($productionGateway['simulator_hash_secret'] === '', 'Production gateway không cấp secret cho simulator');
assertSecurity($productionGateway['configured'] === true, 'Production gateway được xem là đã cấu hình');
assertSecurity(
    $productionGateway['payment_url'] === 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',
    'Production gateway chuyển hướng đến VNPay Sandbox'
);
assertSecurity($productionGateway['signature_valid'] === true, 'Gateway chấp nhận callback có chữ ký hợp lệ');
assertSecurity($productionGateway['tampered_rejected'] === true, 'Gateway từ chối callback bị sửa số tiền');

$developmentSimulator = loadVnpayScenario('development');
assertSecurity($developmentSimulator['mode'] === 'local_simulator', 'Development thiếu credential dùng local simulator');
assertSecurity($developmentSimulator['simulator_enabled'] === true, 'Development local bật simulator');
assertSecurity($developmentSimulator['simulator_hash_secret'] !== '', 'Local simulator có secret demo riêng');
assertSecurity(
    hash_equals($developmentSimulator['hash_secret'], $developmentSimulator['simulator_hash_secret']),
    'VnpayService và simulator dùng cùng secret demo trong local mode'
);
assertSecurity(
    str_ends_with($developmentSimulator['payment_url'], '/payment/vnpay-sandbox-sim'),
    'Development local tạo đúng payment URL đến simulator'
);
assertSecurity($developmentSimulator['signature_valid'] === true, 'Local simulator tạo được callback hợp lệ');
assertSecurity($developmentSimulator['tampered_rejected'] === true, 'Local simulator từ chối callback bị sửa số tiền');

$developmentGateway = loadVnpayScenario('development', 'REAL0001', 'real-development-secret');
assertSecurity($developmentGateway['mode'] === 'gateway', 'Development có credential thật dùng gateway mode');
assertSecurity($developmentGateway['simulator_enabled'] === false, 'Development gateway tắt local simulator');
assertSecurity($developmentGateway['simulator_hash_secret'] === '', 'Development gateway không đưa secret thật vào simulator');

$testingDisabled = loadVnpayScenario('testing');
assertSecurity($testingDisabled['mode'] === 'disabled', 'Testing thiếu credential chuyển sang mode disabled');
assertSecurity($testingDisabled['simulator_enabled'] === false, 'Testing không bật simulator HTTP');

$indexSource = file_get_contents($rootPath . '/public/index.php');
$routeNeedle = '$router->get(\'/payment/vnpay-sandbox-sim\'';
$routePosition = strpos($indexSource, $routeNeedle);
$routePrefix = $routePosition === false
    ? ''
    : substr($indexSource, max(0, $routePosition - 350), 350);
assertSecurity(
    $routePosition !== false
        && str_contains($routePrefix, "APP_ENV === 'development'")
        && str_contains($routePrefix, 'simulator_enabled'),
    'Route simulator chỉ được đăng ký trong development local mode'
);

$controllerSource = file_get_contents($rootPath . '/app/controllers/PaymentController.php');
$methodPosition = strpos($controllerSource, 'function vnpaySandboxSim');
$methodPrefix = $methodPosition === false ? '' : substr($controllerSource, $methodPosition, 900);
assertSecurity(
    str_contains($methodPrefix, "APP_ENV !== 'development'")
        && str_contains($methodPrefix, 'simulator_enabled')
        && str_contains($methodPrefix, 'renderErrorView(404)'),
    'Controller có guard 404 phòng thủ ngoài development local mode'
);
assertSecurity(
    str_contains($methodPrefix, 'simulator_hash_secret'),
    'Controller chỉ ký callback bằng secret riêng của simulator'
);

echo "\n========================================================\n";
echo "VNPay Security Results: {$passed} passed, {$failed} failed\n";
echo "========================================================\n";

exit($failed === 0 ? 0 : 1);
