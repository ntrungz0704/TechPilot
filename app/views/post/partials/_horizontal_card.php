<?php
/**
 * Horizontal Card (danh sách bài viết)
 * Variable: $p (post array)
 * Dùng postImageUrl() và postTypeLabel() từ helpers.php
 */
?>
<div class="news-horizontal-card">
    <a href="<?= url('post/detail/' . e($p['slug'])) ?>" class="news-horizontal-card__img" aria-label="<?= e($p['title']) ?>">
        <img
            src="<?= postImageUrl($p['image'] ?? '') ?>"
            alt="<?= e($p['title']) ?>"
            loading="lazy"
            onerror="this.src='<?= url('assets/images/products/placeholder-component.webp') ?>'"
        >
    </a>
    <div class="news-horizontal-card__body">
        <span class="news-badge-category"><?= postTypeLabel($p['post_type'] ?? '') ?></span>
        <h4><a href="<?= url('post/detail/' . e($p['slug'])) ?>"><?= e($p['title']) ?></a></h4>
        <?php if (!empty($p['summary'])): ?>
            <p class="news-horizontal-card__summary"><?= e($p['summary']) ?></p>
        <?php endif; ?>
        <div class="news-meta">
            <span><?= e($p['author_name'] ?? 'Đội ngũ TechPilot') ?></span>
            <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?= date('d/m/Y', strtotime($p['published_at'] ?? $p['created_at'])) ?></span>
            <?php if (!empty($p['reading_minutes'])): ?>
                <span><i class="fa-regular fa-hourglass-half" aria-hidden="true"></i> <?= (int)$p['reading_minutes'] ?> phút đọc</span>
            <?php endif; ?>
            <span><i class="fa-regular fa-eye" aria-hidden="true"></i> <?= (int)$p['views'] ?> lượt xem</span>
        </div>
    </div>
</div>
