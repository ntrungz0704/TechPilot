<?php
/**
 * Related Post Card (sidebar bài liên quan)
 * Variable: $r (related post array)
 * Dùng postImageUrl() từ helpers.php
 */
?>
<div class="news-related-item">
    <a href="<?= url('post/detail/' . e($r['slug'])) ?>" class="news-related-img" aria-label="<?= e($r['title']) ?>">
        <img
            src="<?= postImageUrl($r['image'] ?? '') ?>"
            alt="<?= e($r['title']) ?>"
            loading="lazy"
            onerror="this.src='<?= url('assets/images/products/placeholder-component.webp') ?>'"
        >
    </a>
    <div class="news-related-content">
        <h5><a href="<?= url('post/detail/' . e($r['slug'])) ?>"><?= e($r['title']) ?></a></h5>
        <span><i class="fa-regular fa-clock" aria-hidden="true"></i> <?= date('d/m/Y', strtotime($r['published_at'] ?? $r['created_at'])) ?></span>
    </div>
</div>
