<?php
/**
 * Import Verified Catalog Script — TechPilot
 * Imports validated catalog_verified.json data into MySQL database with transaction safety,
 * dry-run option, and upsert logic by SKU / canonical_model_key.
 * Strictly requiring verification_status = 'verified'.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/validate_catalog.php';

$db = Database::getConnection();
if (!$db) {
    echo "ERROR: Cannot connect to database\n";
    exit(1);
}

$isDryRun = in_array('--dry-run', $argv, true);

echo "=== TechPilot Import Verified Catalog ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "Mode: " . ($isDryRun ? "DRY-RUN (No DB Changes)" : "PRODUCTION IMPORT") . "\n\n";

$dataFile = __DIR__ . '/../database/catalog_verified.json';
if (!file_exists($dataFile)) {
    echo "ERROR: catalog_verified.json file not found\n";
    exit(1);
}

$catalogData = json_decode(file_get_contents($dataFile), true);
$valResult = validateCatalogData($catalogData);

if (!$valResult['valid']) {
    echo "ERROR: Catalog validation failed! Cannot import.\n";
    foreach ($valResult['errors'] as $e) {
        echo " - $e\n";
    }
    exit(1);
}

// Map category slugs to category_ids
$catRows = $db->query("SELECT id, slug FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$categories = [];
foreach ($catRows as $cRow) {
    $categories[strtolower($cRow['slug'])] = (int)$cRow['id'];
}

// Map brand names to brand_ids (insert brand if missing)
$brandStmt = $db->query("SELECT name, id FROM brands");
$brands = [];
foreach ($brandStmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
    $brands[strtolower($b['name'])] = (int)$b['id'];
}

$insertBrandStmt = $db->prepare("INSERT INTO brands (name, slug, created_at, updated_at) VALUES (:name, :slug, NOW(), NOW())");

$upsertProdStmt = $db->prepare("
    INSERT INTO products (
        sku, model_number, canonical_model_key, category_id, brand_id, name, slug,
        short_desc, description, price, sale_price, stock, image, specs,
        source_url, source_name, source_checked_at, status, verification_status,
        verification_score, verified_at, created_at, updated_at
    ) VALUES (
        :sku, :model_number, :canonical_model_key, :category_id, :brand_id, :name, :slug,
        :short_desc, :description, :price, :sale_price, :stock, :image, :specs,
        :source_url, :source_name, :source_checked_at, :status, :verification_status,
        :verification_score, :verified_at, NOW(), NOW()
    )
    ON DUPLICATE KEY UPDATE
        model_number = VALUES(model_number),
        canonical_model_key = VALUES(canonical_model_key),
        category_id = VALUES(category_id),
        brand_id = VALUES(brand_id),
        name = VALUES(name),
        slug = VALUES(slug),
        short_desc = VALUES(short_desc),
        description = VALUES(description),
        price = VALUES(price),
        sale_price = VALUES(sale_price),
        stock = VALUES(stock),
        image = VALUES(image),
        specs = VALUES(specs),
        source_url = VALUES(source_url),
        source_name = VALUES(source_name),
        source_checked_at = VALUES(source_checked_at),
        status = VALUES(status),
        verification_status = VALUES(verification_status),
        verification_score = VALUES(verification_score),
        verified_at = VALUES(verified_at),
        updated_at = NOW()
");

$products = $catalogData['products'];
$importedCount = 0;

if (!$isDryRun) {
    $db->beginTransaction();
}

try {
    foreach ($products as $p) {
        $sku = trim($p['sku']);
        $catSlug = trim($p['category_slug']);
        $brandName = trim($p['brand']);
        $brandKey = strtolower($brandName);

        if (!isset($categories[$catSlug])) {
            throw new Exception("Category '$catSlug' not found in database");
        }
        $catId = (int)$categories[$catSlug];

        // Ensure brand exists
        if (!isset($brands[$brandKey])) {
            if (!$isDryRun) {
                $bSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $brandName), '-'));
                $insertBrandStmt->execute([':name' => $brandName, ':slug' => $bSlug]);
                $brands[$brandKey] = (int)$db->lastInsertId();
            } else {
                $brands[$brandKey] = 999;
            }
        }
        $brandId = (int)$brands[$brandKey];

        $sourceUrl = $p['sources'][0]['url'] ?? '';
        $rawChecked = $p['sources'][0]['checked_at'] ?? date('Y-m-d H:i:s');
        $sourceCheckedAt = date('Y-m-d H:i:s', strtotime($rawChecked));
        $specsJson = json_encode($p['specs'], JSON_UNESCAPED_UNICODE);
        $primaryImg = $p['images'][0]['path'] ?? ('assets/images/products/placeholder-' . $catSlug . '.svg');

        if (!$isDryRun) {
            $upsertProdStmt->execute([
                ':sku'                 => $sku,
                ':model_number'        => $p['model_number'],
                ':canonical_model_key' => $p['canonical_model_key'],
                ':category_id'         => $catId,
                ':brand_id'            => $brandId,
                ':name'                => $p['name'],
                ':slug'                => $p['slug'],
                ':short_desc'          => $p['short_description'],
                ':description'         => $p['short_description'],
                ':price'               => $p['price'],
                ':sale_price'          => $p['sale_price'],
                ':stock'               => $p['stock'],
                ':image'               => $primaryImg,
                ':specs'               => $specsJson,
                ':source_url'          => $sourceUrl,
                ':source_name'         => 'Official Manufacturer',
                ':source_checked_at'   => $sourceCheckedAt,
                ':status'              => 'active',
                ':verification_status' => 'verified',
                ':verification_score'  => 100,
                ':verified_at'         => date('Y-m-d H:i:s')
            ]);
        }

        $importedCount++;
    }

    if (!$isDryRun) {
        $db->commit();
    }

    echo "Import Complete: Successfully processed $importedCount verified products!\n";

} catch (Exception $e) {
    if (!$isDryRun) {
        $db->rollBack();
    }
    echo "ERROR during import: " . $e->getMessage() . "\n";
    exit(1);
}
