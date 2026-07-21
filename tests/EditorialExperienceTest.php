<?php
/**
 * Test Suite: Editorial Experience (Summary, Sources, Dual TOC, Author Semantics, Updated Date, Content Order)
 * Run: php tests/EditorialExperienceTest.php
 * Exit code: 0 = ALL PASS, 1 = FAIL
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/app/core/MarkdownRenderer.php';
require_once ROOT_PATH . '/app/models/Post.php';
require_once ROOT_PATH . '/app/services/NewsCommerceService.php';

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

// ── 4.5 Duplicate & Malformed Sources Handling ──────────────────────────────
$mdDupSrc = ":::sources\n- [Nguồn 1](https://src1.org)\n:::\n\nĐoạn giữa\n\n:::sources\n- [Nguồn 2](https://src2.org)\n:::\n\nKết";
$resDupSrc = $renderer->render($mdDupSrc);

tc('Sources 4.5a: First sources extracted', has($resDupSrc['sourcesHtml'], 'src1.org'));
tc('Sources 4.5b: Duplicate sources preserved in body', has($resDupSrc['html'], 'src2.org'));

$mdMalSrc = ":::sources\nUnclosed sources line here\n\nTiếp tục.";
$resMalSrc = $renderer->render($mdMalSrc);
tc('Sources 4.5c: Malformed sources preserved in body safely', has($resMalSrc['html'], 'Unclosed sources line here'));

// ── 5. Extracted Blocks Not in articleBlocks ─────────────────────────────────
$hasSummaryInBlocks = false;
foreach ($res['blocks'] as $b) {
    if (str_contains($b['html'], 'article-quick-summary')) {
        $hasSummaryInBlocks = true;
        break;
    }
}
$hasSourcesInBlocks = false;
foreach ($resSrc['blocks'] as $b) {
    if (str_contains($b['html'], 'article-sources-block')) {
        $hasSourcesInBlocks = true;
        break;
    }
}
tc('Blocks 5a: Extracted summary is NOT in articleBlocks', !$hasSummaryInBlocks);
tc('Blocks 5b: Extracted sourcesHtml is NOT in articleBlocks', !$hasSourcesInBlocks);

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

// ── 8. Production Author Schema Logic (Post::buildAuthorSchema) ──────────────
$postRealAuthor = ['has_real_author' => true, 'author_name' => 'Nguyễn Minh Hiếu'];
$schema1 = Post::buildAuthorSchema($postRealAuthor);

tc('Schema 8a: Production Post::buildAuthorSchema produces Person for real author', $schema1['@type'] === 'Person');
tc('Schema 8b: Production author name matches', $schema1['name'] === 'Nguyễn Minh Hiếu');

$postTeamFallback = ['has_real_author' => false, 'author_name' => 'Đội ngũ TechPilot'];
$schema2 = Post::buildAuthorSchema($postTeamFallback);

tc('Schema 8c: Production Post::buildAuthorSchema produces Organization for fallback', $schema2['@type'] === 'Organization');
tc('Schema 8d: Fallback name is Đội ngũ TechPilot', $schema2['name'] === 'Đội ngũ TechPilot');

$postInconsistent = ['has_real_author' => true, 'author_name' => '   '];
$schema3 = Post::buildAuthorSchema($postInconsistent);
tc('Schema 8e: Inconsistent has_real_author=true + empty author_name falls back to Organization', $schema3['@type'] === 'Organization');

// ── 9. Post::incrementViews SQL Integrity & Date Hardening ────────────────────
$postPhpContent = file_get_contents(ROOT_PATH . '/app/models/Post.php');
tc('Model 9a: incrementViews SQL explicitly retains updated_at = updated_at', has($postPhpContent, 'UPDATE posts SET views = views + 1, updated_at = updated_at WHERE id = :id'));

$invalidTs = !empty('invalid-date') ? strtotime('invalid-date') : false;
$hasValidInvalid = ($invalidTs !== false && $invalidTs > 0);
tc('Date 9b: Invalid date string rejected by date parser check', $hasValidInvalid === false);

// ── 10. No-JS Dual TOC Markup, aria-controls & Unique IDs ───────────────────
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

tc('TOC 10a: Mobile TOC aria-controls matches list ID mobile-toc-list', has($mobileTocHtml, 'aria-controls="mobile-toc-list"') && has($mobileTocHtml, 'id="mobile-toc-list"'));
tc('TOC 10b: Desktop TOC does NOT render toggle button', hasNot($desktopTocHtml, 'news-toc-toggle'));
tc('TOC 10c: Unique IDs between Mobile and Desktop TOC', has($mobileTocHtml, 'id="mobile-toc-title"') && has($desktopTocHtml, 'id="desktop-toc-title"'));

$combinedTocHtml = $mobileTocHtml . $desktopTocHtml;
preg_match_all('/\bid="([^"]+)"/', $combinedTocHtml, $idMatches);
$allIds = $idMatches[1] ?? [];
$uniqueIds = array_unique($allIds);
tc('TOC 10d: Combined Dual TOC HTML has ZERO duplicate element IDs', count($allIds) === count($uniqueIds));

// ── 11. Content Order Partial Verification ──────────────────────────────────
ob_start();
$renderedContent  = '<p>Body Text</p>';
$quickSummaryHtml = '<div class="summary">Summary</div>';
$sourcesHtml      = '<div class="sources">Sources</div>';
$endCtaConfig     = ['title' => 'End CTA'];
$post             = ['has_real_author' => false, 'author_name' => 'Đội ngũ TechPilot'];
$articleH2Count   = 0;
$articleHeadings  = [];
require ROOT_PATH . '/app/views/post/partials/_article_content.php';
$contentPartialHtml = ob_get_clean();

$posSummary  = strpos($contentPartialHtml, 'class="summary"');
$posBody     = strpos($contentPartialHtml, 'Body Text');
$posEndCta   = strpos($contentPartialHtml, 'End CTA');
$posSources  = strpos($contentPartialHtml, 'class="sources"');
$posAuthor   = strpos($contentPartialHtml, 'news-author-box');

tc('Order 11a: Quick Summary appears before Body', $posSummary !== false && $posBody !== false && $posSummary < $posBody);
tc('Order 11b: End CTA appears before Sources', $posEndCta !== false && $posSources !== false && $posEndCta < $posSources);
tc('Order 11c: Sources appears before Author Box', $posSources !== false && $posAuthor !== false && $posSources < $posAuthor);

// Output summary
echo implode("\n", $cases) . "\n";
echo "\n══════════════════════════════════════════\n";
echo "Editorial Experience Test Results: {$passed} passed, {$failed} failed\n";
echo "══════════════════════════════════════════\n";

exit($failed > 0 ? 1 : 0);
