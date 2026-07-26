<?php
/**
 * tests/DatabaseRuntimeSafetyTest.php
 * Verifies that Database::getConnection() at application runtime does NOT invoke auto-sync or execute techpilot.sql.
 */

define('ROOT_PATH', dirname(__DIR__));

class DatabaseRuntimeSafetyTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== TECHPILOT DATABASE RUNTIME SAFETY TEST SUITE    ===\n";
        echo "========================================================\n\n";

        $this->testDatabaseFileStructure();
        $this->testRuntimeConnectionDoesNotWipeDatabase();

        echo "\n════════════════════════════════════════════════════════\n";
        echo "Database Runtime Safety Test Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "════════════════════════════════════════════════════════\n";

        if ($this->failed > 0) {
            echo "\n[FAIL] RUNTIME DATABASE SAFETY ERRORS DETECTED:\n";
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

    private function testDatabaseFileStructure(): void
    {
        echo "--- 1. Testing Database Class Source Safety ---\n";

        $dbCode = file_get_contents(ROOT_PATH . '/config/database.php');

        $this->assert(!str_contains($dbCode, 'ensureAutoSync()'), "Database::getConnection() source does NOT contain ensureAutoSync()");
        $this->assert(!str_contains($dbCode, "exec(file_get_contents(\$sqlFile))"), "Database::getConnection() does NOT execute SQL dumps automatically");
    }

    private function testRuntimeConnectionDoesNotWipeDatabase(): void
    {
        echo "\n--- 2. Testing Database Connection Handshake ---\n";

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();

        $this->assert($db instanceof PDO, "Database::getConnection() returns a valid PDO instance");

        // Verify posts table still exists and has published articles
        $stmt = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'published'");
        $count = (int)$stmt->fetchColumn();

        $this->assert($count > 0, "Runtime connection preserves posts table and published articles (Count: {$count})");
    }
}

$test = new DatabaseRuntimeSafetyTest();
$test->run();
