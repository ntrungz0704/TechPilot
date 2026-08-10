<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/services/ChatIntentClassifier.php';
require_once ROOT_PATH . '/app/services/GeminiService.php';

class ChatbotController extends Controller
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =========================================================================
    // ===== Chức năng Hỏi đáp Floating AI Chatbot nổi (UC14) =====
    // =========================================================================
    /**
     * API: Nhận câu hỏi tự nhiên hoặc tương tác với Chatbot Nổi
     */
    public function query(): void
    {
        ini_set('display_errors', '0');
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        if ($this->db === null) {
            echo json_encode([
                'success' => false,
                'type' => 'error',
                'error' => [
                    'code' => 'DATABASE_ERROR',
                    'message' => 'Lỗi kết nối cơ sở dữ liệu.'
                ]
            ]);
            exit;
        }

        $queryText = trim($_GET['q'] ?? '');

        // Validate query rỗng -> HTTP 422
        if ($queryText === '') {
            http_response_code(422);
            echo json_encode([
                'success' => false,
                'type' => 'error',
                'error' => [
                    'code' => 'EMPTY_MESSAGE',
                    'message' => 'Vui lòng nhập nội dung cần hỏi.'
                ]
            ]);
            exit;
        }

        // 1. Kiểm tra Rate limit
        $rateCheck = $this->checkAndIncrementRateLimit();
        if (!$rateCheck['allowed']) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'type' => 'error',
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => $rateCheck['message']
                ]
            ]);
            exit;
        }

        // 2. Simple Math Intent (1 + 1, 500 * 2)
        $mathResult = $this->evaluateSimpleMath($queryText);
        if ($mathResult !== null) {
            echo json_encode([
                'success' => true,
                'type' => 'text',
                'message' => $mathResult,
                'action' => null
            ]);
            exit;
        }

        // Session Context
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['chatbot_context']) || str_contains(mb_strtolower($queryText), 'reset') || str_contains(mb_strtolower($queryText), 'tu dau')) {
            $_SESSION['chatbot_context'] = [
                'last_intent' => null,
                'current_product_id' => null,
                'pending_confirmation' => null,
                'last_user_message' => null
            ];
        }

        $productId = (int)($_GET['product_id'] ?? 0);
        $intent = ChatIntentClassifier::classify($queryText, $productId > 0 ? $productId : null);
        $_SESSION['chatbot_context']['last_intent'] = $intent;
        $_SESSION['chatbot_context']['last_user_message'] = $queryText;

        // 3. Phân nhánh Intent RECOMMENDATION_REQUEST -> Điều hướng sang /ai-assistant
        $msgLower = mb_strtolower($queryText);
        $shortConfirmations = ['vâng', 'đúng', 'ok', 'oke', 'rồi', 'được', 'yes', '20'];
        if (in_array($msgLower, $shortConfirmations, true) && empty($_SESSION['chatbot_context']['pending_confirmation'])) {
            $clarifyMsg = ($msgLower === '20')
                ? "Bạn đang muốn tìm linh kiện/máy tính có mức giá 20 triệu hay số lượng 20 ạ? Bạn có thể nói rõ hơn một chút nhé."
                : "Mình chưa rõ bạn đang xác nhận nội dung nào. Bạn có thể nói rõ hơn một chút câu hỏi của bạn nhé!";

            echo json_encode([
                'success' => true,
                'type' => 'text',
                'message' => $clarifyMsg,
                'action' => null
            ]);
            exit;
        }

        // 6. Xử lý câu hỏi về Sản phẩm đang xem
        if ($productId > 0 || $intent === ChatIntentClassifier::INTENT_PRODUCT_QUESTION) {
            $prod = null;
            if ($productId > 0) {
                $flashSql = activeFlashPriceSql('p');
                $stmt = $this->db->prepare(
                    "SELECT p.*, {$flashSql} AS discount_price, c.name as category_name, c.slug as category_slug, b.name as brand_name
                     FROM products p
                     LEFT JOIN categories c ON p.category_id = c.id
                     LEFT JOIN brands b ON p.brand_id = b.id
                     WHERE p.id = ? AND p.status = 'active'"
                );
                $stmt->execute([$productId]);
                $prod = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if ($prod) {
                // Tính giá thực tế (flash sale / khuyến mãi)
                $eff = getEffectiveProductData($prod);
                $prod['final_price']    = $eff['final_price'];
                $prod['original_price'] = $eff['original_price'];
                $prod['has_discount']   = $eff['has_discount'];
                $prod['discount_pct']   = $eff['discount_pct'];
                $prod['is_flash_sale']  = $eff['is_flash_sale'];

                // Dùng chung chatProduct để đảm bảo nhất quán
                require_once ROOT_PATH . '/app/services/ProductIntelligenceService.php';
                $answerText = ProductIntelligenceService::chatProduct($prod, $queryText);

                echo json_encode([
                    'success' => true,
                    'type' => 'text',
                    'message' => trim($answerText),
                    'action' => null
                ]);
                exit;
            }
        }

        // 7. Hỏi đáp FAQ & Chính sách cửa hàng
        $faqResponse = $this->handleStoreFaq($queryText);
        if ($faqResponse !== null) {
            echo json_encode([
                'success' => true,
                'type' => 'text',
                'message' => $faqResponse,
                'action' => null
            ]);
            exit;
        }

        // 8. General AI Q&A via GeminiService
        try {
            $generalPrompt = "Bạn là trợ lý ảo TechPilot AI. Khách hàng đang trò chuyện với bạn:\n\"$queryText\"\n\nHãy trả lời một cách tự nhiên, lịch sự, tư vấn công nghệ máy tính chuẩn xác và thân thiện. Trả lời bằng tiếng Việt ngắn gọn, KHÔNG dùng các ký tự markdown dấu hoa thị *, ** trong câu trả lời.";
            $answer = GeminiService::callGemini($generalPrompt);

            echo json_encode([
                'success' => true,
                'type' => 'text',
                'message' => trim($answer),
                'action' => null
            ]);
        } catch (Exception $e) {
            http_response_code(502);
            echo json_encode([
                'success' => false,
                'type' => 'error',
                'error' => [
                    'code' => 'GEMINI_ERROR',
                    'message' => 'Trợ lý AI đang tạm thời không khả dụng: ' . $e->getMessage()
                ]
            ]);
        }
        exit;
    }

    /**
     * Đồng bộ hành vi / vị trí trang hiện tại
     */
    public function sync(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Đồng bộ thành công.']);
        exit;
    }

    private function handleStoreFaq(string $q): ?string
    {
        $qLower = mb_strtolower($q);

        if (str_contains($qLower, 'địa chỉ') || str_contains($qLower, 'cửa hàng ở đâu') || str_contains($qLower, 'nằm ở đâu')) {
            return "📍 **Địa chỉ cửa hàng TechPilot:**\n\nShowroom chính: **123 Đường Công Nghệ, Quận Cầu Giấy, TP. Hà Nội**.\nHotline hỗ trợ: **1900 8888** (8:00 - 21:30 hàng ngày).";
        }

        if (str_contains($qLower, 'giờ mở cửa') || str_contains($qLower, 'giờ hoạt động') || str_contains($qLower, 'mấy giờ')) {
            return "⏰ **Thời gian hoạt động cửa hàng TechPilot:**\n\n• Thứ 2 - Chủ Nhật: **8:00 đến 21:30**.\n• Tổng đài hỗ trợ online 24/7 qua Website & Fanpage.";
        }

        if (str_contains($qLower, 'bảo hành') || str_contains($qLower, 'chính sách bảo hành')) {
            return "🛡️ **Chính sách bảo hành tại TechPilot:**\n\n• **Bảo hành 1 đổi 1** trong 30 ngày đầu nếu lỗi nhà sản xuất.\n• Linh kiện mới bảo hành chính hãng 24 - 36 tháng.\n• Hỗ trợ vệ sinh, mỡ tản nhiệt trọn đời máy.";
        }

        if (str_contains($qLower, 'trả góp')) {
            return "💳 **Chính sách trả góp 0% tại TechPilot:**\n\n• Hỗ trợ trả góp 0% qua thẻ tín dụng hơn 25 ngân hàng.\n• Duyệt hồ sơ nhanh qua CCCD/GPLX chỉ 15 phút.";
        }

        if (str_contains($qLower, 'thu cũ') || str_contains($qLower, 'đổi mới') || str_contains($qLower, 'trade in')) {
            return "🔄 **Chương trình Thu cũ Đổi mới (Trade-in):**\n\nTechPilot hỗ trợ thu mua lại máy cũ trợ giá lên đời lên tới 2.000.000đ. Bạn hãy ghé trang [Thu cũ đổi mới](" . url('thu-cu-doi-moi') . ") để tra cứu thử nhé!";
        }

        return null;
    }

    private function evaluateSimpleMath(string $q): ?string
    {
        if (preg_match('/^\s*(\d+(\.\d+)?)\s*([\+\-\*\/])\s*(\d+(\.\d+)?)\s*$/', $q, $m)) {
            $n1 = (float)$m[1];
            $op = $m[3];
            $n2 = (float)$m[4];
            $res = 0;
            switch ($op) {
                case '+': $res = $n1 + $n2; break;
                case '-': $res = $n1 - $n2; break;
                case '*': $res = $n1 * $n2; break;
                case '/': $res = ($n2 != 0) ? $n1 / $n2 : null; break;
            }
            if ($res === null) return "🤖 Không thể chia cho 0.";
            return "🤖 Kết quả tính toán: **{$q} = " . (round($res, 4)) . "**";
        }
        return null;
    }

    private function checkAndIncrementRateLimit(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $isLoggedIn = !empty($_SESSION['user']);
        $maxPerDay = $isLoggedIn ? 50 : 20;

        $today = date('Y-m-d');
        if (!isset($_SESSION['ai_rate_limit']) || $_SESSION['ai_rate_limit']['date'] !== $today) {
            $_SESSION['ai_rate_limit'] = [
                'date' => $today,
                'count' => 0
            ];
        }

        if ($_SESSION['ai_rate_limit']['count'] >= $maxPerDay) {
            return [
                'allowed' => false,
                'message' => "Bạn đã sử dụng hết lượt hỏi AI trong ngày hôm nay ({$maxPerDay} lượt). Vui lòng quay lại vào ngày mai nhé!"
            ];
        }

        $_SESSION['ai_rate_limit']['count']++;
        return ['allowed' => true, 'message' => ''];
    }
}
