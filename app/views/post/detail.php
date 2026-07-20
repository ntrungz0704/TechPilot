<?php
$post = $post ?? null;
$related = $related ?? [];
$safeContent = $safeContent ?? '';

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

<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <a href="<?= url('post') ?>">Tin tức công nghệ</a> <i class="fa-solid fa-chevron-right"></i>
    <span><?= e($post['title']) ?></span>
</section>

<!-- ===== CHI TIẾT BÀI VIẾT ===== -->
<section class="container news-page">
    <div class="news-layout">
        
        <!-- Cột trái: Nội dung chi tiết bài viết -->
        <article class="news-main news-detail-card">
            <span class="news-badge-category" style="margin-bottom: 12px; display: inline-block;">
                <?= getCategoryLabel($post['category_slug'] ?? '', $post['post_type'] ?? '') ?>
            </span>
            <h1 class="news-detail-title"><?= e($post['title']) ?></h1>
            
            <div class="news-meta news-detail-meta">
                <span><i class="fa-solid fa-user"></i> Tác giả: <strong><?= e($post['author_name'] ?? 'Đội ngũ TechPilot') ?></strong></span>
                <span><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></span>
                <?php if (!empty($post['reading_minutes'])): ?>
                    <span><i class="fa-regular fa-hourglass-half"></i> <?= (int)$post['reading_minutes'] ?> phút đọc</span>
                <?php endif; ?>
                <span><i class="fa-regular fa-eye"></i> <?= (int)$post['views'] ?> lượt xem</span>
            </div>

            <?php if (!empty($post['image'])): ?>
                <div class="news-detail-image">
                    <img src="<?= url('assets/images/news/' . e($post['image'])) ?>" alt="<?= e($post['title']) ?>">
                </div>
            <?php endif; ?>

            <!-- Render nội dung an toàn -->
            <div class="news-detail-content">
                <?= $safeContent ?>
            </div>
            
            <div class="news-detail-footer">
                <div class="share-buttons">
                    <span>Chia sẻ bài viết:</span>
                    <button class="btn btn--outline btn--sm" onclick="navigator.clipboard.writeText(window.location.href); alert('Đã sao chép link liên kết!');">
                        <i class="fa-solid fa-link"></i> Sao chép link
                    </button>
                </div>
                <a href="<?= url('post') ?>" class="btn btn--outline btn--sm"><i class="fa-solid fa-arrow-left"></i> Quay lại tin tức</a>
            </div>
        </article>

        <!-- Cột phải: Sidebar -->
        <aside class="news-sidebar">
            
            <!-- Box 1: Bài viết liên quan -->
            <?php if (!empty($related)): ?>
            <div class="news-sidebar-widget">
                <h3 class="widget-title">Bài viết liên quan</h3>
                <div class="news-related-list">
                    <?php foreach ($related as $r): ?>
                        <?php require __DIR__ . '/partials/_related_card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Box 2: Mua theo nhu cầu -->
            <?php require_once __DIR__ . '/partials/_buying_needs.php'; ?>
            
        </aside>
    </div>
</section>
