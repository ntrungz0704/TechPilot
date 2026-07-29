<?php
/**
 * Cấu hình Persona-Aware Product Comparison Engine (TechPilot Compare 4.0)
 * Chứa ma trận danh mục, persona, priority, tiêu chí loại trừ metadata và ma trận trọng số chấm điểm.
 */

return [
    // 1. NHÓM DANH MỤC SO SÁNH -> SLUGS THỰC TẾ TRONG DATABASE
    'categories' => [
        'laptop' => [
            'label' => 'Laptop',
            'slugs' => ['laptop'],
            'icon'  => 'fa-laptop'
        ],
        'prebuilt_pc' => [
            'label' => 'PC Lắp Sẵn',
            'slugs' => ['pc'],
            'icon'  => 'fa-desktop'
        ],
        'monitor' => [
            'label' => 'Màn hình',
            'slugs' => ['monitor'],
            'icon'  => 'fa-tv'
        ],
        'cpu' => [
            'label' => 'CPU',
            'slugs' => ['cpu'],
            'icon'  => 'fa-microchip'
        ],
        'mainboard' => [
            'label' => 'Bo mạch chủ',
            'slugs' => ['mainboard'],
            'icon'  => 'fa-chess-board'
        ],
        'vga' => [
            'label' => 'Card màn hình (VGA)',
            'slugs' => ['vga'],
            'icon'  => 'fa-images'
        ],
        'ram' => [
            'label' => 'RAM',
            'slugs' => ['ram'],
            'icon'  => 'fa-memory'
        ],
        'storage' => [
            'label' => 'Ổ cứng (SSD/HDD)',
            'slugs' => ['storage'],
            'icon'  => 'fa-hard-drive'
        ],
        'psu' => [
            'label' => 'Nguồn máy tính (PSU)',
            'slugs' => ['psu'],
            'icon'  => 'fa-bolt'
        ],
        'case' => [
            'label' => 'Vỏ Case',
            'slugs' => ['case'],
            'icon'  => 'fa-box'
        ],
        'cooling' => [
            'label' => 'Tản nhiệt',
            'slugs' => ['cooling'],
            'icon'  => 'fa-snowflake'
        ],
        'keyboard' => [
            'label' => 'Bàn phím',
            'slugs' => ['keyboard'],
            'icon'  => 'fa-keyboard'
        ],
        'mouse' => [
            'label' => 'Chuột',
            'slugs' => ['mouse'],
            'icon'  => 'fa-computer-mouse'
        ],
        'headset' => [
            'label' => 'Tai nghe',
            'slugs' => ['headset'],
            'icon'  => 'fa-headphones'
        ],
        'chair' => [
            'label' => 'Ghế Gaming',
            'slugs' => ['chair'],
            'icon'  => 'fa-chair'
        ]
    ],

    // 2. PERSONA THEO LOẠI SẢN PHẨM
    'personas' => [
        'laptop' => [
            ['code' => 'student', 'label' => '🎓 Học sinh / Sinh viên', 'desc' => 'Tối ưu mức giá, pin trâu, mỏng nhẹ di chuyển'],
            ['code' => 'office', 'label' => '💼 Nhân viên văn phòng', 'desc' => 'Màn hình sắc nét, bàn phím gõ êm, pin dùng cả ngày'],
            ['code' => 'developer', 'label' => '👨‍💻 Lập trình viên / Coder', 'desc' => 'CPU đa nhân, RAM >= 16GB, SSD nhanh, màn hình góc nhìn rộng'],
            ['code' => 'gamer', 'label' => '🎮 Gamer (eSports / AAA)', 'desc' => 'VGA rời RTX, CPU xung cao, tản nhiệt mát, màn hình 144Hz+'],
            ['code' => 'creator', 'label' => '🎨 Đồ họa / Dựng phim 2D-3D', 'desc' => 'Màn hình chuẩn màu sRGB/DCI-P3, RAM/VGA mạnh, SSD lớn'],
            ['code' => 'business_travel', 'label' => '✈️ Doanh nhân / Di chuyển nhiều', 'desc' => 'Trọng lượng mỏng nhẹ (< 1.4kg), vỏ kim loại sang trọng, bảo mật']
        ],
        'prebuilt_pc' => [
            ['code' => 'office', 'label' => '🏢 Văn phòng / Kế toán', 'desc' => 'Giá tốt, tiết kiệm điện, hoạt động ổn định 24/7'],
            ['code' => 'developer', 'label' => '👨‍💻 Lập trình / Máy chủ local', 'desc' => 'CPU Intel Core i7/Ryzen 7, RAM 32GB+, SSD 1TB, nâng cấp tốt'],
            ['code' => 'esports', 'label' => '🎯 Gaming eSports (FPS cao)', 'desc' => 'CPU xung đơn nhân cao, RTX 4060/4070, RAM 16GB 3600MHz+'],
            ['code' => 'aaa_gaming', 'label' => '🔥 Gaming AAA 4K / Ray Tracing', 'desc' => 'RTX 4070 Super/4080/4090, CPU khủng, Nguồn PSU 750W+'],
            ['code' => 'creator_3d', 'label' => '🎬 Dựng phim 4K / Render 3D', 'desc' => 'CPU nhiều nhân thực, VRAM lớn 12GB+, RAM 32-64GB'],
            ['code' => 'streamer', 'label' => '🎙️ Livestream / Creator', 'desc' => 'Vừa game vừa OBS, tản nhiệt nước, case RGB bắt mắt'],
            ['code' => 'ai_ml', 'label' => '🤖 AI / Deep Learning', 'desc' => 'VRAM GPU lớn (RTX 4060 Ti 16GB / 4080 / 4090), CUDA Core']
        ],
        'monitor' => [
            ['code' => 'office', 'label' => '💼 Văn phòng / Đọc văn bản', 'desc' => 'Bảo vệ mắt, góc nhìn rộng IPS, phẳng, tiết kiệm điện'],
            ['code' => 'esports', 'label' => '🎮 Gamer eSports (Tốc độ)', 'desc' => 'Tần số quét 180Hz-240Hz, phản hồi 0.5-1ms, Fast IPS'],
            ['code' => 'aaa_gaming', 'label' => '🍿 Gamer AAA / Giải trí 4K', 'desc' => 'Độ phân giải 2K/4K, HDR, tấm nền OLED/IPS cao cấp'],
            ['code' => 'creator', 'label' => '🎨 Thiết kế Đồ họa / Nhiếp ảnh', 'desc' => 'Màu sắc chính xác (Delta E < 2, 100% sRGB/DCI-P3)'],
            ['code' => 'video_editor', 'label' => '🎬 Dựng phim / Màn hình Ultrawide', 'desc' => 'Tỷ lệ 21:9 / 32:9, độ phân giải 2K/4K siêu rộng']
        ],
        'vga' => [
            ['code' => 'gaming_1080p', 'label' => '🎯 Gaming Full HD 1080p', 'desc' => 'Cân mượt mọi game eSports và AAA ở thiết lập High'],
            ['code' => 'gaming_1440p', 'label' => '🔥 Gaming 2K QHD 144Hz', 'desc' => 'VRAM 12GB+, DLSS 3, Ray Tracing ấn tượng'],
            ['code' => 'render_ai', 'label' => '🎬 Render 3D & AI Training', 'desc' => 'VRAM 16GB-24GB, CUDA Core dồi dào, tản nhiệt 3 quạt']
        ],
        'cpu' => [
            ['code' => 'gaming', 'label' => '🎮 Chuyên Game (Xung đơn nhân)', 'desc' => 'Tối ưu FPS cho game eSports và AAA'],
            ['code' => 'workstation', 'label' => '🎬 Đa nhiệm nặng / Render / Code', 'desc' => 'Số nhân luồng lớn, bộ nhớ đệm Cache khủng']
        ],
        'psu' => [
            ['code' => 'mid_range', 'label' => '⚡ Cấu hình tầm trung (550W - 650W)', 'desc' => 'Nguồn 80 Plus Bronze/Gold cho cấu hình RTX 4060'],
            ['code' => 'high_end', 'label' => '🔥 Cấu hình cao cấp (750W - 1000W+)', 'desc' => 'Chuẩn ATX 3.0, cáp 12VHPWR cho RTX 4070 Ti/4080/4090']
        ]
    ],

    // 3. TIÊU CHÍ ƯU TIÊN THEO SẢN PHẨM (PC TUYỆT ĐỐI KHÔNG CÓ PIN LÂU / MỎNG NHẸ!)
    'priorities' => [
        'laptop' => [
            ['code' => 'performance', 'label' => '⚡ Hiệu năng phần cứng mạnh mẽ'],
            ['code' => 'battery', 'label' => '🔋 Thời lượng pin trâu'],
            ['code' => 'weight', 'label' => '🪶 Trọng lượng mỏng nhẹ (< 1.5kg)'],
            ['code' => 'display', 'label' => '🖥️ Màn hình nét, chuẩn màu / Tần số quét cao'],
            ['code' => 'keyboard', 'label' => '⌨️ Bàn phím nảy tốt, gõ sướng'],
            ['code' => 'upgradeability', 'label' => '🛠️ Dễ dàng nâng cấp RAM & SSD'],
            ['code' => 'cooling_noise', 'label' => '❄️ Tản nhiệt mát & Ít tiếng ồn']
        ],
        'prebuilt_pc' => [
            ['code' => 'cpu_performance', 'label' => '🧠 CPU cực mạnh (Xử lý dữ liệu / Biên dịch / Render)'],
            ['code' => 'gpu_performance', 'label' => '🎨 Card GPU cực khỏe (Gaming AAA / 3D / AI)'],
            ['code' => 'upgradeability', 'label' => '🛠️ Khả năng mở rộng & Nâng cấp lâu dài'],
            ['code' => 'cooling', 'label' => '❄️ Tản nhiệt mát mẻ, ổn định 24/7'],
            ['code' => 'noise', 'label' => '🤫 Hoạt động êm ái, yên tĩnh'],
            ['code' => 'compactness', 'label' => '📦 Case nhỏ gọn, tiết kiệm diện tích'],
            ['code' => 'power_efficiency', 'label' => '🌱 Nguồn linh kiện bền bỉ, tiết kiệm điện']
        ],
        'monitor' => [
            ['code' => 'refresh_rate', 'label' => '⚡ Tần số quét cao (144Hz - 240Hz+)'],
            ['code' => 'resolution', 'label' => '📐 Độ phân giải sắc nét (2K / 4K)'],
            ['code' => 'color_accuracy', 'label' => '🎨 Màu sắc chính xác (sRGB 100% / DCI-P3)'],
            ['code' => 'response_time', 'label' => '⏱️ Tốc độ phản hồi cực nhanh (0.5ms - 1ms)'],
            ['code' => 'screen_size', 'label' => '📺 Kích thước màn hình lớn (27 inch+)'],
            ['code' => 'connectivity', 'label' => '🔌 Đa dạng cổng cắm (Type-C PD, DisplayPort)']
        ]
    ],

    // 4. DANH SÁCH KEY METADATA NỘI BỘ BẮT BUỘC BỎ QUA KHÔNG HIỂN THỊ HÀNG TẠI TABLE
    'excluded_spec_keys' => [
        'schema_version',
        'raw_specs',
        'attributes',
        'compatibility',
        'use_cases',
        'vfm_score',
        'source_url',
        'source_name'
    ],

    // 5. MAPPING HIỂN THỊ THÔNG SỐ TIẾNG VIỆT THEO DANH MỤC
    'spec_display_schema' => [
        'laptop' => [
            'cpu' => ['label' => 'Bộ vi xử lý (CPU)', 'type' => 'text'],
            'ram' => ['label' => 'Dung lượng RAM', 'type' => 'text', 'highlight_highest' => true],
            'ram_max' => ['label' => 'Khả năng nâng RAM', 'type' => 'text'],
            'ssd' => ['label' => 'Dung lượng SSD', 'type' => 'text', 'highlight_highest' => true],
            'vga' => ['label' => 'Card màn hình (VGA)', 'type' => 'text'],
            'screen_size' => ['label' => 'Kích thước màn hình', 'type' => 'text'],
            'resolution' => ['label' => 'Độ phân giải', 'type' => 'text'],
            'refresh_rate' => ['label' => 'Tần số quét', 'type' => 'text', 'unit' => 'Hz', 'highlight_highest' => true],
            'panel' => ['label' => 'Tấm nền màn hình', 'type' => 'text'],
            'color_gamut' => ['label' => 'Độ phủ màu', 'type' => 'text'],
            'weight' => ['label' => 'Trọng lượng', 'type' => 'text', 'highlight_lowest' => true],
            'battery' => ['label' => 'Dung lượng Pin', 'type' => 'text']
        ],
        'prebuilt_pc' => [
            'cpu' => ['label' => 'Bộ vi xử lý (CPU)', 'type' => 'text'],
            'mainboard' => ['label' => 'Bo mạch chủ (Mainboard)', 'type' => 'text'],
            'vga' => ['label' => 'Card màn hình (VGA)', 'type' => 'text'],
            'ram' => ['label' => 'Dung lượng RAM', 'type' => 'text', 'highlight_highest' => true],
            'ssd' => ['label' => 'Ổ cứng SSD', 'type' => 'text', 'highlight_highest' => true],
            'psu' => ['label' => 'Nguồn máy tính (PSU)', 'type' => 'text'],
            'case' => ['label' => 'Vỏ Case', 'type' => 'text'],
            'cooling' => ['label' => 'Tản nhiệt CPU', 'type' => 'text'],
            'upgradeability' => ['label' => 'Khả năng nâng cấp', 'type' => 'text']
        ],
        'monitor' => [
            'screen_size' => ['label' => 'Kích thước màn hình', 'type' => 'text'],
            'resolution' => ['label' => 'Độ phân giải', 'type' => 'text'],
            'refresh_rate' => ['label' => 'Tần số quét', 'type' => 'text', 'unit' => 'Hz', 'highlight_highest' => true],
            'panel' => ['label' => 'Tấm nền', 'type' => 'text'],
            'response_time' => ['label' => 'Thời gian phản hồi', 'type' => 'text', 'unit' => 'ms', 'highlight_lowest' => true],
            'brightness' => ['label' => 'Độ sáng', 'type' => 'text', 'unit' => 'nits'],
            'color_gamut' => ['label' => 'Độ phủ màu', 'type' => 'text'],
            'hdr' => ['label' => 'Hỗ trợ HDR', 'type' => 'text'],
            'vesa' => ['label' => 'Chuẩn ngàm VESA', 'type' => 'text']
        ]
    ]
];
