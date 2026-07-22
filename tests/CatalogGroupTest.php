<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/core/helpers.php';
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
        echo "RUNNING CHECKPOINT 1 V3 — CATALOG ROUTING & CONTRACT INTEGRATION TESTS\n";
        echo "==================================================\n\n";

        $pdo = Database::getConnection();
        $initialCatCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $initialProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        $this->testCategoryRouteCounts();
        $this->testKeywordSearchTargetedCounts();
        $this->testSubgroupCPULinkAndNoExpansion();
        $this->testHomepageSectionSeparation();
        $this->testPageTitlesVirtualVsExact();
        $this->testInactiveCategoryExclusion();
        $this->testHomeControllerRouteExecution();
        $this->testDatabaseUnavailableDependencyInjectionSeam();
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
        echo sprintf("%-68s %s\n", $name, $statusStr);
        if (!$success && $message) {
            echo "   -> Error: $message\n";
        }
    }

    private function testCategoryRouteCounts(): void
    {
        $productModel = new Product();

        $this->record("1. countSearch('', 'laptop') == 74", $productModel->countSearch('', 'laptop') === 74);
        $this->record("2. countSearch('', 'laptop-gaming') == 38", $productModel->countSearch('', 'laptop-gaming') === 38);
        $this->record("3. countSearch('', 'laptop-van-phong') == 36", $productModel->countSearch('', 'laptop-van-phong') === 36);
        $this->record("4. countSearch('', 'pc') == 36", $productModel->countSearch('', 'pc') === 36);
        $this->record("5. countSearch('', 'pc-build-san') == 36", $productModel->countSearch('', 'pc-build-san') === 36);
        $this->record("6. countSearch('', 'cpu') == 40", $productModel->countSearch('', 'cpu') === 40);
        $this->record("7. countSearch('', 'ram') == 80", $productModel->countSearch('', 'ram') === 80);
        $this->record("8. countSearch('', 'vga') == 80", $productModel->countSearch('', 'vga') === 80);
    }

    private function testKeywordSearchTargetedCounts(): void
    {
        $productModel = new Product();

        $this->record("9. Keyword 'laptop' count == 74", $productModel->countSearch('laptop', '') === 74);
        $this->record("10. Keyword 'laptop gaming' count == 38", $productModel->countSearch('laptop gaming', '') === 38);
        $this->record("11. Keyword 'laptop văn phòng' count == 36", $productModel->countSearch('laptop văn phòng', '') === 36);
        $this->record("12. Keyword 'linh kiện' count == 485", $productModel->countSearch('linh kiện', '') === 485);
        $this->record("13. Keyword 'cpu' count == 40", $productModel->countSearch('cpu', '') === 40);
        $this->record("14. Keyword 'card màn hình' count == 80", $productModel->countSearch('card màn hình', '') === 80);
    }

    private function testSubgroupCPULinkAndNoExpansion(): void
    {
        $menuTree = CategoryMenuService::getActiveMenuTree();
        $linhKienGroup = null;
        foreach ($menuTree as $item) {
            if ($item['id'] === 'pc-linh-kien') {
                $linhKienGroup = $item;
                break;
            }
        }

        $cpuSubSlug = '';
        foreach ($linhKienGroup['mega_columns']['Danh mục con'] ?? [] as $sub) {
            if ($sub['name'] === 'CPU') {
                $cpuSubSlug = $sub['slug'];
                break;
            }
        }

        $productModel = new Product();
        $cpuCount = $productModel->countSearch('', 'cpu');

        $this->record(
            "15. Menu subgroup CPU link is cat=cpu & cat=cpu does NOT expand to 485",
            $cpuSubSlug === 'cpu' && $cpuCount === 40,
            "cpuSubSlug: '$cpuSubSlug', cpuCount: $cpuCount"
        );
    }

    private function testHomepageSectionSeparation(): void
    {
        $productModel = new Product();
        $gamingProds = $productModel->getByCategorySlug('laptop-gaming', 100);
        $vpProds = $productModel->getByCategorySlug('laptop-van-phong', 100);

        $gamingContainsVP = false;
        foreach ($gamingProds as $p) {
            if (($p['category_slug'] ?? '') === 'laptop-van-phong') {
                $gamingContainsVP = true;
                break;
            }
        }

        $vpContainsGaming = false;
        foreach ($vpProds as $p) {
            if (($p['category_slug'] ?? '') === 'laptop-gaming') {
                $vpContainsGaming = true;
                break;
            }
        }

        $this->record(
            "16. Homepage sections: Laptop Gaming & Laptop VP are strictly isolated",
            !$gamingContainsVP && !$vpContainsGaming && count($gamingProds) === 38 && count($vpProds) === 36,
            "Cross contamination in homepage categories"
        );
    }

    private function testPageTitlesVirtualVsExact(): void
    {
        $titleVirtualLaptop = CatalogGroupService::getDisplayName('laptop');
        $titleVirtualPC = CatalogGroupService::getDisplayName('pc');
        $titleVirtualLinhKien = CatalogGroupService::getDisplayName('pc-linh-kien');

        $titleExactGaming = CatalogGroupService::getDisplayName('laptop-gaming');
        $titleExactCPU = CatalogGroupService::getDisplayName('cpu');

        $passVirtual = ($titleVirtualLaptop === 'Laptop') && ($titleVirtualPC === 'PC & Build PC') && ($titleVirtualLinhKien === 'Linh kiện PC');
        $passExact = ($titleExactGaming === 'Laptop Gaming') && ($titleExactCPU === 'CPU');

        $this->record(
            "17. Page titles: Virtual root ('Laptop') vs Exact source ('CPU') resolve correctly",
            $passVirtual && $passExact,
            "Virtual: '$titleVirtualLaptop', Exact CPU: '$titleExactCPU'"
        );
    }

    private function testInactiveCategoryExclusion(): void
    {
        $pdo = Database::getConnection();
        // Deactivate category ID 9 (networking) temporarily in memory test context or verify inactive status filtering
        $productModel = new Product();
        $prods = $productModel->search('', 'networking', 10, 0);

        $this->record(
            "18. Inactive category / empty category filtering works correctly in search",
            empty($prods)
        );
    }

    private function testHomeControllerRouteExecution(): void
    {
        $_GET['cat'] = 'laptop';
        $_GET['q'] = '';

        // Execute HomeController search via buffering
        ob_start();
        $controller = new HomeController();
        $controller->search();
        $output = ob_get_clean();

        $pass = str_contains($output, 'Laptop') && !empty($output);

        unset($_GET['cat'], $_GET['q']);

        $this->record(
            "19. HomeController::search() route execution renders Virtual Group page title",
            $pass,
            "Controller rendering failed or title missing"
        );
    }

    private function testDatabaseUnavailableDependencyInjectionSeam(): void
    {
        CatalogGroupService::setConnectionProvider(fn() => null);

        $storefrontTree = CategoryMenuService::getActiveMenuTree();
        $fallbackGroups = CatalogGroupService::getAllVirtualGroups();

        CatalogGroupService::setConnectionProvider(null);

        $pass = empty($storefrontTree) && ($fallbackGroups['laptop']['status'] ?? '') === 'unavailable';

        $this->record(
            "20. DB unavailable SEAM returns empty storefront tree & unavailable status",
            $pass
        );
    }

    private function testZeroDatabaseMutations(int $initialCatCount, int $initialProdCount): void
    {
        $pdo = Database::getConnection();
        $finalCatCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $finalProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        $pass = ($initialCatCount === $finalCatCount) && ($initialProdCount === $finalProdCount);

        $this->record(
            "21. Zero DB mutations during and after tests",
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
