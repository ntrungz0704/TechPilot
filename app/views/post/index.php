<?php
$featured = $featured ?? null;
$posts = $posts ?? [];
$popular = $popular ?? [];
$heroPopular = $heroPopular ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$currentTag = $currentTag ?? '';
?>

<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <span>Tin tức công nghệ</span>
</section>

<!-- ===== TRANG TIN TỨC CHÍNH ===== -->
<section class="container news-page">
    
    <!-- 1. Dải danh mục tin tức -->
    <?php require_once __DIR__ . '/partials/_category_nav.php'; ?>

    <!-- 2. Bài viết nổi bật (Featured Post) - Chỉ hiển thị ở trang 1 -->
    <?php if ($featured !== null): ?>
        <?php require_once __DIR__ . '/partials/_featured.php'; ?>
    <?php endif; ?>

    <!-- 3. Bố cục 2 cột (Main & Sidebar) -->
    <div class="news-layout">
        <!-- Cột trái: Danh sách bài viết tiếp theo -->
        <div class="news-main">
            <h3 class="news-section-title">Bài viết mới nhất</h3>
            
            <?php if (!empty($posts)): ?>
                <div class="news-list">
                    <?php foreach ($posts as $p): ?>
                        <?php require __DIR__ . '/partials/_horizontal_card.php'; ?>
                    <?php endforeach; ?>
                </div>

                <!-- Phân trang (Pagination) -->
                <?php if ($totalPages > 1): ?>
                    <div class="news-pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="<?= url('post?page=' . ($currentPage - 1) . (!empty($currentTag) ? '&tag=' . $currentTag : '')) ?>" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="<?= url('post?page=' . $i . (!empty($currentTag) ? '&tag=' . $currentTag : '')) ?>" class="page-btn <?= $currentPage === $i ? 'is-active' : '' ?>"><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="<?= url('post?page=' . ($currentPage + 1) . (!empty($currentTag) ? '&tag=' . $currentTag : '')) ?>" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="news-empty">
                    <i class="fa-solid fa-inbox"></i>
                    <h4>Chưa có bài viết nào phù hợp</h4>
                    <p>Hãy chọn bộ lọc hoặc chuyên mục khác.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cột phải: Sidebar -->
        <aside class="news-sidebar">
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
