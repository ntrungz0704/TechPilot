<?php
/**
 * Dịch vụ AI Đa nhà cung cấp (Multi-Provider Priority Failover AI Engine)
 * Tự động dự phòng ưu tiên theo thứ tự: Google Gemini -> Groq Cloud -> QwenCloud (DashScope)
 */

class AiService
{
    /**
     * Lấy cấu hình AI toàn cục từ config/ai.php
     */
    public static function getConfig(): array
    {
        $configPath = ROOT_PATH . '/config/ai.php';
        if (file_exists($configPath)) {
            $config = require $configPath;
            if (is_array($config)) {
                return $config;
            }
        }

        return [
            'provider_order' => ['gemini', 'groq', 'qwen'],
            'timeout'        => 20,
            'providers'      => [
                'gemini' => [
                    'name'     => 'Google Gemini',
                    'api_key'  => trim((string)(getenv('GEMINI_API_KEY') ?: '')),
                    'model'    => trim((string)(getenv('GEMINI_MODEL') ?: 'gemini-3.6-flash')),
                    'api_base' => 'https://generativelanguage.googleapis.com/v1beta',
                    'type'     => 'gemini_native',
                ],
                'groq' => [
                    'name'     => 'Groq Cloud',
                    'api_key'  => trim((string)(getenv('GROQ_API_KEY') ?: '')),
                    'model'    => trim((string)(getenv('GROQ_MODEL') ?: 'openai/gpt-oss-20b')),
                    'api_base' => 'https://api.groq.com/openai/v1',
                    'type'     => 'openai_compatible',
                ],
                'qwen' => [
                    'name'     => 'QwenCloud (DashScope)',
                    'api_key'  => trim((string)(getenv('QWEN_API_KEY') ?: '')),
                    'model'    => trim((string)(getenv('QWEN_MODEL') ?: 'qwen3.7-flash')),
                    'api_base' => 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1',
                    'type'     => 'openai_compatible',
                ],
            ],
        ];
    }

    /**
     * Kiểm tra xem có ít nhất một AI Provider được cấu hình API Key hay không
     */
    public static function isConfigured(): bool
    {
        $config = self::getConfig();
        foreach ($config['provider_order'] as $pKey) {
            $pConfig = $config['providers'][$pKey] ?? null;
            if ($pConfig && !empty($pConfig['api_key'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Lấy tên nhà cung cấp chính hiện tại
     */
    public static function getPrimaryProvider(): string
    {
        $config = self::getConfig();
        $order = $config['provider_order'] ?? ['gemini'];
        return $order[0] ?? 'gemini';
    }

    /**
     * Gửi yêu cầu sinh nội dung với Priority Failover
     */
    public static function generateContent(string $prompt, array $options = []): array
    {
        $config = self::getConfig();
        $order = $config['provider_order'] ?? ['gemini', 'groq', 'qwen'];
        $globalTimeout = $options['timeout'] ?? $config['timeout'] ?? 20;

        $attempted = [];
        $lastError = null;

        foreach ($order as $providerKey) {
            $provider = $config['providers'][$providerKey] ?? null;
            if (!$provider || empty($provider['api_key'])) {
                continue; // Bỏ qua provider chưa cấu hình API Key
            }

            $attempted[] = $provider['name'];

            if ($provider['type'] === 'gemini_native') {
                $res = self::callGeminiNative($provider, $prompt, $globalTimeout);
            } else {
                $res = self::callOpenAiCompatible($provider, $prompt, $globalTimeout);
            }

            if ($res['success']) {
                $res['active_provider'] = $providerKey;
                $res['active_provider_name'] = $provider['name'];
                $res['attempted_providers'] = $attempted;
                return $res;
            }

            $lastError = $res;

            // Xắc định lỗi có thuộc loại tạm thời/quota để chuyển vùng (Failover) hay không
            $httpCode = $res['http_status'] ?? 0;
            $errCode = $res['error_code'] ?? '';

            if (!self::isTransientError($httpCode, $errCode)) {
                // Nếu là lỗi 400 Bad Request hoặc Safety Block, dừng lại ngay không broadcast request lỗi
                $res['attempted_providers'] = $attempted;
                return $res;
            }
        }

        if (empty($attempted)) {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => 'AI_NOT_CONFIGURED',
                'message'     => 'Chưa cấu hình API Key cho bất kỳ nhà cung cấp AI nào.',
                'http_status' => 0,
                'attempted_providers' => []
            ];
        }

        return [
            'success'             => false,
            'text'                => '',
            'error_code'          => $lastError['error_code'] ?? 'ALL_PROVIDERS_FAILED',
            'message'             => 'Tất cả các nhà cung cấp AI (' . implode(', ', $attempted) . ') đều gián đoạn: ' . ($lastError['message'] ?? 'Unknown error'),
            'http_status'         => $lastError['http_status'] ?? 0,
            'attempted_providers' => $attempted
        ];
    }

    /**
     * Gửi yêu cầu cURL tới Google Gemini Native API
     */
    private static function callGeminiNative(array $provider, string $prompt, int $timeout): array
    {
        $apiKey = $provider['api_key'];
        $model = $provider['model'];
        $apiBase = $provider['api_base'];

        $url = $apiBase . '/models/' . $model . ':generateContent';

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-goog-api-key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        try {
            curl_setopt_array($ch, SecureCurl::buildTlsOptions());
        } catch (RuntimeException $e) {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => $e->getMessage(),
                'message'     => 'TLS configuration error: Invalid CA bundle path.',
                'http_status' => 0
            ];
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($ch);
        $curlErr = curl_error($ch);

        if ($response === false) {
            $isTlsError = $curlErrNo === CURLE_SSL_CONNECT_ERROR || $curlErrNo === CURLE_PEER_FAILED_VERIFICATION || (defined('CURLE_SSL_CACERT') && $curlErrNo === CURLE_SSL_CACERT) || str_contains(strtolower((string)$curlErr), 'certificate') || str_contains(strtolower((string)$curlErr), 'ssl');
            
            if ($isTlsError) {
                $errorCode = 'TLS_VERIFICATION_FAILED';
                $errorMessage = 'TLS Certificate Verification Failed.';
            } elseif ($curlErrNo === CURLE_OPERATION_TIMEDOUT) {
                $errorCode = 'AI_TIMEOUT';
                $errorMessage = 'Connection timeout.';
            } else {
                $errorCode = 'AI_NETWORK_ERROR';
                $errorMessage = 'Lỗi kết nối mạng: ' . $curlErr;
            }

            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => $errorCode,
                'message'     => $errorMessage,
                'http_status' => 0
            ];
        }

        if ($httpCode !== 200) {
            $errCode = self::mapHttpErrorCode($httpCode);
            $errData = json_decode($response, true);
            $errDetail = $errData['error']['message'] ?? "HTTP {$httpCode}";

            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => $errCode,
                'message'     => "Gemini ({$errCode}): {$errDetail}",
                'http_status' => $httpCode
            ];
        }

        $data = json_decode($response, true);
        if (!$data || !is_array($data)) {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => 'AI_INVALID_RESPONSE',
                'message'     => 'Phản hồi từ Gemini không phải JSON hợp lệ.',
                'http_status' => 200
            ];
        }

        $finishReason = $data['candidates'][0]['finishReason'] ?? '';
        if ($finishReason === 'SAFETY' || $finishReason === 'RECITATION' || $finishReason === 'BLOCKLIST') {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => 'AI_CONTENT_BLOCKED',
                'message'     => 'Nội dung bị chặn bởi chính sách an toàn của Gemini.',
                'http_status' => 200
            ];
        }

        $textResult = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (trim($textResult) === '') {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => 'AI_EMPTY_RESPONSE',
                'message'     => 'Nội dung phản hồi từ Gemini rỗng.',
                'http_status' => 200
            ];
        }

        return [
            'success'     => true,
            'text'        => trim($textResult),
            'error_code'  => null,
            'message'     => 'Thành công',
            'http_status' => 200
        ];
    }

    /**
     * Gửi yêu cầu cURL tới OpenAI Compatible API (Dùng cho Groq và QwenCloud)
     */
    private static function callOpenAiCompatible(array $provider, string $prompt, int $timeout): array
    {
        $apiKey = $provider['api_key'];
        $model = $provider['model'];
        $apiBase = $provider['api_base'];
        $providerName = $provider['name'];

        $url = $apiBase . '/chat/completions';

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        try {
            curl_setopt_array($ch, SecureCurl::buildTlsOptions());
        } catch (RuntimeException $e) {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => $e->getMessage(),
                'message'     => 'TLS configuration error: Invalid CA bundle path.',
                'http_status' => 0
            ];
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($ch);
        $curlErr = curl_error($ch);

        if ($response === false) {
            $isTlsError = $curlErrNo === CURLE_SSL_CONNECT_ERROR || $curlErrNo === CURLE_PEER_FAILED_VERIFICATION || (defined('CURLE_SSL_CACERT') && $curlErrNo === CURLE_SSL_CACERT) || str_contains(strtolower((string)$curlErr), 'certificate') || str_contains(strtolower((string)$curlErr), 'ssl');
            
            if ($isTlsError) {
                $errorCode = 'TLS_VERIFICATION_FAILED';
                $errorMessage = 'TLS Certificate Verification Failed.';
            } elseif ($curlErrNo === CURLE_OPERATION_TIMEDOUT) {
                $errorCode = 'AI_TIMEOUT';
                $errorMessage = 'Connection timeout.';
            } else {
                $errorCode = 'AI_NETWORK_ERROR';
                $errorMessage = "Lỗi kết nối {$providerName}: {$curlErr}";
            }

            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => $errorCode,
                'message'     => $errorMessage,
                'http_status' => 0
            ];
        }

        if ($httpCode !== 200) {
            $errCode = self::mapHttpErrorCode($httpCode);
            $errData = json_decode($response, true);
            $errDetail = $errData['error']['message'] ?? "HTTP {$httpCode}";

            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => $errCode,
                'message'     => "{$providerName} ({$errCode}): {$errDetail}",
                'http_status' => $httpCode
            ];
        }

        $data = json_decode($response, true);
        if (!$data || !is_array($data)) {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => 'AI_INVALID_RESPONSE',
                'message'     => "Phản hồi từ {$providerName} không phải JSON hợp lệ.",
                'http_status' => 200
            ];
        }

        $textResult = $data['choices'][0]['message']['content'] ?? '';
        if (trim($textResult) === '') {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => 'AI_EMPTY_RESPONSE',
                'message'     => "Nội dung phản hồi từ {$providerName} rỗng.",
                'http_status' => 200
            ];
        }

        return [
            'success'     => true,
            'text'        => trim($textResult),
            'error_code'  => null,
            'message'     => 'Thành công',
            'http_status' => 200
        ];
    }

    /**
     * Hàm gọi dự phòng tương thích ngược (wrapper)
     */
    public static function callGemini(string $prompt, ?array $contextData = null): string
    {
        $res = self::generateContent($prompt);
        if ($res['success']) {
            return $res['text'];
        }

        return "🤖 " . ($res['message'] ?? 'Trợ lý AI đang tạm thời không khả dụng. Vui lòng thử lại sau.');
    }

    /**
     * Kiểm tra xem lỗi có thuộc loại tạm thời/quota để quyết định Failover hay dừng lại
     */
    public static function isTransientError(int $httpCode, string $errorCode): bool
    {
        // 400 Bad Request -> KHÔNG failover (cần sửa request)
        if ($httpCode === 400 || $errorCode === 'AI_BAD_REQUEST') {
            return false;
        }

        // Content blocked -> KHÔNG failover
        if ($errorCode === 'AI_CONTENT_BLOCKED') {
            return false;
        }

        // 429 Quota/Rate Limit, 402 Insufficient Balance, 408 Timeout, 500/502/503/504 Server Error, cURL network error
        if (in_array($httpCode, [429, 402, 408, 500, 502, 503, 504], true) || $httpCode === 0) {
            return true;
        }

        if (in_array($errorCode, ['AI_QUOTA_EXCEEDED', 'AI_TIMEOUT', 'AI_NETWORK_ERROR', 'AI_PROVIDER_UNAVAILABLE', 'TLS_VERIFICATION_FAILED', 'TLS_CA_BUNDLE_INVALID'], true)) {
            return true;
        }

        return false;
    }

    /**
     * Map HTTP status code sang mã lỗi chuẩn AI
     */
    private static function mapHttpErrorCode(int $httpCode): string
    {
        switch ($httpCode) {
            case 400: return 'AI_BAD_REQUEST';
            case 401:
            case 403: return 'AI_INVALID_API_KEY';
            case 402: return 'AI_INSUFFICIENT_BALANCE';
            case 404: return 'AI_MODEL_NOT_FOUND';
            case 408: return 'AI_TIMEOUT';
            case 429: return 'AI_QUOTA_EXCEEDED';
            case 500:
            case 502:
            case 503:
            case 504: return 'AI_PROVIDER_UNAVAILABLE';
            default:  return 'AI_HTTP_ERROR_' . $httpCode;
        }
    }
}
