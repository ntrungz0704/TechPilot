<?php

require_once ROOT_PATH . '/app/services/CatalogGroupService.php';

class CategoryMenuService
{
    /**
     * Lấy mảng cây danh mục cho Storefront Mega Menu dựa trên CatalogGroupService
     */
    public static function getActiveMenuTree(): array
    {
        try {
            $storefrontGroups = CatalogGroupService::getStorefrontGroups();
            $menuTree = [];

            foreach ($storefrontGroups as $group) {
                // Bỏ qua các nhóm không có sản phẩm hoặc chưa sẵn sàng
                if (($group['product_count'] ?? 0) <= 0 || ($group['status'] ?? 'not_ready') !== 'ready') {
                    continue;
                }

                $megaColumns = [];

                // 1. Sub-categories (Danh mục con thực sự có sản phẩm)
                if (!empty($group['subgroups'])) {
                    $subs = [];
                    foreach ($group['subgroups'] as $sub) {
                        $subs[] = [
                            'name' => $sub['name'],
                            'slug' => $sub['slug'],
                        ];
                    }
                    if (!empty($subs)) {
                        $megaColumns['Danh mục con'] = $subs;
                    }
                }

                // 2. Brands (Thương hiệu thực sự có sản phẩm trong nhóm)
                if (!empty($group['brands'])) {
                    $formattedBrands = [];
                    foreach ($group['brands'] as $b) {
                        $formattedBrands[] = [
                            'name'  => $b['name'],
                            'query' => $b['query'],
                        ];
                    }
                    $megaColumns['Thương hiệu'] = $formattedBrands;
                }

                // 3. Price Ranges (Khoảng giá được tối ưu hóa cho từng nhóm)
                if (!empty($group['price_ranges'])) {
                    $megaColumns['Mức giá'] = $group['price_ranges'];
                }

                // Menu link bắt buộc sử dụng virtual_slug ('laptop', 'pc', 'pc-linh-kien', v.v.)
                $menuTree[] = [
                    'id'           => $group['key'],
                    'name'         => $group['name'],
                    'slug'         => $group['virtual_slug'],
                    'icon'         => $group['icon'],
                    'mega_columns' => $megaColumns,
                ];
            }

            if (empty($menuTree)) {
                return self::getFallbackMenuTree();
            }

            return $menuTree;
        } catch (Exception $e) {
            error_log('[CategoryMenuService] ' . $e->getCode() . ' - ' . $e->getMessage());
            return self::getFallbackMenuTree();
        }
    }

    /**
     * Fallback top-level category menu khi DB chưa sẵn sàng
     */
    private static function getFallbackMenuTree(): array
    {
        return [
            [
                'id' => 'laptop',
                'name' => 'Laptop',
                'slug' => 'laptop',
                'icon' => 'fa-solid fa-laptop',
                'mega_columns' => [
                    'Danh mục con' => [
                        ['name' => 'Laptop Gaming', 'slug' => 'laptop-gaming'],
                        ['name' => 'Laptop Văn Phòng', 'slug' => 'laptop-van-phong'],
                    ],
                    'Thương hiệu' => [
                        ['name' => 'ASUS', 'query' => 'q=ASUS'],
                        ['name' => 'MSI', 'query' => 'q=MSI'],
                        ['name' => 'Lenovo', 'query' => 'q=Lenovo'],
                        ['name' => 'Dell', 'query' => 'q=Dell'],
                        ['name' => 'Apple', 'query' => 'q=Apple'],
                    ],
                    'Mức giá' => [
                        ['name' => 'Đến 15 triệu', 'query' => 'min_price=0&max_price=15000000'],
                        ['name' => 'Trên 15 đến 20 triệu', 'query' => 'min_price=15000001&max_price=20000000'],
                        ['name' => 'Trên 20 đến 30 triệu', 'query' => 'min_price=20000001&max_price=30000000'],
                        ['name' => 'Trên 30 triệu', 'query' => 'min_price=30000001'],
                    ]
                ]
            ],
            [
                'id' => 'pc',
                'name' => 'PC & Build PC',
                'slug' => 'pc',
                'icon' => 'fa-solid fa-desktop',
                'mega_columns' => [
                    'Danh mục con' => [
                        ['name' => 'PC Gaming', 'slug' => 'pc-build-san'],
                        ['name' => 'Máy tính đồng bộ', 'slug' => 'may-tinh-bo'],
                    ],
                    'Thương hiệu' => [
                        ['name' => 'Intel', 'query' => 'q=Intel'],
                        ['name' => 'AMD', 'query' => 'q=AMD'],
                        ['name' => 'ASUS', 'query' => 'q=ASUS'],
                        ['name' => 'Gigabyte', 'query' => 'q=Gigabyte'],
                    ],
                    'Mức giá' => [
                        ['name' => 'Đến 15 triệu', 'query' => 'min_price=0&max_price=15000000'],
                        ['name' => 'Trên 15 đến 20 triệu', 'query' => 'min_price=15000001&max_price=20000000'],
                        ['name' => 'Trên 20 đến 30 triệu', 'query' => 'min_price=20000001&max_price=30000000'],
                        ['name' => 'Trên 30 triệu', 'query' => 'min_price=30000001'],
                    ]
                ]
            ],
            [
                'id' => 'pc-linh-kien',
                'name' => 'Linh kiện PC',
                'slug' => 'pc-linh-kien',
                'icon' => 'fa-solid fa-microchip',
                'mega_columns' => [
                    'Danh mục con' => [
                        ['name' => 'Bo mạch chủ (Mainboard)', 'slug' => 'mainboard'],
                        ['name' => 'Bộ vi xử lý (CPU)', 'slug' => 'cpu'],
                        ['name' => 'Card màn hình (VGA)', 'slug' => 'vga'],
                        ['name' => 'Bộ nhớ trong (RAM)', 'slug' => 'ram'],
                        ['name' => 'Ổ cứng (SSD/HDD)', 'slug' => 'storage'],
                        ['name' => 'Nguồn máy tính (PSU)', 'slug' => 'psu'],
                        ['name' => 'Vỏ máy tính (Case)', 'slug' => 'case'],
                        ['name' => 'Tản nhiệt', 'slug' => 'cooling'],
                    ],
                    'Thương hiệu' => [
                        ['name' => 'ASUS', 'query' => 'q=ASUS'],
                        ['name' => 'MSI', 'query' => 'q=MSI'],
                        ['name' => 'Gigabyte', 'query' => 'q=Gigabyte'],
                        ['name' => 'Corsair', 'query' => 'q=Corsair'],
                        ['name' => 'Kingston', 'query' => 'q=Kingston'],
                    ]
                ]
            ],
            [
                'id' => 'man-hinh',
                'name' => 'Màn hình',
                'slug' => 'man-hinh',
                'icon' => 'fa-solid fa-tv',
                'mega_columns' => [
                    'Danh mục con' => [
                        ['name' => 'Màn hình Gaming', 'slug' => 'man-hinh'],
                        ['name' => 'Màn hình Đồ họa', 'slug' => 'man-hinh'],
                    ],
                    'Thương hiệu' => [
                        ['name' => 'ASUS', 'query' => 'q=ASUS'],
                        ['name' => 'LG', 'query' => 'q=LG'],
                        ['name' => 'Samsung', 'query' => 'q=Samsung'],
                        ['name' => 'Dell', 'query' => 'q=Dell'],
                    ]
                ]
            ],
            [
                'id' => 'gaming-gear',
                'name' => 'Gaming Gear',
                'slug' => 'gaming-gear',
                'icon' => 'fa-solid fa-gamepad',
                'mega_columns' => [
                    'Danh mục con' => [
                        ['name' => 'Bàn phím cơ', 'slug' => 'keyboard'],
                        ['name' => 'Chuột Gaming', 'slug' => 'mouse'],
                        ['name' => 'Tai nghe Gaming', 'slug' => 'headset'],
                        ['name' => 'Ghế Gaming', 'slug' => 'chair'],
                    ],
                    'Thương hiệu' => [
                        ['name' => 'Razer', 'query' => 'q=Razer'],
                        ['name' => 'Corsair', 'query' => 'q=Corsair'],
                        ['name' => 'Logitech', 'query' => 'q=Logitech'],
                    ]
                ]
            ],
            [
                'id' => 'office-gear',
                'name' => 'Thiết bị văn phòng',
                'slug' => 'office-gear',
                'icon' => 'fa-solid fa-print',
                'mega_columns' => [
                    'Danh mục con' => [
                        ['name' => 'Máy in', 'slug' => 'office-equipment'],
                        ['name' => 'Máy chiếu', 'slug' => 'office-equipment'],
                    ]
                ]
            ],
            [
                'id' => 'networking',
                'name' => 'Thiết bị mạng',
                'slug' => 'networking',
                'icon' => 'fa-solid fa-wifi',
                'mega_columns' => [
                    'Danh mục con' => [
                        ['name' => 'Router Wi-Fi', 'slug' => 'accessories'],
                        ['name' => 'Bộ phát 4G/5G', 'slug' => 'accessories'],
                    ]
                ]
            ]
        ];
    }
}
