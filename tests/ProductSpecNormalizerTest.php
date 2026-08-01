<?php

$rootPath = dirname(__DIR__);
$passed = 0;
$failed = 0;

function assertNormalizer(bool $condition, string $message): void
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

echo "========================================================\n";
echo "=== TECHPILOT PRODUCT SPEC NORMALIZER TEST SUITE      ===\n";
echo "========================================================\n\n";

$flat = ProductSpecNormalizer::normalize('laptop', [
    'cpu_model' => 'Intel Core i7-14650HX',
    'ram_gb' => 32,
    'storage_gb' => 1000,
    'display_size_inch' => 16,
    'os' => 'Windows 11',
]);
assertNormalizer(($flat['attributes']['ram_capacity_gb'] ?? null) === 32, 'Map RAM Laptop phẳng sang canonical key');
assertNormalizer(($flat['attributes']['storage_capacity_gb'] ?? null) === 1000, 'Map SSD Laptop phẳng sang canonical key');
assertNormalizer(($flat['attributes']['screen_size_inch'] ?? null) === 16, 'Map kích thước màn hình Laptop phẳng');
assertNormalizer(($flat['attributes']['operating_system'] ?? null) === 'Windows 11', 'Map hệ điều hành Laptop phẳng');
assertNormalizer(!isset($flat['attributes']['ram_gb']), 'Không giữ alias RAM gây hiển thị trùng');

$nested = ProductSpecNormalizer::normalize('laptop', [
    'schema_version' => 1,
    'category_slug' => 'laptop',
    'specs' => [
        'cpu_model' => 'AMD Ryzen 7 8845HS',
        'ram_gb' => 16,
        'storage_gb' => 512,
        'display_size_inch' => 15.6,
    ],
    'compatibility' => ['m2_slots' => 2],
    'use_cases' => ['gaming', 'office'],
]);
assertNormalizer(($nested['attributes']['ram_capacity_gb'] ?? null) === 16, 'Unwrap specs và map alias');
assertNormalizer(($nested['compatibility']['m2_slots'] ?? null) === 2, 'Giữ compatibility ngoài attributes');
assertNormalizer(($nested['use_cases'] ?? []) === ['gaming', 'office'], 'Giữ use_cases ngoài attributes');
assertNormalizer(!isset($nested['attributes']['compatibility']), 'Không rò metadata vào bảng thông số');

$v2 = ProductSpecNormalizer::normalize('laptop', [
    'schema_version' => 2,
    'attributes' => [
        'ram_capacity_gb' => 64,
        'storage_capacity_gb' => 2000,
        'screen_size_inch' => 16,
    ],
]);
assertNormalizer(($v2['attributes']['ram_capacity_gb'] ?? null) === 64, 'Giữ nguyên canonical key của schema v2');
assertNormalizer(($v2['schema_version'] ?? null) === 2, 'Output luôn là schema version 2');

$zeroAndFalse = ProductSpecNormalizer::normalize('cpu', [
    'specs' => [
        'integrated_graphics' => false,
        'cores' => 0,
    ],
]);
assertNormalizer(array_key_exists('integrated_graphics', $zeroAndFalse['attributes']), 'Không làm mất boolean false');
assertNormalizer(array_key_exists('cores', $zeroAndFalse['attributes']), 'Không làm mất số 0');

$resolvedLaptop = ProductFacetService::resolveFacetCategory('laptop-gaming');
assertNormalizer($resolvedLaptop === 'laptop', 'Alias một nguồn dùng facet của category vật lý');
assertNormalizer(ProductFacetService::canonicalCategorySlug('laptop-gaming') === 'laptop', 'Alias Gaming sinh URL category Laptop chuẩn');
assertNormalizer(ProductFacetService::canonicalCategorySlug('laptop-van-phong') === 'laptop', 'Alias Văn phòng sinh URL category Laptop chuẩn');
assertNormalizer(ProductFacetService::resolveFacetCategory('pc-linh-kien') === null, 'Nhóm nhiều category không nhận nhầm facet kỹ thuật');

$gamingAliasFilters = ProductFacetService::normalizeFilters('laptop-gaming', []);
$officeAliasFilters = ProductFacetService::normalizeFilters('laptop-van-phong', []);
assertNormalizer(($gamingAliasFilters['gpu'] ?? '') === 'dedicated', 'Alias Laptop Gaming mặc định lọc GPU rời');
assertNormalizer(($officeAliasFilters['gpu'] ?? '') === 'integrated', 'Alias Laptop Văn phòng mặc định lọc đồ họa tích hợp');
assertNormalizer(
    ProductFacetService::normalizeFilters('laptop-gaming', ['gpu' => 'integrated']) === ['gpu' => 'integrated'],
    'Facet hợp lệ do người dùng chọn được ưu tiên hơn default của alias'
);

$validFilters = ProductFacetService::normalizeFilters('laptop', [
    'cpu_family' => 'core-i7',
    'ram_min' => '16',
    'unknown' => 'value',
    'gpu' => 'invalid-option',
]);
assertNormalizer($validFilters === ['cpu_family' => 'core-i7', 'ram_min' => '16'], 'Chỉ nhận filter và option nằm trong allowlist');

echo "\n========================================================\n";
echo "Product Spec Normalizer Results: {$passed} passed, {$failed} failed\n";
echo "========================================================\n";

exit($failed === 0 ? 0 : 1);
