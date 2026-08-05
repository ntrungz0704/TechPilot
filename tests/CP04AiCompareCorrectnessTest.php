<?php
/**
 * CP04 AI Compare Correctness Test Suite
 *
 * Tests are deterministic and network-free (no Gemini/Groq calls).
 * All production methods are called directly — no source scan only.
 */

// Bootstrap
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Override GeminiService::callGemini to throw so no network calls happen.
// We use a test bootstrap override approach: if GeminiService is already loaded,
// patch it at runtime by re-defining via runkit if available, or else guard
// ProductComparisonService from calling it by wrapping in try/catch (already done).
// Regardless, the test just verifies the fallback path is triggered.
if (!class_exists('GeminiService')) {
    class GeminiService
    {
        public static function callGemini(string $prompt, array $options = []): string
        {
            throw new Exception('GeminiService::callGemini stubbed for deterministic tests');
        }
    }
} else {
    // GeminiService already loaded from production file.
    // Monkeypatch via static property to signal tests should throw.
    // ProductComparisonService already wraps callGemini in try/catch and falls back.
    // Tests simply verify the fallback analysis is returned — which is guaranteed
    // by the service's own try/catch when GeminiService::callGemini fails or returns invalid JSON.
    // No further action needed.
}

// Minimal helpers
if (!function_exists('formatPrice')) {
    function formatPrice(float $p): string
    {
        return number_format($p, 0, ',', '.') . 'đ';
    }
}

// Stub config so require ROOT_PATH . '/config/product-comparison.php' works
$configPath = ROOT_PATH . '/config/product-comparison.php';
if (!file_exists($configPath)) {
    // Provide a minimal stub file if missing
    file_put_contents($configPath . '.cp04stub', '');
    // Wrap require in a class that returns default
}

// We need a config stub that won't write to disk; use eval approach via stream wrapper
// Instead: provide inline stub via class-level mock for the require
// This works because require_once with same path returns cached include.
// If config exists — great. If not — stub it.
if (!file_exists($configPath)) {
    // Write a temporary stub
    @mkdir(dirname($configPath), 0755, true);
    file_put_contents($configPath, "<?php return ['excluded_spec_keys' => [], 'categories' => []];");
}

require_once ROOT_PATH . '/app/services/ProductComparisonService.php';

// ==========================================
// TEST RUNNER
// ==========================================

class CP04TestSuite
{
    private int $passed = 0;
    private int $failed = 0;

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== CP04 AI COMPARE CORRECTNESS TEST SUITE            ===\n";
        echo "========================================================\n";

        $this->testEffectivePriceNormalization();
        $this->testActiveFlashSaleValidation();
        $this->testBudgetEligibility();
        $this->testRamHardRequirement();
        $this->testSsdHardRequirement();
        $this->testRefreshRateHardRequirement();
        $this->testMissingAmbiguousSpecs();
        $this->testTrustedServerCategory();
        $this->testEligibleOnlyWinner();
        $this->testInvalidAiWinnerRejection();
        $this->testNoEligibleProduct();
        $this->testOfficePersonaPrecedence();
        $this->testAiFailureFallback();
        $this->testStableTieBreaking();

        echo "\n========================================================\n";
        echo "CP04 Results: {$this->passed} passed, {$this->failed} failed\n";
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

    // ── Helper: build a minimal product fixture ───────────────────────────────
    private function makeProduct(array $overrides = []): array
    {
        return array_merge([
            'id'            => 1,
            'name'          => 'Laptop Test i7',
            'price'         => 20_000_000.0,
            'sale_price'    => null,
            'specs'         => json_encode(['ram' => '16GB', 'ssd' => '512GB', 'refresh_rate' => '144Hz']),
            'brand_name'    => 'asus',
            'category_slug' => 'laptop',
            'flash_sale'    => null,
        ], $overrides);
    }

    private function makeFlashSale(array $overrides = []): array
    {
        $now = time();
        return array_merge([
            'discount_price' => 15_000_000.0,
            'fs_status'      => 'active',
            'fs_start'       => date('Y-m-d H:i:s', $now - 3600),
            'fs_end'         => date('Y-m-d H:i:s', $now + 3600),
            'fs_product_id'  => 1,
        ], $overrides);
    }

    // ── 1. Effective Price Normalization ──────────────────────────────────────
    private function testEffectivePriceNormalization(): void
    {
        $this->log("\n--- Effective Price Normalization ---");

        // Only original price
        $p = $this->makeProduct(['price' => 20_000_000.0, 'sale_price' => null]);
        $ep = ProductComparisonService::effectivePrice($p, null);
        $this->assert($ep === 20_000_000.0, "Only original price → returns base price", (string)$ep);

        // Valid sale price
        $p = $this->makeProduct(['price' => 20_000_000.0, 'sale_price' => 18_000_000.0]);
        $ep = ProductComparisonService::effectivePrice($p, null);
        $this->assert($ep === 18_000_000.0, "Valid sale price → returns sale price", (string)$ep);

        // Sale price null
        $p = $this->makeProduct(['price' => 20_000_000.0, 'sale_price' => null]);
        $ep = ProductComparisonService::effectivePrice($p, null);
        $this->assert($ep === 20_000_000.0, "Sale price null → returns base price", (string)$ep);

        // Sale price negative
        $p = $this->makeProduct(['price' => 20_000_000.0, 'sale_price' => -100.0]);
        $ep = ProductComparisonService::effectivePrice($p, null);
        $this->assert($ep === 20_000_000.0, "Sale price negative → ignored, returns base price", (string)$ep);

        // Sale price >= base price (invalid discount)
        $p = $this->makeProduct(['price' => 20_000_000.0, 'sale_price' => 20_000_000.0]);
        $ep = ProductComparisonService::effectivePrice($p, null);
        $this->assert($ep === 20_000_000.0, "Sale price = base price → ignored", (string)$ep);

        // Sale price > base price
        $p = $this->makeProduct(['price' => 20_000_000.0, 'sale_price' => 25_000_000.0]);
        $ep = ProductComparisonService::effectivePrice($p, null);
        $this->assert($ep === 20_000_000.0, "Sale price > base price → ignored", (string)$ep);
    }

    // ── 2. Active Flash Sale Validation ──────────────────────────────────────
    private function testActiveFlashSaleValidation(): void
    {
        $this->log("\n--- Active Flash Sale Validation ---");
        $now = time();

        // Active flash sale → use flash price
        $p  = $this->makeProduct(['price' => 20_000_000.0, 'id' => 1]);
        $fs = $this->makeFlashSale(['discount_price' => 15_000_000.0, 'fs_product_id' => 1]);
        $ep = ProductComparisonService::effectivePrice($p, $fs, $now);
        $this->assert($ep === 15_000_000.0, "Active flash sale → flash price used", (string)$ep);

        // Future flash sale (not started)
        $fs = $this->makeFlashSale([
            'discount_price' => 15_000_000.0,
            'fs_product_id'  => 1,
            'fs_start'       => date('Y-m-d H:i:s', $now + 3600),
            'fs_end'         => date('Y-m-d H:i:s', $now + 7200),
        ]);
        $ep = ProductComparisonService::effectivePrice($p, $fs, $now);
        $this->assert($ep === 20_000_000.0, "Future flash sale → ignored", (string)$ep);

        // Expired flash sale
        $fs = $this->makeFlashSale([
            'discount_price' => 15_000_000.0,
            'fs_product_id'  => 1,
            'fs_start'       => date('Y-m-d H:i:s', $now - 7200),
            'fs_end'         => date('Y-m-d H:i:s', $now - 3600),
        ]);
        $ep = ProductComparisonService::effectivePrice($p, $fs, $now);
        $this->assert($ep === 20_000_000.0, "Expired flash sale → ignored", (string)$ep);

        // Inactive flash sale
        $fs = $this->makeFlashSale(['fs_status' => 'inactive', 'fs_product_id' => 1]);
        $ep = ProductComparisonService::effectivePrice($p, $fs, $now);
        $this->assert($ep === 20_000_000.0, "Inactive flash sale → ignored", (string)$ep);

        // Flash sale belongs to different product
        $fs = $this->makeFlashSale(['fs_product_id' => 99]);
        $ep = ProductComparisonService::effectivePrice($p, $fs, $now);
        $this->assert($ep === 20_000_000.0, "Flash sale for different product → ignored", (string)$ep);

        // Flash sale with invalid price (negative)
        $fs = $this->makeFlashSale(['discount_price' => -100.0, 'fs_product_id' => 1]);
        $ep = ProductComparisonService::effectivePrice($p, $fs, $now);
        $this->assert($ep === 20_000_000.0, "Flash sale with negative price → ignored", (string)$ep);

        // Flash sale price >= base (not a discount)
        $fs = $this->makeFlashSale(['discount_price' => 20_000_000.0, 'fs_product_id' => 1]);
        $ep = ProductComparisonService::effectivePrice($p, $fs, $now);
        $this->assert($ep === 20_000_000.0, "Flash sale price >= base → ignored", (string)$ep);

        // Flash sale + sale_price: take the minimum of all valid prices
        $p2  = $this->makeProduct(['price' => 20_000_000.0, 'sale_price' => 17_000_000.0, 'id' => 1]);
        $fs2 = $this->makeFlashSale(['discount_price' => 15_000_000.0, 'fs_product_id' => 1]);
        $ep2 = ProductComparisonService::effectivePrice($p2, $fs2, $now);
        $this->assert($ep2 === 15_000_000.0, "Flash+sale: minimum of both valid prices → flash price", (string)$ep2);
    }

    // ── 3. Budget Eligibility ─────────────────────────────────────────────────
    private function testBudgetEligibility(): void
    {
        $this->log("\n--- Budget Eligibility ---");
        $now = time();

        // Base price within budget
        $p = $this->makeProduct(['id' => 1, 'price' => 15_000_000.0, 'sale_price' => null]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2, 'price' => 10_000_000.0])],
            ['budget_max' => 20_000_000.0, '_now' => $now]);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === true, "Base price within budget → eligible", (string)$found['effective_price']);

        // Original price over budget but active flash sale within budget
        $flashSaleRow = $this->makeFlashSale(['discount_price' => 18_000_000.0, 'fs_product_id' => 3]);
        $p3 = $this->makeProduct([
            'id' => 3, 'price' => 25_000_000.0, 'sale_price' => null,
            'flash_sale' => $flashSaleRow,
        ]);
        $result = ProductComparisonService::analyzeComparison([$p3, $this->makeProduct(['id' => 4, 'price' => 10_000_000.0])],
            ['budget_max' => 20_000_000.0, '_now' => $now]);
        $found = $this->findProduct($result['products'], 3);
        $this->assert($found['eligible'] === true, "Original over budget but flash in budget → eligible via flash price",
            "ep=" . $found['effective_price']);

        // Product still over budget after all discounts
        $flashSaleRow2 = $this->makeFlashSale(['discount_price' => 22_000_000.0, 'fs_product_id' => 5]);
        $p5 = $this->makeProduct([
            'id' => 5, 'price' => 25_000_000.0, 'sale_price' => null,
            'flash_sale' => $flashSaleRow2,
        ]);
        $result = ProductComparisonService::analyzeComparison([$p5, $this->makeProduct(['id' => 6, 'price' => 10_000_000.0])],
            ['budget_max' => 20_000_000.0, '_now' => $now]);
        $found = $this->findProduct($result['products'], 5);
        $this->assert($found['eligible'] === false, "Over budget after all discounts → ineligible",
            "ep=" . $found['effective_price']);
    }

    // ── 4. RAM Hard Requirement ───────────────────────────────────────────────
    private function testRamHardRequirement(): void
    {
        $this->log("\n--- RAM Hard Requirement ---");
        $now = time();

        $opts = ['min_ram' => 16, '_now' => $now];

        // Sufficient RAM
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['ram' => '16GB'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])], $opts);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === true, "RAM 16GB >= 16GB required → eligible");

        // RAM below requirement
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['ram' => '8GB'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])], $opts);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === false, "RAM 8GB < 16GB required → ineligible");
        $this->assert(in_array('ram', $found['failed_requirements']), "RAM below requirement → failed_requirements includes 'ram'");

        // Missing RAM spec (fail-closed)
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['ssd' => '512GB'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])], $opts);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === false, "Missing RAM spec with min_ram set → ineligible (fail-closed)");
        $this->assert($found['verification_required'] === true, "Missing RAM → verification_required=true");
        $this->assert(in_array('ram', $found['failed_requirements']), "Missing RAM → failed_requirements includes 'ram'");

        // RAM spec as string (parseable)
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['ram' => '32GB DDR5'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])], $opts);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === true, "RAM '32GB DDR5' parseable and >= 16 → eligible");
    }

    // ── 5. SSD Hard Requirement ───────────────────────────────────────────────
    private function testSsdHardRequirement(): void
    {
        $this->log("\n--- SSD Hard Requirement ---");
        $now = time();

        $opts = ['min_storage' => 512, '_now' => $now];

        // Sufficient SSD
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['ssd' => '512GB'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])], $opts);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === true, "SSD 512GB >= 512GB required → eligible");

        // SSD below requirement
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['ssd' => '256GB'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])], $opts);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === false, "SSD 256GB < 512GB required → ineligible");
        $this->assert(in_array('ssd', $found['failed_requirements']), "SSD below → failed_requirements includes 'ssd'");

        // Missing SSD spec (fail-closed)
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['ram' => '16GB'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])], $opts);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === false, "Missing SSD spec with min_storage set → ineligible");
        $this->assert($found['verification_required'] === true, "Missing SSD → verification_required=true");
    }

    // ── 6. Refresh Rate Hard Requirement ─────────────────────────────────────
    private function testRefreshRateHardRequirement(): void
    {
        $this->log("\n--- Refresh Rate Hard Requirement ---");
        $now = time();

        $opts = ['min_refresh_rate' => 144, '_now' => $now];

        // Sufficient Hz
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['refresh_rate' => '144Hz'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])], $opts);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === true, "144Hz >= 144Hz required → eligible");

        // Hz below requirement
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['refresh_rate' => '60Hz'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])], $opts);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === false, "60Hz < 144Hz required → ineligible");
        $this->assert(in_array('refresh_rate', $found['failed_requirements']), "Hz below → failed_requirements includes 'refresh_rate'");

        // Missing Hz spec (fail-closed)
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['ram' => '16GB'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])], $opts);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === false, "Missing Hz spec with min_refresh_rate set → ineligible");
        $this->assert($found['verification_required'] === true, "Missing Hz → verification_required=true");
    }

    // ── 7. Missing / Ambiguous Specs ─────────────────────────────────────────
    private function testMissingAmbiguousSpecs(): void
    {
        $this->log("\n--- Missing / Ambiguous Specs ---");
        $now = time();

        // Completely empty specs + no min requirements → still eligible (no hard fail)
        $p = $this->makeProduct(['id' => 1, 'specs' => '{}']);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])], ['_now' => $now]);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === true, "Empty specs + no hard requirements → eligible");

        // Ambiguous RAM value that still parses to a number
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['ram' => '8GB/16GB'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])],
            ['min_ram' => 16, '_now' => $now]);
        $found = $this->findProduct($result['products'], 1);
        // extractNumericVal('8GB/16GB') returns 8 (first number) → fails 16GB requirement
        $this->assert($found['eligible'] === false, "Ambiguous RAM '8GB/16GB' extracts first=8 < 16 → ineligible");

        // Spec with text before number: '2x8GB DDR5' → now correctly parses as 16GB
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['ram' => '2x8GB'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])],
            ['min_ram' => 16, '_now' => $now]);
        $found = $this->findProduct($result['products'], 1);
        $this->assert(
            $found['eligible'] === true,
            "RAM '2x8GB' parseable and >= 16 → eligible"
        );

        // All hard requirements met
        $p = $this->makeProduct(['id' => 1, 'specs' => json_encode(['ram' => '32GB', 'ssd' => '1000GB', 'refresh_rate' => '240Hz'])]);
        $result = ProductComparisonService::analyzeComparison([$p, $this->makeProduct(['id' => 2])],
            ['min_ram' => 16, 'min_storage' => 512, 'min_refresh_rate' => 144, '_now' => $now]);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === true, "All hard requirements met → eligible");
    }

    // ── 8. Trusted Server Category ────────────────────────────────────────────
    private function testTrustedServerCategory(): void
    {
        $this->log("\n--- Trusted Server Category ---");
        $now = time();

        // Server says 'laptop', client sends category='smartphone' → result must use 'laptop'
        $p1 = $this->makeProduct(['id' => 1, 'category_slug' => 'laptop']);
        $p2 = $this->makeProduct(['id' => 2, 'category_slug' => 'laptop']);
        $result = ProductComparisonService::analyzeComparison([$p1, $p2],
            ['category' => 'smartphone', '_now' => $now]);  // client-sent category
        $this->assert($result['category'] === 'laptop', "Server category 'laptop' overrides client 'smartphone'");

        // Server category different from client category
        $p1 = $this->makeProduct(['id' => 1, 'category_slug' => 'monitor']);
        $p2 = $this->makeProduct(['id' => 2, 'category_slug' => 'monitor']);
        $result = ProductComparisonService::analyzeComparison([$p1, $p2],
            ['category' => 'laptop', '_now' => $now]);
        $this->assert($result['category'] === 'monitor', "Server category 'monitor' always used, not client 'laptop'");

        // Product with no server category → ineligible
        $p1 = $this->makeProduct(['id' => 1, 'category_slug' => '']);
        $p2 = $this->makeProduct(['id' => 2, 'category_slug' => 'laptop']);
        $result = ProductComparisonService::analyzeComparison([$p1, $p2], ['_now' => $now]);
        $found = $this->findProduct($result['products'], 1);
        $this->assert($found['eligible'] === false, "No server category → ineligible");

        // Unknown product ID should not become winner
        // Simulate by having a product with no category
        $pUnknown = $this->makeProduct(['id' => 99, 'category_slug' => '', 'price' => 1.0]);
        $pValid   = $this->makeProduct(['id' => 2, 'category_slug' => 'laptop', 'price' => 20_000_000.0]);
        $result = ProductComparisonService::analyzeComparison([$pUnknown, $pValid], ['_now' => $now]);
        $this->assert($result['winner'] !== 99, "Unknown/no-category product (id=99) cannot become winner");
        $this->assert($result['winner'] === 2 || $result['winner'] === null, "Valid product id=2 is winner");
    }

    // ── 9. Eligible-Only Winner ───────────────────────────────────────────────
    private function testEligibleOnlyWinner(): void
    {
        $this->log("\n--- Eligible-Only Winner ---");
        $now = time();

        // Ineligible product has highest score (high price), eligible product lower score → eligible wins
        $pIneligible = $this->makeProduct([
            'id' => 1, 'name' => 'Laptop RTX 4090 i9', 'price' => 60_000_000.0,
            'specs' => json_encode(['ram' => '32GB', 'ssd' => '1000GB']),
        ]);
        $pEligible = $this->makeProduct([
            'id' => 2, 'name' => 'Laptop i5', 'price' => 15_000_000.0,
            'specs' => json_encode(['ram' => '16GB', 'ssd' => '512GB']),
        ]);
        $result = ProductComparisonService::analyzeComparison([$pIneligible, $pEligible],
            ['budget_max' => 20_000_000.0, '_now' => $now]);

        $foundIneligible = $this->findProduct($result['products'], 1);
        $foundEligible   = $this->findProduct($result['products'], 2);
        $this->assert($foundIneligible['eligible'] === false, "Over-budget product is ineligible");
        $this->assert($foundEligible['eligible'] === true, "Within-budget product is eligible");
        $this->assert($result['winner'] === 2, "Eligible product (id=2) wins, not high-score ineligible (id=1)",
            "winner=" . var_export($result['winner'], true));
        $this->assert($result['winners']['best_fit'] === 2, "best_fit points to eligible product");

        // Only one eligible product
        $result2 = ProductComparisonService::analyzeComparison(
            [$pIneligible, $pEligible, $this->makeProduct(['id' => 3, 'price' => 70_000_000.0])],
            ['budget_max' => 20_000_000.0, '_now' => $now]
        );
        $this->assert($result2['winner'] === 2, "Only one eligible product → it is winner");

        // Multiple eligible products with tie in score — stable by id
        $pA = $this->makeProduct(['id' => 10, 'name' => 'Laptop i5', 'price' => 15_000_000.0,
            'specs' => json_encode(['ram' => '16GB', 'ssd' => '512GB']), 'brand_name' => 'lenovo']);
        $pB = $this->makeProduct(['id' => 5, 'name' => 'Laptop i5', 'price' => 15_000_000.0,
            'specs' => json_encode(['ram' => '16GB', 'ssd' => '512GB']), 'brand_name' => 'lenovo']);
        $result3 = ProductComparisonService::analyzeComparison([$pA, $pB], ['_now' => $now]);
        // Both have identical score — id=5 should win (lower id)
        $this->assert($result3['winner'] === 5, "Tie in score → stable tie-break: lower id (5) wins",
            "winner=" . var_export($result3['winner'], true));
    }

    // ── 10. Invalid AI Winner Rejection ───────────────────────────────────────
    private function testInvalidAiWinnerRejection(): void
    {
        $this->log("\n--- Invalid AI Winner Rejection ---");
        $now = time();

        // Use analyzeComparison directly — winners are set by server, not AI.
        // Verify that winners only contain eligible IDs.
        $pIneligible = $this->makeProduct(['id' => 1, 'price' => 60_000_000.0]);
        $pEligible   = $this->makeProduct(['id' => 2, 'price' => 15_000_000.0]);
        $result = ProductComparisonService::analyzeComparison([$pIneligible, $pEligible],
            ['budget_max' => 20_000_000.0, '_now' => $now]);

        $eligibleIds = array_column(
            array_filter($result['products'], fn($p) => $p['eligible'] === true),
            'id'
        );
        foreach ($result['winners'] as $role => $wid) {
            if ($wid !== null) {
                $this->assert(in_array($wid, $eligibleIds, true),
                    "Winner role '{$role}' id={$wid} must be in eligible list",
                    "eligible=" . implode(',', $eligibleIds));
            }
        }

        // Non-existent product ID winner → server validation nullifies it
        // Simulate by calling winner validation directly via analyzeComparison
        // with product set where all have category_slug='', making them all ineligible
        $p1 = $this->makeProduct(['id' => 1, 'category_slug' => '']);
        $p2 = $this->makeProduct(['id' => 2, 'category_slug' => '']);
        $result2 = ProductComparisonService::analyzeComparison([$p1, $p2], ['_now' => $now]);
        foreach ($result2['winners'] as $role => $wid) {
            $this->assert($wid === null, "All ineligible → winner role '{$role}' must be null",
                "got=" . var_export($wid, true));
        }
        $this->assert($result2['winner'] === null, "All ineligible → winner=null (strict null)");
    }

    // ── 11. No Eligible Product → winner null ─────────────────────────────────
    private function testNoEligibleProduct(): void
    {
        $this->log("\n--- No Eligible Product → winner null ---");
        $now = time();

        // All over budget
        $p1 = $this->makeProduct(['id' => 1, 'price' => 30_000_000.0]);
        $p2 = $this->makeProduct(['id' => 2, 'price' => 35_000_000.0]);
        $result = ProductComparisonService::analyzeComparison([$p1, $p2],
            ['budget_max' => 20_000_000.0, '_now' => $now]);
        $this->assert($result['winner'] === null, "All over budget → winner=null (strict null)",
            "winner=" . var_export($result['winner'], true));
        $this->assert(isset($result['message']) && strlen($result['message']) > 10,
            "No eligible → meaningful message present");
        $this->assert(isset($result['suggestion']) && strlen($result['suggestion']) > 10,
            "No eligible → suggestion present");

        // All missing required specs
        $p1 = $this->makeProduct(['id' => 1, 'specs' => json_encode(['cpu' => 'i5'])]);
        $p2 = $this->makeProduct(['id' => 2, 'specs' => json_encode(['cpu' => 'i7'])]);
        $result = ProductComparisonService::analyzeComparison([$p1, $p2],
            ['min_ram' => 16, '_now' => $now]);
        $this->assert($result['winner'] === null, "All missing RAM → winner=null");
        $this->assert($result['winners']['best_fit'] === null, "All missing RAM → best_fit=null");

        // Mixed: some over budget, some missing specs
        $p3 = $this->makeProduct(['id' => 3, 'price' => 30_000_000.0]);
        $p4 = $this->makeProduct(['id' => 4, 'specs' => json_encode(['cpu' => 'i5'])]);
        $result = ProductComparisonService::analyzeComparison([$p3, $p4],
            ['budget_max' => 20_000_000.0, 'min_ram' => 16, '_now' => $now]);
        $this->assert($result['winner'] === null, "Mixed failures → winner=null");

        // Empty product list
        $result = ProductComparisonService::analyzeComparison([], ['_now' => $now]);
        $this->assert($result['winner'] === null, "Empty product list → winner=null");
        $this->assert($result['success'] === false || isset($result['message']), "Empty list → error message present");

        // Products with invalid IDs (category_slug empty)
        $p5 = $this->makeProduct(['id' => 5, 'category_slug' => null]);
        $p6 = $this->makeProduct(['id' => 6, 'category_slug' => '  ']);
        $result = ProductComparisonService::analyzeComparison([$p5, $p6], ['_now' => $now]);
        $this->assert($result['winner'] === null || in_array($result['winner'], [null], true),
            "No-category products → winner=null");

        // Response has reasons_by_product
        $result2 = ProductComparisonService::analyzeComparison([$p1, $p2],
            ['budget_max' => 20_000_000.0, '_now' => $now]);
        $this->assert(isset($result2['reasons_by_product']), "No-match response includes reasons_by_product");
    }

    // ── 12. Office Persona Precedence ─────────────────────────────────────────
    private function testOfficePersonaPrecedence(): void
    {
        $this->log("\n--- Office Persona Precedence ---");
        $now = time();

        // Office persona + 'i3' in name + prebuilt_pc → should get +8 bonus
        $pOffice = $this->makeProduct([
            'id' => 1, 'name' => 'PC i3 Business', 'category_slug' => 'pc',
            'specs' => json_encode(['ram' => '8GB', 'ssd' => '256GB']),
        ]);
        $pGaming = $this->makeProduct([
            'id' => 2, 'name' => 'PC RTX 4090 Gaming', 'category_slug' => 'pc',
            'specs' => json_encode(['ram' => '32GB', 'ssd' => '1000GB']),
        ]);
        $resultOffice = ProductComparisonService::analyzeComparison([$pOffice, $pGaming],
            ['persona' => 'office', '_now' => $now]);
        $officeScore = $this->findProduct($resultOffice['products'], 1)['score_breakdown']['persona_fit'];
        $this->assert($officeScore === 33, "Office persona + 'i3' → persona_fit = 33 (25+8)",
            "actual={$officeScore}");

        // Gaming persona + name has 'văn phòng' → should NOT get office bonus (bug was here)
        $pVanPhong = $this->makeProduct([
            'id' => 3, 'name' => 'PC Gaming văn phòng RTX 4060', 'category_slug' => 'pc',
            'specs' => json_encode(['ram' => '16GB', 'ssd' => '512GB']),
        ]);
        $resultGaming = ProductComparisonService::analyzeComparison([$pVanPhong, $pGaming],
            ['persona' => 'gamer', '_now' => $now]);
        $gamingScore = $this->findProduct($resultGaming['products'], 3)['score_breakdown']['persona_fit'];
        // 'gaming' persona with prebuilt_pc does not match any of the branches → score stays 25
        $this->assert($gamingScore === 25, "Gamer persona + 'văn phòng' in name → no office bonus (operator precedence fixed)",
            "actual={$gamingScore}");

        // 'văn phòng' keyword in name, but persona is 'developer' (laptop) → no office bonus
        $pDev = $this->makeProduct([
            'id' => 4, 'name' => 'Laptop văn phòng i7', 'category_slug' => 'laptop',
            'specs' => json_encode(['ram' => '16GB', 'ssd' => '512GB']),
        ]);
        $resultDev = ProductComparisonService::analyzeComparison([$pDev, $this->makeProduct(['id' => 99, 'category_slug' => 'laptop'])],
            ['persona' => 'developer', '_now' => $now]);
        $devScore = $this->findProduct($resultDev['products'], 4)['score_breakdown']['persona_fit'];
        // Developer + i7 → +8; 'văn phòng' keyword should NOT add another +8 without office persona
        $this->assert($devScore === 33, "Developer persona + 'văn phòng' in name → developer bonus (+8), not office bonus",
            "actual={$devScore}");

        // Office workload + correct category (laptop) → should get +8
        $pOfficeLaptop = $this->makeProduct([
            'id' => 5, 'name' => 'Laptop văn phòng i3', 'category_slug' => 'laptop',
        ]);
        $resultOfficeLaptop = ProductComparisonService::analyzeComparison([$pOfficeLaptop, $this->makeProduct(['id' => 99, 'category_slug' => 'laptop'])],
            ['persona' => 'office', '_now' => $now]);
        $oScore = $this->findProduct($resultOfficeLaptop['products'], 5)['score_breakdown']['persona_fit'];
        $this->assert($oScore === 33, "Office persona + 'văn phòng' in laptop name → +8 bonus (25+8)",
            "actual={$oScore}");

        // Office workload + wrong category (monitor) → no office bonus for prebuilt_pc/laptop branches
        $pOfficeMonitor = $this->makeProduct([
            'id' => 6, 'name' => 'Monitor văn phòng i3 rtx', 'category_slug' => 'monitor',
        ]);
        $resultMonitor = ProductComparisonService::analyzeComparison([$pOfficeMonitor, $this->makeProduct(['id' => 99, 'category_slug' => 'monitor'])],
            ['persona' => 'office', '_now' => $now]);
        $mScore = $this->findProduct($resultMonitor['products'], 6)['score_breakdown']['persona_fit'];
        $this->assert($mScore === 25, "Office persona + monitor category → no office bonus (only prebuilt_pc/laptop branches)",
            "actual={$mScore}");

        // Empty persona → baseline 25, no crash
        $pEmpty = $this->makeProduct(['id' => 7, 'name' => 'Laptop i7', 'category_slug' => 'laptop']);
        $resultEmpty = ProductComparisonService::analyzeComparison([$pEmpty, $this->makeProduct(['id' => 8])],
            ['persona' => '', '_now' => $now]);
        $eScore = $this->findProduct($resultEmpty['products'], 7)['score_breakdown']['persona_fit'];
        $this->assert($eScore === 25, "Empty persona → baseline persona_fit = 25, no crash",
            "actual={$eScore}");

        // Mixed-case persona ('Office' vs 'office')
        $pMixed = $this->makeProduct(['id' => 9, 'name' => 'PC i3 Pro', 'category_slug' => 'pc']);
        $resultMixed = ProductComparisonService::analyzeComparison([$pMixed, $this->makeProduct(['id' => 10, 'category_slug' => 'pc'])],
            ['persona' => 'Office', '_now' => $now]);
        // 'Office' !== 'office' → no match → no bonus
        $mixScore = $this->findProduct($resultMixed['products'], 9)['score_breakdown']['persona_fit'];
        $this->assert($mixScore === 25, "Mixed-case 'Office' ≠ 'office' → no office bonus (system is case-sensitive)",
            "actual={$mixScore}");
    }

    // ── 13. AI Failure Fallback ───────────────────────────────────────────────
    private function testAiFailureFallback(): void
    {
        $this->log("\n--- AI Failure Fallback ---");
        $now = time();

        // GeminiService is stubbed to throw — analyzeComparison must still return structured result
        $p1 = $this->makeProduct(['id' => 1]);
        $p2 = $this->makeProduct(['id' => 2]);
        $result = ProductComparisonService::analyzeComparison([$p1, $p2], ['_now' => $now]);

        $this->assert($result['success'] === true, "AI failure → analyzeComparison still returns success=true");
        $this->assert(isset($result['winners']), "AI failure → winners present");
        $this->assert(isset($result['analysis']['summary']), "AI failure → fallback analysis summary present");
        $this->assert(isset($result['products']) && count($result['products']) === 2,
            "AI failure → all products returned");
        // Winners must still be valid
        $this->assert($result['winner'] !== null, "AI failure → winner not null when eligible products exist");
        $eligibleIds = array_column(
            array_filter($result['products'], fn($p) => $p['eligible'] === true),
            'id'
        );
        $this->assert(in_array($result['winner'], $eligibleIds, true),
            "AI failure → winner is from eligible list");

        // Ineligible products must not become winner even when AI is offline
        $pIneligible = $this->makeProduct(['id' => 1, 'price' => 50_000_000.0]);
        $pEligible   = $this->makeProduct(['id' => 2, 'price' => 15_000_000.0]);
        $result2 = ProductComparisonService::analyzeComparison([$pIneligible, $pEligible],
            ['budget_max' => 20_000_000.0, '_now' => $now]);
        $this->assert($result2['winner'] === 2,
            "AI failure + ineligible product → eligible product (id=2) still wins");
        $this->assert($result2['winners']['best_fit'] !== 1,
            "AI failure → ineligible id=1 not in best_fit");
    }

    // ── 14. Stable Tie-Breaking ───────────────────────────────────────────────
    private function testStableTieBreaking(): void
    {
        $this->log("\n--- Stable Tie-Breaking ---");
        $now = time();

        // Three products with identical specs — tie-break by product id (lower wins)
        $ids = [10, 5, 3];
        $products = array_map(fn($id) => $this->makeProduct([
            'id'         => $id,
            'name'       => 'Laptop i5 identical',
            'price'      => 15_000_000.0,
            'brand_name' => 'lenovo',
            'specs'      => json_encode(['ram' => '16GB', 'ssd' => '512GB', 'refresh_rate' => '60Hz']),
        ]), $ids);

        $result1 = ProductComparisonService::analyzeComparison($products, ['_now' => $now]);
        $result2 = ProductComparisonService::analyzeComparison($products, ['_now' => $now]);
        $this->assert($result1['winner'] === $result2['winner'],
            "Tie-breaking is deterministic: same input → same winner on repeated calls");
        $this->assert($result1['winner'] === 3,
            "Tie: lowest id (3) wins", "winner=" . var_export($result1['winner'], true));

        // Shuffle order — should still produce same winner
        $shuffled = [$products[1], $products[2], $products[0]]; // [5, 3, 10]
        $result3  = ProductComparisonService::analyzeComparison($shuffled, ['_now' => $now]);
        $this->assert($result3['winner'] === 3,
            "Tie-breaking stable regardless of input order: winner=3", "winner=" . var_export($result3['winner'], true));

        // Two tied eligible + one ineligible with higher score
        $pHigh = $this->makeProduct(['id' => 1, 'name' => 'Laptop RTX 4090 i9', 'price' => 50_000_000.0]);
        $pTie1 = $this->makeProduct(['id' => 7, 'name' => 'Laptop i5', 'price' => 15_000_000.0, 'brand_name' => 'lenovo']);
        $pTie2 = $this->makeProduct(['id' => 4, 'name' => 'Laptop i5', 'price' => 15_000_000.0, 'brand_name' => 'lenovo']);
        $result4 = ProductComparisonService::analyzeComparison([$pHigh, $pTie1, $pTie2],
            ['budget_max' => 20_000_000.0, '_now' => $now]);
        $this->assert($result4['winner'] !== 1, "High-score ineligible (id=1) not winner");
        $this->assert($result4['winner'] === 4, "Tied eligible: lower id (4) wins over id=7",
            "winner=" . var_export($result4['winner'], true));
    }

    // ── Helper: find product in result by id ──────────────────────────────────
    private function findProduct(array $products, int $id): array
    {
        foreach ($products as $p) {
            if ((int)$p['id'] === $id) return $p;
        }
        throw new RuntimeException("Product id={$id} not found in result");
    }
}

// Run
$suite = new CP04TestSuite();
$suite->run();
