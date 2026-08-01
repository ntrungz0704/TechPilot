<?php

/**
 * Storefront facet contract.
 *
 * - Query parameters are stable, lowercase keys.
 * - Option values are allowlisted by this file; labels are never used as SQL.
 * - `keys` contains canonical and legacy spec keys in priority order.
 * - Price remains a common filter and is expressed with min_price/max_price.
 */
return [
    'category_aliases' => [
        'laptop-gaming' => [
            'category' => 'laptop',
            'defaults' => ['gpu' => 'dedicated'],
        ],
        'laptop-van-phong' => [
            'category' => 'laptop',
            'defaults' => ['gpu' => 'integrated'],
        ],
    ],
    'common' => [
        'price_ranges' => [
            'under-5m' => [
                'label' => 'Dưới 5 triệu',
                'min_price' => 0,
                'max_price' => 5000000,
            ],
            '5m-10m' => [
                'label' => 'Từ 5 - 10 triệu',
                'min_price' => 5000001,
                'max_price' => 10000000,
            ],
            '10m-15m' => [
                'label' => 'Từ 10 - 15 triệu',
                'min_price' => 10000001,
                'max_price' => 15000000,
            ],
            '15m-20m' => [
                'label' => 'Từ 15 - 20 triệu',
                'min_price' => 15000001,
                'max_price' => 20000000,
            ],
            '20m-30m' => [
                'label' => 'Từ 20 - 30 triệu',
                'min_price' => 20000001,
                'max_price' => 30000000,
            ],
            'over-30m' => [
                'label' => 'Trên 30 triệu',
                'min_price' => 30000001,
                'max_price' => 0,
            ],
        ],
    ],
    'categories' => [
        'laptop' => [
            'cpu_family' => [
                'label' => 'CPU',
                'type' => 'text',
                'operator' => 'contains',
                'keys' => ['cpu_model'],
                'options' => [
                    'core-i5' => ['label' => 'Intel Core i5', 'match' => ['core i5']],
                    'core-i7' => ['label' => 'Intel Core i7', 'match' => ['core i7']],
                    'core-i9' => ['label' => 'Intel Core i9', 'match' => ['core i9']],
                    'core-ultra' => ['label' => 'Intel Core Ultra', 'match' => ['core ultra']],
                    'ryzen-7' => ['label' => 'AMD Ryzen 7', 'match' => ['ryzen 7']],
                    'apple-silicon' => ['label' => 'Apple Silicon', 'match' => ['apple m', ' m1', ' m2', ' m3', ' m4']],
                ],
            ],
            'ram_min' => [
                'label' => 'RAM tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['ram_capacity_gb', 'ram_gb'],
                'options' => [
                    '8' => ['label' => 'Từ 8 GB', 'value' => 8],
                    '16' => ['label' => 'Từ 16 GB', 'value' => 16],
                    '32' => ['label' => 'Từ 32 GB', 'value' => 32],
                ],
            ],
            'storage_min' => [
                'label' => 'SSD tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['storage_capacity_gb', 'storage_gb'],
                'options' => [
                    '256' => ['label' => 'Từ 256 GB', 'value' => 256],
                    '512' => ['label' => 'Từ 512 GB', 'value' => 512],
                    '1000' => ['label' => 'Từ 1 TB', 'value' => 1000],
                ],
            ],
            'gpu' => [
                'label' => 'Card đồ họa',
                'type' => 'text',
                'operator' => 'contains',
                'keys' => ['gpu_model'],
                'options' => [
                    'dedicated' => ['label' => 'GPU rời NVIDIA GeForce RTX', 'match' => ['geforce rtx']],
                    'integrated' => ['label' => 'Đồ họa tích hợp', 'match' => ['intel uhd', 'intel iris', 'intel arc graphics', 'radeon 780m', 'radeon graphics']],
                    'rtx-4050' => ['label' => 'NVIDIA RTX 4050', 'match' => ['rtx 4050']],
                    'rtx-4060' => ['label' => 'NVIDIA RTX 4060', 'match' => ['rtx 4060']],
                    'rtx-4070' => ['label' => 'NVIDIA RTX 4070', 'match' => ['rtx 4070']],
                ],
            ],
            'screen_size' => [
                'label' => 'Kích thước màn hình',
                'type' => 'number',
                'operator' => 'range',
                'keys' => ['screen_size_inch', 'display_size_inch'],
                'options' => [
                    'up-to-14' => ['label' => 'Đến 14 inch', 'min' => 0, 'max' => 14],
                    '15-to-15-6' => ['label' => 'Từ 15 - 15.6 inch', 'min' => 15, 'max' => 15.6],
                    'from-16' => ['label' => 'Từ 16 inch', 'min' => 16, 'max' => null],
                ],
            ],
            'refresh_min' => [
                'label' => 'Tần số quét tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['refresh_rate_hz'],
                'options' => [
                    '60' => ['label' => 'Từ 60 Hz', 'value' => 60],
                    '120' => ['label' => 'Từ 120 Hz', 'value' => 120],
                    '144' => ['label' => 'Từ 144 Hz', 'value' => 144],
                    '240' => ['label' => 'Từ 240 Hz', 'value' => 240],
                ],
            ],
            'panel' => [
                'label' => 'Tấm nền',
                'type' => 'text',
                'operator' => 'equals',
                'keys' => ['panel_type'],
                'options' => [
                    'ips' => ['label' => 'IPS', 'match' => ['ips']],
                    'oled' => ['label' => 'OLED', 'match' => ['oled']],
                ],
            ],
        ],
        'pc' => [
            'pc_cpu_family' => [
                'label' => 'Nền tảng CPU',
                'type' => 'text',
                'operator' => 'contains',
                'keys' => ['cpu_model'],
                'options' => [
                    'core-i5' => ['label' => 'Intel Core i5', 'match' => ['core i5']],
                    'ryzen-5' => ['label' => 'AMD Ryzen 5', 'match' => ['ryzen 5']],
                ],
            ],
            'pc_ram_min' => [
                'label' => 'RAM tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['ram_capacity_gb', 'ram_gb'],
                'options' => [
                    '16' => ['label' => 'Từ 16 GB', 'value' => 16],
                    '32' => ['label' => 'Từ 32 GB', 'value' => 32],
                ],
            ],
            'pc_gpu' => [
                'label' => 'Card đồ họa',
                'type' => 'text',
                'operator' => 'contains',
                'keys' => ['gpu_model'],
                'options' => [
                    'rtx-4060' => ['label' => 'NVIDIA RTX 4060', 'match' => ['rtx 4060']],
                    'rtx-4070-super' => ['label' => 'NVIDIA RTX 4070 Super', 'match' => ['rtx 4070 super']],
                ],
            ],
        ],
        'monitor' => [
            'monitor_size' => [
                'label' => 'Kích thước',
                'type' => 'number',
                'operator' => 'equals',
                'keys' => ['screen_size_inch'],
                'options' => [
                    '27' => ['label' => '27 inch', 'match' => ['27']],
                    '32' => ['label' => '32 inch', 'match' => ['32']],
                ],
            ],
            'monitor_refresh_min' => [
                'label' => 'Tần số quét tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['refresh_rate_hz'],
                'options' => [
                    '144' => ['label' => 'Từ 144 Hz', 'value' => 144],
                    '180' => ['label' => 'Từ 180 Hz', 'value' => 180],
                ],
            ],
            'monitor_resolution' => [
                'label' => 'Độ phân giải',
                'type' => 'text',
                'operator' => 'contains',
                'keys' => ['resolution'],
                'options' => [
                    '2k' => ['label' => '2K / QHD', 'match' => ['2k', '2560x1440']],
                    '4k' => ['label' => '4K', 'match' => ['4k', '3840x2160']],
                ],
            ],
        ],
        'cpu' => [
            'cpu_socket' => [
                'label' => 'Socket',
                'type' => 'text',
                'operator' => 'equals',
                'keys' => ['socket'],
                'options' => [
                    'lga1700' => ['label' => 'LGA 1700', 'match' => ['lga1700']],
                    'am5' => ['label' => 'AM5', 'match' => ['am5']],
                ],
            ],
            'cpu_architecture' => [
                'label' => 'Kiến trúc',
                'type' => 'text',
                'operator' => 'contains',
                'keys' => ['architecture'],
                'options' => [
                    'raptor-lake-refresh' => ['label' => 'Raptor Lake Refresh', 'match' => ['raptor lake refresh']],
                    'zen-4' => ['label' => 'AMD Zen 4', 'match' => ['zen 4']],
                ],
            ],
            'cpu_cores_min' => [
                'label' => 'Số nhân tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['cores'],
                'options' => [
                    '6' => ['label' => 'Từ 6 nhân', 'value' => 6],
                    '10' => ['label' => 'Từ 10 nhân', 'value' => 10],
                ],
            ],
        ],
        'mainboard' => [
            'mainboard_socket' => [
                'label' => 'Socket',
                'type' => 'text',
                'operator' => 'equals',
                'keys' => ['socket'],
                'options' => [
                    'lga1700' => ['label' => 'LGA 1700', 'match' => ['lga1700']],
                    'am5' => ['label' => 'AM5', 'match' => ['am5']],
                ],
            ],
            'chipset' => [
                'label' => 'Chipset',
                'type' => 'text',
                'operator' => 'equals',
                'keys' => ['chipset'],
                'options' => [
                    'b650' => ['label' => 'AMD B650', 'match' => ['b650']],
                    'b760' => ['label' => 'Intel B760', 'match' => ['b760']],
                ],
            ],
            'mainboard_ram_type' => [
                'label' => 'Chuẩn RAM',
                'type' => 'text',
                'operator' => 'equals',
                'keys' => ['ram_type', 'memory_type'],
                'options' => [
                    'ddr4' => ['label' => 'DDR4', 'match' => ['ddr4']],
                    'ddr5' => ['label' => 'DDR5', 'match' => ['ddr5']],
                ],
            ],
        ],
        'vga' => [
            'vga_model' => [
                'label' => 'Dòng GPU',
                'type' => 'text',
                'operator' => 'contains',
                'keys' => ['gpu_model', 'gpu_chip'],
                'options' => [
                    'rtx-4060' => ['label' => 'GeForce RTX 4060', 'match' => ['rtx 4060']],
                    'rtx-4070-super' => ['label' => 'GeForce RTX 4070 Super', 'match' => ['rtx 4070 super']],
                ],
            ],
            'vram_min' => [
                'label' => 'VRAM tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['vram_gb'],
                'options' => [
                    '12' => ['label' => 'Từ 12 GB', 'value' => 12],
                    '16' => ['label' => 'Từ 16 GB', 'value' => 16],
                ],
            ],
        ],
        'ram' => [
            'ram_type' => [
                'label' => 'Chuẩn RAM',
                'type' => 'text',
                'operator' => 'equals',
                'keys' => ['memory_type'],
                'options' => [
                    'ddr4' => ['label' => 'DDR4', 'match' => ['ddr4']],
                    'ddr5' => ['label' => 'DDR5', 'match' => ['ddr5']],
                ],
            ],
            'ram_capacity_min' => [
                'label' => 'Dung lượng tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['capacity_gb', 'total_capacity_gb'],
                'options' => [
                    '16' => ['label' => 'Từ 16 GB', 'value' => 16],
                    '32' => ['label' => 'Từ 32 GB', 'value' => 32],
                ],
            ],
            'ram_speed_min' => [
                'label' => 'Bus tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['speed_mhz'],
                'options' => [
                    '3200' => ['label' => 'Từ 3200 MHz', 'value' => 3200],
                    '6000' => ['label' => 'Từ 6000 MHz', 'value' => 6000],
                ],
            ],
        ],
        'storage' => [
            'drive_type' => [
                'label' => 'Loại ổ cứng',
                'type' => 'text',
                'operator' => 'contains',
                'keys' => ['storage_type', 'drive_type'],
                'options' => [
                    'ssd-nvme' => ['label' => 'SSD NVMe', 'match' => ['ssd nvme']],
                    'hdd' => ['label' => 'HDD', 'match' => ['hdd']],
                ],
            ],
            'drive_capacity_min' => [
                'label' => 'Dung lượng tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['capacity_gb'],
                'options' => [
                    '1000' => ['label' => 'Từ 1 TB', 'value' => 1000],
                    '2000' => ['label' => 'Từ 2 TB', 'value' => 2000],
                ],
            ],
        ],
        'psu' => [
            'psu_wattage_min' => [
                'label' => 'Công suất tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['wattage_w', 'wattage'],
                'options' => [
                    '550' => ['label' => 'Từ 550W', 'value' => 550],
                    '650' => ['label' => 'Từ 650W', 'value' => 650],
                    '750' => ['label' => 'Từ 750W', 'value' => 750],
                    '850' => ['label' => 'Từ 850W', 'value' => 850],
                    '950' => ['label' => 'Từ 950W', 'value' => 950],
                ],
            ],
        ],
        'cooling' => [
            'cooler_type' => [
                'label' => 'Loại tản nhiệt',
                'type' => 'text',
                'operator' => 'equals',
                'keys' => ['cooler_type', 'cooling_type'],
                'options' => [
                    'air' => ['label' => 'Tản nhiệt khí', 'match' => ['air']],
                    'aio' => ['label' => 'Tản nhiệt nước AIO', 'match' => ['aio']],
                ],
            ],
        ],
        'power-bank' => [
            'power_bank_capacity_min' => [
                'label' => 'Dung lượng tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['capacity_mah'],
                'options' => [
                    '10000' => ['label' => 'Từ 10.000 mAh', 'value' => 10000],
                    '20000' => ['label' => 'Từ 20.000 mAh', 'value' => 20000],
                ],
            ],
            'power_output_min' => [
                'label' => 'Công suất sạc tối thiểu',
                'type' => 'number',
                'operator' => 'gte',
                'keys' => ['maximum_total_output_w', 'max_output_w'],
                'options' => [
                    '65' => ['label' => 'Từ 65W', 'value' => 65],
                    '100' => ['label' => 'Từ 100W', 'value' => 100],
                ],
            ],
        ],
    ],
];
