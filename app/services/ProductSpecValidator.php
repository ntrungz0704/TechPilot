<?php

/**
 * ProductSpecValidator - Validate category-specific JSON specs & compatibility rules.
 */
class ProductSpecValidator
{
    public static function validate(string $categorySlug, array $specs): array
    {
        $errors = [];

        $slug = strtolower(trim($categorySlug));

        switch ($slug) {
            case 'cpu':
                if (isset($specs['manufacturer']) && !in_array($specs['manufacturer'], ['Intel', 'AMD'])) {
                    $errors[] = "Nhà sản xuất CPU phải là Intel hoặc AMD.";
                }
                if (isset($specs['socket']) && isset($specs['manufacturer'])) {
                    if ($specs['manufacturer'] === 'AMD' && str_contains($specs['socket'], 'LGA1700')) {
                        $errors[] = "Xung đột socket: CPU AMD không thể dùng socket LGA1700.";
                    }
                    if ($specs['manufacturer'] === 'Intel' && str_contains($specs['socket'], 'AM5')) {
                        $errors[] = "Xung đột socket: CPU Intel không thể dùng socket AM5.";
                    }
                }
                break;

            case 'mainboard':
                if (isset($specs['ram_type']) && !in_array($specs['ram_type'], ['DDR4', 'DDR5'])) {
                    $errors[] = "Loại RAM của Mainboard phải là DDR4 hoặc DDR5.";
                }
                break;

            case 'vga':
                if (isset($specs['vram_gb']) && (float)$specs['vram_gb'] < 0) {
                    $errors[] = "VRAM không được âm.";
                }
                break;

            case 'ram':
                if (isset($specs['capacity_gb']) && (int)$specs['capacity_gb'] <= 0) {
                    $errors[] = "Dung lượng RAM phải lớn hơn 0.";
                }
                break;

            case 'psu':
                if (isset($specs['wattage']) && (int)$specs['wattage'] <= 0) {
                    $errors[] = "Công suất nguồn (Wattage) phải lớn hơn 0.";
                }
                break;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
