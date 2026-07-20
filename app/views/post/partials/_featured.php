<?php
/**
 * Featured + Hero Popular Grid
 * Variables: $featured (array), $heroPopular (array)
 * Dùng postImageUrl() và postTypeLabel() từ helpers.php
 */
?>
<h3 class="news-section-title">Nổi bật nhất</h3>
<div class="news-hero-grid">
    <!-- Bài viết nổi bật lớn -->
    <div class="news-featured">
        <a href="<?= url('post/detail/' . e($featured['slug'])) ?>" class="news-featured__img" aria-label="<?= e($featured['title']) ?>">
            <img
                src="<?= postImageUrl($featured['image'] ?? '') ?>"
                alt="<?= e($featured['title']) ?>"
                loading="lazy"
                onerror="this.src='<?= url('assets/images/products/placeholder-component.webp') ?>'"
            >
        </a>
        <div class="news-featured__content">
            <span class="news-badge"><?= postTypeLabel($featured['post_type'] ?? '') ?></span>
            <h2><a href="<?= url('post/detail/' . e($featured['slug'])) ?>"><?= e($featured['title']) ?></a></h2>
            <?php if (!empty($featured['summary'])): ?>
                <p><?= e($featured['summary']) ?></p>
            <?php endif; ?>
            <div class="news-meta">
                <span><i class="fa-regular fa-clock" aria-hidden="true"></i> <?= date('d/m/Y', strtotime($featured['published_at'] ?? $featured['created_at'])) ?></span>
                <?php if (!empty($featured['reading_minutes'])): ?>
                    <span><i class="fa-regular fa-hourglass-half" aria-hidden="true"></i> <?= (int)$featured['reading_minutes'] ?> phút đọc</span>
                <?php endif; ?>
                <span><i class="fa-regular fa-eye" aria-hidden="true"></i> <?= (int)$featured['views'] ?> lượt xem</span>
            </div>
        </div>
    </div>

    <?php if (!empty($heroPopular)): ?>
    <div class="news-hero-popular">
        <?php foreach ($heroPopular as $pop): ?>
            <div class="news-hero-popular-item">
                <a href="<?= url('post/detail/' . e($pop['slug'])) ?>" class="news-hero-popular__img" aria-label="<?= e($pop['title']) ?>">
                    <img
                        src="<?= postImageUrl($pop['image'] ?? '') ?>"
                        alt="<?= e($pop['title']) ?>"
                        loading="lazy"
                        onerror="this.src='<?= url('assets/images/products/placeholder-component.webp') ?>'"
                    >
                </a>
                <div class="news-hero-popular__content">
                    <h4><a href="<?= url('post/detail/' . e($pop['slug'])) ?>"><?= e($pop['title']) ?></a></h4>
                    <div class="news-meta">
                        <span><i class="fa-solid fa-user" aria-hidden="true"></i> <?= e($pop['author_name'] ?? 'Admin') ?></span>
                        <span><i class="fa-regular fa-clock" aria-hidden="true"></i> <?= date('d/m/Y', strtotime($pop['published_at'] ?? $pop['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
