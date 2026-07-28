<?php

/**
 * ChatIntentClassifier - Classifies user message intent into structured categories.
 */
class ChatIntentClassifier
{
    public const INTENT_PRODUCT_QUESTION = 'product_question';
    public const INTENT_PRODUCT_COMPARISON = 'product_comparison';
    public const INTENT_PRODUCT_RECOMMENDATION = 'product_recommendation';
    public const INTENT_COMPATIBILITY_QUESTION = 'compatibility_question';
    public const INTENT_GENERAL_TECHNOLOGY = 'general_technology';
    public const INTENT_GENERAL_QUESTION = 'general_question';

    /**
     * Phân loại ý định từ nội dung tin nhắn và ngữ cảnh trang.
     */
    public static function classify(string $message, ?int $productId = null, string $pageContext = ''): string
    {
        $msg = mb_strtolower(trim($message), 'UTF-8');

        // 1. So sánh sản phẩm
        if (preg_match('/so sánh|khác gì|khác nhau|nên chọn ai|so với/u', $msg)) {
            return self::INTENT_PRODUCT_COMPARISON;
        }

        // 2. Tư vấn / Đề xuất mua máy
        if (preg_match('/tư vấn|gợi ý|nên mua|ngân sách|triệu|tầm giá|mua máy nào/u', $msg)) {
            return self::INTENT_PRODUCT_RECOMMENDATION;
        }

        // 3. Tương thích linh kiện
        if (preg_match('/tương thích|lắp vừa|gắn được|chạy chung|kết hợp/u', $msg)) {
            return self::INTENT_COMPATIBILITY_QUESTION;
        }

        // 4. Kiến thức công nghệ tổng quát (không cần dữ liệu kho hàng)
        if (preg_match('/ddr4.*ddr5|là gì|nguyên lý|tại sao|khác nhau thế nào giữa|cách vệ sinh|hoạt động thế nào/u', $msg) && $productId === null) {
            return self::INTENT_GENERAL_TECHNOLOGY;
        }

        // 5. Câu hỏi về sản phẩm đang xem
        if ($productId !== null || preg_match('/máy này|sản phẩm|game|fps|ram|ssd|vga|bảo hành|pin|có sẵn|còn hàng/u', $msg)) {
            return self::INTENT_PRODUCT_QUESTION;
        }

        return self::INTENT_GENERAL_QUESTION;
    }
}
