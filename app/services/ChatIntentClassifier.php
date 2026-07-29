<?php

/**
 * ChatIntentClassifier - Phân loại ý định tin nhắn người dùng vào các nhóm chức năng chuẩn hóa.
 */
class ChatIntentClassifier
{
    public const INTENT_GREETING = 'GREETING';
    public const INTENT_STORE_FAQ = 'STORE_FAQ';
    public const INTENT_PRODUCT_QUESTION = 'PRODUCT_QUESTION';
    public const INTENT_NAVIGATION = 'NAVIGATION';
    public const INTENT_RECOMMENDATION_REQUEST = 'RECOMMENDATION_REQUEST';
    public const INTENT_COMPARISON_REQUEST = 'COMPARISON_REQUEST';
    public const INTENT_SMALL_TALK = 'SMALL_TALK';
    public const INTENT_UNKNOWN = 'UNKNOWN';

    /**
     * Phân loại ý định từ nội dung tin nhắn và ngữ cảnh sản phẩm.
     */
    public static function classify(string $message, ?int $productId = null, string $pageContext = ''): string
    {
        $msg = mb_strtolower(trim($message), 'UTF-8');

        // 1. Chào hỏi
        if (preg_match('/^(xin chào|chào|hi|hello|lô|lô fen|alo|chào bot|chào em|chào bạn)\b/u', $msg)) {
            return self::INTENT_GREETING;
        }

        // 2. Ý định so sánh sản phẩm (Comparison Request)
        if (preg_match('/so sánh|đối chiếu|máy nào tốt hơn|khác gì|khác nhau giữa/u', $msg)) {
            return self::INTENT_COMPARISON_REQUEST;
        }

        // 3. Ý định tư vấn / đề xuất chọn máy (Recommendation Request)
        if (preg_match('/tư vấn|gợi ý|nên mua|chọn máy|máy cho lập trình|máy cho sinh viên|máy cho đồ họa|chọn pc|tư vấn laptop/u', $msg)) {
            return self::INTENT_RECOMMENDATION_REQUEST;
        }

        // 4. Chính sách cửa hàng & địa chỉ FAQ (Store FAQ)
        if (preg_match('/địa chỉ|cửa hàng|giờ mở cửa|giờ hoạt động|bảo hành|đổi trả|trả góp|vận chuyển|giao hàng|thanh toán|thu cũ|đổi mới/u', $msg)) {
            return self::INTENT_STORE_FAQ;
        }

        // 5. Câu hỏi trực tiếp về sản phẩm đang xem hoặc sản phẩm cụ thể
        if ($productId !== null || preg_match('/máy này|sản phẩm này|game|fps|ram|ssd|vga|pin|còn hàng|giá bao nhiêu/u', $msg)) {
            return self::INTENT_PRODUCT_QUESTION;
        }

        // 6. Điều hướng
        if (preg_match('/trang chủ|giỏ hàng|đơn hàng|liên hệ|danh mục/u', $msg)) {
            return self::INTENT_NAVIGATION;
        }

        return self::INTENT_SMALL_TALK;
    }
}
