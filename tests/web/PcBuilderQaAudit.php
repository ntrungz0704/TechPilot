<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/app/services/PcCompatibilityService.php';

$db = Database::getConnection();
if (!$db instanceof PDO) {
    fwrite(STDERR, "Không kết nối được database local.\n");
    exit(2);
}

$parts = [
    'cpu' => ['ids' => [5], 'required' => ['socket', 'cpu_power_w']],
    'mainboard' => ['ids' => [4], 'required' => ['socket', 'memory_type', 'ram_slots', 'max_memory_gb', 'form_factor']],
    'ram' => ['ids' => [7], 'required' => ['memory_type', 'capacity_gb', 'ram_module_count']],
    'vga' => ['ids' => [6], 'required' => ['length_mm', 'gpu_power_w', 'gpu_rec_psu_w']],
    'storage' => ['ids' => [8], 'required' => []],
    'psu' => ['ids' => [11], 'required' => ['psu_wattage_w']],
    'case' => ['ids' => [9], 'required' => ['case_form_factors', 'max_gpu_length_mm', 'max_cpu_cooler_height_mm']],
    'cooler' => ['ids' => [10], 'required' => ['supported_sockets', 'cooling_type_name']],
    'monitor' => ['ids' => [3], 'required' => []],
    'gear' => ['ids' => [12, 13], 'required' => []],
];

$categoryRows = $db->query(
    "SELECT c.id, c.slug, c.name,
            COUNT(p.id) AS total_products,
            SUM(p.status = 'active') AS active_products
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id, c.slug, c.name
     ORDER BY c.id"
)->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(
    ['categories' => $categoryRows],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) . PHP_EOL;

$eligibleByPart = [];

foreach ($parts as $part => $definition) {
    $ids = implode(',', array_map('intval', $definition['ids']));
    $stmt = $db->query(
        "SELECT id, name, price, sale_price, stock, status,
                verification_status, specs, component_type,
                power_draw_w, recommended_psu_w
         FROM products
         WHERE category_id IN ({$ids})
           AND status = 'active'"
    );
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $eligible = array_values(array_filter(
        $rows,
        static fn(array $row): bool => (int)$row['stock'] > 0
    ));
    $unverifiedEligible = array_values(array_filter(
        $eligible,
        static fn(array $row): bool => ($row['verification_status'] ?? '') !== 'verified'
    ));
    $saleEligible = array_values(array_filter(
        $eligible,
        static fn(array $row): bool =>
            (float)($row['sale_price'] ?? 0) > 0
            && (float)$row['sale_price'] < (float)$row['price']
    ));
    $eligibleByPart[$part] = $eligible;

    $missing = array_fill_keys($definition['required'], 0);
    $flattenForBuilder = static function (array $row): array {
        $parsed = json_decode((string)($row['specs'] ?? ''), true) ?: [];
        if (!empty($row['component_type'])) {
            $parsed['component_type'] = $row['component_type'];
        }
        if (!empty($row['power_draw_w'])) {
            $parsed['power_draw_w'] = $row['power_draw_w'];
        }
        if (!empty($row['recommended_psu_w'])) {
            $parsed['recommended_psu_w'] = $row['recommended_psu_w'];
        }
        return PcCompatibilityService::flattenSpecs($parsed);
    };

    foreach ($eligible as $row) {
        $flat = $flattenForBuilder($row);
        foreach ($definition['required'] as $key) {
            $value = $flat[$key] ?? null;
            if ($value === null || $value === '' || $value === []) {
                $missing[$key]++;
            }
        }
    }

    echo json_encode([
        'part' => $part,
        'active' => count($rows),
        'eligible_by_current_query' => count($eligible),
        'unverified_but_eligible' => count($unverifiedEligible),
        'sale_price_ignored' => count($saleEligible),
        'missing_compatibility_fields' => $missing,
        'sample_flat_keys' => isset($eligible[0])
            ? array_keys($flattenForBuilder($eligible[0]))
            : [],
        'sample_nested_spec_keys' => isset($eligible[0])
            ? array_keys((json_decode((string)$eligible[0]['specs'], true)['specs'] ?? []))
            : [],
        'sample_compatibility_keys' => isset($eligible[0])
            ? array_keys((json_decode((string)$eligible[0]['specs'], true)['compatibility'] ?? []))
            : [],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

$prebuilt = $db->query(
    "SELECT
        COUNT(*) AS active,
        SUM(stock <= 0) AS out_of_stock,
        SUM(verification_status <> 'verified') AS unverified,
        SUM(sale_price IS NOT NULL AND sale_price > 0 AND sale_price < price) AS sale_price_ignored
     FROM products
     WHERE category_id = 2
       AND status = 'active'"
)->fetch(PDO::FETCH_ASSOC);

echo json_encode(
    ['prebuilt_current_query' => $prebuilt],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) . PHP_EOL;

$toBuildProduct = static function (array $row): array {
    $parsed = json_decode((string)($row['specs'] ?? ''), true) ?: [];
    if (!empty($row['component_type'])) {
        $parsed['component_type'] = $row['component_type'];
    }
    if (!empty($row['power_draw_w'])) {
        $parsed['power_draw_w'] = $row['power_draw_w'];
    }
    if (!empty($row['recommended_psu_w'])) {
        $parsed['recommended_psu_w'] = $row['recommended_psu_w'];
    }
    return [
        'id' => (int)$row['id'],
        'name' => (string)$row['name'],
        'price' => (float)$row['price'],
        'specs' => $parsed,
    ];
};

$pickByNestedNumber = static function (
    array $rows,
    string $section,
    string $key,
    bool $highest
): ?array {
    $picked = null;
    $pickedValue = $highest ? -INF : INF;
    foreach ($rows as $row) {
        $parsed = json_decode((string)($row['specs'] ?? ''), true) ?: [];
        $value = (float)($parsed[$section][$key] ?? 0);
        if ($value <= 0) {
            continue;
        }
        if (($highest && $value > $pickedValue) || (!$highest && $value < $pickedValue)) {
            $picked = $row;
            $pickedValue = $value;
        }
    }
    return $picked;
};

$highCpu = $pickByNestedNumber(
    $eligibleByPart['cpu'] ?? [],
    'compatibility',
    'max_power_w',
    true
);
$highGpu = $pickByNestedNumber($eligibleByPart['vga'] ?? [], 'specs', 'tdp_w', true);
$lowPsu = $pickByNestedNumber($eligibleByPart['psu'] ?? [], 'specs', 'wattage', false);

if ($highCpu && $highGpu && $lowPsu) {
    $powerBuild = [
        'cpu' => $toBuildProduct($highCpu),
        'vga' => $toBuildProduct($highGpu),
        'psu' => $toBuildProduct($lowPsu),
    ];
    $power = PcCompatibilityService::calculatePowerRequirements($powerBuild);
    $psuCheck = PcCompatibilityService::checkCompatibility(
        $powerBuild,
        $powerBuild['psu'],
        'psu'
    );
    $cpuRaw = json_decode((string)$highCpu['specs'], true) ?: [];
    $gpuRaw = json_decode((string)$highGpu['specs'], true) ?: [];
    $psuRaw = json_decode((string)$lowPsu['specs'], true) ?: [];

    echo json_encode([
        'power_case' => [
            'cpu' => $highCpu['name'],
            'raw_cpu_max_w' => (float)($cpuRaw['compatibility']['max_power_w'] ?? 0),
            'gpu' => $highGpu['name'],
            'raw_gpu_tdp_w' => (float)($gpuRaw['specs']['tdp_w'] ?? 0),
            'psu' => $lowPsu['name'],
            'raw_psu_w' => (float)($psuRaw['specs']['wattage'] ?? 0),
            'service_result' => $power,
            'service_psu_blockers' => $psuCheck['blockers'],
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

$cpuHeuristicSamples = [];
$stockCoolerMismatches = [];
foreach (array_slice($eligibleByPart['cpu'] ?? [], 0, 12) as $cpuRow) {
    $raw = json_decode((string)($cpuRow['specs'] ?? ''), true) ?: [];
    $nested = $raw['specs'] ?? [];
    $builderProduct = $toBuildProduct($cpuRow);
    $cpuHeuristicSamples[] = [
        'name' => $cpuRow['name'],
        'raw_integrated_gpu' => $nested['integrated_gpu'] ?? null,
        'service_has_igpu' => PcCompatibilityService::hasIntegratedGraphics(
            (string)$cpuRow['name'],
            $builderProduct['specs']
        ),
        'raw_cooler_included' => $nested['cooler_included'] ?? null,
        'service_has_stock_cooler' => PcCompatibilityService::hasStockCooler(
            (string)$cpuRow['name'],
            $builderProduct['specs']
        ),
    ];
}

foreach ($eligibleByPart['cpu'] ?? [] as $cpuRow) {
    $raw = json_decode((string)($cpuRow['specs'] ?? ''), true) ?: [];
    $nested = $raw['specs'] ?? [];
    if (!array_key_exists('cooler_included', $nested)) {
        continue;
    }
    $builderProduct = $toBuildProduct($cpuRow);
    $serviceValue = PcCompatibilityService::hasStockCooler(
        (string)$cpuRow['name'],
        $builderProduct['specs']
    );
    if ((bool)$nested['cooler_included'] !== $serviceValue) {
        $stockCoolerMismatches[] = [
            'name' => $cpuRow['name'],
            'raw' => (bool)$nested['cooler_included'],
            'service' => $serviceValue,
        ];
    }
}

echo json_encode(
    [
        'cpu_heuristic_samples' => $cpuHeuristicSamples,
        'stock_cooler_mismatch_count' => count($stockCoolerMismatches),
        'stock_cooler_mismatch_examples' => array_slice($stockCoolerMismatches, 0, 3),
    ],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
) . PHP_EOL;
