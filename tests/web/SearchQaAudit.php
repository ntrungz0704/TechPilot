<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/config/database.php';

$db = Database::getConnection();
if (!$db instanceof PDO) {
    fwrite(STDERR, "Không kết nối được database local.\n");
    exit(2);
}

$model = new Product();
$sample = $db->query(
    "SELECT sku, name
     FROM products
     WHERE status = 'active'
       AND verification_status = 'verified'
       AND sku IS NOT NULL
       AND sku <> ''
     ORDER BY id
     LIMIT 1"
)->fetch(PDO::FETCH_ASSOC);

if (!$sample) {
    fwrite(STDERR, "Không có sản phẩm mẫu có SKU để kiểm tra.\n");
    exit(2);
}

$total = $model->countSearch();
$cases = [
    'category_alias' => 'laptop',
    'brand' => 'asus',
    'spec' => 'RTX 4060',
    'printer_phrase' => 'máy in',
    'unaccented' => 'may in',
    'documented_synonym' => 'notebook',
    'typo' => 'laptpo',
    'percent_wildcard' => '%',
    'underscore_wildcard' => '_',
    'exact_sku' => (string)$sample['sku'],
    'exact_name' => (string)$sample['name'],
];

echo json_encode([
    'total_active_verified' => $total,
    'sample' => $sample,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

foreach ($cases as $id => $keyword) {
    $count = $model->countSearch($keyword);
    $rows = $model->search($keyword, '', 3);
    $top = array_map(
        static fn(array $row): array => [
            'sku' => (string)($row['sku'] ?? ''),
            'name' => (string)($row['name'] ?? ''),
            'search_score' => (int)($row['search_score'] ?? 0),
        ],
        is_array($rows) ? $rows : []
    );

    echo json_encode([
        'id' => $id,
        'keyword' => $keyword,
        'count' => $count,
        'top' => $top,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

$nameCollation = $db->query(
    "SELECT COLLATION_NAME
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'products'
       AND COLUMN_NAME = 'name'"
)->fetchColumn();

echo json_encode([
    'product_name_collation' => $nameCollation ?: null,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
