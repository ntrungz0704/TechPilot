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
$currentQ        = $currentQ        ?? '';

$pageQueryParams = [];
if (!empty($currentType))     $pageQueryParams['type']     = $currentType;
if (!empty($currentCategory)) $pageQueryParams['category'] = $currentCategory;
if (!empty($currentTag))      $pageQueryParams['tag']      = $currentTag;
if (!empty($currentQ))        $pageQueryParams['q']        = $currentQ;

$hasActiveFilter = !empty($currentType) || !empty($currentCategory) || !empty($currentTag) || !empty($currentQ);
?>

<section class="container breadcrumb" aria-label="Đường dẫn">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
    <span aria-current="page">Tin tức công nghệ</span>
</section>

<!-- ===== TRANG TIN TỨC CHÍNH ===== -->
<section class="container news-page">

    <!-- 1. Dải danh mục tin tức -->
    <?php require_once __DIR__ . '/partials/_category_nav.php'; ?>
    <?php require __DIR__ . '/partials/_hot_topics.php'; ?>

    <!-- 2. Bài viết nổi bật (Featured Post) - Chỉ hiển thị ở trang 1 không filter -->
    <?php if ($featured !== null && !$hasActiveFilter): ?>
        <?php require_once __DIR__ . '/partials/_featured.php'; ?>
    <?php endif; ?>

<?php
// Tính toán Tiêu đề danh sách bài viết động theo bộ lọc
$sectionTitle = 'Bài viết mới nhất';
if (!empty($currentQ)) {
    $sectionTitle = 'Kết quả tìm kiếm cho “' . $currentQ . '”';
} elseif (!empty($currentType) && !empty($currentCategory)) {
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
            <div class="news-main-header" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; margin-bottom: 1rem; gap: 1rem;">
                <h3 class="news-section-title" style="margin-bottom: 0;"><?= e($sectionTitle) ?></h3>
                
                <form action="<?= url('post') ?>" method="get" class="news-search-form" style="display: flex; max-width: 300px; width: 100%; position: relative;">
                    <?php if (!empty($currentType)): ?>
                        <input type="hidden" name="type" value="<?= e($currentType) ?>">
                    <?php endif; ?>
                    <?php if (!empty($currentCategory)): ?>
                        <input type="hidden" name="category" value="<?= e($currentCategory) ?>">
                    <?php endif; ?>
                    <input type="text" name="q" value="<?= e($currentQ) ?>" placeholder="Tìm bài viết, hướng dẫn..." aria-label="Tìm kiếm bài viết" style="width: 100%; padding: 0.5rem 2.5rem 0.5rem 1rem; border: 1px solid var(--border-color); border-radius: 4px; background: var(--bg-card); color: var(--text-color);" required maxlength="150">
                    <button type="submit" style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-magnifying-glass"></i></button>
                    <?php if (!empty($currentQ)): ?>
                        <?php 
                            $clearParams = $pageQueryParams;
                            unset($clearParams['q']);
                        ?>
                        <a href="<?= url('post?' . http_build_query($clearParams)) ?>" style="position: absolute; right: 2rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); text-decoration: none;" aria-label="Xóa tìm kiếm"><i class="fa-solid fa-xmark"></i></a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (!empty($posts)): ?>
                <div class="news-list">
                    <?php foreach ($posts as $p): ?>
                        <?php require __DIR__ . '/partials/_horizontal_card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Phân trang (Pagination) -->
                <?php require __DIR__ . '/partials/_pagination.php'; ?>

            <?php else: ?>
                <div class="news-empty">
                    <i class="fa-solid fa-filter-circle-xmark" aria-hidden="true"></i>
                    <h4>Chưa có bài viết nào phù hợp</h4>
                    <p>
                        <?php if (!empty($currentQ)): ?>
                            Không tìm thấy bài viết phù hợp với “<?= e($currentQ) ?>”.
                        <?php elseif (!empty($currentType) || !empty($currentCategory)): ?>
                            Không tìm thấy bài viết thuộc
                            <strong><?= !empty($currentType) ? postTypeLabel($currentType) : '' ?></strong>
                            <?= (!empty($currentType) && !empty($currentCategory)) ? ' dành cho ' : '' ?>
                            <strong><?= !empty($currentCategory) ? postCategoryLabel($currentCategory) : '' ?></strong>.
                        <?php else: ?>
                            Hãy chọn bộ lọc hoặc chuyên mục khác.
                        <?php endif; ?>
                    </p>
                    <?php 
                        $clearParams = $pageQueryParams;
                        unset($clearParams['q']);
                    ?>
                    <a href="<?= url('post?' . http_build_query($clearParams)) ?>" class="btn btn--primary btn--sm" style="margin-top: 16px; display: inline-block;">
                        <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i> Xem lại danh sách
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

            <!-- Widget 2: Mua theo nhu cầu -->
            <?php require __DIR__ . '/partials/_buying_needs.php'; ?>

        </aside>
    </div>

</section>
