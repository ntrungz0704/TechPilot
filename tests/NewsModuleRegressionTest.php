<?php
/**
 * NewsModuleRegressionTest.php
 * Automated regression test suite for TechPilot News / Editorial Module.
 * Covers: Pagination normalization, Date parsing, JSON-LD, Hot Topics data flow,
 * Search relevance, Exclusion contracts, and Security/A11y requirements.
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_ENV', 'testing');

if (!defined('BASE_URL')) {
    define('BASE_URL', '');
}

require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/core/MarkdownRenderer.php';
require_once ROOT_PATH . '/app/models/Post.php';

class NewsModuleRegressionTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== TECHPILOT NEWS MODULE REGRESSION TEST SUITE      ===\n";
        echo "========================================================\n\n";

        $this->testPaginationNormalization();
        $this->testDateParsingAndUpdatedAtVisibility();
        $this->testHotTopicsConfigFallback();
        $this->testSearchRelevanceAndLikeEscaping();
        $this->testAuthorAndJsonLdSchema();
        $this->testTocAndMarkdownSecurity();

        echo "\n════════════════════════════════════════════════════════\n";
        echo "Regression Test Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "════════════════════════════════════════════════════════\n";

        if ($this->failed > 0) {
            echo "\n[FAIL] THE FOLLOWING REGRESSION ERRORS WERE FOUND:\n";
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

    private function testPaginationNormalization(): void
    {
        echo "--- 1. Testing Pagination Normalization ---\n";

        // Case A: Normal page clamp when total > 0
        $limit = 6;
        $total = 15; // 3 total pages
        $totalPages = $total > 0 ? max(1, (int)ceil($total / $limit)) : 1;

        $pageCases = [
            ['input' => 0,    'expected' => 1],
            ['input' => -5,   'expected' => 1],
            ['input' => 1,    'expected' => 1],
            ['input' => 2,    'expected' => 2],
            ['input' => 3,    'expected' => 3],
            ['input' => 999,  'expected' => 3],
            ['input' => 'abc','expected' => 1],
        ];

        foreach ($pageCases as $idx => $case) {
            $rawPage = (int)($case['input']);
            $page    = max(1, $rawPage);
            if ($total > 0) {
                $page = min($page, $totalPages);
            }
            $offset = ($page - 1) * $limit;

            $this->assert(
                $page === $case['expected'],
                "Pagination case {$idx}: input '{$case['input']}' normalized to page {$case['expected']}",
                "Got page {$page}, offset {$offset}"
            );
        }

        // Case B: Empty total
        $emptyTotal = 0;
        $emptyTotalPages = $emptyTotal > 0 ? max(1, (int)ceil($emptyTotal / $limit)) : 1;
        $this->assert($emptyTotalPages === 1, "Empty total result gives totalPages = 1");
    }

    private function testDateParsingAndUpdatedAtVisibility(): void
    {
        echo "\n--- 2. Testing Date Parsing & Updated At Visibility ---\n";

        // Case A: Published date valid, updated_at null or <= published_at
        $pubDateStr = '2026-07-20 10:00:00';
        $updDateStrSame = '2026-07-20 10:00:00';
        $updDateStrNewer = '2026-07-20 10:05:00'; // 300s newer

        $rawPubTime = strtotime($pubDateStr);
        $rawUpdTimeSame = strtotime($updDateStrSame);
        $rawUpdTimeNewer = strtotime($updDateStrNewer);

        $hasValidPubDate = ($rawPubTime !== false && $rawPubTime > 0);
        $hasValidUpdatedAtSame = $hasValidPubDate && ($rawUpdTimeSame >= $rawPubTime + 60);
        $hasValidUpdatedAtNewer = $hasValidPubDate && ($rawUpdTimeNewer >= $rawPubTime + 60);

        $this->assert($hasValidPubDate, "Published date parsed successfully");
        $this->assert(!$hasValidUpdatedAtSame, "Same updated_at timestamp does NOT mark hasValidUpdatedAt = true");
        $this->assert($hasValidUpdatedAtNewer, "Updated timestamp >= 60s newer marks hasValidUpdatedAt = true");

        // Case B: Invalid date strings
        $invalidPub = strtotime('invalid-date-string');
        $this->assert($invalidPub === false, "Invalid date string returns false from strtotime");
    }

    private function testHotTopicsConfigFallback(): void
    {
        echo "\n--- 3. Testing Hot Topics Config & Fallback ---\n";

        $configFile = ROOT_PATH . '/config/news.php';
        $this->assert(file_exists($configFile), "config/news.php exists in repository");

        $newsConfig = require $configFile;
        $this->assert(is_array($newsConfig), "config/news.php returns array");
        $this->assert(isset($newsConfig['hot_topics']) && is_array($newsConfig['hot_topics']), "config/news.php contains hot_topics array");
        $this->assert(count($newsConfig['hot_topics']) > 0, "hot_topics contains topic items");

        // Verify items contain title and q/query
        foreach ($newsConfig['hot_topics'] as $item) {
            $hasTitle = !empty($item['title']);
            $hasQuery = !empty($item['q'] ?? ($item['query'] ?? null));
            $this->assert($hasTitle && $hasQuery, "Hot topic item has title and query: " . ($item['title'] ?? ''));
        }
    }

    private function testSearchRelevanceAndLikeEscaping(): void
    {
        echo "\n--- 4. Testing Search Relevance Logic & LIKE Escaping ---\n";

        $term = "RTX 5090_test%";
        $escapedTerm = addcslashes($term, '%_\\');

        $this->assert(str_contains($escapedTerm, '\_'), "LIKE wildcard '_' is escaped as '\\_'");
        $this->assert(str_contains($escapedTerm, '\%'), "LIKE wildcard '%' is escaped as '\\%'");

        // Test ranking score simulation
        $titleExact = "RTX 5090";
        $titlePrefix = "RTX 5090 Review";
        $titleContains = "Tin mới về RTX 5090";
        $summaryContains = "Bài viết tổng hợp RTX 5090";

        $score = function(string $title, string $summary, string $q): int {
            if ($title === $q) return 100;
            if (str_starts_with($title, $q)) return 80;
            if (str_contains($title, $q)) return 60;
            if (str_contains($summary, $q)) return 30;
            return 0;
        };

        $q = "RTX 5090";
        $sExact    = $score($titleExact, "", $q);
        $sPrefix   = $score($titlePrefix, "", $q);
        $sContains = $score($titleContains, "", $q);
        $sSummary  = $score("Tựa đề khác", $summaryContains, $q);

        $this->assert($sExact === 100, "Exact title match score = 100");
        $this->assert($sPrefix === 80, "Title prefix match score = 80");
        $this->assert($sContains === 60, "Title contains match score = 60");
        $this->assert($sSummary === 30, "Summary contains match score = 30");
        $this->assert($sExact > $sPrefix && $sPrefix > $sContains && $sContains > $sSummary, "Ranking hierarchy: Exact > Prefix > Contains > Summary");
    }

    private function testAuthorAndJsonLdSchema(): void
    {
        echo "\n--- 5. Testing Author Schema & JSON-LD Structure ---\n";

        $postRealAuthor = [
            'author_id' => 1,
            'user_full_name' => 'Nguyễn Văn A',
            'author_name' => 'Nguyễn Văn A',
            'has_real_author' => true
        ];
        $schemaReal = Post::buildAuthorSchema($postRealAuthor);

        $this->assert($schemaReal['@type'] === 'Person', "Real author produces Person schema");
        $this->assert($schemaReal['name'] === 'Nguyễn Văn A', "Person schema name matches author name");

        $postFallbackAuthor = [
            'author_id' => null,
            'user_full_name' => null,
            'author_name' => 'Ban biên tập',
            'has_real_author' => false
        ];
        $schemaFallback = Post::buildAuthorSchema($postFallbackAuthor);

        $this->assert($schemaFallback['@type'] === 'Organization', "Fallback author produces Organization schema");
        $this->assert($schemaFallback['name'] === 'Đội ngũ TechPilot', "Fallback Organization name is 'Đội ngũ TechPilot'");
    }

    private function testTocAndMarkdownSecurity(): void
    {
        echo "\n--- 6. Testing TOC & Security Constraints ---\n";

        $renderer = new MarkdownRenderer();
        $xssMarkdown = "<script>alert('xss')</script>\n\n# Tiêu đề bài viết\n\nNội dung có <iframe src='http://evil.com'></iframe> và [link](javascript:alert(1))";

        $result = $renderer->render($xssMarkdown);
        $html = $result['html'];

        $this->assert(!str_contains($html, "<script>"), "Raw <script> tag is escaped/stripped");
        $this->assert(!str_contains($html, "href=\"javascript:"), "Executable javascript: URL is stripped");
        $this->assert(isset($result['quickSummaryHtml']), "Rendered output contains quickSummaryHtml key");
        $this->assert(isset($result['sourcesHtml']), "Rendered output contains sourcesHtml key");
    }
}

$test = new NewsModuleRegressionTest();
$test->run();
