<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/services/CatalogGroupService.php';
require_once ROOT_PATH . '/app/services/CategoryMenuService.php';
require_once ROOT_PATH . '/app/models/Product.php';

class CatalogGroupTest
{
    private array $results = [];

    public function runAll(): bool
    {
        echo "==================================================\n";
        echo "RUNNING CHECKPOINT 1 — CATALOG GROUP INTEGRATION TESTS\n";
        echo "==================================================\n\n";

        // Snapshot DB count before tests to verify 0 mutations
        $pdo = Database::getConnection();
        $initialCatCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $initialProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        $this->testVirtualGroupProductCounts();
        $this->testNetworkingIsHiddenAndNotReady();
        $this->testParentCategoryWithChildProductsDisplayed();
        $this->testNoDuplicateBrands();
        $this->testNoZeroCountSubgroups();
        $this->testSearchAliasLinhKien();
        $this->testDatabaseUnavailableFallback();
        $this->testZeroDatabaseMutations($initialCatCount, $initialProdCount);

        echo "\n--------------------------------------------------\n";
        $failed = array_filter($this->results, fn($r) => !$r['success']);
        if (empty($failed)) {
            echo "ALL " . count($this->results) . " INTEGRATION TESTS PASSED SUCCESSFULLY!\n";
            echo "--------------------------------------------------\n";
            return true;
        } else {
            echo count($failed) . " TEST(S) FAILED:\n";
            foreach ($failed as $f) {
                echo " - " . $f['name'] . ": " . $f['message'] . "\n";
            }
            echo "--------------------------------------------------\n";
            return false;
        }
    }

    private function record(string $name, bool $success, string $message = ''): void
    {
        $this->results[] = ['name' => $name, 'success' => $success, 'message' => $message];
        $statusStr = $success ? "[PASS]" : "[FAIL]";
        echo sprintf("%-60s %s\n", $name, $statusStr);
        if (!$success && $message) {
            echo "   -> Error: $message\n";
        }
    }

    private function testVirtualGroupProductCounts(): void
    {
        $groups = CatalogGroupService::getAllVirtualGroups();

        $this->record("1. Laptop active runtime count == 74", ($groups['laptop']['product_count'] ?? 0) === 74, "Expected 74, got " . ($groups['laptop']['product_count'] ?? 0));
        $this->record("2. PC & Build PC active runtime count == 36", ($groups['pc']['product_count'] ?? 0) === 36, "Expected 36, got " . ($groups['pc']['product_count'] ?? 0));
        $this->record("3. Linh kiện PC active runtime count == 485", ($groups['pc-linh-kien']['product_count'] ?? 0) === 485, "Expected 485, got " . ($groups['pc-linh-kien']['product_count'] ?? 0));
        $this->record("4. Màn hình active runtime count == 10", ($groups['man-hinh']['product_count'] ?? 0) === 10, "Expected 10, got " . ($groups['man-hinh']['product_count'] ?? 0));
        $this->record("5. Gaming Gear active runtime count == 10", ($groups['gaming-gear']['product_count'] ?? 0) === 10, "Expected 10, got " . ($groups['gaming-gear']['product_count'] ?? 0));
        $this->record("6. Thiết bị văn phòng active runtime count == 5", ($groups['office-gear']['product_count'] ?? 0) === 5, "Expected 5, got " . ($groups['office-gear']['product_count'] ?? 0));
    }

    private function testNetworkingIsHiddenAndNotReady(): void
    {
        $groups = CatalogGroupService::getAllVirtualGroups();
        $net = $groups['networking'] ?? [];
        $countZero = ($net['product_count'] ?? -1) === 0;
        $statusNotReady = ($net['status'] ?? '') === 'not_ready';

        $storefront = CategoryMenuService::getActiveMenuTree();
        $notInStorefront = true;
        foreach ($storefront as $item) {
            if ($item['slug'] === 'networking') {
                $notInStorefront = false;
                break;
            }
        }

        $this->record(
            "7. Thiết bị mạng has 0 products and is NOT_READY/HIDDEN",
            $countZero && $statusNotReady && $notInStorefront,
            "Count: {$net['product_count']}, Status: {$net['status']}, InStorefront: " . ($notInStorefront ? 'No' : 'Yes')
        );
    }

    private function testParentCategoryWithChildProductsDisplayed(): void
    {
        $menuTree = CategoryMenuService::getActiveMenuTree();
        $hasLinhKien = false;
        foreach ($menuTree as $item) {
            if ($item['slug'] === 'pc-linh-kien') {
                $hasLinhKien = true;
                break;
            }
        }

        $this->record(
            "8. Parent 'pc-linh-kien' with 0 direct products IS displayed via child products",
            $hasLinhKien,
            "pc-linh-kien was not found in storefront menu tree"
        );
    }

    private function testNoDuplicateBrands(): void
    {
        $groups = CatalogGroupService::getAllVirtualGroups();
        $noDuplicates = true;
        $msg = "";

        foreach ($groups as $key => $g) {
            $brandSlugs = array_map(fn($b) => $b['slug'], $g['brands'] ?? []);
            if (count($brandSlugs) !== count(array_unique($brandSlugs))) {
                $noDuplicates = false;
                $msg = "Duplicate brands found in group $key";
                break;
            }
        }

        $this->record("9. No duplicate brands within any virtual group", $noDuplicates, $msg);
    }

    private function testNoZeroCountSubgroups(): void
    {
        $groups = CatalogGroupService::getAllVirtualGroups();
        $noZeroSubgroups = true;
        $msg = "";

        foreach ($groups as $key => $g) {
            foreach ($g['subgroups'] ?? [] as $sub) {
                if (($sub['product_count'] ?? 0) <= 0) {
                    $noZeroSubgroups = false;
                    $msg = "Group $key contains subgroup {$sub['name']} with 0 products";
                    break 2;
                }
            }
        }

        $this->record("10. No subgroup rendered with count == 0", $noZeroSubgroups, $msg);
    }

    private function testSearchAliasLinhKien(): void
    {
        $productModel = new Product();
        $total = $productModel->countSearch('linh kiện');
        $resolved = CatalogGroupService::getGroupBySlug('linh-kien-pc');

        $success = ($total === 485) && ($resolved !== null) && ($resolved['canonical_slug'] === 'pc-linh-kien');

        $this->record(
            "11. Keyword 'linh kiện' resolves to pc-linh-kien (485 items)",
            $success,
            "Expected 485, got $total"
        );
    }

    private function testDatabaseUnavailableFallback(): void
    {
        $fallback = CatalogGroupService::getFallbackGroups();
        $has7 = count($fallback) === 7;
        $netHidden = ($fallback['networking']['status'] ?? '') === 'not_ready';
        $emptyTree = CategoryMenuService::getActiveMenuTree(); // Should be array

        $this->record(
            "12. Safe fallback when DB unavailable returns 7 defined fallback groups",
            $has7 && $netHidden && is_array($emptyTree),
            "Fallback failure"
        );
    }

    private function testZeroDatabaseMutations(int $initialCatCount, int $initialProdCount): void
    {
        $pdo = Database::getConnection();
        $finalCatCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $finalProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        $pass = ($initialCatCount === $finalCatCount) && ($initialProdCount === $finalProdCount);

        $this->record(
            "13. Zero DB mutations during and after tests",
            $pass,
            "DB counts modified! Cats: $initialCatCount -> $finalCatCount, Prods: $initialProdCount -> $finalProdCount"
        );
    }
}

// Run if executed directly from CLI
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $test = new CatalogGroupTest();
    $passed = $test->runAll();
    exit($passed ? 0 : 1);
}
