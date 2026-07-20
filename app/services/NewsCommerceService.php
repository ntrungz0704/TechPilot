<?php

final class NewsCommerceService
{
    public static function buildTrackedUrl(string $path, array $destinationParams, string $placement, string $category): string
    {
        $query = array_merge($destinationParams, [
            'utm_source'   => 'techpilot_news',
            'utm_medium'   => 'article',
            'utm_campaign' => $placement . '_' . ($category !== '' ? $category : 'general'),
        ]);

        $queryString = http_build_query($query);
        if (str_contains($path, '?')) {
            return url($path . '&' . $queryString);
        }
        return url($path . '?' . $queryString);
    }

    public function getConfig(string $categorySlug, string $postType): array
    {
        $cat = strtolower(trim($categorySlug));
        $type = strtolower(trim($postType));

        // Standardize category key
        if (str_contains($cat, 'laptop')) {
            $catKey = 'laptop';
        } elseif (str_contains($cat, 'pc-gaming')) {
            $catKey = 'pc-gaming';
        } elseif (str_contains($cat, 'linh-kien') || str_contains($cat, 'pc-linh-kien')) {
            $catKey = 'pc-linh-kien';
        } elseif (str_contains($cat, 'man-hinh')) {
            $catKey = 'man-hinh';
        } elseif (str_contains($cat, 'gaming-gear')) {
            $catKey = 'gaming-gear';
        } elseif (str_contains($cat, 'office-gear') || str_contains($cat, 'office')) {
            $catKey = 'office-gear';
        } elseif (str_contains($cat, 'networking') || str_contains($cat, 'mang')) {
            $catKey = 'networking';
        } elseif (str_contains($cat, 'ai')) {
            $catKey = 'ai';
        } else {
            $catKey = 'default';
        }

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
                            'label'  => 'Laptop Gaming đỉnh cao',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'laptop-gaming'],
                            'icon'   => 'fa-laptop-code',
                        ],
                        [
                            'label'  => 'Laptop Văn Phòng mỏng nhẹ',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'laptop-van-phong'],
                            'icon'   => 'fa-briefcase',
                        ],
                        [
                            'label'  => 'Laptop Đồ Hoạ - Kỹ Thuật',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'laptop-gaming', 'q' => 'đồ họa'],
                            'icon'   => 'fa-paint-brush',
                        ],
                    ],
                ];

            case 'pc-gaming':
            case 'pc-linh-kien':
                return [
                    'title' => 'Xây dựng & Mua PC',
                    'items' => [
                        [
                            'label'  => 'Tự Build PC theo ý muốn',
                            'path'   => 'build-pc',
                            'params' => [],
                            'icon'   => 'fa-sliders',
                        ],
                        [
                            'label'  => 'PC Build Sẵn hiệu năng cao',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'pc-build-san'],
                            'icon'   => 'fa-desktop',
                        ],
                        [
                            'label'  => 'Linh kiện PC chính hãng',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'pc-linh-kien'],
                            'icon'   => 'fa-microchip',
                        ],
                    ],
                ];

            case 'man-hinh':
                return [
                    'title' => 'Màn hình & Phụ kiện',
                    'items' => [
                        [
                            'label'  => 'Màn Hình Gaming tần số quét cao',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'man-hinh', 'q' => 'gaming'],
                            'icon'   => 'fa-tv',
                        ],
                        [
                            'label'  => 'Màn Hình Đồ Họa màu chuẩn',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'man-hinh', 'q' => 'đồ họa'],
                            'icon'   => 'fa-display',
                        ],
                    ],
                ];

            case 'gaming-gear':
                return [
                    'title' => 'Gaming Gear chính hãng',
                    'items' => [
                        [
                            'label'  => 'Chuột & Bàn phím Gaming',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'gaming-gear'],
                            'icon'   => 'fa-keyboard',
                        ],
                        [
                            'label'  => 'Tai nghe Gaming cao cấp',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'gaming-gear', 'q' => 'tai nghe'],
                            'icon'   => 'fa-headset',
                        ],
                    ],
                ];

            case 'office-gear':
                return [
                    'title' => 'Thiết bị văn phòng',
                    'items' => [
                        [
                            'label'  => 'Máy in & Phụ kiện văn phòng',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'office-gear'],
                            'icon'   => 'fa-print',
                        ],
                        [
                            'label'  => 'Laptop Văn Phòng mỏng nhẹ',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'laptop-van-phong'],
                            'icon'   => 'fa-briefcase',
                        ],
                    ],
                ];

            case 'networking':
                return [
                    'title' => 'Thiết bị mạng & Wifi',
                    'items' => [
                        [
                            'label'  => 'Router & Bộ phát Wifi',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'networking'],
                            'icon'   => 'fa-wifi',
                        ],
                        [
                            'label'  => 'Phụ kiện mạng cao cấp',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'networking', 'q' => 'phụ kiện'],
                            'icon'   => 'fa-network-wired',
                        ],
                    ],
                ];

            case 'ai':
                return [
                    'title' => 'Sản phẩm công nghệ AI',
                    'items' => [
                        [
                            'label'  => 'Laptop tích hợp AI',
                            'path'   => 'home/search',
                            'params' => ['q' => 'AI'],
                            'icon'   => 'fa-robot',
                        ],
                        [
                            'label'  => 'PC Workstation & AI',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'pc-build-san', 'q' => 'AI'],
                            'icon'   => 'fa-brain',
                        ],
                    ],
                ];

            default:
                return [
                    'title' => 'Gợi ý mua sắm',
                    'items' => [
                        [
                            'label'  => 'Laptop Gaming & Văn phòng',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'laptop-gaming'],
                            'icon'   => 'fa-laptop',
                        ],
                        [
                            'label'  => 'Tự Xây Dựng Cấu Hình PC',
                            'path'   => 'build-pc',
                            'params' => [],
                            'icon'   => 'fa-gears',
                        ],
                        [
                            'label'  => 'Linh Kiện & Phụ Kiện',
                            'path'   => 'home/search',
                            'params' => ['cat' => 'pc-linh-kien'],
                            'icon'   => 'fa-microchip',
                        ],
                    ],
                ];
        }
    }

    private function getMidCtaConfig(string $catKey, string $type): ?array
    {
        // Only allow Mid CTA for commercial intent post types
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
                    'cta_id'        => 'mid-laptop-' . $type,
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
                    'cta_id'        => 'mid-pc-' . $type,
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
                    'cta_id'        => 'mid-gear-' . $type,
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
                    'cta_id'        => 'mid-office-' . $type,
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
                    'cta_id'        => 'mid-network-' . $type,
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
                    'cta_id'        => 'mid-general-' . $type,
                ];
        }
    }

    private function getEndCtaConfig(string $catKey, string $type): ?array
    {
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
                    'cta_id'      => 'end-laptop-' . $type,
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
                    'cta_id'      => 'end-pc-' . $type,
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
                    'cta_id'      => 'end-office-' . $type,
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
                    'cta_id'      => 'end-network-' . $type,
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
                    'cta_id'      => 'end-general-' . $type,
                ];
        }
    }
}
