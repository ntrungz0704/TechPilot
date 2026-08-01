<?php

$appUrl = getenv('APP_URL') ?: 'http://127.0.0.1:8000';
$tmnCode = getenv('VNPAY_TMN_CODE') ?: 'DEMO0001';
$hashSecret = getenv('VNPAY_HASH_SECRET') ?: 'TECHPILOT_VNPAY_SECRET_KEY_123456';

// If custom TMN code is set in .env, use real VNPay Sandbox URL, else use integrated local Gateway Simulator
$useRealSandbox = !empty(getenv('VNPAY_TMN_CODE')) && strlen(trim((string)getenv('VNPAY_TMN_CODE'))) === 8;
$paymentUrl = $useRealSandbox 
    ? 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html' 
    : rtrim($appUrl, '/') . '/payment/vnpay-sandbox-sim';

$config = [
    'tmn_code' => $tmnCode,
    'hash_secret' => $hashSecret,
    'payment_url' => $paymentUrl,
    'return_url' => getenv('VNPAY_RETURN_URL') ?: rtrim($appUrl, '/') . '/payment/vnpay-return',
    'ipn_url' => getenv('VNPAY_IPN_URL') ?: '',
];

$localFile = __DIR__ . '/vnpay.local.php';
if (file_exists($localFile)) {
    $local = require $localFile;
    if (is_array($local)) {
        $config = array_replace($config, $local);
    }
}

return $config;
