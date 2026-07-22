<?php
$verticalCategories = require ROOT_PATH . '/app/data/category-menu.php';

// Fetch all active products' data to filter the menu
require_once ROOT_PATH . '/config/database.php';
$db = Database::getConnection();

// Get active products with category slug and brand name
$stmt = $db->query("
    SELECT p.name, p.specs, p.sale_price, c.slug as category_slug, b.name as brand_name 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN brands b ON p.brand_id = b.id
    WHERE p.status = 'active'
");
$activeProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hasProducts = function($catSlug, $searchTerm = '') use ($activeProducts) {
    foreach ($activeProducts as $p) {
        if ($p['category_slug'] === $catSlug) {
            if ($searchTerm === '') {
                return true; // Category has at least one product
            }
            
            // Check price filter strings
            if (strpos($searchTerm, 'triệu') !== false) {
                $price = (float)$p['sale_price'];
                if ($searchTerm === 'Dưới 15 triệu' || $searchTerm === 'Dưới 20 triệu') {
                    $limit = $searchTerm === 'Dưới 15 triệu' ? 15000000 : 20000000;
                    if ($price < $limit) return true;
                } elseif ($searchTerm === 'Từ 15 đến 20 triệu') {
                    if ($price >= 15000000 && $price <= 20000000) return true;
                } elseif ($searchTerm === '20 - 30 triệu') {
                    if ($price >= 20000000 && $price <= 30000000) return true;
                } elseif ($searchTerm === '30 - 40 triệu') {
                    if ($price >= 30000000 && $price <= 40000000) return true;
                } elseif ($searchTerm === 'Trên 20 triệu') {
                    if ($price > 20000000) return true;
                } elseif ($searchTerm === 'Trên 40 triệu') {
                    if ($price > 40000000) return true;
                }
                continue; // Move to next product if price doesn't match
            }

            // Check brand
            if (!empty($p['brand_name']) && stripos($p['brand_name'], $searchTerm) !== false) {
                return true;
            }
            
            // Check name
            if (stripos($p['name'], $searchTerm) !== false) {
                return true;
            }
            
            // Check raw specs JSON string
            if (!empty($p['specs']) && stripos($p['specs'], $searchTerm) !== false) {
                return true;
            }
        }
    }
    return false;
};

// Filter the array
$filteredCategories = [];
foreach ($verticalCategories as $item) {
    $catSlug = !empty($item['slug']) ? $item['slug'] : 'laptop-gaming';
    if (!$hasProducts($catSlug)) {
        continue; // Skip this main category if it has no products at all
    }
    
    $filteredColumns = [];
    if (!empty($item['columns'])) {
        foreach ($item['columns'] as $title => $subitems) {
            $filteredSubitems = [];
            foreach ($subitems as $subitem) {
                if ($hasProducts($catSlug, $subitem)) {
                    $filteredSubitems[] = $subitem;
                }
            }
            if (!empty($filteredSubitems)) {
                $filteredColumns[$title] = $filteredSubitems;
            }
        }
    }
    
    $item['columns'] = $filteredColumns;
    $filteredCategories[] = $item;
}
?>
<nav class="vertical-menu" id="sharedCategoryMenu">
    <?php foreach ($filteredCategories as $index => $item): ?>
        <?php $catSlug = !empty($item['slug']) ? $item['slug'] : 'laptop-gaming'; ?>
        <div class="vertical-menu__item" data-category-item="<?= (int)$index ?>">
            <div class="mobile-category-row">
                <a href="<?= url('home/search?cat=' . e($catSlug)) ?>" class="vertical-menu__link">
                    <div>
                        <i class="<?= e($item['icon']) ?>" style="width: 20px;"></i>
                        <span><?= e($item['name']) ?></span>
                    </div>
                    <i class="fa-solid fa-chevron-right arrow-right"></i>
                </a>
                <?php if (!empty($item['columns'])): ?>
                    <button type="button" class="mobile-category-toggle" aria-expanded="false" aria-label="Mở danh mục con">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                <?php endif; ?>
            </div>
            <?php if (!empty($item['columns'])): ?>
                <div class="mega-menu">
                    <div class="mega-menu__inner">
                        <?php foreach ($item['columns'] as $title => $subitems): ?>
                            <div class="mega-menu__column">
                                <h5><?= e($title) ?></h5>
                                <ul>
                                    <?php foreach ($subitems as $subitem): ?>
                                        <li><a href="<?= url('home/search?cat=' . e($catSlug) . '&q=' . urlencode($subitem)) ?>"><?= e($subitem) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</nav>
