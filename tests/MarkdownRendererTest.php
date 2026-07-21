<?php
/**
 * MarkdownRenderer – Renderer Test Suite
 *
 * Chạy từ project root:
 *   php tests/MarkdownRendererTest.php
 *
 * Không đặt trong public web root.
 * Exit code: 0 = tất cả pass; 1 = có case fail.
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

$r = new MarkdownRenderer();

// ══════════════════════════════════════════════════════════════════════════════
// TC1 – Code fence có dòng trống xung quanh
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("Trước\n\n```php\necho 'hello';\n```\n\nSau");
tc('TC1a – code có dòng trống, escape đúng',  has($res['html'], 'echo &#039;hello&#039;;'));
tc('TC1b – có <pre>',                          has($res['html'], '<pre>'));
tc('TC1c – đoạn trước là <p>',                has($res['html'], '<p>Trước</p>'));
tc('TC1d – đoạn sau là <p>',                  has($res['html'], '<p>Sau</p>'));

// ══════════════════════════════════════════════════════════════════════════════
// TC2 – Code fence không có dòng trống xung quanh
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("Trước\n```php\necho 'hello';\n```\nSau");
tc('TC2a – code không dòng trống, render được', has($res['html'], '<pre>'));
tc('TC2b – placeholder không lọt ra ngoài',     hasNot($res['html'], '__TECHPILOT_CODE_'));

// ══════════════════════════════════════════════════════════════════════════════
// TC3 – Code fence ở đầu document
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("```php\necho 'start';\n```\n\nĐoạn sau");
tc('TC3 – code ở đầu document',  has($res['html'], 'echo &#039;start&#039;;'));
tc('TC3b – đoạn sau render đúng', has($res['html'], '<p>Đoạn sau</p>'));

// ══════════════════════════════════════════════════════════════════════════════
// TC4 – Code fence ở cuối document
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("Đoạn đầu\n\n```php\necho 'end';\n```");
tc('TC4 – code ở cuối document', has($res['html'], 'echo &#039;end&#039;;'));

// ══════════════════════════════════════════════════════════════════════════════
// TC5 – Code chứa HTML (XSS escape)
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("```html\n<script>alert('xss')</script>\n```");
tc('TC5a – HTML trong code escaped',         has($res['html'], '&lt;script&gt;'));
tc('TC5b – không render raw <script>',       hasNot($res['html'], '<script>alert'));

// ══════════════════════════════════════════════════════════════════════════════
// TC6 – Code chứa </script> trong nội dung
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("```js\nvar x = '</script>';\n```");
tc('TC6 – </script> trong code escaped', has($res['html'], '&lt;/script&gt;'));

// ══════════════════════════════════════════════════════════════════════════════
// TC7 – Code chứa ba backtick trong string (edge case)
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("```js\n// code có ` backtick ` đơn\nconsole.log('ok');\n```");
tc('TC7 – backtick đơn trong code', has($res['html'], 'backtick'));

// ══════════════════════════════════════════════════════════════════════════════
// TC8 – Code block liên tiếp
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("```php\necho 1;\n```\n\n```php\necho 2;\n```");
tc('TC8a – block 1 trong code liên tiếp', has($res['html'], 'echo 1;'));
tc('TC8b – block 2 trong code liên tiếp', has($res['html'], 'echo 2;'));
// Phải có 2 thẻ <pre> riêng biệt
tc('TC8c – hai <pre> riêng', substr_count($res['html'], '<pre>') === 2);

// ══════════════════════════════════════════════════════════════════════════════
// TC9 – Code block không có ngôn ngữ
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("```\nplain block\n```");
tc('TC9a – code không có language render đúng', has($res['html'], 'plain block'));
tc('TC9b – không có class="language-"',          hasNot($res['html'], 'class="language-"'));

// ══════════════════════════════════════════════════════════════════════════════
// TC10 – Document chỉ có một code block
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("```php\necho 'only';\n```");
tc('TC10 – document chỉ có một code block', has($res['html'], "echo &#039;only&#039;;"));

// ══════════════════════════════════════════════════════════════════════════════
// TC11 – Pros block
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render(":::pros\n- Nhanh\n- Nhẹ\n:::");
tc('TC11a – pros-block class',    has($res['html'], 'pros-block'));
tc('TC11b – Ưu điểm label',      has($res['html'], 'Ưu điểm'));
tc('TC11c – item Nhanh trong li', has($res['html'], '<li>Nhanh</li>'));

// ══════════════════════════════════════════════════════════════════════════════
// TC12 – Cons block
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render(":::cons\nGiá cao\n:::");
tc('TC12a – cons-block class',    has($res['html'], 'cons-block'));
tc('TC12b – Nhược điểm label',   has($res['html'], 'Nhược điểm'));

// ══════════════════════════════════════════════════════════════════════════════
// TC13 – YouTube @[youtube](ID) syntax
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("@[youtube](dQw4w9WgXcQ)");
tc('TC13a – YouTube embed nocookie',  has($res['html'], 'youtube-nocookie.com/embed/dQw4w9WgXcQ'));
tc('TC13b – loading=lazy',            has($res['html'], 'loading="lazy"'));
tc('TC13c – referrerpolicy',          has($res['html'], 'referrerpolicy="strict-origin-when-cross-origin"'));
tc('TC13d – web-share trong allow',   has($res['html'], 'web-share'));
tc('TC13e – allowfullscreen',         has($res['html'], 'allowfullscreen'));
tc('TC13f – không có frameborder',    hasNot($res['html'], 'frameborder'));

// ══════════════════════════════════════════════════════════════════════════════
// TC14 – YouTube URL đầy đủ
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("https://www.youtube.com/watch?v=dQw4w9WgXcQ");
tc('TC14 – YouTube URL đầy đủ', has($res['html'], 'youtube-nocookie.com/embed/dQw4w9WgXcQ'));

// ══════════════════════════════════════════════════════════════════════════════
// TC15 – YouTube URL với playlist/timestamp (vẫn extract ID đúng)
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s&list=PLabcdef123");
tc('TC15 – YouTube URL có timestamp+playlist', has($res['html'], 'youtube-nocookie.com/embed/dQw4w9WgXcQ'));

// ══════════════════════════════════════════════════════════════════════════════
// TC16 – YouTube ID không hợp lệ (< 11 ký tự)
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("@[youtube](tooshort)");
tc('TC16 – YouTube ID ngắn không render iframe', hasNot($res['html'], 'youtube-nocookie.com'));

// ══════════════════════════════════════════════════════════════════════════════
// TC17 – YouTube ID không hợp lệ (ký tự đặc biệt)
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render("@[youtube](invalid!!!!!)");
tc('TC17 – YouTube ID có ký tự đặc biệt không render', hasNot($res['html'], 'youtube-nocookie.com'));

// ══════════════════════════════════════════════════════════════════════════════
// TC18 – Arbitrary iframe không render
// ══════════════════════════════════════════════════════════════════════════════
$res = $r->render('<iframe src="https://evil.com" onload="steal()"></iframe>');
tc('TC18 – iframe không render raw', hasNot($res['html'], '<iframe'));

// ══════════════════════════════════════════════════════════════════════════════
// Output
// ══════════════════════════════════════════════════════════════════════════════
echo implode("\n", $cases) . "\n";
echo "\n══════════════════════════════════════════\n";
echo "Results: {$passed} passed, {$failed} failed\n";
echo "══════════════════════════════════════════\n";

exit($failed > 0 ? 1 : 0);
