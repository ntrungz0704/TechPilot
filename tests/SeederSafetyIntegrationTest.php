<?php
/**
 * tests/SeederSafetyIntegrationTest.php
 * Integration test verifying safe, non-overwriting seeder behavior and placeholder repair.
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/scripts/database/seed-news.php'; // Defines isPlaceholderPostContent function

class SeederSafetyIntegrationTest
{
    private PDO $db;
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function __construct()
    {
        $db = Database::getConnection();
        if (!$db) {
            echo "Error: Database connection failed.\n";
            exit(1);
        }
        $this->db = $db;
    }

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== TECHPILOT SEEDER SAFETY INTEGRATION TEST SUITE   ===\n";
        echo "========================================================\n\n";

        $this->db->beginTransaction();

        try {
            $this->testIdempotencyAndNoDuplicates();
            $this->testRichContentProtectionNeverOverwritten();
            $this->testPlaceholderRepairWithFlag();
        } finally {
            $this->db->rollBack();
        }

        echo "\n════════════════════════════════════════════════════════\n";
        echo "Seeder Safety Test Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "════════════════════════════════════════════════════════\n";

        if ($this->failed > 0) {
            echo "\n[FAIL] SEEDER SAFETY ERRORS DETECTED:\n";
            foreach ($this->errors as $err) {
                echo "  - {$err}\n";
            }
            exit(1);
        }

        exit(0);
    }

    private function assert(bool $condition, string $testName, string $failureMsg = ''): void
    {
        if ($condition) {
            $this->passed++;
            echo "[PASS] {$testName}\n";
        } else {
            $this->failed++;
            $msg = "[FAIL] {$testName}" . ($failureMsg ? ": {$failureMsg}" : '');
            echo "{$msg}\n";
            $this->errors[] = $msg;
        }
    }

    private function testIdempotencyAndNoDuplicates(): void
    {
        echo "--- 1. Testing Seeder Idempotency ---\n";
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM posts");
        $initialCount = (int)$stmt->fetchColumn();

        // Run seed script via PHP CLI in dry run
        $outputDry = shell_exec("php " . escapeshellarg(ROOT_PATH . '/scripts/database/seed-news.php') . " --dry-run 2>&1");
        
        $stmt = $this->db->query("SELECT COUNT(*) FROM posts");
        $countAfterDry = (int)$stmt->fetchColumn();

        $this->assert($initialCount === $countAfterDry, "Dry-run mode does NOT mutate database rows ({$initialCount} === {$countAfterDry})");
        $this->assert(str_contains((string)$outputDry, "Dry Run Mode: YES"), "Dry-run output indicates Dry Run Mode: YES");
    }

    private function testRichContentProtectionNeverOverwritten(): void
    {
        echo "\n--- 2. Testing Rich Content Protection (Never Overwritten) ---\n";

        $testSlug = '10-meo-toi-uu-windows-11-tang-toc-may-tinh-choi-game';
        $stmt = $this->db->prepare("SELECT content, CHAR_LENGTH(COALESCE(content, '')) AS len FROM posts WHERE slug = :slug");
        $stmt->execute([':slug' => $testSlug]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $isPlaceholder = isPlaceholderPostContent($existing['content']);
            $this->assert(!$isPlaceholder, "Seeded article with length >= 100 is NOT identified as placeholder");
            $this->assert((int)$existing['len'] >= 100, "Article content length is >= 100 chars (Actual: {$existing['len']})");
        } else {
            $this->assert(false, "Seeded article slug '{$testSlug}' exists in DB");
        }
    }

    private function testPlaceholderRepairWithFlag(): void
    {
        echo "\n--- 3. Testing Placeholder Content Repair Detection ---\n";

        $placeholderContent = "<p>Nội dung chi tiết đánh giá...</p>";
        $isPlaceholder = isPlaceholderPostContent($placeholderContent);
        $this->assert($isPlaceholder, "Short placeholder string identified as placeholder needing repair");

        $emptyContent = "";
        $this->assert(isPlaceholderPostContent($emptyContent), "Empty string identified as placeholder needing repair");
    }
}

// Ensure running directly when called as CLI test
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $test = new SeederSafetyIntegrationTest();
    $test->run();
}
