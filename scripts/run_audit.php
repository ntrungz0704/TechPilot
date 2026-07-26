<?php
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getConnection();
if (!$pdo) {
    die("Database connection failed.\n");
}

echo "=== STAGE 1 AUDIT ===\n\n";

// Show products table columns
$cols = $pdo->query("DESCRIBE products")->fetchAll(PDO::FETCH_COLUMN);
echo "PRODUCTS TABLE COLUMNS: " . implode(", ", $cols) . "\n\n";

$imgCol = in_array('image', $cols) ? 'image' : (in_array('thumbnail', $cols) ? 'thumbnail' : 'image_url');
$soldCol = in_array('sold_count', $cols) ? 'sold_count' : (in_array('sold_quantity', $cols) ? 'sold_quantity' : (in_array('sold', $cols) ? 'sold' : ''));
$ratingAvgCol = in_array('rating_avg', $cols) ? 'rating_avg' : (in_array('rating', $cols) ? 'rating' : '');
$reviewCountCol = in_array('review_count', $cols) ? 'review_count' : (in_array('reviews_count', $cols) ? 'reviews_count' : '');

// 1. Total products
$total = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$active = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
$inactive = $pdo->query("SELECT COUNT(*) FROM products WHERE status != 'active'")->fetchColumn();
echo "1. TOTAL PRODUCTS: $total (Active: $active, Inactive: $inactive)\n\n";

// 2. Product count per category
echo "2. PRODUCTS PER CATEGORY:\n";
$stmt = $pdo->query("
    SELECT c.id, c.name, c.slug, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category_id = c.id AND p.status = 'active'
    GROUP BY c.id, c.name, c.slug
    ORDER BY c.id
");
$catCounts = $stmt->fetchAll();
foreach ($catCounts as $c) {
    echo "  - [ID {$c['id']}] {$c['name']} ({$c['slug']}): {$c['product_count']} active products\n";
}
echo "\n";

// 3. Products without category or non-existent category
$noCat = $pdo->query("SELECT COUNT(*) FROM products WHERE category_id IS NULL OR category_id = 0")->fetchColumn();
$badCat = $pdo->query("SELECT COUNT(*) FROM products WHERE category_id NOT IN (SELECT id FROM categories)")->fetchColumn();
echo "3. CATEGORY ISSUES: No Category: $noCat | Non-existent Category: $badCat\n\n";

// 4. Products without brand
$noBrand = $pdo->query("SELECT COUNT(*) FROM products WHERE brand_id IS NULL OR brand_id = 0 OR brand_id NOT IN (SELECT id FROM brands)")->fetchColumn();
echo "4. BRAND ISSUES: Products without valid brand: $noBrand\n\n";

// 5. Slug issues
$dupSlugs = $pdo->query("SELECT slug, COUNT(*) c FROM products GROUP BY slug HAVING c > 1")->fetchAll();
$emptySlugs = $pdo->query("SELECT COUNT(*) FROM products WHERE slug IS NULL OR TRIM(slug) = ''")->fetchColumn();
echo "5. SLUG ISSUES: Duplicate Slugs: " . count($dupSlugs) . " | Empty Slugs: $emptySlugs\n\n";

// 6. Image issues
$emptyThumb = $pdo->query("SELECT COUNT(*) FROM products WHERE `$imgCol` IS NULL OR TRIM(`$imgCol`) = ''")->fetchColumn();
echo "6. IMAGE ISSUES: Empty Image/Thumbnail ($imgCol): $emptyThumb\n";

$stmt = $pdo->query("
    SELECT `$imgCol`, COUNT(DISTINCT category_id) cat_count, COUNT(*) total_prods
    FROM products
    WHERE `$imgCol` IS NOT NULL AND `$imgCol` != ''
    GROUP BY `$imgCol`
    HAVING cat_count > 1
");
$sharedImgs = $stmt->fetchAll();
echo "  - Images shared across DIFFERENT categories: " . count($sharedImgs) . "\n\n";

// 7. Specs issues
$emptySpecs = $pdo->query("SELECT COUNT(*) FROM products WHERE specs IS NULL OR TRIM(specs) = '' OR specs = '{}'")->fetchColumn();
$allProds = $pdo->query("SELECT id, name, specs FROM products")->fetchAll();
$invalidJson = 0;
foreach ($allProds as $p) {
    if (!empty($p['specs'])) {
        json_decode($p['specs']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $invalidJson++;
        }
    }
}
echo "7. SPECS ISSUES: Empty/Default Specs: $emptySpecs | Invalid JSON Specs: $invalidJson\n\n";

// 8. Stock & Price issues
$negativeStock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 0")->fetchColumn();
$salePriceError = $pdo->query("SELECT COUNT(*) FROM products WHERE sale_price IS NOT NULL AND sale_price > price AND sale_price > 0")->fetchColumn();
echo "8. STOCK & PRICE ISSUES: Negative Stock: $negativeStock | Sale Price > Price: $salePriceError\n\n";

// 9. Rating & Sold quantity mismatches
if (!empty($ratingAvgCol)) {
    $stmt = $pdo->query("
        SELECT p.id, p.name, p.`$ratingAvgCol`,
               COALESCE(r.calc_avg, 0) calc_avg, COALESCE(r.calc_count, 0) calc_count
        FROM products p
        LEFT JOIN (
            SELECT product_id, AVG(rating) calc_avg, COUNT(*) calc_count
            FROM reviews WHERE status = 'approved' GROUP BY product_id
        ) r ON r.product_id = p.id
        WHERE ABS(COALESCE(p.`$ratingAvgCol`, 0) - COALESCE(r.calc_avg, 0)) > 0.01
    ");
    $ratingMismatches = $stmt->fetchAll();
    echo "9. RATING MISMATCHES vs REVIEWS: " . count($ratingMismatches) . " products\n\n";
} else {
    echo "9. RATING MISMATCHES: Column rating_avg not found in products table\n\n";
}

if (!empty($soldCol)) {
    $stmt = $pdo->query("
        SELECT p.id, p.name, p.`$soldCol`, COALESCE(oi.calc_sold, 0) calc_sold
        FROM products p
        LEFT JOIN (
            SELECT oi.product_id, SUM(oi.quantity) calc_sold
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            WHERE o.status = 'completed'
            GROUP BY oi.product_id
        ) oi ON oi.product_id = p.id
        WHERE COALESCE(p.`$soldCol`, 0) != COALESCE(oi.calc_sold, 0)
    ");
    $soldMismatches = $stmt->fetchAll();
    echo "10. SOLD QUANTITY MISMATCHES vs ORDERS: " . count($soldMismatches) . " products\n\n";
} else {
    echo "10. SOLD QUANTITY MISMATCHES: Column sold_count not found in products table\n\n";
}
