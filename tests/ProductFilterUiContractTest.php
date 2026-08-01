<?php

$rootPath = dirname(__DIR__);
$passed = 0;
$failed = 0;

function assertFilterUi(bool $condition, string $message): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "[PASS] {$message}\n";
        return;
    }

    $failed++;
    echo "[FAIL] {$message}\n";
}

require_once $rootPath . '/config/app.php';
require_once $rootPath . '/app/core/helpers.php';

$pageTitle = 'Laptop';
$keyword = 'gaming';
$categories = [
    ['slug' => 'laptop', 'name' => 'Laptop'],
    ['slug' => 'cpu', 'name' => 'CPU'],
];
$categorySlug = 'laptop';
$sort = 'price-low';
$minPrice = 5000001;
$maxPrice = 10000000;
$totalResults = 0;
$products = [];
$brandSlug = 'asus';
$inStockOnly = true;
$promoOnly = false;
$activeBrands = [['slug' => 'asus', 'name' => 'ASUS']];
$facetDefinitions = ProductFacetService::getFacetDefinitions('laptop');
$facetFilters = ['ram_min' => '32', 'gpu' => 'rtx-4060'];
$priceRanges = ProductFacetService::getPriceRanges();
$subgroups = [];
$page = 1;
$limit = 24;
$isStopwordQuery = false;
$searchError = false;

ob_start();
require $rootPath . '/app/views/home/search.php';
$html = (string)ob_get_clean();

echo "========================================================\n";
echo "=== TECHPILOT PRODUCT FILTER UI CONTRACT TEST SUITE  ===\n";
echo "========================================================\n\n";

assertFilterUi(str_contains($html, 'name="brand" value="asus"'), 'Form tìm kiếm giữ brand đang chọn');
assertFilterUi(str_contains($html, 'name="stock" value="1"'), 'Form tìm kiếm giữ stock toggle');
assertFilterUi(str_contains($html, 'name="sort" value="price-low"'), 'Form tìm kiếm giữ sort');
assertFilterUi(str_contains($html, 'name="ram_min" value="32"'), 'Form tìm kiếm giữ facet RAM');
assertFilterUi(str_contains($html, 'name="gpu" value="rtx-4060"'), 'Form tìm kiếm giữ facet GPU');
assertFilterUi(str_contains($html, 'data-price-min="5000001"') && str_contains($html, 'data-price-max="10000000"'), 'Khoảng 5-10 triệu gửi đủ hai biên');
assertFilterUi(str_contains($html, 'Từ 5 - 10 triệu'), 'UI nhận diện đúng khoảng giá đang active');
assertFilterUi(str_contains($html, 'RAM tối thiểu: Từ 32 GB'), 'UI hiển thị active chip RAM theo label allowlist');
assertFilterUi(str_contains($html, 'Card đồ họa: NVIDIA RTX 4060'), 'UI hiển thị active chip GPU theo label allowlist');
assertFilterUi(!str_contains($html, 'max_price=0'), 'URL mặc định không phát sinh max_price=0');

$js = file_get_contents($rootPath . '/public/assets/js/search-filters.js');
assertFilterUi(str_contains($js, 'function applyPriceRange'), 'JavaScript có handler khoảng giá hai biên');
assertFilterUi(str_contains($js, "url.searchParams.delete('price')"), 'JavaScript xóa tham số price legacy khi chọn khoảng mới');
assertFilterUi(str_contains($js, "url.searchParams.delete('page')"), 'Thay filter reset về trang 1');

$controller = file_get_contents($rootPath . '/app/controllers/HomeController.php');
assertFilterUi(substr_count($controller, '$facetFilters') >= 4, 'Controller truyền cùng facet state vào count, search và view');
assertFilterUi(!str_contains($html, 'data-filter-key="cpu"'), 'UI không còn render tham số CPU legacy mơ hồ');

$homeView = file_get_contents($rootPath . '/app/views/home/index.php');
assertFilterUi(str_contains($homeView, 'cat=laptop&gpu=dedicated'), 'Section Laptop Gaming dùng facet GPU rời thay cho alias giả');
assertFilterUi(str_contains($homeView, 'cat=laptop&gpu=integrated'), 'Section Laptop Văn phòng dùng facet đồ họa tích hợp');
assertFilterUi(!str_contains($homeView, 'cat=laptop-gaming'), 'Trang chủ không còn phát sinh URL alias Laptop Gaming');
assertFilterUi(!str_contains($homeView, 'cat=laptop-van-phong'), 'Trang chủ không còn phát sinh URL alias Laptop Văn phòng');

$menuTree = CategoryMenuService::getActiveMenuTree();
$gamingMenu = null;
foreach ($menuTree as $menuItem) {
    if (($menuItem['id'] ?? '') === 'laptop-gaming') {
        $gamingMenu = $menuItem;
        break;
    }
}
assertFilterUi(($gamingMenu['slug'] ?? '') === 'laptop', 'Mega menu Gaming giữ category Laptop chuẩn');
assertFilterUi(($gamingMenu['query'] ?? '') === 'gpu=dedicated', 'Mega menu Gaming luôn kèm facet GPU rời');

$globalCategoryMenu = $menuTree;
$isStatic = false;
ob_start();
require $rootPath . '/app/views/layouts/partials/category-mega-menu.php';
$menuHtml = (string)ob_get_clean();
assertFilterUi(str_contains($menuHtml, 'cat=laptop&gpu=dedicated'), 'Mega menu render facet Gaming vào URL thật');
preg_match_all('/\sid="panel-laptop"/', $menuHtml, $laptopPanelIds);
preg_match_all('/\sid="panel-laptop-gaming"/', $menuHtml, $gamingPanelIds);
assertFilterUi(count($laptopPanelIds[0] ?? []) === 1, 'Panel Laptop thường có DOM id duy nhất');
assertFilterUi(count($gamingPanelIds[0] ?? []) === 1, 'Panel Laptop Gaming có DOM id riêng');

foreach ($menuTree as $menuItem) {
    foreach ($menuItem['mega_columns'] ?? [] as $column => $subitems) {
        $previousPriceMax = null;
        foreach ($subitems as $subitem) {
            $query = is_array($subitem) ? (string)($subitem['query'] ?? '') : '';
            if (!str_contains($query, 'min_price=') && !str_contains($query, 'max_price=')) {
                continue;
            }

            parse_str($query, $queryParams);
            $currentMin = isset($queryParams['min_price']) ? (int)$queryParams['min_price'] : 0;
            $currentMax = isset($queryParams['max_price']) ? (int)$queryParams['max_price'] : 0;
            if ($previousPriceMax !== null) {
                assertFilterUi(
                    $currentMin === $previousPriceMax + 1,
                    "Khoảng giá {$menuItem['id']}.{$column} nối tiếp và không chồng lấn"
                );
            }
            $previousPriceMax = $currentMax > 0 ? $currentMax : null;
        }
    }
}

echo "\n========================================================\n";
echo "Product Filter UI Results: {$passed} passed, {$failed} failed\n";
echo "========================================================\n";

exit($failed === 0 ? 0 : 1);
