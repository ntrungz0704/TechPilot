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

$ctaId        = e($ctaConfig['cta_id'] ?? 'cta-' . $placement);
$titleId      = 'articleCtaTitle_' . str_replace('-', '_', $ctaId);
$placementTag = e($placement);
$catTag       = e($categorySlug ?? 'general');
$dataTracking = e($ctaId);

$primaryUrl = !empty($ctaConfig['primary_btn'])
    ? NewsCommerceService::buildTrackedUrl(
        $ctaConfig['primary_btn']['path'],
        $ctaConfig['primary_btn']['params'] ?? [],
        $placementTag,
        $catTag
      )
    : '#';

$secondaryUrl = !empty($ctaConfig['secondary_btn'])
    ? NewsCommerceService::buildTrackedUrl(
        $ctaConfig['secondary_btn']['path'],
        $ctaConfig['secondary_btn']['params'] ?? [],
        $placementTag,
        $catTag
      )
    : null;
?>

<aside 
    class="article-cta-box article-cta-<?= $placementTag ?>" 
    aria-labelledby="<?= $titleId ?>" 
    data-news-cta="<?= $dataTracking ?>"
>
    <div class="article-cta-content">
        <h4 id="<?= $titleId ?>" class="article-cta-title">
            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
            <?= e($ctaConfig['title']) ?>
        </h4>
        
        <?php if (!empty($ctaConfig['desc'])): ?>
            <p class="article-cta-desc"><?= e($ctaConfig['desc']) ?></p>
        <?php endif; ?>

        <div class="article-cta-actions">
            <?php if (!empty($ctaConfig['primary_btn'])): ?>
                <a href="<?= $primaryUrl ?>" class="btn btn--primary article-cta-btn">
                    <?= e($ctaConfig['primary_btn']['label']) ?>
                    <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            <?php endif; ?>

            <?php if ($secondaryUrl && !empty($ctaConfig['secondary_btn'])): ?>
                <a href="<?= $secondaryUrl ?>" class="btn btn--outline article-cta-btn-secondary">
                    <?= e($ctaConfig['secondary_btn']['label']) ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</aside>
