<?php
if (!function_exists('getCategoryLabel')) {
    function getCategoryLabel(string $slug, string $type): string {
        $labels = [
            'news' => 'Tin mới',
            'review' => 'Review',
            'guide' => 'Tư vấn',
            'comparison' => 'So sánh',
        ];
        return $labels[$type] ?? 'Công nghệ';
    }
}
?>
<div class="news-hero-grid">
    <div class="news-featured">
        <a href="<?= url('post/detail/' . $featured['slug']) ?>" class="news-featured__img">
            <img src="<?= url('assets/images/news/' . e($featured['image'])) ?>" alt="<?= e($featured['title']) ?>">
        </a>
        <div class="news-featured__content">
            <span class="news-badge"><?= getCategoryLabel($featured['category_slug'], $featured['post_type']) ?></span>
            <h2><a href="<?= url('post/detail/' . $featured['slug']) ?>"><?= e($featured['title']) ?></a></h2>
            <p><?= e($featured['summary']) ?></p>
            <div class="news-meta">
                <span><i class="fa-regular fa-clock"></i> <?= date('d/m/Y', strtotime($featured['published_at'] ?? $featured['created_at'])) ?></span>
                <?php if (!empty($featured['reading_minutes'])): ?>
                    <span><i class="fa-regular fa-hourglass-half"></i> <?= (int)$featured['reading_minutes'] ?> phút đọc</span>
                <?php endif; ?>
                <span><i class="fa-regular fa-eye"></i> <?= (int)$featured['views'] ?> lượt xem</span>
            </div>
        </div>
    </div>

    <?php if (!empty($heroPopular)): ?>
    <div class="news-hero-popular">
        <?php foreach ($heroPopular as $pop): ?>
            <div class="news-hero-popular-item">
                <a href="<?= url('post/detail/' . $pop['slug']) ?>" class="news-hero-popular__img">
                    <img src="<?= url('assets/images/news/' . e($pop['image'])) ?>" alt="<?= e($pop['title']) ?>">
                </a>
                <div class="news-hero-popular__content">
                    <span class="news-badge-category"><?= getCategoryLabel($pop['category_slug'], $pop['post_type']) ?></span>
                    <h4><a href="<?= url('post/detail/' . $pop['slug']) ?>"><?= e($pop['title']) ?></a></h4>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
