<?php
/**
 * Related Post Card (sidebar bài liên quan)
 * Variable: $r (related post array)
 * Dùng postImageUrl() từ helpers.php
 */
?>
<a href="<?= url('post/detail/' . e($r['slug'])) ?>" class="news-related-item">
    <?php if (!empty($r['image'])): ?>
    <div class="news-related-img">
        <img
            src="<?= postImageUrl($r['image']) ?>"
            alt="<?= e($r['title'] ?? '') ?>"
            loading="lazy"
            onerror="this.src='<?= url('assets/images/products/placeholder-component.webp') ?>'"
        >
    </div>
    <?php endif; ?>
    <div class="news-related-content">
        <h5><?= e($r['title'] ?? '') ?></h5>
        <?php if (!empty($r['published_at']) || !empty($r['created_at'])): ?>
        <span><i class="fa-regular fa-clock" aria-hidden="true"></i> <?= date('d/m/Y', strtotime($r['published_at'] ?? $r['created_at'])) ?></span>
        <?php endif; ?>
    </div>
</a>
