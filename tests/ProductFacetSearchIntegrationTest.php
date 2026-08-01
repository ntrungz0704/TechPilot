<?php

$rootPath = dirname(__DIR__);
$passed = 0;
$failed = 0;

function assertFacetSearch(bool $condition, string $message): void
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

function normalizedAttributes(array $row): array
{
    return ProductSpecNormalizer::normalize(
        (string)($row['category_slug'] ?? ''),
        (string)($row['specs'] ?? '{}')
    )['attributes'] ?? [];
}

require_once $rootPath . '/config/app.php';

echo "========================================================\n";
echo "=== TECHPILOT PRODUCT FACET SEARCH INTEGRATION TEST  ===\n";
echo "========================================================\n\n";

$model = new Product();
$baseCount = $model->countSearch('', 'laptop');
assertFacetSearch($baseCount > 0, 'Có dữ liệu Laptop để kiểm tra facet');

$ramFilters = ['ram_min' => '32'];
$ramCount = $model->countSearch('', 'laptop', '', 0, 0, false, false, $ramFilters);
$ramRows = $model->search('', 'laptop', 1000, 0, '', 0, 0, 'newest', false, false, $ramFilters);
assertFacetSearch(is_array($ramRows), 'Query facet RAM chạy thành công');
assertFacetSearch($ramCount > 0 && $ramCount < $baseCount, 'RAM từ 32GB thu hẹp tập Laptop');
assertFacetSearch($ramCount === count($ramRows), 'search và countSearch dùng cùng điều kiện RAM');

$allRamValid = true;
foreach ($ramRows ?: [] as $row) {
    $attrs = normalizedAttributes($row);
    $allRamValid = $allRamValid && (float)($attrs['ram_capacity_gb'] ?? 0) >= 32;
}
assertFacetSearch($allRamValid, 'Mọi Laptop trả về đều có RAM tối thiểu 32GB');

$gpuFilters = ['gpu' => 'rtx-4060'];
$gpuCount = $model->countSearch('', 'laptop', '', 0, 0, false, false, $gpuFilters);
$gpuRows = $model->search('', 'laptop', 1000, 0, '', 0, 0, 'newest', false, false, $gpuFilters);
assertFacetSearch($gpuCount > 0 && $gpuCount < $baseCount, 'RTX 4060 thu hẹp tập Laptop');
assertFacetSearch($gpuCount === count($gpuRows ?: []), 'search và countSearch dùng cùng điều kiện GPU');

$allGpuValid = true;
foreach ($gpuRows ?: [] as $row) {
    $attrs = normalizedAttributes($row);
    $allGpuValid = $allGpuValid && str_contains(mb_strtolower((string)($attrs['gpu_model'] ?? ''), 'UTF-8'), 'rtx 4060');
}
assertFacetSearch($allGpuValid, 'Mọi Laptop trả về đều có RTX 4060');

$combined = ['ram_min' => '32', 'gpu' => 'rtx-4060'];
$combinedCount = $model->countSearch('', 'laptop', '', 0, 0, false, false, $combined);
assertFacetSearch($combinedCount <= $ramCount && $combinedCount <= $gpuCount, 'Kết hợp facet dùng AND giữa RAM và GPU');

$invalidCount = $model->countSearch('', 'laptop', '', 0, 0, false, false, [
    'ram_min' => '999999',
    'unknown' => 'anything',
]);
assertFacetSearch($invalidCount === $baseCount, 'Option ngoài allowlist không làm thay đổi query');

$gamingAliasCount = $model->countSearch('', 'laptop-gaming');
$officeAliasCount = $model->countSearch('', 'laptop-van-phong');
$gamingAliasRows = $model->search('', 'laptop-gaming', 1000) ?: [];
$officeAliasRows = $model->search('', 'laptop-van-phong', 1000) ?: [];
$gamingAliasIds = array_column($gamingAliasRows, 'id');
$officeAliasIds = array_column($officeAliasRows, 'id');
assertFacetSearch($gamingAliasCount > 0, 'Alias Laptop Gaming trả về tập GPU rời có dữ liệu');
assertFacetSearch($officeAliasCount > 0, 'Alias Laptop Văn phòng trả về tập đồ họa tích hợp có dữ liệu');
assertFacetSearch(array_intersect($gamingAliasIds, $officeAliasIds) === [], 'Hai alias Laptop không còn trả trùng sản phẩm');
assertFacetSearch($gamingAliasCount + $officeAliasCount === $baseCount, 'Hai alias Laptop tạo hai tập riêng và phủ đúng toàn bộ dữ liệu hiện tại');

$gamingRamCount = $model->countSearch('', 'laptop-gaming', '', 0, 0, false, false, $ramFilters);
assertFacetSearch($gamingRamCount > 0 && $gamingRamCount <= $ramCount, 'Alias Gaming vẫn kết hợp AND được với facet RAM');

$plan = $model->getSearchPlan('', 'laptop', '', 0, 0, false, false, $combined);
$planSql = implode(' ', $plan['conditions'] ?? []);
assertFacetSearch(str_contains($planSql, 'JSON_VALID(p.specs)'), 'Search plan bảo vệ JSON_EXTRACT bằng JSON_VALID');
assertFacetSearch(str_contains($planSql, '$.attributes.ram_capacity_gb'), 'Search plan hỗ trợ schema attributes canonical');
assertFacetSearch(str_contains($planSql, '$.specs.ram_gb'), 'Search plan hỗ trợ legacy nested specs');
assertFacetSearch(str_contains($planSql, '$.ram_gb'), 'Search plan hỗ trợ legacy JSON phẳng');

$facetService = new ProductFacetService();
$facetConfig = require $rootPath . '/config/product-facets.php';
foreach (array_keys($facetConfig['categories'] ?? []) as $categorySlug) {
    $categoryCount = $model->countSearch('', $categorySlug);
    assertFacetSearch($categoryCount > 0, "Danh mục {$categorySlug} có dữ liệu để kiểm tra facet");

    foreach ($facetService->getFacetDefinitions($categorySlug) as $parameter => $definition) {
        foreach (array_keys($definition['options'] ?? []) as $optionValue) {
            $filters = [$parameter => (string)$optionValue];
            $optionCount = $model->countSearch('', $categorySlug, '', 0, 0, false, false, $filters);
            $optionRows = $model->search(
                '',
                $categorySlug,
                1000,
                0,
                '',
                0,
                0,
                'newest',
                false,
                false,
                $filters
            );

            assertFacetSearch(
                $optionCount > 0,
                "{$categorySlug}.{$parameter}={$optionValue} trả về ít nhất một sản phẩm"
            );
            assertFacetSearch(
                $optionCount === count($optionRows ?: []),
                "{$categorySlug}.{$parameter}={$optionValue} đồng bộ search/countSearch"
            );
        }
    }
}

echo "\n========================================================\n";
echo "Product Facet Search Results: {$passed} passed, {$failed} failed\n";
echo "========================================================\n";

exit($failed === 0 ? 0 : 1);
