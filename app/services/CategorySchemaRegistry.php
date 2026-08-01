<?php

/**
 * Registry Quản lý Schema Thông số Kỹ thuật theo Danh mục Sản phẩm (TSIE Engine)
 * Định nghĩa chi tiết các nhóm trường, trường bắt buộc (required), trường tùy chọn và đơn vị đo lường
 * cho 10 nhóm danh mục phần cứng E-commerce.
 */
class CategorySchemaRegistry
{
    /**
     * Lấy Schema đầy đủ dựa trên tên danh mục (Category Name hoặc Slug)
     */
    public static function getSchemaForCategory(string $categoryName): array
    {
        $catLower = strtolower(trim($categoryName));

        if (str_contains($catLower, 'laptop') || str_contains($catLower, 'macbook')) {
            return self::getLaptopSchema();
        } elseif (str_contains($catLower, 'vga') || str_contains($catLower, 'card màn hình') || str_contains($catLower, 'gpu')) {
            return self::getVgaSchema();
        } elseif (str_contains($catLower, 'cpu') || str_contains($catLower, 'vi xử lý')) {
            return self::getCpuSchema();
        } elseif (str_contains($catLower, 'ram') || str_contains($catLower, 'bộ nhớ trong')) {
            return self::getRamSchema();
        } elseif (str_contains($catLower, 'ssd') || str_contains($catLower, 'ổ cứng') || str_contains($catLower, 'hdd') || str_contains($catLower, 'storage')) {
            return self::getStorageSchema();
        } elseif (str_contains($catLower, 'mainboard') || str_contains($catLower, 'bo mạch chủ')) {
            return self::getMainboardSchema();
        } elseif (str_contains($catLower, 'màn hình') || str_contains($catLower, 'monitor')) {
            return self::getMonitorSchema();
        } elseif (str_contains($catLower, 'psu') || str_contains($catLower, 'nguồn')) {
            return self::getPsuSchema();
        } elseif (str_contains($catLower, 'tản nhiệt') || str_contains($catLower, 'cooling')) {
            return self::getCoolingSchema();
        }

        return self::getGenericSchema();
    }

    /**
     * Schema cho Laptop
     */
    public static function getLaptopSchema(): array
    {
        return [
            'category_code' => 'laptop',
            'category_label' => 'Laptop',
            'groups' => [
                [
                    'group_name' => 'Hiệu năng',
                    'fields' => [
                        ['key' => 'cpu_model', 'label' => 'Model CPU', 'type' => 'string', 'required' => true],
                        ['key' => 'cpu_cores_threads', 'label' => 'Số nhân / Số luồng', 'type' => 'string', 'required' => true],
                        ['key' => 'gpu_model', 'label' => 'Card đồ họa (GPU)', 'type' => 'string', 'required' => true],
                        ['key' => 'vram_gb', 'label' => 'Dung lượng VRAM', 'type' => 'string', 'required' => false]
                    ]
                ],
                [
                    'group_name' => 'Bộ nhớ & Lưu trữ',
                    'fields' => [
                        ['key' => 'ram_spec', 'label' => 'Dung lượng & Loại RAM', 'type' => 'string', 'required' => true],
                        ['key' => 'ram_bus_mhz', 'label' => 'Bus RAM', 'type' => 'string', 'required' => false],
                        ['key' => 'storage_spec', 'label' => 'Dung lượng & Loại ổ cứng', 'type' => 'string', 'required' => true]
                    ]
                ],
                [
                    'group_name' => 'Màn hình',
                    'fields' => [
                        ['key' => 'display_size', 'label' => 'Kích thước màn hình', 'type' => 'string', 'required' => true],
                        ['key' => 'resolution', 'label' => 'Độ phân giải', 'type' => 'string', 'required' => true],
                        ['key' => 'refresh_rate_hz', 'label' => 'Tần số quét', 'type' => 'string', 'required' => false],
                        ['key' => 'panel_type', 'label' => 'Tấm nền', 'type' => 'string', 'required' => false]
                    ]
                ],
                [
                    'group_name' => 'Thiết kế & Pin',
                    'fields' => [
                        ['key' => 'battery_capacity', 'label' => 'Dung lượng Pin', 'type' => 'string', 'required' => false],
                        ['key' => 'weight_kg', 'label' => 'Trọng lượng', 'type' => 'string', 'required' => true]
                    ]
                ],
                [
                    'group_name' => 'Kết nối & Bổ sung',
                    'fields' => [
                        ['key' => 'os', 'label' => 'Hệ điều hành', 'type' => 'string', 'required' => true],
                        ['key' => 'warranty', 'label' => 'Bảo hành', 'type' => 'string', 'required' => true]
                    ]
                ]
            ]
        ];
    }

    /**
     * Schema cho VGA - Card màn hình
     */
    public static function getVgaSchema(): array
    {
        return [
            'category_code' => 'vga',
            'category_label' => 'VGA - Card Màn Hình',
            'groups' => [
                [
                    'group_name' => 'Xử lý đồ họa',
                    'fields' => [
                        ['key' => 'gpu_chipset', 'label' => 'Chipset đồ họa', 'type' => 'string', 'required' => true],
                        ['key' => 'vram_size', 'label' => 'Dung lượng VRAM', 'type' => 'string', 'required' => true],
                        ['key' => 'memory_type', 'label' => 'Loại bộ nhớ (GDDR)', 'type' => 'string', 'required' => true],
                        ['key' => 'bus_width', 'label' => 'Băng thông bộ nhớ (Bus)', 'type' => 'string', 'required' => false]
                    ]
                ],
                [
                    'group_name' => 'Nguồn & Kích thước',
                    'fields' => [
                        ['key' => 'psu_recommended', 'label' => 'Nguồn đề xuất (PSU Watt)', 'type' => 'string', 'required' => true],
                        ['key' => 'power_connectors', 'label' => 'Cổng nguồn phụ', 'type' => 'string', 'required' => false],
                        ['key' => 'card_length', 'label' => 'Kích thước / Chiều dài card', 'type' => 'string', 'required' => false]
                    ]
                ],
                [
                    'group_name' => 'Cổng xuất hình & Bảo hành',
                    'fields' => [
                        ['key' => 'output_ports', 'label' => 'Cổng xuất hình (HDMI/DP)', 'type' => 'string', 'required' => true],
                        ['key' => 'warranty', 'label' => 'Bảo hành', 'type' => 'string', 'required' => true]
                    ]
                ]
            ]
        ];
    }

    /**
     * Schema cho CPU - Vi xử lý
     */
    public static function getCpuSchema(): array
    {
        return [
            'category_code' => 'cpu',
            'category_label' => 'CPU - Bộ Vi Xử Lý',
            'groups' => [
                [
                    'group_name' => 'Thông số cốt lõi',
                    'fields' => [
                        ['key' => 'socket', 'label' => 'Socket hỗ trợ', 'type' => 'string', 'required' => true],
                        ['key' => 'cores_threads', 'label' => 'Số nhân / Số luồng', 'type' => 'string', 'required' => true],
                        ['key' => 'base_clock', 'label' => 'Xung nhịp cơ bản', 'type' => 'string', 'required' => true],
                        ['key' => 'boost_clock', 'label' => 'Xung nhịp tối đa (Boost)', 'type' => 'string', 'required' => true],
                        ['key' => 'cache', 'label' => 'Bộ nhớ đệm (Cache)', 'type' => 'string', 'required' => false]
                    ]
                ],
                [
                    'group_name' => 'Điện năng & Đồ họa',
                    'fields' => [
                        ['key' => 'tdp', 'label' => 'Công suất tiêu thụ (TDP)', 'type' => 'string', 'required' => true],
                        ['key' => 'igpu', 'label' => 'Nhân đồ họa tích hợp (iGPU)', 'type' => 'string', 'required' => false],
                        ['key' => 'warranty', 'label' => 'Bảo hành', 'type' => 'string', 'required' => true]
                    ]
                ]
            ]
        ];
    }

    /**
     * Schema cho RAM - Bộ nhớ trong
     */
    public static function getRamSchema(): array
    {
        return [
            'category_code' => 'ram',
            'category_label' => 'RAM - Bộ Nhớ Trong',
            'groups' => [
                [
                    'group_name' => 'Thông số bộ nhớ',
                    'fields' => [
                        ['key' => 'capacity', 'label' => 'Dung lượng RAM', 'type' => 'string', 'required' => true],
                        ['key' => 'ram_type', 'label' => 'Chuẩn RAM (DDR4/DDR5)', 'type' => 'string', 'required' => true],
                        ['key' => 'bus_speed', 'label' => 'Bus RAM (Speed MHz)', 'type' => 'string', 'required' => true],
                        ['key' => 'latency_cl', 'label' => 'Độ trễ Latency (CL)', 'type' => 'string', 'required' => false],
                        ['key' => 'voltage', 'label' => 'Điện áp (Voltage)', 'type' => 'string', 'required' => false],
                        ['key' => 'warranty', 'label' => 'Bảo hành', 'type' => 'string', 'required' => true]
                    ]
                ]
            ]
        ];
    }

    /**
     * Schema cho SSD / Storage
     */
    public static function getStorageSchema(): array
    {
        return [
            'category_code' => 'storage',
            'category_label' => 'SSD / Ổ Cứng Lưu Trữ',
            'groups' => [
                [
                    'group_name' => 'Thông số lưu trữ',
                    'fields' => [
                        ['key' => 'capacity', 'label' => 'Dung lượng ổ cứng', 'type' => 'string', 'required' => true],
                        ['key' => 'interface', 'label' => 'Chuẩn giao tiếp (NVMe/SATA)', 'type' => 'string', 'required' => true],
                        ['key' => 'read_speed', 'label' => 'Tốc độ đọc tối đa', 'type' => 'string', 'required' => true],
                        ['key' => 'write_speed', 'label' => 'Tốc độ ghi tối đa', 'type' => 'string', 'required' => true],
                        ['key' => 'form_factor', 'label' => 'Kích thước / Chuẩn chân cắm (M.2 2280/2.5")', 'type' => 'string', 'required' => false],
                        ['key' => 'warranty', 'label' => 'Bảo hành', 'type' => 'string', 'required' => true]
                    ]
                ]
            ]
        ];
    }

    /**
     * Schema cho Mainboard
     */
    public static function getMainboardSchema(): array
    {
        return [
            'category_code' => 'mainboard',
            'category_label' => 'Mainboard - Bo Mạch Chủ',
            'groups' => [
                [
                    'group_name' => 'Tương thích & Chuẩn',
                    'fields' => [
                        ['key' => 'chipset', 'label' => 'Chipset', 'type' => 'string', 'required' => true],
                        ['key' => 'socket', 'label' => 'Socket CPU', 'type' => 'string', 'required' => true],
                        ['key' => 'form_factor', 'label' => 'Kích thước chuẩn (ATX/mATX/ITX)', 'type' => 'string', 'required' => true],
                        ['key' => 'ram_slots', 'label' => 'Số khe RAM & Dung lượng tối đa', 'type' => 'string', 'required' => true],
                        ['key' => 'warranty', 'label' => 'Bảo hành', 'type' => 'string', 'required' => true]
                    ]
                ]
            ]
        ];
    }

    /**
     * Schema cho Monitor
     */
    public static function getMonitorSchema(): array
    {
        return [
            'category_code' => 'monitor',
            'category_label' => 'Màn Hình Máy Tính',
            'groups' => [
                [
                    'group_name' => 'Hiển thị & Tần số',
                    'fields' => [
                        ['key' => 'screen_size', 'label' => 'Kích thước màn hình', 'type' => 'string', 'required' => true],
                        ['key' => 'resolution', 'label' => 'Độ phân giải', 'type' => 'string', 'required' => true],
                        ['key' => 'panel_type', 'label' => 'Tấm nền (IPS/VA/OLED)', 'type' => 'string', 'required' => true],
                        ['key' => 'refresh_rate', 'label' => 'Tần số quét (Hz)', 'type' => 'string', 'required' => true],
                        ['key' => 'response_time', 'label' => 'Thời gian phản hồi (ms)', 'type' => 'string', 'required' => false],
                        ['key' => 'warranty', 'label' => 'Bảo hành', 'type' => 'string', 'required' => true]
                    ]
                ]
            ]
        ];
    }

    /**
     * Schema cho PSU - Nguồn
     */
    public static function getPsuSchema(): array
    {
        return [
            'category_code' => 'psu',
            'category_label' => 'Nguồn Máy Tính (PSU)',
            'groups' => [
                [
                    'group_name' => 'Công suất & Hiệu suất',
                    'fields' => [
                        ['key' => 'wattage', 'label' => 'Công suất thực (Watt)', 'type' => 'string', 'required' => true],
                        ['key' => 'efficiency_rating', 'label' => 'Chứng nhận hiệu suất (80 Plus)', 'type' => 'string', 'required' => true],
                        ['key' => 'modularity', 'label' => 'Dạng dây cắm (Full Modular/Non-Modular)', 'type' => 'string', 'required' => false],
                        ['key' => 'warranty', 'label' => 'Bảo hành', 'type' => 'string', 'required' => true]
                    ]
                ]
            ]
        ];
    }

    /**
     * Schema cho Tản nhiệt
     */
    public static function getCoolingSchema(): array
    {
        return [
            'category_code' => 'cooling',
            'category_label' => 'Tản Nhiệt CPU / Fan',
            'groups' => [
                [
                    'group_name' => 'Tản nhiệt',
                    'fields' => [
                        ['key' => 'cooling_type', 'label' => 'Loại tản nhiệt (Khí / AIO Nước)', 'type' => 'string', 'required' => true],
                        ['key' => 'radiator_size', 'label' => 'Kích thước Két nước / Quạt (120/240/360mm)', 'type' => 'string', 'required' => true],
                        ['key' => 'socket_support', 'label' => 'Socket hỗ trợ', 'type' => 'string', 'required' => true],
                        ['key' => 'warranty', 'label' => 'Bảo hành', 'type' => 'string', 'required' => true]
                    ]
                ]
            ]
        ];
    }

    /**
     * Schema chung cho các loại sản phẩm khác
     */
    public static function getGenericSchema(): array
    {
        return [
            'category_code' => 'generic',
            'category_label' => 'Thiết bị & Phụ kiện Công nghệ',
            'groups' => [
                [
                    'group_name' => 'Thông số chính',
                    'fields' => [
                        ['key' => 'model', 'label' => 'Model sản phẩm', 'type' => 'string', 'required' => true],
                        ['key' => 'brand', 'label' => 'Thương hiệu', 'type' => 'string', 'required' => true],
                        ['key' => 'spec_summary', 'label' => 'Thông số nổi bật', 'type' => 'string', 'required' => true],
                        ['key' => 'warranty', 'label' => 'Bảo hành', 'type' => 'string', 'required' => true]
                    ]
                ]
            ]
        ];
    }
}
