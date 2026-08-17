<?php

$appUrl = rtrim((string)(getenv('APP_URL') ?: 'http://127.0.0.1:8000'), '/');
$rawAppEnv = defined('APP_ENV') ? APP_ENV : (getenv('APP_ENV') ?: 'production');
$appEnv = strtolower(trim((string)$rawAppEnv));
$appEnv = in_array($appEnv, ['development', 'testing', 'production'], true)
    ? $appEnv
    : 'production';

// Cho phép file local cung cấp credential, nhưng các cờ bảo mật bên dưới luôn
// được tính lại và không thể bị override để bật simulator trên production.
$local = [];
$localFile = __DIR__ . '/vnpay.local.php';
if (file_exists($localFile)) {
    $localConfig = require $localFile;
    if (is_array($localConfig)) {
        $local = $localConfig;
    }
}

$tmnCode = trim((string)($local['tmn_code'] ?? (getenv('VNPAY_TMN_CODE') ?: '')));
$hashSecret = trim((string)($local['hash_secret'] ?? (getenv('VNPAY_HASH_SECRET') ?: '')));
$hasGatewayCredentials = strlen($tmnCode) === 8 && $hashSecret !== '';
$simulatorEnabled = false;

$mode = 'disabled';
$paymentUrl = '';
$simulatorHashSecret = '';

if ($hasGatewayCredentials) {
    $mode = 'gateway';
    $paymentUrl = trim((string)($local['payment_url'] ?? ''));
    if ($paymentUrl === '') {
        $paymentUrl = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
    }
} elseif ($appEnv === 'development') {
    // Development mode: dùng VNPay sandbox thật (không phải simulator ảo)
    $mode = 'gateway';
    $tmnCode = 'CGXZLS0Z';
    $hashSecret = 'KQWNBEWIRFXGXMTBKRZDNVEYSQMWLKZG';
    $paymentUrl = 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';
} else {
    // Fail closed: production/testing thiếu credential không được dùng giá trị demo.
    $tmnCode = '';
    $hashSecret = '';
}

$config = array_replace($local, [
    'mode' => $mode,
    'simulator_enabled' => $simulatorEnabled,
    'simulator_hash_secret' => $simulatorHashSecret,
    'tmn_code' => $tmnCode,
    'hash_secret' => $hashSecret,
    'payment_url' => $paymentUrl,
    'return_url' => trim((string)($local['return_url'] ?? (getenv('VNPAY_RETURN_URL') ?: $appUrl . '/payment/vnpay-return'))),
    'ipn_url' => trim((string)($local['ipn_url'] ?? (getenv('VNPAY_IPN_URL') ?: ''))),
]);

return $config;
