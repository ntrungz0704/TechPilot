<?php

/**
 * Service AI Hỗ trợ nhập sản phẩm tự động (AI Product Assistant)
 * Tự động tạo Mô tả, Thông số kỹ thuật (Specs JSON), SEO Meta, Highlights, Pros/Cons
 * Sử dụng Động cơ AI Multi-Provider (Gemini -> Groq -> Qwen) với Heuristic Fallback
 */

require_once ROOT_PATH . '/app/services/AiService.php';

class AiProductAssistantService
{
    public static function generateProductData(string $inputQuery, array $existingCategories = [], array $existingBrands = []): array
    {
        $inputQuery = trim($inputQuery);
        if ($inputQuery === '') {
            throw new Exception('Vui lòng nhập tên sản phẩm, mã model hoặc liên kết thông tin.');
        }

        $categoriesStr = implode(', ', array_column($existingCategories, 'name'));
        $brandsStr     = implode(', ', array_column($existingBrands, 'name'));

        $prompt = <<<PROMPT
Bạn là chuyên gia thương mại điện tử công nghệ phần cứng PC & Laptop.
Hãy tạo thông tin sản phẩm đầy đủ và chuẩn SEO cho sản phẩm: "{$inputQuery}".

Danh mục hiện có: [{$categoriesStr}]
Thương hiệu hiện có: [{$brandsStr}]

Yêu cầu trả về DUY NHẤT một chuỗi JSON hợp lệ (không kèm bất kỳ văn bản Markdown nào khác) với cấu trúc như sau:
{
    "name": "Tên sản phẩm chuẩn chỉnh đầy đủ",
    "slug": "slug-san-pham-chuan-seo",
    "description": "Mô tả sản phẩm khoảng 2-3 đoạn văn hấp dẫn, chuyên nghiệp.",
    "specs": {
        "Thuộc tính 1": "Giá trị 1",
        "Thuộc tính 2": "Giá trị 2"
    },
    "seo_title": "Tiêu đề SEO (dưới 60 ký tự)",
    "seo_description": "Mô tả SEO meta (dưới 160 ký tự)",
    "meta_keywords": "từ khóa 1, từ khóa 2, từ khóa 3",
    "highlights": ["Đặc điểm 1", "Đặc điểm 2", "Đặc điểm 3"],
    "pros": ["Ưu điểm 1", "Ưu điểm 2"],
    "cons": ["Hạn chế 1"],
    "proposed_category": "Tên danh mục phù hợp nhất từ danh sách",
    "proposed_brand": "Tên thương hiệu phù hợp nhất từ danh sách",
    "thumbnail_alt": "Mô tả ảnh đại diện chuẩn SEO"
}
PROMPT;

        if (AiService::isConfigured()) {
            try {
                $res = AiService::generateContent($prompt, ['timeout' => 20]);
                if (!empty($res['text'])) {
                    $jsonText = $res['text'];
                    // Clean codeblock formatting if present
                    $jsonText = preg_replace('/^```(?:json)?\s*/i', '', trim($jsonText));
                    $jsonText = preg_replace('/\s*```$/', '', $jsonText);

                    $parsed = json_decode($jsonText, true);
                    if (is_array($parsed) && isset($parsed['name'])) {
                        $parsed['provider'] = $res['provider'] ?? 'AI Engine';
                        return $parsed;
                    }
                }
            } catch (Throwable $e) {
                error_log('AiProductAssistantService error: ' . $e->getMessage());
            }
        }

        // Smart Heuristic Fallback Generator when AI API keys are unavailable or fail
        return self::generateFallbackData($inputQuery, $existingCategories, $existingBrands);
    }

    private static function generateFallbackData(string $query, array $categories, array $brands): array
    {
        $cleanQuery = trim($query);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cleanQuery), '-'));

        $detectedCategory = 'Laptop';
        $detectedBrand = 'ASUS';

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
            $specs = [
                'CPU' => 'Intel Core i7 / AMD Ryzen 7 thế hệ mới',
                'RAM' => '16GB DDR5 5600MHz',
                'SSD' => '512GB PCIe NVMe Gen4',
                'VGA' => 'NVIDIA GeForce RTX 4060 8GB',
                'Màn hình' => '15.6 inch FHD/QHD IPS 144Hz',
                'Trọng lượng' => '2.1 kg'
            ];
        } elseif (str_contains($queryLower, 'pc') || str_contains($queryLower, 'máy tính')) {
            $detectedCategory = 'PC';
            $specs = [
                'CPU' => 'Intel Core i5-13400F',
                'Mainboard' => 'B760M DDR5',
                'RAM' => '16GB DDR5 5200MHz',
                'SSD' => '512GB NVMe M.2',
                'VGA' => 'RTX 4060 8GB',
                'Nguồn' => '650W 80 Plus Bronze'
            ];
        } elseif (str_contains($queryLower, 'màn hình') || str_contains($queryLower, 'monitor')) {
            $detectedCategory = 'Màn hình';
            $specs = [
                'Kích thước' => '27 inch',
                'Độ phân giải' => '2560 x 1440 (2K QHD)',
                'Tần số quét' => '180Hz',
                'Tấm nền' => 'Fast IPS',
                'Thời gian phản hồi' => '1ms GTG'
            ];
        } else {
            $detectedCategory = 'Linh kiện';
            $specs = [
                'Chuẩn kết nối' => 'PCIe / USB 3.2',
                'Bảo hành' => '36 Tháng',
                'Xuất xứ' => 'Chính hãng'
            ];
        }

        return [
            'name' => $cleanQuery,
            'slug' => $slug,
            'description' => "Thế hệ {$cleanQuery} mang đến hiệu năng vượt trội cùng thiết kế hiện đại, tối ưu cho cả làm việc chuyên nghiệp lẫn giải trí chơi game đỉnh cao. Hệ thống tản nhiệt tiên tiến giúp máy luôn duy trì hiệu suất mượt mà trong mọi tác vụ nặng.",
            'specs' => $specs,
            'seo_title' => "Mua {$cleanQuery} Chính Hãng - Giá Tốt Nhất Tại TechPilot",
            'seo_description' => "Đặt mua {$cleanQuery} chính hãng, bảo hành 36 tháng, trả góp 0%, giao hàng siêu tốc toàn quốc tại hệ thống TechPilot.",
            'meta_keywords' => "{$cleanQuery}, {$detectedBrand}, {$detectedCategory}, techpilot",
            'highlights' => [
                'Hiệu năng mạnh mẽ với cấu hình đời mới',
                'Thiết kế cao cấp, tối ưu tản nhiệt hiệu quả',
                'Bảo hành chính hãng 36 tháng tại TechPilot'
            ],
            'pros' => ['Cấu hình cực mạnh trong tầm giá', 'Màn hình hiển thị sắc nét'],
            'cons' => ['Cần cắm sạc khi chơi game nặng để đạt hiệu năng tối đa'],
            'proposed_category' => $detectedCategory,
            'proposed_brand' => $detectedBrand,
            'thumbnail_alt' => "Ảnh sản phẩm {$cleanQuery} chính hãng",
            'provider' => 'TechPilot AI Heuristic Engine (Offline Mode)'
        ];
    }
}
