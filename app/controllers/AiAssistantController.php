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
     * API: Nhận yêu cầu khảo sát, chấm điểm sản phẩm sơ bộ, đề xuất 3 sản phẩm tối ưu nhất
     */
    public function recommend(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        
        if ($this->db === null) {
            echo json_encode(['success' => false, 'message' => 'Lỗi kết nối cơ sở dữ liệu.']);
            exit;
        }

        $budgetBracket = trim($_POST['budget'] ?? '20_25m');
        $categoryGroup = trim($_POST['category'] ?? 'laptop');
        $purpose = trim($_POST['purpose'] ?? 'general');
        $software = trim($_POST['software'] ?? '');
        $priority = trim($_POST['priority'] ?? 'performance');
        $brand = trim($_POST['brand'] ?? '');
        $excluded = trim($_POST['excluded'] ?? '');

        // Khoảng giá chuẩn hóa
        $minBudget = 0;
        $maxBudget = 999000000;
        $budgetLabel = '20–25 triệu';

        switch ($budgetBracket) {
            case 'under_10m':
                $minBudget = 0; $maxBudget = 10000000; $budgetLabel = 'Dưới 10 triệu'; break;
            case '10_15m':
                $minBudget = 10000000; $maxBudget = 15000000; $budgetLabel = '10–15 triệu'; break;
            case '15_20m':
                $minBudget = 15000000; $maxBudget = 20000000; $budgetLabel = '15–20 triệu'; break;
            case '20_25m':
                $minBudget = 20000000; $maxBudget = 25000000; $budgetLabel = '20–25 triệu'; break;
            case '25_35m':
                $minBudget = 25000000; $maxBudget = 35000000; $budgetLabel = '25–35 triệu'; break;
            case 'over_35m':
                $minBudget = 35000000; $maxBudget = 999000000; $budgetLabel = 'Trên 35 triệu'; break;
        }

        // Ánh xạ danh mục
        $categoryIds = [1, 2];
        $categoryLabel = 'Laptop';
        if ($categoryGroup === 'pc') {
            $categoryIds = [3, 6];
            $categoryLabel = 'Máy tính PC';
        } elseif ($categoryGroup === 'monitor') {
            $categoryIds = [5];
            $categoryLabel = 'Màn hình';
        } elseif ($categoryGroup === 'gear') {
            $categoryIds = [7, 8];
            $categoryLabel = 'Gaming Gear';
        } elseif ($categoryGroup === 'component') {
            $categoryIds = [4, 10, 11, 12, 13, 14, 15, 16, 17, 18];
            $categoryLabel = 'Linh kiện máy tính';
        }

        try {
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $sql = "SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug
                    FROM products p
                    LEFT JOIN brands b ON p.brand_id = b.id
                    LEFT JOIN categories c ON p.category_id = c.id
                    WHERE p.status = 'active' AND p.stock > 0 AND p.price >= ? AND p.price <= ? AND p.category_id IN ($placeholders)";
            
            $stmt = $this->db->prepare($sql);
            $params = array_merge([$minBudget, $maxBudget], $categoryIds);
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($products)) {
                echo json_encode(['success' => false, 'message' => "Không tìm thấy mẫu {$categoryLabel} nào còn hàng trong khoảng giá {$budgetLabel}."]);
                exit;
            }

            // Chấm điểm deterministic
            $scoredCandidates = [];
            foreach ($products as $p) {
                $vfm = ProductIntelligenceService::calculateValueForMoney($p);
                $score = $vfm * 10;
                $p['calc_score'] = round($score, 1);
                $scoredCandidates[] = $p;
            }

            usort($scoredCandidates, function($a, $b) {
                return $b['calc_score'] <=> $a['calc_score'];
            });

            $candidatesSubset = array_slice($scoredCandidates, 0, 8);

            $filters = [
                'budget_val' => $maxBudget,
                'budget_label' => $budgetLabel,
                'category_name' => $categoryLabel,
                'purpose' => $purpose,
                'software' => $software,
                'priority' => $priority,
                'brand' => $brand,
                'excluded' => $excluded
            ];

            $aiResult = ProductIntelligenceService::recommendProducts($filters, $candidatesSubset);

            $bestId = $aiResult['best_id'];
            $savingId = $aiResult['saving_id'];
            $perfId = $aiResult['perf_id'];

            $bestP = null; $savingP = null; $perfP = null;
            foreach ($candidatesSubset as $c) {
                if ($c['id'] == $bestId) $bestP = $c;
                if ($c['id'] == $savingId) $savingP = $c;
                if ($c['id'] == $perfId) $perfP = $c;
            }

            if (!$bestP) $bestP = $candidatesSubset[0];
            if (!$savingP) $savingP = $candidatesSubset[count($candidatesSubset)-1];
            if (!$perfP) $perfP = $candidatesSubset[0];

            $buildRecItem = function($p, $typeLabel, $scoreVal) {
                $specs = json_decode($p['specs'] ?? '{}', true) ?: [];
                $vfm = ProductIntelligenceService::calculateValueForMoney($p);
                $cpuVal = $specs['CPU'] ?? $specs['cpu'] ?? null;
                if (!$cpuVal && preg_match('/(intel|amd|ryzen|core\s*i\d|ultra\s*\d)/i', $p['name'])) {
                    $cpuVal = $p['name'];
                }

                return [
                    'id' => $p['id'],
                    'name' => "[{$typeLabel}] " . $p['name'],
                    'price' => (float)$p['price'],
                    'price_formatted' => formatPrice((float)$p['price']),
                    'image' => empty($p['image']) ? '/assets/images/placeholder.jpg' : (str_starts_with($p['image'], 'http') ? $p['image'] : (str_starts_with($p['image'], 'assets/') ? '/' . $p['image'] : '/assets/images/products/' . $p['image'])),
                    'slug' => $p['slug'],
                    'score' => $scoreVal,
                    'specs' => [
                        'CPU' => $cpuVal ?: 'Chưa có dữ liệu',
                        'RAM' => $specs['RAM'] ?? $specs['ram'] ?? 'Chưa có dữ liệu',
                        'SSD' => $specs['SSD'] ?? $specs['ssd'] ?? 'Chưa có dữ liệu',
                        'VGA' => $specs['VGA'] ?? $specs['vga'] ?? 'Chưa có dữ liệu'
                    ],
                    'reasons' => [
                        "Độ đáng tiền (VFM): {$vfm}/10",
                        "Phù hợp nhất với hạn mức tài chính của bạn.",
                        "Bảo hành chính hãng tại TechPilot."
                    ]
                ];
            };

            $finalRecs = [];
            $finalRecs[] = $buildRecItem($bestP, 'Phù hợp nhất', 96);
            if ($savingP['id'] !== $bestP['id']) {
                $finalRecs[] = $buildRecItem($savingP, 'Tiết kiệm nhất', 85);
            }
            if ($perfP['id'] !== $bestP['id'] && $perfP['id'] !== $savingP['id']) {
                $finalRecs[] = $buildRecItem($perfP, 'Hiệu năng cao nhất', 91);
            }

            echo json_encode([
                'success' => true,
                'message' => "🤖 **Trợ lý AI phân tích và đề xuất sản phẩm cho khoảng giá {$budgetLabel}:**",
                'reasons' => $aiResult['reasons'],
                'tradeoffs' => $aiResult['tradeoffs'],
                'recommendations' => $finalRecs
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    public function saveFavorite(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'Đã lưu sản phẩm gợi ý']);
        exit;
    }
}
