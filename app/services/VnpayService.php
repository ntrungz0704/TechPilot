<?php

class VnpayService
{
    private array $config;

    public function __construct()
    {
        $this->config = require dirname(__DIR__, 2) . '/config/vnpay.php';
    }

    public function isConfigured(): bool
    {
        $tmn = trim((string)($this->config['tmn_code'] ?? ''));
        $secret = trim((string)($this->config['hash_secret'] ?? ''));
        return !empty($tmn)
            && strlen($tmn) === 8
            && !empty($secret)
            && !empty($this->config['payment_url'])
            && !empty($this->config['return_url']);
    }

    public function createPaymentUrl(array $order): string
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('VNPay credentials are not configured or invalid.');
        }
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $amount = (int) $order['total'];
        $orderCode = (string) $order['order_code'];

        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => trim((string)$this->config['tmn_code']),
            'vnp_Amount' => $amount * 100,
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $orderCode,
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $orderCode,
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => $this->config['return_url'],
            'vnp_IpAddr' => $this->getClientIp(),
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_ExpireDate' => date('YmdHis', strtotime('+15 minutes')),
        ];

        ksort($params);

        $hashData = [];
        $queryData = [];

        foreach ($params as $key => $value) {
            if ($value === '' || $value === null) {
                continue;
            }

            $hashData[] = urlencode($key) . '=' . urlencode((string) $value);
            $queryData[] = urlencode($key) . '=' . urlencode((string) $value);
        }

        $hashString = implode('&', $hashData);
        $queryString = implode('&', $queryData);

        $secureHash = hash_hmac(
            'sha512',
            $hashString,
            trim((string)$this->config['hash_secret'])
        );

        return $this->config['payment_url']
            . '?'
            . $queryString
            . '&vnp_SecureHash='
            . $secureHash;
    }

    public function verifyResponse(array $data): bool
    {
        $secret = trim((string)($this->config['hash_secret'] ?? ''));
        if (empty($secret)) return false;

        $tmnCode = trim((string)($this->config['tmn_code'] ?? ''));
        if (!empty($tmnCode) && ($data['vnp_TmnCode'] ?? '') !== $tmnCode) {
            return false;
        }

        $receivedHash = (string) ($data['vnp_SecureHash'] ?? '');

        if ($receivedHash === '') {
            return false;
        }

        unset($data['vnp_SecureHash']);
        unset($data['vnp_SecureHashType']);

        $vnpData = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'vnp_')) {
                $vnpData[$key] = $value;
            }
        }

        ksort($vnpData);

        $hashParts = [];

        foreach ($vnpData as $key => $value) {
            $hashParts[] = urlencode($key)
                . '='
                . urlencode((string) $value);
        }

        $calculatedHash = hash_hmac(
            'sha512',
            implode('&', $hashParts),
            $secret
        );

        return hash_equals($calculatedHash, strtolower($receivedHash));
    }

    private function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
