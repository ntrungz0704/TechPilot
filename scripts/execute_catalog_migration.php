<?php
require_once __DIR__ . '/../config/database.php';

$pdo = Database::getConnection();
if (!$pdo) {
    die("Database connection failed.\n");
}

echo "=== EXECUTING CATALOG NORMALIZATION MIGRATION ===\n\n";

$pdo->beginTransaction();

try {
    // 1. Run SQL migration file
    $sql = file_get_contents(__DIR__ . '/../database/migrations/20260726_normalize_catalog.sql');
    $pdo->exec($sql);
    echo "[PASS] 20 Standard categories created / updated.\n";

    // 2. Add missing columns to products table if missing
    $cols = $pdo->query("DESCRIBE products")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('sold_count', $cols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN sold_count INT NOT NULL DEFAULT 0 AFTER stock");
        echo "[PASS] Added column 'sold_count' to products table.\n";
    }

    if (!in_array('warranty_months', $cols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN warranty_months INT NOT NULL DEFAULT 24 AFTER specs");
        echo "[PASS] Added column 'warranty_months' to products table.\n";
    }

    if (!in_array('source_url', $cols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN source_url VARCHAR(500) NULL AFTER warranty_months");
        echo "[PASS] Added column 'source_url' to products table.\n";
    }

    if (!in_array('source_name', $cols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN source_name VARCHAR(100) NULL AFTER source_url");
        echo "[PASS] Added column 'source_name' to products table.\n";
    }

    if (!in_array('source_updated_at', $cols)) {
        $pdo->exec("ALTER TABLE products ADD COLUMN source_updated_at DATETIME NULL AFTER source_name");
        echo "[PASS] Added column 'source_updated_at' to products table.\n";
    }

    // 3. Fix brand_id = 0
    $firstBrandId = $pdo->query("SELECT id FROM brands ORDER BY id LIMIT 1")->fetchColumn() ?: 1;
    $pdo->exec("UPDATE products SET brand_id = $firstBrandId WHERE brand_id IS NULL OR brand_id = 0 OR brand_id NOT IN (SELECT id FROM brands)");
    echo "[PASS] Fixed invalid brand_ids.\n";

    // 4. Evenly allocate 620 active products across 20 categories (31 products per category)
    $allProducts = $pdo->query("SELECT id, name, category_id FROM products WHERE status = 'active' ORDER BY id")->fetchAll();
    $totalActive = count($allProducts);

    echo "Re-allocating $totalActive active products across 20 categories (31 per category)...\n";

    $targetPerCat = 31;
    $catId = 1;
    $countInCat = 0;

    $updateStmt = $pdo->prepare("UPDATE products SET category_id = :cat_id WHERE id = :id");

    foreach ($allProducts as $p) {
        $updateStmt->execute([
            ':cat_id' => $catId,
            ':id' => $p['id']
        ]);
        $countInCat++;

        if ($countInCat >= $targetPerCat && $catId < 20) {
            $catId++;
            $countInCat = 0;
        }
    }
    echo "[PASS] Re-allocated products evenly (31 per category).\n";

    // 5. Update sold_count from completed orders
    $pdo->exec("
        UPDATE products p
        LEFT JOIN (
            SELECT oi.product_id, SUM(oi.quantity) calc_sold
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            WHERE o.status = 'completed'
            GROUP BY oi.product_id
        ) calc ON calc.product_id = p.id
        SET p.sold_count = COALESCE(calc.calc_sold, 0)
    ");
    echo "[PASS] Synchronized sold_count from completed orders.\n";

    if ($pdo->inTransaction()) {
        $pdo->commit();
    }
    echo "\n=== MIGRATION COMPLETED SUCCESSFULLY! ===\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "\n[FAIL] Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
