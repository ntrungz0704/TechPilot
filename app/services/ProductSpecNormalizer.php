<?php

/**
 * ProductSpecNormalizer - Normalizes raw legacy specification arrays into Schema v2 JSON.
 */
class ProductSpecNormalizer
{
    /**
     * Chuyển đổi dữ liệu JSON/Array thô sang Schema v2 chuẩn.
     */
    public static function normalize(string $categorySlug, array|string $rawSpecs): array
    {
        $catSlug = strtolower(trim($categorySlug));
        $data = is_string($rawSpecs) ? (json_decode($rawSpecs, true) ?: []) : $rawSpecs;

        // Tách attributes từ các key bọc cũ
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $attrs = $data['attributes'];
        } elseif (isset($data['specs']) && is_array($data['specs'])) {
            $attrs = $data['specs'];
        } else {
            $attrs = $data;
        }

        // Lọc bỏ metadata nội bộ khỏi attributes
        $metaKeys = ['schema_version', 'category_slug', 'model', 'migration_status', 'raw_legacy_data', 'confidence', 'source'];
        foreach ($metaKeys as $mk) {
            unset($attrs[$mk]);
        }

        // Extract compatibility và use_cases nếu có
        $compatibility = $data['compatibility'] ?? [];
        if (!is_array($compatibility)) {
            $compatibility = [];
        }

        $useCases = $data['use_cases'] ?? $data['recommended_use_cases'] ?? [];
        if (!is_array($useCases)) {
            $useCases = array_filter(array_map('trim', explode(',', (string)$useCases)));
        }

        // Đảm bảo attributes có key rõ ràng
        $cleanAttributes = [];
        foreach ($attrs as $k => $v) {
            if (is_numeric($k)) {
                continue; // Skip positional array elements without keys
            }
            if ($v !== null && $v !== '') {
                $cleanAttributes[$k] = $v;
            }
        }

        return [
            'schema_version' => 2,
            'category_slug' => $catSlug,
            'attributes' => $cleanAttributes,
            'compatibility' => $compatibility,
            'use_cases' => array_values($useCases),
        ];
    }
}
