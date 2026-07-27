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
            ['id' => 'laptop', 'name' => 'Laptop', 'slug' => 'laptop', 'icon' => 'fa-solid fa-laptop', 'mega_columns' => []],
            ['id' => 'pc', 'name' => 'PC & Build PC', 'slug' => 'pc', 'icon' => 'fa-solid fa-desktop', 'mega_columns' => []],
            ['id' => 'pc-linh-kien', 'name' => 'Linh kiện PC', 'slug' => 'pc-linh-kien', 'icon' => 'fa-solid fa-microchip', 'mega_columns' => []],
            ['id' => 'man-hinh', 'name' => 'Màn hình', 'slug' => 'man-hinh', 'icon' => 'fa-solid fa-tv', 'mega_columns' => []],
            ['id' => 'gaming-gear', 'name' => 'Gaming Gear', 'slug' => 'gaming-gear', 'icon' => 'fa-solid fa-gamepad', 'mega_columns' => []],
            ['id' => 'office-gear', 'name' => 'Thiết bị văn phòng', 'slug' => 'office-gear', 'icon' => 'fa-solid fa-print', 'mega_columns' => []],
            ['id' => 'networking', 'name' => 'Thiết bị mạng', 'slug' => 'networking', 'icon' => 'fa-solid fa-wifi', 'mega_columns' => []],
        ];
    }
}
