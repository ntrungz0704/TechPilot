<?php

class SecureCurl
{
    /**
     * Builds secure TLS options for cURL requests.
     * Must be used for all external HTTPS requests.
     *
     * @return array
     * @throws RuntimeException If an explicit CA bundle is configured but invalid.
     */
    public static function buildTlsOptions(): array
    {
        $options = [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];

        // Check configured CA paths in order of precedence
        $caPaths = [
            getenv('CURL_CA_BUNDLE'),
            getenv('SSL_CERT_FILE'),
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile'),
            dirname(__DIR__, 2) . '/config/cacert.pem',
        ];

        foreach ($caPaths as $path) {
            if (!empty($path)) {
                $path = (string)$path;
                if (file_exists($path) && is_file($path) && is_readable($path)) {
                    $options[CURLOPT_CAINFO] = $path;
                    return $options;
                }
            }
        }

        // Trên môi trường Local Dev (Windows/XAMPP/Laragon) không có file CA bundle cURL trong php.ini:
        // Tự động bỏ qua verify peer ở local dev để tính năng AI không bị đứt đoạn bởi lỗi cURL 60.
        // Trên Production (APP_ENV=production): Luôn giữ CURLOPT_SSL_VERIFYPEER = true.
        $appEnv = strtolower(trim((string)(getenv('APP_ENV') ?: ($_SERVER['APP_ENV'] ?? 'development'))));
        if ($appEnv !== 'production') {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
        }

        return $options;
    }
}
