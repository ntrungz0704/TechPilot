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
        // Chỉ ước tính cho Laptop (category 1, 2) hoặc PC (category 3, 6)
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

        // 1. Liên Minh Huyền Thoại (LOL)
        if ($isIntegrated) {
            $games['LOL'] = ['name' => 'Liên Minh Huyền Thoại', 'settings' => 'Trung bình 1080p', 'fps' => '80 - 100 FPS', 'status' => 'Mượt'];
        } elseif ($isMidVga) {
            $games['LOL'] = ['name' => 'Liên Minh Huyền Thoại', 'settings' => 'Cực cao 1080p', 'fps' => '140 - 180 FPS', 'status' => 'Cực mượt'];
        } else {
            $games['LOL'] = ['name' => 'Liên Minh Huyền Thoại', 'settings' => 'Cực cao 1440p', 'fps' => '200+ FPS', 'status' => 'Cực mượt'];
        }

        // 2. Valorant
        if ($isIntegrated) {
            $games['Valorant'] = ['name' => 'Valorant', 'settings' => 'Thấp 1080p', 'fps' => '60 - 80 FPS', 'status' => 'Chơi ổn'];
        } elseif ($isMidVga) {
            $games['Valorant'] = ['name' => 'Valorant', 'settings' => 'Cao 1080p', 'fps' => '120 - 160 FPS', 'status' => 'Mượt'];
        } else {
            $games['Valorant'] = ['name' => 'Valorant', 'settings' => 'Cao 1080p', 'fps' => '240+ FPS', 'status' => 'Cực mượt'];
        }

        // 3. GTA V
        if ($isIntegrated) {
            $games['GTAV'] = ['name' => 'GTA V', 'settings' => 'Thấp 720p/1080p', 'fps' => '30 - 45 FPS', 'status' => 'Hơi lag'];
        } elseif ($isMidVga) {
            $games['GTAV'] = ['name' => 'GTA V', 'settings' => 'Cao 1080p', 'fps' => '70 - 90 FPS', 'status' => 'Mượt'];
        } else {
            $games['GTAV'] = ['name' => 'GTA V', 'settings' => 'Rất cao 1080p', 'fps' => '100 - 130 FPS', 'status' => 'Cực mượt'];
        }

        // 4. Counter-Strike 2 (CS2)
        if ($isIntegrated) {
            $games['CS2'] = ['name' => 'Counter-Strike 2', 'settings' => 'Thấp 1080p', 'fps' => '30 - 45 FPS', 'status' => 'Khó chơi'];
        } elseif ($isMidVga) {
            $games['CS2'] = ['name' => 'CS2', 'settings' => 'Trung bình 1080p', 'fps' => '80 - 110 FPS', 'status' => 'Mượt'];
        } else {
            $games['CS2'] = ['name' => 'CS2', 'settings' => 'Cao 1080p', 'fps' => '150 - 200 FPS', 'status' => 'Cực mượt'];
        }

        // 5. Cyberpunk 2077
        if ($isIntegrated) {
            $games['CP2077'] = ['name' => 'Cyberpunk 2077', 'settings' => 'Thấp 720p (FSR)', 'fps' => '20 - 28 FPS', 'status' => 'Không mượt'];
        } elseif ($isMidVga) {
            $games['CP2077'] = ['name' => 'Cyberpunk 2077', 'settings' => 'Thấp/Trung bình 1080p', 'fps' => '40 - 55 FPS', 'status' => 'Chơi ổn'];
        } else {
            $games['CP2077'] = ['name' => 'Cyberpunk 2077', 'settings' => 'Cao 1080p (DLSS)', 'fps' => '75 - 95 FPS', 'status' => 'Mượt'];
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

        // Chấm điểm phần cứng thô
        $hwScore = 50; // Điểm cơ bản
        
        // Cộng điểm RAM
        if ($ram >= 32) $hwScore += 25;
        elseif ($ram >= 16) $hwScore += 15;
        else $hwScore += 5;

        // Cộng điểm CPU
        if (strpos($cpu, 'i9') !== false || strpos($cpu, 'ryzen 9') !== false || strpos($cpu, 'ultra 9') !== false) {
            $hwScore += 30;
        } elseif (strpos($cpu, 'i7') !== false || strpos($cpu, 'ryzen 7') !== false || strpos($cpu, 'ultra 7') !== false) {
            $hwScore += 20;
        } elseif (strpos($cpu, 'i5') !== false || strpos($cpu, 'ryzen 5') !== false || strpos($cpu, 'ultra 5') !== false) {
            $hwScore += 12;
        } else {
            $hwScore += 5;
        }

        // Cộng điểm VGA
        if (strpos($vga, '4080') !== false || strpos($vga, '4090') !== false) {
            $hwScore += 35;
        } elseif (strpos($vga, '4070') !== false || strpos($vga, '4060') !== false) {
            $hwScore += 25;
        } elseif (strpos($vga, '3060') !== false || strpos($vga, '3050') !== false || strpos($vga, '1660') !== false || strpos($vga, '1650') !== false) {
            $hwScore += 15;
        }

        // Công thức tính đáng giá = (Điểm cấu hình / Giá trong triệu) * hệ số điều chỉnh
        $priceInMillions = $price / 1000000;
        if ($priceInMillions <= 0) $priceInMillions = 1;

        $ratio = ($hwScore / $priceInMillions) * 1.5;
        
        // Điều chỉnh theo phân khúc giá để tránh lệch
        $vfm = 5.0 + $ratio;
        if ($vfm > 9.8) $vfm = 9.8 - ($priceInMillions * 0.01); // Càng đắt thì điểm đáng giá giảm nhẹ
        if ($vfm < 4.0) $vfm = 4.0;

        return round($vfm, 1);
    }

    /**
     * Tính toán phân loại hiệu năng trên giá Performance per Price
     */
    public static function calculatePerformancePriceRatio(array $product): array
    {
        $vfm = self::calculateValueForMoney($product);

        if ($vfm >= 8.5) {
            return ['label' => 'Rất cao', 'class' => 'vfm-high', 'text' => 'P/P rất tốt'];
        } elseif ($vfm >= 7.0) {
            return ['label' => 'Cao', 'class' => 'vfm-good', 'text' => 'P/P tốt'];
        } elseif ($vfm >= 5.5) {
            return ['label' => 'Khá', 'class' => 'vfm-med', 'text' => 'P/P trung bình'];
        } else {
            return ['label' => 'Trung bình', 'class' => 'vfm-low', 'text' => 'P/P thấp'];
        }
    }

    /**
     * Phân tích và so sánh 2-4 sản phẩm bằng AI
     */
    public static function analyzeComparison(array $products): array
    {
        $contextProducts = [];
        $promptText = "Bạn là chuyên gia tư vấn phần cứng máy tính tại TechPilot. Hãy thực hiện so sánh chi tiết các sản phẩm sau đây:\n\n";

        foreach ($products as $i => $p) {
            $specs = json_decode($p['specs'] ?? '{}', true) ?: [];
            $specsStr = implode(', ', array_map(function($k, $v) { return "$k: $v"; }, array_keys($specs), $specs));
            
            $pData = [
                'id' => $p['id'],
                'name' => $p['name'],
                'price' => (float)$p['price'],
                'specs' => $specs,
                'brand' => $p['brand_name'] ?? 'N/A'
            ];
            $contextProducts[] = $pData;

            $promptText .= "Sản phẩm " . ($i + 1) . " (ID: " . $p['id'] . "):\n";
            $promptText .= "- Tên: " . $p['name'] . "\n";
            $promptText .= "- Hãng: " . ($p['brand_name'] ?? 'N/A') . "\n";
            $promptText .= "- Giá: " . number_format($p['price'], 0, ',', '.') . "đ\n";
            $promptText .= "- Thông số: " . $specsStr . "\n\n";
        }

        $promptText .= "Yêu cầu bạn phân tích các ý sau:\n";
        $promptText .= "1. Sự khác nhau chính giữa các sản phẩm là ở đâu?\n";
        $promptText .= "2. Sản phẩm nào có hiệu năng xử lý (CPU/GPU) mạnh mẽ nhất?\n";
        $promptText .= "3. Sản phẩm nào mang tính tiết kiệm/tối ưu ngân sách nhất?\n";
        $promptText .= "4. Sản phẩm nào phù hợp nhất cho các nhu cầu: Học tập, Văn phòng, Gaming hay Đồ họa thiết kế?\n";
        $promptText .= "5. Mức chênh lệch giá tiền giữa các sản phẩm có xứng đáng với cấu hình mang lại hay không?\n";
        $promptText .= "6. Cuối cùng, hãy đưa ra đề xuất lựa chọn tối ưu nhất và giải thích ngắn gọn tại sao.\n\n";
        $promptText .= "Lưu ý:\n";
        $promptText .= "- Chỉ so sánh dựa trên dữ liệu thật đã cung cấp. KHÔNG tự tạo cấu hình hoặc giá ảo.\n";
        $promptText .= "- Câu trả lời viết bằng tiếng Việt có định dạng Markdown sạch đẹp.\n";
        $promptText .= "- Ở dòng CUỐI CÙNG của câu trả lời, hãy in thẻ sau: `[RECOMMENDED_ID: x]` (với x là ID của sản phẩm được bạn đề xuất nhất). Thẻ này cực kỳ quan trọng để hệ thống lập trình nhận diện.";

        // Gọi API Gemini (hoặc Mock)
        $rawResponse = GeminiService::callGemini($promptText, [
            'type' => 'comparison',
            'products' => $contextProducts
        ]);

        // Nếu là JSON từ Mock
        $decoded = json_decode($rawResponse, true);
        if (is_array($decoded) && isset($decoded['analysis'])) {
            return [
                'analysis' => $decoded['analysis'],
                'recommended_id' => (int)$decoded['recommended_id']
            ];
        }

        // Parse ID được đề xuất từ thẻ [RECOMMENDED_ID: x]
        $recommendedId = $products[0]['id'];
        if (preg_match('/\[RECOMMENDED_ID:\s*(\d+)\]/', $rawResponse, $matches)) {
            $recommendedId = (int)$matches[1];
        }

        // Loại bỏ thẻ khỏi chuỗi hiển thị
        $cleanAnalysis = preg_replace('/\[RECOMMENDED_ID:\s*\d+\]/', '', $rawResponse);

        return [
            'analysis' => trim($cleanAnalysis),
            'recommended_id' => $recommendedId
        ];
    }

    /**
     * Đề xuất sản phẩm theo hồ sơ người dùng
     */
    public static function recommendProducts(array $filters, array $candidates): array
    {
        $contextCandidates = [];
        $promptText = "Bạn là trợ lý AI thông minh tư vấn mua hàng tại TechPilot. Dưới đây là hồ sơ nhu cầu của khách hàng:\n";
        $promptText .= "- Ngân sách tối đa: " . number_format($filters['budget_val'], 0, ',', '.') . "đ\n";
        $promptText .= "- Nhóm thiết bị cần tìm: " . $filters['category_name'] . "\n";
        $promptText .= "- Mục đích sử dụng chính: " . $filters['purpose'] . "\n";
        $promptText .= "- Phần mềm/Game thường chạy: " . ($filters['software'] ?: 'Không ghi nhận') . "\n";
        $promptText .= "- Tiêu chí ưu tiên: " . $filters['priority'] . "\n";
        $promptText .= "- Thương hiệu yêu thích: " . ($filters['brand'] ?: 'Tùy ý') . "\n";
        $promptText .= "- Loại trừ (không muốn mua): " . ($filters['excluded'] ?: 'Không có') . "\n\n";

        $promptText .= "Dưới đây là danh sách " . count($candidates) . " sản phẩm ứng viên có sẵn trong kho hàng (đã được PHP lọc phù hợp sơ bộ):\n\n";

        foreach ($candidates as $i => $c) {
            $specs = json_decode($c['specs'] ?? '{}', true) ?: [];
            $specsStr = implode(', ', array_map(function($k, $v) { return "$k: $v"; }, array_keys($specs), $specs));
            
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

        $promptText .= "Nhiệm vụ của bạn:\n";
        $promptText .= "1. Từ danh sách ứng viên trên, hãy chọn ra đúng 3 sản phẩm tương ứng với 3 nhãn:\n";
        $promptText .= "   - Phù hợp nhất (BEST_ID): Sản phẩm cân đối hoàn hảo nhất cho nhu cầu của khách hàng.\n";
        $promptText .= "   - Tiết kiệm (SAVING_ID): Sản phẩm giá mềm nhất nhưng vẫn đáp ứng cơ bản nhu cầu của khách.\n";
        $promptText .= "   - Hiệu năng cao (PERF_ID): Sản phẩm mạnh mẽ nhất phục vụ tối đa nhu cầu của khách (nằm trong tầm giá).\n";
        $promptText .= "2. Viết bài phân tích ngắn gọn lý do tại sao lại chọn 3 sản phẩm đó và điểm cần đánh đổi của từng sản phẩm là gì.\n";
        $promptText .= "3. Chỉ chọn ID có trong danh sách ứng viên trên. Tuyệt đối KHÔNG tự sáng chế sản phẩm hoặc ID ảo.\n\n";
        $promptText .= "Hãy định dạng kết quả theo Markdown và ở dòng CUỐI CÙNG ghi thẻ định danh dạng: `[BEST_ID: x] [SAVING_ID: y] [PERF_ID: z]` (với x, y, z là các ID được chọn).";

        // Gọi API
        $rawResponse = GeminiService::callGemini($promptText, [
            'type' => 'recommendation',
            'candidates' => $contextCandidates,
            'filters' => $filters
        ]);

        // Nếu là JSON từ Mock
        $decoded = json_decode($rawResponse, true);
        if (is_array($decoded) && isset($decoded['best_id'])) {
            return [
                'best_id' => (int)$decoded['best_id'],
                'saving_id' => (int)$decoded['saving_id'],
                'perf_id' => (int)$decoded['perf_id'],
                'reasons' => $decoded['reasons'],
                'tradeoffs' => $decoded['tradeoffs']
            ];
        }

        // Parse các ID
        $bestId = $candidates[0]['id'];
        $savingId = $candidates[0]['id'];
        $perfId = $candidates[0]['id'];

        if (preg_match('/\[BEST_ID:\s*(\d+)\]/', $rawResponse, $m1)) $bestId = (int)$m1[1];
        if (preg_match('/\[SAVING_ID:\s*(\d+)\]/', $rawResponse, $m2)) $savingId = (int)$m2[1];
        if (preg_match('/\[PERF_ID:\s*(\d+)\]/', $rawResponse, $m3)) $perfId = (int)$m3[1];

        // Tách lý do và điểm đánh đổi từ văn bản phản hồi
        $cleanText = preg_replace('/\[(BEST|SAVING|PERF)_ID:\s*\d+\]/', '', $rawResponse);
        $parts = explode('đánh đổi', strtolower($cleanText));
        
        $reasons = trim($cleanText);
        $tradeoffs = "• Cân nhắc nâng cấp RAM hoặc lưu trữ SSD sau này tùy nhu cầu thực tế của bạn.";

        if (count($parts) > 1) {
            $reasons = trim(substr($cleanText, 0, strlen($cleanText) - strlen($parts[1]) - 15));
            $tradeoffs = "• " . trim($parts[1]);
        }

        return [
            'best_id' => $bestId,
            'saving_id' => $savingId,
            'perf_id' => $perfId,
            'reasons' => $reasons,
            'tradeoffs' => $tradeoffs
        ];
    }

    /**
     * Chat hỏi đáp theo từng sản phẩm cụ thể
     */
    public static function chatProduct(array $product, string $question): string
    {
        $specs = json_decode($product['specs'] ?? '{}', true) ?: [];
        $specsStr = implode(', ', array_map(function($k, $v) { return "$k: $v"; }, array_keys($specs), $specs));

        $promptText = "Bạn là trợ lý ảo TechPilot AI. Khách hàng đang xem trang sản phẩm sau và hỏi bạn một câu hỏi:\n\n";
        $promptText .= "Sản phẩm: " . $product['name'] . "\n";
        $promptText .= "Hãng: " . ($product['brand_name'] ?? 'N/A') . "\n";
        $promptText .= "Giá bán: " . number_format($product['price'], 0, ',', '.') . "đ\n";
        $promptText .= "Cấu hình chi tiết: " . $specsStr . "\n\n";
        $promptText .= "Câu hỏi của khách hàng: \"" . $question . "\"\n\n";
        $promptText .= "Hãy trả lời câu hỏi của khách hàng một cách ngắn gọn, thân thiện, trung thực dựa trên cấu hình thật của máy. Có thể ước tính FPS game hoặc tư vấn khả năng chạy phần mềm đồ họa, nâng cấp RAM/SSD nếu câu hỏi liên quan.";

        return GeminiService::callGemini($promptText, [
            'type' => 'product_chat',
            'product' => $product
        ]);
    }
}
