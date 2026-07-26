<?php

/**
 * ProductSpecPresenter - Single Source of Truth for converting raw JSON specs into Vietnamese labels with units and grouped technical tables.
 */
class ProductSpecPresenter
{
    private static array $specLabels = [
        // Common & General
        'manufacturer' => 'Hãng sản xuất',
        'series' => 'Dòng sản phẩm',
        'warranty_months' => 'Thời gian bảo hành',
        'use_case_fit' => 'Nhu cầu phù hợp',

        // VGA Specs
        'gpu_model' => 'Chip đồ họa (GPU)',
        'architecture' => 'Kiến trúc',
        'vram_gb' => 'Dung lượng VRAM',
        'vram_type' => 'Loại bộ nhớ (VRAM)',
        'memory_bus_bit' => 'Bus bộ nhớ',
        'boost_clock_mhz' => 'Xung nhịp Boost',
        'power_draw_w' => 'Công suất tiêu thụ',
        'recommended_psu_w' => 'Khuyên dùng nguồn (PSU)',
        'power_connectors' => 'Đầu cấp nguồn',
        'length_mm' => 'Chiều dài',
        'width_mm' => 'Chiều rộng',
        'height_mm' => 'Chiều cao',
        'slot_width' => 'Độ dày (Slot)',
        'display_outputs' => 'Cổng xuất hình',
        'max_resolution' => 'Độ phân giải tối đa',

        // CPU Specs
        'socket' => 'Socket hỗ trợ',
        'cores' => 'Số nhân',
        'threads' => 'Số luồng',
        'base_clock_ghz' => 'Xung cơ bản',
        'boost_clock_ghz' => 'Xung Boost tối đa',
        'tdp_w' => 'Công suất tỏa nhiệt (TDP)',
        'supported_ram_types' => 'Loại RAM hỗ trợ',

        // Mainboard Specs
        'chipset' => 'Chipset',
        'form_factor' => 'Kích thước Mainboard',
        'ram_type' => 'Chuẩn RAM hỗ trợ',
        'dimm_slots' => 'Số khe cắm RAM',
        'max_ram_gb' => 'Dung lượng RAM tối đa',

        // RAM Specs
        'capacity_gb' => 'Dung lượng RAM',
        'speed_mhz' => 'Tốc độ bus (Speed)',
        'cas_latency' => 'Độ trễ (CAS Latency)',

        // Storage Specs
        'storage_type' => 'Loại ổ cứng',
        'interface' => 'Chuẩn kết nối',
        'read_speed_mbps' => 'Tốc độ đọc tối đa',
        'write_speed_mbps' => 'Tốc độ ghi tối đa',

        // Monitor Specs
        'screen_size_inch' => 'Kích thước màn hình',
        'resolution' => 'Độ phân giải',
        'panel_type' => 'Tấm nền (Panel)',
        'refresh_rate_hz' => 'Tần số quét',
        'response_time_ms' => 'Thời gian phản hồi',

        // Laptop & PC Specs
        'cpu_model' => 'Vi xử lý (CPU)',
        'storage_capacity_gb' => 'Dung lượng ổ cứng',
        'psu_wattage' => 'Công suất nguồn',

        // Peripherals Specs
        'layout' => 'Bố cục (Layout)',
        'switch_type' => 'Loại Switch',
        'rgb' => 'Đèn LED RGB',
        'max_dpi' => 'Độ phân giải mắt đọc (DPI)',
        'weight_g' => 'Trọng lượng',
        'max_load_kg' => 'Tải trọng tối đa',
        'material' => 'Chất liệu',
        'recline_degree' => 'Góc ngả lưng',
        'driver_mm' => 'Kích thước Màng loa (Driver)',
        'surround' => 'Công nghệ âm thanh',
        'channels' => 'Kênh âm thanh',
        'total_power_w' => 'Tổng công suất (RMS)',
        'capacity_mah' => 'Dung lượng pin',
        'max_output_w' => 'Công suất sạc tối đa'
    ];

    /**
     * Chuyển đổi giá trị specs thô thành chuỗi Tiếng Việt có đơn vị.
     */
    public static function formatValue(string $key, mixed $value): string
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

        switch ($key) {
            case 'vram_gb':
            case 'capacity_gb':
            case 'storage_capacity_gb':
            case 'ram_capacity_gb':
            case 'max_ram_gb':
                return $valStr . ' GB';

            case 'memory_bus_bit':
                return $valStr . '-bit';

            case 'boost_clock_mhz':
            case 'speed_mhz':
            case 'refresh_rate_hz':
                return $valStr . ' MHz';

            case 'base_clock_ghz':
            case 'boost_clock_ghz':
                return $valStr . ' GHz';

            case 'power_draw_w':
            case 'recommended_psu_w':
            case 'tdp_w':
            case 'psu_wattage':
            case 'total_power_w':
            case 'max_output_w':
            case 'wattage':
                return $valStr . ' W';

            case 'length_mm':
            case 'width_mm':
            case 'height_mm':
                return $valStr . ' mm';

            case 'screen_size_inch':
                return $valStr . ' inch';

            case 'response_time_ms':
                return $valStr . ' ms';

            case 'read_speed_mbps':
            case 'write_speed_mbps':
                return $valStr . ' MB/s';

            case 'max_load_kg':
                return $valStr . ' kg';

            case 'weight_g':
                return $valStr . ' g';

            case 'capacity_mah':
                return $valStr . ' mAh';

            case 'warranty_months':
                return $valStr . ' tháng';

            case 'slot_width':
                return $valStr . ' Slot';

            case 'cores':
                return $valStr . ' nhân';

            case 'threads':
                return $valStr . ' luồng';

            default:
                return $valStr;
        }
    }

    /**
     * Lấy tên nhãn Tiếng Việt của thuộc tính.
     */
    public static function getLabel(string $key): string
    {
        return self::$specLabels[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Phân nhóm thông số kỹ thuật theo từng Category để hiển thị bảng chuyên nghiệp.
     */
    public static function getGroupedSpecs(string $categorySlug, array $specs): array
    {
        $slug = strtolower(trim($categorySlug));

        if ($slug === 'vga') {
            return [
                'Thông tin GPU' => [
                    'gpu_model' => self::formatValue('gpu_model', $specs['gpu_model'] ?? null),
                    'architecture' => self::formatValue('architecture', $specs['architecture'] ?? null),
                    'boost_clock_mhz' => self::formatValue('boost_clock_mhz', $specs['boost_clock_mhz'] ?? null),
                ],
                'Bộ nhớ Đồ họa (VRAM)' => [
                    'vram_gb' => self::formatValue('vram_gb', $specs['vram_gb'] ?? null),
                    'vram_type' => self::formatValue('vram_type', $specs['vram_type'] ?? null),
                    'memory_bus_bit' => self::formatValue('memory_bus_bit', $specs['memory_bus_bit'] ?? null),
                ],
                'Nguồn & Tương thích' => [
                    'power_draw_w' => self::formatValue('power_draw_w', $specs['power_draw_w'] ?? null),
                    'recommended_psu_w' => self::formatValue('recommended_psu_w', $specs['recommended_psu_w'] ?? null),
                    'power_connectors' => self::formatValue('power_connectors', $specs['power_connectors'] ?? null),
                    'length_mm' => self::formatValue('length_mm', $specs['length_mm'] ?? null),
                    'slot_width' => self::formatValue('slot_width', $specs['slot_width'] ?? null),
                ],
                'Cổng kết nối' => [
                    'display_outputs' => self::formatValue('display_outputs', $specs['display_outputs'] ?? null),
                    'max_resolution' => self::formatValue('max_resolution', $specs['max_resolution'] ?? null),
                    'warranty_months' => self::formatValue('warranty_months', $specs['warranty_months'] ?? 36),
                ]
            ];
        }

        // General Grouping for other categories
        $grouped = ['Thông số chính' => []];
        foreach ($specs as $key => $val) {
            $grouped['Thông số chính'][self::getLabel($key)] = self::formatValue($key, $val);
        }
        return $grouped;
    }
}
