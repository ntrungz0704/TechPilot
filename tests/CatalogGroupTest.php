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
        echo "RUNNING CHECKPOINT 1 V4 — CATALOG ROUTING & CONTRACT INTEGRATION TESTS\n";
        echo "==================================================\n\n";

        $pdo = Database::getConnection();
        $initialCatCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $initialProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        $this->testCategoryRouteCounts();
        $this->testKeywordSearchTargetedCounts();
        $this->testSubgroupCPULinkAndNoExpansion();
        $this->testExactSourceRouteNoDescendantExpansionTransaction();
        $this->testRealCategoryInactiveTransaction();
        $this->testDataBackedPriceRanges();
        $this->testPageTitlesVirtualVsExact();
        $this->testHomeControllerRouteHeaderTitleParsing();
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

    private function testExactSourceRouteNoDescendantExpansionTransaction(): void
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            // 1. Chèn 1 category con thử nghiệm dưới CPU (category ID 10)
            $stmtCat = $pdo->prepare("INSERT INTO categories (name, slug, parent_id, status) VALUES ('CPU Intel Gen 14', 'cpu-intel-gen14', 10, 'active')");
            $stmtCat->execute();
            $newCatId = (int)$pdo->lastInsertId();

            // 2. Chèn 1 sản phẩm active thử nghiệm vào category con mới này
            $stmtProd = $pdo->prepare("INSERT INTO products (name, slug, category_id, price, status) VALUES ('Test CPU Gen 14', 'test-cpu-gen14', :cat_id, 5000000, 'active')");
            $stmtProd->execute([':cat_id' => $newCatId]);

            $productModel = new Product();
            $cpuSearchCount = $productModel->countSearch('', 'cpu');
            $cpuProds = $productModel->getByCategorySlug('cpu', 100);

            $containsTestProd = false;
            foreach ($cpuProds as $p) {
                if (($p['slug'] ?? '') === 'test-cpu-gen14') {
                    $containsTestProd = true;
                    break;
                }
            }

            $pass = ($cpuSearchCount === 40) && !$containsTestProd;
            $this->record(
                "16. Exact source route 'cpu' locked: does NOT expand descendants in SQL",
                $pass,
                "cpuSearchCount: $cpuSearchCount (expected 40), containsTestProd: " . ($containsTestProd ? 'Yes' : 'No')
            );
        } finally {
            $pdo->rollBack(); // Khôi phục trạng thái CSDL hoàn toàn
        }
    }

    private function testRealCategoryInactiveTransaction(): void
    {
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            // Chuyển category laptop-gaming (ID 1) sang inactive
            $pdo->exec("UPDATE categories SET status = 'inactive' WHERE id = 1");

            $productModel = new Product();
            $gamingCount = $productModel->countSearch('', 'laptop-gaming');
            $laptopGroupCount = $productModel->countSearch('', 'laptop');
            $gamingProds = $productModel->search('', 'laptop-gaming', 50, 0);

            $pass = ($gamingCount === 0) && ($laptopGroupCount === 36) && empty($gamingProds);
            $this->record(
                "17. Real category inactive transaction excludes products from search()",
                $pass,
                "gamingCount: $gamingCount, laptopGroupCount: $laptopGroupCount, gamingProdsCount: " . count($gamingProds)
            );
        } finally {
            $pdo->rollBack();
        }
    }

    private function testDataBackedPriceRanges(): void
    {
        $groups = CatalogGroupService::getAllVirtualGroups();
        $productModel = new Product();
        $allPass = true;
        $errMsg = "";

        foreach ($groups as $gKey => $group) {
            $ranges = $group['price_ranges'] ?? [];

            // 1. Kiểm tra Laptop & PC không được có range rỗng
            if (($gKey === 'laptop' || $gKey === 'pc') && empty($ranges)) {
                $allPass = false;
                $errMsg = "Group $gKey has empty price ranges!";
                break;
            }

            $totalRangeCount = 0;
            foreach ($ranges as $idx => $r) {
                // 2. Mọi price range phải có product_count > 0
                if (($r['product_count'] ?? 0) <= 0) {
                    $allPass = false;
                    $errMsg = "Group $gKey price range '{$r['name']}' has count 0!";
                    break 2;
                }

                // 3. Phân tích query và kiểm tra xem countSearch với min_price/max_price có khớp khai báo
                parse_str($r['query'], $qParams);
                $minP = (float)($qParams['min_price'] ?? 0);
                $maxP = (float)($qParams['max_price'] ?? 0);

                $dbCount = $productModel->countSearch('', $group['virtual_slug'], '', $minP, $maxP);
                if ($dbCount !== $r['product_count']) {
                    $allPass = false;
                    $errMsg = "Group $gKey range '{$r['name']}' query count ($dbCount) != contract product_count ({$r['product_count']})!";
                    break 2;
                }

                $totalRangeCount += $r['product_count'];
            }

            // 4. Tổng số sản phẩm khớp từng range bằng đúng tổng sản phẩm của group (không đếm trùng)
            if ($group['product_count'] > 0 && $totalRangeCount !== $group['product_count']) {
                $allPass = false;
                $errMsg = "Group $gKey range total products ($totalRangeCount) != group total products ({$group['product_count']})!";
                break;
            }
        }

        $this->record(
            "18. Price ranges are data-backed, non-overlapping, and match runtime DB queries",
            $allPass,
            $errMsg
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
            "19. Page titles: Virtual root ('Laptop') vs Exact source ('CPU') resolve correctly",
            $passVirtual && $passExact,
            "Virtual: '$titleVirtualLaptop', Exact CPU: '$titleExactCPU'"
        );
    }

    private function testHomeControllerRouteHeaderTitleParsing(): void
    {
        // 1. Kiểm tra route cat=laptop -> HTML title tag chứa <title>Laptop - TechPilot</title> hoặc <title>Laptop</title>
        $_GET['cat'] = 'laptop';
        $_GET['q'] = '';
        ob_start();
        $controller = new HomeController();
        $controller->search();
        $htmlLaptop = ob_get_clean();

        preg_match('/<title>(.*?)<\/title>/is', $htmlLaptop, $matchesLaptop);
        $titleTextLaptop = trim($matchesLaptop[1] ?? '');
        $passLaptop = str_contains($titleTextLaptop, 'Laptop');

        // 2. Kiểm tra route cat=cpu -> HTML title tag chứa <title>CPU - TechPilot</title> hoặc <title>CPU</title>
        $_GET['cat'] = 'cpu';
        ob_start();
        $controller = new HomeController();
        $controller->search();
        $htmlCPU = ob_get_clean();

        preg_match('/<title>(.*?)<\/title>/is', $htmlCPU, $matchesCPU);
        $titleTextCPU = trim($matchesCPU[1] ?? '');
        $passCPU = str_contains($titleTextCPU, 'CPU') && !str_contains($titleTextCPU, 'Linh kiện PC');

        unset($_GET['cat'], $_GET['q']);

        $this->record(
            "20. HomeController::search() parses exact <title> tags ('Laptop' & 'CPU')",
            $passLaptop && $passCPU,
            "Laptop title: '$titleTextLaptop', CPU title: '$titleTextCPU'"
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
            "21. DB unavailable SEAM returns empty storefront tree & unavailable status",
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
            "22. Zero DB mutations during and after tests",
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
