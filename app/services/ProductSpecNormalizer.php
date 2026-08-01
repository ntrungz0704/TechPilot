<?php

/**
 * ProductSpecNormalizer - Normalizes raw legacy specification arrays into Schema v2 JSON.
 */
class ProductSpecNormalizer
{
    /**
     * Canonical field => accepted legacy keys.
     *
     * The first key is the canonical key used by product-spec-schemas.php.
     * Values not listed here are preserved so existing consumers do not lose
     * category-specific metadata.
     */
    private const FIELD_ALIASES = [
        'laptop' => [
            'ram_capacity_gb' => ['ram_capacity_gb', 'ram_gb'],
            'storage_capacity_gb' => ['storage_capacity_gb', 'storage_gb'],
            'screen_size_inch' => ['screen_size_inch', 'display_size_inch'],
            'operating_system' => ['operating_system', 'os'],
        ],
        'pc' => [
            'ram_capacity_gb' => ['ram_capacity_gb', 'ram_gb'],
            'storage_devices' => ['storage_devices', 'storage'],
            'psu_efficiency' => ['psu_efficiency', 'psu_certification'],
            'cooling_system' => ['cooling_system', 'cooler_model'],
            'operating_system' => ['operating_system', 'os'],
        ],
        'monitor' => [
            'color_gamut_srgb' => ['color_gamut_srgb', 'color_gamut_srgb_percent'],
            'hdr_standard' => ['hdr_standard', 'hdr'],
        ],
        'mainboard' => [
            'supported_cpu_generations' => ['supported_cpu_generations', 'cpu_generations'],
            'ram_type' => ['ram_type', 'memory_type'],
            'dimm_slots' => ['dimm_slots', 'ram_slots'],
        ],
        'cpu' => [
            'cache_l3_mb' => ['cache_l3_mb', 'l3_cache_mb'],
            'tdp_w' => ['tdp_w', 'base_power_w'],
            'integrated_graphics' => ['integrated_graphics', 'integrated_gpu'],
            'memory_types' => ['memory_types', 'supported_memory'],
        ],
        'vga' => [
            'gpu_model' => ['gpu_model', 'gpu_chip'],
            'ray_tracing_support' => ['ray_tracing_support', 'ray_tracing'],
            'power_draw_w' => ['power_draw_w', 'tdp_w'],
            'slot_width' => ['slot_width', 'width_slots'],
            'display_outputs' => ['display_outputs', 'ports'],
        ],
        'ram' => [
            'capacity_gb' => ['capacity_gb', 'total_capacity_gb'],
            'voltage' => ['voltage', 'voltage_v'],
        ],
        'storage' => [
            'storage_type' => ['storage_type', 'drive_type'],
            'heatsink' => ['heatsink', 'heatsink_included'],
        ],
        'cooling' => [
            'supported_sockets' => ['supported_sockets', 'sockets'],
            'tdp_capacity_w' => ['tdp_capacity_w', 'cooling_capacity_w'],
        ],
        'psu' => [
            'wattage_w' => ['wattage_w', 'wattage'],
        ],
        'keyboard' => [
            'connection_type' => ['connection_type', 'connection'],
        ],
        'mouse' => [
            'sensor_model' => ['sensor_model', 'sensor'],
            'connection_type' => ['connection_type', 'connection'],
        ],
        'headset' => [
            'driver_mm' => ['driver_mm', 'driver_size_mm'],
            'connection_type' => ['connection_type', 'connection'],
            'surround' => ['surround', 'surround_sound'],
        ],
        'speaker' => [
            'channel_configuration' => ['channel_configuration', 'speaker_type'],
            'connection_type' => ['connection_type', 'connection'],
        ],
        'office-equipment' => [
            'office_equipment_type' => ['office_equipment_type', 'office_device_type'],
            'connectivity' => ['connectivity', 'connection'],
        ],
        'power-bank' => [
            'maximum_total_output_w' => ['maximum_total_output_w', 'max_output_w'],
        ],
    ];

    private static function hasValue(array $values, string $key): bool
    {
        if (!array_key_exists($key, $values)) {
            return false;
        }

        $value = $values[$key];
        return $value !== null && $value !== '' && (!is_array($value) || $value !== []);
    }

    /**
     * Trả về attributes đã unwrap nhưng chưa đổi tên key.
     */
    public static function extractAttributes(array|string $rawSpecs): array
    {
        $data = is_string($rawSpecs) ? (json_decode($rawSpecs, true) ?: []) : $rawSpecs;
        if (!is_array($data)) {
            return [];
        }

        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $attrs = $data['attributes'];
        } elseif (isset($data['specs']) && is_array($data['specs'])) {
            $attrs = $data['specs'];
        } else {
            $attrs = $data;
        }

        $nonAttributeKeys = [
            'schema_version', 'category_slug', 'model', 'migration_status',
            'raw_legacy_data', 'confidence', 'source', 'attributes', 'specs',
            'compatibility', 'use_cases', 'recommended_use_cases',
        ];
        foreach ($nonAttributeKeys as $key) {
            unset($attrs[$key]);
        }

        return $attrs;
    }

    /**
     * Chuyển đổi dữ liệu JSON/Array thô sang Schema v2 chuẩn.
     */
    public static function normalize(string $categorySlug, array|string $rawSpecs): array
    {
        $catSlug = strtolower(trim($categorySlug));
        $data = is_string($rawSpecs) ? (json_decode($rawSpecs, true) ?: []) : $rawSpecs;
        if (!is_array($data)) {
            $data = [];
        }

        $attrs = self::extractAttributes($data);

        // Map legacy aliases về canonical key dùng chung cho display/filter.
        foreach (self::FIELD_ALIASES[$catSlug] ?? [] as $canonical => $aliases) {
            if (!self::hasValue($attrs, $canonical)) {
                foreach ($aliases as $alias) {
                    if (self::hasValue($attrs, $alias)) {
                        $attrs[$canonical] = $attrs[$alias];
                        break;
                    }
                }
            }

            foreach ($aliases as $alias) {
                if ($alias !== $canonical) {
                    unset($attrs[$alias]);
                }
            }
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
            if ($v !== null && $v !== '' && (!is_array($v) || $v !== [])) {
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
