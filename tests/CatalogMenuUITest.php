<?php

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/services/CatalogGroupService.php';
require_once ROOT_PATH . '/app/services/CategoryMenuService.php';

class CatalogMenuUITest
{
    private array $results = [];

    public function runAll(): bool
    {
        echo "==================================================\n";
        echo "RUNNING CHECKPOINT 2 V2 — CATEGORY MENU UI INTEGRATION TESTS\n";
        echo "==================================================\n\n";

        $pdo = Database::getConnection();
        $initialCatCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $initialProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        $this->testStorefrontMenuTreeContracts();
        $this->testHeaderViewMarkupAndQuickCategories();
        $this->testPartialCategoryMegaMenuMarkup();
        $this->testZeroDatabaseMutations($initialCatCount, $initialProdCount);

        echo "\n--------------------------------------------------\n";
        $failed = array_filter($this->results, fn($r) => !$r['success']);
        if (empty($failed)) {
            echo "ALL " . count($this->results) . " UI INTEGRATION TESTS PASSED SUCCESSFULLY!\n";
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

    private function testStorefrontMenuTreeContracts(): void
    {
        $menuTree = CategoryMenuService::getActiveMenuTree();

        $laptopSlug = '';
        $pcSlug = '';
        $cpuSubSlug = '';
        $hasNetworking = false;

        foreach ($menuTree as $group) {
            if ($group['name'] === 'Laptop') {
                $laptopSlug = $group['slug'];
            }
            if ($group['name'] === 'PC & Build PC') {
                $pcSlug = $group['slug'];
            }
            if ($group['name'] === 'Thiết bị mạng' || $group['slug'] === 'networking') {
                $hasNetworking = true;
            }

            if ($group['name'] === 'Linh kiện PC') {
                foreach ($group['mega_columns']['Danh mục con'] ?? [] as $sub) {
                    if (($sub['name'] ?? '') === 'CPU') {
                        $cpuSubSlug = $sub['slug'] ?? '';
                    }
                }
            }
        }

        $this->record("1. Root Laptop menu virtual slug == 'laptop'", $laptopSlug === 'laptop', "Actual: '$laptopSlug'");
        $this->record("2. Root PC menu virtual slug == 'pc'", $pcSlug === 'pc', "Actual: '$pcSlug'");
        $this->record("3. CPU subgroup exact source slug == 'cpu'", $cpuSubSlug === 'cpu', "Actual: '$cpuSubSlug'");
        $this->record("4. Empty Networking group is NOT rendered", !$hasNetworking);
    }

    private function testHeaderViewMarkupAndQuickCategories(): void
    {
        $globalCategoryMenu = CategoryMenuService::getActiveMenuTree();
        $catParam = '';
        $qParam = '';

        ob_start();
        include ROOT_PATH . '/app/views/layouts/header.php';
        $htmlHeader = ob_get_clean();

        // 1. Check mobile quick categories
        $hasMobileLaptop = str_contains($htmlHeader, 'home/search?cat=laptop');
        $hasMobilePC = str_contains($htmlHeader, 'home/search?cat=pc');
        $hasMobileLinhKien = str_contains($htmlHeader, 'home/search?cat=pc-linh-kien');
        $noPCLaptopVPBug = !str_contains($htmlHeader, 'home/search?cat=laptop-van-phong" class="quick-cat-item"');

        $this->record(
            "5. Mobile quick categories map correctly (Laptop=laptop, PC=pc, Linh kiện=pc-linh-kien)",
            $hasMobileLaptop && $hasMobilePC && $hasMobileLinhKien && $noPCLaptopVPBug
        );

        // 2. Check mainNavMenu and mobileCategoryToggle separate ARIA targets
        $hasMainNavTarget = str_contains($htmlHeader, 'aria-controls="mainNavMenu"');
        $hasCategoryDrawerTarget = str_contains($htmlHeader, 'aria-controls="categoryMobileDrawer"') || str_contains($htmlHeader, 'id="mobileCategoryToggle"');

        $this->record(
            "6. Separate triggers for mainNavMenu and category drawer in header markup",
            $hasMainNavTarget && $hasCategoryDrawerTarget
        );

        // 3. Search select uses $globalCategoryMenu without calling CatalogGroupService in view
        $hasSearchSelectVirtualOptions = str_contains($htmlHeader, 'value="laptop"') && str_contains($htmlHeader, 'value="pc"');
        $noDuplicateHydrationInView = !str_contains($htmlHeader, 'CatalogGroupService::getStorefrontGroups()');

        $this->record(
            "7. Header search select uses \$globalCategoryMenu without double hydration",
            $hasSearchSelectVirtualOptions && $noDuplicateHydrationInView
        );
    }

    private function testPartialCategoryMegaMenuMarkup(): void
    {
        $globalCategoryMenu = CategoryMenuService::getActiveMenuTree();
        $isStatic = false;

        ob_start();
        include ROOT_PATH . '/app/views/layouts/partials/category-mega-menu.php';
        $htmlMega = ob_get_clean();

        $hasVirtualLaptop = str_contains($htmlMega, 'home/search?cat=laptop');
        $hasVirtualPC = str_contains($htmlMega, 'home/search?cat=pc');
        $hasExactCPU = str_contains($htmlMega, 'home/search?cat=cpu');
        $hasCloseBtn = str_contains($htmlMega, 'id="categoryDrawerClose"');
        $hasMobilePanelInline = str_contains($htmlMega, 'class="category-mobile__panel"');
        $hasFriendlyRange = str_contains($htmlMega, 'Đến 15 triệu') || str_contains($htmlMega, 'Trên 15 đến 20 triệu');

        $this->record("8. Mega menu renders virtual Laptop & PC links and exact CPU link", $hasVirtualLaptop && $hasVirtualPC && $hasExactCPU);
        $this->record("9. Category drawer contains internal close button (#categoryDrawerClose)", $hasCloseBtn);
        $this->record("10. Inline mobile accordion panels & friendly price range labels rendered", $hasMobilePanelInline && $hasFriendlyRange);
    }

    private function testZeroDatabaseMutations(int $initialCatCount, int $initialProdCount): void
    {
        $pdo = Database::getConnection();
        $finalCatCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $finalProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        $pass = ($initialCatCount === $finalCatCount) && ($initialProdCount === $finalProdCount);

        $this->record("11. Zero DB mutations during and after UI integration tests", $pass);
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $test = new CatalogMenuUITest();
    $passed = $test->runAll();
    exit($passed ? 0 : 1);
}
