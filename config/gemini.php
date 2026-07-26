<?php
/**
 * Cấu hình kết nối Gemini AI API
 */

return [
    'api_key' => getenv('GEMINI_API_KEY') ?: ''
];
