<?php
/**
 * Export Verified Catalog Script — TechPilot
 * Queries MySQL database for products strictly having status = 'active' AND verification_status = 'verified',
 * and exports them to database/catalog_verified.json.
 */

require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();
if (!$db) {
    echo "ERROR: Cannot connect to database\n";
    exit(1);
}

echo "=== TechPilot Export Verified Catalog ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

$stmt = $db->query("
    SELECT p.*, c.slug AS category_slug, b.name AS brand_name
    FROM products p
    INNER JOIN categories c ON c.id = p.category_id
    INNER JOIN brands b ON b.id = p.brand_id
    WHERE p.status = 'active' AND p.verification_status = 'verified'
    ORDER BY p.id ASC
");

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$exportedProducts = [];

foreach ($rows as $r) {
    $specs = !empty($r['specs']) ? json_decode($r['specs'], true) : [];
    if (!is_array($specs)) {
        $specs = [];
    }

    $exportedProducts[] = [
        'sku'                 => $r['sku'],
        'canonical_model_key' => $r['canonical_model_key'] ?: $r['slug'],
        'name'                => $r['name'],
        'slug'                => $r['slug'],
        'category_slug'       => $r['category_slug'],
        'brand'               => $r['brand_name'],
        'model_number'        => $r['model_number'] ?: 'STANDARD',
        'price'               => (float)$r['price'],
        'sale_price'          => $r['sale_price'] !== null ? (float)$r['sale_price'] : null,
        'stock'               => (int)$r['stock'],
        'status'              => $r['status'],
        'short_description'   => $r['short_desc'] ?: $r['name'],
        'specs'               => $specs,
        'images'              => [
            [
                'path'       => $r['image'] ?: ('assets/images/products/placeholder-' . $r['category_slug'] . '.svg'),
                'source_url' => $r['source_url'],
                'sha256'     => null,
                'is_primary' => true
            ]
        ],
        'sources'             => [
            [
                'type'       => 'manufacturer',
                'url'        => $r['source_url'] ?: 'https://techpilot.vn',
                'checked_at' => $r['source_checked_at'] ?: date('c')
            ]
        ],
        'verification'        => [
            'status'      => $r['verification_status'],
            'score'       => (int)$r['verification_score'],
            'verified_at' => $r['verified_at'] ?: date('c'),
            'notes'       => ['Verified authentic product entry']
        ]
    ];
}

$payload = [
    'schema_version' => '1.0',
    'generated_at'   => date('c'),
    'products'       => $exportedProducts
];

$destFile = __DIR__ . '/../database/catalog_verified.json';
file_put_contents($destFile, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

echo "Successfully exported " . count($exportedProducts) . " verified products to database/catalog_verified.json!\n";
