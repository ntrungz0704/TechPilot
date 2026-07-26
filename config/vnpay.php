<?php

$config = [
    'tmn_code' => getenv('VNPAY_TMN_CODE') ?: '',
    'hash_secret' => getenv('VNPAY_HASH_SECRET') ?: '',

    'payment_url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',

    'return_url' => getenv('VNPAY_RETURN_URL') ?: 'http://localhost/TechPilot/public/payment/vnpay-return',

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
