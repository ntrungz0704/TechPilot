<?php
/**
 * Cấu hình kết nối Gemini AI API
 */

return [
    'api_key'  => trim((string)(getenv('GEMINI_API_KEY') ?: '')),
    'model'    => trim((string)(getenv('GEMINI_MODEL') ?: 'gemini-3.6-flash')),
    'api_base' => rtrim(
        trim((string)(getenv('GEMINI_API_BASE')
            ?: 'https://generativelanguage.googleapis.com/v1beta')),
        '/'
    ),
    'timeout'  => max(
        5,
        min(60, (int)(getenv('GEMINI_TIMEOUT_SECONDS') ?: 20))
    ),
];
