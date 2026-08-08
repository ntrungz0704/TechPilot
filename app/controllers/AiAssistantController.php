<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/services/GeminiService.php';
require_once ROOT_PATH . '/app/services/ProductIntelligenceService.php';
require_once ROOT_PATH . '/app/services/AiRecommendationService.php';
require_once ROOT_PATH . '/app/models/Wishlist.php';

class AiAssistantController extends Controller
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // =========================================================================
    // ===== Chức năng Trợ lý AI tư vấn 5 bước động (Multi-Provider Failover) (UC13) =====
    // =========================================================================
    /**
     * Hiển thị trang khảo sát và Trợ lý AI
     */
    public function index(): void
    {
        $this->render('ai-assistant/index', [
            'pageTitle'  => 'Trợ lý ảo tư vấn mua sắm AI (TechPilot AI 4.0)',
            'csrf_token' => $_SESSION['csrf_token'] ?? ''
        ]);
    }

    /**
     * API: Nhận yêu cầu khảo sát, chấm điểm sản phẩm sơ bộ, đề xuất 3 sản phẩm tối ưu nhất
     */
    public function recommend(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($this->db === null) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error'   => [
                    'code'    => 'DATABASE_ERROR',
                    'message' => 'Lỗi kết nối cơ sở dữ liệu.'
                ]
            ]);
            exit;
        }

        // 1. XÁC THỰC CSRF TOKEN CHO REQUEST POST
        $csrfToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['_csrf'] ?? ($_POST['csrf_token'] ?? ''));
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (empty($csrfToken) || empty($sessionToken) || !hash_equals($sessionToken, $csrfToken)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'error'   => [
                    'code'    => 'CSRF_INVALID',
                    'message' => 'Xác thực CSRF không hợp lệ. Vui lòng làm mới trang và thử lại.'
                ]
            ]);
            exit;
        }

        // 2. NHẬN DỮ LIỆU KHẢO SÁT DỘNG
        $params = [
            'budget_code' => trim($_POST['budget_code'] ?? ($_POST['budget'] ?? '20_25m')),
            'category'    => trim($_POST['category'] ?? 'laptop'),
            'subcategory' => trim($_POST['subcategory'] ?? ''),
            'purpose'     => trim($_POST['purpose'] ?? ''),
            'priority'    => trim($_POST['priority'] ?? ''),
            'software'    => trim($_POST['software'] ?? ''),
            'brand'       => trim($_POST['brand'] ?? ''),
            'excluded'    => trim($_POST['excluded'] ?? '')
        ];

        // 3. XỬ LÝ KHẢO SÁT QUA SERVICE ĐỊNH HƯỚNG
        try {
            $result = AiRecommendationService::processRecommendation($params, $this->db);
            if (!$result['success']) {
                http_response_code(422);
            }
            echo json_encode($result);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error'   => [
                    'code'    => 'SERVER_ERROR',
                    'message' => 'Có lỗi xảy ra trong quá trình phân tích dữ liệu AI: ' . $e->getMessage()
                ]
            ]);
        }
        exit;
    }
    // =========================================================================
    // ===== Hoàn thành chức năng Trợ lý AI tư vấn 5 bước động (UC13) =====
    // =========================================================================

    public function saveFavorite(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['user']['id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'requireLogin' => true,
                'message' => 'Vui lòng đăng nhập để lưu sản phẩm yêu thích.'
            ]);
            exit;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false]);
            exit;
        }

        try {
            $stmt = $this->db->prepare("SELECT id FROM products WHERE id = :id AND status = 'active' LIMIT 1");
            $stmt->execute(['id' => $productId]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false]);
                exit;
            }

            $wishlist = new Wishlist();
            $wishlist->add($_SESSION['user']['id'], $productId);

            echo json_encode([
                'success' => true,
                'inWishlist' => true,
                'count' => $wishlist->count($_SESSION['user']['id']),
                'message' => 'Đã lưu sản phẩm vào danh sách yêu thích.'
            ]);
        } catch (Throwable $e) {
            http_response_code(503);
            echo json_encode(['success' => false]);
        }
        exit;
    }
}
