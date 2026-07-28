<?php
/**
 * Product Specification Schemas Registry for 20 Categories in TechPilot.
 * Single Source of Truth for labels, data types, units, input validation, and display grouping.
 */

return [
    'laptop' => [
        'name' => 'Laptop',
        'groups' => [
            'Hiệu năng' => [
                'cpu_model' => ['label' => 'Model CPU', 'type' => 'string', 'unit' => '', 'required' => true],
                'cpu_generation' => ['label' => 'Thế hệ CPU', 'type' => 'string', 'unit' => '', 'required' => false],
                'cpu_cores' => ['label' => 'Số nhân CPU', 'type' => 'integer', 'unit' => 'nhân', 'required' => false],
                'cpu_threads' => ['label' => 'Số luồng CPU', 'type' => 'integer', 'unit' => 'luồng', 'required' => false],
                'cpu_base_clock_ghz' => ['label' => 'Xung nhịp cơ bản', 'type' => 'float', 'unit' => 'GHz', 'required' => false],
                'cpu_boost_clock_ghz' => ['label' => 'Xung nhịp tối đa', 'type' => 'float', 'unit' => 'GHz', 'required' => false],
                'gpu_model' => ['label' => 'Card đồ họa (GPU)', 'type' => 'string', 'unit' => '', 'required' => true],
                'gpu_type' => ['label' => 'Loại GPU', 'type' => 'enum', 'options' => ['Rời', 'Tích hợp'], 'unit' => '', 'required' => false],
                'gpu_vram_gb' => ['label' => 'Dung lượng VRAM', 'type' => 'integer', 'unit' => 'GB', 'required' => false],
            ],
            'Bộ nhớ & Lưu trữ' => [
                'ram_capacity_gb' => ['label' => 'Dung lượng RAM', 'type' => 'integer', 'unit' => 'GB', 'required' => true],
                'ram_type' => ['label' => 'Loại RAM', 'type' => 'string', 'unit' => '', 'required' => false],
                'ram_speed_mhz' => ['label' => 'Bus RAM', 'type' => 'integer', 'unit' => 'MHz', 'required' => false],
                'ram_slots' => ['label' => 'Số khe RAM', 'type' => 'integer', 'unit' => 'khe', 'required' => false],
                'max_ram_gb' => ['label' => 'RAM tối đa hỗ trợ', 'type' => 'integer', 'unit' => 'GB', 'required' => false],
                'storage_type' => ['label' => 'Loại ổ cứng', 'type' => 'string', 'unit' => '', 'required' => true],
                'storage_capacity_gb' => ['label' => 'Dung lượng ổ cứng', 'type' => 'integer', 'unit' => 'GB', 'required' => true],
                'storage_interface' => ['label' => 'Chuẩn kết nối SSD', 'type' => 'string', 'unit' => '', 'required' => false],
            ],
            'Màn hình' => [
                'screen_size_inch' => ['label' => 'Kích thước màn hình', 'type' => 'float', 'unit' => 'inch', 'required' => true],
                'resolution' => ['label' => 'Độ phân giải', 'type' => 'string', 'unit' => '', 'required' => true],
                'panel_type' => ['label' => 'Tấm nền', 'type' => 'string', 'unit' => '', 'required' => false],
                'refresh_rate_hz' => ['label' => 'Tần số quét', 'type' => 'integer', 'unit' => 'Hz', 'required' => false],
                'brightness_nits' => ['label' => 'Độ sáng', 'type' => 'integer', 'unit' => 'nits', 'required' => false],
                'color_gamut' => ['label' => 'Độ phủ màu', 'type' => 'string', 'unit' => '', 'required' => false],
                'touchscreen' => ['label' => 'Màn hình cảm ứng', 'type' => 'boolean', 'unit' => '', 'required' => false],
            ],
            'Thiết kế & Pin' => [
                'battery_wh' => ['label' => 'Dung lượng Pin', 'type' => 'integer', 'unit' => 'Wh', 'required' => false],
                'weight_kg' => ['label' => 'Trọng lượng', 'type' => 'float', 'unit' => 'kg', 'required' => false],
                'material' => ['label' => 'Chất liệu vỏ', 'type' => 'string', 'unit' => '', 'required' => false],
                'dimensions_mm' => ['label' => 'Kích thước', 'type' => 'string', 'unit' => 'mm', 'required' => false],
            ],
            'Kết nối & Tính năng' => [
                'operating_system' => ['label' => 'Hệ điều hành', 'type' => 'string', 'unit' => '', 'required' => false],
                'ports' => ['label' => 'Cổng kết nối', 'type' => 'array', 'unit' => '', 'required' => false],
                'wireless' => ['label' => 'Kết nối không dây', 'type' => 'string', 'unit' => '', 'required' => false],
                'webcam' => ['label' => 'Webcam', 'type' => 'string', 'unit' => '', 'required' => false],
                'keyboard_backlight' => ['label' => 'Đèn nền bàn phím', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'pc' => [
        'name' => 'PC Bộ',
        'groups' => [
            'Hiệu năng chính' => [
                'cpu_model' => ['label' => 'Model CPU', 'type' => 'string', 'unit' => '', 'required' => true],
                'cpu_cores' => ['label' => 'Số nhân CPU', 'type' => 'integer', 'unit' => 'nhân', 'required' => false],
                'cpu_threads' => ['label' => 'Số luồng CPU', 'type' => 'integer', 'unit' => 'luồng', 'required' => false],
                'mainboard_model' => ['label' => 'Bo mạch chủ (Mainboard)', 'type' => 'string', 'unit' => '', 'required' => false],
                'chipset' => ['label' => 'Chipset', 'type' => 'string', 'unit' => '', 'required' => false],
                'gpu_model' => ['label' => 'Card đồ họa (GPU)', 'type' => 'string', 'unit' => '', 'required' => true],
                'gpu_vram_gb' => ['label' => 'VRAM', 'type' => 'integer', 'unit' => 'GB', 'required' => false],
            ],
            'Bộ nhớ & Nguồn' => [
                'ram_capacity_gb' => ['label' => 'Dung lượng RAM', 'type' => 'integer', 'unit' => 'GB', 'required' => true],
                'ram_type' => ['label' => 'Chuẩn RAM', 'type' => 'string', 'unit' => '', 'required' => false],
                'ram_speed_mhz' => ['label' => 'Tốc độ RAM', 'type' => 'integer', 'unit' => 'MHz', 'required' => false],
                'storage_devices' => ['label' => 'Ổ cứng lưu trữ', 'type' => 'string', 'unit' => '', 'required' => true],
                'psu_wattage' => ['label' => 'Công suất Nguồn (PSU)', 'type' => 'integer', 'unit' => 'W', 'required' => false],
                'psu_efficiency' => ['label' => 'Chuẩn chứng nhận PSU', 'type' => 'string', 'unit' => '', 'required' => false],
            ],
            'Vỏ case & Tản nhiệt' => [
                'case_model' => ['label' => 'Vỏ Case', 'type' => 'string', 'unit' => '', 'required' => false],
                'cooling_system' => ['label' => 'Hệ thống tản nhiệt', 'type' => 'string', 'unit' => '', 'required' => false],
                'operating_system' => ['label' => 'Hệ điều hành', 'type' => 'string', 'unit' => '', 'required' => false],
                'recommended_use_cases' => ['label' => 'Nhu cầu khuyên dùng', 'type' => 'array', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Thời gian bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'monitor' => [
        'name' => 'Màn hình',
        'groups' => [
            'Hiển thị' => [
                'screen_size_inch' => ['label' => 'Kích thước màn hình', 'type' => 'float', 'unit' => 'inch', 'required' => true],
                'resolution' => ['label' => 'Độ phân giải', 'type' => 'string', 'unit' => '', 'required' => true],
                'aspect_ratio' => ['label' => 'Tỉ lệ khung hình', 'type' => 'string', 'unit' => '', 'required' => false],
                'panel_type' => ['label' => 'Tấm nền', 'type' => 'string', 'unit' => '', 'required' => true],
                'refresh_rate_hz' => ['label' => 'Tần số quét', 'type' => 'integer', 'unit' => 'Hz', 'required' => true],
                'response_time_ms' => ['label' => 'Thời gian phản hồi', 'type' => 'float', 'unit' => 'ms', 'required' => false],
                'brightness_nits' => ['label' => 'Độ sáng', 'type' => 'integer', 'unit' => 'nits', 'required' => false],
                'contrast_ratio' => ['label' => 'Độ tương phản', 'type' => 'string', 'unit' => '', 'required' => false],
                'color_gamut_srgb' => ['label' => 'Độ phủ màu sRGB', 'type' => 'string', 'unit' => '', 'required' => false],
                'hdr_standard' => ['label' => 'Chuẩn HDR', 'type' => 'string', 'unit' => '', 'required' => false],
                'adaptive_sync' => ['label' => 'Công nghệ chống xé hình', 'type' => 'string', 'unit' => '', 'required' => false],
            ],
            'Thiết kế & Kết nối' => [
                'curved' => ['label' => 'Màn hình cong', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'ports' => ['label' => 'Cổng xuất hình', 'type' => 'array', 'unit' => '', 'required' => false],
                'speaker' => ['label' => 'Loa tích hợp', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'vesa_mount' => ['label' => 'Hỗ trợ ngàm VESA', 'type' => 'string', 'unit' => '', 'required' => false],
                'weight_kg' => ['label' => 'Trọng lượng', 'type' => 'float', 'unit' => 'kg', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'mainboard' => [
        'name' => 'Mainboard',
        'groups' => [
            'Cấu hình chính' => [
                'socket' => ['label' => 'Socket CPU', 'type' => 'string', 'unit' => '', 'required' => true],
                'chipset' => ['label' => 'Chipset', 'type' => 'string', 'unit' => '', 'required' => true],
                'form_factor' => ['label' => 'Kích thước (Form Factor)', 'type' => 'string', 'unit' => '', 'required' => true],
                'supported_cpu_generations' => ['label' => 'Thế hệ CPU hỗ trợ', 'type' => 'string', 'unit' => '', 'required' => false],
            ],
            'RAM & Mở rộng' => [
                'ram_type' => ['label' => 'Chuẩn RAM', 'type' => 'string', 'unit' => '', 'required' => true],
                'dimm_slots' => ['label' => 'Số khe RAM', 'type' => 'integer', 'unit' => 'khe', 'required' => true],
                'max_ram_gb' => ['label' => 'Dung lượng RAM tối đa', 'type' => 'integer', 'unit' => 'GB', 'required' => false],
                'max_ram_speed_mhz' => ['label' => 'Bus RAM tối đa', 'type' => 'integer', 'unit' => 'MHz', 'required' => false],
                'pcie_slots' => ['label' => 'Khe PCIe', 'type' => 'string', 'unit' => '', 'required' => false],
                'm2_slots' => ['label' => 'Số khe M.2 SSD', 'type' => 'integer', 'unit' => 'khe', 'required' => false],
                'sata_ports' => ['label' => 'Số cổng SATA 3', 'type' => 'integer', 'unit' => 'cổng', 'required' => false],
            ],
            'Kết nối & Tính năng' => [
                'lan_speed' => ['label' => 'Tốc độ Cổng LAN', 'type' => 'string', 'unit' => '', 'required' => false],
                'wifi' => ['label' => 'Kết nối Wi-Fi', 'type' => 'string', 'unit' => '', 'required' => false],
                'bluetooth' => ['label' => 'Bluetooth', 'type' => 'string', 'unit' => '', 'required' => false],
                'audio_codec' => ['label' => 'Chip Âm thanh', 'type' => 'string', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'cpu' => [
        'name' => 'CPU',
        'groups' => [
            'Thông số xử lý' => [
                'socket' => ['label' => 'Socket', 'type' => 'string', 'unit' => '', 'required' => true],
                'architecture' => ['label' => 'Kiến trúc', 'type' => 'string', 'unit' => '', 'required' => false],
                'generation' => ['label' => 'Thế hệ', 'type' => 'string', 'unit' => '', 'required' => false],
                'cores' => ['label' => 'Số nhân', 'type' => 'integer', 'unit' => 'nhân', 'required' => true],
                'threads' => ['label' => 'Số luồng', 'type' => 'integer', 'unit' => 'luồng', 'required' => true],
                'base_clock_ghz' => ['label' => 'Xung cơ bản', 'type' => 'float', 'unit' => 'GHz', 'required' => true],
                'boost_clock_ghz' => ['label' => 'Xung Boost tối đa', 'type' => 'float', 'unit' => 'GHz', 'required' => true],
                'cache_l3_mb' => ['label' => 'Bộ nhớ đệm L3', 'type' => 'float', 'unit' => 'MB', 'required' => false],
            ],
            'Nguồn & Bộ nhớ' => [
                'tdp_w' => ['label' => 'Công suất tiêu thụ (TDP)', 'type' => 'integer', 'unit' => 'W', 'required' => true],
                'integrated_graphics' => ['label' => 'Đồ họa tích hợp (iGPU)', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'igpu_model' => ['label' => 'Model iGPU', 'type' => 'string', 'unit' => '', 'required' => false],
                'memory_types' => ['label' => 'Loại RAM hỗ trợ', 'type' => 'string', 'unit' => '', 'required' => false],
                'cooler_included' => ['label' => 'Đi kèm tản nhiệt', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'unlocked' => ['label' => 'Hỗ trợ Ép xung (Unlocked)', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'vga' => [
        'name' => 'Card màn hình (VGA)',
        'groups' => [
            'Chip đồ họa (GPU)' => [
                'gpu_model' => ['label' => 'Model GPU', 'type' => 'string', 'unit' => '', 'required' => true],
                'architecture' => ['label' => 'Kiến trúc', 'type' => 'string', 'unit' => '', 'required' => false],
                'cuda_or_stream_processors' => ['label' => 'Số nhân xử lý (CUDA/Stream)', 'type' => 'integer', 'unit' => 'nhân', 'required' => false],
                'base_clock_mhz' => ['label' => 'Xung cơ bản', 'type' => 'integer', 'unit' => 'MHz', 'required' => false],
                'boost_clock_mhz' => ['label' => 'Xung Boost', 'type' => 'integer', 'unit' => 'MHz', 'required' => true],
            ],
            'VRAM & Băng thông' => [
                'vram_gb' => ['label' => 'Dung lượng VRAM', 'type' => 'integer', 'unit' => 'GB', 'required' => true],
                'vram_type' => ['label' => 'Chuẩn VRAM', 'type' => 'string', 'unit' => '', 'required' => true],
                'memory_bus_bit' => ['label' => 'Bus bộ nhớ', 'type' => 'integer', 'unit' => 'bit', 'required' => false],
                'ray_tracing_support' => ['label' => 'Hỗ trợ Ray Tracing', 'type' => 'boolean', 'unit' => '', 'required' => false],
            ],
            'Nguồn & Kích thước' => [
                'power_draw_w' => ['label' => 'Công suất tiêu thụ', 'type' => 'integer', 'unit' => 'W', 'required' => true],
                'recommended_psu_w' => ['label' => 'Khuyên dùng nguồn (PSU)', 'type' => 'integer', 'unit' => 'W', 'required' => true],
                'power_connectors' => ['label' => 'Đầu cấp nguồn', 'type' => 'string', 'unit' => '', 'required' => false],
                'length_mm' => ['label' => 'Chiều dài card', 'type' => 'integer', 'unit' => 'mm', 'required' => false],
                'slot_width' => ['label' => 'Độ dày (Slot)', 'type' => 'string', 'unit' => 'Slot', 'required' => false],
                'display_outputs' => ['label' => 'Cổng xuất hình', 'type' => 'array', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'ram' => [
        'name' => 'RAM',
        'groups' => [
            'Thông số RAM' => [
                'memory_type' => ['label' => 'Chuẩn RAM (DDR4/DDR5)', 'type' => 'string', 'unit' => '', 'required' => true],
                'capacity_gb' => ['label' => 'Tổng dung lượng', 'type' => 'integer', 'unit' => 'GB', 'required' => true],
                'module_count' => ['label' => 'Số lượng thanh RAM', 'type' => 'integer', 'unit' => 'thanh', 'required' => true],
                'speed_mhz' => ['label' => 'Tốc độ bus (Speed)', 'type' => 'integer', 'unit' => 'MHz', 'required' => true],
                'cas_latency' => ['label' => 'Độ trễ (CAS Latency)', 'type' => 'string', 'unit' => '', 'required' => false],
                'voltage' => ['label' => 'Điện áp', 'type' => 'string', 'unit' => 'V', 'required' => false],
                'xmp_support' => ['label' => 'Hỗ trợ Intel XMP', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'expo_support' => ['label' => 'Hỗ trợ AMD EXPO', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'rgb' => ['label' => 'Đèn LED RGB', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'storage' => [
        'name' => 'Ổ cứng SSD/HDD',
        'groups' => [
            'Thông số lưu trữ' => [
                'storage_type' => ['label' => 'Loại ổ cứng (SSD/HDD)', 'type' => 'string', 'unit' => '', 'required' => true],
                'capacity_gb' => ['label' => 'Dung lượng', 'type' => 'integer', 'unit' => 'GB', 'required' => true],
                'form_factor' => ['label' => 'Kích thước (Form Factor)', 'type' => 'string', 'unit' => '', 'required' => false],
                'interface' => ['label' => 'Chuẩn giao tiếp', 'type' => 'string', 'unit' => '', 'required' => true],
                'read_speed_mbps' => ['label' => 'Tốc độ đọc tối đa', 'type' => 'integer', 'unit' => 'MB/s', 'required' => true],
                'write_speed_mbps' => ['label' => 'Tốc độ ghi tối đa', 'type' => 'integer', 'unit' => 'MB/s', 'required' => true],
                'tbw' => ['label' => 'Độ bền đọc ghi (TBW)', 'type' => 'integer', 'unit' => 'TB', 'required' => false],
                'heatsink' => ['label' => 'Tản nhiệt đi kèm', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'case' => [
        'name' => 'Vỏ Case',
        'groups' => [
            'Thông số Case' => [
                'case_type' => ['label' => 'Loại Case', 'type' => 'string', 'unit' => '', 'required' => true],
                'supported_form_factors' => ['label' => 'Mainboard hỗ trợ', 'type' => 'array', 'unit' => '', 'required' => true],
                'material' => ['label' => 'Chất liệu', 'type' => 'string', 'unit' => '', 'required' => false],
                'side_panel' => ['label' => 'Mặt hông (Kính/Thép)', 'type' => 'string', 'unit' => '', 'required' => false],
                'max_gpu_length_mm' => ['label' => 'Chiều dài VGA tối đa', 'type' => 'integer', 'unit' => 'mm', 'required' => false],
                'max_cpu_cooler_height_mm' => ['label' => 'Chiều cao tản CPU tối đa', 'type' => 'integer', 'unit' => 'mm', 'required' => false],
                'included_fans' => ['label' => 'Quạt đi kèm', 'type' => 'string', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'cooling' => [
        'name' => 'Tản nhiệt',
        'groups' => [
            'Thông số tản nhiệt' => [
                'cooling_type' => ['label' => 'Loại tản nhiệt (Khí/Nước AIO)', 'type' => 'string', 'unit' => '', 'required' => true],
                'supported_sockets' => ['label' => 'Socket hỗ trợ', 'type' => 'array', 'unit' => '', 'required' => true],
                'tdp_capacity_w' => ['label' => 'Công suất tản nhiệt (TDP)', 'type' => 'integer', 'unit' => 'W', 'required' => false],
                'radiator_size_mm' => ['label' => 'Kích thước Radiator', 'type' => 'integer', 'unit' => 'mm', 'required' => false],
                'fan_count' => ['label' => 'Số lượng quạt', 'type' => 'integer', 'unit' => 'quạt', 'required' => false],
                'rgb' => ['label' => 'Đèn RGB', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'psu' => [
        'name' => 'Nguồn (PSU)',
        'groups' => [
            'Thông số Nguồn' => [
                'wattage_w' => ['label' => 'Công suất thực', 'type' => 'integer', 'unit' => 'W', 'required' => true],
                'efficiency_rating' => ['label' => 'Chuẩn hiệu suất (80 Plus)', 'type' => 'string', 'unit' => '', 'required' => true],
                'form_factor' => ['label' => 'Kích thước (ATX/SFX)', 'type' => 'string', 'unit' => '', 'required' => false],
                'modular_type' => ['label' => 'Kiểu dây (Full Modular/Non-mod)', 'type' => 'string', 'unit' => '', 'required' => false],
                'pcie_connectors' => ['label' => 'Số đầu cấp nguồn PCIe', 'type' => 'string', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'keyboard' => [
        'name' => 'Bàn phím',
        'groups' => [
            'Thông số Bàn phím' => [
                'keyboard_type' => ['label' => 'Loại bàn phím (Cơ/Giả cơ)', 'type' => 'string', 'unit' => '', 'required' => true],
                'layout' => ['label' => 'Bố cục (Layout)', 'type' => 'string', 'unit' => '', 'required' => false],
                'switch_type' => ['label' => 'Loại Switch', 'type' => 'string', 'unit' => '', 'required' => false],
                'hot_swappable' => ['label' => 'Mạch Hot-swap', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'connection_type' => ['label' => 'Phương thức kết nối', 'type' => 'string', 'unit' => '', 'required' => true],
                'rgb' => ['label' => 'Đèn LED RGB', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'weight_g' => ['label' => 'Trọng lượng', 'type' => 'integer', 'unit' => 'g', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'mouse' => [
        'name' => 'Chuột Gaming',
        'groups' => [
            'Thông số Chuột' => [
                'sensor_model' => ['label' => 'Mắt đọc (Sensor)', 'type' => 'string', 'unit' => '', 'required' => false],
                'max_dpi' => ['label' => 'Độ phân giải tối đa (DPI)', 'type' => 'integer', 'unit' => 'DPI', 'required' => true],
                'polling_rate_hz' => ['label' => 'Tần số phản hồi (Polling Rate)', 'type' => 'integer', 'unit' => 'Hz', 'required' => false],
                'button_count' => ['label' => 'Số nút bấm', 'type' => 'integer', 'unit' => 'nút', 'required' => false],
                'connection_type' => ['label' => 'Kết nối (Dây/Wireless)', 'type' => 'string', 'unit' => '', 'required' => true],
                'weight_g' => ['label' => 'Trọng lượng siêu nhẹ', 'type' => 'integer', 'unit' => 'g', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'chair' => [
        'name' => 'Ghế Gaming / Văn phòng',
        'groups' => [
            'Thông số Ghế' => [
                'chair_type' => ['label' => 'Loại ghế', 'type' => 'string', 'unit' => '', 'required' => true],
                'frame_material' => ['label' => 'Khung ghế', 'type' => 'string', 'unit' => '', 'required' => false],
                'upholstery_material' => ['label' => 'Chất liệu bọc (Da/Lưới)', 'type' => 'string', 'unit' => '', 'required' => true],
                'max_load_kg' => ['label' => 'Tải trọng tối đa', 'type' => 'integer', 'unit' => 'kg', 'required' => true],
                'recline_degree' => ['label' => 'Góc ngả lưng tối đa', 'type' => 'integer', 'unit' => 'độ', 'required' => false],
                'armrest_type' => ['label' => 'Tay vịn (2D/3D/4D)', 'type' => 'string', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'headset' => [
        'name' => 'Tai nghe',
        'groups' => [
            'Thông số Tai nghe' => [
                'headset_type' => ['label' => 'Kiểu tai nghe (Over-ear/In-ear)', 'type' => 'string', 'unit' => '', 'required' => true],
                'driver_mm' => ['label' => 'Kích thước màng loa', 'type' => 'integer', 'unit' => 'mm', 'required' => false],
                'frequency_response_hz' => ['label' => 'Dải tần đáp ứng', 'type' => 'string', 'unit' => 'Hz', 'required' => false],
                'connection_type' => ['label' => 'Chuẩn kết nối', 'type' => 'string', 'unit' => '', 'required' => true],
                'surround' => ['label' => 'Âm thanh vòm (7.1)', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'microphone_type' => ['label' => 'Micro đi kèm', 'type' => 'string', 'unit' => '', 'required' => false],
                'weight_g' => ['label' => 'Trọng lượng', 'type' => 'integer', 'unit' => 'g', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'speaker' => [
        'name' => 'Loa',
        'groups' => [
            'Thông số Loa' => [
                'speaker_type' => ['label' => 'Loại loa', 'type' => 'string', 'unit' => '', 'required' => true],
                'channel_configuration' => ['label' => 'Cấu hình kênh (2.0/2.1/5.1)', 'type' => 'string', 'unit' => '', 'required' => false],
                'total_power_w' => ['label' => 'Tổng công suất (RMS)', 'type' => 'integer', 'unit' => 'W', 'required' => true],
                'connection_type' => ['label' => 'Cổng kết nối / Bluetooth', 'type' => 'string', 'unit' => '', 'required' => true],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'console' => [
        'name' => 'Máy chơi game Console',
        'groups' => [
            'Cấu hình Console' => [
                'console_model' => ['label' => 'Model máy', 'type' => 'string', 'unit' => '', 'required' => true],
                'storage_capacity_gb' => ['label' => 'Dung lượng ổ cứng', 'type' => 'integer', 'unit' => 'GB', 'required' => true],
                'maximum_resolution' => ['label' => 'Độ phân giải tối đa', 'type' => 'string', 'unit' => '', 'required' => false],
                'maximum_refresh_rate_hz' => ['label' => 'Tần số quét tối đa', 'type' => 'integer', 'unit' => 'Hz', 'required' => false],
                'optical_drive' => ['label' => 'Ổ đĩa đĩa Game (Disc Drive)', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'controller_included' => ['label' => 'Tay cầm đi kèm', 'type' => 'string', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'accessories' => [
        'name' => 'Phụ kiện máy tính',
        'groups' => [
            'Thông số Phụ kiện' => [
                'accessory_type' => ['label' => 'Loại phụ kiện', 'type' => 'string', 'unit' => '', 'required' => true],
                'compatible_devices' => ['label' => 'Thiết bị tương thích', 'type' => 'string', 'unit' => '', 'required' => false],
                'connection_type' => ['label' => 'Chuẩn kết nối', 'type' => 'string', 'unit' => '', 'required' => false],
                'cable_length_m' => ['label' => 'Chiều dài dây', 'type' => 'float', 'unit' => 'm', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'office-equipment' => [
        'name' => 'Thiết bị văn phòng',
        'groups' => [
            'Thông số Thiết bị văn phòng' => [
                'office_equipment_type' => ['label' => 'Loại thiết bị', 'type' => 'string', 'unit' => '', 'required' => true],
                'functions' => ['label' => 'Chức năng chính', 'type' => 'string', 'unit' => '', 'required' => false],
                'print_speed_ppm' => ['label' => 'Tốc độ in', 'type' => 'integer', 'unit' => 'trang/phút', 'required' => false],
                'connectivity' => ['label' => 'Kết nối (Wi-Fi/LAN/USB)', 'type' => 'string', 'unit' => '', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ],

    'power-bank' => [
        'name' => 'Sạc dự phòng',
        'groups' => [
            'Thông số Sạc dự phòng' => [
                'capacity_mah' => ['label' => 'Dung lượng Pin', 'type' => 'integer', 'unit' => 'mAh', 'required' => true],
                'maximum_total_output_w' => ['label' => 'Công suất sạc tối đa', 'type' => 'integer', 'unit' => 'W', 'required' => true],
                'output_ports' => ['label' => 'Cổng sạc ra', 'type' => 'string', 'unit' => '', 'required' => false],
                'power_delivery' => ['label' => 'Hỗ trợ Sạc nhanh PD', 'type' => 'boolean', 'unit' => '', 'required' => false],
                'weight_g' => ['label' => 'Trọng lượng', 'type' => 'integer', 'unit' => 'g', 'required' => false],
                'warranty_months' => ['label' => 'Bảo hành', 'type' => 'integer', 'unit' => 'tháng', 'required' => false],
            ]
        ]
    ]
];
