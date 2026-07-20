<?php
/**
 * View Partial hiển thị nội dung bài viết & Contextual CTA.
 * Variables: 
 * - $safeContent (string): HTML nội dung bài viết.
 * - $headings (array): Danh sách các heading để build TOC.
 * - $blocks (array): Các block phần thân bài từ MarkdownRenderer.
 * - $midCtaConfig (array|null): Cấu hình Mid CTA.
 * - $endCtaConfig (array|null): Cấu hình End CTA.
 * - $postType (string): Loại bài viết.
 * - $categorySlug (string): Danh mục bài viết.
 */

$safeContent  = $safeContent ?? '';
$headings     = $headings ?? [];
$blocks       = $blocks ?? [];
$midCtaConfig = $midCtaConfig ?? null;
$endCtaConfig = $endCtaConfig ?? null;
$postType     = $postType ?? '';
$categorySlug = $categorySlug ?? '';

// Tính toán HTML của body (có chèn Mid CTA nếu phù hợp)
$renderedBodyHtml = '';
$totalBlocks      = count($blocks);
$allowMidCta      = !empty($midCtaConfig) 
    && $totalBlocks >= 6 
    && in_array($postType, ['review', 'guide', 'comparison'], true);

$midCtaInserted = false;
$targetMidIndex = (int)floor($totalBlocks * 0.45);

if (!empty($blocks)) {
    for ($i = 0; $i < $totalBlocks; $i++) {
        $block = $blocks[$i];
        $renderedBodyHtml .= $block['html'] . "\n";

        // Vị trí hợp lệ chèn Mid CTA:
        // - Đã qua ~45% nội dung và chưa chèn
        // - Block hiện tại không phải là heading
        // - Không phải là 2 block cuối bài
        if ($allowMidCta && !$midCtaInserted && $i >= $targetMidIndex && $i < ($totalBlocks - 2)) {
            if ($block['type'] !== 'heading') {
                ob_start();
                $ctaConfig = $midCtaConfig;
                $placement = 'mid-article';
                require __DIR__ . '/_article_cta.php';
                $renderedBodyHtml .= ob_get_clean() . "\n";
                $midCtaInserted = true;
            }
        }
    }
} else {
    $renderedBodyHtml = $safeContent;
}
?>

<div class="news-detail-content">
    
    <?php if (!empty($headings)): ?>
        <div class="news-toc" aria-label="Mục lục bài viết">
            <div class="news-toc-header">
                <strong>Nội dung bài viết</strong>
            </div>
            <ul class="news-toc-list">
                <?php foreach ($headings as $heading): ?>
                    <li class="news-toc-item news-toc-level-<?= (int)$heading['level'] ?>">
                        <a href="#<?= e($heading['id']) ?>"><?= e($heading['text']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?= $renderedBodyHtml ?>

    <?php if (!empty($endCtaConfig)): ?>
        <?php
        $ctaConfig = $endCtaConfig;
        $placement = 'end-article';
        require __DIR__ . '/_article_cta.php';
        ?>
    <?php endif; ?>
</div>
