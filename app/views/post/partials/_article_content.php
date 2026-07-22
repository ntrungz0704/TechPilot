<?php
/**
 * View Partial hiển thị nội dung bài viết & Contextual CTA.
 * Contract:
 * - $renderedContent (string)
 * - $articleHeadings (array)
 * - $articleBlocks (array)
 * - $articleWordCount (int)
 * - $articleH2Count (int)
 * - $quickSummaryHtml (string)
 * - $sourcesHtml (string)
 * - $postType (string)
 * - $categorySlug (string)
 * - $midCtaConfig (array|null)
 * - $endCtaConfig (array|null)
 */

$renderedContent  = $renderedContent ?? '';
$articleHeadings  = is_array($articleHeadings ?? null) ? $articleHeadings : [];
$articleBlocks    = is_array($articleBlocks ?? null) ? $articleBlocks : [];
$articleWordCount = max(0, (int)($articleWordCount ?? 0));
$articleH2Count   = max(0, (int)($articleH2Count ?? 0));
$quickSummaryHtml = (string)($quickSummaryHtml ?? '');
$sourcesHtml      = (string)($sourcesHtml ?? '');
$postType         = strtolower(trim((string)($postType ?? '')));
$categorySlug     = strtolower(trim((string)($categorySlug ?? '')));
$midCtaConfig     = $midCtaConfig ?? null;
$endCtaConfig     = $endCtaConfig ?? null;

$totalBlocks = count($articleBlocks);

// Step 4: Mid-Article CTA Eligibility
$allowMidCta = !empty($midCtaConfig)
    && $articleWordCount >= 800
    && $articleH2Count >= 2
    && $totalBlocks >= 8
    && in_array($postType, ['review', 'guide', 'comparison'], true);

// Step 5: Safe Mid-CTA Insertion Boundary Calculation
$midCtaInsertAfterIndex = null;

if ($allowMidCta) {
    $minIdx = 2;
    $maxIdx = $totalBlocks - 4; // Not in last 3 blocks
    $targetIdx = (int)round($totalBlocks * 0.5);

    $bestCandidate = null;
    $bestDistance = 999;

    $paragraphsInCurrentSection = 0;
    for ($i = 0; $i < $totalBlocks; $i++) {
        $blockType = $articleBlocks[$i]['type'] ?? '';

        if ($blockType === 'heading') {
            $paragraphsInCurrentSection = 0;
            continue;
        }

        if ($blockType === 'paragraph') {
            $paragraphsInCurrentSection++;

            if ($i >= $minIdx && $i <= $maxIdx) {
                // Must have at least 2 paragraphs in this section before inserting
                if ($paragraphsInCurrentSection >= 2) {
                    $nextType = $articleBlocks[$i + 1]['type'] ?? '';
                    $isSectionEnd = ($nextType === 'heading');

                    $dist = abs($i - $targetIdx);
                    if ($isSectionEnd) {
                        $dist -= 2; // Priority for section end boundary
                    }

                    if ($dist < $bestDistance) {
                        $bestDistance = $dist;
                        $bestCandidate = $i;
                    }
                }
            }
        }
    }

    $midCtaInsertAfterIndex = $bestCandidate;
}

// Build rendered HTML body
$renderedBodyHtml = '';
if (!empty($articleBlocks)) {
    for ($i = 0; $i < $totalBlocks; $i++) {
        $block = $articleBlocks[$i];
        $renderedBodyHtml .= $block['html'] . "\n";

        if ($i === $midCtaInsertAfterIndex) {
            ob_start();
            $ctaConfig = $midCtaConfig;
            $placement = 'mid-article';
            require __DIR__ . '/_article_cta.php';
            $renderedBodyHtml .= ob_get_clean() . "\n";
        }
    }
} else {
    $renderedBodyHtml = $renderedContent;
}
?>

$isContentEmpty = empty(trim((string)($post['content'] ?? ''))) && empty(trim((string)$renderedContent));
?>

<div class="news-detail-content">

    <?php if ($isContentEmpty): ?>
        <div class="news-detail__empty-notice alert alert--warning" style="margin: 20px 0; padding: 25px; border-radius: 8px; background-color: rgba(234, 179, 8, 0.1); border: 1px solid var(--warning, #eab308); color: var(--text-primary); text-align: center;">
            <i class="fa-solid fa-file-circle-exclamation" style="font-size: 2rem; color: #eab308; margin-bottom: 10px; display: block;" aria-hidden="true"></i>
            <h3 style="margin-bottom: 8px; font-size: 1.1rem; font-weight: 600;">Nội dung bài viết chưa sẵn sàng</h3>
            <p style="margin: 0; color: var(--text-secondary); font-size: 0.95rem;">Bài viết này hiện chưa có nội dung chi tiết hoặc đang trong quá trình cập nhật từ biên tập viên.</p>
        </div>
    <?php else: ?>

        <!-- Quick Summary (nếu có) -->
        <?php if (!empty($quickSummaryHtml)): ?>
            <?= $quickSummaryHtml ?>
        <?php endif; ?>
        
        <!-- Table of Contents (Mobile Accordion, Threshold: $articleH2Count >= 3) -->
        <?php if ($articleH2Count >= 3 && !empty($articleHeadings)): ?>
            <div class="news-mobile-toc-wrapper">
                <?php
                $tocVariant = 'mobile';
                $tocIdPrefix = 'mobile-toc';
                require __DIR__ . '/_article_toc.php';
                ?>
            </div>
        <?php endif; ?>

        <!-- Body Bài viết -->
        <?= $renderedBodyHtml ?>

        <!-- End CTA (Allowed for review, guide, comparison, howto - đứng trước Sources và Author Box) -->
        <?php if (!empty($endCtaConfig)): ?>
            <?php
            $ctaConfig = $endCtaConfig;
            $placement = 'end-article';
            require __DIR__ . '/_article_cta.php';
            ?>
        <?php endif; ?>

        <!-- Sources / Nguồn tham khảo (nếu có) -->
        <?php if (!empty($sourcesHtml)): ?>
            <?= $sourcesHtml ?>
        <?php endif; ?>

        <!-- Khối tác giả (Author Box) -->
        <?php require __DIR__ . '/_author_box.php'; ?>

    <?php endif; ?>
</div>

