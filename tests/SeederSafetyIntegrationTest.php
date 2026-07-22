<?php
/**
 * tests/SeederSafetyIntegrationTest.php
 * Direct PDO Integration Test Suite for NewsSeederService.
 * Validates insert, skip, repair, dry-run, metadata preservation, idempotency & transaction safety.
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Post.php';
require_once ROOT_PATH . '/app/services/NewsSeederService.php';

class SeederSafetyIntegrationTest
{
    private PDO $db;
    private NewsSeederService $seeder;
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
        $this->seeder = new NewsSeederService($this->db);
    }

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== TECHPILOT SEEDER SAFETY INTEGRATION TEST SUITE   ===\n";
        echo "========================================================\n\n";

        $this->db->beginTransaction();

        try {
            $this->testDirectServiceInsertMissingPost();
            $this->testDirectServiceSkipRichContent();
            $this->testDirectServiceSkipPlaceholderDefaultMode();
            $this->testDirectServiceRepairPlaceholderWithFlag();
            $this->testDirectServiceDryRunNoMutation();
            $this->testDirectServiceIdempotency();
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

    private function testDirectServiceInsertMissingPost(): void
    {
        echo "--- 1. Direct Service: Insert Missing Post ---\n";

        $slug = 'test-cp3-fixture-missing-slug';
        
        // Cleanup fixture if exists
        $stmt = $this->db->prepare("DELETE FROM posts WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);

        $seedData = [
            [
                'title' => 'Missing Article Test Fixture',
                'slug' => $slug,
                'summary' => 'Summary for missing article fixture',
                'content' => ":::summary\n- Summary line\n:::\n\n## 1. Section Heading\n\n" . str_repeat("Dữ liệu thử nghiệm bài viết mới. ", 15),
                'image' => 'assets/images/missing-cover.jpg',
                'category_slug' => 'cong-nghe',
                'post_type' => 'news',
                'author_name' => 'CP3 Test Author',
                'status' => 'published',
                'views' => 0,
                'reading_minutes' => 5,
                'created_at' => '2026-03-01 10:00:00',
            ]
        ];

        $res = $this->seeder->run($seedData, false, false);

        $this->assert($res['inserted'] === 1, "NewsSeederService reported inserted = 1");
        $this->assert($res['repaired'] === 0, "NewsSeederService reported repaired = 0");
        $this->assert($res['skipped_rich'] === 0, "NewsSeederService reported skipped_rich = 0");

        $stmt = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assert((bool)$row, "Missing post inserted successfully into database");
        $this->assert($row['title'] === 'Missing Article Test Fixture', "Inserted title matches seed");
        $this->assert($row['status'] === 'published', "Inserted status matches seed");
    }

    private function testDirectServiceSkipRichContent(): void
    {
        echo "\n--- 2. Direct Service: Skip Rich User Content ---\n";

        $slug = 'test-cp3-fixture-rich-slug';
        $richContent = ":::summary\n- User custom summary\n:::\n\n## 1. User Section\n\n" . str_repeat("Nội dung thật của người dùng không được ghi đè. ", 20);

        // Setup existing rich post
        $stmt = $this->db->prepare("DELETE FROM posts WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);

        $stmt = $this->db->prepare(
            "INSERT INTO posts (title, slug, summary, content, image, category_slug, post_type, author_name, status, views, is_featured, created_at)
             VALUES ('Tiêu đề người dùng', :slug, 'Tóm tắt người dùng', :content, 'uploads/posts/user-cover.png', 'laptop', 'review', 'Tác giả thật', 'hidden', 1234, 1, '2026-01-15 08:00:00')"
        );
        $stmt->execute([':slug' => $slug, ':content' => $richContent]);

        $seedAttempt = [
            [
                'title' => 'Ghi đè tiêu đề thất bại',
                'slug' => $slug,
                'summary' => 'Ghi đè tóm tắt thất bại',
                'content' => ':::summary\n- Ghi đè content thất bại\n:::\n\n## Ghi đè',
                'image' => 'assets/images/seed-cover.jpg',
                'category_slug' => 'pc-gaming',
                'post_type' => 'guide',
                'author_name' => 'Seed Author Fake',
                'status' => 'published',
            ]
        ];

        // Run default mode
        $resDefault = $this->seeder->run($seedAttempt, false, false);
        $this->assert($resDefault['skipped_rich'] === 1, "Default mode skips rich post (skipped_rich = 1)");

        // Run repair mode
        $resRepair = $this->seeder->run($seedAttempt, false, true);
        $this->assert($resRepair['skipped_rich'] === 1, "Repair mode ALSO skips rich post (skipped_rich = 1)");

        // Check DB row integrity
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assert($row['content'] === $richContent, "Rich post content remained 100% untouched");
        $this->assert($row['image'] === 'uploads/posts/user-cover.png', "Rich post image remained untouched");
        $this->assert((int)$row['views'] === 1234, "Rich post views remained untouched");
        $this->assert($row['author_name'] === 'Tác giả thật', "Rich post author_name remained untouched");
        $this->assert($row['status'] === 'hidden', "Rich post status remained untouched");
    }

    private function testDirectServiceSkipPlaceholderDefaultMode(): void
    {
        echo "\n--- 3. Direct Service: Skip Placeholder in Default Mode ---\n";

        $slug = 'test-cp3-fixture-placeholder-slug';
        $placeholderContent = "Nội dung chi tiết đánh giá...";

        $stmt = $this->db->prepare("DELETE FROM posts WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);

        $stmt = $this->db->prepare(
            "INSERT INTO posts (title, slug, summary, content, image, category_slug, post_type, author_name, status, views, created_at)
             VALUES ('Tiêu đề placeholder', :slug, 'Tóm tắt placeholder', :content, 'uploads/posts/placeholder-custom.jpg', 'cong-nghe', 'news', 'Biên tập viên', 'draft', 55, '2026-02-01 10:00:00')"
        );
        $stmt->execute([':slug' => $slug, ':content' => $placeholderContent]);

        $seedData = [
            [
                'title' => 'Title mới đã repair',
                'slug' => $slug,
                'summary' => 'Summary mới đã repair',
                'content' => ":::summary\n- Repaired summary\n:::\n\n## 1. Repaired Heading\n\n" . str_repeat("Nội dung markdown đã được repair thành công. ", 10),
                'image' => 'assets/images/seed-cover.jpg',
                'category_slug' => 'cong-nghe',
                'post_type' => 'news',
            ]
        ];

        $resDefault = $this->seeder->run($seedData, false, false);
        $this->assert($resDefault['skipped_placeholder'] === 1, "Default mode skips placeholder post (skipped_placeholder = 1)");

        $stmt = $this->db->prepare("SELECT content FROM posts WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        $content = $stmt->fetchColumn();

        $this->assert($content === $placeholderContent, "Placeholder content NOT modified under default mode");
    }

    private function testDirectServiceRepairPlaceholderWithFlag(): void
    {
        echo "\n--- 4. Direct Service: Repair Placeholder & Preserve Real Metadata ---\n";

        $slug = 'test-cp3-fixture-placeholder-slug';

        $seedData = [
            [
                'title' => 'Title mới đã repair',
                'slug' => $slug,
                'summary' => 'Summary mới đã repair',
                'content' => ":::summary\n- Repaired summary\n:::\n\n## 1. Repaired Heading\n\n" . str_repeat("Nội dung markdown đã được repair thành công. ", 10),
                'image' => 'assets/images/seed-cover.jpg',
                'category_slug' => 'cong-nghe',
                'post_type' => 'news',
                'author_name' => 'Seed Author Fallback',
            ]
        ];

        $resRepair = $this->seeder->run($seedData, false, true);
        $this->assert($resRepair['repaired'] === 1, "Repair mode repairs placeholder post (repaired = 1)");

        $stmt = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assert(str_contains($row['content'], 'Nội dung markdown đã được repair thành công'), "Placeholder content updated to rich seed");
        $this->assert($row['image'] === 'uploads/posts/placeholder-custom.jpg', "PRESERVED existing real cover image");
        $this->assert((int)$row['views'] === 55, "PRESERVED existing views count");
        $this->assert($row['author_name'] === 'Biên tập viên', "PRESERVED existing author_name");
        $this->assert($row['status'] === 'draft', "PRESERVED existing status");
        $this->assert($row['created_at'] === '2026-02-01 10:00:00', "PRESERVED existing created_at timestamp");
    }

    private function testDirectServiceDryRunNoMutation(): void
    {
        echo "\n--- 5. Direct Service: Dry-Run Mode Zero Mutation ---\n";

        $stmt = $this->db->query("SELECT COUNT(*), SUM(views) FROM posts");
        $beforeStats = $stmt->fetch(PDO::FETCH_NUM);

        $seedData = [
            [
                'title' => 'Dry Run New Item',
                'slug' => 'test-cp3-fixture-dryrun-missing',
                'summary' => 'Dry Run Summary',
                'content' => ':::summary\n- Dry run\n:::\n\n## Heading\n\n' . str_repeat("Dry run text. ", 15),
                'image' => 'assets/images/dryrun.jpg',
            ]
        ];

        $resDry = $this->seeder->run($seedData, true, true);
        $this->assert($resDry['inserted'] === 1, "Dry run reports would insert = 1");

        $stmt = $this->db->query("SELECT COUNT(*), SUM(views) FROM posts");
        $afterStats = $stmt->fetch(PDO::FETCH_NUM);

        $this->assert($beforeStats[0] === $afterStats[0], "Dry run resulted in ZERO row count changes");
        $this->assert($beforeStats[1] === $afterStats[1], "Dry run resulted in ZERO metadata column changes");
    }

    private function testDirectServiceIdempotency(): void
    {
        echo "\n--- 6. Direct Service: Idempotency ---\n";

        $seedFile = ROOT_PATH . '/database/seeds/news_posts.php';
        if (file_exists($seedFile)) {
            $seedPosts = require $seedFile;
            if (is_array($seedPosts)) {
                $res1 = $this->seeder->run($seedPosts, false, false);
                $res2 = $this->seeder->run($seedPosts, false, false);

                $this->assert($res2['inserted'] === 0, "Second seeder run inserts ZERO duplicate posts");
            }
        }
    }
}

// Ensure running directly when called as CLI test
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $test = new SeederSafetyIntegrationTest();
    $test->run();
}
