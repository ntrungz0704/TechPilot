<?php

class CategoryMenuService {
    public static function getActiveMenuTree() {
        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        
        if ($db === null) {
            return []; // Return empty menu tree if database is unavailable
        }

        // 1. Get main categories (parent_id IS NULL)
        $stmt = $db->query("SELECT id, name, slug, icon FROM categories WHERE status = 'active' AND parent_id IS NULL ORDER BY sort_order ASC, name ASC");
        $mainCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. Get sub categories
        $stmt = $db->query("SELECT id, parent_id, name, slug FROM categories WHERE status = 'active' AND parent_id IS NOT NULL ORDER BY sort_order ASC, name ASC");
        $subCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 3. Get active products with category_id, brand name, brand slug
        $stmt = $db->query("
            SELECT p.category_id, b.name as brand_name, b.slug as brand_slug
            FROM products p
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE p.status = 'active'
        ");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $menuTree = [];
        
        foreach ($mainCategories as $cat) {
            $catId = $cat['id'];
            $catBrands = [];
            $hasProducts = false;
            
            foreach ($products as $p) {
                if ($p['category_id'] == $catId) {
                    $hasProducts = true;
                }
                if ($p['category_id'] == $catId && !empty($p['brand_name'])) {
                    $catBrands[$p['brand_name']] = $p['brand_slug'];
                }
            }
            
            if (!$hasProducts) {
                continue; // Skip categories without products
            }
            
            $megaColumns = [];
            
            // Sub-categories
            $subs = [];
            foreach ($subCategories as $sub) {
                if ($sub['parent_id'] == $catId) {
                    $subs[] = ['name' => $sub['name'], 'slug' => $sub['slug']];
                }
            }
            if (!empty($subs)) {
                $megaColumns['Danh mục con'] = $subs;
            }
            
            // Brands
            if (!empty($catBrands)) {
                $formattedBrands = [];
                ksort($catBrands); // Sort by brand name
                foreach ($catBrands as $bName => $bSlug) {
                    $formattedBrands[] = ['name' => $bName, 'query' => 'brand=' . urlencode($bSlug)];
                }
                $megaColumns['Thương hiệu'] = $formattedBrands;
            }
            
            // Price Ranges
            $megaColumns['Mức giá'] = [
                ['name' => 'Dưới 15 triệu', 'query' => 'min_price=0&max_price=15000000'],
                ['name' => 'Từ 15 - 20 triệu', 'query' => 'min_price=15000000&max_price=20000000'],
                ['name' => 'Từ 20 - 30 triệu', 'query' => 'min_price=20000000&max_price=30000000'],
                ['name' => 'Trên 30 triệu', 'query' => 'min_price=30000000']
            ];
            
            $cat['mega_columns'] = $megaColumns;
            $menuTree[] = $cat;
        }
        
        return $menuTree;
    }
}
