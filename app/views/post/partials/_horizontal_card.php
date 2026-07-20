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
<div class="news-horizontal-card">
    <a href="<?= url('post/detail/' . $p['slug']) ?>" class="news-horizontal-card__img">
        <img src="<?= url('assets/images/news/' . e($p['image'])) ?>" alt="<?= e($p['title']) ?>">
    </a>
    <div class="news-horizontal-card__body">
        <span class="news-badge-category"><?= getCategoryLabel($p['category_slug'] ?? '', $p['post_type'] ?? '') ?></span>
        <h4><a href="<?= url('post/detail/' . $p['slug']) ?>"><?= e($p['title']) ?></a></h4>
        <p class="news-horizontal-card__summary"><?= e($p['summary']) ?></p>
        <div class="news-meta">
            <span><?= e($p['author_name'] ?? 'Đội ngũ TechPilot') ?></span>
            <span><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($p['published_at'] ?? $p['created_at'])) ?></span>
            <?php if (!empty($p['reading_minutes'])): ?>
                <span><i class="fa-regular fa-hourglass-half"></i> <?= (int)$p['reading_minutes'] ?> phút đọc</span>
            <?php endif; ?>
            <span><i class="fa-regular fa-eye"></i> <?= (int)$p['views'] ?> lượt xem</span>
        </div>
    </div>
</div>
