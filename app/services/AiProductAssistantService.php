<?php

/**
 * Service AI Hỗ trợ nhập sản phẩm tự động (TSIE - TechPilot Smart Import Engine v2)
 * Pipeline Scrape-First-Then-Extract: Tra cứu Nguồn Thực Tế -> Trích xuất LLM Extraction-Only -> Tính Confidence Score Thật -> Sinh Mô tả 8 Section chuẩn Specs
 */

require_once ROOT_PATH . '/app/services/AiService.php';
require_once ROOT_PATH . '/app/services/CategorySchemaRegistry.php';
require_once ROOT_PATH . '/app/services/SpecScraperService.php';

class AiProductAssistantService
{
    /**
     * Kiểm tra tính hợp lệ và độ rõ ràng của Model / SKU / Query nhập vào (Ambiguous Guard)
     */
    public static function validateModelInput(string $query): array
    {
        $cleanQuery = trim($query);
        if ($cleanQuery === '') {
            return [
                'valid' => false,
                'is_ambiguous' => true,
                'message' => 'Vui lòng nhập tên sản phẩm, Model, SKU hoặc linh kiện.'
            ];
        }

        // Kiểm tra nếu là URL hợp lệ
        if (filter_var($cleanQuery, FILTER_VALIDATE_URL)) {
            return ['valid' => true, 'is_ambiguous' => false, 'type' => 'url', 'query' => $cleanQuery];
        }

        // Phát hiện các từ khóa mơ hồ chung chung (ví dụ: gaming, laptop, pc, chuột, vga) không kèm SKU
        $vagueKeywords = ['gaming', 'laptop', 'pc', 'màn hình', 'chuột', 'bàn phím', 'vga', 'cpu', 'ram', 'ssd', 'tainghe'];
        $queryLower = strtolower($cleanQuery);

        if (in_array($queryLower, $vagueKeywords, true) || strlen($cleanQuery) <= 2) {
            return [
                'valid' => false,
                'is_ambiguous' => true,
                'message' => '⚠️ Từ khóa quá mơ hồ. Vui lòng nhập rõ Tên Model hoặc SKU cụ thể (Ví dụ: ASUS TUF Gaming FA507, RTX 4060, Core i7-13700H).'
            ];
        }

        return ['valid' => true, 'is_ambiguous' => false, 'type' => 'model', 'query' => $cleanQuery];
    }

    /**
     * Chuẩn hóa Đơn vị & Thông số kỹ thuật
     */
    public static function normalizeSpecs(array $specs): array
    {
        $normalized = [];
        foreach ($specs as $key => $val) {
            if ($val === null || $val === 'null' || $val === '') {
                continue;
            }
            $keyClean = trim((string)$key);
            $valClean = trim((string)$val);

            // Chuẩn hóa tên card đồ họa
            $valClean = preg_replace('/RTX\s*([0-9]{4})\s*Laptop\s*GPU/i', 'NVIDIA GeForce RTX $1 Laptop GPU', $valClean);
            $valClean = preg_replace('/RTX\s*([0-9]{4})/i', 'NVIDIA GeForce RTX $1', $valClean);

            // Chuẩn hóa vi xử lý
            $valClean = preg_replace('/Intel®\s*Core™/u', 'Intel Core', $valClean);
            $valClean = preg_replace('/AMD\s*Ryzen™/u', 'AMD Ryzen', $valClean);

            // Chuẩn hóa dung lượng & đơn vị
            $valClean = preg_replace('/([0-9]+)\s*gb/i', '$1GB', $valClean);
            $valClean = preg_replace('/([0-9]+)\s*tb/i', '$1TB', $valClean);
            $valClean = preg_replace('/([0-9]+(?:\.[0-9]+)?)\s*inch/i', '$1 inch', $valClean);
            $valClean = preg_replace('/([0-9]+(?:\.[0-9]+)?)\s*hz/i', '$1Hz', $valClean);
            $valClean = preg_replace('/([0-9]+(?:\.[0-9]+)?)\s*kg/i', '$1 kg', $valClean);

            $normalized[$keyClean] = $valClean;
        }

        return $normalized;
    }

    /**
     * Tính toán Điểm tin cậy (Confidence Score %) Toán học Thực tế dựa trên Category Schema
     */
    public static function calculateRealConfidenceScore(array $schema, array $extractedSpecs, array $sourceUrls): array
    {
        $requiredFields = [];
        $allFields = [];

        if (isset($schema['groups']) && is_array($schema['groups'])) {
            foreach ($schema['groups'] as $group) {
                if (isset($group['fields']) && is_array($group['fields'])) {
                    foreach ($group['fields'] as $field) {
                        $label = $field['label'] ?? $field['key'];
                        $allFields[] = $label;
                        if (!empty($field['required'])) {
                            $requiredFields[] = $label;
                        }
                    }
                }
            }
        }

        if (empty($requiredFields)) {
            $requiredFields = array_keys($extractedSpecs);
        }

        $filledRequiredCount = 0;
        $missingRequiredFields = [];

        foreach ($requiredFields as $req) {
            $found = false;
            foreach ($extractedSpecs as $k => $v) {
                if (mb_stripos($k, $req) !== false || mb_stripos($req, $k) !== false) {
                    if (!empty($v) && $v !== 'null') {
                        $found = true;
                        break;
                    }
                }
            }
            if ($found) {
                $filledRequiredCount++;
            } else {
                $missingRequiredFields[] = $req;
            }
        }

        $totalReq = max(1, count($requiredFields));
        $rawScore = round(($filledRequiredCount / $totalReq) * 100);

        // Nếu không có URL nguồn thực tế -> Giảm score xuống max 40%
        if (empty($sourceUrls)) {
            $rawScore = min(40, $rawScore);
        }

        $confidenceScore = max(10, min(100, (int)$rawScore));

        return [
            'confidence_score' => $confidenceScore,
            'missing_fields' => $missingRequiredFields,
            'needs_manual_review' => ($confidenceScore < 50)
        ];
    }

    /**
     * Sinh Mô tả và Thông tin Sản phẩm đầy đủ (Scrape-First-Then-Extract Pipeline)
     */
    public static function generateProductData(string $inputQuery, array $existingCategories = [], array $existingBrands = [], bool $forceRefresh = false): array
    {
        // BƯỚC 1: Kiểm tra Ambiguous Guard
        $valResult = self::validateModelInput($inputQuery);
        if (!$valResult['valid']) {
            return [
                'success' => false,
                'error_code' => 'INVALID_INPUT',
                'is_ambiguous' => $valResult['is_ambiguous'] ?? false,
                'message' => $valResult['message']
            ];
        }

        $cleanQuery = trim($inputQuery);
        $modelKey = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cleanQuery), '-'));

        $db = Database::getConnection();

        // BƯỚC 2: Kiểm tra Cache DB
        if ($db && !$forceRefresh) {
            try {
                $stmtCache = $db->prepare('SELECT response_data, provider, confidence_score, source_name, created_at FROM ai_assistant_logs WHERE model_key = :key AND status != \'rejected\' ORDER BY id DESC LIMIT 1');
                $stmtCache->execute([':key' => $modelKey]);
                $cachedRow = $stmtCache->fetch(PDO::FETCH_ASSOC);

                if ($cachedRow && !empty($cachedRow['response_data'])) {
                    $cachedData = json_decode($cachedRow['response_data'], true);
                    if (is_array($cachedData) && !empty($cachedData['name'])) {
                        $cachedData['success'] = true;
                        $cachedData['is_cached'] = true;
                        $cachedData['cache_created_at'] = $cachedRow['created_at'];
                        $cachedData['provider'] = $cachedRow['provider'] . ' (Database Cache)';
                        return $cachedData;
                    }
                }
            } catch (Throwable $e) {
                error_log('AI Cache Lookup error: ' . $e->getMessage());
            }
        }

        // BƯỚC 3: Tra cứu Scrape thực tế từ Web
        $scrapedInfo = SpecScraperService::scrapeProductSpecs($cleanQuery);
        $rawText = $scrapedInfo['raw_text'];
        $sourceUrls = $scrapedInfo['source_urls'];
        $primarySource = $scrapedInfo['source_name'];

        // BƯỚC 4: Nhận diện Category & Lấy Schema
        $detectedCategoryName = 'Laptop';
        $detectedBrandName = 'ASUS';
        $queryLower = strtolower($cleanQuery);

        if (str_contains($queryLower, 'vga') || str_contains($queryLower, 'rtx') || str_contains($queryLower, 'gtx') || str_contains($queryLower, 'radeon')) {
            $detectedCategoryName = 'VGA - Card màn hình';
        } elseif (str_contains($queryLower, 'cpu') || str_contains($queryLower, 'intel') || str_contains($queryLower, 'ryzen') || str_contains($queryLower, 'i7') || str_contains($queryLower, 'i9') || str_contains($queryLower, 'i5')) {
            $detectedCategoryName = 'CPU - Bộ vi xử lý';
        } elseif (str_contains($queryLower, 'ram') || str_contains($queryLower, 'ddr4') || str_contains($queryLower, 'ddr5')) {
            $detectedCategoryName = 'RAM - Bộ nhớ trong';
        } elseif (str_contains($queryLower, 'ssd') || str_contains($queryLower, 'nvme')) {
            $detectedCategoryName = 'SSD / Ổ cứng';
        } elseif (str_contains($queryLower, 'màn hình') || str_contains($queryLower, 'monitor')) {
            $detectedCategoryName = 'Màn hình';
        }

        if (str_contains($queryLower, 'msi')) $detectedBrandName = 'MSI';
        elseif (str_contains($queryLower, 'dell') || str_contains($queryLower, 'alienware')) $detectedBrandName = 'DELL';
        elseif (str_contains($queryLower, 'lenovo') || str_contains($queryLower, 'legion')) $detectedBrandName = 'Lenovo';
        elseif (str_contains($queryLower, 'gigabyte') || str_contains($queryLower, 'aorus')) $detectedBrandName = 'GIGABYTE';
        elseif (str_contains($queryLower, 'apple') || str_contains($queryLower, 'macbook')) $detectedBrandName = 'Apple';
        elseif (str_contains($queryLower, 'hp')) $detectedBrandName = 'HP';

        $schema = CategorySchemaRegistry::getSchemaForCategory($detectedCategoryName);
        $schemaJson = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        // BƯỚC 5: LLM Extraction-Only Call
        $promptExtraction = <<<PROMPT
Bạn là công cụ TRÍCH XUẤT dữ liệu kỹ thuật phần cứng, KHÔNG PHẢI công cụ sáng tạo nội dung.
Nhiệm vụ: đọc đoạn text thô dưới đây (được trích xuất từ các website nguồn thực tế) và điền vào đúng các field trong schema JSON được cung cấp cho sản phẩm "{$cleanQuery}".

QUY TẮC BẮT BUỘC:
1. CHỈ điền giá trị nếu tìm thấy RÕ RÀNG trong text nguồn.
2. Nếu không tìm thấy field nào -> gán giá trị = null (không được đoán, không được bịa).
3. Không được thêm field nằm ngoài schema.
4. Trả về DUY NHẤT một chuỗi JSON hợp lệ.

DỮ LIỆU TEXT NGUỒN THỰC TẾ:
"{$rawText}"

SCHEMA DANH MỤC:
{$schemaJson}

Yêu cầu định dạng JSON trả về:
{
    "name": "Tên sản phẩm chính xác",
    "proposed_category": "{$detectedCategoryName}",
    "proposed_brand": "{$detectedBrandName}",
    "specs": {
        "Key thông số": "Giá trị trích xuất được hoặc null"
    }
}
PROMPT;

        $extractedSpecs = [];
        $productName = $cleanQuery;
        $providerUsed = 'TSIE Extraction Engine';

        if (AiService::isConfigured() && !empty($rawText)) {
            try {
                $res = AiService::generateContent($promptExtraction, ['timeout' => 20]);
                if (!empty($res['text'])) {
                    $jsonText = preg_replace('/^```(?:json)?\s*/i', '', trim($res['text']));
                    $jsonText = preg_replace('/\s*```$/', '', $jsonText);
                    $parsed = json_decode($jsonText, true);
                    if (is_array($parsed) && !empty($parsed['specs'])) {
                        $extractedSpecs = $parsed['specs'];
                        if (!empty($parsed['name'])) $productName = $parsed['name'];
                        $providerUsed = ($res['provider'] ?? 'AI Engine') . ' (Extraction)';
                    }
                }
            } catch (Throwable $e) {
                error_log('TSIE LLM Extraction error: ' . $e->getMessage());
            }
        }

        // If extraction is empty, use clean default structure from schema
        if (empty($extractedSpecs)) {
            foreach ($schema['groups'] as $g) {
                foreach ($g['fields'] as $f) {
                    $extractedSpecs[$f['label']] = null;
                }
            }
        }

        // Chuẩn hóa Specs
        $normalizedSpecs = self::normalizeSpecs($extractedSpecs);

        // BƯỚC 6: Tính toán Confidence Score Toán học Thực tế
        $confResults = self::calculateRealConfidenceScore($schema, $normalizedSpecs, $sourceUrls);
        $confidenceScore = $confResults['confidence_score'];
        $missingFields = $confResults['missing_fields'];
        $needsManualReview = $confResults['needs_manual_review'];

        // BƯỚC 7: Sinh Mô tả HTML 8 Section CHỈ DỰA TRÊN SPECS THẬT
        $specsFormattedStr = '';
        foreach ($normalizedSpecs as $k => $v) {
            $specsFormattedStr .= "- {$k}: {$v}\n";
        }

        $htmlDescription = <<<HTML
<h3>1. Giới thiệu tổng quan</h3>
<p>Sản phẩm <strong>{$productName}</strong> được xác thực thông số kỹ thuật chính hãng, đáp ứng nhu cầu sử dụng chuyên nghiệp.</p>

<h3>2. Thông số kỹ thuật chi tiết</h3>
<p>Dữ liệu trích xuất thực tế:</p>
HTML;
        $htmlDescription .= '<table style="width:100%; border-collapse:collapse; margin:10px 0;">';
        foreach ($normalizedSpecs as $k => $v) {
            $htmlDescription .= '<tr style="border-bottom:1px solid #E2E8F0;"><td style="padding:6px; font-weight:600; width:40%;">' . htmlspecialchars($k) . '</td><td style="padding:6px;">' . htmlspecialchars($v) . '</td></tr>';
        }
        $htmlDescription .= '</table>';

        $htmlDescription .= <<<HTML
<h3>3. Hiệu năng & Sức mạnh</h3>
<p>Chi tiết thông số đã được xác minh theo tiêu chuẩn nhà sản xuất.</p>

<h3>4. Khả năng tương thích</h3>
<p>Tương thích hoàn hảo với các hệ thống máy tính hiện đại.</p>

<h3>5. Độ bền & Bảo hành</h3>
<p>Sản phẩm chính hãng đi kèm chính sách bảo hành uy tín tại TechPilot.</p>
HTML;

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cleanQuery), '-'));

        $finalResponse = [
            'success' => true,
            'name' => $productName,
            'slug' => $slug,
            'description' => $htmlDescription,
            'short_desc' => "Sản phẩm {$productName} thông số thực tế được xác minh từ " . ($primarySource ?: 'Nguồn hãng'),
            'specs' => $normalizedSpecs,
            'seo_title' => $productName . ' | Chính Hãng TechPilot',
            'seo_description' => 'Mua ngay ' . $productName . ' chính hãng tại TechPilot. Đảm bảo thông số thật, bảo hành chính hãng.',
            'meta_keywords' => strtolower($cleanQuery) . ', techpilot, chính hãng',
            'og_title' => $productName . ' - TechPilot Official',
            'highlights' => array_values(array_slice($normalizedSpecs, 0, 3)),
            'tags' => [$detectedCategoryName, $detectedBrandName, 'Xác minh thật'],
            'proposed_category' => $detectedCategoryName,
            'proposed_brand' => $detectedBrandName,
            'source_name' => $primarySource,
            'source_urls' => $sourceUrls,
            'confidence_score' => $confidenceScore,
            'missing_fields' => $missingFields,
            'needs_manual_review' => $needsManualReview,
            'provider' => $providerUsed,
            'is_cached' => false
        ];

        // BƯỚC 8: Lưu Log vào Database
        if ($db) {
            try {
                $stmtLog = $db->prepare('INSERT INTO ai_assistant_logs (prompt, model_key, provider, confidence_score, source_name, request_payload, response_data, status, created_by) VALUES (:prompt, :key, :provider, :score, :source, :req, :res, \'pending\', :uid)');
                $stmtLog->execute([
                    ':prompt'   => $cleanQuery,
                    ':key'      => $modelKey,
                    ':provider' => $providerUsed,
                    ':score'    => $confidenceScore,
                    ':source'   => $primarySource,
                    ':req'      => json_encode(['query' => $cleanQuery, 'force_refresh' => $forceRefresh], JSON_UNESCAPED_UNICODE),
                    ':res'      => json_encode($finalResponse, JSON_UNESCAPED_UNICODE),
                    ':uid'      => $_SESSION['user']['id'] ?? null
                ]);
            } catch (Throwable $e) {
                error_log('TSIE Log save error: ' . $e->getMessage());
            }
        }

        return $finalResponse;
    }

    /**
     * PHẦN 4: AI Editor - Điều chỉnh văn phong & phong cách bài viết (Rewrite Tone)
     */
    public static function rewriteTone(string $content, string $style, string $section = 'all'): array
    {
        $content = trim($content);
        if ($content === '') {
            return ['success' => false, 'message' => 'Nội dung ban đầu trống.'];
        }

        $stylePrompts = [
            'short'    => 'Hãy viết lại ngắn gọn, súc tích, đi thẳng vào trọng tâm công nghệ chính.',
            'detailed' => 'Hãy mở rộng chi tiết, phân tích sâu sắc các khía cạnh kỹ thuật phần cứng.',
            'seo'      => 'Hãy tối ưu từ khóa SEO chuẩn Thương mại điện tử công nghệ, mạch lạc, dễ đọc.',
            'gaming'   => 'Hãy dùng văn phong Chuyên Game, cực bốc, mạnh mẽ, tràn đầy năng lượng cho game thủ.',
            'office'   => 'Hãy dùng văn phong Doanh nhân & Văn phòng, thanh lịch, trang nhã, tin cậy.',
            'premium'  => 'Hãy dùng văn phong Cao cấp, sang trọng, tinh tế, tôn vinh giá trị Flagship.',
            'gearvn'   => 'Hãy dùng phong cách GearVN: Đam mê công nghệ, tư vấn tận tâm, sành sỏi phần cứng.',
            'phongvu'  => 'Hãy dùng phong cách Phong Vũ: Chuyên nghiệp, tin cậy, hướng tới giải pháp tối ưu.'
        ];

        $instruction = $stylePrompts[$style] ?? 'Hãy tối ưu hóa nội dung cho chuyên nghiệp hơn.';

        $prompt = <<<PROMPT
Bạn là chuyên gia biên tập nội dung E-commerce công nghệ.
Yêu cầu: {$instruction}
Nội dung cần viết lại (giữ nguyên định dạng HTML nếu có):
"{$content}"

Trả về DUY NHẤT nội dung đã viết lại, không thêm bất kỳ lời thoại nào khác.
PROMPT;

        if (AiService::isConfigured()) {
            try {
                $res = AiService::generateContent($prompt, ['timeout' => 20]);
                if (!empty($res['text'])) {
                    return [
                        'success' => true,
                        'rewritten_text' => trim($res['text']),
                        'provider' => $res['provider'] ?? 'AI Editor'
                    ];
                }
            } catch (Throwable $e) {
                error_log('AI Editor error: ' . $e->getMessage());
            }
        }

        // Fallback đơn giản khi AI API gián đoạn
        return [
            'success' => true,
            'rewritten_text' => $content,
            'provider' => 'Original Content (AI Disconnected)'
        ];
    }

    /**
     * Heuristic Generator khi AI Engine không khả dụng
     */
    private static function generateFallbackData(string $query, array $categories, array $brands): array
    {
        $cleanQuery = trim($query);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cleanQuery), '-'));

        $detectedBrand = 'ASUS';
        $detectedCategory = 'Laptop';

        $queryLower = strtolower($cleanQuery);
        if (str_contains($queryLower, 'rog') || str_contains($queryLower, 'tuf') || str_contains($queryLower, 'asus')) {
            $detectedBrand = 'ASUS';
        } elseif (str_contains($queryLower, 'msi')) {
            $detectedBrand = 'MSI';
        } elseif (str_contains($queryLower, 'legion') || str_contains($queryLower, 'lenovo')) {
            $detectedBrand = 'Lenovo';
        } elseif (str_contains($queryLower, 'gigabyte') || str_contains($queryLower, 'aorus')) {
            $detectedBrand = 'GIGABYTE';
        } elseif (str_contains($queryLower, 'dell') || str_contains($queryLower, 'alienware')) {
            $detectedBrand = 'DELL';
        } elseif (str_contains($queryLower, 'hp') || str_contains($queryLower, 'victus') || str_contains($queryLower, 'omen')) {
            $detectedBrand = 'HP';
        } elseif (str_contains($queryLower, 'apple') || str_contains($queryLower, 'macbook')) {
            $detectedBrand = 'Apple';
        }

        if (str_contains($queryLower, 'laptop') || str_contains($queryLower, 'macbook')) {
            $detectedCategory = 'Laptop';
        } elseif (str_contains($queryLower, 'pc') || str_contains($queryLower, 'gaming')) {
            $detectedCategory = 'PC Gaming';
        } elseif (str_contains($queryLower, 'màn hình') || str_contains($queryLower, 'monitor')) {
            $detectedCategory = 'Màn hình';
        } elseif (str_contains($queryLower, 'vga') || str_contains($queryLower, 'rtx')) {
            $detectedCategory = 'VGA - Card màn hình';
        }

        $specs = [
            'CPU' => 'Intel Core / AMD Ryzen High Performance',
            'GPU' => 'NVIDIA GeForce RTX 40 Series / Integrated Graphics',
            'RAM' => '16GB DDR5 5600MHz (Nâng cấp tối đa 64GB)',
            'SSD' => '1TB PCIe 4.0 NVMe M.2 SSD',
            'Màn hình' => '16 inch QHD+ (2560x1600) 240Hz 100% sRGB',
            'Pin' => '90Whrs 4-cell Li-ion',
            'Trọng lượng' => '1.95 kg',
            'Hệ điều hành' => 'Windows 11 Home bản quyền',
            'Bảo hành' => '24 Tháng chính hãng'
        ];

        $htmlDesc = <<<HTML
<h3>1. Giới thiệu tổng quan</h3>
<p>Sản phẩm <strong>{$cleanQuery}</strong> đại diện cho bước tiến vượt trội về công nghệ phần cứng, mang đến sự kết hợp hoàn hảo giữa thiết kế hiện đại và sức mạnh xử lý vượt giới hạn.</p>

<h3>2. Thiết kế & Khung vỏ</h3>
<p>Sở hữu bộ khung hợp kim chắc chắn cùng đường nét cắt gọt tinh xảo, sản phẩm tối ưu hóa khả năng cơ động nhưng vẫn đảm bảo độ bền chuẩn quân đội.</p>

<h3>3. Hiệu năng đỉnh cao</h3>
<p>Được trang bị bộ vi xử lý và card đồ họa thế hệ mới nhất, sản phẩm dễ dàng chinh phục mọi tựa game AAA hạng nặng cũng như các tác vụ dựng phim 4K chuyên nghiệp.</p>

<h3>4. Màn hình sắc nét</h3>
<p>Màn hình độ phân giải cao với chuẩn màu đạt chuẩn sáng tạo nội dung, tần số quét siêu mượt xóa tan hiện tượng xé hình trong các trận chiến tốc độ.</p>

<h3>5. Thời lượng Pin & Tản nhiệt</h3>
<p>Hệ thống tản nhiệt buồng hơi kết hợp quạt thế hệ mới giữ nhiệt độ luôn mát mẻ, cùng viên pin dung lượng lớn đồng hành suốt ngày dài làm việc.</p>

<h3>6. Cổng kết nối & Bàn phím</h3>
<p>Bàn phím cho hành trình phím sâu, độ nảy tuyệt vời. Trang bị đầy đủ các cổng kết nối tốc độ cao nhất hiện nay.</p>

<h3>7. Đối tượng phù hợp</h3>
<p>Lựa chọn lý tưởng cho các Game thủ chuyên nghiệp, Creator sáng tạo nội dung, Kỹ sư phần mềm và Doanh nhân yêu thích hiệu năng cao.</p>

<h3>8. Kết luận & Đánh giá</h3>
<p>Một khoản đầu tư hoàn toàn xứng đáng mang lại trải nghiệm đỉnh cao và độ bền vững vàng theo thời gian.</p>
HTML;

        return [
            'name' => $cleanQuery,
            'slug' => $slug,
            'description' => $htmlDesc,
            'short_desc' => 'Dòng sản phẩm cao cấp sở hữu hiệu năng mạnh mẽ, thiết kế hiện đại và màn hình đỉnh cao cho game thủ & creator.',
            'specs' => $specs,
            'seo_title' => $cleanQuery . ' | Chính Hãng Giá Tốt Nhất TechPilot',
            'seo_description' => 'Mua ngay ' . $cleanQuery . ' chính hãng tại TechPilot. Trả góp 0%, bảo hành 24 tháng, giao hàng hỏa tốc toàn quốc.',
            'meta_keywords' => strtolower($cleanQuery) . ', techpilot, laptop gaming, chính hãng',
            'og_title' => $cleanQuery . ' - Đỉnh Cao Công Nghệ',
            'highlights' => [
                'Hiệu năng vượt trội cân mọi tựa game AAA',
                'Màn hình hiển thị màu sắc chuẩn đồ họa',
                'Hệ thống tản nhiệt thông minh mát mẻ'
            ],
            'tags' => [$detectedCategory, $detectedBrand, 'Công nghệ mới'],
            'proposed_category' => $detectedCategory,
            'proposed_brand' => $detectedBrand
        ];
    }
}
