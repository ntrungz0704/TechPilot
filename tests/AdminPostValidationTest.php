<?php
/**
 * tests/AdminPostValidationTest.php
 * Unit test for AdminPostController store/update validation integration.
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/models/Post.php';

class AdminPostValidationTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== TECHPILOT ADMIN POST VALIDATION TEST SUITE       ===\n";
        echo "========================================================\n\n";

        $this->testPublishedContentValidationRules();
        $this->testDraftContentFlexibility();

        echo "\n════════════════════════════════════════════════════════\n";
        echo "Admin Post Validation Test Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "════════════════════════════════════════════════════════\n";

        if ($this->failed > 0) {
            echo "\n[FAIL] ADMIN POST VALIDATION ERRORS DETECTED:\n";
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

    private function testPublishedContentValidationRules(): void
    {
        echo "--- 1. Testing Published Content Validation Rules ---\n";

        // Empty published content -> Invalid
        $res1 = Post::validatePublishedContent('', 'published');
        $this->assert(!$res1['valid'], "Published post with empty content is invalid");
        $this->assert(in_array('Nội dung bài viết xuất bản không được để rỗng.', $res1['errors']), "Returns correct empty error message");

        // Short published content -> Invalid
        $res2 = Post::validatePublishedContent('Nội dung quá ngắn', 'published');
        $this->assert(!$res2['valid'], "Published post with content < 100 chars is invalid");

        // Placeholder content -> Invalid
        $res3 = Post::validatePublishedContent('<p>Nội dung chi tiết đánh giá...</p>', 'published');
        $this->assert(!$res3['valid'], "Published post with placeholder content string is invalid");

        // Valid rich content -> Valid
        $richContent = ":::summary\n- Hướng dẫn chi tiết\n:::\n\n## 1. Giới thiệu\n\n" . str_repeat("Nội dung thử nghiệm đầy đủ chuẩn SEO. ", 15);
        $res4 = Post::validatePublishedContent($richContent, 'published');
        $this->assert($res4['valid'], "Published post with rich markdown content >= 100 chars is valid");
        $this->assert(empty($res4['errors']), "Returns zero validation errors for rich content");
    }

    private function testDraftContentFlexibility(): void
    {
        echo "\n--- 2. Testing Draft Content Flexibility ---\n";

        // Empty draft content -> Valid
        $res1 = Post::validatePublishedContent('', 'draft');
        $this->assert($res1['valid'], "Draft post with empty content is allowed");

        // Short draft content -> Valid
        $res2 = Post::validatePublishedContent('Nội dung ngắn', 'draft');
        $this->assert($res2['valid'], "Draft post with short content is allowed");
    }
}

$test = new AdminPostValidationTest();
$test->run();
