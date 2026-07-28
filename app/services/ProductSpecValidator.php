<?php

/**
 * ProductSpecValidator - Validates category specification attributes.
 */
class ProductSpecValidator
{
    /**
     * Kiểm tra tính hợp lệ của mảng attributes theo danh mục.
     */
    public static function validate(string $categorySlug, array $attributes): array
    {
        $errors = [];
        $catSlug = strtolower(trim($categorySlug));

        switch ($catSlug) {
            case 'cpu':
                if (isset($attributes['cores']) && ((int)$attributes['cores'] <= 0)) {
                    $errors[] = 'Số nhân CPU phải là số nguyên dương.';
                }
                if (isset($attributes['threads']) && isset($attributes['cores']) && ((int)$attributes['threads'] < (int)$attributes['cores'])) {
                    $errors[] = 'Số luồng CPU phải lớn hơn hoặc bằng số nhân.';
                }
                break;

            case 'psu':
                if (isset($attributes['wattage_w']) && ((int)$attributes['wattage_w'] < 100 || (int)$attributes['wattage_w'] > 3000)) {
                    $errors[] = 'Công suất Nguồn (wattage) không hợp lệ (phải từ 100W đến 3000W).';
                }
                break;

            case 'monitor':
                if (isset($attributes['refresh_rate_hz']) && ((int)$attributes['refresh_rate_hz'] < 30 || (int)$attributes['refresh_rate_hz'] > 1000)) {
                    $errors[] = 'Tần số quét màn hình phải từ 30 Hz đến 1000 Hz.';
                }
                break;

            case 'power-bank':
                if (isset($attributes['capacity_mah']) && (int)$attributes['capacity_mah'] <= 0) {
                    $errors[] = 'Dung lượng sạc dự phòng phải lớn hơn 0 mAh.';
                }
                break;
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}
