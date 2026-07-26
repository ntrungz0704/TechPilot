<?php
/**
 * Generate 4 Angle Category SVGs Script — TechPilot
 * Creates 4 distinct, high-quality, category-matched SVG vector artwork files
 * for each of TechPilot's 20 categories (80 vector SVG files total).
 */

$outputDir = __DIR__ . '/../public/assets/images/products';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$categoryAngleConfigs = [
    'laptop' => [
        'title' => 'Laptop',
        'color' => '#3B82F6',
        'angles' => [
            'Góc 3/4 Chính diện',
            'Góc nghiêng Cạnh bên',
            'Bàn phím & Touchpad',
            'Mặt lưng & Cổng kết nối'
        ],
        'drawings' => [
            '<path d="M60 220 L340 220 L320 90 L80 90 Z" fill="#1E293B" stroke="#3B82F6" stroke-width="4"/><rect x="90" y="100" width="220" height="110" rx="4" fill="#0F172A"/><path d="M40 230 L360 230 L380 260 L20 260 Z" fill="#334155" stroke="#3B82F6" stroke-width="4"/>',
            '<path d="M100 240 L300 240 L280 80 L260 80 Z" fill="#1E293B" stroke="#3B82F6" stroke-width="4"/><path d="M80 250 L320 250 L310 260 L90 260 Z" fill="#334155"/>',
            '<rect x="60" y="80" width="280" height="180" rx="12" fill="#1E293B" stroke="#3B82F6" stroke-width="4"/><rect x="80" y="100" width="240" height="100" rx="6" fill="#0F172A"/><rect x="140" y="210" width="120" height="40" rx="4" fill="#334155"/>',
            '<rect x="60" y="70" width="280" height="180" rx="12" fill="#1E293B" stroke="#3B82F6" stroke-width="4"/><circle cx="200" cy="160" r="30" fill="none" stroke="#3B82F6" stroke-width="6"/>'
        ]
    ],
    'pc' => [
        'title' => 'PC Gaming & Build PC',
        'color' => '#8B5CF6',
        'angles' => [
            'Mặt bên Kính cường lực RGB',
            'Mặt trước Lưới Airflow',
            'Linh kiện bên trong',
            'Mặt sau Cổng I/O'
        ],
        'drawings' => [
            '<rect x="100" y="50" width="200" height="240" rx="10" fill="#1E293B" stroke="#8B5CF6" stroke-width="4"/><rect x="115" y="65" width="170" height="210" rx="6" fill="#0F172A" stroke="#8B5CF6" stroke-dasharray="8 4"/><circle cx="160" cy="110" r="25" fill="none" stroke="#EC4899" stroke-width="4"/><circle cx="230" cy="110" r="25" fill="none" stroke="#3B82F6" stroke-width="4"/>',
            '<rect x="120" y="50" width="160" height="240" rx="8" fill="#1E293B" stroke="#8B5CF6" stroke-width="4"/><line x1="140" y1="70" x2="260" y2="70" stroke="#8B5CF6" stroke-width="4"/><line x1="140" y1="90" x2="260" y2="90" stroke="#8B5CF6" stroke-width="4"/><line x1="140" y1="110" x2="260" y2="110" stroke="#8B5CF6" stroke-width="4"/>',
            '<rect x="80" y="60" width="240" height="200" rx="8" fill="#0F172A" stroke="#8B5CF6" stroke-width="4"/><rect x="110" y="90" width="80" height="80" fill="#1E293B" stroke="#EC4899" stroke-width="3"/><rect x="210" y="100" width="90" height="40" fill="#334155" stroke="#3B82F6" stroke-width="3"/>',
            '<rect x="130" y="50" width="140" height="240" rx="8" fill="#1E293B" stroke="#8B5CF6" stroke-width="4"/><rect x="150" y="70" width="40" height="80" fill="#0F172A"/><rect x="150" y="170" width="100" height="40" fill="#0F172A"/>'
        ]
    ],
    'man-hinh' => [
        'title' => 'Màn hình Monitor',
        'color' => '#06B6D4',
        'angles' => [
            'Chính diện Viền mỏng',
            'Góc nghiêng Siêu mỏng',
            'Chân đế & Mặt sau',
            'Cận cảnh Tấm nền'
        ],
        'drawings' => [
            '<rect x="50" y="60" width="300" height="180" rx="6" fill="#0F172A" stroke="#06B6D4" stroke-width="4"/><polygon points="180,240 220,240 230,280 170,280" fill="#334155"/><rect x="140" y="280" width="120" height="10" rx="3" fill="#06B6D4"/>',
            '<path d="M190 60 L210 60 L200 240 L180 240 Z" fill="#1E293B" stroke="#06B6D4" stroke-width="4"/><polygon points="190,240 200,240 220,280 170,280" fill="#334155"/>',
            '<rect x="50" y="60" width="300" height="180" rx="6" fill="#1E293B" stroke="#06B6D4" stroke-width="4"/><circle cx="200" cy="150" r="30" fill="#0F172A" stroke="#06B6D4" stroke-width="3"/><polygon points="180,240 220,240 230,280 170,280" fill="#334155"/>',
            '<rect x="40" y="40" width="320" height="220" rx="8" fill="#0F172A" stroke="#06B6D4" stroke-width="6"/><circle cx="200" cy="150" r="50" fill="none" stroke="#10B981" stroke-width="4" stroke-dasharray="10 5"/>'
        ]
    ],
    'mainboard' => [
        'title' => 'Mainboard',
        'color' => '#10B981',
        'angles' => [
            'Tổng thể Chuẩn ATX',
            'Tản nhiệt VRM & Socket',
            'Giáp M.2 & Slot PCIe',
            'Cụm Cổng I/O Sau'
        ],
        'drawings' => [
            '<rect x="70" y="40" width="260" height="250" rx="8" fill="#064E3B" stroke="#10B981" stroke-width="4"/><rect x="120" y="70" width="70" height="70" fill="#1E293B" stroke="#10B981" stroke-width="3"/><rect x="220" y="70" width="20" height="100" fill="#0F172A"/><rect x="120" y="180" width="160" height="15" fill="#334155"/><rect x="120" y="210" width="160" height="15" fill="#334155"/>',
            '<rect x="80" y="60" width="240" height="180" rx="8" fill="#0F172A" stroke="#10B981" stroke-width="4"/><rect x="150" y="100" width="100" height="100" fill="#1E293B" stroke="#F59E0B" stroke-width="4"/><path d="M100 80 L200 80 L180 120 L100 120 Z" fill="#064E3B" stroke="#10B981" stroke-width="2"/>',
            '<rect x="60" y="80" width="280" height="160" rx="8" fill="#064E3B" stroke="#10B981" stroke-width="4"/><rect x="90" y="110" width="220" height="25" fill="#1E293B" stroke="#10B981" stroke-width="2"/><rect x="90" y="160" width="220" height="25" fill="#1E293B" stroke="#10B981" stroke-width="2"/>',
            '<rect x="90" y="50" width="220" height="220" rx="8" fill="#0F172A" stroke="#10B981" stroke-width="4"/><rect x="110" y="70" width="50" height="180" fill="#1E293B" stroke="#10B981" stroke-width="2"/>'
        ]
    ],
    'cpu' => [
        'title' => 'Bộ vi xử lý CPU',
        'color' => '#F59E0B',
        'angles' => [
            'Mặt trên Nắp IHS',
            'Mặt dưới Chân cắm LGA/AM5',
            'Vỏ hộp Bao bì chính hãng',
            'Cấu trúc Nhân Die CPU'
        ],
        'drawings' => [
            '<rect x="90" y="50" width="220" height="220" rx="12" fill="#D97706" stroke="#F59E0B" stroke-width="4"/><rect x="120" y="80" width="160" height="160" rx="8" fill="#92400E" stroke="#FBBF24" stroke-width="3"/><text x="200" y="170" font-family="Arial" font-size="22" font-weight="bold" fill="#FEF3C7" text-anchor="middle">CPU IHS</text>',
            '<rect x="90" y="50" width="220" height="220" rx="12" fill="#78350F" stroke="#F59E0B" stroke-width="4"/><rect x="110" y="70" width="180" height="180" fill="none" stroke="#FBBF24" stroke-width="2" stroke-dasharray="4 4"/><circle cx="200" cy="160" r="30" fill="#B45309"/>',
            '<rect x="100" y="60" width="200" height="210" rx="10" fill="#1E293B" stroke="#F59E0B" stroke-width="5"/><rect x="130" y="90" width="140" height="100" rx="6" fill="#D97706"/><text x="200" y="230" font-family="Arial" font-size="16" font-weight="bold" fill="#FBBF24" text-anchor="middle">PROCESSOR</text>',
            '<rect x="70" y="50" width="260" height="220" rx="8" fill="#0F172A" stroke="#F59E0B" stroke-width="4"/><rect x="110" y="80" width="80" height="70" fill="#D97706"/><rect x="210" y="80" width="80" height="70" fill="#D97706"/><rect x="110" y="170" width="180" height="70" fill="#B45309"/>'
        ]
    ],
    'vga' => [
        'title' => 'Card màn hình VGA',
        'color' => '#EF4444',
        'angles' => [
            'Chính diện 3 Quạt tản nhiệt',
            'Mặt lưng Backplate Kim loại',
            'Cạnh bên Đèn LED RGB',
            'Chân cắm PCIe & Cổng xuất hình'
        ],
        'drawings' => [
            '<rect x="40" y="90" width="320" height="140" rx="12" fill="#1E293B" stroke="#EF4444" stroke-width="4"/><circle cx="100" cy="160" r="40" fill="#0F172A" stroke="#EF4444" stroke-width="3"/><circle cx="200" cy="160" r="40" fill="#0F172A" stroke="#EF4444" stroke-width="3"/><circle cx="300" cy="160" r="40" fill="#0F172A" stroke="#EF4444" stroke-width="3"/>',
            '<rect x="40" y="90" width="320" height="140" rx="12" fill="#374151" stroke="#EF4444" stroke-width="4"/><path d="M60 110 L340 110 M60 210 L340 210" stroke="#9CA3AF" stroke-width="2" stroke-dasharray="10 5"/>',
            '<rect x="50" y="110" width="300" height="80" rx="8" fill="#111827" stroke="#EF4444" stroke-width="4"/><text x="200" y="155" font-family="Arial" font-size="20" font-weight="bold" fill="#F43F5E" text-anchor="middle">GEFORCE RTX</text>',
            '<rect x="40" y="80" width="320" height="160" rx="8" fill="#1F2937" stroke="#EF4444" stroke-width="4"/><rect x="80" y="220" width="160" height="15" fill="#F59E0B"/><rect x="45" y="100" width="25" height="120" fill="#9CA3AF"/>'
        ]
    ],
    'ram' => [
        'title' => 'Bộ nhớ RAM',
        'color' => '#EC4899',
        'angles' => [
            'Cặp thanh RAM Bộc tản RGB',
            'Mặt bên Tản nhiệt nhôm',
            'Chân cắm Chân kim loại mạ vàng',
            'Dải LED RGB Phía trên'
        ],
        'drawings' => [
            '<rect x="60" y="80" width="280" height="70" rx="6" fill="#1E293B" stroke="#EC4899" stroke-width="4"/><rect x="60" y="170" width="280" height="70" rx="6" fill="#1E293B" stroke="#EC4899" stroke-width="4"/><rect x="70" y="85" width="260" height="15" fill="#F472B6"/><rect x="70" y="175" width="260" height="15" fill="#F472B6"/>',
            '<rect x="50" y="110" width="300" height="100" rx="8" fill="#374151" stroke="#EC4899" stroke-width="4"/><path d="M70 125 L330 125 L310 165 L90 165 Z" fill="#1F2937"/>',
            '<rect x="50" y="90" width="300" height="140" rx="8" fill="#1E293B" stroke="#EC4899" stroke-width="4"/><rect x="70" y="210" width="260" height="15" fill="#F59E0B" stroke-dasharray="6 2"/>',
            '<rect x="40" y="120" width="320" height="80" rx="10" fill="#0F172A" stroke="#EC4899" stroke-width="4"/><rect x="60" y="135" width="280" height="50" rx="6" fill="#F472B6" stroke="#3B82F6" stroke-width="2"/>'
        ]
    ],
    'storage' => [
        'title' => 'Ổ cứng SSD & HDD',
        'color' => '#14B8A6',
        'angles' => [
            'M.2 NVMe SSD Mặt trên',
            'Tản nhiệt Nhôm M.2',
            'SATA 2.5 Inch Chuẩn',
            'Chip Flash Controller'
        ],
        'drawings' => [
            '<rect x="60" y="120" width="280" height="80" rx="6" fill="#064E3B" stroke="#14B8A6" stroke-width="4"/><rect x="90" y="135" width="60" height="50" fill="#1E293B"/><rect x="170" y="135" width="60" height="50" fill="#1E293B"/><rect x="250" y="135" width="60" height="50" fill="#1E293B"/><rect x="330" y="130" width="10" height="60" fill="#F59E0B"/>',
            '<rect x="50" y="110" width="300" height="100" rx="8" fill="#1F2937" stroke="#14B8A6" stroke-width="4"/><line x1="70" y1="130" x2="330" y2="130" stroke="#14B8A6" stroke-width="6"/><line x1="70" y1="160" x2="330" y2="160" stroke="#14B8A6" stroke-width="6"/>',
            '<rect x="80" y="60" width="240" height="200" rx="10" fill="#374151" stroke="#14B8A6" stroke-width="4"/><rect x="110" y="90" width="180" height="120" rx="6" fill="#111827"/><text x="200" y="160" font-family="Arial" font-size="20" font-weight="bold" fill="#2DD4BF" text-anchor="middle">SATA SSD 2.5"</text>',
            '<rect x="70" y="80" width="260" height="160" rx="8" fill="#064E3B" stroke="#14B8A6" stroke-width="4"/><rect x="110" y="110" width="80" height="80" fill="#1E293B" stroke="#14B8A6" stroke-width="3"/><rect x="210" y="110" width="90" height="50" fill="#1E293B"/>'
        ]
    ],
    'case' => [
        'title' => 'Vỏ máy tính Case',
        'color' => '#6366F1',
        'angles' => [
            'Tháp ATX Kính cường lực',
            'Mặt trước Lưới khoang gió',
            'Khoang Giấu dây Cạnh sau',
            'Cụm Cổng I/O Mặt trên'
        ],
        'drawings' => [
            '<rect x="110" y="40" width="180" height="260" rx="10" fill="#1E293B" stroke="#6366F1" stroke-width="4"/><rect x="125" y="55" width="150" height="230" rx="6" fill="#0F172A" stroke="#6366F1" stroke-dasharray="6 3"/>',
            '<rect x="120" y="40" width="160" height="260" rx="8" fill="#111827" stroke="#6366F1" stroke-width="4"/><line x1="140" y1="60" x2="260" y2="60" stroke="#6366F1" stroke-width="3"/><line x1="140" y1="80" x2="260" y2="80" stroke="#6366F1" stroke-width="3"/><line x1="140" y1="100" x2="260" y2="100" stroke="#6366F1" stroke-width="3"/>',
            '<rect x="100" y="50" width="200" height="240" rx="8" fill="#374151" stroke="#6366F1" stroke-width="4"/><path d="M130 80 Q180 150 240 80 M130 180 Q180 250 240 180" stroke="#6366F1" stroke-width="4" fill="none"/>',
            '<rect x="80" y="100" width="240" height="120" rx="10" fill="#1E293B" stroke="#6366F1" stroke-width="4"/><circle cx="120" cy="160" r="15" fill="#6366F1"/><circle cx="170" cy="160" r="8" fill="#9CA3AF"/><circle cx="200" cy="160" r="8" fill="#9CA3AF"/><rect x="230" y="152" width="30" height="16" fill="#9CA3AF"/>'
        ]
    ],
    'cooling' => [
        'title' => 'Tản nhiệt PC',
        'color' => '#38BDF8',
        'angles' => [
            'Tản nước AIO 360mm',
            'Block nước CPU RGB',
            'Tản khí Tháp đôi Dual Fan',
            'Quạt Radiator ARGB'
        ],
        'drawings' => [
            '<rect x="40" y="70" width="320" height="60" rx="8" fill="#0F172A" stroke="#38BDF8" stroke-width="4"/><circle cx="200" cy="220" r="40" fill="#1E293B" stroke="#38BDF8" stroke-width="4"/><path d="M120 130 C120 180 170 180 170 220 M280 130 C280 180 230 180 230 220" stroke="#38BDF8" stroke-width="8" fill="none"/>',
            '<circle cx="200" cy="160" r="60" fill="#1E293B" stroke="#38BDF8" stroke-width="6"/><circle cx="200" cy="160" r="40" fill="none" stroke="#EC4899" stroke-width="4"/>',
            '<rect x="100" y="80" width="200" height="160" rx="8" fill="#374151" stroke="#38BDF8" stroke-width="4"/><line x1="120" y1="90" x2="280" y2="90" stroke="#38BDF8" stroke-width="4"/><line x1="120" y1="120" x2="280" y2="120" stroke="#38BDF8" stroke-width="4"/><line x1="120" y1="150" x2="280" y2="150" stroke="#38BDF8" stroke-width="4"/>',
            '<rect x="90" y="50" width="220" height="220" rx="12" fill="#0F172A" stroke="#38BDF8" stroke-width="4"/><circle cx="200" cy="160" r="80" fill="none" stroke="#38BDF8" stroke-width="4"/><path d="M200 80 L200 240 M120 160 L280 160" stroke="#38BDF8" stroke-width="6"/>'
        ]
    ],
    'psu' => [
        'title' => 'Nguồn máy tính PSU',
        'color' => '#EAB308',
        'angles' => [
            'Mặt quạt tản nhiệt Nguồn',
            'Cụm Cổng dây Full Modular',
            'Thông số công suất Nhãn dán',
            'Bộ Dây cáp nguồn Bọc lưới'
        ],
        'drawings' => [
            '<rect x="80" y="60" width="240" height="200" rx="10" fill="#1E293B" stroke="#EAB308" stroke-width="4"/><circle cx="200" cy="160" r="70" fill="#0F172A" stroke="#EAB308" stroke-width="4"/><circle cx="200" cy="160" r="20" fill="#EAB308"/>',
            '<rect x="80" y="60" width="240" height="200" rx="10" fill="#1E293B" stroke="#EAB308" stroke-width="4"/><rect x="110" y="90" width="40" height="30" fill="#0F172A"/><rect x="170" y="90" width="40" height="30" fill="#0F172A"/><rect x="230" y="90" width="40" height="30" fill="#0F172A"/><rect x="110" y="150" width="60" height="40" fill="#0F172A"/>',
            '<rect x="70" y="70" width="260" height="180" rx="8" fill="#374151" stroke="#EAB308" stroke-width="4"/><rect x="90" y="90" width="220" height="140" fill="#FEF08A"/><text x="200" y="140" font-family="Arial" font-size="24" font-weight="bold" fill="#854D0E" text-anchor="middle">80 PLUS GOLD</text><text x="200" y="180" font-family="Arial" font-size="18" font-weight="bold" fill="#1E293B" text-anchor="middle">750W ATX 3.0</text>',
            '<rect x="60" y="80" width="280" height="160" rx="8" fill="#1E293B" stroke="#EAB308" stroke-width="4"/><path d="M80 120 C140 180 200 100 320 160" stroke="#EAB308" stroke-width="8" fill="none"/><path d="M80 160 C140 220 200 140 320 200" stroke="#F59E0B" stroke-width="8" fill="none"/>'
        ]
    ],
    'keyboard' => [
        'title' => 'Bàn phím cơ Keyboard',
        'color' => '#A855F7',
        'angles' => [
            'Chính diện Chuẩn Layout 75%',
            'Keycap & Switch Cơ học',
            'Góc nghiêng Chân nâng hạ',
            'Đèn nền RGB Mạch ngược'
        ],
        'drawings' => [
            '<rect x="40" y="100" width="320" height="120" rx="10" fill="#1E293B" stroke="#A855F7" stroke-width="4"/><rect x="55" y="115" width="290" height="90" rx="6" fill="#0F172A"/><line x1="65" y1="140" x2="335" y2="140" stroke="#A855F7" stroke-width="2" stroke-dasharray="12 4"/><line x1="65" y1="170" x2="335" y2="170" stroke="#A855F7" stroke-width="2" stroke-dasharray="12 4"/>',
            '<rect x="100" y="80" width="200" height="160" rx="10" fill="#0F172A" stroke="#A855F7" stroke-width="4"/><rect x="140" y="120" width="120" height="80" rx="8" fill="#A855F7"/><rect x="160" y="140" width="80" height="40" rx="4" fill="#F3E8FF"/>',
            '<path d="M50 180 L350 150 L340 120 L60 140 Z" fill="#1E293B" stroke="#A855F7" stroke-width="4"/><polygon points="320,152 340,150 335,180" fill="#A855F7"/>',
            '<rect x="40" y="90" width="320" height="140" rx="12" fill="#0F172A" stroke="#A855F7" stroke-width="4"/><path d="M60 160 C120 110 200 210 340 160" stroke="#EC4899" stroke-width="12" fill="none" opacity="0.8"/>'
        ]
    ],
    'mouse' => [
        'title' => 'Chuột Gaming Mouse',
        'color' => '#3B82F6',
        'angles' => [
            'Chính diện Thiết kế Ergonomic',
            'Cạnh bên Phím bấm phụ',
            'Mặt dưới Mắt đọc & Skate PTFE',
            'Dock sạc Không dây'
        ],
        'drawings' => [
            '<ellipse cx="200" cy="160" rx="70" ry="100" fill="#1E293B" stroke="#3B82F6" stroke-width="4"/><line x1="200" y1="60" x2="200" y2="130" stroke="#3B82F6" stroke-width="3"/><rect x="192" y="90" width="16" height="30" rx="8" fill="#3B82F6"/>',
            '<path d="M120 180 C120 100 200 80 280 180 C260 220 140 220 120 180 Z" fill="#1E293B" stroke="#3B82F6" stroke-width="4"/><rect x="180" y="120" width="35" height="18" rx="4" fill="#3B82F6"/>',
            '<ellipse cx="200" cy="160" rx="70" ry="100" fill="#0F172A" stroke="#3B82F6" stroke-width="4"/><ellipse cx="200" cy="160" rx="15" ry="25" fill="#EF4444"/><polygon points="150,90 250,90 240,110 160,110" fill="#9CA3AF"/>',
            '<rect x="120" y="180" width="160" height="60" rx="12" fill="#1E293B" stroke="#3B82F6" stroke-width="4"/><ellipse cx="200" cy="150" rx="50" ry="70" fill="#0F172A" stroke="#3B82F6" stroke-width="3"/>'
        ]
    ],
    'headset' => [
        'title' => 'Tai nghe Headset',
        'color' => '#10B981',
        'angles' => [
            'Chính diện Khung vòm Kim loại',
            'Đệm tai Đệm da Memory Foam',
            'Micro Boom Tháo rời',
            'Góc gập Xoay 90 độ'
        ],
        'drawings' => [
            '<path d="M100 180 C100 70 300 70 300 180" stroke="#10B981" stroke-width="12" fill="none"/><rect x="70" y="160" width="50" height="80" rx="20" fill="#1E293B" stroke="#10B981" stroke-width="4"/><rect x="280" y="160" width="50" height="80" rx="20" fill="#1E293B" stroke="#10B981" stroke-width="4"/>',
            '<ellipse cx="200" cy="160" rx="70" ry="90" fill="#1E293B" stroke="#10B981" stroke-width="6"/><ellipse cx="200" cy="160" rx="40" ry="55" fill="#0F172A"/>',
            '<path d="M100 160 C100 70 300 70 300 160" stroke="#10B981" stroke-width="10" fill="none"/><rect x="70" y="150" width="45" height="70" rx="15" fill="#1E293B"/><path d="M90 200 C90 260 170 260 170 240" stroke="#F59E0B" stroke-width="4" fill="none"/><circle cx="170" cy="240" r="10" fill="#F59E0B"/>',
            '<rect x="90" y="140" width="80" height="50" rx="12" fill="#1E293B" stroke="#10B981" stroke-width="4"/><rect x="230" y="140" width="80" height="50" rx="12" fill="#1E293B" stroke="#10B981" stroke-width="4"/><path d="M130 90 L270 90" stroke="#10B981" stroke-width="8"/>'
        ]
    ],
    'speaker' => [
        'title' => 'Loa máy tính Speaker',
        'color' => '#F59E0B',
        'angles' => [
            'Cặp Loa vệ tinh & Subwoofer',
            'Thanh Loa Soundbar RGB',
            'Màng loa Mid-Bass & Tweeter',
            'Núm xoay Điều khiển Âm lượng'
        ],
        'drawings' => [
            '<rect x="60" y="120" width="70" height="140" rx="8" fill="#1E293B" stroke="#F59E0B" stroke-width="4"/><rect x="270" y="120" width="70" height="140" rx="8" fill="#1E293B" stroke="#F59E0B" stroke-width="4"/><rect x="150" y="80" width="100" height="180" rx="10" fill="#0F172A" stroke="#F59E0B" stroke-width="4"/><circle cx="200" cy="180" r="35" fill="#334155"/>',
            '<rect x="40" y="130" width="320" height="60" rx="12" fill="#1E293B" stroke="#F59E0B" stroke-width="4"/><line x1="60" y1="160" x2="340" y2="160" stroke="#FBBF24" stroke-width="6" stroke-dasharray="10 5"/>',
            '<rect x="110" y="60" width="180" height="220" rx="12" fill="#0F172A" stroke="#F59E0B" stroke-width="4"/><circle cx="200" cy="110" r="25" fill="#1E293B" stroke="#F59E0B" stroke-width="3"/><circle cx="200" cy="200" r="45" fill="#1E293B" stroke="#F59E0B" stroke-width="4"/>',
            '<rect x="100" y="70" width="200" height="200" rx="12" fill="#1E293B" stroke="#F59E0B" stroke-width="4"/><circle cx="200" cy="170" r="60" fill="#0F172A" stroke="#F59E0B" stroke-width="5"/><line x1="200" y1="170" x2="200" y2="120" stroke="#FBBF24" stroke-width="6"/>'
        ]
    ],
    'chair' => [
        'title' => 'Ghế Gaming Ergonomic',
        'color' => '#EF4444',
        'angles' => [
            'Chính diện Đệm Da PU',
            'Góc ngả lưng 180 Độ',
            'Tay ghế 4D Kèm Đệm cổ',
            'Chân đế Bánh xe Xoay 360'
        ],
        'drawings' => [
            '<rect x="130" y="50" width="140" height="150" rx="15" fill="#1E293B" stroke="#EF4444" stroke-width="4"/><rect x="110" y="190" width="180" height="40" rx="8" fill="#1E293B" stroke="#EF4444" stroke-width="4"/><path d="M150 230 L250 230 L200 280 Z" fill="#374151"/>',
            '<path d="M120 180 L280 180 L280 150 L120 150 Z" fill="#1E293B" stroke="#EF4444" stroke-width="4"/><rect x="100" y="190" width="140" height="35" rx="6" fill="#374151"/>',
            '<rect x="130" y="60" width="140" height="140" rx="12" fill="#1E293B" stroke="#EF4444" stroke-width="4"/><rect x="80" y="140" width="30" height="60" rx="6" fill="#EF4444"/><rect x="290" y="140" width="30" height="60" rx="6" fill="#EF4444"/>',
            '<circle cx="200" cy="200" r="20" fill="#1E293B" stroke="#EF4444" stroke-width="4"/><line x1="200" y1="200" x2="120" y2="240" stroke="#EF4444" stroke-width="6"/><line x1="200" y1="200" x2="280" y2="240" stroke="#EF4444" stroke-width="6"/><circle cx="120" cy="245" r="10" fill="#374151"/><circle cx="280" cy="245" r="10" fill="#374151"/>'
        ]
    ],
    'console' => [
        'title' => 'Máy chơi game Console',
        'color' => '#8B5CF6',
        'angles' => [
            'Máy Cầm tay Cụm Phím Cặp',
            'Thumbstick & D-Pad Cận cảnh',
            'Cạnh trên Cổng Tản nhiệt & Cò',
            'Chế độ Dock & Chân chống'
        ],
        'drawings' => [
            '<rect x="60" y="100" width="280" height="120" rx="20" fill="#1E293B" stroke="#8B5CF6" stroke-width="4"/><rect x="120" y="115" width="160" height="90" rx="6" fill="#0F172A"/><circle cx="90" cy="145" r="15" fill="#8B5CF6"/><circle cx="310" cy="170" r="15" fill="#8B5CF6"/>',
            '<rect x="80" y="80" width="240" height="160" rx="12" fill="#0F172A" stroke="#8B5CF6" stroke-width="4"/><circle cx="140" cy="160" r="30" fill="#1E293B" stroke="#8B5CF6" stroke-width="4"/><polygon points="250,130 270,160 250,190 230,160" fill="#EC4899"/>',
            '<rect x="50" y="120" width="300" height="70" rx="10" fill="#1E293B" stroke="#8B5CF6" stroke-width="4"/><rect x="80" y="100" width="40" height="20" rx="4" fill="#8B5CF6"/><rect x="280" y="100" width="40" height="20" rx="4" fill="#8B5CF6"/>',
            '<rect x="100" y="140" width="200" height="80" rx="10" fill="#1E293B" stroke="#8B5CF6" stroke-width="4"/><rect x="70" y="100" width="260" height="60" rx="8" fill="#0F172A" stroke="#8B5CF6" stroke-width="3"/>'
        ]
    ],
    'accessories' => [
        'title' => 'Phụ kiện máy tính',
        'color' => '#64748B',
        'angles' => [
            'Bộ Hub USB-C Đa năng',
            'Cáp HDMI 2.1 Tốc độ cao',
            'Giá đỡ Card màn hình VGA',
            'Dây cuốn Quản lý Cáp gọn'
        ],
        'drawings' => [
            '<rect x="80" y="110" width="200" height="80" rx="10" fill="#1E293B" stroke="#64748B" stroke-width="4"/><rect x="280" y="140" width="60" height="20" rx="4" fill="#38BDF8"/><rect x="100" y="135" width="25" height="10" fill="#0F172A"/><rect x="140" y="135" width="25" height="10" fill="#0F172A"/><rect x="180" y="135" width="25" height="10" fill="#0F172A"/>',
            '<rect x="100" y="130" width="80" height="40" rx="6" fill="#334155" stroke="#64748B" stroke-width="3"/><path d="M180 150 C260 150 260 220 320 220" stroke="#F59E0B" stroke-width="8" fill="none"/>',
            '<rect x="180" y="50" width="40" height="220" rx="6" fill="#1E293B" stroke="#64748B" stroke-width="4"/><rect x="130" y="130" width="90" height="25" rx="4" fill="#38BDF8"/>',
            '<rect x="60" y="120" width="280" height="60" rx="8" fill="#334155" stroke="#64748B" stroke-width="4"/><line x1="80" y1="150" x2="320" y2="150" stroke="#64748B" stroke-width="4" stroke-dasharray="10 5"/>'
        ]
    ],
    'office-equipment' => [
        'title' => 'Thiết bị văn phòng',
        'color' => '#2563EB',
        'angles' => [
            'Máy in Laser Đa năng',
            'Khay nạp Tài liệu Tự động',
            'Màn hình Cảm ứng Điều khiển',
            'Khoang Hộp mực Toner'
        ],
        'drawings' => [
            '<rect x="80" y="100" width="240" height="160" rx="12" fill="#1E293B" stroke="#2563EB" stroke-width="4"/><rect x="110" y="60" width="180" height="50" rx="6" fill="#334155"/><rect x="120" y="210" width="160" height="30" fill="#0F172A"/>',
            '<rect x="70" y="80" width="260" height="80" rx="10" fill="#1E293B" stroke="#2563EB" stroke-width="4"/><rect x="100" y="60" width="200" height="30" rx="4" fill="#94A3B8"/>',
            '<rect x="100" y="80" width="200" height="150" rx="10" fill="#1E293B" stroke="#2563EB" stroke-width="4"/><rect x="130" y="110" width="140" height="90" rx="6" fill="#0F172A" stroke="#38BDF8" stroke-width="3"/>',
            '<rect x="70" y="90" width="260" height="140" rx="10" fill="#334155" stroke="#2563EB" stroke-width="4"/><rect x="100" y="120" width="200" height="80" rx="8" fill="#0F172A"/>'
        ]
    ],
    'power-bank' => [
        'title' => 'Sạc dự phòng Power Bank',
        'color' => '#059669',
        'angles' => [
            'Chính diện Dung lượng 20000mAh',
            'Cổng sạc Nhanh Dual USB-C & A',
            'Màn hình LED Báo % Pin',
            'Mặt lưng Nam châm MagSafe'
        ],
        'drawings' => [
            '<rect x="120" y="60" width="160" height="220" rx="16" fill="#1E293B" stroke="#059669" stroke-width="4"/><text x="200" y="180" font-family="Arial" font-size="22" font-weight="bold" fill="#34D399" text-anchor="middle">20000 mAh</text>',
            '<rect x="100" y="100" width="200" height="120" rx="12" fill="#1E293B" stroke="#059669" stroke-width="4"/><rect x="130" y="145" width="30" height="15" rx="3" fill="#0F172A"/><rect x="185" y="145" width="30" height="15" rx="3" fill="#0F172A"/><rect x="240" y="145" width="30" height="15" rx="3" fill="#059669"/>',
            '<rect x="120" y="60" width="160" height="220" rx="16" fill="#1E293B" stroke="#059669" stroke-width="4"/><rect x="145" y="90" width="110" height="45" rx="6" fill="#0F172A"/><text x="200" y="122" font-family="Arial" font-size="24" font-weight="bold" fill="#10B981" text-anchor="middle">100%</text>',
            '<rect x="120" y="60" width="160" height="220" rx="16" fill="#1E293B" stroke="#059669" stroke-width="4"/><circle cx="200" cy="170" r="45" fill="none" stroke="#34D399" stroke-width="5" stroke-dasharray="10 4"/>'
        ]
    ]
];

$createdCount = 0;

foreach ($categoryAngleConfigs as $catSlug => $cfg) {
    for ($angleIdx = 1; $angleIdx <= 4; $angleIdx++) {
        $filename = "placeholder-{$catSlug}-{$angleIdx}.svg";
        $filePath = $outputDir . '/' . $filename;
        $angleLabel = $cfg['angles'][$angleIdx - 1];
        $drawing = $cfg['drawings'][$angleIdx - 1];

        $svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 320" width="100%" height="100%">
  <defs>
    <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#0F172A" />
      <stop offset="50%" stop-color="#1E293B" />
      <stop offset="100%" stop-color="#090D16" />
    </linearGradient>
  </defs>
  <rect width="400" height="320" rx="12" fill="url(#bgGrad)"/>
  <rect x="2" y="2" width="396" height="316" rx="10" fill="none" stroke="{$cfg['color']}" stroke-width="2" stroke-opacity="0.3"/>
  <g>
    {$drawing}
  </g>
  <rect x="20" y="270" width="360" height="36" rx="6" fill="#0F172A" fill-opacity="0.85" stroke="{$cfg['color']}" stroke-width="1.5"/>
  <text x="200" y="293" font-family="system-ui, -apple-system, sans-serif" font-size="13" font-weight="600" fill="#F8FAFC" text-anchor="middle">
    {$cfg['title']} • Góc {$angleIdx}/4: {$angleLabel}
  </text>
</svg>
SVG;

        file_put_contents($filePath, $svgContent);
        $createdCount++;
    }
}

echo "Successfully generated $createdCount category angle SVG placeholders under public/assets/images/products/\n";
