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
            <span class="news-badge-category news-detail-category-badge">
                <?= postTypeLabel($post['post_type'] ?? '') ?>
            </span>

            <!-- Tiêu đề H1 -->
            <h1 class="news-detail-title"><?= e($post['title']) ?></h1>

            <!-- Summary -->
            <?php if (!empty($post['summary'])): ?>
                <p class="news-detail-summary"><?= e($post['summary']) ?></p>
            <?php endif; ?>

            <?php
            $rawPubTime = !empty($post['published_at']) ? strtotime($post['published_at']) : (!empty($post['created_at']) ? strtotime($post['created_at']) : false);
            $hasValidPubDate = ($rawPubTime !== false && $rawPubTime > 0);
            ?>

            <!-- Meta: Tác giả, Ngày, Thời gian đọc, Lượt xem -->
            <div class="news-meta news-detail-meta">
                <span><i class="fa-solid fa-user" aria-hidden="true"></i> <strong><?= e($post['author_name'] ?? 'Đội ngũ TechPilot') ?></strong></span>
                <?php if ($hasValidPubDate): ?>
                    <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?= date('d/m/Y', $rawPubTime) ?></span>
                <?php endif; ?>
                <?php if (!empty($hasValidUpdatedAt)): ?>
                    <span><i class="fa-solid fa-rotate" aria-hidden="true"></i> Cập nhật: <?= date('d/m/Y', strtotime($post['updated_at'])) ?></span>
                <?php endif; ?>
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
                        fetchpriority="high"
                        decoding="async"
                    >
                </div>
            <?php endif; ?>

            <!-- Nội dung bài viết -->
            <?php if (!empty($hasArticleContent)): ?>
                <?php require __DIR__ . '/partials/_article_content.php'; ?>
            <?php else: ?>
                <section class="article-content-empty" role="status">
                    <div class="article-content-empty__icon" aria-hidden="true">
                        <i class="fa-regular fa-file-lines"></i>
                    </div>

                    <h2>Nội dung đang được cập nhật</h2>

                    <p>
                        Bài viết này hiện chưa có nội dung chi tiết.
                        Bạn có thể quay lại sau hoặc xem các bài viết liên quan.
                    </p>
                </section>
            <?php endif; ?>

            <!-- Footer: Chia sẻ + Quay lại -->
            <div class="news-detail-footer">
                <div class="share-buttons">
                    <button type="button" class="btn btn--outline btn--sm share-unified-btn" aria-label="Chia sẻ bài viết này">
                        <i class="fa-solid fa-share-nodes" aria-hidden="true"></i> <span>Chia sẻ bài viết</span>
                    </button>
                </div>
                <a href="<?= url('post') ?>" class="btn btn--outline btn--sm">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Quay lại tin tức
                </a>
            </div>
        </article>

        <!-- Cột phải: Sidebar (TOC + Bài viết liên quan) -->
        <?php if ((!empty($hasArticleContent) && $articleH2Count >= 1 && !empty($articleHeadings)) || !empty($related)): ?>
        <aside class="news-sidebar" aria-label="Mục lục bài viết">
            <?php if (!empty($hasArticleContent) && $articleH2Count >= 1 && !empty($articleHeadings)): ?>
            <div class="news-sidebar-widget news-sidebar-toc-widget">
                <?php
                $tocVariant = 'desktop';
                $tocIdPrefix = 'desktop-toc';
                require __DIR__ . '/partials/_article_toc.php';
                ?>
            </div>
            <?php endif; ?>
            <?php if (!empty($related)): ?>
            <div class="news-sidebar-widget" style="position: static; margin-top: 20px;">
                <h3 style="font-size:16px;font-weight:700;margin-bottom:14px;color:var(--text-primary);">
                    <i class="fa-solid fa-newspaper" style="margin-right:6px;color:var(--primary);"></i>Bài viết liên quan
                </h3>
                <div style="display:flex;flex-direction:column;gap:12px;">
                    <?php foreach (array_slice($related, 0, 5) as $rel): ?>
                    <a href="<?= url('post/detail/' . e($rel['slug'])) ?>" style="display:flex;gap:10px;text-decoration:none;padding:8px;border-radius:8px;transition:all 0.2s;border:1px solid transparent;" onmouseover="this.style.background='#F8FAFC';this.style.borderColor='#E2E8F0'" onmouseout="this.style.background='';this.style.borderColor='transparent'">
                        <?php if (!empty($rel['image'])): ?>
                        <img src="<?= e($rel['image']) ?>" alt="" style="width:60px;height:45px;object-fit:cover;border-radius:6px;flex-shrink:0;" loading="lazy">
                        <?php endif; ?>
                        <span style="font-size:13px;font-weight:600;color:var(--text-primary);line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= e($rel['title']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </aside>
        <?php endif; ?>
    </div>
</section>

<!-- ===== BÀI VIẾT LIÊN QUAN (HÀNG LƯỚT SLIDER NGANG) ===== -->
<?php if (!empty($related)): ?>
<section class="news-related-bottom-section" aria-label="Bài viết liên quan">
    <div class="container">
        <div class="news-section-header news-related-header">
            <div>
                <h3 class="news-section-title">
                    <i class="fa-solid fa-layer-group" aria-hidden="true"></i> Bài viết liên quan
                </h3>
                <span class="news-section-subtitle">Gợi ý các tin tức & đánh giá công nghệ tương tự (kéo hoặc lướt qua phải để xem thêm)</span>
            </div>
            <div class="news-carousel-nav" aria-label="Điều hướng bài viết">
                <button type="button" class="news-carousel-btn news-carousel-prev" aria-label="Xem bài viết trước">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button type="button" class="news-carousel-btn news-carousel-next" aria-label="Xem bài viết tiếp theo">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="news-related-carousel-track" role="region" aria-label="Danh sách bài viết gợi ý" tabindex="0">
            <?php foreach ($related as $r): ?>
                <div class="news-related-grid-card">
                    <a href="<?= url('post/detail/' . e($r['slug'])) ?>" class="news-related-grid-img" aria-label="<?= e($r['title']) ?>">
                        <img
                            src="<?= postImageUrl($r['image'] ?? '') ?>"
                            alt="<?= e($r['title']) ?>"
                            loading="lazy"
                            onerror="this.src='<?= url('assets/images/products/placeholder-component.webp') ?>'"
                        >
                        <span class="news-badge-category news-related-grid-badge"><?= postTypeLabel($r['post_type'] ?? '') ?></span>
                    </a>
                    <div class="news-related-grid-body">
                        <h4 class="news-related-grid-title">
                            <a href="<?= url('post/detail/' . e($r['slug'])) ?>"><?= e($r['title']) ?></a>
                        </h4>
                        <div class="news-meta news-related-grid-meta">
                            <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> <?= date('d/m/Y', strtotime($r['published_at'] ?? $r['created_at'])) ?></span>
                            <?php if (!empty($r['reading_minutes'])): ?>
                                <span><i class="fa-regular fa-hourglass-half" aria-hidden="true"></i> <?= (int)$r['reading_minutes'] ?> phút đọc</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Toast notification (append bởi news.js) -->
<div
    id="newsToast"
    class="toast-message"
    role="status"
    aria-live="polite"
    aria-atomic="true"
></div>
