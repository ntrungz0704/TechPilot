<?php

/**
 * ProductSpecPresenter - Single Source of Truth for converting raw JSON specs into Vietnamese labels with units and grouped technical tables.
 * Powered by config/product-spec-schemas.php for 20 tech categories.
 */
class ProductSpecPresenter
{
    private static ?array $schemas = null;

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
     * Chuyển đổi giá trị specs thô thành chuỗi Tiếng Việt có đơn vị chuẩn.
     */
    public static function formatValue(string $key, mixed $value, string $customUnit = ''): string
    {
        if ($value === null || $value === '') {
            return 'Đang cập nhật';
        }

        if (is_bool($value)) {
            return $value ? 'Có' : 'Không';
        }

        if (is_array($value)) {
            return implode(', ', array_map('strval', $value));
        }

        $valStr = (string)$value;

        // Nếu đã có đơn vị tùy chỉnh từ schema
        if ($customUnit !== '') {
            return $valStr . ' ' . $customUnit;
        }

        // Quy tắc chuẩn hóa đơn vị theo key
        switch ($key) {
            case 'vram_gb':
            case 'capacity_gb':
            case 'storage_capacity_gb':
            case 'ram_capacity_gb':
            case 'max_ram_gb':
            case 'gpu_vram_gb':
            case 'memory_gb':
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
                return $valStr . ' Hz'; // ĐÃ SỬA: Hz CHUẨN XÁC!

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
            case 'maximum_total_output_w':
            case 'tdp_capacity_w':
                return $valStr . ' W';

            case 'length_mm':
            case 'width_mm':
            case 'height_mm':
            case 'dimensions_mm':
            case 'radiator_size_mm':
            case 'driver_mm':
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

        // Từ điển bổ sung cho các key tự do
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
     * Phân nhóm thông số kỹ thuật theo 20 Category Schemas để hiển thị bảng chuyên nghiệp.
     */
    public static function getGroupedSpecs(string $categorySlug, array $specs): array
    {
        $catKey = strtolower(trim($categorySlug));
        // Ánh xạ slug ảo nếu có
        $aliasMap = [
            'may-tinh' => 'pc',
            'pc-gaming' => 'pc',
            'linh-kien-pc' => 'vga',
            'man-hinh' => 'monitor',
            'gaming-gear' => 'keyboard',
            'thiet-bi-van-phong' => 'office-equipment',
        ];
        if (isset($aliasMap[$catKey])) {
            $catKey = $aliasMap[$catKey];
        }

        $schemas = self::getSchemas();

        if (isset($schemas[$catKey])) {
            $schemaDef = $schemas[$catKey];
            $grouped = [];

            foreach ($schemaDef['groups'] as $groupName => $fields) {
                $groupItems = [];
                foreach ($fields as $fieldKey => $fieldMeta) {
                    $rawVal = $specs[$fieldKey] ?? null;
                    if ($rawVal !== null && $rawVal !== '') {
                        $label = $fieldMeta['label'] ?? self::getLabel($fieldKey);
                        $unit = $fieldMeta['unit'] ?? '';
                        $groupItems[$label] = self::formatValue($fieldKey, $rawVal, $unit);
                    }
                }
                if (!empty($groupItems)) {
                    $grouped[$groupName] = $groupItems;
                }
            }

            // Nếu có các thuộc tính phụ nằm ngoài schema
            $extraItems = [];
            foreach ($specs as $key => $val) {
                $found = false;
                foreach ($schemaDef['groups'] as $fields) {
                    if (isset($fields[$key])) {
                        $found = true;
                        break;
                    }
                }
                if (!$found && $val !== null && $val !== '') {
                    $extraItems[self::getLabel($key, $catKey)] = self::formatValue($key, $val);
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
        foreach ($specs as $key => $val) {
            if ($val !== null && $val !== '') {
                $grouped['Thông số chính'][self::getLabel($key, $catKey)] = self::formatValue($key, $val);
            }
        }
        return $grouped;
    }
}
