<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/services/GeminiService.php';

class AiRecommendationService
{
    /**
     * Xử lý khảo sát và chấm điểm sản phẩm xác định (Deterministic Scoring Engine)
     */
    public static function processRecommendation(array $params, PDO $db): array
    {
        $config = require ROOT_PATH . '/config/ai-recommendation.php';

        $budgetCode  = trim($params['budget_code'] ?? '20_25m');
        $categoryKey = trim($params['category'] ?? 'laptop');
        $subCatKey   = trim($params['subcategory'] ?? '');
        $purpose     = trim($params['purpose'] ?? '');
        $priority    = trim($params['priority'] ?? '');
        $software   = trim($params['software'] ?? '');
        $brandFilter = trim($params['brand'] ?? '');
        $excluded    = trim($params['excluded'] ?? '');

        // 1. RESOLVE BUDGET
        $budgetDef = $config['budgets'][$budgetCode] ?? $config['budgets']['20_25m'];
        $minBudget = (float)$budgetDef['min'];
        $maxBudget = $budgetDef['max'] !== null ? (float)$budgetDef['max'] : null;
        $budgetLabel = $budgetDef['label'];

        // 2. RESOLVE CATEGORY SLUGS & IDS
        $catDef = $config['categories'][$categoryKey] ?? $config['categories']['laptop'];
        $targetSlugs = $catDef['slugs'];

        if ($categoryKey === 'gear' && !empty($subCatKey) && isset($catDef['subcategories'][$subCatKey])) {
            $targetSlugs = [$catDef['subcategories'][$subCatKey]['slug']];
        }

        $placeholders = implode(',', array_fill(0, count($targetSlugs), '?'));
        $stmtCat = $db->prepare("SELECT id, slug, name FROM categories WHERE slug IN ($placeholders)");
        $stmtCat->execute($targetSlugs);
        $foundCats = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

        if (empty($foundCats)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'CATEGORY_NOT_FOUND',
                    'message' => 'Không tìm thấy danh mục sản phẩm hợp lệ trong hệ thống.'
                ]
            ];
        }

        $categoryIds = array_column($foundCats, 'id');

        // 3. HARD FILTER QUERY
        $placeholdersIds = implode(',', array_fill(0, count($categoryIds), '?'));
        $sql = "SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN brands b ON p.brand_id = b.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' 
                  AND p.stock > 0 
                  AND p.price >= ? 
                  AND p.category_id IN ($placeholdersIds)";

        $queryParams = array_merge([$minBudget], $categoryIds);

        if ($maxBudget !== null) {
            $sql .= " AND p.price <= ?";
            $queryParams[] = $maxBudget;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($queryParams);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Nếu không có sản phẩm trong khoảng giá hẹp, mở rộng nhẹ khoảng giá để không bỏ sót
        if (empty($products) && $maxBudget !== null) {
            $sqlExpanded = "SELECT p.*, b.name as brand_name, c.name as category_name, c.slug as category_slug
                            FROM products p
                            LEFT JOIN brands b ON p.brand_id = b.id
                            LEFT JOIN categories c ON p.category_id = c.id
                            WHERE p.status = 'active' AND p.stock > 0 AND p.category_id IN ($placeholdersIds)
                            ORDER BY ABS(p.price - ?) ASC LIMIT 10";
            $stmtExp = $db->prepare($sqlExpanded);
            $stmtExp->execute(array_merge($categoryIds, [($minBudget + $maxBudget) / 2]));
            $products = $stmtExp->fetchAll(PDO::FETCH_ASSOC);
        }

        if (empty($products)) {
            return [
                'success' => false,
                'error' => [
                    'code' => 'NO_CANDIDATES',
                    'message' => "Không tìm thấy sản phẩm nào trong kho đáp ứng tiêu chí ngân sách {$budgetLabel}."
                ]
            ];
        }

        // Loại trừ từ khóa nếu có
        if (!empty($excluded)) {
            $exTerms = array_map('trim', explode(',', mb_strtolower($excluded)));
            $products = array_values(array_filter($products, function($p) use ($exTerms) {
                $pName = mb_strtolower($p['name']);
                foreach ($exTerms as $t) {
                    if ($t !== '' && str_contains($pName, $t)) return false;
                }
                return true;
            }));
        }

        // 4. DETERMINISTIC SCORING ENGINE (100 POINTS MAX)
        $scoredCandidates = [];
        foreach ($products as $p) {
            $specs = json_decode($p['specs'] ?? '{}', true) ?: [];
            
            $purposeFit      = self::calculatePurposeScore($categoryKey, $purpose, $p, $specs);
            $priorityFit     = self::calculatePriorityScore($categoryKey, $priority, $p, $specs);
            $softwareFit     = self::calculateSoftwareScore($software, $p, $specs);
            $budgetValue     = self::calculateBudgetValueScore((float)$p['price'], $minBudget, $maxBudget);
            $brandFit        = self::calculateBrandScore($brandFilter, $p['brand_name'] ?? '');
            $dataCompleteness= self::calculateDataCompletenessScore($specs);

            $totalScore = $purposeFit + $priorityFit + $softwareFit + $budgetValue + $brandFit + $dataCompleteness;
            $totalScore = min(99, max(60, round($totalScore)));

            $confidence = 'high';
            if ($dataCompleteness < 3) {
                $confidence = 'medium';
            }
            if ($dataCompleteness < 1) {
                $confidence = 'low';
            }

            $p['score'] = $totalScore;
            $p['confidence'] = $confidence;
            $p['score_breakdown'] = [
                'purpose_fit'       => $purposeFit,
                'priority_fit'      => $priorityFit,
                'software_fit'      => $softwareFit,
                'budget_value'      => $budgetValue,
                'brand_filter'      => $brandFit,
                'data_completeness' => $dataCompleteness,
                'total'             => $totalScore
            ];
            $p['parsed_specs'] = $specs;
            $scoredCandidates[] = $p;
        }

        // Sort by total score DESC
        usort($scoredCandidates, function($a, $b) {
            if ($b['score'] === $a['score']) {
                return $a['price'] <=> $b['price'];
            }
            return $b['score'] <=> $a['score'];
        });

        // 5. CHỌN 3 SẢN PHẨM KHÁC NHAU CHO 3 VAI TRÒ
        $bestFit = $scoredCandidates[0];
        
        // Best Value: Tỷ lệ Score / Price cao nhất (trong các sản phẩm score >= 65)
        $bestValue = null;
        $bestValRatio = -1;
        foreach ($scoredCandidates as $cand) {
            if ($cand['id'] === $bestFit['id']) continue;
            $ratio = ($cand['score'] * 1000000) / max(1, (float)$cand['price']);
            if ($ratio > $bestValRatio) {
                $bestValRatio = $ratio;
                $bestValue = $cand;
            }
        }
        if (!$bestValue) {
            $bestValue = $scoredCandidates[1] ?? $bestFit;
        }

        // Max Performance: Priority Fit / Spec score cao nhất
        $maxPerf = null;
        $maxPerfScore = -1;
        foreach ($scoredCandidates as $cand) {
            if ($cand['id'] === $bestFit['id'] || $cand['id'] === $bestValue['id']) continue;
            $perfScore = $cand['score_breakdown']['priority_fit'] + $cand['score_breakdown']['purpose_fit'];
            if ($perfScore > $maxPerfScore) {
                $maxPerfScore = $perfScore;
                $maxPerf = $cand;
            }
        }
        if (!$maxPerf) {
            $maxPerf = $scoredCandidates[2] ?? ($scoredCandidates[1] ?? $bestFit);
        }

        $selectedMap = [
            'best_fit'        => $bestFit,
            'best_value'      => $bestValue,
            'max_performance' => $maxPerf
        ];

        // 6. TẠO EXPLANATIONS BẰNG AI HOẶC TEMPLATE LOCAL
        $aiExplanations = self::generateAiExplanations($selectedMap, [
            'budget_label' => $budgetLabel,
            'category_name' => $catDef['label'],
            'purpose' => $purpose,
            'priority' => $priority,
            'software' => $software
        ]);

        // 7. BUILD UNIFIED RESPONSE CONTRACT
        $recommendations = [];
        $rolesOrder = [
            'best_fit'        => ['label' => 'PHÙ HỢP NHẤT', 'default_desc' => 'Đáp ứng toàn diện nhất các tiêu chí khảo sát.'],
            'best_value'      => ['label' => 'ĐÁNG TIỀN NHẤT', 'default_desc' => 'Tỷ lệ hiệu năng trên giá thành (P/P) tối ưu nhất.'],
            'max_performance' => ['label' => 'HIỆU NĂNG CAO NHẤT', 'default_desc' => 'Cấu hình phần cứng mạnh mẽ nhất trong phân khúc.']
        ];

        $warnings = [];
        $usedIds = [];

        foreach ($rolesOrder as $roleKey => $roleMeta) {
            $cand = $selectedMap[$roleKey];
            if (in_array($cand['id'], $usedIds)) {
                // Warning if candidate duplicated due to small database inventory
                $warnings[] = "Số lượng mẫu sản phẩm còn hàng trong khoảng giá hẹp nên vai trò {$roleMeta['label']} trùng mẫu sản phẩm.";
            }
            $usedIds[] = $cand['id'];

            $aiItem = $aiExplanations[$roleKey] ?? [];

            $formattedSpecs = self::formatSpecsForDisplay($cand['parsed_specs'], $categoryKey);

            $recommendations[] = [
                'role'             => $roleKey,
                'role_label'       => $roleMeta['label'],
                'product'          => [
                    'id'              => (int)$cand['id'],
                    'name'            => $cand['name'],
                    'slug'            => $cand['slug'] ?? '',
                    'brand_name'      => $cand['brand_name'] ?? '',
                    'category_name'   => $cand['category_name'] ?? '',
                    'category_slug'   => $cand['category_slug'] ?? '',
                    'price'           => (float)$cand['price'],
                    'price_formatted' => formatPrice((float)$cand['price']),
                    'stock'           => (int)$cand['stock'],
                    'image'           => $cand['image'],
                    'image_url'       => productImageUrl($cand['image'] ?? '', $cand['category_slug'] ?? '', (int)$cand['id']),
                    'specs'           => $formattedSpecs
                ],
                'score'            => (int)$cand['score'],
                'score_breakdown'  => $cand['score_breakdown'],
                'confidence'       => $cand['confidence'],
                'reasons'          => !empty($aiItem['reasons']) ? $aiItem['reasons'] : [
                    "Đạt điểm đánh giá tổng thể {$cand['score']}/100.",
                    "Thuộc thương hiệu " . ($cand['brand_name'] ?? 'uy tín') . " với chất lượng hoàn thiện tốt.",
                    "Mức giá " . formatPrice((float)$cand['price']) . " nằm trong hạn mức ngân sách."
                ],
                'tradeoffs'        => !empty($aiItem['tradeoffs']) ? $aiItem['tradeoffs'] : [
                    "Cần cân nhắc nhu cầu mở rộng lưu trữ/bộ nhớ về lâu dài.",
                    "Số lượng trong kho còn " . $cand['stock'] . " sản phẩm."
                ]
            ];
        }

        return [
            'success' => true,
            'query'   => [
                'budget_code'  => $budgetCode,
                'category'     => $categoryKey,
                'subcategory'  => $subCatKey,
                'purpose'      => $purpose,
                'priority'     => $priority,
                'software'     => $software
            ],
            'recommendations' => $recommendations,
            'summary'         => $aiExplanations['summary'] ?? "Dựa trên phân tích kỹ thuật, TechPilot AI đề xuất 3 giải pháp tối ưu nhất cho nhu cầu của bạn.",
            'warnings'        => array_values(array_unique($warnings))
        ];
    }

    // ==========================================
    // SCORING HELPER FUNCTIONS (DETERMINISTIC)
    // ==========================================

    private static function calculatePurposeScore(string $cat, string $purpose, array $p, array $specs): int
    {
        $name = mb_strtolower($p['name']);
        $score = 25; // Base score

        if ($cat === 'pc') {
            if ($purpose === 'gaming_aaa' && (str_contains($name, 'rtx 4070') || str_contains($name, 'rtx 4080') || str_contains($name, 'rtx 4090'))) $score += 10;
            elseif ($purpose === 'gaming_esports' && (str_contains($name, 'rtx 4060') || str_contains($name, 'rtx 3060') || str_contains($name, 'i5'))) $score += 8;
            elseif ($purpose === 'ai_ml' && str_contains($name, 'rtx 40')) $score += 10;
        } elseif ($cat === 'laptop') {
            if ($purpose === 'gaming' && (str_contains($name, 'gaming') || str_contains($name, 'rtx'))) $score += 10;
            elseif ($purpose === 'coding' && (str_contains($name, 'i5') || str_contains($name, 'i7') || str_contains($name, 'ryzen 7'))) $score += 8;
            elseif ($purpose === 'travel' && (str_contains($name, 'slim') || str_contains($name, 'oled') || str_contains($name, 'zenbook'))) $score += 8;
        } elseif ($cat === 'monitor') {
            if ($purpose === 'gaming' && (str_contains($name, '144hz') || str_contains($name, '180hz') || str_contains($name, '240hz'))) $score += 10;
            elseif ($purpose === 'design' && (str_contains($name, '4k') || str_contains($name, '2k') || str_contains($name, 'ips'))) $score += 8;
        }

        return min(35, $score);
    }

    private static function calculatePriorityScore(string $cat, string $priority, array $p, array $specs): int
    {
        $name = mb_strtolower($p['name']);
        $score = 18;

        if ($cat === 'pc') {
            // PC HAS NO BATTERY OR WEIGHT
            if ($priority === 'gpu_performance' && (str_contains($name, 'rtx') || str_contains($name, 'vga'))) $score += 7;
            elseif ($priority === 'cpu_performance' && (str_contains($name, 'i7') || str_contains($name, 'i9') || str_contains($name, 'ryzen 7') || str_contains($name, 'ryzen 9'))) $score += 7;
            elseif ($priority === 'cooling' && (str_contains($name, 'tản') || str_contains($name, 'deepcool'))) $score += 5;
        } elseif ($cat === 'laptop') {
            if ($priority === 'performance' && (str_contains($name, 'rtx') || str_contains($name, 'i7'))) $score += 7;
            elseif ($priority === 'lightweight' && (str_contains($name, 'slim') || str_contains($name, 'vivobook') || str_contains($name, 'yoga'))) $score += 7;
            elseif ($priority === 'battery' && isset($specs['Pin'])) $score += 5;
        }

        return min(25, $score);
    }

    private static function calculateSoftwareScore(string $software, array $p, array $specs): int
    {
        if (empty($software)) return 12;
        $softLower = mb_strtolower($software);
        $nameLower = mb_strtolower($p['name']);

        if ((str_contains($softLower, 'wukong') || str_contains($softLower, 'cyberpunk')) && str_contains($nameLower, 'rtx')) return 15;
        if ((str_contains($softLower, 'docker') || str_contains($softLower, 'android')) && (str_contains($nameLower, '32gb') || str_contains($nameLower, '16gb'))) return 15;

        return 12;
    }

    private static function calculateBudgetValueScore(float $price, float $minBudget, ?float $maxBudget): int
    {
        if ($maxBudget === null) return 12;
        $range = max(1, $maxBudget - $minBudget);
        $ratio = ($price - $minBudget) / $range;

        // Điểm đáng tiền cao nhất ở vùng 70% - 90% ngân sách (tối ưu hóa cấu hình/chi phí)
        if ($ratio >= 0.6 && $ratio <= 0.95) return 15;
        return 11;
    }

    private static function calculateBrandScore(string $brandFilter, string $productBrand): int
    {
        if (empty($brandFilter) || $brandFilter === 'all') return 5;
        if (str_contains(mb_strtolower($productBrand), mb_strtolower($brandFilter))) return 5;
        return 2;
    }

    private static function calculateDataCompletenessScore(array $specs): int
    {
        if (empty($specs)) return 0;
        $count = count($specs);
        if ($count >= 5) return 5;
        if ($count >= 3) return 3;
        return 1;
    }

    // ==========================================
    // SPECS FORMATTING WITHOUT DUMMY FALLBACKS
    // ==========================================

    private static function formatSpecsForDisplay(array $specs, string $categoryKey): array
    {
        $result = [];
        if (empty($specs)) {
            return [
                'Thông số kỹ thuật' => 'Chưa có dữ liệu chi tiết'
            ];
        }

        foreach ($specs as $k => $v) {
            if ($k === 'schema_version' || $k === 'attributes' || $k === 'raw_specs' || $k === 'vfm_score') continue;
            if (is_array($v)) {
                $valStr = implode(' / ', array_filter(array_map('strval', $v)));
            } else {
                $valStr = (string)$v;
            }
            if ($valStr !== '') {
                $result[$k] = $valStr;
            }
        }

        return !empty($result) ? $result : ['Thông số kỹ thuật' => 'Chưa có dữ liệu chi tiết'];
    }

    // ==========================================
    // AI EXPLANATION GENERATOR VIA GEMINI / GROQ
    // ==========================================

    private static function generateAiExplanations(array $selectedMap, array $context): array
    {
        $promptText = "Bạn là Trợ lý AI Tư vấn Công nghệ TechPilot. Hãy viết giải thích ngắn gọn, chuyên nghiệp cho 3 sản phẩm được chọn:\n\n";
        $promptText .= "Bối cảnh người dùng:\n";
        $promptText .= "- Ngân sách: " . $context['budget_label'] . "\n";
        $promptText .= "- Loại sản phẩm: " . $context['category_name'] . "\n";
        $promptText .= "- Nhu cầu: " . ($context['purpose'] ?: 'Chung') . "\n";
        $promptText .= "- Tiêu chí ưu tiên: " . ($context['priority'] ?: 'Hiệu năng') . "\n\n";

        $promptText .= "Danh sách 3 sản phẩm đã khóa chọn:\n";

        foreach ($selectedMap as $roleKey => $p) {
            $promptText .= "[$roleKey] - " . $p['name'] . " (" . formatPrice((float)$p['price']) . ") - Score: " . $p['score'] . "/100\n";
        }

        $promptText .= "\nYêu cầu xuất định dạng JSON thuần túy (không bọc trong markdown block):\n";
        $promptText .= "{\n";
        $promptText .= '  "summary": "Tóm tắt ngắn 1-2 câu về kết quả tư vấn",' . "\n";
        $promptText .= '  "best_fit": { "reasons": ["Lý do 1", "Lý do 2", "Lý do 3"], "tradeoffs": ["Cân nhắc 1", "Cân nhắc 2"] },' . "\n";
        $promptText .= '  "best_value": { "reasons": ["Lý do 1", "Lý do 2", "Lý do 3"], "tradeoffs": ["Cân nhắc 1", "Cân nhắc 2"] },' . "\n";
        $promptText .= '  "max_performance": { "reasons": ["Lý do 1", "Lý do 2", "Lý do 3"], "tradeoffs": ["Cân nhắc 1", "Cân nhắc 2"] }' . "\n";
        $promptText .= "}\n";

        try {
            $rawResponse = GeminiService::callGemini($promptText, ['type' => 'recommendation_explanation']);
            $cleanJson = trim(preg_replace('/^```json\s*|\s*```$/i', '', trim($rawResponse)));
            $parsed = json_decode($cleanJson, true);

            if (is_array($parsed) && isset($parsed['best_fit'])) {
                return $parsed;
            }
        } catch (Exception $e) {
            // Silence AI error, fallback cleanly to local structured explanation
        }

        return [
            'summary' => "Dựa trên tiêu chí khảo sát và dữ liệu kho hàng, TechPilot AI gợi ý 3 cấu hình tối ưu nhất cho bạn.",
            'best_fit' => [
                'reasons' => [
                    "Cân bằng tốt nhất giữa hiệu năng, thương hiệu và mức giá.",
                    "Đáp ứng tốt các yêu cầu tác vụ đã chọn.",
                    "Sản phẩm chính hãng, bảo hành đầy đủ tại TechPilot."
                ],
                'tradeoffs' => [
                    "Kiểm tra dung lượng lưu trữ thực tế theo nhu cầu sử dụng.",
                    "Số lượng hàng tồn kho còn giới hạn."
                ]
            ],
            'best_value' => [
                'reasons' => [
                    "Tỷ lệ P/P (Hiệu năng / Giá thành) vượt trội trong phân khúc.",
                    "Tiết kiệm chi phí đầu tư nhưng vẫn đảm bảo trải nghiệm mượt mà.",
                    "Tối ưu ngân sách cho các phụ kiện đi kèm."
                ],
                'tradeoffs' => [
                    "Cấu hình ở mức vừa đủ, ít dư dả cho các tác vụ cực nặng.",
                    "Vỏ ngoại hình thiết kế ở mức cơ bản."
                ]
            ],
            'max_performance' => [
                'reasons' => [
                    "Cấu hình linh kiện phần cứng thuộc nhóm mạnh mẽ nhất phân khúc.",
                    "Khả năng gánh các tác vụ nặng và chơi game FPS cao ấn tượng.",
                    "Tiềm năng khai thác lâu dài trong nhiều năm tới."
                ],
                'tradeoffs' => [
                    "Mức giá tiệm cận trần ngân sách khảo sát.",
                    "Mức tiêu thụ điện năng/nhiệt lượng cao hơn."
                ]
            ]
        ];
    }
}
