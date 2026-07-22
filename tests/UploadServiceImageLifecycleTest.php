<?php
/**
 * tests/UploadServiceImageLifecycleTest.php
 * Unit & Security tests for UploadService::deleteImage and image lifecycle management.
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/services/UploadService.php';

class UploadServiceImageLifecycleTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== TECHPILOT UPLOAD SERVICE IMAGE LIFECYCLE TESTS   ===\n";
        echo "========================================================\n\n";

        $this->testMethodExists();
        $this->testSubdirectoryDeletions();
        $this->testMissingFileIdempotency();
        $this->testSecurityPathTraversalProtection();
        $this->testCleanupReturnHandlingAndPostDeleteOrchestration();

        echo "\n════════════════════════════════════════════════════════\n";
        echo "UploadService Test Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "════════════════════════════════════════════════════════\n";

        if ($this->failed > 0) {
            echo "\n[FAIL] UPLOAD SERVICE ERRORS DETECTED:\n";
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

    private function testMethodExists(): void
    {
        echo "--- 1. Testing Method Existence ---\n";
        $this->assert(method_exists(UploadService::class, 'deleteImage'), "UploadService::deleteImage method exists");
    }

    private function testSubdirectoryDeletions(): void
    {
        echo "\n--- 2. Testing Deletion in Subdirectories ---\n";

        // Create dummy fixture file in public/assets/images/posts/
        $postsDir = ROOT_PATH . '/public/assets/images/posts';
        if (!is_dir($postsDir)) {
            mkdir($postsDir, 0755, true);
        }
        $fixturePostsFile = $postsDir . '/test_fixture_' . bin2hex(random_bytes(4)) . '.jpg';
        file_put_contents($fixturePostsFile, 'dummy image content');
        $this->assert(file_exists($fixturePostsFile), "Posts fixture file created");

        $relPostsPath = 'posts/' . basename($fixturePostsFile);
        $resPosts = UploadService::deleteImage($relPostsPath);
        $this->assert($resPosts === true, "deleteImage returns true for posts fixture");
        $this->assert(!file_exists($fixturePostsFile), "Posts fixture file deleted from disk");

        // Create dummy fixture file in public/assets/images/news/
        $newsDir = ROOT_PATH . '/public/assets/images/news';
        if (!is_dir($newsDir)) {
            mkdir($newsDir, 0755, true);
        }
        $fixtureNewsFile = $newsDir . '/test_fixture_' . bin2hex(random_bytes(4)) . '.png';
        file_put_contents($fixtureNewsFile, 'dummy image content');
        $this->assert(file_exists($fixtureNewsFile), "News fixture file created");

        $relNewsPath = 'news/' . basename($fixtureNewsFile);
        $resNews = UploadService::deleteImage($relNewsPath);
        $this->assert($resNews === true, "deleteImage returns true for news fixture");
        $this->assert(!file_exists($fixtureNewsFile), "News fixture file deleted from disk");
    }

    private function testMissingFileIdempotency(): void
    {
        echo "\n--- 3. Testing Missing File Idempotency ---\n";

        $nonExistentPath = 'posts/non_existent_file_' . bin2hex(random_bytes(6)) . '.jpg';
        $res = UploadService::deleteImage($nonExistentPath);
        $this->assert($res === true, "Missing file deletion returns true (idempotent)");

        $emptyRes = UploadService::deleteImage(null);
        $this->assert($emptyRes === true, "Null image path deletion returns true (idempotent)");
    }

    private function testSecurityPathTraversalProtection(): void
    {
        echo "\n--- 4. Testing Path Traversal & Base Directory Security ---\n";

        // Path traversal attempts
        $traversal1 = '../config/database.php';
        $res1 = UploadService::deleteImage($traversal1);
        $this->assert($res1 === false, "Path traversal attempt '../config/database.php' is rejected");
        $this->assert(file_exists(ROOT_PATH . '/config/database.php'), "Critical config/database.php was NOT deleted");

        $traversal2 = 'posts/../../index.php';
        $res2 = UploadService::deleteImage($traversal2);
        $this->assert($res2 === false, "Path traversal attempt 'posts/../../index.php' is rejected");
        $this->assert(file_exists(ROOT_PATH . '/index.php'), "Root index.php was NOT deleted");

        // Absolute path outside base directory
        $absPath = ROOT_PATH . '/config/app.php';
        $res3 = UploadService::deleteImage($absPath);
        $this->assert($res3 === false, "Absolute path outside base upload dir is rejected");
        $this->assert(file_exists(ROOT_PATH . '/config/app.php'), "Config app.php was NOT deleted");
    }

    private function testCleanupReturnHandlingAndPostDeleteOrchestration(): void
    {
        echo "\n--- 5. Testing Cleanup Return Values & Post-Delete Flow ---\n";

        // Invalid path returns false
        $resInvalid = UploadService::deleteImage('../config/database.php');
        $this->assert($resInvalid === false, "Invalid path cleanup returns false as boolean result");

        // Controller source code check for return value checking and post-delete order
        $controllerSrc = file_get_contents(ROOT_PATH . '/app/controllers/AdminPostController.php');

        $this->assert(str_contains($controllerSrc, "\$cleaned = UploadService::deleteImage"), "AdminPostController captures deleteImage return value into \$cleaned");
        $this->assert(str_contains($controllerSrc, "if (!\$cleaned)"), "AdminPostController checks if (!\$cleaned) to log warning");
        $this->assert(str_contains($controllerSrc, "error_log("), "AdminPostController logs warning on cleanup failure");

        // Post delete order: DELETE SQL execution position BEFORE deleteImage call position in delete()
        $deleteMethodPos = strpos($controllerSrc, 'public function delete');
        $sqlDeletePos = strpos($controllerSrc, "DELETE FROM posts WHERE id = :id", $deleteMethodPos);
        $fileDeletePos = strpos($controllerSrc, "UploadService::deleteImage", $deleteMethodPos);

        $this->assert($deleteMethodPos !== false && $sqlDeletePos !== false && $fileDeletePos !== false, "delete() contains both DB DELETE SQL and UploadService::deleteImage");
        $this->assert($sqlDeletePos < $fileDeletePos, "delete() executes DB DELETE query BEFORE attempting file cleanup");
    }
}

$test = new UploadServiceImageLifecycleTest();
$test->run();
