<?php
/**
 * Cấu hình kết nối AI đa nhà cung cấp (Multi-Provider AI Priority Failover)
 * Thứ tự ưu tiên mặc định: Gemini (Chính) -> Groq (Dự phòng 1) -> QwenCloud (Dự phòng 2)
 */

$orderStr = trim((string)(getenv('AI_PROVIDER_ORDER') ?: 'gemini,groq,qwen'));
$providerOrder = array_filter(array_map('trim', explode(',', strtolower($orderStr))));

return [
    'provider_order' => !empty($providerOrder) ? array_values($providerOrder) : ['gemini', 'groq', 'qwen'],
    'timeout'        => max(5, min(60, (int)(getenv('AI_TIMEOUT_SECONDS') ?: 20))),

    'providers' => [
        'gemini' => [
            'name'     => 'Google Gemini',
            'api_key'  => trim((string)(getenv('GEMINI_API_KEY') ?: '')),
            'model'    => trim((string)(getenv('GEMINI_MODEL') ?: 'gemini-3.6-flash')),
            'api_base' => rtrim(trim((string)(getenv('GEMINI_API_BASE') ?: 'https://generativelanguage.googleapis.com/v1beta')), '/'),
            'type'     => 'gemini_native',
        ],
        'groq' => [
            'name'     => 'Groq Cloud',
            'api_key'  => trim((string)(getenv('GROQ_API_KEY') ?: '')),
            'model'    => trim((string)(getenv('GROQ_MODEL') ?: 'openai/gpt-oss-20b')),
            'api_base' => rtrim(trim((string)(getenv('GROQ_API_BASE') ?: 'https://api.groq.com/openai/v1')), '/'),
            'type'     => 'openai_compatible',
        ],
        'qwen' => [
            'name'     => 'QwenCloud (DashScope)',
            'api_key'  => trim((string)(getenv('QWEN_API_KEY') ?: '')),
            'model'    => trim((string)(getenv('QWEN_MODEL') ?: 'qwen3.7-flash')),
            'api_base' => rtrim(trim((string)(getenv('QWEN_API_BASE') ?: 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1')), '/'),
            'type'     => 'openai_compatible',
        ],
    ],
];
