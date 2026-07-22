<?php
/**
 * tests/AdminPostValidationTest.php
 * Unit & Orchestration Integration test suite for Admin Post Publishing Validation & Form UX.
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/services/PostPublishingValidator.php';

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

        $this->testPostPublishingValidatorRules();
        $this->testControllerSourceAndOrchestrationContracts();

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

    private function testPostPublishingValidatorRules(): void
    {
        echo "--- 1. Testing Production PostPublishingValidator Rules ---\n";

        // 1. Empty title -> Invalid
        $res1 = PostPublishingValidator::validate([
            'title' => '',
            'content' => ':::summary\n- Test\n:::\n\n## Header\n\n' . str_repeat('Nội dung chuẩn SEO hợp lệ. ', 15),
            'status' => 'published'
        ]);
        $this->assert(!$res1['valid'], "Empty title is invalid");
        $this->assert(isset($res1['errors']['title']), "Structured errors.title is present for empty title");
        $this->assert($res1['errors']['title'] === 'Vui lòng nhập tiêu đề bài viết.', "Correct error message for title");

        // 2. Published empty content -> Invalid
        $res2 = PostPublishingValidator::validate([
            'title' => 'Tiêu đề hợp lệ',
            'content' => '',
            'status' => 'published'
        ]);
        $this->assert(!$res2['valid'], "Published post with empty content is invalid");
        $this->assert(isset($res2['errors']['content']), "Structured errors.content is present for empty published content");

        // 3. Published short content (< 100 chars) -> Invalid
        $res3 = PostPublishingValidator::validate([
            'title' => 'Tiêu đề hợp lệ',
            'content' => 'Nội dung ngắn dưới 100 ký tự',
            'status' => 'published'
        ]);
        $this->assert(!$res3['valid'], "Published post with content < 100 chars is invalid");
        $this->assert(isset($res3['errors']['content']), "Structured errors.content is present for short published content");

        // 4. Published placeholder content -> Invalid
        $res4 = PostPublishingValidator::validate([
            'title' => 'Tiêu đề hợp lệ',
            'content' => '<p>Nội dung chi tiết đánh giá...</p>',
            'status' => 'published'
        ]);
        $this->assert(!$res4['valid'], "Published post with placeholder content is invalid");
        $this->assert(isset($res4['errors']['content']), "Structured errors.content is present for placeholder content");

        // 5. Published rich markdown content -> Valid
        $richContent = ":::summary\n- Hướng dẫn chi tiết sản phẩm\n:::\n\n## 1. Giới thiệu\n\n" . str_repeat("Nội dung thử nghiệm đầy đủ chuẩn SEO. ", 15);
        $res5 = PostPublishingValidator::validate([
            'title' => 'Tiêu đề hợp lệ',
            'content' => $richContent,
            'status' => 'published'
        ]);
        $this->assert($res5['valid'], "Published post with rich markdown content >= 100 chars is valid");
        $this->assert(empty($res5['errors']), "Zero validation errors returned for rich content");

        // 6. Draft empty content -> Valid (when title is valid)
        $res6 = PostPublishingValidator::validate([
            'title' => 'Tiêu đề bản nháp',
            'content' => '',
            'status' => 'draft'
        ]);
        $this->assert($res6['valid'], "Draft post with empty content is allowed");
        $this->assert(empty($res6['errors']), "Draft empty content has no errors");

        // 7. Draft short content -> Valid
        $res7 = PostPublishingValidator::validate([
            'title' => 'Tiêu đề bản nháp ngắn',
            'content' => 'Bản nháp ngắn',
            'status' => 'draft'
        ]);
        $this->assert($res7['valid'], "Draft post with short content is allowed");

        // 8. Hidden status empty content -> Valid contract explicit
        $res8 = PostPublishingValidator::validate([
            'title' => 'Bài viết tạm ẩn',
            'content' => '',
            'status' => 'hidden'
        ]);
        $this->assert($res8['valid'], "Hidden post with empty content is allowed per contract");
        $this->assert(empty($res8['errors']), "Hidden empty content has no errors");
    }

    private function testControllerSourceAndOrchestrationContracts(): void
    {
        echo "\n--- 2. Testing Controller Source & View Orchestration Contracts ---\n";

        $controllerSrc = file_get_contents(ROOT_PATH . '/app/controllers/AdminPostController.php');
        $createViewSrc = file_get_contents(ROOT_PATH . '/app/views/admin/posts/create.php');
        $editViewSrc   = file_get_contents(ROOT_PATH . '/app/views/admin/posts/edit.php');

        // Controller imports and uses PostPublishingValidator
        $this->assert(str_contains($controllerSrc, "PostPublishingValidator::validate"), "AdminPostController calls PostPublishingValidator::validate");

        // Store validation position before upload Image
        $storePos = strpos($controllerSrc, 'public function store');
        $valPosInStore = strpos($controllerSrc, 'PostPublishingValidator::validate', $storePos);
        $uploadPosInStore = strpos($controllerSrc, 'UploadService::uploadImage', $storePos);
        $this->assert($valPosInStore !== false && $uploadPosInStore !== false && $valPosInStore < $uploadPosInStore, "store() validates BEFORE UploadService::uploadImage");

        // Update validation position before upload/unlink
        $updatePos = strpos($controllerSrc, 'public function update');
        $valPosInUpdate = strpos($controllerSrc, 'PostPublishingValidator::validate', $updatePos);
        $uploadPosInUpdate = strpos($controllerSrc, 'UploadService::uploadImage', $updatePos);
        $unlinkPosInUpdate = strpos($controllerSrc, 'deleteImage', $updatePos);
        $this->assert($valPosInUpdate !== false && $uploadPosInUpdate !== false && $valPosInUpdate < $uploadPosInUpdate, "update() validates BEFORE UploadService::uploadImage");
        $this->assert($unlinkPosInUpdate !== false && $uploadPosInUpdate < $unlinkPosInUpdate, "update() unlinks old image ONLY AFTER DB update/upload");

        // Throwable safety boundary assertions
        $this->assert(str_contains($controllerSrc, "catch (Throwable \$e)"), "AdminPostController catches Throwable at side-effect boundaries");
        $this->assert(str_contains($controllerSrc, "UploadService::deleteImage(\$uploadedImage, 'posts')"), "store() cleans uploaded image if DB insert fails");
        $this->assert(str_contains($controllerSrc, "UploadService::deleteImage(\$newImage, 'posts')"), "update() cleans new uploaded image if DB update fails");

        // View assertions
        $this->assert(str_contains($createViewSrc, "\$errors['title']"), "create.php renders errors['title']");
        $this->assert(str_contains($createViewSrc, "\$errors['content']"), "create.php renders errors['content']");
        $this->assert(str_contains($editViewSrc, "\$errors['title']"), "edit.php renders errors['title']");
        $this->assert(str_contains($editViewSrc, "\$errors['content']"), "edit.php renders errors['content']");

        $this->assert(str_contains($createViewSrc, "e(\$errors['title'])") || str_contains($createViewSrc, "e(\$errors['content'])"), "Field errors are safely escaped with e(...)");
        $this->assert(str_contains($createViewSrc, "form-control--invalid"), "create.php contains form-control--invalid class");
        $this->assert(str_contains($editViewSrc, "form-control--invalid"), "edit.php contains form-control--invalid class");
    }
}

$test = new AdminPostValidationTest();
$test->run();
