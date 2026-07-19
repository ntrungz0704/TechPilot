<?php

class CategoryMenuService
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getMegaMenuData()
    {
        // Get all active categories
        $stmt = $this->db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC, id ASC");
        $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $parents = [];
        $childrenMap = [];

        foreach ($allCategories as $cat) {
            if (empty($cat['parent_id'])) {
                $parents[] = $cat;
            } else {
                $childrenMap[$cat['parent_id']][] = $cat;
            }
        }

        $megaMenu = [];
        foreach ($parents as $parent) {
            $parentId = $parent['id'];
            $children = $childrenMap[$parentId] ?? [];
            
            // Get related brands (brands that have products in this category or its children)
            $catIds = array_column($children, 'id');
            $catIds[] = $parentId;
            
            $placeholders = str_repeat('?,', count($catIds) - 1) . '?';
            $sqlBrands = "SELECT DISTINCT b.* FROM brands b 
                          JOIN products p ON b.id = p.brand_id 
                          WHERE p.category_id IN ($placeholders) AND b.status = 'active'
                          LIMIT 10";
            $stmtBrands = $this->db->prepare($sqlBrands);
            $stmtBrands->execute($catIds);
            $brands = $stmtBrands->fetchAll(PDO::FETCH_ASSOC);

            // Get featured products (e.g., best seller or ai recommend) for this parent category
            $sqlProducts = "SELECT * FROM products 
                            WHERE category_id IN ($placeholders) AND status = 'active'
                            ORDER BY is_best_seller DESC, rating DESC, id DESC
                            LIMIT 3";
            $stmtProducts = $this->db->prepare($sqlProducts);
            $stmtProducts->execute($catIds);
            $featuredProducts = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);

            // Hardcode standard price ranges (can be dynamically created but hardcode is standard for tech shops)
            $priceRanges = [
                ['label' => 'Dưới 10 triệu', 'url' => 'home/search?cat=' . $parent['slug'] . '&price=under10'],
                ['label' => '10 - 15 triệu', 'url' => 'home/search?cat=' . $parent['slug'] . '&price=10to15'],
                ['label' => '15 - 20 triệu', 'url' => 'home/search?cat=' . $parent['slug'] . '&price=15to20'],
                ['label' => '20 - 30 triệu', 'url' => 'home/search?cat=' . $parent['slug'] . '&price=20to30'],
                ['label' => 'Trên 30 triệu', 'url' => 'home/search?cat=' . $parent['slug'] . '&price=over30'],
            ];

            $megaMenu[] = [
                'parent' => $parent,
                'children' => $children,
                'brands' => $brands,
                'featured_products' => $featuredProducts,
                'price_ranges' => $priceRanges
            ];
        }

        return $megaMenu;
    }
}
