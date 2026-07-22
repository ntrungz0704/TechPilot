<?php
/**
 * Contextual CTA Component
 * Variables:
 * - $ctaConfig (array): Cấu hình CTA từ NewsCommerceService
 * - $placement (string): 'mid-article' hoặc 'end-article'
 * - $categorySlug (string): Category slug của bài viết
 */

if (empty($ctaConfig) || empty($ctaConfig['title'])) {
    return;
}

$placementValue = trim((string)($placement ?? 'article'));
$categoryValue  = trim((string)($categorySlug ?? 'general'));
$rawCtaId       = (string)($ctaConfig['cta_id'] ?? 'cta');

$normPlacement = NewsCommerceService::normalizeTrackingValue($placementValue);
$normCtaId     = NewsCommerceService::normalizeTrackingValue($rawCtaId);

$titleId = 'articleCtaTitle_' . $normPlacement . '_' . $normCtaId;

$primaryUrl = !empty($ctaConfig['primary_btn'])
    ? NewsCommerceService::buildTrackedUrl(
        $ctaConfig['primary_btn']['path'],
        $ctaConfig['primary_btn']['params'] ?? [],
        $placementValue,
        $categoryValue
      )
    : '#';

$secondaryUrl = !empty($ctaConfig['secondary_btn'])
    ? NewsCommerceService::buildTrackedUrl(
        $ctaConfig['secondary_btn']['path'],
        $ctaConfig['secondary_btn']['params'] ?? [],
        $placementValue,
        $categoryValue
      )
    : null;
?>

<aside class="article-cta-box article-cta-<?= e($normPlacement) ?>" aria-labelledby="<?= e($titleId) ?>" data-news-cta="<?= e($normCtaId) ?>">
    <div class="article-cta-content">
        <h3 id="<?= e($titleId) ?>" class="article-cta-title">
            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
            <?= e($ctaConfig['title']) ?>
        </h3>
        
        <?php if (!empty($ctaConfig['desc'])): ?>
            <p class="article-cta-desc"><?= e($ctaConfig['desc']) ?></p>
        <?php endif; ?>

        <div class="article-cta-actions">
            <?php if (!empty($ctaConfig['primary_btn'])): ?>
                <a href="<?= e($primaryUrl) ?>" class="btn btn--primary article-cta-btn">
                    <?= e($ctaConfig['primary_btn']['label']) ?>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            <?php endif; ?>

            <?php if ($secondaryUrl && !empty($ctaConfig['secondary_btn'])): ?>
                <a href="<?= e($secondaryUrl) ?>" class="btn btn--outline article-cta-btn-secondary">
                    <?= e($ctaConfig['secondary_btn']['label']) ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</aside>
