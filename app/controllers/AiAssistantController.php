<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/services/GeminiService.php';
require_once ROOT_PATH . '/app/services/ProductIntelligenceService.php';

class AiAssistantController extends Controller
{
    private ?PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Hiển thị trang khảo sát và Trợ lý AI
     */
    public function index(): void
    {
        $this->render('ai-assistant/index', [
            'pageTitle' => 'Trợ lý ảo tư vấn mua sắm AI',
            'csrf_token' => $_SESSION['csrf_token'] ?? ''
        ]);
    }

    /**
     * API: Nhận yêu cầu khảo sát, chấm điểm sản phẩm sơ bộ, gọi Gemini để đề xuất 3 sản phẩm tối ưu nhất
     */
    public function recommend(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($this->db === null) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            exit;
        }

        // Lấy thông tin từ request POST
        $budgetBracket = trim($_POST['budget'] ?? 'over_25m');
        $categoryGroup = trim($_POST['category'] ?? 'laptop');
        $purpose = trim($_POST['purpose'] ?? 'general');
        $software = trim($_POST['software'] ?? '');
        $priority = trim($_POST['priority'] ?? 'performance');
        $brand = trim($_POST['brand'] ?? '');
        $excluded = trim($_POST['excluded'] ?? '');

        // Phân tích ngân sách tối đa
        $maxBudget = 999000000;
        if ($budgetBracket === 'under_10m') $maxBudget = 10000000;
        elseif ($budgetBracket === '10_15m') $maxBudget = 15000000;
        elseif ($budgetBracket === '15_25m') $maxBudget = 25000000;
        elseif ($budgetBracket === '25_35m') $maxBudget = 35000000;

        // Ánh xạ nhóm danh mục sang ID thực tế trong database
        $categoryIds = [];
        $categoryLabel = 'Sản phẩm';
        if ($categoryGroup === 'laptop') {
            $categoryIds = [1, 2];
            $categoryLabel = 'Laptop';
        } elseif ($categoryGroup === 'pc') {
            $categoryIds = [3, 6];
            $categoryLabel = 'Máy tính PC';
        } elseif ($categoryGroup === 'monitor') {
            $categoryIds = [5];
            $categoryLabel = 'Màn hình';
        } elseif ($categoryGroup === 'gear') {
            $categoryIds = [7, 8];
            $categoryLabel = 'Thiết bị phụ kiện/Gear';
        } elseif ($categoryGroup === 'component') {
            $categoryIds = [4, 10, 11, 12, 13, 14, 15, 16, 17, 18];
            $categoryLabel = 'Linh kiện máy tính';
        } else {
            $categoryIds = [1, 2]; // mặc định Laptop
            $categoryLabel = 'Laptop';
        }

        try {
            // 1. Query các sản phẩm đang hoạt động, còn hàng và nằm trong khoảng giá
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $sql = "SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug
                    FROM products p
                    LEFT JOIN brands b ON p.brand_id = b.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'active' AND p.stock > 0 AND p.price <= ? AND p.category_id IN ($placeholders)";
            
            $stmt = $this->db->prepare($sql);
            $params = array_merge([$maxBudget], $categoryIds);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($products)) {
                echo json_encode(['success' => false, 'message' => "Không tìm thấy mẫu {$categoryLabel} nào còn hàng dưới mức giá đề xuất của bạn."]);
                exit;
            }

            // 2. Chấm điểm sơ bộ (Heuristic scoring) sản phẩm dựa trên các tùy chọn của khách hàng
            $scoredCandidates = [];
            foreach ($products as $p) {
                $score = 50.0;
                $specs = json_decode($p['specs'] ?? '{}', true) ?: [];
                $cpu = strtolower($specs['CPU'] ?? $specs['cpu'] ?? '');
                $ram = (int)filter_var($specs['RAM'] ?? $specs['ram'] ?? '8', FILTER_SANITIZE_NUMBER_INT);
                $vga = strtolower($specs['VGA'] ?? $specs['vga'] ?? '');
                $isGamingVga = (strpos($vga, 'rtx') !== false || strpos($vga, 'gtx') !== false || strpos($vga, 'radeon rx') !== false);

                // Chấm theo nhu cầu
                if ($purpose === 'gaming') {
                    if ($p['category_id'] == 1) $score += 20; // Laptop Gaming
                    if ($isGamingVga) $score += 20;
                    if ($ram >= 16) $score += 10;
                } elseif ($purpose === 'design' || $purpose === 'graphic') {
                    if ($ram >= 16) $score += 20;
                    if ($isGamingVga) $score += 15;
                    if (strpos($cpu, 'i7') !== false || strpos($cpu, 'ryzen 7') !== false) $score += 10;
                } elseif ($purpose === 'coding') {
                    if ($ram >= 16) $score += 25;
                    if (strpos($cpu, 'i5') !== false || strpos($cpu, 'ryzen 5') !== false) $score += 10;
                    if (strpos($cpu, 'i7') !== false || strpos($cpu, 'ryzen 7') !== false) $score += 15;
                } elseif ($purpose === 'office') {
                    if ($p['category_id'] == 2) $score += 20; // Laptop văn phòng
                    if (!$isGamingVga) $score += 10; // Không cần card rời tốn pin
                    if ($price = (float)$p['price'] < 18000000) $score += 10;
                }

                // Chấm theo độ ưu tiên
                if ($priority === 'performance') {
                    if (strpos($cpu, 'i7') !== false || strpos($cpu, 'ryzen 7') !== false) $score += 15;
                    if ($ram >= 16) $score += 10;
                } elseif ($priority === 'lightweight') {
                    if ($p['category_id'] == 2) $score += 15;
                    if (strpos(strtolower($p['name']), 'ultra') !== false || strpos(strtolower($p['name']), 'thin') !== false || strpos(strtolower($p['name']), 'macbook') !== false) {
                        $score += 20;
                    }
                } elseif ($priority === 'battery') {
                    if ($p['category_id'] == 2 && !$isGamingVga) $score += 20;
                } elseif ($priority === 'upgrade') {
                    if ($p['category_id'] == 3 || $p['category_id'] == 6) $score += 20; // PC dễ nâng cấp hơn
                }

                // Ưu tiên thương hiệu
                if ($brand !== '' && strpos(strtolower($p['brand_name'] ?? ''), strtolower($brand)) !== false) {
                    $score += 25;
                }

                // Loại trừ từ khóa không mong muốn
                if ($excluded !== '') {
                    $excludeTerms = explode(',', strtolower($excluded));
                    foreach ($excludeTerms as $term) {
                        $term = trim($term);
                        if ($term !== '' && (strpos(strtolower($p['name']), $term) !== false || strpos($cpu, $term) !== false)) {
                            $score -= 150; // Loại hẳn
                        }
                    }
                }

                $p['calc_score'] = $score;
                $scoredCandidates[] = $p;
            }

            // Sắp xếp giảm dần theo điểm
            usort($scoredCandidates, function ($a, $b) {
                return $b['calc_score'] <=> $a['calc_score'];
            });

            // Chỉ lấy top 8 sản phẩm điểm cao nhất để gửi cho AI
            $candidatesSubset = array_slice($scoredCandidates, 0, 8);

            // 3. Gửi danh sách cho AI thông qua ProductIntelligenceService
            $filters = [
                'budget_val' => $maxBudget,
                'category_name' => $categoryLabel,
                'purpose' => $purpose,
                'software' => $software,
                'priority' => $priority,
                'brand' => $brand,
                'excluded' => $excluded
            ];

            $aiResult = ProductIntelligenceService::recommendProducts($filters, $candidatesSubset);

            // 4. Lấy thông tin thật từ database cho 3 ID được đề xuất
            $bestProduct = null;
            $savingProduct = null;
            $perfProduct = null;

            foreach ($candidatesSubset as $c) {
                if ($c['id'] == $aiResult['best_id']) $bestProduct = $c;
                if ($c['id'] == $aiResult['saving_id']) $savingProduct = $c;
                if ($c['id'] == $aiResult['perf_id']) $perfProduct = $c;
            }

            // Fallback nếu AI trả về ID không khớp (phòng lỗi LLM)
            if (!$bestProduct) $bestProduct = $candidatesSubset[0];
            if (!$savingProduct) $savingProduct = $candidatesSubset[count($candidatesSubset) - 1];
            if (!$perfProduct) {
                // Lấy sản phẩm có giá cao nhất trong các ứng viên làm hiệu năng
                $perfProduct = $candidatesSubset[0];
                foreach ($candidatesSubset as $c) {
                    if ($c['price'] > $perfProduct['price']) $perfProduct = $c;
                }
            }

            // Helper để format sản phẩm phản hồi kèm điểm số bổ sung
            $formatProductData = function(array $p, string $recType, int $suitabilityBase) {
                $specs = json_decode($p['specs'] ?? '{}', true) ?: [];
                $vfm = ProductIntelligenceService::calculateValueForMoney($p);
                $pp = ProductIntelligenceService::calculatePerformancePriceRatio($p);
                $fps = ProductIntelligenceService::estimateFps($specs, $p['category_slug'] ?? $p['category_name'] ?? '');

                // Tính điểm phù hợp (Suitability Score) ngẫu nhiên dao động theo loại đề xuất
                $suitability = $suitabilityBase;
                if ($recType === 'best') $suitability = rand(94, 98);
                elseif ($recType === 'perf') $suitability = rand(88, 93);
                elseif ($recType === 'saving') $suitability = rand(80, 87);

                return [
                    'id' => $p['id'],
                    'name' => $p['name'],
                    'price' => (float)$p['price'],
                    'price_formatted' => number_format($p['price'], 0, ',', '.') . 'đ',
                    'image' => $p['image'],
                    'slug' => $p['slug'],
                    'specs' => [
                        'CPU' => $specs['CPU'] ?? $specs['cpu'] ?? 'N/A',
                        'RAM' => $specs['RAM'] ?? $specs['ram'] ?? '8GB',
                        'SSD' => $specs['SSD'] ?? $specs['ssd'] ?? ($specs['Ổ cứng'] ?? '512GB NVMe'),
                        'VGA' => $specs['VGA'] ?? $specs['vga'] ?? 'Intel HD Graphics'
                    ],
                    'vfm_score' => $vfm,
                    'pp_ratio' => $pp,
                    'fps_list' => $fps,
                    'suitability_score' => $suitability,
                    'category_slug' => $p['category_slug'] ?? $p['category_name'] ?? ''
                ];
            };

            echo json_encode([
                'success' => true,
                'best' => $formatProductData($bestProduct, 'best', 95),
                'saving' => $formatProductData($savingProduct, 'saving', 85),
                'perf' => $formatProductData($perfProduct, 'perf', 90),
                'reasons' => $aiResult['reasons'],
                'tradeoffs' => $aiResult['tradeoffs']
            ]);

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * API: Lưu các gợi ý AI yêu thích (Wishlist)
     */
    public function saveFavorite(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $productId = (int)($_POST['product_id'] ?? 0);
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không hợp lệ.']);
            exit;
        }

        if (!isset($_SESSION['ai_favorites'])) {
            $_SESSION['ai_favorites'] = [];
        }

        // Lưu vào session cho mọi khách
        if (!in_array($productId, $_SESSION['ai_favorites'])) {
            $_SESSION['ai_favorites'][] = $productId;
        }

        $user = currentUser();
        if ($user) {
            // Nếu đã đăng nhập, lưu thật vào bảng wishlists
            if ($this->db) {
                try {
                    $stmt = $this->db->prepare("INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?, ?)");
                    $stmt->execute([$user['id'], $productId]);
                    echo json_encode(['success' => true, 'message' => 'Đã lưu sản phẩm vào danh sách Yêu thích của bạn!']);
                } catch (Exception $e) {
                    echo json_encode(['success' => true, 'message' => 'Đã lưu tạm thời (Lỗi lưu database: ' . $e->getMessage() . ')']);
                }
            } else {
                echo json_encode(['success' => true, 'message' => 'Đã lưu tạm thời vào Danh sách yêu thích.']);
            }
        } else {
            echo json_encode(['success' => true, 'message' => 'Đã lưu vào danh sách Yêu thích tạm thời. Vui lòng đăng nhập để lưu trữ lâu dài!']);
        }
        exit;
    }
}
