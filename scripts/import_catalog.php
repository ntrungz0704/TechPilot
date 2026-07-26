<?php
/**
 * TechPilot Catalog CLI Importer
 * Usage:
 *   php scripts/import_catalog.php --file=raw_data.json [--dry-run] [--category=cpu] [--limit=10]
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/services/ProductSpecValidator.php';

$options = getopt('', ['file:', 'dry-run', 'category:', 'limit:']);

if (empty($options['file']) || !file_exists($options['file'])) {
    echo "Usage: php scripts/import_catalog.php --file=<path_to_json> [--dry-run] [--category=slug] [--limit=N]\n";
    exit(1);
}

$dryRun = isset($options['dry-run']);
$categoryFilter = $options['category'] ?? null;
$limit = isset($options['limit']) ? (int)$options['limit'] : 0;

$jsonContent = file_get_contents($options['file']);
$items = json_decode($jsonContent, true);

if (!is_array($items)) {
    echo "[FAIL] Invalid JSON file format.\n";
    exit(1);
}

$pdo = Database::getConnection();
if (!$pdo) {
    echo "[FAIL] Database connection failed.\n";
    exit(1);
}

echo "=== STARTING CATALOG IMPORT " . ($dryRun ? "(DRY RUN MODE)" : "(LIVE DATABASE TRANSACTION)") . " ===\n";

$inserted = 0;
$updated = 0;
$skipped = 0;
$failed = 0;

$count = 0;

if (!$dryRun) {
    $pdo->beginTransaction();
}

try {
    foreach ($items as $item) {
        if ($limit > 0 && $count >= $limit) {
            break;
        }

        $sku = trim($item['sku'] ?? '');
        $name = trim($item['name'] ?? '');
        $slug = trim($item['slug'] ?? '');
        $catSlug = trim($item['category_slug'] ?? 'laptop');

        if ($categoryFilter !== null && $catSlug !== $categoryFilter) {
            continue;
        }

        if (empty($name) || empty($slug)) {
            $skipped++;
            continue;
        }

        // Validate Category
        $catStmt = $pdo->prepare("SELECT id FROM categories WHERE slug = :slug AND status = 'active' LIMIT 1");
        $catStmt->execute([':slug' => $catSlug]);
        $catId = $catStmt->fetchColumn();

        if (!$catId) {
            $failed++;
            echo "  [FAIL] Category '$catSlug' not found for item '$name'\n";
            continue;
        }

        // Validate Brand
        $brandName = trim($item['brand_name'] ?? 'Generic');
        $brandStmt = $pdo->prepare("SELECT id FROM brands WHERE LOWER(name) = LOWER(:bname) LIMIT 1");
        $brandStmt->execute([':bname' => $brandName]);
        $brandId = $brandStmt->fetchColumn();

        if (!$brandId) {
            $brandId = $pdo->query("SELECT id FROM brands ORDER BY id LIMIT 1")->fetchColumn() ?: 1;
        }

        // Validate Specs
        $specs = is_array($item['specs'] ?? null) ? $item['specs'] : (json_decode($item['specs'] ?? '{}', true) ?: []);
        $valRes = ProductSpecValidator::validate($catSlug, $specs);
        if (!$valRes['valid']) {
            echo "  [WARN] Spec warning for '$name': " . implode(", ", $valRes['errors']) . "\n";
        }

        // Check if product exists by SKU or Slug
        $checkStmt = $pdo->prepare("SELECT id FROM products WHERE (sku = :sku AND :sku != '') OR slug = :slug LIMIT 1");
        $checkStmt->execute([':sku' => $sku, ':slug' => $slug]);
        $existingId = $checkStmt->fetchColumn();

        $price = (float)($item['price'] ?? 0);
        $salePrice = (float)($item['sale_price'] ?? 0);
        $stock = max(0, (int)($item['stock'] ?? 0));
        $image = trim($item['image'] ?? 'assets/images/products/placeholder.png');
        $specsJson = json_encode($specs, JSON_UNESCAPED_UNICODE);

        if ($existingId) {
            if (!$dryRun) {
                $upStmt = $pdo->prepare("
                    UPDATE products SET
                        category_id = :cat_id,
                        brand_id = :brand_id,
                        name = :name,
                        price = :price,
                        sale_price = :sale_price,
                        image = :image,
                        specs = :specs,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $upStmt->execute([
                    ':cat_id' => $catId,
                    ':brand_id' => $brandId,
                    ':name' => $name,
                    ':price' => $price,
                    ':sale_price' => $salePrice,
                    ':image' => $image,
                    ':specs' => $specsJson,
                    ':id' => $existingId
                ]);
            }
            $updated++;
        } else {
            if (!$dryRun) {
                $insStmt = $pdo->prepare("
                    INSERT INTO products (sku, name, slug, category_id, brand_id, price, sale_price, stock, image, specs, status, created_at, updated_at)
                    VALUES (:sku, :name, :slug, :cat_id, :brand_id, :price, :sale_price, :stock, :image, :specs, 'inactive', NOW(), NOW())
                ");
                $insStmt->execute([
                    ':sku' => $sku ?: 'SKU-' . rand(10000, 99999),
                    ':name' => $name,
                    ':slug' => $slug,
                    ':cat_id' => $catId,
                    ':brand_id' => $brandId,
                    ':price' => $price,
                    ':sale_price' => $salePrice,
                    ':stock' => $stock,
                    ':image' => $image,
                    ':specs' => $specsJson
                ]);
            }
            $inserted++;
        }

        $count++;
    }

    if (!$dryRun) {
        $pdo->commit();
    }

    echo "\n=== IMPORT COMPLETED ===\n";
    echo "  Inserted: $inserted | Updated: $updated | Skipped: $skipped | Failed: $failed\n";

} catch (Exception $e) {
    if (!$dryRun && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n[FAIL] Import Transaction Failed: " . $e->getMessage() . "\n";
    exit(1);
}
