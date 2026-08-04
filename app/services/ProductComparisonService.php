<?php
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/services/GeminiService.php';

class ProductComparisonService
{
    /**
     * Phân tích và chấm điểm so sánh theo Persona & Danh mục (Deterministic Persona Engine)
     */
    public static function analyzeComparison(array $products, array $options = []): array
    {
        if (empty($products)) {
            return [
                'success' => false,
                'message' => 'Danh sách sản phẩm so sánh rỗng.'
            ];
        }

        $config = require ROOT_PATH . '/config/product-comparison.php';

        $catKey   = trim($options['category'] ?? 'laptop');
        $persona  = trim($options['persona'] ?? 'developer');
        $priorities = (array)($options['priorities'] ?? ['performance']);
        $maxBudget  = !empty($options['budget_max']) ? (float)$options['budget_max'] : null;
        $minRam     = !empty($options['min_ram']) ? (int)$options['min_ram'] : 0;
        $minStorage = !empty($options['min_storage']) ? (int)$options['min_storage'] : 0;
        $minRefresh = !empty($options['min_refresh_rate']) ? (int)$options['min_refresh_rate'] : 0;

        // 1. EVALUATE HARD REQUIREMENTS & DETERMINISTIC SCORING FOR EACH PRODUCT
        $analyzedProducts = [];
        foreach ($products as $p) {
            $rawSpecs = json_decode($p['specs'] ?? '{}', true) ?: [];
            $parsedSpecs = self::cleanAndParseSpecs($rawSpecs, $p);

            // Hard Requirement Verification
            $eligible = true;
            $ineligibleReasons = [];

            if ($maxBudget !== null && (float)$p['price'] > $maxBudget) {
                $eligible = false;
                $ineligibleReasons[] = "Giá " . formatPrice((float)$p['price']) . " vượt mức ngân sách " . formatPrice($maxBudget);
            }

            if ($minRam > 0) {
                $ramVal = self::extractNumericVal($parsedSpecs['RAM'] ?? '');
                if ($ramVal > 0 && $ramVal < $minRam) {
                    $eligible = false;
                    $ineligibleReasons[] = "RAM {$ramVal}GB thấp hơn mức tối thiểu {$minRam}GB";
                }
            }

            if ($minStorage > 0) {
                $ssdVal = self::extractNumericVal($parsedSpecs['SSD'] ?? $parsedSpecs['Storage'] ?? '');
                if ($ssdVal > 0 && $ssdVal < $minStorage) {
                    $eligible = false;
                    $ineligibleReasons[] = "Dung lượng lưu trữ thấp hơn mức tối thiểu {$minStorage}GB";
                }
            }

            if ($minRefresh > 0) {
                $hzVal = self::extractNumericVal($parsedSpecs['Tần số quét'] ?? $parsedSpecs['refresh_rate'] ?? '');
                if ($hzVal > 0 && $hzVal < $minRefresh) {
                    $eligible = false;
                    $ineligibleReasons[] = "Tần số quét {$hzVal}Hz chưa đạt mức yêu cầu {$minRefresh}Hz";
                }
            }

            // Chấm điểm xác định 100 điểm
            $personaFit    = self::calcPersonaFit($catKey, $persona, $p, $parsedSpecs);
            $performanceFit= self::calcPerformanceFit($catKey, $p, $parsedSpecs);
            $valueFit      = self::calcValueFit((float)$p['price'], $performanceFit);
            $longevity     = self::calcLongevityUpgrade($catKey, $p, $parsedSpecs);
            $categoryQual  = self::calcCategoryQuality($catKey, $p, $parsedSpecs);
            $dataComplete  = self::calcDataCompleteness($parsedSpecs);

            $totalScore = $personaFit + $performanceFit + $valueFit + $longevity + $categoryQual + $dataComplete;
            $totalScore = min(99, max(50, round($totalScore)));

            // Phân loại độ tin cậy dữ liệu (Confidence Rating)
            $confidence = 'high';
            if ($dataComplete < 3) $confidence = 'medium';
            if ($dataComplete < 1) $confidence = 'low';

            $strengths = self::extractStrengths($catKey, $p, $parsedSpecs, $totalScore);
            $weaknesses = self::extractWeaknesses($catKey, $p, $parsedSpecs, $ineligibleReasons);

            $p['eligible']           = $eligible;
            $p['ineligible_reasons'] = $ineligibleReasons;
            $p['total_score']        = $totalScore;
            $p['confidence']         = $confidence;
            $p['parsed_specs']       = $parsedSpecs;
            $p['score_breakdown']    = [
                'persona_fit'               => $personaFit,
                'performance_fit'           => $performanceFit,
                'value_fit'                 => $valueFit,
                'longevity_upgrade'         => $longevity,
                'category_specific_quality' => $categoryQual,
                'data_completeness'         => $dataComplete,
                'total'                     => $totalScore
            ];
            $p['strengths']  = $strengths;
            $p['weaknesses'] = $weaknesses;
            $p['fps_estimates'] = self::calculateGameFpsEstimates($catKey, $p, $parsedSpecs);

            $analyzedProducts[] = $p;
        }

        // Sort candidates
        usort($analyzedProducts, function($a, $b) {
            if ($a['eligible'] !== $b['eligible']) {
                return $b['eligible'] <=> $a['eligible'];
            }
            return $b['total_score'] <=> $a['total_score'];
        });

        // 2. CHỌN 3 SẢN PHẨM CHIẾN THẮNG THEO VAI TRÒ
        $bestFit = $analyzedProducts[0];
        
        $bestValue = null;
        $maxValRatio = -1;
        foreach ($analyzedProducts as $cand) {
            if ($cand['id'] === $bestFit['id']) continue;
            $ratio = ($cand['total_score'] * 1000000) / max(1, (float)$cand['price']);
            if ($ratio > $maxValRatio) {
                $maxValRatio = $ratio;
                $bestValue = $cand;
            }
        }
        if (!$bestValue) $bestValue = $analyzedProducts[1] ?? $bestFit;

        $bestPerf = null;
        $maxPerfScore = -1;
        foreach ($analyzedProducts as $cand) {
            if ($cand['id'] === $bestFit['id'] || $cand['id'] === $bestValue['id']) continue;
            $perfScore = $cand['score_breakdown']['performance_fit'] + $cand['score_breakdown']['persona_fit'];
            if ($perfScore > $maxPerfScore) {
                $maxPerfScore = $perfScore;
                $bestPerf = $cand;
            }
        }
        if (!$bestPerf) $bestPerf = $analyzedProducts[2] ?? ($analyzedProducts[1] ?? $bestFit);

        $winners = [
            'best_fit'         => (int)$bestFit['id'],
            'best_value'       => (int)$bestValue['id'],
            'best_performance' => (int)$bestPerf['id']
        ];

        // 3. GENERATE AI EXPLANATION FOR LOCKED BACKEND WINNERS
        $aiAnalysis = self::generateAiComparisonExplanation($analyzedProducts, $winners, [
            'category' => $catKey,
            'persona'  => $persona,
            'priorities' => $priorities
        ]);

        return [
            'success'    => true,
            'category'   => $catKey,
            'persona'    => $persona,
            'priorities' => $priorities,
            'products'   => $analyzedProducts,
            'winners'    => $winners,
            'analysis'   => $aiAnalysis
        ];
    }

    // ==========================================
    // DETERMINISTIC SCORING ENGINE
    // ==========================================

    private static function calcPersonaFit(string $cat, string $persona, array $p, array $specs): int
    {
        $name = mb_strtolower($p['name']);
        $score = 25;

        if ($cat === 'prebuilt_pc') {
            if ($persona === 'aaa_gaming' && (str_contains($name, 'rtx 4070') || str_contains($name, 'rtx 4080') || str_contains($name, 'rtx 4090'))) $score += 10;
            elseif ($persona === 'esports' && (str_contains($name, 'rtx 4060') || str_contains($name, 'i5') || str_contains($name, 'ryzen 5'))) $score += 8;
            elseif ($persona === 'ai_ml' && str_contains($name, 'rtx 40')) $score += 10;
            elseif ($persona === 'office' && str_contains($name, 'i3') || str_contains($name, 'văn phòng')) $score += 8;
        } elseif ($cat === 'laptop') {
            if ($persona === 'gamer' && (str_contains($name, 'gaming') || str_contains($name, 'rtx'))) $score += 10;
            elseif ($persona === 'developer' && (str_contains($name, 'i7') || str_contains($name, 'ryzen 7') || str_contains($name, '32gb'))) $score += 8;
            elseif ($persona === 'business_travel' && (str_contains($name, 'slim') || str_contains($name, 'oled') || str_contains($name, 'zenbook'))) $score += 8;
        } elseif ($cat === 'monitor') {
            if ($persona === 'esports' && (str_contains($name, '180hz') || str_contains($name, '240hz'))) $score += 10;
            elseif ($persona === 'creator' && (str_contains($name, '4k') || str_contains($name, '2k') || str_contains($name, 'ips'))) $score += 8;
        }

        return min(35, $score);
    }

    private static function calcPerformanceFit(string $cat, array $p, array $specs): int
    {
        $name = mb_strtolower($p['name']);
        $score = 18;

        if (str_contains($name, 'i9') || str_contains($name, 'ryzen 9') || str_contains($name, 'rtx 4080') || str_contains($name, 'rtx 4090')) $score = 25;
        elseif (str_contains($name, 'i7') || str_contains($name, 'ryzen 7') || str_contains($name, 'rtx 4070')) $score = 22;
        elseif (str_contains($name, 'i5') || str_contains($name, 'ryzen 5') || str_contains($name, 'rtx 4060')) $score = 20;

        return min(25, $score);
    }

    private static function calcValueFit(float $price, int $perfScore): int
    {
        if ($price <= 0) return 10;
        $valRatio = ($perfScore * 1000000) / $price;
        if ($valRatio >= 1.0) return 15;
        if ($valRatio >= 0.7) return 12;
        return 9;
    }

    private static function calcLongevityUpgrade(string $cat, array $p, array $specs): int
    {
        $name = mb_strtolower($p['name']);
        if ($cat === 'prebuilt_pc') return 10; // PC lắp sẵn nâng cấp dễ dàng
        if (str_contains($name, '32gb') || str_contains($name, '1tb') || str_contains($name, 'dễ nâng cấp')) return 9;
        return 7;
    }

    private static function calcCategoryQuality(string $cat, array $p, array $specs): int
    {
        $brand = mb_strtolower($p['brand_name'] ?? '');
        if (in_array($brand, ['asus', 'msi', 'dell', 'gigabyte', 'apple', 'razer', 'corsair'])) return 10;
        return 7;
    }

    private static function calcDataCompleteness(array $specs): int
    {
        if (empty($specs)) return 0;
        $count = count($specs);
        if ($count >= 5) return 5;
        if ($count >= 3) return 3;
        return 1;
    }

    // ==========================================
    // METADATA CLEANING & SPEC PARSING
    // ==========================================

    private static function cleanAndParseSpecs(array $rawSpecs, array $p): array
    {
        $config = require ROOT_PATH . '/config/product-comparison.php';
        $excludedKeys = $config['excluded_spec_keys'] ?? [];

        $cleaned = [];

        // Flatten attributes if stored inside normalized JSON
        if (isset($rawSpecs['attributes']) && is_array($rawSpecs['attributes'])) {
            foreach ($rawSpecs['attributes'] as $k => $v) {
                if (in_array($k, $excludedKeys)) continue;
                $cleaned[self::humanizeSpecKey($k)] = self::stringifySpecValue($v);
            }
        }

        foreach ($rawSpecs as $k => $v) {
            if (in_array($k, $excludedKeys)) continue;
            if ($k === 'attributes') continue;
            $cleaned[self::humanizeSpecKey($k)] = self::stringifySpecValue($v);
        }

        // Bổ sung thông số từ Tên sản phẩm nếu thiếu
        if (!isset($cleaned['CPU']) && preg_match('/(Intel Core i\d[-\w]*|AMD Ryzen \d[-\w]*|Apple M\d\w*)/i', $p['name'], $m)) {
            $cleaned['CPU'] = $m[1];
        }
        if (!isset($cleaned['VGA']) && preg_match('/(RTX \d{4}[-\w]*|GTX \d{4}[-\w]*|Radeon RX \d{4}[-\w]*)/i', $p['name'], $m)) {
            $cleaned['VGA'] = $m[1];
        }
        if (!isset($cleaned['RAM']) && preg_match('/(\d{1,2}GB)\s*(DDR\d)?/i', $p['name'], $m)) {
            $cleaned['RAM'] = $m[0];
        }
        if (!isset($cleaned['SSD']) && preg_match('/(SSD\s*\d{3,4}GB|SSD\s*1TB|2TB)/i', $p['name'], $m)) {
            $cleaned['SSD'] = $m[0];
        }

        return $cleaned;
    }

    private static function humanizeSpecKey(string $k): string
    {
        $map = [
            'cpu' => 'CPU',
            'ram' => 'RAM',
            'ssd' => 'SSD',
            'vga' => 'Card đồ họa (VGA)',
            'screen_size' => 'Kích thước màn hình',
            'screen_size_inch' => 'Kích thước màn hình',
            'resolution' => 'Độ phân giải',
            'refresh_rate' => 'Tần số quét',
            'refresh_rate_hz' => 'Tần số quét',
            'panel' => 'Tấm nền',
            'panel_type' => 'Tấm nền',
            'weight' => 'Trọng lượng',
            'battery' => 'Dung lượng pin',
            'psu' => 'Nguồn PSU',
            'mainboard' => 'Bo mạch chủ',
            'wattage' => 'Công suất',
            'efficiency' => 'Chuẩn 80 Plus'
        ];
        return $map[strtolower($k)] ?? ucfirst($k);
    }

    private static function extractNumericVal(string $str): float
    {
        if (preg_match('/(\d+(\.\d+)?)/', $str, $m)) {
            return (float)$m[1];
        }
        return 0;
    }

    private static function extractStrengths(string $cat, array $p, array $specs, int $score): array
    {
        $s = [];
        if ($score >= 90) $s[] = "Điểm đánh giá tổng thể cao vượt trội ({$score}/100)";
        if (!empty($specs['CPU'])) $s[] = "Bộ vi xử lý mạnh mẽ: " . $specs['CPU'];
        if (!empty($specs['VGA'])) $s[] = "Card đồ họa xử lý mượt: " . $specs['VGA'];
        if (!empty($specs['RAM'])) $s[] = "Bộ nhớ RAM đáp ứng tốt tác vụ: " . $specs['RAM'];
        if (empty($s)) $s[] = "Sản phẩm chính hãng với chế độ bảo hành 36 tháng";
        return array_slice($s, 0, 3);
    }

    private static function extractWeaknesses(string $cat, array $p, array $specs, array $ineligible): array
    {
        $w = $ineligible;
        if (empty($w)) {
            if ($cat === 'laptop' && (float)$p['price'] > 30000000) $w[] = "Mức giá ở phân khúc cao cấp";
            else $w[] = "Cần kiểm tra dung lượng ổ cứng thực tế theo nhu cầu cá nhân";
        }
        return array_slice($w, 0, 2);
    }

    // ==========================================
    // FPS ESTIMATES (ONLY FOR LAPTOP & PC WITH VERIFIED GPU DATA)
    // ==========================================

    private static function calculateGameFpsEstimates(string $cat, array $p, array $specs): ?array
    {
        if (!in_array($cat, ['laptop', 'prebuilt_pc'])) return null;

        $vgaStr = mb_strtolower($specs['Card đồ họa (VGA)'] ?? $specs['VGA'] ?? $p['name']);

        if (str_contains($vgaStr, 'rtx 4090') || str_contains($vgaStr, 'rtx 4080')) {
            return ['esports' => '300+ FPS (2K/4K)', 'aaa' => '100 - 140 FPS (4K Ultra)', 'confidence' => 'Cao'];
        } elseif (str_contains($vgaStr, 'rtx 4070') || str_contains($vgaStr, 'rtx 3080')) {
            return ['esports' => '240+ FPS (1080p/2K)', 'aaa' => '80 - 110 FPS (2K Ultra)', 'confidence' => 'Cao'];
        } elseif (str_contains($vgaStr, 'rtx 4060') || str_contains($vgaStr, 'rtx 3060')) {
            return ['esports' => '180+ FPS (1080p)', 'aaa' => '60 - 85 FPS (1080p High)', 'confidence' => 'Trung bình'];
        } elseif (str_contains($vgaStr, 'rtx 3050') || str_contains($vgaStr, 'gtx 1650')) {
            return ['esports' => '120+ FPS (1080p)', 'aaa' => '40 - 60 FPS (1080p Medium)', 'confidence' => 'Trung bình'];
        }

        return ['note' => 'Chưa đủ dữ liệu GPU để ước tính FPS chính xác', 'confidence' => 'Thấp'];
    }

    // ==========================================
    // AI COMPARISON EXPLANATION GENERATOR
    // ==========================================

    private static function generateAiComparisonExplanation(array $products, array $winners, array $context): array
    {
        $promptText = "Bạn là Chuyên gia So sánh Phần cứng Máy tính TechPilot. Dưới đây là kết quả so sánh 2-4 sản phẩm đã được hệ thống backend chấm điểm xác định:\n\n";
        $promptText .= "Bối cảnh:\n";
        $promptText .= "- Danh mục: " . $context['category'] . "\n";
        $promptText .= "- Đối tượng / Persona: " . $context['persona'] . "\n\n";

        $promptText .= "Danh sách sản phẩm so sánh:\n";
        foreach ($products as $p) {
            $isBestFit = $p['id'] === $winners['best_fit'] ? ' [PHÙ HỢP NHẤT]' : '';
            $isBestVal = $p['id'] === $winners['best_value'] ? ' [ĐÁNG TIỀN NHẤT]' : '';
            $isBestPerf = $p['id'] === $winners['best_performance'] ? ' [HIỆU NĂNG CAO NHẤT]' : '';
            $promptText .= "- ID {$p['id']}: {$p['name']} (" . formatPrice((float)$p['price']) . ") - Score: {$p['total_score']}/100{$isBestFit}{$isBestVal}{$isBestPerf}\n";
        }

        $promptText .= "\nHãy viết báo cáo phân tích chuyên sâu định dạng JSON thuần túy:\n";
        $promptText .= "{\n";
        $promptText .= '  "summary": "Tóm tắt kết luận so sánh 2-3 câu ngắn gọn",' . "\n";
        $promptText .= '  "who_should_buy": "Ai nên mua mẫu Phù hợp nhất và mẫu Đáng tiền nhất",' . "\n";
        $promptText .= '  "who_should_avoid": "Ai nên cân nhắc hoặc tránh mua mẫu nào",' . "\n";
        $promptText .= '  "tradeoffs": "Điểm cần đánh đổi chính khi lựa chọn giữa các mẫu trên"' . "\n";
        $promptText .= "}\n";

        try {
            $rawResponse = GeminiService::callGemini($promptText, ['type' => 'compare_explanation']);
            $cleanJson = trim(preg_replace('/^```json\s*|\s*```$/i', '', trim($rawResponse)));
            $parsed = json_decode($cleanJson, true);

            if (is_array($parsed) && isset($parsed['summary'])) {
                return $parsed;
            }
        } catch (Exception $e) {
            // Silence AI error, fallback to local template
        }

        return [
            'summary' => "Dựa trên phân tích kỹ thuật theo Persona " . $context['persona'] . ", hệ thống đã xác định được 3 sản phẩm tương ứng với các vai trò Phù hợp nhất, Đáng tiền nhất và Hiệu năng cao nhất.",
            'who_should_buy' => "• Nếu bạn cần sự an tâm và cân bằng nhất: Hãy chọn mẫu Phù hợp nhất.\n• Nếu bạn muốn tối ưu hóa từng đồng chi phí: Hãy chọn mẫu Đáng tiền nhất.",
            'who_should_avoid' => "• Nên tránh các mẫu vượt quá hạn mức tài chính hoặc có cấu hình chưa đạt yêu cầu tối thiểu.",
            'tradeoffs' => "• Các mẫu có hiệu năng cao hơn thường có mức giá tiệm cận trần ngân sách hoặc tỏa nhiều nhiệt hơn."
        ];
    }

    private static function stringifySpecValue($val): string
    {
        if (is_array($val)) {
            $items = [];
            foreach ($val as $subKey => $subVal) {
                $subStr = self::stringifySpecValue($subVal);
                if ($subStr !== '') {
                    $items[] = is_string($subKey) && !is_numeric($subKey) ? "$subKey: $subStr" : $subStr;
                }
            }
            return implode(' / ', array_filter($items));
        }
        if (is_scalar($val) || (is_object($val) && method_exists($val, '__toString'))) {
            return (string)$val;
        }
        return '';
    }
}
