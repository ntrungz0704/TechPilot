<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/core/Database.php';
require_once ROOT_PATH . '/app/models/Compare.php';
require_once ROOT_PATH . '/app/services/ProductComparisonService.php';

class CP04CompareDataContractTest
{
    private $db;
    private $passed = 0;
    private $failed = 0;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    private function log(string $message): void
    {
        echo $message . "\n";
    }

    private function assert(bool $condition, string $description, ?string $failureDetail = null): void
    {
        if ($condition) {
            $this->passed++;
            $this->log("[PASS] " . $description);
        } else {
            $this->failed++;
            $detail = $failureDetail ? " ($failureDetail)" : "";
            $this->log("[FAIL] " . $description . $detail);
        }
    }

    public function run(): void
    {
        $this->log("========================================================");
        $this->log("=== CP04 COMPARE DATA CONTRACT TEST                  ===");
        $this->log("========================================================\n");

        $this->testCategoryNormalization();
        $this->testGbTbMbNormalization();
        $this->testAmbiguousCapacity();
        $this->testRefreshRateParser();
        $this->testBasePriceValidation();
        $this->testResolvedProductCount();
        $this->testIntegrationDataHydration();

        $this->log("\n========================================================");
        $this->log("CP04 Data Contract Results: {$this->passed} passed, {$this->failed} failed");
        $this->log("========================================================");

        if ($this->failed > 0) {
            exit(1);
        }
    }

    // ── 1. Category Normalization Mapping ─────────────────────────────────────
    private function testCategoryNormalization(): void
    {
        $this->log("--- Category Normalization Mapping ---");
        $config = require ROOT_PATH . '/config/product-comparison.php';
        
        $this->assert(ProductComparisonService::normalizeCategoryKey('laptop', $config) === 'laptop', "Map 'laptop' -> 'laptop'");
        $this->assert(ProductComparisonService::normalizeCategoryKey('laptop-gaming', $config) === 'laptop', "Map 'laptop-gaming' -> 'laptop'");
        $this->assert(ProductComparisonService::normalizeCategoryKey('pc', $config) === 'prebuilt_pc', "Map 'pc' -> 'prebuilt_pc'");
        $this->assert(ProductComparisonService::normalizeCategoryKey('unknown', $config) === null, "Map 'unknown' -> null");
        $this->assert(ProductComparisonService::normalizeCategoryKey('', $config) === null, "Map empty -> null");

        // Test analyzeComparison with mixed categories
        $products = [
            ['id' => 1, 'category_slug' => 'laptop', 'price' => 10000000],
            ['id' => 2, 'category_slug' => 'pc', 'price' => 10000000]
        ];
        $result = ProductComparisonService::analyzeComparison($products);
        $this->assert($result['success'] === true, "Mixed categories -> success=true (graceful failure)");
        $this->assert($result['winner'] === null, "Mixed categories -> winner=null");
        $this->assert(str_contains($result['message'], 'nhiều danh mục khác nhau'), "Mixed categories -> distinct error message");
    }

    // ── 2. GB/TB/MB Normalization Matrix ──────────────────────────────────────
    private function testGbTbMbNormalization(): void
    {
        $this->log("\n--- GB/TB/MB Normalization Matrix ---");
        $cases = [
            '512GB' => 512.0,
            '1TB' => 1024.0,
            '2TB' => 2048.0,
            '0.5TB' => 512.0,
            '1024MB' => 1.0,
            '512MB' => 0.5,
            '256MB' => 0.25,
            '1536MB' => 1.5,
            '1MB' => 0.0009765625,
            '16 GB' => 16.0,
            '2x8GB' => 16.0,
            '2 X 16 GB' => 32.0,
            '2.5TB' => 2560.0,
            'CORSAIR 16GB' => 16.0,
            'MEMORY 32GB' => 32.0,
            'STORAGE 1TB' => 1024.0,
        ];
        foreach ($cases as $str => $expected) {
            $this->assert(ProductComparisonService::parseStorageGb($str) === $expected, "parseStorageGb('$str') === $expected");
        }
    }

    // ── 3. Ambiguous Capacity Matrix ──────────────────────────────────────────
    private function testAmbiguousCapacity(): void
    {
        $this->log("\n--- Ambiguous RAM/SSD Matrix ---");
        $cases = [
            '512GB / 1TB',
            '1TB HOẶC 2TB',
            '8GB/16GB',
            '16GB OR 32GB',
            '512GB SSD + 1TB HDD',
            '16GB onboard + 16GB slot',
            '1TB SSD, 2TB HDD',
            'unknown',
            '',
        ];
        foreach ($cases as $str) {
            $this->assert(ProductComparisonService::parseStorageGb($str) === null, "parseStorageGb('$str') === null (ambiguous)");
        }

        // Test in analyzeComparison -> creates fail-closed
        $products = [[
            'id' => 1, 'category_slug' => 'laptop', 'price' => 10000000,
            'specs' => json_encode(['RAM' => '8GB/16GB'])
        ]];
        $result = ProductComparisonService::analyzeComparison($products, ['expected_count' => 1, 'min_ram' => 8]);
        
        $p1 = $result['products'][0] ?? [];
        $this->assert(($p1['eligible'] ?? true) === false, "Ambiguous RAM in analyzeComparison -> eligible=false");
        $this->assert(($p1['verification_required'] ?? false) === true, "Ambiguous RAM -> verification_required=true");
        $this->assert(in_array('ram', $p1['failed_requirements'] ?? []), "Ambiguous RAM -> failed_requirements includes 'ram'");
    }

    // ── 4. Refresh Rate Fail-Closed Parser ────────────────────────────────────
    private function testRefreshRateParser(): void
    {
        $this->log("\n--- Refresh Rate Parser Matrix ---");
        $casesValid = [
            '60Hz' => 60.0,
            '144 Hz' => 144.0,
            '240Hz IPS' => 240.0,
        ];
        foreach ($casesValid as $str => $expected) {
            $this->assert(ProductComparisonService::parseRefreshRateHz($str) === $expected, "parseRefreshRateHz('$str') === $expected");
        }

        $casesInvalid = [
            '60Hz / 144Hz',
            '144Hz hoặc 240Hz',
            '144Hz OR 240Hz',
            '60-144Hz',
            'unknown',
            '',
        ];
        foreach ($casesInvalid as $str) {
            $this->assert(ProductComparisonService::parseRefreshRateHz($str) === null, "parseRefreshRateHz('$str') === null (ambiguous)");
        }

        $products = [[
            'id' => 1, 'category_slug' => 'laptop', 'price' => 10000000,
            'specs' => json_encode(['Màn hình' => '144Hz hoặc 240Hz'])
        ]];
        $result = ProductComparisonService::analyzeComparison($products, ['expected_count' => 1, 'min_refresh_rate' => 144]);
        $p1 = $result['products'][0] ?? [];
        $this->assert(($p1['eligible'] ?? true) === false, "Ambiguous Refresh Rate -> eligible=false");
        $this->assert(($p1['verification_required'] ?? false) === true, "Ambiguous Refresh Rate -> verification_required=true");
        $this->assert(in_array('refresh_rate', $p1['failed_requirements'] ?? []), "Ambiguous Refresh Rate -> failed_requirements includes 'refresh_rate'");
    }

    // ── 5. Base-Price Failure Matrix ──────────────────────────────────────────
    private function testBasePriceValidation(): void
    {
        $this->log("\n--- Base-Price Failure Matrix ---");
        $invalidPrices = [0, -100, INF, NAN, null, 'free', ''];
        foreach ($invalidPrices as $price) {
            $desc = is_string($price) ? "'$price'" : (is_null($price) ? 'null' : (is_float($price) && is_nan($price) ? 'NAN' : (is_float($price) && is_infinite($price) ? 'INF' : $price)));
            $products = [['id' => 1, 'category_slug' => 'laptop', 'price' => $price]];
            $res = ProductComparisonService::analyzeComparison($products, ['expected_count' => 1]);
            $p = $res['products'][0] ?? [];
            $this->assert(($p['eligible'] ?? true) === false, "Base price $desc -> eligible=false");
            $this->assert(in_array('price', $p['failed_requirements'] ?? []), "Base price $desc -> failed_requirements includes 'price'");
            $this->assert(($p['effective_price'] ?? -1) === 0.0, "Base price $desc -> effective_price=0");
        }

        $res = ProductComparisonService::analyzeComparison([['id' => 1, 'category_slug' => 'laptop', 'price' => 10000000]], ['expected_count' => 1]);
        $this->assert(($res['products'][0]['eligible'] ?? false) === true, "Valid positive price -> eligible=true");
    }

    // ── 6. Resolved Product Count Behavior ────────────────────────────────────
    private function testResolvedProductCount(): void
    {
        $this->log("\n--- Resolved Product Count Behavior ---");
        $products = [['id' => 1, 'category_slug' => 'laptop', 'price' => 10000]];
        $result = ProductComparisonService::analyzeComparison($products, ['expected_count' => 2]);
        $this->assert($result['success'] === true, "expected >= 2 but actual < 2 -> success structure");
        $this->assert($result['winner'] === null, "expected >= 2 but actual < 2 -> winner=null");
        $this->assert(str_contains($result['message'], 'không còn hợp lệ'), "expected >= 2 but actual < 2 -> message is correct");
        
        $result2 = ProductComparisonService::analyzeComparison($products, ['expected_count' => 1]);
        $this->assert($result2['winner'] === 1, "expected=1, actual=1 -> allows winner=1");
    }

    // ── 7. Integration Data Hydration (Flash Sale Matrix) ────────────────────
    private function testIntegrationDataHydration(): void
    {
        $this->log("\n--- Production Integration Data Hydration (Flash Sale Matrix) ---");

        $this->db->beginTransaction();
        try {
            $this->db->exec("INSERT INTO brands (name, slug) VALUES ('TestBrand', 'test-brand')");
            $brandId = (int)$this->db->lastInsertId();

            $stmtCat = $this->db->query("SELECT id FROM categories WHERE slug = 'laptop' LIMIT 1");
            $catRow = $stmtCat->fetch(PDO::FETCH_ASSOC);
            if (!$catRow) {
                $this->db->exec("INSERT INTO categories (name, slug) VALUES ('Laptop Test', 'laptop')");
                $catId = (int)$this->db->lastInsertId();
            } else {
                $catId = (int)$catRow['id'];
            }

            // Products
            $stmt = $this->db->prepare("INSERT INTO products (name, slug, price, status, category_id, brand_id) VALUES (?, ?, ?, 'active', ?, ?)");
            // P1: Active FS
            $stmt->execute(['P1', 'p-1', 20000000, $catId, $brandId]);
            $p1 = (int)$this->db->lastInsertId();
            // P2: Future FS
            $stmt->execute(['P2', 'p-2', 20000000, $catId, $brandId]);
            $p2 = (int)$this->db->lastInsertId();
            // P3: Expired FS
            $stmt->execute(['P3', 'p-3', 20000000, $catId, $brandId]);
            $p3 = (int)$this->db->lastInsertId();
            // P4: Inactive FS
            $stmt->execute(['P4', 'p-4', 20000000, $catId, $brandId]);
            $p4 = (int)$this->db->lastInsertId();
            // P5: Sold-out FS
            $stmt->execute(['P5', 'p-5', 20000000, $catId, $brandId]);
            $p5 = (int)$this->db->lastInsertId();
            // P6: Multiple Active FS rows (Tie Breaker)
            $stmt->execute(['P6', 'p-6', 20000000, $catId, $brandId]);
            $p6 = (int)$this->db->lastInsertId();
            // P7: No FS + valid sale_price
            $stmt = $this->db->prepare("INSERT INTO products (name, slug, price, sale_price, status, category_id, brand_id) VALUES (?, ?, ?, ?, 'active', ?, ?)");
            $stmt->execute(['P7', 'p-7', 20000000, 18000000, $catId, $brandId]);
            $p7 = (int)$this->db->lastInsertId();
            // P8: End Boundary FS (end_time = NOW())
            $stmt = $this->db->prepare("INSERT INTO products (name, slug, price, status, category_id, brand_id) VALUES (?, ?, ?, 'active', ?, ?)");
            $stmt->execute(['P8', 'p-8', 20000000, $catId, $brandId]);
            $p8 = (int)$this->db->lastInsertId();

            // Flash Sales
            $stmtFS = $this->db->prepare("INSERT INTO flash_sales (title, slug, start_time, end_time, status) VALUES (?, ?, ?, ?, ?)");
            
            // FS Active
            $stmtFS->execute(['FS A', 'fs-a', date('Y-m-d H:i:s', time() - 3600), date('Y-m-d H:i:s', time() + 3600), 'active']);
            $fsA = (int)$this->db->lastInsertId();
            // FS Future
            $stmtFS->execute(['FS B', 'fs-b', date('Y-m-d H:i:s', time() + 3600), date('Y-m-d H:i:s', time() + 7200), 'active']);
            $fsB = (int)$this->db->lastInsertId();
            // FS Expired
            $stmtFS->execute(['FS C', 'fs-c', date('Y-m-d H:i:s', time() - 7200), date('Y-m-d H:i:s', time() - 3600), 'active']);
            $fsC = (int)$this->db->lastInsertId();
            // FS Inactive
            $stmtFS->execute(['FS D', 'fs-d', date('Y-m-d H:i:s', time() - 3600), date('Y-m-d H:i:s', time() + 3600), 'hidden']);
            $fsD = (int)$this->db->lastInsertId();
            // FS End Boundary
            $nowDb = $this->db->query("SELECT NOW()")->fetchColumn();
            $stmtFS->execute(['FS E', 'fs-e', date('Y-m-d H:i:s', time() - 3600), $nowDb, 'active']);
            $fsE = (int)$this->db->lastInsertId();

            // Flash Sale Items
            $stmtItem = $this->db->prepare("INSERT INTO flash_sale_items (flash_sale_id, product_id, discount_price, allocation_quantity, sold_quantity) VALUES (?, ?, ?, ?, ?)");
            
            $stmtItem->execute([$fsA, $p1, 15000000, 10, 0]);
            $stmtItem->execute([$fsB, $p2, 15000000, 10, 0]);
            $stmtItem->execute([$fsC, $p3, 15000000, 10, 0]);
            $stmtItem->execute([$fsD, $p4, 15000000, 10, 0]);
            $stmtItem->execute([$fsA, $p5, 15000000, 10, 10]); // Sold out
            
            // P6 has two active rows. One is 16M, one is 15M
            $stmtItem->execute([$fsA, $p6, 16000000, 10, 0]);
            $stmtItem->execute([$fsA, $p6, 15000000, 10, 0]); 
            
            // P8 End Boundary
            $stmtItem->execute([$fsE, $p8, 15000000, 10, 0]);

            $model = new Compare();
            $allProducts = $model->getProductsByIds([$p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8]);
            
            // Map products by ID
            $pMap = [];
            foreach ($allProducts as $p) {
                $pMap[(int)$p['id']] = $p;
            }

            // A. Active
            $this->assert(isset($pMap[$p1]['flash_sale']) && (float)$pMap[$p1]['flash_sale']['discount_price'] === 15000000.0, "Active Flash Sale -> hydrated");
            $resA = ProductComparisonService::analyzeComparison([$pMap[$p1]], ['expected_count' => 1, '_now' => time()]);
            $this->assert((float)$resA['products'][0]['effective_price'] === 15000000.0, "Active Flash Sale -> effective price applies");

            // B. Future
            $this->assert($pMap[$p2]['flash_sale'] === null, "Future Flash Sale -> not hydrated");

            // C. Expired
            $this->assert($pMap[$p3]['flash_sale'] === null, "Expired Flash Sale -> not hydrated");

            // D. Inactive
            $this->assert($pMap[$p4]['flash_sale'] === null, "Inactive Flash Sale -> not hydrated");

            // E. Sold-out
            $this->assert($pMap[$p5]['flash_sale'] === null, "Sold-out Flash Sale -> not hydrated");

            // F. Multiple
            $this->assert(isset($pMap[$p6]['flash_sale']) && (float)$pMap[$p6]['flash_sale']['discount_price'] === 15000000.0, "Multiple active rows -> selects lowest discount_price");

            // G. No FS + sale_price
            $this->assert($pMap[$p7]['flash_sale'] === null, "No Flash Sale + valid sale_price -> flash_sale=null");
            $resG = ProductComparisonService::analyzeComparison([$pMap[$p7]], ['expected_count' => 1, '_now' => time()]);
            $this->assert((float)$resG['products'][0]['effective_price'] === 18000000.0, "No Flash Sale + valid sale_price -> effective_price=sale_price");

            // H. End Boundary
            // Hydration might happen because the query is end_time > NOW(), if NOW() drifts, it might pass or fail.
            // But we created it with $nowDb. In DB query `end_time > NOW()`, so if it equals, it will fail.
            $this->assert($pMap[$p8]['flash_sale'] === null, "End boundary (end_time = current DB time) -> not hydrated (end_time > NOW() false)");

            $this->db->rollBack();
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->assert(false, "Database integration threw exception", $e->getMessage());
        }
    }
}

// Run
$suite = new CP04CompareDataContractTest();
$suite->run();
