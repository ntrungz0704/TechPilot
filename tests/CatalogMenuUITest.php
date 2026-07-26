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
        echo "RUNNING CHECKPOINT 2 V3 — CATEGORY MENU UI INTEGRATION TESTS\n";
        echo "==================================================\n\n";

        $pdo = Database::getConnection();
        $initialCatCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $initialProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        $this->testStorefrontMenuTreeContracts();
        $this->testHeaderViewMarkupAndAriaTargets();
        $this->testSearchSelectSelectedStateResolution();
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

    private function testHeaderViewMarkupAndAriaTargets(): void
    {
        $globalCategoryMenu = CategoryMenuService::getActiveMenuTree();
        $catParam = '';
        $qParam = '';

        ob_start();
        include ROOT_PATH . '/app/views/layouts/header.php';
        $htmlHeader = ob_get_clean();

        ob_start();
        $isStatic = false;
        include ROOT_PATH . '/app/views/layouts/partials/category-mega-menu.php';
        $htmlMega = ob_get_clean();

        $fullHtml = '<div>' . $htmlHeader . $htmlMega . '</div>';

        // 1. DOM Parsing to verify all aria-controls target IDs exist in DOM
        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        $doc->loadHTML('<?xml encoding="UTF-8">' . $fullHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $controlsNodes = $xpath->query('//*[@aria-controls]');

        $missingTargets = [];
        $validatedCount = 0;

        foreach ($controlsNodes as $node) {
            $targetId = $node->getAttribute('aria-controls');
            if (empty($targetId)) continue;

            $targetNode = $doc->getElementById($targetId);
            if (!$targetNode) {
                $missingTargets[] = $targetId;
            } else {
                $validatedCount++;
            }
        }

        $allTargetsExist = empty($missingTargets) && $validatedCount > 0;
        $missingMsg = !empty($missingTargets) ? 'Missing targets: ' . implode(', ', array_unique($missingTargets)) : '';

        $this->record(
            "5. All aria-controls targets exist in DOM (DOMDocument verification)",
            $allTargetsExist,
            $missingMsg
        );

        // 2. Check mobile quick categories links
        $hasMobileLaptop = str_contains($htmlHeader, 'home/search?cat=laptop');
        $hasMobilePC = str_contains($htmlHeader, 'home/search?cat=pc');
        $hasMobileLinhKien = str_contains($htmlHeader, 'home/search?cat=pc-linh-kien');

        $this->record(
            "6. Mobile quick categories map correctly (Laptop=laptop, PC=pc, Linh kiện=pc-linh-kien)",
            $hasMobileLaptop && $hasMobilePC && $hasMobileLinhKien
        );

        // 3. Search select uses $globalCategoryMenu without double hydration
        $hasSearchSelectVirtualOptions = str_contains($htmlHeader, 'value="laptop"') && str_contains($htmlHeader, 'value="pc"');
        $noDuplicateHydrationInView = !str_contains($htmlHeader, 'CatalogGroupService::getStorefrontGroups()');

        $this->record(
            "7. Header search select uses \$globalCategoryMenu without double hydration",
            $hasSearchSelectVirtualOptions && $noDuplicateHydrationInView
        );
    }

    private function testSearchSelectSelectedStateResolution(): void
    {
        $globalCategoryMenu = CategoryMenuService::getActiveMenuTree();

        // Case A: cat=cpu -> search select option for pc-linh-kien is selected
        $catParam = 'cpu';
        $qParam = '';
        ob_start();
        include ROOT_PATH . '/app/views/layouts/header.php';
        $htmlCpu = ob_get_clean();

        $cpuSelected = str_contains($htmlCpu, '<option value="pc-linh-kien" selected>');

        // Case B: cat=laptop-gaming -> search select option for laptop is selected
        $catParam = 'laptop-gaming';
        ob_start();
        include ROOT_PATH . '/app/views/layouts/header.php';
        $htmlLaptopGaming = ob_get_clean();

        $laptopGamingSelected = str_contains($htmlLaptopGaming, '<option value="laptop" selected>');

        $this->record(
            "8. Search select option selected state resolves (cat=cpu -> pc-linh-kien, cat=laptop-gaming -> laptop)",
            $cpuSelected && $laptopGamingSelected,
            "cpuSelected=" . ($cpuSelected ? '1' : '0') . ", laptopGamingSelected=" . ($laptopGamingSelected ? '1' : '0')
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

        $this->record("9. Mega menu renders virtual Laptop & PC links and exact CPU link", $hasVirtualLaptop && $hasVirtualPC && $hasExactCPU);
        $this->record("10. Category drawer contains internal close button (#categoryDrawerClose)", $hasCloseBtn);
        $this->record("11. Inline mobile accordion panels & friendly price range labels rendered", $hasMobilePanelInline && $hasFriendlyRange);
    }

    private function testZeroDatabaseMutations(int $initialCatCount, int $initialProdCount): void
    {
        $pdo = Database::getConnection();
        $finalCatCount = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        $finalProdCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

        $pass = ($initialCatCount === $finalCatCount) && ($initialProdCount === $finalProdCount);

        $this->record("12. Zero DB mutations during and after UI integration tests", $pass);
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $test = new CatalogMenuUITest();
    $passed = $test->runAll();
    exit($passed ? 0 : 1);
}
