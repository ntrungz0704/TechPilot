<?php
/**
 * NewsSearchIntegrationTest.php
 * Real Database Integration Test for TechPilot News Module.
 * Executes production PDO queries in Post model using database transactions.
 * Asserts ranking relevance, LIKE wildcard escaping (!, %, _), quote/backslash safety,
 * filter alignment, and incrementViews updated_at preservation.
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_ENV', 'testing');

if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/models/Post.php';

class NewsSearchIntegrationTest
{
    private ?PDO $db = null;
    private Post $postModel;
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->postModel = new Post();
    }

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== TECHPILOT NEWS DB INTEGRATION TEST SUITE          ===\n";
        echo "========================================================\n\n";

        if ($this->db === null) {
            echo "[SKIP] Database connection unavailable (Database::getConnection() returned null)\n";
            exit(2);
        }

        try {
            $this->db->beginTransaction();
            $this->executeTests();
            $this->db->rollBack();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            echo "[FAIL] Exception during integration test: " . $e->getMessage() . "\n";
            echo $e->getTraceAsString() . "\n";
            exit(1);
        }

        echo "\n════════════════════════════════════════════════════════\n";
        echo "Database Integration Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "════════════════════════════════════════════════════════\n";

        if ($this->failed > 0) {
            echo "\n[FAIL] THE FOLLOWING INTEGRATION ERRORS WERE FOUND:\n";
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
            $msg = "FAIL: {$testName}" . ($failureMsg ? " -> {$failureMsg}" : '');
            $this->errors[] = $msg;
            echo "[FAIL] {$testName}\n";
        }
    }

    private function executeTests(): void
    {
        $prefix = 'news-it-' . bin2hex(random_bytes(4)) . '-';
        $now = date('Y-m-d H:i:s');
        $oldDate = date('Y-m-d H:i:s', time() - 3600);

        // 1. Insert fixture posts with controlled relevance and wildcards
        $fixtures = [
            'exact' => [
                'title' => $prefix . 'RTX 5090',
                'slug' => $prefix . 'rtx-5090',
                'summary' => 'Mo ta ngan exact match',
                'content' => 'Noi dung exact match',
                'post_type' => 'review',
                'category_slug' => 'pc-linh-kien',
            ],
            'prefix' => [
                'title' => $prefix . 'RTX 5090 Review Chi Tiet',
                'slug' => $prefix . 'rtx-5090-review-chi-tiet',
                'summary' => 'Mo ta prefix match',
                'content' => 'Noi dung prefix match',
                'post_type' => 'review',
                'category_slug' => 'pc-linh-kien',
            ],
            'contains_title' => [
                'title' => 'Tin moi ve ' . $prefix . 'RTX 5090 tai Viet Nam',
                'slug' => $prefix . 'tin-moi-ve-rtx-5090',
                'summary' => 'Mo ta contains title match',
                'content' => 'Noi dung contains title match',
                'post_type' => 'news',
                'category_slug' => 'pc-linh-kien',
            ],
            'summary_only' => [
                'title' => $prefix . 'Bai viet chu de khac AAA',
                'slug' => $prefix . 'bai-viet-chu-de-khac-aaa',
                'summary' => 'Mo ta chu de co ' . $prefix . 'RTX 5090 o day',
                'content' => 'Noi dung khong chua tu khoa',
                'post_type' => 'news',
                'category_slug' => 'laptop',
            ],
            'content_only' => [
                'title' => $prefix . 'Bai viet chu de khac BBB',
                'slug' => $prefix . 'bai-viet-chu-de-khac-bbb',
                'summary' => 'Mo ta khong chua tu khoa',
                'content' => 'Noi dung chuyen sau ve ' . $prefix . 'RTX 5090 o day',
                'post_type' => 'guide',
                'category_slug' => 'laptop',
            ],
            'wildcard_percent' => [
                'title' => $prefix . '100% hieu nang',
                'slug' => $prefix . 'gpu-100-percent-hieu-nang',
                'summary' => 'Mo ta chua 100%',
                'content' => 'Noi dung 100%',
                'post_type' => 'news',
                'category_slug' => 'pc-linh-kien',
            ],
            'wildcard_underscore' => [
                'title' => $prefix . 'laptop_sinh_vien',
                'slug' => $prefix . 'laptop-sinh-vien-underscore',
                'summary' => 'Mo ta chua laptop_sinh_vien',
                'content' => 'Noi dung',
                'post_type' => 'guide',
                'category_slug' => 'laptop',
            ],
            'wildcard_exclamation' => [
                'title' => $prefix . 'Bang! Special',
                'slug' => $prefix . 'bang-special-exclamation',
                'summary' => 'Mo ta chua Bang!',
                'content' => 'Noi dung',
                'post_type' => 'news',
                'category_slug' => 'pc-linh-kien',
            ],
        ];

        $stmtInsert = $this->db->prepare('
            INSERT INTO posts (title, slug, summary, content, post_type, category_slug, status, views, published_at, created_at, updated_at)
            VALUES (:title, :slug, :summary, :content, :post_type, :category_slug, "published", 10, :published_at, :created_at, :updated_at)
        ');

        $insertedIds = [];
        foreach ($fixtures as $key => $fix) {
            $stmtInsert->execute([
                ':title' => $fix['title'],
                ':slug' => $fix['slug'],
                ':summary' => $fix['summary'],
                ':content' => $fix['content'],
                ':post_type' => $fix['post_type'],
                ':category_slug' => $fix['category_slug'],
                ':published_at' => $oldDate,
                ':created_at' => $oldDate,
                ':updated_at' => $oldDate,
            ]);
            $insertedIds[$key] = (int)$this->db->lastInsertId();
        }

        echo "--- 1. Testing Production Search Methods & Weighted Relevance ---\n";

        // Query by prefix term
        $searchTerm = $prefix . 'RTX 5090';
        $count = $this->postModel->countAll('', '', '', $searchTerm);
        $results = $this->postModel->getAll(0, 10, '', '', '', $searchTerm);

        $this->assert($count === 5, "countAll returns 5 matching items for search term '{$searchTerm}'");
        $this->assert(count($results) === 5, "getAll returns exactly 5 items");

        // Verify order: Exact > Prefix > Contains Title > Summary Only > Content Only
        if (count($results) === 5) {
            $this->assert($results[0]['id'] === $insertedIds['exact'], "Rank 1 is Exact Title Match (score 100)");
            $this->assert($results[1]['id'] === $insertedIds['prefix'], "Rank 2 is Prefix Title Match (score 80)");
            $this->assert($results[2]['id'] === $insertedIds['contains_title'], "Rank 3 is Title Contains Match (score 60)");
            $this->assert($results[3]['id'] === $insertedIds['summary_only'], "Rank 4 is Summary-only Match (score 30)");
            $this->assert($results[4]['id'] === $insertedIds['content_only'], "Rank 5 is Content-only Match (score 10)");
        }

        echo "\n--- 2. Testing LIKE Wildcard Escaping (!, %, _, quotes, backslash) ---\n";

        // Test literal '%'
        $qPercent = $prefix . '100%';
        $countPercent = $this->postModel->countAll('', '', '', $qPercent);
        $resPercent = $this->postModel->getAll(0, 10, '', '', '', $qPercent);
        $this->assert($countPercent === 1, "Searching literal '%' matches ONLY 1 item containing '100%'");
        $this->assert(isset($resPercent[0]) && $resPercent[0]['id'] === $insertedIds['wildcard_percent'], "Matches correct 'GPU 100% hieu nang' fixture");

        // Test literal '_'
        $qUnderscore = $prefix . 'laptop_sinh_vien';
        $countUnderscore = $this->postModel->countAll('', '', '', $qUnderscore);
        $resUnderscore = $this->postModel->getAll(0, 10, '', '', '', $qUnderscore);
        $this->assert($countUnderscore === 1, "Searching literal '_' matches ONLY 1 item containing 'laptop_sinh_vien'");
        $this->assert(isset($resUnderscore[0]) && $resUnderscore[0]['id'] === $insertedIds['wildcard_underscore'], "Matches correct 'laptop_sinh_vien' fixture");

        // Test literal '!'
        $qExclamation = $prefix . 'Bang!';
        $countExclamation = $this->postModel->countAll('', '', '', $qExclamation);
        $resExclamation = $this->postModel->getAll(0, 10, '', '', '', $qExclamation);
        $this->assert($countExclamation === 1, "Searching literal '!' matches ONLY 1 item containing 'Bang!'");
        $this->assert(isset($resExclamation[0]) && $resExclamation[0]['id'] === $insertedIds['wildcard_exclamation'], "Matches correct 'Bang! Special' fixture");

        // Test single quote, backslash & script inputs
        $qQuote = "' OR '1'='1";
        $countQuote = $this->postModel->countAll('', '', '', $qQuote);
        $this->assert($countQuote === 0, "SQL injection single quote input cleanly returns 0 matches without error");

        $qBackslash = "C:\\Windows\\";
        $countBackslash = $this->postModel->countAll('', '', '', $qBackslash);
        $this->assert($countBackslash === 0, "Backslash input cleanly returns 0 matches without PDO error");

        $qScript = "<script>alert('xss')</script>";
        $countScript = $this->postModel->countAll('', '', '', $qScript);
        $this->assert($countScript === 0, "<script> input cleanly returns 0 matches without execution");

        echo "\n--- 3. Testing Combined Filter Criteria (Type + Category + Search) ---\n";

        $countFiltered = $this->postModel->countAll('review', 'pc-linh-kien', '', $searchTerm);
        $resFiltered = $this->postModel->getAll(0, 10, 'review', 'pc-linh-kien', '', $searchTerm);

        $this->assert($countFiltered === 2, "Filtered countAll for review + pc-linh-kien + search term returns 2");
        $this->assert(count($resFiltered) === 2, "Filtered getAll returns 2 items");

        echo "\n--- 4. Testing incrementViews updated_at Preservation ---\n";

        $testId = $insertedIds['exact'];
        $stmtBefore = $this->db->prepare('SELECT views, updated_at FROM posts WHERE id = :id');
        $stmtBefore->execute([':id' => $testId]);
        $rowBefore = $stmtBefore->fetch(PDO::FETCH_ASSOC);

        $viewsBefore = (int)$rowBefore['views'];
        $updatedAtBefore = $rowBefore['updated_at'];

        // Call production incrementViews method
        $this->postModel->incrementViews($testId);

        $stmtAfter = $this->db->prepare('SELECT views, updated_at FROM posts WHERE id = :id');
        $stmtAfter->execute([':id' => $testId]);
        $rowAfter = $stmtAfter->fetch(PDO::FETCH_ASSOC);

        $viewsAfter = (int)$rowAfter['views'];
        $updatedAtAfter = $rowAfter['updated_at'];

        $this->assert($viewsAfter === $viewsBefore + 1, "incrementViews increments views count by 1 ({$viewsBefore} -> {$viewsAfter})");
        $this->assert($updatedAtAfter === $updatedAtBefore, "incrementViews PRESERVES updated_at timestamp ('{$updatedAtBefore}' === '{$updatedAtAfter}')");
    }
}

$test = new NewsSearchIntegrationTest();
$test->run();
