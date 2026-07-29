<?php
/**
 * Cấu hình AI Recommendation Engine (TechPilot AI 4.0)
 * Chứa budget mapping, category mapping, và ma trận tùy chọn động theo loại sản phẩm.
 */

return [
    // 1. MAPPING NGÂN SÁCH CHUẨN
    'budgets' => [
        'under_10m' => [
            'label' => 'Dưới 10 triệu',
            'min'   => 0,
            'max'   => 10000000
        ],
        '10_15m' => [
            'label' => '10 – 15 triệu',
            'min'   => 10000000,
            'max'   => 15000000
        ],
        '15_20m' => [
            'label' => '15 – 20 triệu',
            'min'   => 15000000,
            'max'   => 20000000
        ],
        '20_25m' => [
            'label' => '20 – 25 triệu',
            'min'   => 20000000,
            'max'   => 25000000
        ],
        '25_35m' => [
            'label' => '25 – 35 triệu',
            'min'   => 25000000,
            'max'   => 35000000
        ],
        'over_35m' => [
            'label' => 'Trên 35 triệu',
            'min'   => 35000000,
            'max'   => null // Không giới hạn
        ]
    ],

    // 2. MAPPING DANH MỤC LOGICAL -> SLUGS THỰC TẾ
    'categories' => [
        'laptop' => [
            'label' => 'Laptop',
            'slugs' => ['laptop']
        ],
        'pc' => [
            'label' => 'PC lắp sẵn',
            'slugs' => ['pc']
        ],
        'monitor' => [
            'label' => 'Màn hình',
            'slugs' => ['monitor']
        ],
        'gear' => [
            'label' => 'Gaming Gear / Phụ kiện',
            'slugs' => ['keyboard', 'mouse', 'headset', 'speaker', 'chair', 'console', 'accessories'],
            'subcategories' => [
                'keyboard'    => ['label' => 'Bàn phím', 'slug' => 'keyboard'],
                'mouse'       => ['label' => 'Chuột', 'slug' => 'mouse'],
                'headset'     => ['label' => 'Tai nghe', 'slug' => 'headset'],
                'speaker'     => ['label' => 'Loa', 'slug' => 'speaker'],
                'chair'       => ['label' => 'Ghế Gaming', 'slug' => 'chair'],
                'controller'  => ['label' => 'Tay cầm / Console', 'slug' => 'console'],
                'accessories' => ['label' => 'Phụ kiện khác', 'slug' => 'accessories']
            ]
        ]
    ],

    // 3. MỤC ĐÍCH SỬ DỤNG THEO LOẠI SẢN PHẨM
    'purposes' => [
        'laptop' => [
            ['code' => 'office', 'label' => '💻 Học tập / Văn phòng', 'desc' => 'Word, Excel, học online, lướt web mượt mà'],
            ['code' => 'coding', 'label' => '👨‍💻 Lập trình / Công nghệ', 'desc' => 'VSCode, Docker, Android Studio, IDEs'],
            ['code' => 'design', 'label' => '🎨 Đồ họa / Dựng phim 2D-3D', 'desc' => 'Photoshop, Premiere, AutoCAD, Blender'],
            ['code' => 'gaming', 'label' => '🎮 Chơi Game (eSports / AAA)', 'desc' => 'Valorant, LoL, FC Online, Black Myth Wukong'],
            ['code' => 'travel', 'label' => '✈️ Di chuyển / Công tác', 'desc' => 'Mỏng nhẹ, pin trâu, độ bền cao']
        ],
        'pc' => [
            ['code' => 'office', 'label' => '🏢 Văn phòng / Kế toán', 'desc' => 'Bền bỉ, hoạt động liên tục, tiết kiệm điện'],
            ['code' => 'coding', 'label' => '👨‍💻 Lập trình / Máy chủ local', 'desc' => 'Chạy đa nhiệm, ảo hóa, biên dịch nhanh'],
            ['code' => 'design_2d', 'label' => '🖌️ Thiết kế Đồ họa 2D', 'desc' => 'Photoshop, Illustrator, Lightroom, Canva'],
            ['code' => 'render_3d', 'label' => '🎬 Dựng phim 4K / Rendering 3D', 'desc' => 'Premiere, After Effects, Maya, Blender'],
            ['code' => 'gaming_esports', 'label' => '🎯 Gaming eSports (High FPS)', 'desc' => 'CS2, Valorant, LoL, Naraka 240Hz+'],
            ['code' => 'gaming_aaa', 'label' => '🔥 Gaming AAA 4K / Ray Tracing', 'desc' => 'Cyberpunk 2077, Wukong, Elden Ring, GTA V'],
            ['code' => 'livestream', 'label' => '🎙️ Livestream / Creator', 'desc' => 'Vừa game vừa stream OBS, vMix mượt mà'],
            ['code' => 'ai_ml', 'label' => '🤖 AI / Machine Learning', 'desc' => 'VRAM GPU lớn (RTX 4060/4070+), CUDA Core']
        ],
        'monitor' => [
            ['code' => 'office', 'label' => '💼 Văn phòng / Đọc tài liệu', 'desc' => 'Bảo vệ mắt, chống chói, góc nhìn rộng IPS'],
            ['code' => 'gaming', 'label' => '🎮 Chơi Game eSports / Fast IPS', 'desc' => 'Tần số quét cao 144Hz-240Hz, phản hồi 1ms'],
            ['code' => 'design', 'label' => '🎨 Đồ họa / Chuẩn màu sRGB/DCI-P3', 'desc' => 'Màu sắc chính xác Delta E < 2, IPS/OLED'],
            ['code' => 'video_edit', 'label' => '🎬 Dựng phim / Màn hình siêu rộng (Ultrawide)', 'desc' => 'Không gian làm việc rộng, độ phân giải 2K/4K'],
            ['code' => 'entertainment', 'label' => '🍿 Giải trí / Xem phim 4K', 'desc' => 'Độ phân giải 4K, HDR, loa tích hợp']
        ],
        'gear' => [
            'mouse' => [
                ['code' => 'fps', 'label' => '🎯 Chơi game FPS (Cảm biến siêu chuẩn)', 'desc' => 'Trọng lượng siêu nhẹ, DPI cao, switch bền'],
                ['code' => 'moba', 'label' => '⚔️ Chơi game MOBA / MMO', 'desc' => 'Nhiều nút bấm phụ lập trình macro'],
                ['code' => 'office', 'label' => '💼 Văn phòng / Công thái học', 'desc' => 'Chống đau cổ tay, click yên tĩnh (Silent)']
            ],
            'keyboard' => [
                ['code' => 'gaming', 'label' => '🎮 Chơi game phản hồi nhanh', 'desc' => 'Switch cơ học (Red/Blue/Brown/Speed), RGB'],
                ['code' => 'typing', 'label' => '✍️ Gõ văn bản / Lập trình', 'desc' => 'Cảm giác gõ sướng tay, Hotswap, Custom keycap'],
                ['code' => 'silent', 'label' => '🤫 Yên tĩnh văn phòng', 'desc' => 'Silent Switch không gây ồn đồng nghiệp']
            ],
            'headset' => [
                ['code' => 'esports', 'label' => '🎧 Gaming eSports 7.1', 'desc' => 'Định vị tiếng chân, mic lọc ồn đàm thoại clear'],
                ['code' => 'music', 'label' => '🎵 Nghe nhạc / Giải trí', 'desc' => 'Âm bass ấm, dải âm cân bằng, đeo thoải mái']
            ],
            'speaker' => [
                ['code' => 'desktop', 'label' => '🔊 Loa máy tính để bàn 2.0 / 2.1', 'desc' => 'Âm thanh sống động xem phim nghe nhạc'],
                ['code' => 'bluetooth', 'label' => '📻 Loa Bluetooth di động', 'desc' => 'Kết nối không dây tiện lợi']
            ],
            'chair' => [
                ['code' => 'gaming', 'label' => '🎮 Ghế Gaming bọc da / đệm êm', 'desc' => 'Thiết kế ngầu, ngả lưng 180 độ'],
                ['code' => 'ergo', 'label' => '🧘 Ghế công thái học lưới mát', 'desc' => 'Đỡ lưng cột sống, lưới thoáng khí ngồi lâu']
            ],
            'controller' => [
                ['code' => 'fifa', 'label' => '⚽ Chơi FC Online / FIFA / Game thể thao', 'desc' => 'Cần analog nhạy, phím nảy tốt'],
                ['code' => 'action', 'label' => '⚔️ Game hành động / Chặt chém', 'desc' => 'Rung phản hồi lực, trigger chính xác']
            ],
            'accessories' => [
                ['code' => 'setup', 'label' => '✨ Trang trí góc Setup', 'desc' => 'Lót chuột cực đại, giá treo tai nghe, dây cáp']
            ]
        ]
    ],

    // 4. TIÊU CHÍ ƯU TIÊN THEO LOẠI SẢN PHẨM (PC TUYỆT ĐỐI KHÔNG CÓ PIN LÂU / MỎNG NHẸ!)
    'priorities' => [
        'laptop' => [
            ['code' => 'performance', 'label' => '⚡ Hiệu năng tối đa trong tầm giá'],
            ['code' => 'battery', 'label' => '🔋 Thời lượng pin dài (Pin trâu)'],
            ['code' => 'lightweight', 'label' => '🪶 Mỏng nhẹ, dễ mang đi (Dưới 1.5kg)'],
            ['code' => 'display', 'label' => '🖥️ Màn hình đẹp, tần số quét cao / Chuẩn màu'],
            ['code' => 'keyboard', 'label' => '⌨️ Bàn phím nảy tốt, gõ sướng'],
            ['code' => 'upgradeability', 'label' => '🛠️ Dễ nâng cấp RAM & SSD sau này'],
            ['code' => 'cooling_noise', 'label' => '❄️ Máy mát & Ít ồn khi tải nặng']
        ],
        'pc' => [
            ['code' => 'cpu_performance', 'label' => '🧠 Hiệu năng CPU tối đa (Xử lý dữ liệu / Render / Biển dịch)'],
            ['code' => 'gpu_performance', 'label' => '🎨 Hiệu năng Card đồ họa GPU (Chơi Game / 3D / AI)'],
            ['code' => 'upgradeability', 'label' => '🛠️ Khả năng mở rộng & Nâng cấp lâu dài'],
            ['code' => 'cooling', 'label' => '❄️ Tản nhiệt mát mẻ, ổn định 24/7'],
            ['code' => 'low_noise', 'label' => '🤫 Hoạt động êm ái, ít tiếng ồn'],
            ['code' => 'compact_case', 'label' => '📦 Case nhỏ gọn (Mini-ITX / mATX)'],
            ['code' => 'rgb_design', 'label' => '✨ Thiết kế đẹp, LED RGB phong cách'],
            ['code' => 'power_efficiency', 'label' => '🌱 Nguồn linh kiện bền bỉ, tiết kiệm điện']
        ],
        'monitor' => [
            ['code' => 'refresh_rate', 'label' => '⚡ Tần số quét cao (144Hz - 240Hz+)'],
            ['code' => 'resolution', 'label' => '📐 Độ phân giải cao (2K QHD / 4K UHD)'],
            ['code' => 'color_accuracy', 'label' => '🎨 Màu sắc chuẩn xác (sRGB 100% / DCI-P3)'],
            ['code' => 'screen_size', 'label' => '📺 Kích thước màn hình lớn (27 inch+)'],
            ['code' => 'response_time', 'label' => '⏱️ Tốc độ phản hồi tức thì (0.5ms - 1ms)'],
            ['code' => 'eye_care', 'label' => '👁️ Bảo vệ mắt (Flicker-Free, Low Blue Light)'],
            ['code' => 'connectivity', 'label' => '🔌 Đa dạng cổng (Type-C 65W PD, DisplayPort)']
        ],
        'gear' => [
            'mouse' => [
                ['code' => 'wireless', 'label' => '📡 Kết nối không dây tốc độ cao (2.4GHz / Bluetooth)'],
                ['code' => 'sensor', 'label' => '🎯 Cảm biến mắt đọc chính xác từng Pixel'],
                ['code' => 'weight', 'label' => '🪶 Trọng lượng siêu nhẹ (< 60g)'],
                ['code' => 'battery', 'label' => '🔋 Thời lượng pin trâu (cho chuột không dây)']
            ],
            'keyboard' => [
                ['code' => 'switch', 'label' => '🔘 Switch nảy êm, gõ sướng tay'],
                ['code' => 'hotswap', 'label' => '🛠️ Mạch Hotswap dễ thay switch/keycap'],
                ['code' => 'wireless', 'label' => '📡 Kết nối 3 chế độ (Cáp / 2.4G / BT)'],
                ['code' => 'rgb', 'label' => '✨ Đèn nền LED RGB nhiều hiệu ứng']
            ],
            'headset' => [
                ['code' => 'sound_71', 'label' => '🎧 Âm thanh vòm 7.1 giả lập định vị'],
                ['code' => 'mic', 'label' => '🎙️ Micro lọc ồn đàm thoại trong trẻo'],
                ['code' => 'comfort', 'label' => '☁️ Đệm tai êm ái đeo lâu không đau'],
                ['code' => 'wireless', 'label' => '📡 Kết nối không dây độ trễ siêu thấp']
            ],
            'speaker' => [
                ['code' => 'power', 'label' => '🔊 Công suất lớn, Bass đập lực'],
                ['code' => 'bluetooth', 'label' => '📡 Kết nối Bluetooth tiện lợi']
            ],
            'chair' => [
                ['code' => 'ergo', 'label' => '🧘 Thiết kế công thái học bảo vệ lưng'],
                ['code' => 'mesh', 'label' => '🌬️ Chất liệu lưới thoáng khí không nóng'],
                ['code' => 'reclining', 'label' => '🛋️ Ngả lưng sâu nghỉ trưa thoải mái']
            ],
            'controller' => [
                ['code' => 'wireless', 'label' => '📡 Không dây độ trễ thấp'],
                ['code' => 'vibration', 'label' => '📳 Rung phản hồi lực sống động'],
                ['code' => 'battery', 'label' => '🔋 Pin sạc tích hợp dùng lâu']
            ],
            'accessories' => [
                ['code' => 'quality', 'label' => '⭐ Chất liệu cao cấp, độ bền dài lâu']
            ]
        ]
    ],

    // 5. BỘ LỌC NÂNG CAO THEO LOẠI SẢN PHẨM
    'filters' => [
        'laptop' => [
            'ram_min'   => ['label' => 'RAM tối thiểu', 'options' => [0 => 'Tất cả', 8 => 'Từ 8GB', 16 => 'Từ 16GB', 32 => 'Từ 32GB']],
            'ssd_min'   => ['label' => 'Lưu trữ SSD', 'options' => [0 => 'Tất cả', 256 => 'Từ 256GB', 512 => 'Từ 512GB', 1024 => 'Từ 1TB']],
            'screen_size' => ['label' => 'Kích thước màn', 'options' => ['all' => 'Tất cả', 'small' => '13 - 14 inch', 'large' => '15.6 - 16 inch+']],
            'has_gpu'   => ['label' => 'Card đồ họa rời', 'options' => ['all' => 'Tất cả', 'yes' => 'Bắt buộc có Card rời (RTX/GTX)', 'no' => 'Card onboard (Mỏng nhẹ)']]
        ],
        'pc' => [
            'ram_min'   => ['label' => 'RAM tối thiểu', 'options' => [0 => 'Tất cả', 16 => 'Từ 16GB', 32 => 'Từ 32GB', 64 => 'Từ 64GB']],
            'ssd_min'   => ['label' => 'Lưu trữ SSD', 'options' => [0 => 'Tất cả', 512 => 'Từ 512GB', 1024 => 'Từ 1TB']],
            'gpu_type'  => ['label' => 'Loại Card GPU', 'options' => ['all' => 'Tất cả', 'rtx40' => 'Series RTX 40 (4060/4070/4080/4090)', 'rtx30' => 'Series RTX 30/GTX', 'igpu' => 'VGA Onboard (Văn phòng)']],
            'has_wifi'  => ['label' => 'Wi-Fi & Bluetooth', 'options' => ['all' => 'Tất cả', 'yes' => 'Tích hợp sẵn Wi-Fi / Bluetooth']]
        ],
        'monitor' => [
            'screen_size' => ['label' => 'Kích thước', 'options' => ['all' => 'Tất cả', '24' => '24 inch', '27' => '27 inch', '32_plus' => '32 inch trở lên']],
            'resolution'  => ['label' => 'Độ phân giải', 'options' => ['all' => 'Tất cả', 'fhd' => 'FHD (1080p)', '2k' => '2K QHD (1440p)', '4k' => '4K UHD']],
            'refresh_rate' => ['label' => 'Tần số quét', 'options' => ['all' => 'Tất cả', '100hz' => 'Từ 100Hz', '144hz' => 'Từ 144Hz - 180Hz', '240hz' => 'Từ 240Hz+']]
        ],
        'gear' => [
            'connection'  => ['label' => 'Kết nối', 'options' => ['all' => 'Tất cả', 'wireless' => 'Không dây (Wireless/BT)', 'wired' => 'Có dây']],
            'brand_filter' => ['label' => 'Thương hiệu ưu tiên', 'options' => ['all' => 'Tất cả hãng', 'razer' => 'Razer', 'logitech' => 'Logitech', 'corsair' => 'Corsair', 'asus' => 'ASUS ROG']]
        ]
    ]
];
