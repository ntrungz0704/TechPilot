<?php
/**
 * Trang danh sách bài viết - /post
 * Variables: $featured, $posts, $popular, $heroPopular, $currentPage, $totalPages, $currentType, $currentCategory, $currentTag
 */
$featured        = $featured        ?? null;
$posts           = $posts           ?? [];
$popular         = $popular         ?? [];
$heroPopular     = $heroPopular     ?? [];
$currentPage     = $currentPage     ?? 1;
$totalPages      = $totalPages      ?? 1;
$currentType     = $currentType     ?? '';
$currentCategory = $currentCategory ?? '';
$currentTag      = $currentTag      ?? '';

$pageQueryParams = [];
if (!empty($currentType))     $pageQueryParams['type']     = $currentType;
if (!empty($currentCategory)) $pageQueryParams['category'] = $currentCategory;
if (!empty($currentTag))      $pageQueryParams['tag']      = $currentTag;

$hasActiveFilter = !empty($currentType) || !empty($currentCategory) || !empty($currentTag);
?>

<section class="container breadcrumb" aria-label="Đường dẫn">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    <span aria-current="page">Tin tức công nghệ</span>
</section>

<!-- ===== TRANG TIN TỨC CHÍNH ===== -->
<section class="container news-page">

    <!-- 1. Dải danh mục tin tức -->
    <?php require_once __DIR__ . '/partials/_category_nav.php'; ?>

    <!-- 2. Bài viết nổi bật (Featured Post) - Chỉ hiển thị ở trang 1 không filter -->
    <?php if ($featured !== null && !$hasActiveFilter): ?>
        <?php require_once __DIR__ . '/partials/_featured.php'; ?>
    <?php endif; ?>

<?php
// Tính toán Tiêu đề danh sách bài viết động theo bộ lọc
$sectionTitle = 'Bài viết mới nhất';
if (!empty($currentType) && !empty($currentCategory)) {
    $sectionTitle = postTypeLabel($currentType) . ' — ' . postCategoryLabel($currentCategory);
} elseif (!empty($currentType)) {
    $sectionTitle = postTypeLabel($currentType);
} elseif (!empty($currentCategory)) {
    $sectionTitle = 'Bài viết ' . postCategoryLabel($currentCategory);
} elseif (!empty($currentTag)) {
    $sectionTitle = 'Bài viết tag: ' . e($currentTag);
}
?>

    <!-- 3. Bố cục 2 cột (Main & Sidebar) -->
    <div class="news-layout">
        <!-- Cột trái: Danh sách bài viết -->
        <div class="news-main">
            <h3 class="news-section-title"><?= e($sectionTitle) ?></h3>

            <?php if (!empty($posts)): ?>
                <div class="news-list">
                    <?php foreach ($posts as $p): ?>
                        <?php require __DIR__ . '/partials/_horizontal_card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Phân trang (Pagination) -->
                <?php if ($totalPages > 1): ?>
                    <nav class="news-pagination" aria-label="Phân trang">
                        <?php if ($currentPage > 1): ?>
                            <?php $prevParams = array_merge($pageQueryParams, ['page' => $currentPage - 1]); ?>
                            <a href="<?= url('post?' . http_build_query($prevParams)) ?>" class="page-btn" aria-label="Trang trước">
                                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php $iParams = array_merge($pageQueryParams, ['page' => $i]); ?>
                            <a
                                href="<?= url('post?' . http_build_query($iParams)) ?>"
                                class="page-btn <?= $currentPage === $i ? 'is-active' : '' ?>"
                                <?= $currentPage === $i ? 'aria-current="page"' : '' ?>
                            ><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <?php $nextParams = array_merge($pageQueryParams, ['page' => $currentPage + 1]); ?>
                            <a href="<?= url('post?' . http_build_query($nextParams)) ?>" class="page-btn" aria-label="Trang sau">
                                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="news-empty">
                    <i class="fa-solid fa-filter-circle-xmark" aria-hidden="true"></i>
                    <h4>Chưa có bài viết nào phù hợp</h4>
                    <p>
                        <?php if (!empty($currentType) || !empty($currentCategory)): ?>
                            Không tìm thấy bài viết thuộc
                            <strong><?= !empty($currentType) ? postTypeLabel($currentType) : '' ?></strong>
                            <?= (!empty($currentType) && !empty($currentCategory)) ? ' dành cho ' : '' ?>
                            <strong><?= !empty($currentCategory) ? postCategoryLabel($currentCategory) : '' ?></strong>.
                        <?php else: ?>
                            Hãy chọn bộ lọc hoặc chuyên mục khác.
                        <?php endif; ?>
                    </p>
                    <a href="<?= url('post') ?>" class="btn btn--primary btn--sm" style="margin-top: 16px; display: inline-block;">
                        <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i> Xem tất cả bài viết
                    </a>
                </div>
            <?php endif; ?>

        </div>

        <!-- Cột phải: Sidebar -->
        <aside class="news-sidebar" aria-label="Nội dung liên quan">
            <!-- Box 1: Bài viết phổ biến -->
            <?php if (!empty($popular)): ?>
            <div class="news-sidebar-widget">
                <h3 class="widget-title">Bài viết phổ biến</h3>
                <div class="news-popular-list">
                    <?php
                    $rank = 1;
                    foreach ($popular as $pop):
                    ?>
                        <?php require __DIR__ . '/partials/_popular_item.php'; ?>
                    <?php
                        $rank++;
                    endforeach;
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Box 2: Mua theo nhu cầu -->
            <?php require_once __DIR__ . '/partials/_buying_needs.php'; ?>

        </aside>
    </div>

</section>
