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
        echo "RUNNING CHECKPOINT 2 — CATEGORY MENU UI INTEGRATION TESTS\n";
        echo "==================================================\n\n";

        $pdo = Database::getConnection();
        $initialCatCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $initialProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        $this->testStorefrontMenuTreeContracts();
        $this->testPartialViewOutputRendering();
        $this->testHeaderSearchSelectVirtualGroups();
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
        $this->record("4. Empty Networking group is NOT rendered", !$hasNetworking, "Networking present: " . ($hasNetworking ? 'Yes' : 'No'));
    }

    private function testPartialViewOutputRendering(): void
    {
        $globalCategoryMenu = CategoryMenuService::getActiveMenuTree();
        $isStatic = false;

        ob_start();
        include ROOT_PATH . '/app/views/layouts/partials/category-mega-menu.php';
        $html = ob_get_clean();

        $hasVirtualLaptopLink = str_contains($html, 'home/search?cat=laptop');
        $hasVirtualPCLink = str_contains($html, 'home/search?cat=pc');
        $hasExactCPULink = str_contains($html, 'home/search?cat=cpu');
        $hasFriendlyPriceRange = str_contains($html, 'Đến 15 triệu') || str_contains($html, 'Trên 15 đến 20 triệu');
        $hasMobileAccordion = str_contains($html, 'category-mobile-accordion-toggle');
        $hasViewAllLink = str_contains($html, 'mega-panel__view-all');

        $this->record("5. Mega menu renders virtual Laptop link (cat=laptop)", $hasVirtualLaptopLink);
        $this->record("6. Mega menu renders virtual PC link (cat=pc)", $hasVirtualPCLink);
        $this->record("7. Mega menu renders exact CPU link (cat=cpu)", $hasExactCPULink);
        $this->record("8. Price range wording uses friendly labels ('Đến', 'Trên... đến...')", $hasFriendlyPriceRange);
        $this->record("9. View markup contains mobile accordion toggle & 'Xem tất cả' link", $hasMobileAccordion && $hasViewAllLink);
    }

    private function testHeaderSearchSelectVirtualGroups(): void
    {
        $storefrontGroups = CatalogGroupService::getStorefrontGroups();
        $expectedSlugs = ['laptop', 'pc', 'pc-linh-kien', 'man-hinh', 'gaming-gear', 'office-gear'];

        $actualSlugs = array_map(fn($g) => $g['virtual_slug'], $storefrontGroups);

        $pass = ($actualSlugs === $expectedSlugs);

        $this->record(
            "10. Header search select uses 6 storefront virtual group slugs",
            $pass,
            "Actual: " . implode(', ', $actualSlugs)
        );
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
