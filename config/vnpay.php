<?php

return [
    'tmn_code' => getenv('VNPAY_TMN_CODE') ?: '',
    'hash_secret' => getenv('VNPAY_HASH_SECRET') ?: '',

    'payment_url' => 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html',

    'return_url' => 'http://localhost/techpilot/payment/vnpay-return',

    'ipn_url' => 'https://TEN-MIEN-CONG-KHAI-CUA-BAN/payment/vnpay-ipn',
];