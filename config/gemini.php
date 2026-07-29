<?php
/**
 * Cấu hình kết nối Gemini AI API (Wrapper tương thích ngược với config/ai.php)
 */

$aiConfigPath = __DIR__ . '/ai.php';
if (file_exists($aiConfigPath)) {
    $aiConfig = require $aiConfigPath;
    $gemini = $aiConfig['providers']['gemini'] ?? [];
    return [
        'api_key'  => $gemini['api_key'] ?? '',
        'model'    => $gemini['model'] ?? 'gemini-3.6-flash',
        'api_base' => $gemini['api_base'] ?? 'https://generativelanguage.googleapis.com/v1beta',
        'timeout'  => $aiConfig['timeout'] ?? 20,
    ];
}

return [
    'api_key'  => trim((string)(getenv('GEMINI_API_KEY') ?: '')),
    'model'    => trim((string)(getenv('GEMINI_MODEL') ?: 'gemini-3.6-flash')),
    'api_base' => rtrim(trim((string)(getenv('GEMINI_API_BASE') ?: 'https://generativelanguage.googleapis.com/v1beta')), '/'),
    'timeout'  => max(5, min(60, (int)(getenv('GEMINI_TIMEOUT_SECONDS') ?: 20))),
];
