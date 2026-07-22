<?php
/**
 * tests/SeederSafetyIntegrationTest.php
 * Comprehensive Direct PDO Integration Test Suite for NewsSeederService.
 * Validates insert, skip, repair, dry-run, metadata preservation, idempotency & transaction rollback.
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
    private ?int $validUserId = null;
    private string $suffix;
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
        $this->suffix = bin2hex(random_bytes(6));

        // Query valid user ID if present
        $stmt = $this->db->query("SELECT id FROM users LIMIT 1");
        $val = $stmt->fetchColumn();
        if ($val !== false) {
            $this->validUserId = (int)$val;
        }
    }

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== TECHPILOT SEEDER SAFETY INTEGRATION TEST SUITE   ===\n";
        echo "========================================================\n\n";

        // 1. Transaction Rollback Test (runs on isolated connection, outside transaction)
        $this->testTransactionRollback();

        // Outer transaction for isolated fixture tests
        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }

        try {
            $this->testInsertMissingPost();
            $this->testRichContentDefaultModePreserve();
            $this->testRichContentRepairModePreserve();
            $this->testPlaceholderContentDefaultModeSkip();
            $this->testPlaceholderContentRepairModePreserveMetadata();
            $this->testDryRunInsertNoMutation();
            $this->testDryRunRepairNoMutation();
            $this->testIdempotency();
        } finally {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
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

    private function testInsertMissingPost(): void
    {
        echo "--- 1. Direct Service: Insert Missing Post ---\n";

        $slug = "test-cp3-insert-{$this->suffix}";
        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");

        $seed = [
            [
                'title' => 'Missing Article Title',
                'slug' => $slug,
                'summary' => 'Missing Article Summary',
                'content' => ":::summary\n- Summary line\n:::\n\n## 1. Section Heading\n\n" . str_repeat("Dữ liệu thử nghiệm bài viết mới. ", 10),
                'image' => 'assets/images/missing.jpg',
                'category_slug' => 'cong-nghe',
                'post_type' => 'news',
                'author_name' => 'CP3 Test Author',
                'status' => 'published',
                'views' => 0,
                'reading_minutes' => 5,
                'created_at' => '2026-03-01 10:00:00',
            ]
        ];

        $res = $this->seeder->run($seed, false, false);

        $this->assert($res['inserted'] === 1, "Insert Missing: result inserted = 1");

        $stmt = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assert((bool)$row, "Insert Missing: row exists in database");
        $this->assert($row['title'] === 'Missing Article Title', "Insert Missing: title matches seed");
        $this->assert($row['content'] === $seed[0]['content'], "Insert Missing: content byte-for-byte matches seed");
        $this->assert($row['slug'] === $slug, "Insert Missing: slug matches seed");
        $this->assert($row['status'] === 'published', "Insert Missing: status matches seed");

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");
    }

    private function testRichContentDefaultModePreserve(): void
    {
        echo "\n--- 2. Direct Service: Rich Content Default Mode Preserve ---\n";

        $slug = "test-cp3-rich-default-{$this->suffix}";
        $richContent = ":::summary\n- User custom summary\n:::\n\n## User Section\n\n" . str_repeat("Dữ liệu rich content của user 1. ", 15);

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");

        $stmt = $this->db->prepare(
            "INSERT INTO posts (title, slug, summary, content, image, category_slug, post_type, author_id, author_name, status, views, is_featured, created_at, published_at)
             VALUES ('Tiêu đề người dùng 1', :slug, 'Tóm tắt 1', :content, 'uploads/posts/user-cover1.png', 'laptop', 'review', :author_id, 'User Author 1', 'hidden', 1234, 1, '2026-01-10 08:00:00', '2026-01-10 08:00:00')"
        );
        $stmt->execute([':slug' => $slug, ':content' => $richContent, ':author_id' => $this->validUserId]);

        $stmtBefore = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmtBefore->execute([':slug' => $slug]);
        $before = $stmtBefore->fetch(PDO::FETCH_ASSOC);

        $seedAttempt = [
            [
                'title' => 'Ghi đè tiêu đề 1',
                'slug' => $slug,
                'summary' => 'Ghi đè tóm tắt 1',
                'content' => ':::summary\n- Ghi đè content 1\n:::\n\n## Ghi đè 1',
                'image' => 'assets/images/seed-cover.jpg',
                'category_slug' => 'pc-gaming',
                'post_type' => 'guide',
                'author_name' => 'Fake Author 1',
                'status' => 'published',
            ]
        ];

        $res = $this->seeder->run($seedAttempt, false, false);
        $this->assert($res['skipped_rich'] === 1, "Rich Default: skipped_rich = 1");

        $stmtAfter = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmtAfter->execute([':slug' => $slug]);
        $after = $stmtAfter->fetch(PDO::FETCH_ASSOC);

        $this->assert($after['id'] === $before['id'], "Rich Default: id unchanged");
        $this->assert($after['slug'] === $before['slug'], "Rich Default: slug unchanged");
        $this->assert($after['content'] === $before['content'], "Rich Default: content unchanged");
        $this->assert($after['image'] === $before['image'], "Rich Default: image unchanged");
        $this->assert((int)$after['views'] === (int)$before['views'], "Rich Default: views unchanged");
        $this->assert($after['author_id'] === $before['author_id'], "Rich Default: author_id unchanged");
        $this->assert($after['author_name'] === $before['author_name'], "Rich Default: author_name unchanged");
        $this->assert($after['status'] === $before['status'], "Rich Default: status unchanged");
        $this->assert($after['created_at'] === $before['created_at'], "Rich Default: created_at unchanged");
        $this->assert($after['published_at'] === $before['published_at'], "Rich Default: published_at unchanged");

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");
    }

    private function testRichContentRepairModePreserve(): void
    {
        echo "\n--- 3. Direct Service: Rich Content Repair Mode Preserve ---\n";

        $slug = "test-cp3-rich-repair-{$this->suffix}";
        $richContent = ":::summary\n- User custom summary 2\n:::\n\n## User Section 2\n\n" . str_repeat("Dữ liệu rich content của user 2. ", 15);

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");

        $stmt = $this->db->prepare(
            "INSERT INTO posts (title, slug, summary, content, image, category_slug, post_type, author_id, author_name, status, views, is_featured, created_at, published_at)
             VALUES ('Tiêu đề người dùng 2', :slug, 'Tóm tắt 2', :content, 'uploads/posts/user-cover2.png', 'laptop', 'review', :author_id, 'User Author 2', 'hidden', 2345, 1, '2026-01-12 08:00:00', '2026-01-12 08:00:00')"
        );
        $stmt->execute([':slug' => $slug, ':content' => $richContent, ':author_id' => $this->validUserId]);

        $stmtBefore = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmtBefore->execute([':slug' => $slug]);
        $before = $stmtBefore->fetch(PDO::FETCH_ASSOC);

        $seedAttempt = [
            [
                'title' => 'Ghi đè tiêu đề 2',
                'slug' => $slug,
                'summary' => 'Ghi đè tóm tắt 2',
                'content' => ':::summary\n- Ghi đè content 2\n:::\n\n## Ghi đè 2',
                'image' => 'assets/images/seed-cover.jpg',
                'category_slug' => 'pc-gaming',
                'post_type' => 'guide',
                'author_name' => 'Fake Author 2',
                'status' => 'published',
            ]
        ];

        $res = $this->seeder->run($seedAttempt, false, true);
        $this->assert($res['skipped_rich'] === 1, "Rich Repair: skipped_rich = 1");

        $stmtAfter = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmtAfter->execute([':slug' => $slug]);
        $after = $stmtAfter->fetch(PDO::FETCH_ASSOC);

        $this->assert($after['id'] === $before['id'], "Rich Repair: id unchanged");
        $this->assert($after['slug'] === $before['slug'], "Rich Repair: slug unchanged");
        $this->assert($after['content'] === $before['content'], "Rich Repair: content unchanged");
        $this->assert($after['image'] === $before['image'], "Rich Repair: image unchanged");
        $this->assert((int)$after['views'] === (int)$before['views'], "Rich Repair: views unchanged");
        $this->assert($after['author_id'] === $before['author_id'], "Rich Repair: author_id unchanged");
        $this->assert($after['author_name'] === $before['author_name'], "Rich Repair: author_name unchanged");
        $this->assert($after['status'] === $before['status'], "Rich Repair: status unchanged");
        $this->assert($after['created_at'] === $before['created_at'], "Rich Repair: created_at unchanged");
        $this->assert($after['published_at'] === $before['published_at'], "Rich Repair: published_at unchanged");

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");
    }

    private function testPlaceholderContentDefaultModeSkip(): void
    {
        echo "\n--- 4. Direct Service: Placeholder Default Mode Skip ---\n";

        $slug = "test-cp3-placeholder-default-{$this->suffix}";
        $placeholderContent = "Nội dung chi tiết đánh giá...";

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");

        $stmt = $this->db->prepare(
            "INSERT INTO posts (title, slug, summary, content, image, category_slug, post_type, author_name, status, views, created_at)
             VALUES ('Tiêu đề placeholder', :slug, 'Tóm tắt placeholder', :content, 'uploads/posts/ph.jpg', 'cong-nghe', 'news', 'Biên tập viên', 'draft', 55, '2026-02-01 10:00:00')"
        );
        $stmt->execute([':slug' => $slug, ':content' => $placeholderContent]);

        $seedAttempt = [
            [
                'title' => 'Title mới đã repair',
                'slug' => $slug,
                'summary' => 'Summary mới đã repair',
                'content' => ":::summary\n- Repaired summary\n:::\n\n## 1. Repaired Heading\n\n" . str_repeat("Nội dung markdown đã được repair. ", 10),
            ]
        ];

        $res = $this->seeder->run($seedAttempt, false, false);
        $this->assert($res['skipped_placeholder'] === 1, "Placeholder Default: skipped_placeholder = 1");

        $stmt = $this->db->prepare("SELECT content FROM posts WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        $content = $stmt->fetchColumn();

        $this->assert($content === $placeholderContent, "Placeholder Default: content NOT modified");

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");
    }

    private function testPlaceholderContentRepairModePreserveMetadata(): void
    {
        echo "\n--- 5. Direct Service: Placeholder Repair Mode & Metadata Preservation ---\n";

        $slug = "test-cp3-placeholder-repair-{$this->suffix}";
        $placeholderContent = "Nội dung chi tiết đánh giá...";

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");

        $stmt = $this->db->prepare(
            "INSERT INTO posts (title, slug, summary, content, image, category_slug, post_type, author_id, author_name, status, views, is_featured, reading_minutes, created_at, published_at)
             VALUES ('Tiêu đề ph gốc', :slug, 'Tóm tắt ph gốc', :content, 'uploads/posts/real-cover-ph.png', 'laptop', 'review', :author_id, 'Biên tập viên thật', 'hidden', 777, 0, 7, '2026-01-01 10:00:00', '2026-01-01 10:00:00')"
        );
        $stmt->execute([':slug' => $slug, ':content' => $placeholderContent, ':author_id' => $this->validUserId]);

        $stmtBefore = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmtBefore->execute([':slug' => $slug]);
        $before = $stmtBefore->fetch(PDO::FETCH_ASSOC);

        $seedRepaired = [
            [
                'title' => 'Title mới đã được repair',
                'slug' => $slug,
                'summary' => 'Summary mới đã được repair',
                'content' => ":::summary\n- Summary mới đã repair\n:::\n\n## 1. Section Mới\n\n" . str_repeat("Nội dung markdown hoàn chỉnh được repair. ", 12),
                'image' => 'assets/images/seed-fallback-cover.jpg',
                'category_slug' => 'pc-gaming',
                'post_type' => 'guide',
                'author_name' => 'Seed Author Fallback',
                'status' => 'published',
                'reading_minutes' => 15,
            ]
        ];

        $res = $this->seeder->run($seedRepaired, false, true);
        $this->assert($res['repaired'] === 1, "Placeholder Repair: repaired = 1");

        $stmtAfter = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmtAfter->execute([':slug' => $slug]);
        $after = $stmtAfter->fetch(PDO::FETCH_ASSOC);

        $this->assert(str_contains($after['content'], 'Nội dung markdown hoàn chỉnh được repair'), "Placeholder Repair: content updated to rich seed Markdown");
        $this->assert($after['id'] === $before['id'], "Placeholder Repair: id unchanged");
        $this->assert($after['slug'] === $before['slug'], "Placeholder Repair: slug unchanged");
        $this->assert($after['image'] === $before['image'], "Placeholder Repair: image unchanged ('uploads/posts/real-cover-ph.png')");
        $this->assert((int)$after['views'] === (int)$before['views'], "Placeholder Repair: views unchanged (777)");
        $this->assert($after['author_id'] === $before['author_id'], "Placeholder Repair: author_id unchanged");
        $this->assert($after['author_name'] === $before['author_name'], "Placeholder Repair: author_name unchanged ('Biên tập viên thật')");
        $this->assert($after['status'] === $before['status'], "Placeholder Repair: status unchanged ('hidden')");
        $this->assert($after['created_at'] === $before['created_at'], "Placeholder Repair: created_at unchanged");
        $this->assert($after['published_at'] === $before['published_at'], "Placeholder Repair: published_at unchanged");
        $this->assert($after['category_slug'] === $before['category_slug'], "Placeholder Repair: category_slug unchanged ('laptop')");
        $this->assert($after['post_type'] === $before['post_type'], "Placeholder Repair: post_type unchanged ('review')");
        $this->assert((int)$after['reading_minutes'] === (int)$before['reading_minutes'], "Placeholder Repair: reading_minutes unchanged (7)");

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");
    }

    private function testDryRunInsertNoMutation(): void
    {
        echo "\n--- 6. Direct Service: Dry-Run Insert Zero Mutation ---\n";

        $slug = "test-cp3-dry-insert-{$this->suffix}";
        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");

        $stmt = $this->db->query("SELECT COUNT(*) FROM posts");
        $countBefore = (int)$stmt->fetchColumn();

        $seed = [
            [
                'title' => 'Dry Run Missing Title',
                'slug' => $slug,
                'summary' => 'Dry Run Summary',
                'content' => ':::summary\n- Dry run\n:::\n\n## Dry Run Section\n\n' . str_repeat("Dry run text. ", 10),
            ]
        ];

        $res = $this->seeder->run($seed, true, false);
        $this->assert($res['inserted'] === 1, "Dry-Run Insert: reported inserted = 1");

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM posts WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        $rowExists = ((int)$stmt->fetchColumn() > 0);

        $stmt = $this->db->query("SELECT COUNT(*) FROM posts");
        $countAfter = (int)$stmt->fetchColumn();

        $this->assert(!$rowExists, "Dry-Run Insert: row does NOT exist in database");
        $this->assert($countBefore === $countAfter, "Dry-Run Insert: total row count unchanged");
    }

    private function testDryRunRepairNoMutation(): void
    {
        echo "\n--- 7. Direct Service: Dry-Run Repair Zero Mutation ---\n";

        $slug = "test-cp3-dry-repair-{$this->suffix}";
        $phContent = "Nội dung chi tiết đánh giá...";

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");

        $stmt = $this->db->prepare(
            "INSERT INTO posts (title, slug, summary, content, image, category_slug, post_type, author_id, author_name, status, views, is_featured, reading_minutes, created_at, published_at)
             VALUES ('Dry Repair Title', :slug, 'Dry Repair Summary', :content, 'uploads/posts/dry-cover.jpg', 'laptop', 'review', :author_id, 'Dry Author', 'hidden', 999, 0, 7, '2026-02-10 10:00:00', '2026-02-10 10:00:00')"
        );
        $stmt->execute([':slug' => $slug, ':content' => $phContent, ':author_id' => $this->validUserId]);

        $stmtBefore = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmtBefore->execute([':slug' => $slug]);
        $before = $stmtBefore->fetch(PDO::FETCH_ASSOC);

        $seedRepair = [
            [
                'title' => 'Title mới đã repair',
                'slug' => $slug,
                'summary' => 'Summary mới đã repair',
                'content' => ":::summary\n- Repaired summary\n:::\n\n## 1. Heading\n\n" . str_repeat("Nội dung markdown đã repair. ", 10),
                'image' => 'assets/images/seed-dry-cover.jpg',
                'category_slug' => 'pc-gaming',
                'post_type' => 'guide',
                'author_name' => 'Seed Author',
                'status' => 'published',
                'reading_minutes' => 15,
            ]
        ];

        $res = $this->seeder->run($seedRepair, true, true);
        $this->assert($res['repaired'] === 1, "Dry-Run Repair: reported repaired = 1");

        $stmtAfter = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmtAfter->execute([':slug' => $slug]);
        $after = $stmtAfter->fetch(PDO::FETCH_ASSOC);

        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM posts WHERE slug = :slug");
        $stmtCount->execute([':slug' => $slug]);
        $count = (int)$stmtCount->fetchColumn();

        $this->assert($count === 1, "Dry-Run Repair: COUNT(*) WHERE slug = 1");
        $this->assert($after['id'] === $before['id'], "Dry-Run Repair: id unchanged");
        $this->assert($after['slug'] === $before['slug'], "Dry-Run Repair: slug unchanged");
        $this->assert($after['content'] === $before['content'], "Dry-Run Repair: content unchanged in database");
        $this->assert($after['image'] === $before['image'], "Dry-Run Repair: image unchanged in database");
        $this->assert((int)$after['views'] === (int)$before['views'], "Dry-Run Repair: views unchanged in database");
        $this->assert($after['author_id'] === $before['author_id'], "Dry-Run Repair: author_id unchanged in database");
        $this->assert($after['author_name'] === $before['author_name'], "Dry-Run Repair: author_name unchanged in database");
        $this->assert($after['status'] === $before['status'], "Dry-Run Repair: status unchanged in database");
        $this->assert($after['created_at'] === $before['created_at'], "Dry-Run Repair: created_at unchanged in database");
        $this->assert($after['published_at'] === $before['published_at'], "Dry-Run Repair: published_at unchanged in database");
        $this->assert($after['category_slug'] === $before['category_slug'], "Dry-Run Repair: category_slug unchanged in database");
        $this->assert($after['post_type'] === $before['post_type'], "Dry-Run Repair: post_type unchanged in database");
        $this->assert((int)$after['reading_minutes'] === (int)$before['reading_minutes'], "Dry-Run Repair: reading_minutes unchanged in database");

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");
    }

    private function testTransactionRollback(): void
    {
        echo "\n--- 8. Direct Service: Transaction Rollback on Failure ---\n";

        $validSlug = "test-cp3-rollback-valid-{$this->suffix}";
        $invalidSlug = "test-cp3-rollback-invalid-{$this->suffix}";

        $isolatedDb = Database::getConnection();
        $this->assert(!$isolatedDb->inTransaction(), "Rollback Test: isolated connection is initially not in transaction");

        try {
            $isolatedDb->exec("DELETE FROM posts WHERE slug IN ('{$validSlug}', '{$invalidSlug}')");

            $batch = [
                [
                    'title' => 'Valid Entry 1',
                    'slug' => $validSlug,
                    'summary' => 'Summary 1',
                    'content' => ":::summary\n- Summary\n:::\n\n## Heading\n\n" . str_repeat("Valid text. ", 10),
                    'image' => 'assets/images/valid.jpg',
                    'created_at' => '2026-03-01 10:00:00',
                ],
                [
                    'title' => 'Invalid Entry 2',
                    'slug' => $invalidSlug,
                    'summary' => 'Summary 2',
                    'content' => 'Invalid text',
                    // Invalid datetime value triggers PDOException in MySQL 8.0 strict mode
                    'created_at' => 'INVALID_DATETIME_CRASH_TRIGGER',
                ],
            ];

            $caughtPdoException = false;
            try {
                $isolatedSeeder = new NewsSeederService($isolatedDb);
                $isolatedSeeder->run($batch, false, false);
            } catch (PDOException $e) {
                $caughtPdoException = true;
            }

            $this->assert($caughtPdoException === true, "Rollback Test: caughtPdoException === true");

            $stmtValid = $isolatedDb->prepare("SELECT COUNT(*) FROM posts WHERE slug = :slug");
            $stmtValid->execute([':slug' => $validSlug]);
            $validCount = (int)$stmtValid->fetchColumn();

            $stmtInvalid = $isolatedDb->prepare("SELECT COUNT(*) FROM posts WHERE slug = :slug");
            $stmtInvalid->execute([':slug' => $invalidSlug]);
            $invalidCount = (int)$stmtInvalid->fetchColumn();

            $this->assert($validCount === 0, "Rollback Test: valid entry count = 0 (completely rolled back)");
            $this->assert($invalidCount === 0, "Rollback Test: invalid entry count = 0");
            $this->assert(!$isolatedDb->inTransaction(), "Rollback Test: connection is not left in an open transaction");
        } finally {
            $isolatedDb->exec("DELETE FROM posts WHERE slug IN ('{$validSlug}', '{$invalidSlug}')");
        }
    }

    private function testIdempotency(): void
    {
        echo "\n--- 9. Direct Service: Idempotency ---\n";

        $slug = "test-cp3-idempotent-{$this->suffix}";
        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");

        $seed = [
            [
                'title' => 'Idempotent Test Title',
                'slug' => $slug,
                'summary' => 'Idempotent Test Summary',
                'content' => ":::summary\n- Summary\n:::\n\n## Heading\n\n" . str_repeat("Idempotent content line. ", 10),
                'image' => 'assets/images/idempotent.jpg',
                'category_slug' => 'laptop',
                'post_type' => 'review',
                'author_name' => 'Idempotent Author',
                'status' => 'published',
                'reading_minutes' => 5,
            ]
        ];

        $res1 = $this->seeder->run($seed, false, false);
        $this->assert($res1['inserted'] === 1, "Idempotency: Run 1 inserted = 1");

        $stmt1 = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmt1->execute([':slug' => $slug]);
        $rowAfterRun1 = $stmt1->fetch(PDO::FETCH_ASSOC);

        $res2 = $this->seeder->run($seed, false, false);
        $this->assert($res2['inserted'] === 0, "Idempotency: Run 2 inserted = 0");
        $this->assert($res2['skipped_rich'] === 1, "Idempotency: Run 2 skipped_rich = 1");

        $stmt2 = $this->db->prepare("SELECT * FROM posts WHERE slug = :slug");
        $stmt2->execute([':slug' => $slug]);
        $rowAfterRun2 = $stmt2->fetch(PDO::FETCH_ASSOC);

        $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM posts WHERE slug = :slug");
        $stmtCount->execute([':slug' => $slug]);
        $count = (int)$stmtCount->fetchColumn();

        $this->assert($count === 1, "Idempotency: COUNT(*) WHERE slug = 1 (no duplicate rows)");
        $this->assert($rowAfterRun2['id'] === $rowAfterRun1['id'], "Idempotency: id unchanged");
        $this->assert($rowAfterRun2['slug'] === $rowAfterRun1['slug'], "Idempotency: slug unchanged");
        $this->assert($rowAfterRun2['content'] === $rowAfterRun1['content'], "Idempotency: content unchanged byte-for-byte");
        $this->assert($rowAfterRun2['image'] === $rowAfterRun1['image'], "Idempotency: image unchanged");
        $this->assert((int)$rowAfterRun2['views'] === (int)$rowAfterRun1['views'], "Idempotency: views unchanged");
        $this->assert($rowAfterRun2['author_id'] === $rowAfterRun1['author_id'], "Idempotency: author_id unchanged");
        $this->assert($rowAfterRun2['author_name'] === $rowAfterRun1['author_name'], "Idempotency: author_name unchanged");
        $this->assert($rowAfterRun2['status'] === $rowAfterRun1['status'], "Idempotency: status unchanged");
        $this->assert($rowAfterRun2['created_at'] === $rowAfterRun1['created_at'], "Idempotency: created_at unchanged");
        $this->assert($rowAfterRun2['published_at'] === $rowAfterRun1['published_at'], "Idempotency: published_at unchanged");

        $this->db->exec("DELETE FROM posts WHERE slug = '{$slug}'");
    }
}

// Ensure running directly when called as CLI test
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $test = new SeederSafetyIntegrationTest();
    $test->run();
}
