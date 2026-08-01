<?php

/**
 * Read-only benchmark for the storefront search/facet query plan.
 *
 * Usage: php tests/ProductFacetBenchmark.php [iterations]
 */

$rootPath = dirname(__DIR__);
require_once $rootPath . '/config/app.php';
require_once $rootPath . '/config/database.php';

$iterations = max(5, min(500, (int)($argv[1] ?? 50)));
$warmups = 3;
$db = Database::getConnection();

if (!$db instanceof PDO) {
    fwrite(STDERR, "Không thể kết nối database để benchmark.\n");
    exit(1);
}

function benchmarkQuery(callable $query, int $warmups, int $iterations): array
{
    for ($i = 0; $i < $warmups; $i++) {
        $query();
    }

    $durations = [];
    for ($i = 0; $i < $iterations; $i++) {
        $startedAt = hrtime(true);
        $query();
        $durations[] = (hrtime(true) - $startedAt) / 1_000_000;
    }

    sort($durations, SORT_NUMERIC);
    $percentile = static function (float $ratio) use ($durations): float {
        $index = (int)ceil(count($durations) * $ratio) - 1;
        return $durations[max(0, min(count($durations) - 1, $index))];
    };

    return [
        'p50' => $percentile(0.50),
        'p95' => $percentile(0.95),
        'max' => max($durations),
    ];
}

function explainCountQuery(PDO $db, Product $model, array $scenario): array
{
    $plan = $model->getSearchPlan(
        $scenario['keyword'],
        $scenario['category'],
        '',
        0,
        0,
        false,
        false,
        $scenario['facets']
    );

    $sql = 'EXPLAIN SELECT COUNT(DISTINCT p.id)'
        . ' FROM products p'
        . ' LEFT JOIN brands b ON p.brand_id = b.id'
        . ' LEFT JOIN categories c ON p.category_id = c.id'
        . ' WHERE ' . implode(' AND ', $plan['conditions']);

    $stmt = $db->prepare($sql);
    foreach ($plan['params'] as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

$model = new Product();
$scenarios = [
    'category_laptop' => [
        'keyword' => '',
        'category' => 'laptop',
        'facets' => [],
        'sort' => 'newest',
    ],
    'laptop_one_facet' => [
        'keyword' => '',
        'category' => 'laptop',
        'facets' => ['gpu' => 'dedicated'],
        'sort' => 'newest',
    ],
    'laptop_three_facets' => [
        'keyword' => '',
        'category' => 'laptop',
        'facets' => ['ram_min' => '32', 'gpu' => 'rtx-4060', 'refresh_min' => '144'],
        'sort' => 'newest',
    ],
    'pc_three_facets' => [
        'keyword' => '',
        'category' => 'pc',
        'facets' => ['pc_cpu_family' => 'core-i5', 'pc_ram_min' => '16', 'pc_gpu' => 'rtx-4060'],
        'sort' => 'newest',
    ],
    'keyword_rtx_4060' => [
        'keyword' => 'RTX 4060',
        'category' => '',
        'facets' => [],
        'sort' => 'relevance',
    ],
];

$databaseVersion = (string)$db->query('SELECT VERSION()')->fetchColumn();
$totalProducts = (int)$db->query('SELECT COUNT(*) FROM products')->fetchColumn();
$activeVerified = (int)$db->query(
    "SELECT COUNT(*) FROM products WHERE status = 'active' AND verification_status = 'verified'"
)->fetchColumn();

echo "============================================================\n";
echo "=== TECHPILOT PRODUCT FACET READ-ONLY BENCHMARK          ===\n";
echo "============================================================\n";
echo "Database: {$databaseVersion}\n";
echo "Products: {$totalProducts} total / {$activeVerified} active+verified\n";
echo "Iterations: {$iterations} (+ {$warmups} warmups)\n\n";
echo str_pad('Scenario', 26)
    . str_pad('Rows', 8, ' ', STR_PAD_LEFT)
    . str_pad('Count p50', 13, ' ', STR_PAD_LEFT)
    . str_pad('Count p95', 13, ' ', STR_PAD_LEFT)
    . str_pad('List p50', 13, ' ', STR_PAD_LEFT)
    . str_pad('List p95', 13, ' ', STR_PAD_LEFT)
    . "\n";

foreach ($scenarios as $name => $scenario) {
    $countResult = null;
    $listResult = null;

    $countStats = benchmarkQuery(function () use ($model, $scenario, &$countResult): void {
        $countResult = $model->countSearch(
            $scenario['keyword'],
            $scenario['category'],
            '',
            0,
            0,
            false,
            false,
            $scenario['facets']
        );
    }, $warmups, $iterations);

    $listStats = benchmarkQuery(function () use ($model, $scenario, &$listResult): void {
        $listResult = $model->search(
            $scenario['keyword'],
            $scenario['category'],
            24,
            0,
            '',
            0,
            0,
            $scenario['sort'],
            false,
            false,
            $scenario['facets']
        );
    }, $warmups, $iterations);

    if (!is_array($listResult)) {
        fwrite(STDERR, "Search benchmark failed for {$name}.\n");
        exit(1);
    }

    printf(
        "%-26s%8d%11.3f ms%11.3f ms%11.3f ms%11.3f ms\n",
        $name,
        (int)$countResult,
        $countStats['p50'],
        $countStats['p95'],
        $listStats['p50'],
        $listStats['p95']
    );
}

echo "\nEXPLAIN count query (table | type | key | estimated rows | extra)\n";
foreach (['category_laptop', 'laptop_three_facets', 'keyword_rtx_4060'] as $scenarioName) {
    echo "[{$scenarioName}]\n";
    foreach (explainCountQuery($db, $model, $scenarios[$scenarioName]) as $row) {
        echo implode(' | ', [
            (string)($row['table'] ?? ''),
            (string)($row['type'] ?? ''),
            (string)($row['key'] ?? 'NULL'),
            (string)($row['rows'] ?? ''),
            (string)($row['Extra'] ?? ''),
        ]) . "\n";
    }
}

echo "\nBenchmark chỉ đọc; không tạo index, migration hoặc ghi dữ liệu.\n";
