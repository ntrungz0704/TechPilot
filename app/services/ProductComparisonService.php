<?php
require_once ROOT_PATH . '/app/core/helpers.php';
if (!class_exists('GeminiService')) {
    require_once ROOT_PATH . '/app/services/GeminiService.php';
}

class ProductComparisonService
{
    /**
     * Determine normalized category key from server slug.
     */
    public static function normalizeCategoryKey(string $serverSlug, array $config): ?string
    {
        $serverSlug = trim($serverSlug);
        if ($serverSlug === '') return null;

        $categories = $config['categories'] ?? [];
        foreach ($categories as $key => $catData) {
            if (isset($catData['slugs']) && in_array($serverSlug, $catData['slugs'], true)) {
                return (string)$key;
            }
        }
        return null;
    }

    /**
     * Parse RAM/Storage capacity into integer/float GB, strictly handling units and rejecting ambiguity.
     */
    public static function parseStorageGb(string $val): ?float
    {
        $val = mb_strtoupper(trim($val));
        if ($val === '') return null;

        // Reject ambiguous or conflicting strings
        if (preg_match('/\b(?:OR|HOẶC)\b/iu', $val) || str_contains($val, '/')) {
            return null;
        }

        $capacities = [];

        // e.g., 2x8GB, 2 X 16 GB, 2×8GB -> extract as a single entity
        $val = preg_replace_callback('/\b(\d+)\s*[X×]\s*(\d+(?:\.\d+)?)\s*(GB|MB|TB)\b/u', function($m) use (&$capacities) {
            $num = (float)$m[1] * (float)$m[2];
            $capacities[] = ['num' => $num, 'unit' => $m[3]];
            return ''; // consume to avoid double matching
        }, $val);

        // Standard capacities
        preg_replace_callback('/\b(\d+(?:\.\d+)?)\s*(GB|TB|MB)\b/u', function($m) use (&$capacities) {
            $capacities[] = ['num' => (float)$m[1], 'unit' => $m[2]];
            return '';
        }, $val);

        if (count($capacities) !== 1) {
            return null;
        }

        $num = $capacities[0]['num'];
        $unit = $capacities[0]['unit'];

        if ($unit === 'TB') return $num * 1024.0;
        if ($unit === 'MB') return $num / 1024.0;
        return $num;
    }

    /**
     * Parse Refresh Rate (Hz), rejecting ambiguity.
     */
    public static function parseRefreshRateHz(string $val): ?float
    {
        $val = mb_strtoupper(trim($val));
        if ($val === '') return null;

        // Reject numeric ranges like 60-144Hz
        if (preg_match('/\b(?:OR|HOẶC)\b/iu', $val) || str_contains($val, '/') || preg_match('/\d+\s*(?:HZ)?\s*-\s*\d+\s*(?:HZ)?/iu', $val)) {
            return null;
        }

        $rates = [];
        preg_replace_callback('/\b(\d+(?:\.\d+)?)\s*HZ\b/iu', function($m) use (&$rates) {
            $rates[] = (float)$m[1];
            return '';
        }, $val);

        if (count($rates) !== 1) {
            return null;
        }

        return $rates[0];
    }

    /**
     * Compute the single canonical effective price for a product.
     * Considers: base price, sale_price, active flash-sale price.
     * Returns the lowest valid price at time of comparison.
     */
    public static function effectivePrice(array $product, ?array $flashSale, ?int $nowTs = null): float
    {
        $now = $nowTs ?? time();

        $basePrice = (float)($product['price'] ?? 0);

        // Sale price: must be finite, > 0, and < base price
        $salePrice = isset($product['sale_price']) && $product['sale_price'] !== null
            ? (float)$product['sale_price']
            : null;

        if ($salePrice !== null && (!is_finite($salePrice) || $salePrice <= 0 || $salePrice >= $basePrice)) {
            $salePrice = null;
        }

        // Flash sale price validation
        $flashPrice = null;
        if ($flashSale !== null) {
            $dp            = (float)($flashSale['discount_price'] ?? -1);
            $fsStatus      = (string)($flashSale['fs_status'] ?? '');
            $fsStart       = isset($flashSale['fs_start']) ? strtotime($flashSale['fs_start']) : 0;
            $fsEnd         = isset($flashSale['fs_end'])   ? strtotime($flashSale['fs_end'])   : 0;
            $fsProductId   = (int)($flashSale['fs_product_id'] ?? 0);
            $productId     = (int)($product['id'] ?? 0);

            $validStatus   = ($fsStatus === 'active');
            $validTime     = ($fsStart > 0 && $fsEnd > 0 && $now >= $fsStart && $now < $fsEnd);
            $validOwner    = ($fsProductId === $productId);
            $validPrice    = (is_finite($dp) && $dp > 0 && $dp < $basePrice);

            if ($validStatus && $validTime && $validOwner && $validPrice) {
                $flashPrice = $dp;
            }
        }

        $candidates = [$basePrice];
        if ($salePrice !== null) $candidates[] = $salePrice;
        if ($flashPrice !== null) $candidates[] = $flashPrice;

        return max(0, min($candidates));
    }

    /**
     * Phân tích và chấm điểm so sánh theo Persona & Danh mục (Deterministic Persona Engine)
     */
    public static function analyzeComparison(array $products, array $options = []): array
    {
        $expectedCount = $options['expected_count'] ?? count($products);

        if (empty($products) || ($expectedCount >= 2 && count($products) < 2)) {
            return [
                'success' => true, // API success structure, just no winner
                'winner'  => null,
                'message' => 'Các sản phẩm so sánh không còn hợp lệ hoặc không tồn tại.',
                'suggestion' => 'Vui lòng kiểm tra lại danh sách sản phẩm.',
                'products' => [],
                'winners'  => ['best_fit' => null, 'best_value' => null, 'best_performance' => null],
                'reasons_by_product' => [],
                'verification_required' => [],
                'analysis' => self::fallbackAnalysis('unknown', 'unknown'),
            ];
        }

        $config = require ROOT_PATH . '/config/product-comparison.php';

        // Evaluate categories to ensure all belong to the same normalized category
        $validCatKeys = [];
        foreach ($products as $p) {
            $cKey = self::normalizeCategoryKey($p['category_slug'] ?? '', $config);
            if ($cKey !== null) {
                $validCatKeys[$cKey] = true;
            }
        }

        if (count($validCatKeys) > 1) {
            return [
                'success' => true,
                'winner'  => null,
                'message' => 'Không thể so sánh các sản phẩm thuộc nhiều danh mục khác nhau.',
                'suggestion' => 'Vui lòng chọn các sản phẩm cùng loại.',
                'products' => [],
                'winners'  => ['best_fit' => null, 'best_value' => null, 'best_performance' => null],
                'reasons_by_product' => [],
                'verification_required' => [],
                'analysis' => self::fallbackAnalysis('mixed', 'mixed'),
            ];
        }

        $catKey = !empty($validCatKeys) ? array_key_first($validCatKeys) : null;

        if ($catKey === null) {
            return [
                'success' => true,
                'winner'  => null,
                'message' => 'Sản phẩm không thuộc danh mục hợp lệ nào để so sánh.',
                'suggestion' => 'Vui lòng kiểm tra lại dữ liệu sản phẩm.',
                'products' => [],
                'winners'  => ['best_fit' => null, 'best_value' => null, 'best_performance' => null],
                'reasons_by_product' => [],
                'verification_required' => [],
                'analysis' => self::fallbackAnalysis('unknown', 'unknown'),
            ];
        }

        $persona    = trim($options['persona'] ?? 'developer');
        $priorities = (array)($options['priorities'] ?? ['performance']);
        $maxBudget  = isset($options['budget_max']) && $options['budget_max'] > 0
                      ? (float)$options['budget_max'] : null;
        $minRam     = !empty($options['min_ram'])          ? (int)$options['min_ram']          : 0;
        $minStorage = !empty($options['min_storage'])      ? (int)$options['min_storage']      : 0;
        $minRefresh = !empty($options['min_refresh_rate']) ? (int)$options['min_refresh_rate'] : 0;

        $now = $options['_now'] ?? null;

        // ── 1. EVALUATE EACH PRODUCT ────────────────────────────────────────
        $analyzedProducts = [];
        $reasonsByProduct = [];

        foreach ($products as $p) {
            $pid = (int)$p['id'];
            $eligible            = true;
            $verificationRequired = false;
            $ineligibleReasons   = [];
            $failedRequirements  = [];
            $verificationReasons = [];
            $effectivePrice      = 0;

            $pCatKey = self::normalizeCategoryKey($p['category_slug'] ?? '', $config);
            if ($pCatKey !== $catKey) {
                // Technically shouldn't hit here since we already verified above,
                // but good for safety if a product had no category.
                $eligible = false;
                $failedRequirements[] = 'category';
                $ineligibleReasons[]  = 'Danh mục sản phẩm không phù hợp';
            }

            // Base price validation
            $rawPrice = $p['price'] ?? null;
            if (!is_numeric($rawPrice) || !is_finite((float)$rawPrice) || (float)$rawPrice <= 0) {
                $eligible = false;
                $failedRequirements[] = 'price';
                $ineligibleReasons[]  = 'Giá sản phẩm không hợp lệ (base price = ' . var_export($rawPrice, true) . ')';
            } else {
                $effectivePrice = self::effectivePrice($p, $p['flash_sale'] ?? null, $now);
            }

            $p['effective_price'] = $effectivePrice;

            $rawSpecs    = json_decode($p['specs'] ?? '{}', true) ?: [];
            $parsedSpecs = self::cleanAndParseSpecs($rawSpecs, $p);
            $p['normalized_specs'] = $parsedSpecs;

            // Budget check
            if ($eligible && $maxBudget !== null && $effectivePrice > $maxBudget) {
                $eligible = false;
                $failedRequirements[] = 'budget';
                $ineligibleReasons[]  = 'Giá ' . formatPrice($effectivePrice) .
                                        ' vượt mức ngân sách ' . formatPrice($maxBudget);
            }

            // RAM hard requirement
            if ($minRam > 0) {
                $ramVal = self::parseStorageGb($parsedSpecs['RAM'] ?? '');
                if ($ramVal === null || $ramVal <= 0) {
                    $eligible = false;
                    $verificationRequired = true;
                    $failedRequirements[]  = 'ram';
                    $ineligibleReasons[]   = "Thiếu thông số RAM hoặc chuỗi mơ hồ — không thể xác minh yêu cầu tối thiểu {$minRam}GB";
                    $verificationReasons[] = "RAM chưa được xác minh";
                } elseif ($ramVal < $minRam) {
                    $eligible = false;
                    $failedRequirements[]  = 'ram';
                    $ineligibleReasons[]   = "RAM {$ramVal}GB thấp hơn mức tối thiểu {$minRam}GB";
                }
            }

            // SSD hard requirement
            if ($minStorage > 0) {
                $ssdVal = self::parseStorageGb($parsedSpecs['SSD'] ?? $parsedSpecs['Storage'] ?? '');
                if ($ssdVal === null || $ssdVal <= 0) {
                    $eligible = false;
                    $verificationRequired = true;
                    $failedRequirements[]  = 'ssd';
                    $ineligibleReasons[]   = "Thiếu thông số SSD hoặc chuỗi mơ hồ — không thể xác minh yêu cầu tối thiểu {$minStorage}GB";
                    $verificationReasons[] = "SSD chưa được xác minh";
                } elseif ($ssdVal < $minStorage) {
                    $eligible = false;
                    $failedRequirements[]  = 'ssd';
                    $ineligibleReasons[]   = "Dung lượng lưu trữ {$ssdVal}GB thấp hơn mức tối thiểu {$minStorage}GB";
                }
            }

            // Refresh rate hard requirement
            if ($minRefresh > 0) {
                $hzVal = self::parseRefreshRateHz($parsedSpecs['Tần số quét'] ?? $parsedSpecs['refresh_rate'] ?? $parsedSpecs['Màn hình'] ?? '');
                if ($hzVal === null || $hzVal <= 0) {
                    $eligible = false;
                    $verificationRequired = true;
                    $failedRequirements[]  = 'refresh_rate';
                    $ineligibleReasons[]   = "Thiếu thông số tần số quét hoặc chuỗi mơ hồ — không thể xác minh yêu cầu tối thiểu {$minRefresh}Hz";
                    $verificationReasons[] = "Tần số quét chưa được xác minh";
                } elseif ($hzVal < $minRefresh) {
                    $eligible = false;
                    $failedRequirements[]  = 'refresh_rate';
                    $ineligibleReasons[]   = "Tần số quét {$hzVal}Hz chưa đạt mức yêu cầu {$minRefresh}Hz";
                }
            }

            // Scoring
            $personaFit     = self::calcPersonaFit($catKey, $persona, $p, $parsedSpecs);
            $performanceFit = self::calcPerformanceFit($catKey, $p, $parsedSpecs);
            $valueFit       = self::calcValueFit($effectivePrice, $performanceFit);
            $longevity      = self::calcLongevityUpgrade($catKey, $p, $parsedSpecs);
            $categoryQual   = self::calcCategoryQuality($catKey, $p, $parsedSpecs);
            $dataComplete   = self::calcDataCompleteness($parsedSpecs);

            $totalScore = $personaFit + $performanceFit + $valueFit + $longevity + $categoryQual + $dataComplete;
            $totalScore = min(99, max(50, round($totalScore)));

            $confidence = 'high';
            if ($dataComplete < 3) $confidence = 'medium';
            if ($dataComplete < 1) $confidence = 'low';

            $strengths  = self::extractStrengths($catKey, $p, $parsedSpecs, $totalScore);
            $weaknesses = self::extractWeaknesses($catKey, $p, $parsedSpecs, $ineligibleReasons);

            $p['eligible']              = $eligible;
            $p['verification_required'] = $verificationRequired;
            $p['ineligible_reasons']    = $ineligibleReasons;
            $p['failed_requirements']   = $failedRequirements;
            $p['verification_reasons']  = $verificationReasons;
            $p['total_score']           = $totalScore;
            $p['confidence']            = $confidence;
            $p['parsed_specs']          = $parsedSpecs;
            $p['score_breakdown']       = [
                'persona_fit'               => $personaFit,
                'performance_fit'           => $performanceFit,
                'value_fit'                 => $valueFit,
                'longevity_upgrade'         => $longevity,
                'category_specific_quality' => $categoryQual,
                'data_completeness'         => $dataComplete,
                'total'                     => $totalScore,
            ];
            $p['strengths']    = $strengths;
            $p['weaknesses']   = $weaknesses;
            $p['fps_estimates'] = self::calculateGameFpsEstimates($catKey, $p, $parsedSpecs);

            $reasonsByProduct[$pid] = $ineligibleReasons;
            $analyzedProducts[]     = $p;
        }

        // Sort: eligible first, then by score desc, then by id asc (stable tie-breaking)
        usort($analyzedProducts, function ($a, $b) {
            if ($a['eligible'] !== $b['eligible']) {
                return $b['eligible'] <=> $a['eligible'];
            }
            if ($b['total_score'] !== $a['total_score']) {
                return $b['total_score'] <=> $a['total_score'];
            }
            return $a['id'] <=> $b['id'];
        });

        // ── 2. WINNER SELECTION — eligible products only ─────────────────────
        $eligibleProducts = array_values(array_filter($analyzedProducts, fn($p) => $p['eligible'] === true));

        if (empty($eligibleProducts)) {
            $verificationRequiredList = array_values(
                array_filter($analyzedProducts, fn($p) => $p['verification_required'] === true)
            );

            return [
                'success' => true,
                'winner'  => null,
                'category' => $catKey,
                'persona'  => $persona,
                'message'  => 'Không có sản phẩm nào đáp ứng đầy đủ yêu cầu hợp lệ để so sánh.',
                'suggestion' => 'Vui lòng kiểm tra lại ngân sách, yêu cầu tối thiểu hoặc tính hợp lệ của sản phẩm.',
                'products' => $analyzedProducts,
                'winners'  => ['best_fit' => null, 'best_value' => null, 'best_performance' => null],
                'reasons_by_product' => $reasonsByProduct,
                'verification_required' => array_column($verificationRequiredList, 'id'),
                'analysis' => self::fallbackAnalysis($catKey, $persona),
            ];
        }

        $bestFit = $eligibleProducts[0];

        $bestValue = null;
        $maxValRatio = -1;
        foreach ($eligibleProducts as $cand) {
            if ($cand['id'] === $bestFit['id']) continue;
            $ep    = (float)$cand['effective_price'];
            $ratio = ($cand['total_score'] * 1_000_000) / max(1, $ep);
            if ($ratio > $maxValRatio || ($ratio === $maxValRatio && $cand['id'] < ($bestValue['id'] ?? PHP_INT_MAX))) {
                $maxValRatio = $ratio;
                $bestValue   = $cand;
            }
        }
        if (!$bestValue) $bestValue = $eligibleProducts[1] ?? $bestFit;

        $bestPerf = null;
        $maxPerfScore = -1;
        foreach ($eligibleProducts as $cand) {
            if ($cand['id'] === $bestFit['id'] || $cand['id'] === $bestValue['id']) continue;
            $perfScore = $cand['score_breakdown']['performance_fit'] + $cand['score_breakdown']['persona_fit'];
            if ($perfScore > $maxPerfScore || ($perfScore === $maxPerfScore && $cand['id'] < ($bestPerf['id'] ?? PHP_INT_MAX))) {
                $maxPerfScore = $perfScore;
                $bestPerf     = $cand;
            }
        }
        if (!$bestPerf) $bestPerf = $eligibleProducts[2] ?? ($eligibleProducts[1] ?? $bestFit);

        $winners = [
            'best_fit'         => (int)$bestFit['id'],
            'best_value'       => (int)$bestValue['id'],
            'best_performance' => (int)$bestPerf['id'],
        ];

        // ── 3. SERVER VALIDATE AI WINNER IDs ────────────────────────────────
        $eligibleIds = array_column($eligibleProducts, 'id');
        foreach ($winners as $role => $wid) {
            if ($wid !== null && !in_array($wid, $eligibleIds, true)) {
                $winners[$role] = null;
            }
        }

        // ── 4. AI EXPLANATION ───────────────────────────────────────────────
        $aiAnalysis = self::generateAiComparisonExplanation($analyzedProducts, $winners, [
            'category'  => $catKey,
            'persona'   => $persona,
            'priorities' => $priorities,
        ]);

        return [
            'success'    => true,
            'winner'     => $winners['best_fit'],
            'category'   => $catKey,
            'persona'    => $persona,
            'priorities' => $priorities,
            'products'   => $analyzedProducts,
            'winners'    => $winners,
            'reasons_by_product' => $reasonsByProduct,
            'analysis'   => $aiAnalysis,
        ];
    }

    // ==========================================
    // DETERMINISTIC SCORING ENGINE
    // ==========================================

    private static function calcPersonaFit(string $cat, string $persona, array $p, array $specs): int
    {
        $name  = mb_strtolower($p['name']);
        $score = 25;

        if ($cat === 'prebuilt_pc') {
            if ($persona === 'aaa_gaming' && (str_contains($name, 'rtx 4070') || str_contains($name, 'rtx 4080') || str_contains($name, 'rtx 4090'))) {
                $score += 10;
            } elseif ($persona === 'esports' && (str_contains($name, 'rtx 4060') || str_contains($name, 'i5') || str_contains($name, 'ryzen 5'))) {
                $score += 8;
            } elseif ($persona === 'ai_ml' && str_contains($name, 'rtx 40')) {
                $score += 10;
            } else {
                $isOfficePersona      = ($persona === 'office');
                $matchesOfficeKeyword = (str_contains($name, 'i3') || str_contains($name, 'văn phòng'));
                $needsOfficeWorkload  = ($isOfficePersona && $matchesOfficeKeyword);
                if ($needsOfficeWorkload) {
                    $score += 8;
                }
            }
        } elseif ($cat === 'laptop') {
            if ($persona === 'gamer' && (str_contains($name, 'gaming') || str_contains($name, 'rtx'))) {
                $score += 10;
            } elseif ($persona === 'developer' && (str_contains($name, 'i7') || str_contains($name, 'ryzen 7') || str_contains($name, '32gb'))) {
                $score += 8;
            } elseif ($persona === 'business_travel' && (str_contains($name, 'slim') || str_contains($name, 'oled') || str_contains($name, 'zenbook'))) {
                $score += 8;
            } else {
                $isOfficePersona      = ($persona === 'office');
                $matchesOfficeKeyword = (str_contains($name, 'i3') || str_contains($name, 'văn phòng'));
                $needsOfficeWorkload  = ($isOfficePersona && $matchesOfficeKeyword);
                if ($needsOfficeWorkload) {
                    $score += 8;
                }
            }
        } elseif ($cat === 'monitor') {
            if ($persona === 'esports' && (str_contains($name, '180hz') || str_contains($name, '240hz'))) {
                $score += 10;
            } elseif ($persona === 'creator' && (str_contains($name, '4k') || str_contains($name, '2k') || str_contains($name, 'ips'))) {
                $score += 8;
            }
        }

        return min(35, $score);
    }

    private static function calcPerformanceFit(string $cat, array $p, array $specs): int
    {
        $name  = mb_strtolower($p['name']);
        $score = 18;
        if (str_contains($name, 'i9') || str_contains($name, 'ryzen 9') || str_contains($name, 'rtx 4080') || str_contains($name, 'rtx 4090')) $score = 25;
        elseif (str_contains($name, 'i7') || str_contains($name, 'ryzen 7') || str_contains($name, 'rtx 4070')) $score = 22;
        elseif (str_contains($name, 'i5') || str_contains($name, 'ryzen 5') || str_contains($name, 'rtx 4060')) $score = 20;
        return min(25, $score);
    }

    private static function calcValueFit(float $price, int $perfScore): int
    {
        if ($price <= 0) return 10;
        $valRatio = ($perfScore * 1_000_000) / $price;
        if ($valRatio >= 1.0) return 15;
        if ($valRatio >= 0.7) return 12;
        return 9;
    }

    private static function calcLongevityUpgrade(string $cat, array $p, array $specs): int
    {
        $name = mb_strtolower($p['name']);
        if ($cat === 'prebuilt_pc') return 10;
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
        $config      = require ROOT_PATH . '/config/product-comparison.php';
        $excludedKeys = $config['excluded_spec_keys'] ?? [];

        $cleaned = [];

        if (isset($rawSpecs['attributes']) && is_array($rawSpecs['attributes'])) {
            foreach ($rawSpecs['attributes'] as $k => $v) {
                if (in_array($k, $excludedKeys)) continue;
                $cleaned[self::humanizeSpecKey($k)] = is_array($v)
                    ? implode(' / ', array_filter(array_map('strval', $v)))
                    : (string)$v;
            }
        }

        foreach ($rawSpecs as $k => $v) {
            if (in_array($k, $excludedKeys)) continue;
            if ($k === 'attributes') continue;
            $cleaned[self::humanizeSpecKey($k)] = is_array($v)
                ? implode(' / ', array_filter(array_map('strval', $v)))
                : (string)$v;
        }

        if (!isset($cleaned['CPU']) && preg_match('/(Intel Core i\d[-\w]*|AMD Ryzen \d[-\w]*|Apple M\d\w*)/i', $p['name'], $m)) {
            $cleaned['CPU'] = $m[1];
        }
        if (!isset($cleaned['VGA']) && preg_match('/(RTX \d{4}[-\w]*|GTX \d{4}[-\w]*|Radeon RX \d{4}[-\w]*)/i', $p['name'], $m)) {
            $cleaned['VGA'] = $m[1];
        }

        return $cleaned;
    }

    private static function humanizeSpecKey(string $k): string
    {
        $map = [
            'cpu'              => 'CPU',
            'ram'              => 'RAM',
            'ssd'              => 'SSD',
            'storage'          => 'Storage',
            'vga'              => 'Card đồ họa (VGA)',
            'screen_size'      => 'Kích thước màn hình',
            'screen_size_inch' => 'Kích thước màn hình',
            'resolution'       => 'Độ phân giải',
            'refresh_rate'     => 'Tần số quét',
            'refresh_rate_hz'  => 'Tần số quét',
            'panel'            => 'Tấm nền',
            'panel_type'       => 'Tấm nền',
            'weight'           => 'Trọng lượng',
            'battery'          => 'Dung lượng pin',
            'psu'              => 'Nguồn PSU',
            'mainboard'        => 'Bo mạch chủ',
            'wattage'          => 'Công suất',
            'efficiency'       => 'Chuẩn 80 Plus',
        ];
        return $map[strtolower($k)] ?? ucfirst($k);
    }

    public static function extractNumericVal(string $str): float
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
            if ($cat === 'laptop' && (float)($p['effective_price'] ?? $p['price']) > 30000000) {
                $w[] = "Mức giá ở phân khúc cao cấp";
            } else {
                $w[] = "Cần kiểm tra dung lượng ổ cứng thực tế theo nhu cầu cá nhân";
            }
        }
        return array_slice($w, 0, 2);
    }

    // ==========================================
    // FPS ESTIMATES
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
        $promptText  = "Bạn là Chuyên gia So sánh Phần cứng Máy tính TechPilot. Dưới đây là kết quả so sánh 2-4 sản phẩm đã được hệ thống backend chấm điểm xác định:\n\n";
        $promptText .= "Bối cảnh:\n";
        $promptText .= "- Danh mục: " . $context['category'] . "\n";
        $promptText .= "- Đối tượng / Persona: " . $context['persona'] . "\n\n";
        $promptText .= "Danh sách sản phẩm so sánh:\n";
        foreach ($products as $p) {
            $isBestFit  = $p['id'] === $winners['best_fit']         ? ' [PHÙ HỢP NHẤT]'    : '';
            $isBestVal  = $p['id'] === $winners['best_value']       ? ' [ĐÁNG TIỀN NHẤT]'  : '';
            $isBestPerf = $p['id'] === $winners['best_performance'] ? ' [HIỆU NĂNG CAO NHẤT]' : '';
            $ep = isset($p['effective_price']) ? formatPrice((float)$p['effective_price']) : formatPrice((float)$p['price']);
            $promptText .= "- ID {$p['id']}: {$p['name']} ({$ep}) - Score: {$p['total_score']}/100{$isBestFit}{$isBestVal}{$isBestPerf}\n";
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
            $cleanJson   = trim(preg_replace('/^```json\s*|\s*```$/i', '', trim($rawResponse)));
            $parsed      = json_decode($cleanJson, true);

            if (is_array($parsed) && isset($parsed['summary'])) {
                return $parsed;
            }
        } catch (Exception $e) {
            // Fallback
        }

        return self::fallbackAnalysis($context['category'], $context['persona']);
    }

    private static function fallbackAnalysis(string $category, string $persona): array
    {
        return [
            'summary'         => "Dựa trên phân tích kỹ thuật theo Persona {$persona}, hệ thống đã xác định kết quả so sánh theo danh mục {$category}.",
            'who_should_buy'  => "• Nếu bạn cần sự an tâm và cân bằng nhất: Hãy chọn mẫu Phù hợp nhất.\n• Nếu bạn muốn tối ưu hóa từng đồng chi phí: Hãy chọn mẫu Đáng tiền nhất.",
            'who_should_avoid' => "• Nên tránh các mẫu vượt quá hạn mức tài chính hoặc có cấu hình chưa đạt yêu cầu tối thiểu.",
            'tradeoffs'       => "• Các mẫu có hiệu năng cao hơn thường có mức giá tiệm cận trần ngân sách hoặc tỏa nhiều nhiệt hơn.",
        ];
    }
}
