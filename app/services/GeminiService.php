<?php

/**
 * GeminiService - Server-Side Google Gemini API Client with Database Grounding and Strict Security Rules.
 */
class GeminiService
{
    /**
     * Gọi API Gemini với Prompt và Ngữ cảnh dữ liệu thực từ MySQL Database.
     *
     * @param string $prompt
     * @param array|null $contextData
     * @return array
     */
    public static function callGemini(string $prompt, ?array $contextData = null): array
    {
        $config = require ROOT_PATH . '/config/gemini.php';
        $apiKey = trim($config['api_key'] ?? '');

        if ($apiKey === '') {
            return [
                'success' => false,
                'code' => 'AI_NOT_CONFIGURED',
                'message' => 'Trợ lý AI chưa được cấu hình API Key trên máy chủ TechPilot.'
            ];
        }

        $systemInstruction = self::buildSystemInstruction($contextData);
        $fullPrompt = $systemInstruction . "\n\n" . $prompt;

        return self::sendApiRequest($apiKey, $fullPrompt, $config['model'] ?? 'gemini-1.5-flash');
    }

    /**
     * Xây dựng System Instruction bắt buộc AI tuân thủ nguyên tắc Grounding trung thực.
     */
    private static function buildSystemInstruction(?array $contextData): string
    {
        $instruction = "Bạn là Trợ lý AI tư vấn chuyên nghiệp của Siêu thị Công nghệ TechPilot.\n";
        $instruction .= "NGUYÊN TẮC BẮT BUỘC:\n";
        $instruction .= "1. Phân biệt rõ dữ kiện thực tế được cung cấp từ Database TechPilot với kiến thức kỹ thuật chung.\n";
        $instruction .= "2. Nếu thông tin sản phẩm (giá, tồn kho, cổng kết nối, bảo hành) KHÔNG CÓ trong ngữ cảnh cung cấp, hãy trả lời rõ: \"Thông tin này hiện chưa được cập nhật trong hệ thống TechPilot.\"\n";
        $instruction .= "3. Tuyệt đối KHÔNG tự sáng chế giá ảo, cấu hình ảo, tồn kho ảo hoặc bảo hành giả lập.\n";
        $instruction .= "4. Trả lời bằng tiếng Việt lịch sự, thân thiện, rõ ràng có định dạng Markdown.\n\n";

        if (!empty($contextData)) {
            $instruction .= "=== DỮ LIỆU THỰC TẾ TỪ DATABASE TECHPILOT ===\n";
            $instruction .= json_encode($contextData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
            $instruction .= "=============================================\n";
        }

        return $instruction;
    }

    /**
     * Gửi HTTP POST request thực tế tới Google Gemini API với SSL Verification.
     */
    private static function sendApiRequest(string $apiKey, string $fullPrompt, string $model = 'gemini-1.5-flash'): array
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($model) . ':generateContent?key=' . $apiKey;

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $fullPrompt]
                    ]
                ]
            ]
        ];

        $jsonPayload = json_encode($payload);

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
        } else {
            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/json\r\n",
                    'content' => $jsonPayload,
                    'timeout' => 15,
                    'ignore_errors' => true
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true
                ]
            ];
            $context  = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);
            $httpCode = 200;
            $headers = function_exists('http_get_last_response_headers') ? http_get_last_response_headers() : ($http_response_header ?? []);
            if (is_array($headers) && isset($headers[0])) {
                if (preg_match('#HTTP/\d\.\d\s+(\d+)#', $headers[0], $m)) {
                    $httpCode = (int)$m[1];
                }
            }
            $curlError = 'stream_context error';
        }

        if ($response === false) {
            return [
                'success' => false,
                'code' => 'CONNECTION_ERROR',
                'message' => 'Không thể kết nối tới máy chủ Gemini AI: ' . $curlError
            ];
        }

        if ($httpCode !== 200) {
            $errData = json_decode($response, true);
            $errMessage = $errData['error']['message'] ?? 'Lỗi không xác định từ máy chủ API.';
            return [
                'success' => false,
                'code' => 'API_HTTP_' . $httpCode,
                'message' => "Lỗi API Gemini ({$httpCode}): {$errMessage}"
            ];
        }

        $data = json_decode($response, true);
        $textResult = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (trim($textResult) === '') {
            return [
                'success' => false,
                'code' => 'EMPTY_RESPONSE',
                'message' => 'Không nhận được phản hồi hợp lệ từ Gemini AI.'
            ];
        }

        return [
            'success' => true,
            'answer' => $textResult
        ];
    }
}
