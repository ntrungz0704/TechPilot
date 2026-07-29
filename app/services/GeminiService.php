<?php
/**
 * Dịch vụ kết nối Google Gemini AI API chính thức cho TechPilot
 */

class GeminiService
{
    /**
     * Lấy cấu hình Gemini từ config/gemini.php
     */
    private static function getConfig(): array
    {
        $configPath = ROOT_PATH . '/config/gemini.php';
        if (file_exists($configPath)) {
            $config = require $configPath;
            if (is_array($config)) {
                return $config;
            }
        }

        return [
            'api_key'  => trim((string)(getenv('GEMINI_API_KEY') ?: '')),
            'model'    => trim((string)(getenv('GEMINI_MODEL') ?: 'gemini-3.6-flash')),
            'api_base' => rtrim(trim((string)(getenv('GEMINI_API_BASE') ?: 'https://generativelanguage.googleapis.com/v1beta')), '/'),
            'timeout'  => 20,
        ];
    }

    /**
     * Kiểm tra Gemini API Key đã được cấu hình hay chưa
     */
    public static function isConfigured(): bool
    {
        $config = self::getConfig();
        return !empty($config['api_key']);
    }

    /**
     * Lấy tên model đang được cấu hình
     */
    public static function getConfiguredModel(): string
    {
        $config = self::getConfig();
        return $config['model'] ?? 'gemini-3.6-flash';
    }

    /**
     * Kiểm tra trạng thái cấu hình hiện tại
     */
    public static function validateConfiguration(): array
    {
        $config = self::getConfig();
        $apiKey = $config['api_key'] ?? '';
        $isConfigured = !empty($apiKey);

        return [
            'configured'  => $isConfigured,
            'api_key_set' => $isConfigured,
            'model'       => $config['model'] ?? 'gemini-3.6-flash',
            'error'       => $isConfigured ? null : 'GEMINI_NOT_CONFIGURED',
        ];
    }

    /**
     * Lấy danh sách các model khả dụng từ Gemini API
     */
    public static function listAvailableModels(): array
    {
        $config = self::getConfig();
        $apiKey = $config['api_key'] ?? '';
        $apiBase = $config['api_base'] ?? 'https://generativelanguage.googleapis.com/v1beta';

        if (empty($apiKey)) {
            return [
                'success'    => false,
                'error_code' => 'GEMINI_NOT_CONFIGURED',
                'message'    => 'API Key chưa được cấu hình trong .env',
                'models'     => []
            ];
        }

        $url = $apiBase . '/models';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-goog-api-key: ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);

        if ($response === false) {
            return [
                'success'    => false,
                'error_code' => 'GEMINI_NETWORK_ERROR',
                'message'    => 'Lỗi kết nối cURL: ' . $curlErr,
                'models'     => []
            ];
        }

        if ($httpCode !== 200) {
            return [
                'success'    => false,
                'error_code' => self::mapErrorCode($httpCode),
                'message'    => "Lỗi HTTP {$httpCode} khi lấy danh sách model.",
                'models'     => []
            ];
        }

        $data = json_decode($response, true);
        $models = [];
        if (isset($data['models']) && is_array($data['models'])) {
            foreach ($data['models'] as $m) {
                $models[] = [
                    'name'                       => str_replace('models/', '', $m['name'] ?? ''),
                    'display_name'               => $m['displayName'] ?? '',
                    'supported_generation_methods' => $m['supportedGenerationMethods'] ?? []
                ];
            }
        }

        return [
            'success'    => true,
            'error_code' => null,
            'message'    => 'Lấy danh sách model thành công.',
            'models'     => $models
        ];
    }

    /**
     * Kiểm tra sức khỏe kết nối Gemini API (Health Check)
     */
    public static function healthCheck(): array
    {
        $config = self::getConfig();
        $apiKey = $config['api_key'] ?? '';

        if (empty($apiKey)) {
            return [
                'status'     => 'NOT_CONFIGURED',
                'configured' => false,
                'model'      => $config['model'] ?? 'gemini-3.6-flash',
                'message'    => 'Chưa cấu hình GEMINI_API_KEY trong .env'
            ];
        }

        // Test generateContent smoke test
        $res = self::generateContent('Chỉ trả lời đúng chữ OK', ['timeout' => 10]);

        if ($res['success']) {
            return [
                'status'     => 'READY',
                'configured' => true,
                'model'      => $config['model'],
                'message'    => 'Gemini API sẵn sàng hoạt động.'
            ];
        }

        return [
            'status'     => $res['error_code'] ?? 'ERROR',
            'configured' => true,
            'model'      => $config['model'],
            'message'    => $res['message'] ?? 'Lỗi không xác định từ Gemini API'
        ];
    }

    /**
     * Gửi yêu cầu sinh nội dung chính thức tới Google Gemini API
     */
    public static function generateContent(string $prompt, array $options = []): array
    {
        $config = self::getConfig();
        $apiKey = $config['api_key'] ?? '';
        $model = $config['model'] ?? 'gemini-3.6-flash';
        $apiBase = $config['api_base'] ?? 'https://generativelanguage.googleapis.com/v1beta';
        $timeout = $options['timeout'] ?? $config['timeout'] ?? 20;

        if (empty($apiKey)) {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => 'GEMINI_NOT_CONFIGURED',
                'message'     => 'Trợ lý AI chưa được cấu hình API Key.',
                'http_status' => 0
            ];
        }

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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrNo = curl_errno($ch);
        $curlErr = curl_error($ch);

        if ($response === false) {
            $errorCode = ($curlErrNo === CURLE_OPERATION_TIMEDOUT) ? 'GEMINI_TIMEOUT' : 'GEMINI_NETWORK_ERROR';
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => $errorCode,
                'message'     => 'Không thể kết nối tới máy chủ Gemini AI: ' . $curlErr,
                'http_status' => 0
            ];
        }

        if ($httpCode !== 200) {
            $errCode = self::mapErrorCode($httpCode);
            $errData = json_decode($response, true);
            $errDetail = $errData['error']['message'] ?? "Lỗi máy chủ HTTP {$httpCode}";

            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => $errCode,
                'message'     => "Lỗi API Gemini ({$errCode}): {$errDetail}",
                'http_status' => $httpCode
            ];
        }

        $data = json_decode($response, true);
        if (!$data || !is_array($data)) {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => 'GEMINI_INVALID_RESPONSE',
                'message'     => 'Phản hồi từ AI không phải JSON hợp lệ.',
                'http_status' => 200
            ];
        }

        // Kiểm tra candidate có bị block hay không
        $finishReason = $data['candidates'][0]['finishReason'] ?? '';
        if ($finishReason === 'SAFETY' || $finishReason === 'RECITATION' || $finishReason === 'BLOCKLIST') {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => 'GEMINI_CONTENT_BLOCKED',
                'message'     => 'Nội dung bị chặn bởi chính sách an toàn của AI.',
                'http_status' => 200
            ];
        }

        $textResult = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if (trim($textResult) === '') {
            return [
                'success'     => false,
                'text'        => '',
                'error_code'  => 'GEMINI_EMPTY_RESPONSE',
                'message'     => 'Không nhận được nội dung phản hồi từ mô hình AI.',
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

        // Trả về thông báo lỗi rõ ràng, tuyệt đối không dùng Mock giả mạo Gemini
        return "🤖 " . ($res['message'] ?? 'Trợ lý AI đang tạm thời không khả dụng. Vui lòng thử lại sau.');
    }

    /**
     * Map HTTP status code sang mã lỗi chuẩn Gemini
     */
    private static function mapErrorCode(int $httpCode): string
    {
        switch ($httpCode) {
            case 400: return 'GEMINI_BAD_REQUEST';
            case 401:
            case 403: return 'GEMINI_INVALID_API_KEY';
            case 404: return 'GEMINI_MODEL_NOT_FOUND';
            case 429: return 'GEMINI_QUOTA_EXCEEDED';
            case 500:
            case 502:
            case 503: return 'GEMINI_PROVIDER_UNAVAILABLE';
            default:  return 'GEMINI_HTTP_ERROR_' . $httpCode;
        }
    }
}
