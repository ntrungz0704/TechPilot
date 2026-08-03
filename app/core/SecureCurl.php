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
            ini_get('openssl.cafile')
        ];

        foreach ($caPaths as $path) {
            if (!empty($path)) {
                $path = (string)$path;
                if (!file_exists($path) || !is_file($path) || !is_readable($path)) {
                    throw new RuntimeException('TLS_CA_BUNDLE_INVALID');
                }
                $options[CURLOPT_CAINFO] = $path;
                return $options;
            }
        }

        // If no explicit CA path is configured, we rely on the system default trust store.
        // We do not disable verification.
        return $options;
    }
}
