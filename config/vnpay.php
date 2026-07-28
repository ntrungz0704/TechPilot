<?php

$appUrl = getenv('APP_URL') ?: 'http://127.0.0.1:8000';

$config = [
    'tmn_code' => getenv('VNPAY_TMN_CODE') ?: '',
    'hash_secret' => getenv('VNPAY_HASH_SECRET') ?: '',

    'payment_url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',

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
