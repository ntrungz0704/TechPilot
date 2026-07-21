<?php
/**
 * Trang chi tiết bài viết - /post/detail/{slug}
 * Contract: $post, $related, $renderedContent, $articleHeadings, $articleBlocks, $articleWordCount, $articleH2Count, $postType, $categorySlug, $midCtaConfig, $endCtaConfig, $commerceContext
 */
$post             = $post             ?? null;
$related          = $related          ?? [];
$renderedContent  = $renderedContent  ?? '';
$articleHeadings  = is_array($articleHeadings ?? null) ? $articleHeadings : [];
$articleBlocks    = is_array($articleBlocks ?? null) ? $articleBlocks : [];
$articleWordCount = max(0, (int)($articleWordCount ?? 0));
$articleH2Count   = max(0, (int)($articleH2Count ?? 0));
$postType         = strtolower(trim((string)($postType ?? '')));
$categorySlug     = strtolower(trim((string)($categorySlug ?? '')));

if (!$post) return;
?>

<section class="container breadcrumb" aria-label="Đường dẫn">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    <a href="<?= url('post') ?>">Tin tức công nghệ</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    <span aria-current="page"><?= e($post['title']) ?></span>
</section>

<!-- Reading Progress Bar -->
<div class="news-reading-progress" aria-hidden="true">
    <div class="news-reading-progress-bar" id="readingProgressBar"></div>
</div>

<!-- ===== CHI TIẾT BÀI VIẾT ===== -->
<section class="container news-page">
    <div class="news-layout">

        <!-- Cột trái: Nội dung chi tiết bài viết -->
        <article class="news-main news-detail-card">
            <!-- Badge loại bài -->
            <span class="news-badge-category" style="margin-bottom: 12px; display: inline-block;">
                <?= postTypeLabel($post['post_type'] ?? '') ?>
            </span>

            <!-- Tiêu đề H1 -->
            <h1 class="news-detail-title"><?= e($post['title']) ?></h1>

            <!-- Summary -->
            <?php if (!empty($post['summary'])): ?>
                <p class="news-detail-summary"><?= e($post['summary']) ?></p>
            <?php endif; ?>

            <!-- Meta: Tác giả, Ngày, Thời gian đọc, Lượt xem -->
            <div class="news-meta news-detail-meta">
                <span><i class="fa-solid fa-user" aria-hidden="true"></i> <strong><?= e($post['author_name'] ?? 'Đội ngũ TechPilot') ?></strong></span>
                <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?= date('d/m/Y', strtotime($post['published_at'] ?? $post['created_at'])) ?></span>
                <?php if (!empty($post['reading_minutes'])): ?>
                    <span><i class="fa-regular fa-hourglass-half" aria-hidden="true"></i> <?= (int)$post['reading_minutes'] ?> phút đọc</span>
                <?php endif; ?>
                <span><i class="fa-regular fa-eye" aria-hidden="true"></i> <?= (int)$post['views'] ?> lượt xem</span>
            </div>

            <!-- Ảnh cover -->
            <?php if (!empty($post['image'])): ?>
                <div class="news-detail-image">
                    <img
                        src="<?= postImageUrl($post['image']) ?>"
                        alt="<?= e($post['title']) ?>"
                        loading="eager"
                        onerror="this.parentElement.style.display='none'"
                    >
                </div>
            <?php endif; ?>

            <!-- Nội dung bài viết -->
            <?php require __DIR__ . '/partials/_article_content.php'; ?>

            <!-- Footer: Chia sẻ + Quay lại -->
            <div class="news-detail-footer">
                <div class="share-buttons">
                    <span>Chia sẻ:</span>
                    <button type="button" class="btn btn--outline btn--sm share-native-btn" aria-label="Chia sẻ bài viết này">
                        <i class="fa-solid fa-share-nodes" aria-hidden="true"></i> Chia sẻ
                    </button>
                    <button type="button" class="btn btn--outline btn--sm copy-link-btn" aria-label="Sao chép liên kết bài viết">
                        <i class="fa-solid fa-link" aria-hidden="true"></i> Sao chép link
                    </button>
                </div>
                <a href="<?= url('post') ?>" class="btn btn--outline btn--sm">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Quay lại tin tức
                </a>
            </div>
        </article>

        <!-- Cột phải: Sidebar -->
        <aside class="news-sidebar" aria-label="Nội dung liên quan">

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
            <?php require __DIR__ . '/partials/_buying_needs.php'; ?>

        </aside>
    </div>
</section>

<!-- Toast notification (append bởi news.js) -->
<div
    id="newsToast"
    class="toast-message"
    role="status"
    aria-live="polite"
    aria-atomic="true"
></div>
