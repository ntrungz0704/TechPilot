<?php

final class NewsCommerceService
{
    private const CATEGORY_ALIASES = [
        ''                 => 'default',
        'cong-nghe'        => 'default',
        'laptop'           => 'laptop',
        'laptop-gaming'    => 'laptop',
        'laptop-van-phong' => 'laptop',
        'pc-gaming'        => 'pc-gaming',
        'gaming'           => 'pc-gaming',
        'pc-build-san'     => 'pc-gaming',
        'pc-linh-kien'     => 'pc-linh-kien',
        'man-hinh'          => 'man-hinh',
        'gaming-gear'      => 'gaming-gear',
        'office-gear'      => 'office-gear',
        'networking'        => 'networking',
        'ai'               => 'ai',
        'ai-cong-nghe-moi' => 'ai',
    ];

    public static function normalizeTrackingValue(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = strtr($value, [
            'á' => 'a', 'à' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ắ' => 'a', 'ằ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'é' => 'e', 'è' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ế' => 'e', 'ề' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'í' => 'i', 'ì' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ố' => 'o', 'ồ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ớ' => 'o', 'ờ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ứ' => 'u', 'ừ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ý' => 'y', 'ỳ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
            'đ' => 'd'
        ]);
        $value = preg_replace('/[^a-z0-9_\-]+/u', '_', $value);
        $value = preg_replace('/_+/', '_', $value);
        $value = trim($value, '_-');
        return $value !== '' ? $value : 'general';
    }

    public static function buildTrackedUrl(string $path, array $destinationParams, string $placement, string $category): string
    {
        $normPlacement = self::normalizeTrackingValue($placement);
        $normCat       = self::normalizeTrackingValue($category);

        $query = array_merge($destinationParams, [
            'utm_source'   => 'techpilot_news',
            'utm_medium'   => 'article',
            'utm_campaign' => $normPlacement . '_' . $normCat,
        ]);

        $cleanPath   = rtrim(trim($path), '?&');
        $sep         = str_contains($cleanPath, '?') ? '&' : '?';
        $queryString = http_build_query($query);

        return url($cleanPath . $sep . $queryString);
    }

    public function getConfig(string $categorySlug, string $postType): array
    {
        $cat  = strtolower(trim($categorySlug));
        $type = strtolower(trim($postType));

        // Exact alias lookup
        $catKey = self::CATEGORY_ALIASES[$cat] ?? 'default';

        $sidebar = $this->getSidebarConfig($catKey);
        $midCta  = $this->getMidCtaConfig($catKey, $type);
        $endCta  = $this->getEndCtaConfig($catKey, $type);

        return [
            'sidebar' => $sidebar,
            'mid_cta' => $midCta,
            'end_cta' => $endCta,
        ];
    }

    private function getSidebarConfig(string $catKey): array
    {
        switch ($catKey) {
            case 'laptop':
                return [
                    'title' => 'Mua Laptop theo nhu cầu',
                    'items' => [
                        [
                            'label'       => 'Laptop Gaming đỉnh cao',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'laptop-gaming'],
                            'icon'        => 'fa-laptop-code',
                            'tracking_id' => 'sidebar_laptop_gaming',
                        ],
                        [
                            'label'       => 'Laptop Văn Phòng mỏng nhẹ',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'laptop-van-phong'],
                            'icon'        => 'fa-briefcase',
                            'tracking_id' => 'sidebar_laptop_van_phong',
                        ],
                        [
                            'label'       => 'Laptop Đồ Hoạ - Kỹ Thuật',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'laptop-gaming', 'q' => 'đồ họa'],
                            'icon'        => 'fa-paint-brush',
                            'tracking_id' => 'sidebar_laptop_do_hoa',
                        ],
                    ],
                ];

            case 'pc-gaming':
            case 'pc-linh-kien':
                return [
                    'title' => 'Xây dựng & Mua PC',
                    'items' => [
                        [
                            'label'       => 'Tự Build PC theo ý muốn',
                            'path'        => 'build-pc',
                            'params'      => [],
                            'icon'        => 'fa-sliders',
                            'tracking_id' => 'sidebar_pc_builder',
                        ],
                        [
                            'label'       => 'PC Build Sẵn hiệu năng cao',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'pc-build-san'],
                            'icon'        => 'fa-desktop',
                            'tracking_id' => 'sidebar_pc_build_san',
                        ],
                        [
                            'label'       => 'Linh kiện PC chính hãng',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'pc-linh-kien'],
                            'icon'        => 'fa-microchip',
                            'tracking_id' => 'sidebar_pc_linh_kien',
                        ],
                    ],
                ];

            case 'man-hinh':
                return [
                    'title' => 'Màn hình & Phụ kiện',
                    'items' => [
                        [
                            'label'       => 'Màn Hình Gaming tần số quét cao',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'man-hinh', 'q' => 'gaming'],
                            'icon'        => 'fa-tv',
                            'tracking_id' => 'sidebar_man_hinh_gaming',
                        ],
                        [
                            'label'       => 'Màn Hình Đồ Họa màu chuẩn',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'man-hinh', 'q' => 'đồ họa'],
                            'icon'        => 'fa-display',
                            'tracking_id' => 'sidebar_man_hinh_do_hoa',
                        ],
                    ],
                ];

            case 'gaming-gear':
                return [
                    'title' => 'Gaming Gear chính hãng',
                    'items' => [
                        [
                            'label'       => 'Chuột & Bàn phím Gaming',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'gaming-gear'],
                            'icon'        => 'fa-keyboard',
                            'tracking_id' => 'sidebar_gaming_gear',
                        ],
                        [
                            'label'       => 'Tai nghe Gaming cao cấp',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'gaming-gear', 'q' => 'tai nghe'],
                            'icon'        => 'fa-headset',
                            'tracking_id' => 'sidebar_tai_nghe_gaming',
                        ],
                    ],
                ];

            case 'office-gear':
                return [
                    'title' => 'Thiết bị văn phòng',
                    'items' => [
                        [
                            'label'       => 'Máy in & Phụ kiện văn phòng',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'office-gear'],
                            'icon'        => 'fa-print',
                            'tracking_id' => 'sidebar_office_gear',
                        ],
                        [
                            'label'       => 'Laptop Văn Phòng mỏng nhẹ',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'laptop-van-phong'],
                            'icon'        => 'fa-briefcase',
                            'tracking_id' => 'sidebar_laptop_van_phong',
                        ],
                    ],
                ];

            case 'networking':
                return [
                    'title' => 'Thiết bị mạng & Wifi',
                    'items' => [
                        [
                            'label'       => 'Router & Bộ phát Wifi',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'networking'],
                            'icon'        => 'fa-wifi',
                            'tracking_id' => 'sidebar_networking',
                        ],
                        [
                            'label'       => 'Phụ kiện mạng cao cấp',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'networking', 'q' => 'phụ kiện'],
                            'icon'        => 'fa-network-wired',
                            'tracking_id' => 'sidebar_phu_kien_mang',
                        ],
                    ],
                ];

            case 'ai':
                return [
                    'title' => 'Sản phẩm công nghệ AI',
                    'items' => [
                        [
                            'label'       => 'Laptop tích hợp AI',
                            'path'        => 'home/search',
                            'params'      => ['q' => 'AI'],
                            'icon'        => 'fa-robot',
                            'tracking_id' => 'sidebar_laptop_ai',
                        ],
                        [
                            'label'       => 'PC Workstation & AI',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'pc-build-san', 'q' => 'AI'],
                            'icon'        => 'fa-brain',
                            'tracking_id' => 'sidebar_pc_ai',
                        ],
                    ],
                ];

            default:
                return [
                    'title' => 'Gợi ý mua sắm',
                    'items' => [
                        [
                            'label'       => 'Laptop Gaming & Văn phòng',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'laptop-gaming'],
                            'icon'        => 'fa-laptop',
                            'tracking_id' => 'sidebar_laptop_general',
                        ],
                        [
                            'label'       => 'Tự Xây Dựng Cấu Hình PC',
                            'path'        => 'build-pc',
                            'params'      => [],
                            'icon'        => 'fa-gears',
                            'tracking_id' => 'sidebar_pc_builder',
                        ],
                        [
                            'label'       => 'Linh Kiện & Phụ Kiện',
                            'path'        => 'home/search',
                            'params'      => ['cat' => 'pc-linh-kien'],
                            'icon'        => 'fa-microchip',
                            'tracking_id' => 'sidebar_pc_linh_kien',
                        ],
                    ],
                ];
        }
    }

    private function getMidCtaConfig(string $catKey, string $type): ?array
    {
        // Only allow Mid CTA for review, guide, comparison
        if (!in_array($type, ['review', 'guide', 'comparison'], true)) {
            return null;
        }

        switch ($catKey) {
            case 'laptop':
                return [
                    'title'         => 'Đang tìm kiếm chiếc Laptop phù hợp?',
                    'desc'          => 'Khám phá ngay các dòng Laptop Gaming và Văn Phòng chính hãng với mức giá ưu đãi tại TechPilot.',
                    'primary_btn'   => [
                        'label'  => 'Xem Laptop Gaming',
                        'path'   => 'home/search',
                        'params' => ['cat' => 'laptop-gaming'],
                    ],
                    'secondary_btn' => [
                        'label'  => 'Xem Laptop Văn Phòng',
                        'path'   => 'home/search',
                        'params' => ['cat' => 'laptop-van-phong'],
                    ],
                    'cta_id'        => 'mid_laptop_' . $type,
                ];

            case 'pc-gaming':
            case 'pc-linh-kien':
                return [
                    'title'         => 'Muốn tự tay xây dựng cấu hình PC mơ ước?',
                    'desc'          => 'Sử dụng công cụ PC Builder thông minh của TechPilot để tự do phối ghép linh kiện chuẩn xác 100%.',
                    'primary_btn'   => [
                        'label'  => 'Build PC Ngay',
                        'path'   => 'build-pc',
                        'params' => [],
                    ],
                    'secondary_btn' => [
                        'label'  => 'Xem PC Build Sẵn',
                        'path'   => 'home/search',
                        'params' => ['cat' => 'pc-build-san'],
                    ],
                    'cta_id'        => 'mid_pc_' . $type,
                ];

            case 'man-hinh':
            case 'gaming-gear':
                return [
                    'title'         => 'Nâng cấp góc Gaming & Làm việc của bạn',
                    'desc'          => 'Trang bị Màn hình sắc nét và Gaming Gear chính hãng để tối ưu hóa trải nghiệm sử dụng.',
                    'primary_btn'   => [
                        'label'  => 'Khám Phá Màn Hình',
                        'path'   => 'home/search',
                        'params' => ['cat' => 'man-hinh'],
                    ],
                    'secondary_btn' => [
                        'label'  => 'Xem Gaming Gear',
                        'path'   => 'home/search',
                        'params' => ['cat' => 'gaming-gear'],
                    ],
                    'cta_id'        => 'mid_gear_' . $type,
                ];

            case 'office-gear':
                return [
                    'title'         => 'Trang bị thiết bị văn phòng chuyên nghiệp',
                    'desc'          => 'Khám phá máy in, thiết bị trình chiếu và phụ kiện văn phòng chính hãng bảo hành uy tín.',
                    'primary_btn'   => [
                        'label'  => 'Xem Thiết Bị Văn Phòng',
                        'path'   => 'home/search',
                        'params' => ['cat' => 'office-gear'],
                    ],
                    'secondary_btn' => null,
                    'cta_id'        => 'mid_office_' . $type,
                ];

            case 'networking':
                return [
                    'title'         => 'Nâng cấp hệ thống mạng & Wifi tốc độ cao',
                    'desc'          => 'Đảm bảo kết nối internet mượt mà, ổn định cho công việc và giải trí gia đình.',
                    'primary_btn'   => [
                        'label'  => 'Xem Thiết Bị Mạng',
                        'path'   => 'home/search',
                        'params' => ['cat' => 'networking'],
                    ],
                    'secondary_btn' => null,
                    'cta_id'        => 'mid_network_' . $type,
                ];

            default:
                return [
                    'title'         => 'Tìm kiếm thiết bị công nghệ chính hãng',
                    'desc'          => 'TechPilot cung cấp các sản phẩm Laptop, PC và Linh kiện công nghệ bảo hành chính hãng.',
                    'primary_btn'   => [
                        'label'  => 'Tất Cả Sản Phẩm',
                        'path'   => 'home/search',
                        'params' => [],
                    ],
                    'secondary_btn' => null,
                    'cta_id'        => 'mid_general_' . $type,
                ];
        }
    }

    private function getEndCtaConfig(string $catKey, string $type): ?array
    {
        // NO End CTA for news or unknown
        if (!in_array($type, ['review', 'guide', 'comparison', 'howto'], true)) {
            return null;
        }

        switch ($catKey) {
            case 'laptop':
                return [
                    'title'       => 'Cần tư vấn thêm về dòng Laptop phù hợp?',
                    'desc'        => 'Đội ngũ chuyên gia TechPilot sẵn sàng hỗ trợ tư vấn chọn mua mẫu máy tối ưu nhất cho nhu cầu của bạn.',
                    'primary_btn' => [
                        'label'  => 'Khám Phá Danh Mục Laptop',
                        'path'   => 'home/search',
                        'params' => ['cat' => 'laptop-gaming'],
                    ],
                    'secondary_btn' => null,
                    'cta_id'      => 'end_laptop_' . $type,
                ];

            case 'pc-gaming':
            case 'pc-linh-kien':
                return [
                    'title'       => 'Sẵn sàng sở hữu dàn PC hiệu năng cao?',
                    'desc'        => 'Khám phá các dàn PC dựng sẵn được tối ưu hóa hiệu năng hoặc bắt đầu xây dựng cấu hình riêng ngay hôm nay.',
                    'primary_btn' => [
                        'label'  => 'Khám Phá PC Build Sẵn',
                        'path'   => 'home/search',
                        'params' => ['cat' => 'pc-build-san'],
                    ],
                    'secondary_btn' => [
                        'label'  => 'Tự Build PC',
                        'path'   => 'build-pc',
                        'params' => [],
                    ],
                    'cta_id'      => 'end_pc_' . $type,
                ];

            case 'office-gear':
                return [
                    'title'       => 'Giải pháp thiết bị cho doanh nghiệp & văn phòng',
                    'desc'        => 'Tham khảo các dòng máy in, bộ phát wifi và phụ kiện chính hãng giá ưu đãi.',
                    'primary_btn' => [
                        'label'  => 'Khám Phá Thiết Bị Văn Phòng',
                        'path'   => 'home/search',
                        'params' => ['cat' => 'office-gear'],
                    ],
                    'secondary_btn' => null,
                    'cta_id'      => 'end_office_' . $type,
                ];

            case 'networking':
                return [
                    'title'       => 'Tối ưu trải nghiệm kết nối không dây',
                    'desc'        => 'Xem ngay các giải pháp Router Wifi 6 và bộ mở rộng sóng mạng tốt nhất.',
                    'primary_btn' => [
                        'label'  => 'Xem Thiết Bị Mạng',
                        'path'   => 'home/search',
                        'params' => ['cat' => 'networking'],
                    ],
                    'secondary_btn' => null,
                    'cta_id'      => 'end_network_' . $type,
                ];

            default:
                return [
                    'title'       => 'Tham khảo thêm các sản phẩm công nghệ tại TechPilot',
                    'desc'        => 'Cam kết 100% sản phẩm chính hãng, bảo hành uy tín và hỗ trợ kỹ thuật trọn đời.',
                    'primary_btn' => [
                        'label'  => 'Đến Cửa Hàng TechPilot',
                        'path'   => 'home/search',
                        'params' => [],
                    ],
                    'secondary_btn' => null,
                    'cta_id'      => 'end_general_' . $type,
                ];
        }
    }
}
