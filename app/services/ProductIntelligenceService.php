<?php
/**
 * Dịch vụ tính toán hiệu năng, phân tích đáng tiền và chuẩn bị prompt AI
 */

class ProductIntelligenceService
{
    /**
     * Ước tính FPS cho các game phổ biến dựa trên cấu hình Laptop/PC
     */
    public static function estimateFps(array $specs, string $categorySlug): array
    {
        $slugLower = strtolower($categorySlug);
        if (strpos($slugLower, 'laptop') === false && strpos($slugLower, 'pc') === false && strpos($slugLower, 'máy tính') === false) {
            return [];
        }

        $cpu = strtolower($specs['CPU'] ?? $specs['cpu'] ?? '');
        $ram = (int)filter_var($specs['RAM'] ?? $specs['ram'] ?? '8', FILTER_SANITIZE_NUMBER_INT);
        $vga = strtolower($specs['VGA'] ?? $specs['vga'] ?? '');
        
        $isHighVga = (strpos($vga, '4060') !== false || strpos($vga, '4070') !== false || strpos($vga, '4080') !== false || strpos($vga, '4090') !== false || strpos($vga, '7700') !== false || strpos($vga, '7800') !== false);
        $isMidVga = (strpos($vga, '3050') !== false || strpos($vga, '3060') !== false || strpos($vga, '2060') !== false || strpos($vga, '1660') !== false || strpos($vga, '1650') !== false || strpos($vga, '6600') !== false);
        $isIntegrated = !$isHighVga && !$isMidVga;

        $games = [];

        if ($isIntegrated) {
            $games['LOL'] = ['name' => 'Liên Minh Huyền Thoại', 'settings' => 'Trung bình 1080p', 'fps' => '80 - 100 FPS', 'status' => 'Mượt'];
        } elseif ($isMidVga) {
            $games['LOL'] = ['name' => 'Liên Minh Huyền Thoại', 'settings' => 'Cực cao 1080p', 'fps' => '140 - 180 FPS', 'status' => 'Cực mượt'];
        } else {
            $games['LOL'] = ['name' => 'Liên Minh Huyền Thoại', 'settings' => 'Cực cao 1440p', 'fps' => '200+ FPS', 'status' => 'Cực mượt'];
        }

        if ($isIntegrated) {
            $games['Valorant'] = ['name' => 'Valorant', 'settings' => 'Thấp 1080p', 'fps' => '60 - 80 FPS', 'status' => 'Chơi ổn'];
        } elseif ($isMidVga) {
            $games['Valorant'] = ['name' => 'Valorant', 'settings' => 'Cao 1080p', 'fps' => '120 - 160 FPS', 'status' => 'Mượt'];
        } else {
            $games['Valorant'] = ['name' => 'Valorant', 'settings' => 'Cao 1080p', 'fps' => '240+ FPS', 'status' => 'Cực mượt'];
        }

        if ($isIntegrated) {
            $games['GTAV'] = ['name' => 'GTA V', 'settings' => 'Thấp 720p/1080p', 'fps' => '30 - 45 FPS', 'status' => 'Hơi lag'];
        } elseif ($isMidVga) {
            $games['GTAV'] = ['name' => 'GTA V', 'settings' => 'Cao 1080p', 'fps' => '70 - 90 FPS', 'status' => 'Mượt'];
        } else {
            $games['GTAV'] = ['name' => 'GTA V', 'settings' => 'Rất cao 1080p', 'fps' => '100 - 130 FPS', 'status' => 'Cực mượt'];
        }

        return $games;
    }

    /**
     * Tính toán điểm đáng tiền "Value for Money Score" từ 1.0 đến 10.0
     */
    public static function calculateValueForMoney(array $product): float
    {
        $price = (float)($product['price'] ?? 0);
        if ($price <= 0) return 5.0;

        $specs = json_decode($product['specs'] ?? '{}', true) ?: [];
        $cpu = strtolower($specs['CPU'] ?? $specs['cpu'] ?? '');
        $ram = (int)filter_var($specs['RAM'] ?? $specs['ram'] ?? '8', FILTER_SANITIZE_NUMBER_INT);
        $vga = strtolower($specs['VGA'] ?? $specs['vga'] ?? '');

        $hwScore = 50;
        
        if ($ram >= 32) $hwScore += 25;
        elseif ($ram >= 16) $hwScore += 15;
        else $hwScore += 5;

        if (strpos($cpu, 'i9') !== false || strpos($cpu, 'ryzen 9') !== false || strpos($cpu, 'ultra 9') !== false) {
            $hwScore += 30;
        } elseif (strpos($cpu, 'i7') !== false || strpos($cpu, 'ryzen 7') !== false || strpos($cpu, 'ultra 7') !== false) {
            $hwScore += 20;
        } elseif (strpos($cpu, 'i5') !== false || strpos($cpu, 'ryzen 5') !== false || strpos($cpu, 'ultra 5') !== false) {
            $hwScore += 12;
        }

        if (strpos($vga, '4090') !== false || strpos($vga, '4080') !== false) {
            $hwScore += 35;
        } elseif (strpos($vga, '4070') !== false || strpos($vga, '7800') !== false) {
            $hwScore += 25;
        } elseif (strpos($vga, '4060') !== false || strpos($vga, '3060') !== false || strpos($vga, '7700') !== false) {
            $hwScore += 18;
        } elseif (strpos($vga, '3050') !== false || strpos($vga, '1650') !== false) {
            $hwScore += 10;
        }

        $priceMillions = $price / 1000000;
        $vfmRatio = ($hwScore / max(1, $priceMillions)) * 2.2;

        if ($vfmRatio > 9.8) $vfmRatio = 9.8;
        if ($vfmRatio < 5.5) $vfmRatio = 5.5;

        return round($vfmRatio, 1);
    }

    /**
     * Tỷ lệ Hiệu năng / Giá (P/P)
     */
    public static function calculatePerformancePriceRatio(array $product): array
    {
        $vfm = self::calculateValueForMoney($product);
        if ($vfm >= 8.5) {
            return ['label' => 'Rất cao', 'class' => 'vfm-high'];
        } elseif ($vfm >= 7.0) {
            return ['label' => 'Tốt', 'class' => 'vfm-good'];
        } elseif ($vfm >= 6.0) {
            return ['label' => 'Trung bình', 'class' => 'vfm-med'];
        }
        return ['label' => 'Cơ bản', 'class' => 'vfm-low'];
    }

    /**
     * Chat AI về sản phẩm cụ thể — trả lời câu hỏi khách hàng trên trang chi tiết sản phẩm
     */
    public static function chatProduct(array $product, string $question): string
    {
        require_once ROOT_PATH . '/app/services/GeminiService.php';

        $specs = json_decode($product['specs'] ?? '{}', true) ?: [];

        // Unwrap nested specs (PC build format)
        if (isset($specs['specs']) && is_array($specs['specs'])) {
            $innerSpecs = $specs['specs'];
            unset($specs['specs']);
            $specs = array_merge($specs, $innerSpecs);
        }

        // Remove meta keys
        $skipKeys = ['schema_version', 'attributes', 'raw_specs', 'vfm_score', 'category_slug', 'model', 'compatibility', 'use_cases'];
        foreach ($skipKeys as $sk) {
            unset($specs[$sk]);
        }

        $specsParts = [];
        foreach ($specs as $k => $v) {
            $valStr = self::stringifySpecValue($v);
            if ($valStr !== '') {
                $specsParts[] = "$k: $valStr";
            }
        }
        $specsStr = implode(', ', $specsParts);

        $productName = $product['name'] ?? 'Sản phẩm';
        $price = isset($product['price']) ? number_format((float)$product['price'], 0, ',', '.') . 'đ' : 'Chưa cập nhật';
        $stock = (int)($product['stock'] ?? 0);
        $brandName = $product['brand_name'] ?? '';
        $categoryName = $product['category_name'] ?? '';

        $prompt  = "Bạn là Trợ lý ảo TechPilot AI, chuyên tư vấn công nghệ.\n";
        $prompt .= "Khách hàng đang xem sản phẩm: **{$productName}**\n";
        $prompt .= "- Thương hiệu: {$brandName}\n";
        $prompt .= "- Danh mục: {$categoryName}\n";
        $prompt .= "- Giá bán: {$price}\n";
        $prompt .= "- Tình trạng kho: " . ($stock > 0 ? "Còn hàng ({$stock} sản phẩm)" : "Hết hàng") . "\n";
        $prompt .= "- Thông số kỹ thuật: {$specsStr}\n\n";
        $prompt .= "Câu hỏi của khách: \"{$question}\"\n\n";
        $prompt .= "Hãy trả lời tự nhiên, thân thiện, chính xác dựa trên cấu hình thật của sản phẩm. Trả lời ngắn gọn 2-4 câu bằng tiếng Việt.";

        return GeminiService::callGemini($prompt, [
            'type' => 'product_chat',
            'product_id' => $product['id'] ?? 0
        ]);
    }

    /**
     * So sánh 2 hoặc nhiều sản phẩm bằng AI Gemini
     */
    public static function analyzeComparison(array $products): array
    {
        if (count($products) < 2) {
            throw new Exception("Cần ít nhất 2 sản phẩm để thực hiện so sánh.");
        }

        $promptText = "Bạn là chuyên gia phân tích công nghệ phần cứng của TechPilot. Hãy so sánh chi tiết các sản phẩm sau:\n\n";

        foreach ($products as $i => $p) {
            $specs = json_decode($p['specs'] ?? '{}', true) ?: [];
            $specsStr = implode(', ', array_map(function($k, $v) {
                $valStr = self::stringifySpecValue($v);
                return "$k: $valStr";
            }, array_keys($specs), $specs));

            $promptText .= "Sản phẩm " . ($i + 1) . " (ID: {$p['id']}):\n";
            $promptText .= "- Tên: " . $p['name'] . "\n";
            $promptText .= "- Giá: " . number_format($p['price'], 0, ',', '.') . "đ\n";
            $promptText .= "- Thông số: " . $specsStr . "\n\n";
        }

        $promptText .= "Yêu cầu phân tích:\n";
        $promptText .= "1. Đánh giá ưu điểm/nhược điểm từng máy dựa trên thông số thật.\n";
        $promptText .= "2. Đưa ra kết luận sản phẩm nào đáng mua nhất cho từng nhóm nhu cầu (Gaming, Đồ họa, Học tập).\n";
        $promptText .= "3. Định dạng bài viết bằng Markdown chuyên nghiệp.\n";
        $promptText .= "4. Cuối bài ghi rõ thẻ dạng: `[RECOMMENDED_ID: x]` (với x là ID sản phẩm khuyên dùng nhất).";

        $rawResponse = GeminiService::callGemini($promptText, [
            'type' => 'comparison',
            'products' => $products
        ]);

        $recId = $products[0]['id'];
        if (preg_match('/\[RECOMMENDED_ID:\s*(\d+)\]/', $rawResponse, $m)) {
            $recId = (int)$m[1];
        }

        $cleanAnalysis = preg_replace('/\[RECOMMENDED_ID:\s*\d+\]/', '', $rawResponse);

        return [
            'analysis' => trim($cleanAnalysis),
            'recommended_id' => $recId
        ];
    }

    /**
     * AI Đề xuất 3 sản phẩm tối ưu từ danh sách ứng viên
     */
    public static function recommendProducts(array $filters, array $candidates): array
    {
        if (empty($candidates)) {
            throw new Exception("Không có sản phẩm ứng viên để tư vấn.");
        }

        $contextCandidates = [];
        $promptText = "Bạn là Chuyên gia tư vấn phần cứng máy tính TechPilot. Dưới đây là tiêu chí chọn mua của khách hàng:\n";
        $promptText .= "- Ngân sách tối đa: " . number_format($filters['budget_val'], 0, ',', '.') . "đ\n";
        $promptText .= "- Loại sản phẩm: " . $filters['category_name'] . "\n";
        $promptText .= "- Nhu cầu/Đối tượng: " . $filters['purpose'] . "\n";
        $promptText .= "- Phần mềm/Game: " . ($filters['software'] ?: 'Không ghi nhận') . "\n";
        $promptText .= "- Tiêu chí ưu tiên: " . $filters['priority'] . "\n\n";

        $promptText .= "Danh sách " . count($candidates) . " sản phẩm ứng viên trong kho:\n\n";

        foreach ($candidates as $i => $c) {
            $specs = json_decode($c['specs'] ?? '{}', true) ?: [];
            $specsStr = implode(', ', array_map(function($k, $v) {
                $valStr = self::stringifySpecValue($v);
                return "$k: $valStr";
            }, array_keys($specs), $specs));
            
            $contextCandidates[] = [
                'id' => $c['id'],
                'name' => $c['name'],
                'price' => (float)$c['price'],
                'specs' => $specs
            ];

            $promptText .= "Ứng viên " . ($i + 1) . " (ID: " . $c['id'] . "):\n";
            $promptText .= "- Tên: " . $c['name'] . "\n";
            $promptText .= "- Giá: " . number_format($c['price'], 0, ',', '.') . "đ\n";
            $promptText .= "- Thông số: " . $specsStr . "\n\n";
        }

        $promptText .= "Hãy chọn ra đúng 3 sản phẩm tương ứng với 3 nhãn: BEST_ID (Phù hợp nhất), SAVING_ID (Tiết kiệm nhất), PERF_ID (Hiệu năng cao nhất).\n";
        $promptText .= "Hãy định dạng kết quả theo Markdown và ở dòng CUỐI CÙNG ghi thẻ định danh dạng: `[BEST_ID: x] [SAVING_ID: y] [PERF_ID: z]`.";

        $rawResponse = GeminiService::callGemini($promptText, [
            'type' => 'recommendation',
            'candidates' => $contextCandidates,
            'filters' => $filters
        ]);

        $bestId = $candidates[0]['id'];
        $savingId = $candidates[0]['id'];
        $perfId = $candidates[0]['id'];

        if (preg_match('/\[BEST_ID:\s*(\d+)\]/', $rawResponse, $m1)) $bestId = (int)$m1[1];
        if (preg_match('/\[SAVING_ID:\s*(\d+)\]/', $rawResponse, $m2)) $savingId = (int)$m2[1];
        if (preg_match('/\[PERF_ID:\s*(\d+)\]/', $rawResponse, $m3)) $perfId = (int)$m3[1];

        $cleanText = preg_replace('/\[(BEST|SAVING|PERF)_ID:\s*\d+\]/', '', $rawResponse);
        $parts = explode('đánh đổi', strtolower($cleanText));
        
        $reasonsStr = trim($cleanText);
        $tradeoffsStr = "• Cân nhắc nâng cấp RAM hoặc lưu trữ SSD sau này tùy nhu cầu thực tế của bạn.";

        if (count($parts) > 1) {
            $reasonsStr = trim(substr($cleanText, 0, strlen($cleanText) - strlen($parts[1]) - 15));
            $tradeoffsStr = "• " . trim($parts[1]);
        }

        $reasonsArr = array_values(array_filter(explode("\n", $reasonsStr)));
        if (empty($reasonsArr)) {
            $reasonsArr = ["Lựa chọn tối ưu nhất trong tầm giá dựa trên phân tích cấu hình thực tế."];
        }

        $tradeoffsArr = array_values(array_filter(explode("\n", $tradeoffsStr)));
        if (empty($tradeoffsArr)) {
            $tradeoffsArr = ["Cân nhắc nâng cấp linh kiện sau này khi nhu cầu công việc mở rộng."];
        }

        return [
            'best_id' => $bestId,
            'saving_id' => $savingId,
            'perf_id' => $perfId,
            'reasons' => $reasonsArr,
            'tradeoffs' => $tradeoffsArr
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
