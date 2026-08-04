<?php
/**
 * CP04 Data Contract Integration Test
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/config/database.php';

if (!class_exists('GeminiService')) {
    class GeminiService {
        public static function callGemini(string $p, array $o = []): string { return '{}'; }
    }
}
require_once ROOT_PATH . '/app/services/ProductComparisonService.php';
require_once ROOT_PATH . '/app/models/Compare.php';

class CP04CompareDataContractTest
{
    private int $passed = 0;
    private int $failed = 0;
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== CP04 COMPARE DATA CONTRACT TEST                  ===\n";
        echo "========================================================\n";

        $this->testCategoryNormalization();
        $this->testCapacityUnitNormalization();
        $this->testBasePriceFailClosed();
        $this->testResolvedProductCount();
        $this->testIntegrationDataHydration();

        echo "\n========================================================\n";
        echo "CP04 Data Contract Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "========================================================\n";
        exit($this->failed > 0 ? 1 : 0);
    }

    private function assert(bool $condition, string $message, string $detail = ''): void
    {
        if ($condition) {
            $this->passed++;
            echo "[PASS] {$message}\n";
        } else {
            $this->failed++;
            $suffix = $detail ? " ({$detail})" : '';
            echo "[FAIL] {$message}{$suffix}\n";
        }
    }

    private function log(string $msg): void
    {
        echo $msg . "\n";
    }

    // ── 1. Category Normalization ─────────────────────────────────────────────
    private function testCategoryNormalization(): void
    {
        $this->log("\n--- Category Normalization Mapping ---");
        $config = ['categories' => [
            'laptop' => ['slugs' => ['laptop', 'laptop-gaming']],
            'prebuilt_pc' => ['slugs' => ['pc', 'desktop']],
        ]];

        $this->assert(ProductComparisonService::normalizeCategoryKey('laptop', $config) === 'laptop', "Map 'laptop' -> 'laptop'");
        $this->assert(ProductComparisonService::normalizeCategoryKey('laptop-gaming', $config) === 'laptop', "Map 'laptop-gaming' -> 'laptop'");
        $this->assert(ProductComparisonService::normalizeCategoryKey('pc', $config) === 'prebuilt_pc', "Map 'pc' -> 'prebuilt_pc'");
        $this->assert(ProductComparisonService::normalizeCategoryKey('unknown', $config) === null, "Map 'unknown' -> null");
        $this->assert(ProductComparisonService::normalizeCategoryKey('', $config) === null, "Map empty -> null");

        // Mixed category behavior
        $p1 = ['id' => 1, 'category_slug' => 'laptop', 'price' => 1000, 'name' => 'L1', 'status' => 'active'];
        $p2 = ['id' => 2, 'category_slug' => 'pc', 'price' => 1000, 'name' => 'P1', 'status' => 'active'];
        $result = ProductComparisonService::analyzeComparison([$p1, $p2], []);
        $this->assert($result['success'] === true, "Mixed categories -> success=true (graceful failure)");
        $this->assert($result['winner'] === null, "Mixed categories -> winner=null");
        $this->assert(strpos($result['message'], 'nhiều danh mục khác nhau') !== false, "Mixed categories -> distinct error message");
    }

    // ── 2. Capacity Unit Normalization ────────────────────────────────────────
    private function testCapacityUnitNormalization(): void
    {
        $this->log("\n--- GB/TB/MB Normalization Matrix ---");
        $tests = [
            '512GB'   => 512,
            '1TB'     => 1024,
            '2TB'     => 2048,
            '0.5TB'   => 512,
            '1024MB'  => 1,
            '16 GB'   => 16,
            '2x8GB'   => 16,
            '2 X 16 GB' => 32,
            '2.5TB'   => 2560,
        ];
        foreach ($tests as $input => $expected) {
            $parsed = ProductComparisonService::parseStorageGb($input);
            $this->assert($parsed === $expected, "parseStorageGb('{$input}') === {$expected}");
        }

        $this->log("\n--- Ambiguous RAM/SSD Matrix ---");
        $ambiguous = [
            '512GB / 1TB',
            '1TB HOẶC 2TB',
            '8GB/16GB',
            '16GB OR 32GB',
            'unknown',
            ''
        ];
        foreach ($ambiguous as $input) {
            $parsed = ProductComparisonService::parseStorageGb($input);
            $this->assert($parsed === null, "parseStorageGb('{$input}') === null (ambiguous)");
        }

        // Test fail-closed in analyzeComparison
        $p = ['id' => 1, 'category_slug' => 'laptop', 'price' => 1000, 'name' => 'L1', 'specs' => json_encode(['ram' => '8GB/16GB'])];
        $result = ProductComparisonService::analyzeComparison([$p], ['min_ram' => 16, 'expected_count' => 1]);
        $found = $result['products'][0] ?? null;
        $this->assert($found && $found['eligible'] === false, "Ambiguous RAM in analyzeComparison -> eligible=false");
        $this->assert($found && $found['verification_required'] === true, "Ambiguous RAM -> verification_required=true");
        $this->assert($found && in_array('ram', $found['failed_requirements']), "Ambiguous RAM -> failed_requirements includes 'ram'");
    }

    // ── 3. Base-Price Failure Matrix ──────────────────────────────────────────
    private function testBasePriceFailClosed(): void
    {
        $this->log("\n--- Base-Price Failure Matrix ---");
        $tests = [
            ['val' => 0,          'desc' => 'price = 0'],
            ['val' => -500,       'desc' => 'price < 0'],
            ['val' => INF,        'desc' => 'price = INF'],
            ['val' => NAN,        'desc' => 'price = NAN'],
            ['val' => null,       'desc' => 'missing price'],
            ['val' => 'invalid',  'desc' => 'non-numeric string'],
        ];

        foreach ($tests as $t) {
            $p = ['id' => 1, 'category_slug' => 'laptop', 'name' => 'L1', 'price' => $t['val']];
            $result = ProductComparisonService::analyzeComparison([$p], ['expected_count' => 1]);
            $found = $result['products'][0] ?? null;
            $this->assert($found && $found['eligible'] === false, "Base price {$t['desc']} -> eligible=false");
            $this->assert($found && in_array('price', $found['failed_requirements']), "Base price {$t['desc']} -> failed_requirements includes 'price'");
            $this->assert($found && (float)$found['effective_price'] === 0.0, "Base price {$t['desc']} -> effective_price=0");
        }

        // Valid positive price
        $p = ['id' => 1, 'category_slug' => 'laptop', 'name' => 'L1', 'price' => 15000000];
        $result = ProductComparisonService::analyzeComparison([$p], ['expected_count' => 1]);
        $found = $result['products'][0] ?? null;
        $this->assert($found && $found['eligible'] === true, "Valid positive price -> eligible=true");
    }

    // ── 4. Resolved Product Count ─────────────────────────────────────────────
    private function testResolvedProductCount(): void
    {
        $this->log("\n--- Resolved Product Count Behavior ---");
        $p1 = ['id' => 1, 'category_slug' => 'laptop', 'price' => 1000, 'name' => 'L1'];
        
        // expected_count=2, actual=1
        $result = ProductComparisonService::analyzeComparison([$p1], ['expected_count' => 2]);
        $this->assert($result['success'] === true, "expected >= 2 but actual < 2 -> success structure");
        $this->assert($result['winner'] === null, "expected >= 2 but actual < 2 -> winner=null");
        $this->assert(strpos($result['message'], 'không còn hợp lệ') !== false, "expected >= 2 but actual < 2 -> message is correct");

        // expected_count=1, actual=1 (allowed fallback for single-view debug, etc)
        $result2 = ProductComparisonService::analyzeComparison([$p1], ['expected_count' => 1]);
        $this->assert($result2['winner'] === 1, "expected=1, actual=1 -> allows winner=1");
    }

    // ── 5. Integration Data Hydration ─────────────────────────────────────────
    private function testIntegrationDataHydration(): void
    {
        $this->log("\n--- Production Integration Data Hydration ---");

        $this->db->beginTransaction();
        try {
            // Setup Brand & Category
            $this->db->exec("INSERT INTO brands (name, slug) VALUES ('TestBrand', 'test-brand')");
            $brandId = (int)$this->db->lastInsertId();

            $this->db->exec("INSERT INTO categories (name, slug) VALUES ('Laptop Test', 'laptop-test')");
            $catId = (int)$this->db->lastInsertId();

            // Insert Product (Base price: 20M)
            $stmt = $this->db->prepare("INSERT INTO products (name, slug, price, status, category_id, brand_id) VALUES ('IntProd', 'int-prod', 20000000, 'active', ?, ?)");
            $stmt->execute([$catId, $brandId]);
            $productId = (int)$this->db->lastInsertId();

            // Insert Flash Sale & Item (Discount: 15M, Active)
            $this->db->exec("INSERT INTO flash_sales (title, slug, start_time, end_time, status) VALUES ('FS1', 'fs-1', NOW() - INTERVAL 1 HOUR, NOW() + INTERVAL 1 HOUR, 'active')");
            $fsId = (int)$this->db->lastInsertId();

            $stmtFS = $this->db->prepare("INSERT INTO flash_sale_items (flash_sale_id, product_id, discount_price, allocation_quantity, sold_quantity) VALUES (?, ?, 15000000, 10, 0)");
            $stmtFS->execute([$fsId, $productId]);

            // Test Model
            $model = new Compare();
            $products = $model->getProductsByIds([$productId]);
            
            $this->assert(count($products) === 1, "Model resolved exactly 1 product");
            $p = $products[0];
            $this->assert(isset($p['category_slug']) && $p['category_slug'] === 'laptop-test', "Model returns category_slug from categories");
            $this->assert($p['brand_name'] === 'TestBrand', "Model returns brand_name");
            
            // Verify Hydrated Flash Sale Alias Contract
            $fs = $p['flash_sale'];
            $this->assert(is_array($fs), "Flash sale was hydrated");
            $this->assert(isset($fs['discount_price']), "Flash sale has discount_price");
            $this->assert(isset($fs['fs_status']), "Flash sale has fs_status");
            $this->assert(isset($fs['fs_start']), "Flash sale has fs_start");
            $this->assert(isset($fs['fs_end']), "Flash sale has fs_end");
            $this->assert(isset($fs['fs_product_id']), "Flash sale has fs_product_id");
            $this->assert((float)$fs['discount_price'] === 15000000.0, "Hydrated discount price is correct");

            // Test effective price with budget integration
            // Base is 20M, Flash is 15M. Budget is 18M. 
            // Mock config for normalizeCategoryKey to allow 'laptop-test'
            $config = ['categories' => ['laptop' => ['slugs' => ['laptop-test']]]];
            // We use the actual product row retrieved from DB
            $opts = ['budget_max' => 18000000, 'expected_count' => 1];
            
            // Temporary patch config logic in memory:
            // Since ProductComparisonService internally requires the real config, we can't easily mock it without a test seam.
            // Wait, the real config might not map 'laptop-test'. Let's insert a real slug like 'laptop' instead!
            // Re-do with 'laptop' slug which exists in real config
            $stmtCat = $this->db->query("SELECT id FROM categories WHERE slug = 'laptop' LIMIT 1");
            $catRow = $stmtCat->fetch(PDO::FETCH_ASSOC);
            if (!$catRow) {
                $this->db->exec("INSERT INTO categories (name, slug) VALUES ('Laptop Test', 'laptop')");
                $catId2 = (int)$this->db->lastInsertId();
            } else {
                $catId2 = (int)$catRow['id'];
            }
            $stmt = $this->db->prepare("INSERT INTO products (name, slug, price, status, category_id, brand_id) VALUES ('IntProd2', 'int-prod-2', 20000000, 'active', ?, ?)");
            $stmt->execute([$catId2, $brandId]);
            $productId2 = (int)$this->db->lastInsertId();
            $this->db->exec("INSERT INTO flash_sales (title, slug, start_time, end_time, status) VALUES ('FS2', 'fs-2', NOW() - INTERVAL 1 HOUR, NOW() + INTERVAL 1 HOUR, 'active')");
            $fsId2 = (int)$this->db->lastInsertId();
            $stmtFS2 = $this->db->prepare("INSERT INTO flash_sale_items (flash_sale_id, product_id, discount_price, allocation_quantity, sold_quantity) VALUES (?, ?, 15000000, 10, 0)");
            $stmtFS2->execute([$fsId2, $productId2]);

            $products2 = $model->getProductsByIds([$productId2]);
            $p2 = $products2[0];
            
            $opts['_now'] = strtotime($p2['flash_sale']['fs_start']) + 10;
            $result = ProductComparisonService::analyzeComparison([$p2], $opts);
            if (!$result['products'][0]['eligible']) {
                var_dump($result['products'][0]['ineligible_reasons']);
                var_dump($p2['flash_sale']);
            }
            $this->assert($result['products'][0]['eligible'] === true, "Product original price > budget but Flash Sale < budget -> eligible=true");
            $this->assert((float)$result['products'][0]['effective_price'] === 15000000.0, "effective_price = flash sale price");
            $this->assert($result['winner'] === $productId2, "Can become winner");

            // Test without flash sale (delete it)
            $this->db->exec("DELETE FROM flash_sales WHERE id = {$fsId2}");
            $products3 = $model->getProductsByIds([$productId2]);
            $this->assert($products3[0]['flash_sale'] === null, "No Flash Sale -> flash_sale=null");
            $result3 = ProductComparisonService::analyzeComparison([$products3[0]], $opts);
            $this->assert($result3['products'][0]['eligible'] === false, "No Flash Sale -> base price > budget -> eligible=false");
            $this->assert((float)$result3['products'][0]['effective_price'] === 20000000.0, "effective_price = base price");
            
            // Test future flash sale
            $this->db->exec("INSERT INTO flash_sales (title, slug, start_time, end_time, status) VALUES ('FS3', 'fs-3', NOW() + INTERVAL 1 HOUR, NOW() + INTERVAL 2 HOUR, 'active')");
            $fsId3 = (int)$this->db->lastInsertId();
            $stmtFS3 = $this->db->prepare("INSERT INTO flash_sale_items (flash_sale_id, product_id, discount_price, allocation_quantity, sold_quantity) VALUES (?, ?, 15000000, 10, 0)");
            $stmtFS3->execute([$fsId3, $productId2]);
            $products4 = $model->getProductsByIds([$productId2]);
            $this->assert($products4[0]['flash_sale'] === null, "Future Flash Sale -> not hydrated (null)");

            // Test model without category slug
            $this->db->exec("UPDATE categories SET slug = '' WHERE id = {$catId2}");
            $products5 = $model->getProductsByIds([$productId2]);
            $this->assert($products5[0]['category_slug'] === '', "Empty slug hydrated from DB");
            $result5 = ProductComparisonService::analyzeComparison([$products5[0]], ['expected_count' => 1]);
            $this->assert(empty($result5['products']) && $result5['winner'] === null, "Empty category slug -> eligible=false (products array empty)");
            
            $this->db->rollBack();
            $this->log("[PASS] Database integration tests completed successfully");
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->assert(false, "Database integration threw exception: " . $e->getMessage());
        }
    }
}

// Run
$suite = new CP04CompareDataContractTest();
$suite->run();
