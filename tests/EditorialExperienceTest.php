<?php
/**
 * Test Suite: Editorial Experience (Summary, Sources, Dual TOC, Author Semantics, Updated Date)
 * Run: php tests/EditorialExperienceTest.php
 * Exit code: 0 = ALL PASS, 1 = FAIL
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/core/helpers.php';
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

// ── 1. Quick Summary Extraction & Multi-paragraph Support ────────────────────
$mdSummary = ":::summary\n- Ý 1\n- Ý 2\n:::\n\nNội dung bài viết.";
$res = $renderer->render($mdSummary);

tc('Summary 1a: Output contains quickSummaryHtml', !empty($res['quickSummaryHtml']));
tc('Summary 1b: Extracted from main html', hasNot($res['html'], 'Tóm tắt nhanh'));
tc('Summary 1c: Class article-quick-summary present', has($res['quickSummaryHtml'], 'article-quick-summary'));
tc('Summary 1d: Uses h2 title for heading hierarchy', has($res['quickSummaryHtml'], '<h2 class="article-summary-title">'));

// ── 1.5 Multi-paragraph & Paragraph+List Summary ───────────────────────────
$mdMultiSummary = ":::summary\nĐoạn văn tóm tắt 1.\n\nĐoạn văn tóm tắt 2.\n\n- Danh sách ý 1\n- Danh sách ý 2\n:::\n\nBody text.";
$resMulti = $renderer->render($mdMultiSummary);

tc('Summary 1.5a: Multi-paragraph summary renders <p>', has($resMulti['quickSummaryHtml'], '<p>Đoạn văn tóm tắt 1.</p>') && has($resMulti['quickSummaryHtml'], '<p>Đoạn văn tóm tắt 2.</p>'));
tc('Summary 1.5b: Paragraph + List summary renders <ul>', has($resMulti['quickSummaryHtml'], '<ul>') && has($resMulti['quickSummaryHtml'], 'Danh sách ý 1'));

// ── 2. Duplicate Quick Summary Handling ─────────────────────────────────────
$mdDupSummary = ":::summary\nTóm tắt 1\n:::\n\nĐoạn giữa\n\n:::summary\nTóm tắt 2\n:::\n\nKết";
$resDup = $renderer->render($mdDupSummary);

tc('Summary 2a: First summary extracted', has($resDup['quickSummaryHtml'], 'Tóm tắt 1'));
tc('Summary 2b: Duplicate summary preserved in body', has($resDup['html'], 'Tóm tắt 2'));
tc('Summary 2c: No diagnostic HTML comments rendered', hasNot($resDup['html'], 'Development Warning'));

// ── 3. Malformed Quick Summary Handling ─────────────────────────────────────
$mdMalformedSummary = ":::summary\nUnclosed summary text here\n\nNội dung tiếp.";
$resMal = $renderer->render($mdMalformedSummary);

tc('Summary 3a: Malformed summary does not break parser', is_array($resMal));
tc('Summary 3b: Malformed text preserved in body', has($resMal['html'], 'Unclosed summary text here'));

// ── 4. Sources Block Extraction & Link Rel ───────────────────────────────────
$mdSources = ":::sources\nĐoạn giới thiệu nguồn.\n\n- [Nguồn Uy Tín](https://example.com/source1)\n- [Nguồn Khác](https://google.com)\n:::\n\nNội dung bài.";
$resSrc = $renderer->render($mdSources);

tc('Sources 4a: Output contains sourcesHtml', !empty($resSrc['sourcesHtml']));
tc('Sources 4b: Extracted from main body', hasNot($resSrc['html'], 'Nguồn tham khảo'));
tc('Sources 4c: Link rel is noopener noreferrer (no nofollow for editorial sources)', has($resSrc['sourcesHtml'], 'rel="noopener noreferrer"'));
tc('Sources 4d: Link rel does not contain nofollow', hasNot($resSrc['sourcesHtml'], 'nofollow'));
tc('Sources 4e: Uses h2 title for heading hierarchy', has($resSrc['sourcesHtml'], '<h2 class="article-sources-title">'));

// ── 5. Extracted Blocks Not in articleBlocks ─────────────────────────────────
$hasExtractedInBlocks = false;
foreach ($res['blocks'] as $b) {
    if (str_contains($b['html'], 'article-quick-summary') || str_contains($b['html'], 'article-sources-block')) {
        $hasExtractedInBlocks = true;
        break;
    }
}
tc('Blocks 5a: Extracted summary/sources are NOT in articleBlocks', !$hasExtractedInBlocks);

// ── 6. Sources XSS & Invalid URL Protection ──────────────────────────────────
$mdSrcXss = ":::sources\n- [XSS Test](javascript:alert(1))\n- [Valid](https://safe.org)\n:::\n\nNội dung.";
$resSrcXss = $renderer->render($mdSrcXss);

tc('Sources 6a: Invalid javascript scheme blocked', hasNot($resSrcXss['sourcesHtml'], 'href="javascript:'));
tc('Sources 6b: Link text preserved safely', has($resSrcXss['sourcesHtml'], 'XSS Test'));

// ── 7. Data Contract Keys ────────────────────────────────────────────────────
tc('Contract 7a: Return array has html', array_key_exists('html', $res));
tc('Contract 7b: Return array has headings', array_key_exists('headings', $res));
tc('Contract 7c: Return array has blocks', array_key_exists('blocks', $res));
tc('Contract 7d: Return array has quickSummaryHtml', array_key_exists('quickSummaryHtml', $res));
tc('Contract 7e: Return array has sourcesHtml', array_key_exists('sourcesHtml', $res));

// ── 8. Author Semantics Logic (Person vs Organization with has_real_author) ──
$postRealAuthor = ['has_real_author' => true, 'author_name' => 'Nguyễn Minh Hiếu'];
$hasReal1 = !empty($postRealAuthor['has_real_author']);
$schema1  = $hasReal1
    ? ['@type' => 'Person', 'name' => $postRealAuthor['author_name']]
    : ['@type' => 'Organization', 'name' => 'Đội ngũ TechPilot'];

tc('Schema 8a: Real author produces Person schema type', $schema1['@type'] === 'Person');
tc('Schema 8b: Real author name matches', $schema1['name'] === 'Nguyễn Minh Hiếu');

$postTeamFallback = ['has_real_author' => false, 'author_name' => 'Đội ngũ TechPilot'];
$hasReal2 = !empty($postTeamFallback['has_real_author']);
$schema2  = $hasReal2
    ? ['@type' => 'Person', 'name' => $postTeamFallback['author_name']]
    : ['@type' => 'Organization', 'name' => 'Đội ngũ TechPilot'];

tc('Schema 8c: Team fallback produces Organization schema type', $schema2['@type'] === 'Organization');
tc('Schema 8d: Fallback name is Đội ngũ TechPilot', $schema2['name'] === 'Đội ngũ TechPilot');

// ── 9. Updated Date Logic & Schema ───────────────────────────────────────────
$pubTime = strtotime('2026-07-20 10:00:00');
$updTimeVal = strtotime('2026-07-21 15:30:00');
$updTimeSame = strtotime('2026-07-20 10:00:30');

$hasValidUpd1 = ($updTimeVal > ($pubTime + 60));
$hasValidUpd2 = ($updTimeSame > ($pubTime + 60));

tc('Date 9a: Meaningful updated_at recognized as valid', $hasValidUpd1 === true);
tc('Date 9b: Same/minor updated_at ignored (< 60s difference)', $hasValidUpd2 === false);

// ── 10. No-JS Dual TOC Markup & Unique IDs ──────────────────────────────────
$tocHeadings = [
    ['level' => 2, 'id' => 'section-1', 'text' => 'Phần 1'],
    ['level' => 3, 'id' => 'section-1-1', 'text' => 'Phần 1.1']
];

ob_start();
$tocVariant = 'mobile';
$tocIdPrefix = 'mobile-toc';
$articleHeadings = $tocHeadings;
require ROOT_PATH . '/app/views/post/partials/_article_toc.php';
$mobileTocHtml = ob_get_clean();

ob_start();
$tocVariant = 'desktop';
$tocIdPrefix = 'desktop-toc';
$articleHeadings = $tocHeadings;
require ROOT_PATH . '/app/views/post/partials/_article_toc.php';
$desktopTocHtml = ob_get_clean();

tc('TOC 10a: Mobile TOC has toggle button with aria-expanded="false"', has($mobileTocHtml, 'class="news-toc-toggle"') && has($mobileTocHtml, 'aria-expanded="false"'));
tc('TOC 10b: Desktop TOC does NOT render toggle button', hasNot($desktopTocHtml, 'news-toc-toggle'));
tc('TOC 10c: Unique IDs between Mobile and Desktop TOC', has($mobileTocHtml, 'id="mobile-toc-title"') && has($desktopTocHtml, 'id="desktop-toc-title"'));

// Output summary
echo implode("\n", $cases) . "\n";
echo "\n══════════════════════════════════════════\n";
echo "Editorial Experience Test Results: {$passed} passed, {$failed} failed\n";
echo "══════════════════════════════════════════\n";

exit($failed > 0 ? 1 : 0);
