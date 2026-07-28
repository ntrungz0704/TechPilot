<?php

/**
 * ProductSpecPresenter - Single Source of Truth for converting raw JSON specs into Vietnamese labels with units and grouped technical tables.
 * Powered by config/product-spec-schemas.php for 20 tech categories.
 */
class ProductSpecPresenter
{
    private static ?array $schemas = null;

    /** Internal metadata keys to strip from customer views */
    private static array $internalMetadataKeys = [
        'schema_version',
        'category_slug',
        'model',
        'migration_status',
        'confidence',
        'source',
        'raw_legacy_data'
    ];

    /**
     * Tải Registry Schemas cho 20 Danh mục sản phẩm.
     */
    public static function getSchemas(): array
    {
        if (self::$schemas === null) {
            $path = ROOT_PATH . '/config/product-spec-schemas.php';
            if (file_exists($path)) {
                self::$schemas = require $path;
            } else {
                self::$schemas = [];
            }
        }
        return self::$schemas;
    }

    /**
     * Chuẩn hóa và format bất kỳ giá trị specs thô nào (scalar, array, object).
     */
    public static function formatValue(string $key, mixed $value, string $customUnit = ''): string
    {
        if ($value === null || $value === '') {
            return 'Đang cập nhật';
        }

        if (is_bool($value)) {
            return $value ? 'Có' : 'Không';
        }

        // Xử lý Array an toàn
        if (is_array($value)) {
            return self::formatArrayValue($key, $value, $customUnit);
        }

        $valStr = trim((string)$value);

        if ($customUnit !== '') {
            return $valStr . ' ' . $customUnit;
        }

        return self::applyUnitRules($key, $valStr);
    }

    /**
     * Xử lý định dạng mảng (list scalar, associative dimension, list object).
     */
    private static function formatArrayValue(string $key, array $value, string $customUnit = ''): string
    {
        if (empty($value)) {
            return 'Đang cập nhật';
        }

        // 1. Kiểm tra associative array dạng kích thước (length, width, height)
        if (isset($value['length']) || isset($value['width']) || isset($value['height']) || isset($value['length_mm'])) {
            $l = $value['length'] ?? $value['length_mm'] ?? '';
            $w = $value['width'] ?? $value['width_mm'] ?? '';
            $h = $value['height'] ?? $value['height_mm'] ?? '';
            $unit = $value['unit'] ?? ($customUnit !== '' ? $customUnit : 'mm');

            $dims = array_filter([$l, $w, $h], fn($v) => $v !== null && $v !== '');
            if (!empty($dims)) {
                return implode(' × ', $dims) . ($unit !== '' ? ' ' . $unit : '');
            }
        }

        // 2. Mảng chứa các object con (e.g. ports, input_ports)
        $formattedItems = [];
        $isList = array_is_list($value);

        foreach ($value as $k => $item) {
            if (is_scalar($item) || is_bool($item)) {
                if (is_bool($item)) {
                    $formattedItems[] = ($isList ? '' : $k . ': ') . ($item ? 'Có' : 'Không');
                } else {
                    $strItem = trim((string)$item);
                    if ($strItem !== '') {
                        $formattedItems[] = $isList ? $strItem : "{$k}: {$strItem}";
                    }
                }
            } elseif (is_array($item)) {
                // Formatting sub-object without "Array" string
                $subParts = [];
                foreach ($item as $subK => $subV) {
                    if (is_scalar($subV) || is_bool($subV)) {
                        $formattedSub = is_bool($subV) ? ($subV ? 'Có' : 'Không') : trim((string)$subV);
                        if ($formattedSub !== '') {
                            $subParts[] = is_numeric($subK) ? $formattedSub : "{$subK} {$formattedSub}";
                        }
                    }
                }
                if (!empty($subParts)) {
                    $formattedItems[] = implode(' ', $subParts);
                }
            }
        }

        if (empty($formattedItems)) {
            return 'Đang cập nhật';
        }

        $result = implode(', ', $formattedItems);
        if ($customUnit !== '' && !str_ends_with($result, $customUnit)) {
            $result .= ' ' . $customUnit;
        }

        return $result;
    }

    /**
     * Quy tắc gắn đơn vị chuẩn theo key
     */
    private static function applyUnitRules(string $key, string $valStr): string
    {
        switch ($key) {
            case 'vram_gb':
            case 'capacity_gb':
            case 'storage_capacity_gb':
            case 'ram_capacity_gb':
            case 'max_ram_gb':
            case 'gpu_vram_gb':
            case 'memory_gb':
            case 'total_capacity_gb':
                return $valStr . ' GB';

            case 'memory_bus_bit':
                return $valStr . '-bit';

            case 'boost_clock_mhz':
            case 'base_clock_mhz':
            case 'speed_mhz':
            case 'ram_speed_mhz':
            case 'max_ram_speed_mhz':
            case 'polling_rate_hz':
                return $valStr . ' MHz';

            case 'refresh_rate_hz':
            case 'maximum_refresh_rate_hz':
                return $valStr . ' Hz';

            case 'base_clock_ghz':
            case 'boost_clock_ghz':
            case 'cpu_base_clock_ghz':
            case 'cpu_boost_clock_ghz':
                return $valStr . ' GHz';

            case 'power_draw_w':
            case 'recommended_psu_w':
            case 'tdp_w':
            case 'psu_wattage':
            case 'total_power_w':
            case 'max_output_w':
            case 'wattage':
            case 'wattage_w':
            case 'max_output_power_w':
            case 'maximum_total_output_w':
            case 'tdp_capacity_w':
                return $valStr . ' W';

            case 'length_mm':
            case 'width_mm':
            case 'height_mm':
            case 'dimensions_mm':
            case 'radiator_size_mm':
            case 'driver_mm':
            case 'driver_size_mm':
            case 'max_gpu_length_mm':
            case 'max_cpu_cooler_height_mm':
                return $valStr . ' mm';

            case 'screen_size_inch':
                return $valStr . ' inch';

            case 'response_time_ms':
                return $valStr . ' ms';

            case 'read_speed_mbps':
            case 'write_speed_mbps':
                return $valStr . ' MB/s';

            case 'max_load_kg':
            case 'weight_kg':
                return $valStr . ' kg';

            case 'weight_g':
                return $valStr . ' g';

            case 'capacity_mah':
            case 'rated_capacity_mah':
                return $valStr . ' mAh';

            case 'warranty_months':
                return $valStr . ' tháng';

            case 'slot_width':
                return $valStr . ' Slot';

            case 'cpu_cores':
            case 'cores':
                return $valStr . ' nhân';

            case 'cpu_threads':
            case 'threads':
                return $valStr . ' luồng';

            default:
                return $valStr;
        }
    }

    /**
     * Lấy tên nhãn Tiếng Việt của thuộc tính.
     */
    public static function getLabel(string $key, ?string $categorySlug = null): string
    {
        if (in_array($key, self::$internalMetadataKeys, true)) {
            return '';
        }

        if ($categorySlug !== null) {
            $schemas = self::getSchemas();
            $catKey = strtolower(trim($categorySlug));
            if (isset($schemas[$catKey])) {
                foreach ($schemas[$catKey]['groups'] as $groupName => $fields) {
                    if (isset($fields[$key]['label'])) {
                        return $fields[$key]['label'];
                    }
                }
            }
        }

        $defaultLabels = [
            'manufacturer' => 'Hãng sản xuất',
            'series' => 'Dòng sản phẩm',
            'warranty_months' => 'Bảo hành',
            'cpu_model' => 'Vi xử lý (CPU)',
            'gpu_model' => 'Card đồ họa (GPU)',
            'ram_capacity_gb' => 'Dung lượng RAM',
            'storage_capacity_gb' => 'Dung lượng ổ cứng',
            'screen_size_inch' => 'Màn hình',
            'refresh_rate_hz' => 'Tần số quét',
        ];

        return $defaultLabels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Phân nhóm thông số kỹ thuật theo 20 Category Schemas.
     * Tự động loại bỏ metadata nội bộ (schema_version, category_slug, model).
     */
    public static function getGroupedSpecs(string $categorySlug, array $specs): array
    {
        $catKey = strtolower(trim($categorySlug));

        // Tách attributes nếu có bọc trong key 'specs' hoặc 'attributes'
        if (isset($specs['attributes']) && is_array($specs['attributes'])) {
            $actualSpecs = $specs['attributes'];
        } elseif (isset($specs['specs']) && is_array($specs['specs'])) {
            $actualSpecs = $specs['specs'];
        } else {
            $actualSpecs = $specs;
        }

        // Lọc bỏ metadata nội bộ
        foreach (self::$internalMetadataKeys as $metaKey) {
            unset($actualSpecs[$metaKey]);
        }

        $schemas = self::getSchemas();

        if (isset($schemas[$catKey])) {
            $schemaDef = $schemas[$catKey];
            $grouped = [];

            foreach ($schemaDef['groups'] as $groupName => $fields) {
                $groupItems = [];
                foreach ($fields as $fieldKey => $fieldMeta) {
                    if (in_array($fieldKey, self::$internalMetadataKeys, true)) {
                        continue;
                    }
                    $rawVal = $actualSpecs[$fieldKey] ?? null;
                    if ($rawVal !== null && $rawVal !== '' && (!is_array($rawVal) || !empty($rawVal))) {
                        $label = $fieldMeta['label'] ?? self::getLabel($fieldKey, $catKey);
                        $unit = $fieldMeta['unit'] ?? '';
                        $groupItems[$label] = self::formatValue($fieldKey, $rawVal, $unit);
                    }
                }
                if (!empty($groupItems)) {
                    $grouped[$groupName] = $groupItems;
                }
            }

            // Các thuộc tính phụ hợp lệ nằm ngoài schema nhóm
            $extraItems = [];
            foreach ($actualSpecs as $key => $val) {
                if (in_array($key, self::$internalMetadataKeys, true)) {
                    continue;
                }
                $found = false;
                foreach ($schemaDef['groups'] as $fields) {
                    if (isset($fields[$key])) {
                        $found = true;
                        break;
                    }
                }
                if (!$found && $val !== null && $val !== '' && (!is_array($val) || !empty($val))) {
                    $label = self::getLabel($key, $catKey);
                    if ($label !== '') {
                        $extraItems[$label] = self::formatValue($key, $val);
                    }
                }
            }
            if (!empty($extraItems)) {
                $grouped['Thông tin bổ sung'] = $extraItems;
            }

            if (!empty($grouped)) {
                return $grouped;
            }
        }

        // Tự động phân nhóm mặc định nếu không khớp schema cụ thể
        $grouped = ['Thông số chính' => []];
        foreach ($actualSpecs as $key => $val) {
            if (in_array($key, self::$internalMetadataKeys, true)) {
                continue;
            }
            if ($val !== null && $val !== '' && (!is_array($val) || !empty($val))) {
                $label = self::getLabel($key, $catKey);
                if ($label !== '') {
                    $grouped['Thông số chính'][$label] = self::formatValue($key, $val);
                }
            }
        }
        return $grouped;
    }
}
