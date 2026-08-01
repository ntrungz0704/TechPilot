<?php

$rootPath = dirname(__DIR__);
$passed = 0;
$failed = 0;

function assertFacetContract(bool $condition, string $message): void
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

$config = require $rootPath . '/config/product-facets.php';
$allowedTypes = ['text', 'number', 'boolean', 'array'];
$allowedOperators = ['contains', 'equals', 'gte', 'lte', 'range', 'array_contains', 'present'];

echo "========================================================\n";
echo "=== TECHPILOT PRODUCT FACET CONTRACT TEST SUITE      ===\n";
echo "========================================================\n\n";

assertFacetContract(isset($config['common']['price_ranges']), 'Có contract khoảng giá dùng chung');
assertFacetContract(isset($config['categories']['laptop']), 'Laptop là danh mục pilot của facet engine');

foreach ($config['category_aliases'] ?? [] as $alias => $aliasDefinition) {
    $targetCategory = (string)($aliasDefinition['category'] ?? '');
    assertFacetContract(isset($config['categories'][$targetCategory]), "Alias {$alias} trỏ tới category có facet");

    foreach ($aliasDefinition['defaults'] ?? [] as $parameter => $value) {
        assertFacetContract(
            isset($config['categories'][$targetCategory][$parameter]['options'][$value]),
            "Alias {$alias} dùng default {$parameter}={$value} hợp lệ"
        );
    }
}

$previousMax = null;
foreach ($config['common']['price_ranges'] ?? [] as $key => $range) {
    $min = (int)($range['min_price'] ?? 0);
    $max = (int)($range['max_price'] ?? 0);
    assertFacetContract(trim((string)($range['label'] ?? '')) !== '', "Khoảng giá {$key} có label");
    assertFacetContract($max === 0 || $max >= $min, "Khoảng giá {$key} có biên hợp lệ");
    if ($previousMax !== null) {
        assertFacetContract($min === $previousMax + 1, "Khoảng giá {$key} nối tiếp khoảng trước, không chồng lấn");
    }
    $previousMax = $max > 0 ? $max : null;
}

$facetParams = [];
foreach ($config['categories'] ?? [] as $category => $facets) {
    foreach ($facets as $param => $definition) {
        $contractId = "{$category}.{$param}";
        assertFacetContract(preg_match('/^[a-z][a-z0-9_]*$/', $param) === 1, "{$contractId} có query parameter ổn định");
        assertFacetContract(!isset($facetParams[$param]) || $facetParams[$param] === $definition, "{$contractId} không xung đột contract");
        $facetParams[$param] = $definition;

        assertFacetContract(in_array($definition['type'] ?? '', $allowedTypes, true), "{$contractId} có type được hỗ trợ");
        assertFacetContract(in_array($definition['operator'] ?? '', $allowedOperators, true), "{$contractId} có operator được hỗ trợ");
        assertFacetContract(!empty($definition['keys']) && is_array($definition['keys']), "{$contractId} khai báo spec key");
        assertFacetContract(!empty($definition['options']) && is_array($definition['options']), "{$contractId} có allowlist option");

        foreach ($definition['options'] ?? [] as $value => $option) {
            assertFacetContract((string)$value !== '', "{$contractId} không có option rỗng");
            assertFacetContract(trim((string)($option['label'] ?? '')) !== '', "{$contractId} option {$value} có label");
        }
    }
}

assertFacetContract(isset($facetParams['cpu_family']), 'Contract dùng cpu_family thay cho tham số cpu mơ hồ');
assertFacetContract(isset($facetParams['ram_min']), 'Contract RAM dùng giá trị số ram_min');
assertFacetContract(isset($facetParams['storage_min']), 'Contract SSD dùng giá trị số storage_min');

echo "\n========================================================\n";
echo "Product Facet Contract Results: {$passed} passed, {$failed} failed\n";
echo "========================================================\n";

exit($failed === 0 ? 0 : 1);
