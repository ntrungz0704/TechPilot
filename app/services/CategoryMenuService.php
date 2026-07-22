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

                $menuTree[] = [
                    'id'           => $group['key'],
                    'name'         => $group['name'],
                    'slug'         => $group['canonical_slug'],
                    'icon'         => $group['icon'],
                    'mega_columns' => $megaColumns,
                ];
            }

            return $menuTree;
        } catch (Exception $e) {
            error_log('CategoryMenuService error: ' . $e->getMessage());
            return []; // Graceful fallback
        }
    }
}
