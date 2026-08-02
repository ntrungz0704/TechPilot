<?php
/**
 * Dịch vụ kết nối Gemini AI API cho TechPilot (Proxy Wrapper cho AiService)
 */

require_once __DIR__ . '/AiService.php';

class GeminiService
{
    /**
     * Lấy cấu hình Gemini từ AiService
     */
    private static function getConfig(): array
    {
        $aiConfig = AiService::getConfig();
        $gemini = $aiConfig['providers']['gemini'] ?? [];
        return [
            'api_key'  => $gemini['api_key'] ?? '',
            'model'    => $gemini['model'] ?? 'gemini-3.6-flash',
            'api_base' => $gemini['api_base'] ?? 'https://generativelanguage.googleapis.com/v1beta',
            'timeout'  => $aiConfig['timeout'] ?? 20,
        ];
    }

    /**
     * Kiểm tra xem AI Service đã được cấu hình hay chưa
     */
    public static function isConfigured(): bool
    {
        return AiService::isConfigured();
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
        $isConfigured = self::isConfigured();
        $config = self::getConfig();

        return [
            'configured'  => $isConfigured,
            'api_key_set' => $isConfigured,
            'model'       => $config['model'] ?? 'gemini-3.6-flash',
            'error'       => $isConfigured ? null : 'AI_NOT_CONFIGURED',
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
        $sslVerify = !empty(ini_get('curl.cainfo')) && file_exists((string)ini_get('curl.cainfo'));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $sslVerify);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $sslVerify ? 2 : 0);

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
                'error_code' => 'GEMINI_HTTP_ERROR_' . $httpCode,
                'message'    => "Lỗi HTTP {$httpCode} khi lấy danh sách model.",
                'models'     => []
            ];
        }

        $data = json_decode($response, true);
        $models = [];
        if (isset($data['models']) && is_array($data['models'])) {
            foreach ($data['models'] as $m) {
                $models[] = [
                    'name'                         => str_replace('models/', '', $m['name'] ?? ''),
                    'display_name'                 => $m['displayName'] ?? '',
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
     * Kiểm tra sức khỏe kết nối AI API (Health Check)
     */
    public static function healthCheck(): array
    {
        if (!self::isConfigured()) {
            return [
                'status'     => 'NOT_CONFIGURED',
                'configured' => false,
                'model'      => self::getConfiguredModel(),
                'message'    => 'Chưa cấu hình API Key trong .env'
            ];
        }

        $res = AiService::generateContent('Chỉ trả lời đúng chữ OK', ['timeout' => 10]);

        if ($res['success']) {
            return [
                'status'          => 'READY',
                'configured'      => true,
                'active_provider' => $res['active_provider_name'] ?? 'Gemini',
                'model'           => self::getConfiguredModel(),
                'message'         => 'Hệ thống AI sẵn sàng hoạt động (' . ($res['active_provider_name'] ?? 'Gemini') . ').'
            ];
        }

        return [
            'status'     => $res['error_code'] ?? 'ERROR',
            'configured' => true,
            'model'      => self::getConfiguredModel(),
            'message'    => $res['message'] ?? 'Lỗi không xác định từ máy chủ AI'
        ];
    }

    /**
     * Ủy quyền hàm generateContent cho AiService
     */
    public static function generateContent(string $prompt, array $options = []): array
    {
        return AiService::generateContent($prompt, $options);
    }

    /**
     * Ủy quyền hàm callGemini cho AiService
     */
    public static function callGemini(string $prompt, ?array $contextData = null): string
    {
        return AiService::callGemini($prompt, $contextData);
    }
}
