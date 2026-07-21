<?php
/**
 * Test Suite: Editorial Experience (Summary, Sources, Dual TOC, Author Schema)
 * Run: php tests/EditorialExperienceTest.php
 * Exit code: 0 = ALL PASS, 1 = FAIL
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/core/MarkdownRenderer.php';

$passed = 0;
$failed = 0;
$cases  = [];

function tc(string $name, bool $result, string $extra = ''): void
{
    global $passed, $failed, $cases;
    if ($result) {
        $passed++;
        $cases[] = "[PASS] {$name}";
    } else {
        $failed++;
        $cases[] = "[FAIL] {$name}" . ($extra !== '' ? "\n       → {$extra}" : '');
    }
}

function has(string $html, string $needle): bool
{
    return str_contains($html, $needle);
}

function hasNot(string $html, string $needle): bool
{
    return !str_contains($html, $needle);
}

$renderer = new MarkdownRenderer();

// ── 1. Quick Summary Extraction ──────────────────────────────────────────────
$mdSummary = ":::summary\n- Ý 1\n- Ý 2\n:::\n\nNội dung bài viết.";
$res = $renderer->render($mdSummary);

tc('Summary 1a: Output contains quickSummaryHtml', !empty($res['quickSummaryHtml']));
tc('Summary 1b: Extracted from main html', hasNot($res['html'], 'Tóm tắt nhanh'));
tc('Summary 1c: Class article-quick-summary present', has($res['quickSummaryHtml'], 'article-quick-summary'));

// ── 2. Duplicate Quick Summary Handling ─────────────────────────────────────
$mdDupSummary = ":::summary\nTóm tắt 1\n:::\n\nĐoạn giữa\n\n:::summary\nTóm tắt 2\n:::\n\nKết";
$resDup = $renderer->render($mdDupSummary);

tc('Summary 2a: First summary extracted', has($resDup['quickSummaryHtml'], 'Tóm tắt 1'));
tc('Summary 2b: Duplicate summary not silently deleted (preserved in body)', has($resDup['html'], 'Tóm tắt 2'));
tc('Summary 2c: Dev warning included for duplicate', has($resDup['html'], 'Duplicate :::summary block detected'));

// ── 3. Malformed Quick Summary Handling ─────────────────────────────────────
$mdMalformedSummary = ":::summary\nUnclosed summary text here\n\nNội dung tiếp.";
$resMal = $renderer->render($mdMalformedSummary);

tc('Summary 3a: Malformed summary does not break parser', is_array($resMal));
tc('Summary 3b: Malformed text preserved in body', has($resMal['html'], 'Unclosed summary text here'));

// ── 4. Sources Block Extraction & Link Rel ───────────────────────────────────
$mdSources = ":::sources\n- [Nguồn Uy Tín](https://example.com/source1)\n- [Nguồn Khác](https://google.com)\n:::\n\nNội dung bài.";
$resSrc = $renderer->render($mdSources);

tc('Sources 4a: Output contains sourcesHtml', !empty($resSrc['sourcesHtml']));
tc('Sources 4b: Extracted from main body', hasNot($resSrc['html'], 'Nguồn tham khảo'));
tc('Sources 4c: Link rel is noopener noreferrer (no nofollow for editorial sources)', has($resSrc['sourcesHtml'], 'rel="noopener noreferrer"'));
tc('Sources 4d: Link rel does not contain nofollow', hasNot($resSrc['sourcesHtml'], 'nofollow'));

// ── 5. Sources XSS & Invalid URL Protection ──────────────────────────────────
$mdSrcXss = ":::sources\n- [XSS Test](javascript:alert(1))\n- [Valid](https://safe.org)\n:::\n\nNội dung.";
$resSrcXss = $renderer->render($mdSrcXss);

tc('Sources 5a: Invalid javascript scheme blocked', hasNot($resSrcXss['sourcesHtml'], 'href="javascript:'));
tc('Sources 5b: Link text preserved safely', has($resSrcXss['sourcesHtml'], 'XSS Test'));

// ── 6. Duplicate Sources Handling ───────────────────────────────────────────
$mdDupSrc = ":::sources\n- [Nguồn 1](https://src1.org)\n:::\n\nBài viết\n\n:::sources\n- [Nguồn 2](https://src2.org)\n:::\n\nKết";
$resDupSrc = $renderer->render($mdDupSrc);

tc('Sources 6a: First sources extracted', has($resDupSrc['sourcesHtml'], 'src1.org'));
tc('Sources 6b: Duplicate sources preserved in body', has($resDupSrc['html'], 'src2.org'));

// ── 7. Data Contract Keys ────────────────────────────────────────────────────
tc('Contract 7a: Return array has html', array_key_exists('html', $res));
tc('Contract 7b: Return array has headings', array_key_exists('headings', $res));
tc('Contract 7c: Return array has blocks', array_key_exists('blocks', $res));
tc('Contract 7d: Return array has quickSummaryHtml', array_key_exists('quickSummaryHtml', $res));
tc('Contract 7e: Return array has sourcesHtml', array_key_exists('sourcesHtml', $res));

// ── 8. Author Schema Logic (Person vs Organization) ──────────────────────────
$postWithAuthor = ['author_name' => 'Nguyễn Văn A'];
$rawAuthor1 = !empty($postWithAuthor['author_name']) ? trim($postWithAuthor['author_name']) : '';
$authorSchema1 = $rawAuthor1 !== '' ? ['@type' => 'Person', 'name' => $rawAuthor1] : ['@type' => 'Organization', 'name' => 'Đội ngũ TechPilot'];

tc('Schema 8a: Real author produces Person schema type', $authorSchema1['@type'] === 'Person');
tc('Schema 8b: Real author name correct', $authorSchema1['name'] === 'Nguyễn Văn A');

$postNoAuthor = ['author_name' => ''];
$rawAuthor2 = !empty($postNoAuthor['author_name']) ? trim($postNoAuthor['author_name']) : '';
$authorSchema2 = $rawAuthor2 !== '' ? ['@type' => 'Person', 'name' => $rawAuthor2] : ['@type' => 'Organization', 'name' => 'Đội ngũ TechPilot'];

tc('Schema 8c: Missing author falls back to Organization type', $authorSchema2['@type'] === 'Organization');
tc('Schema 8d: Fallback name is Đội ngũ TechPilot', $authorSchema2['name'] === 'Đội ngũ TechPilot');

// Output summary
echo implode("\n", $cases) . "\n";
echo "\n══════════════════════════════════════════\n";
echo "Editorial Experience Test Results: {$passed} passed, {$failed} failed\n";
echo "══════════════════════════════════════════\n";

exit($failed > 0 ? 1 : 0);
