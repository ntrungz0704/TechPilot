<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/services/CatalogGroupService.php';
require_once ROOT_PATH . '/app/services/CategoryMenuService.php';
require_once ROOT_PATH . '/app/models/Product.php';
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/controllers/HomeController.php';

class CatalogGroupTest
{
    private array $results = [];

    public function runAll(): bool
    {
        echo "==================================================\n";
        echo "RUNNING CHECKPOINT 1 V2 — CATALOG GROUP INTEGRATION TESTS\n";
        echo "==================================================\n\n";

        // Snapshot DB count before tests to verify 0 mutations
        $pdo = Database::getConnection();
        $initialCatCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $initialProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        $this->testSearchCountLaptop();
        $this->testSearchLaptopPage1Limit24();
        $this->testLaptopSourceSlugsOnly();
        $this->testSearchCountPC();
        $this->testStorefrontMenuLaptopSlug();
        $this->testStorefrontMenuPCSlug();
        $this->testHomeSearchCatLaptopSimulation();
        $this->testVirtualPageTitle();
        $this->testGetByCategorySlugActiveStatusOnly();
        $this->testNoDuplicateMappingInProductPhp();
        $this->testDatabaseUnavailableDependencyInjectionSeam();
        $this->testFallbackGroupsNotReadyStatus();
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
        echo sprintf("%-65s %s\n", $name, $statusStr);
        if (!$success && $message) {
            echo "   -> Error: $message\n";
        }
    }

    private function testSearchCountLaptop(): void
    {
        $productModel = new Product();
        $total = $productModel->countSearch('', 'laptop');
        $this->record("1. countSearch('', 'laptop') == 74", $total === 74, "Expected 74, got $total");
    }

    private function testSearchLaptopPage1Limit24(): void
    {
        $productModel = new Product();
        $prods = $productModel->search('', 'laptop', 24, 0);
        $count = count($prods);
        $this->record("2. search('', 'laptop', 24) returns 24 products on page 1", $count === 24, "Expected 24, got $count");
    }

    private function testLaptopSourceSlugsOnly(): void
    {
        $productModel = new Product();
        $prods = $productModel->search('', 'laptop', 100, 0);
        $validSlugs = ['laptop-gaming', 'laptop-van-phong'];
        $allValid = true;
        $invalidSlugFound = "";

        foreach ($prods as $p) {
            if (!in_array($p['category_slug'], $validSlugs, true)) {
                $allValid = false;
                $invalidSlugFound = $p['category_slug'];
                break;
            }
        }

        $this->record(
            "3. Products returned for 'laptop' belong ONLY to laptop-gaming or laptop-van-phong",
            $allValid && !empty($prods),
            "Found invalid category_slug: $invalidSlugFound"
        );
    }

    private function testSearchCountPC(): void
    {
        $productModel = new Product();
        $total = $productModel->countSearch('', 'pc');
        $this->record("4. countSearch('', 'pc') == 36", $total === 36, "Expected 36, got $total");
    }

    private function testStorefrontMenuLaptopSlug(): void
    {
        $menuTree = CategoryMenuService::getActiveMenuTree();
        $laptopSlug = '';
        foreach ($menuTree as $item) {
            if ($item['id'] === 'laptop') {
                $laptopSlug = $item['slug'];
                break;
            }
        }
        $this->record(
            "5. Storefront Menu item for Laptop has slug 'laptop' (NOT laptop-gaming)",
            $laptopSlug === 'laptop',
            "Expected 'laptop', got '$laptopSlug'"
        );
    }

    private function testStorefrontMenuPCSlug(): void
    {
        $menuTree = CategoryMenuService::getActiveMenuTree();
        $pcSlug = '';
        foreach ($menuTree as $item) {
            if ($item['id'] === 'pc') {
                $pcSlug = $item['slug'];
                break;
            }
        }
        $this->record(
            "6. Storefront Menu item for PC has slug 'pc' (NOT pc-build-san)",
            $pcSlug === 'pc',
            "Expected 'pc', got '$pcSlug'"
        );
    }

    private function testHomeSearchCatLaptopSimulation(): void
    {
        $_GET['cat'] = 'laptop';
        $_GET['q'] = '';
        $productModel = new Product();
        $totalResults = $productModel->countSearch('', $_GET['cat']);

        $this->record(
            "7. home/search?cat=laptop request context has totalResults = 74",
            $totalResults === 74,
            "Expected 74, got $totalResults"
        );
        unset($_GET['cat'], $_GET['q']);
    }

    private function testVirtualPageTitle(): void
    {
        $titleLaptop = CatalogGroupService::getDisplayName('laptop');
        $titlePC = CatalogGroupService::getDisplayName('pc');
        $titleLinhKien = CatalogGroupService::getDisplayName('pc-linh-kien');

        $pass = ($titleLaptop === 'Laptop') && ($titlePC === 'PC & Build PC') && ($titleLinhKien === 'Linh kiện PC');
        $this->record(
            "8. Virtual page titles resolve correctly ('Laptop', 'PC & Build PC', 'Linh kiện PC')",
            $pass,
            "Laptop: '$titleLaptop', PC: '$titlePC', LinhKien: '$titleLinhKien'"
        );
    }

    private function testGetByCategorySlugActiveStatusOnly(): void
    {
        $productModel = new Product();
        $prods = $productModel->getByCategorySlug('laptop-gaming', 50);
        $allActive = true;

        foreach ($prods as $p) {
            if (($p['status'] ?? '') !== 'active') {
                $allActive = false;
                break;
            }
        }

        $this->record(
            "9. getByCategorySlug returns ONLY active status products & categories",
            $allActive && !empty($prods),
            "Inactive product found in results"
        );
    }

    private function testNoDuplicateMappingInProductPhp(): void
    {
        $productPhpContent = file_get_contents(ROOT_PATH . '/app/models/Product.php');
        $hasHardcodedAliasesArray = str_contains($productPhpContent, "'laptop gaming'     => ['laptop-gaming']");
        $usesSingleSourceOfTruth = str_contains($productPhpContent, "CatalogGroupService::getKeywordAliasMap()");

        $this->record(
            "10. No duplicate category mapping array in Product.php",
            !$hasHardcodedAliasesArray && $usesSingleSourceOfTruth,
            "Product.php still contains hardcoded \$aliases array!"
        );
    }

    private function testDatabaseUnavailableDependencyInjectionSeam(): void
    {
        // Set connection provider to return null (simulating DB crash / offline)
        CatalogGroupService::setConnectionProvider(fn() => null);

        $storefrontTree = CategoryMenuService::getActiveMenuTree();
        $allGroupsFallback = CatalogGroupService::getAllVirtualGroups();

        // Restore real DB connection
        CatalogGroupService::setConnectionProvider(null);

        $treeIsEmpty = empty($storefrontTree);
        $groupsCount7 = count($allGroupsFallback) === 7;

        $this->record(
            "11. DB unavailable simulated via connection provider SEAM returns empty menu tree",
            $treeIsEmpty && $groupsCount7,
            "Storefront menu tree was not empty or fallback failed"
        );
    }

    private function testFallbackGroupsNotReadyStatus(): void
    {
        // Set connection provider to return null
        CatalogGroupService::setConnectionProvider(fn() => null);

        $fallbackGroups = CatalogGroupService::getAllVirtualGroups();

        // Restore real DB connection
        CatalogGroupService::setConnectionProvider(null);

        $noneReady = true;
        foreach ($fallbackGroups as $g) {
            if (($g['status'] ?? '') === 'ready' || ($g['product_count'] ?? -1) !== 0) {
                $noneReady = false;
                break;
            }
        }

        $this->record(
            "12. Fallback groups with count 0 MUST NOT have status 'ready'",
            $noneReady,
            "Group found with status 'ready' or product_count > 0 during DB offline"
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
