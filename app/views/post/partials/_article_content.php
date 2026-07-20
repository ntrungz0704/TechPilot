<?php
/**
 * View Partial hiển thị nội dung bài viết & Contextual CTA.
 * Contract:
 * - $renderedContent (string)
 * - $articleHeadings (array)
 * - $articleBlocks (array)
 * - $articleWordCount (int)
 * - $articleH2Count (int)
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

<div class="news-detail-content">
    
    <!-- Step 6 & 7: Table of Contents (Threshold: $articleH2Count >= 3) -->
    <?php if ($articleH2Count >= 3 && !empty($articleHeadings)): ?>
        <nav class="news-toc" aria-labelledby="articleTocTitle">
            <div class="news-toc-header">
                <strong id="articleTocTitle">Nội dung bài viết</strong>
                <button type="button" class="news-toc-toggle" aria-expanded="false" aria-controls="articleTocList" aria-label="Thu gọn/Mở rộng mục lục">
                    <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                </button>
            </div>
            <ul id="articleTocList" class="news-toc-list">
                <?php foreach ($articleHeadings as $heading): ?>
                    <li class="news-toc-item news-toc-level-<?= (int)$heading['level'] ?>">
                        <a href="#<?= e($heading['id']) ?>"><?= e($heading['text']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <?= $renderedBodyHtml ?>

    <!-- End CTA (Allowed for review, guide, comparison, howto) -->
    <?php if (!empty($endCtaConfig)): ?>
        <?php
        $ctaConfig = $endCtaConfig;
        $placement = 'end-article';
        require __DIR__ . '/_article_cta.php';
        ?>
    <?php endif; ?>
</div>
