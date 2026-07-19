<?php
$featured = $featured ?? null;
$posts = $posts ?? [];
$popular = $popular ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$currentTag = $currentTag ?? '';

$tags = [
    '' => 'Tất cả',
    'laptop' => 'Laptop',
    'gaming' => 'PC Gaming',
    'linh-kien' => 'Linh kiện',
    'danh-gia' => 'Đánh giá',
    'thu-thuat' => 'Thủ thuật'
];
?>

<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <span>Tin tức công nghệ</span>
</section>

<!-- ===== TRANG TIN TỨC CHÍNH ===== -->
<section class="container news-page">
    
    <!-- 1. Dải danh mục tin tức (Cuộn ngang trên mobile) -->
    <div class="news-tags">
        <?php foreach ($tags as $key => $name): ?>
            <?php 
            $isActive = ($currentTag === $key);
            $link = url('post' . (!empty($key) ? '?tag=' . $key : ''));
            ?>
            <a href="<?= $link ?>" class="news-tag-pill <?= $isActive ? 'is-active' : '' ?>"><?= e($name) ?></a>
        <?php endforeach; ?>
    </div>

    <!-- 2. Bài viết nổi bật (Featured Post) - Chỉ hiển thị ở trang 1 và không lọc tag -->
    <?php if ($featured !== null): ?>
        <div class="news-featured">
            <a href="<?= url('post/detail/' . $featured['slug']) ?>" class="news-featured__img">
                <img src="<?= url('assets/images/news/' . e($featured['image'])) ?>" alt="<?= e($featured['title']) ?>" onerror="this.outerHTML='<div class=\'news-img-placeholder\'><i class=\'fa-solid fa-newspaper\'></i></div>'">
            </a>
            <div class="news-featured__content">
                <span class="news-badge">Nổi bật</span>
                <h2><a href="<?= url('post/detail/' . $featured['slug']) ?>"><?= e($featured['title']) ?></a></h2>
                <p><?= e($featured['summary']) ?></p>
                <div class="news-meta">
                    <span><i class="fa-regular fa-clock"></i> <?= date('d/m/Y', strtotime($featured['created_at'])) ?></span>
                    <span><i class="fa-regular fa-eye"></i> <?= (int)$featured['views'] ?> lượt xem</span>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 3. Bố cục 2 cột (Main & Sidebar) -->
    <div class="news-layout">
        <!-- Cột trái: Danh sách bài viết tiếp theo -->
        <div class="news-main">
            <h3 class="news-section-title">Bài viết mới nhất</h3>
            
            <?php if (!empty($posts)): ?>
                <div class="news-list">
                    <?php foreach ($posts as $p): ?>
                        <div class="news-horizontal-card">
                            <a href="<?= url('post/detail/' . $p['slug']) ?>" class="news-horizontal-card__img">
                                <img src="<?= url('assets/images/news/' . e($p['image'])) ?>" alt="<?= e($p['title']) ?>" onerror="this.outerHTML='<div class=\'news-img-placeholder\'><i class=\'fa-solid fa-newspaper\'></i></div>'">
                            </a>
                            <div class="news-horizontal-card__body">
                                <span class="news-badge-category">Công nghệ</span>
                                <h4><a href="<?= url('post/detail/' . $p['slug']) ?>"><?= e($p['title']) ?></a></h4>
                                <p class="news-horizontal-card__summary"><?= e($p['summary']) ?></p>
                                <div class="news-meta">
                                    <span><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y', strtotime($p['created_at'])) ?></span>
                                    <span><i class="fa-regular fa-eye"></i> <?= (int)$p['views'] ?> lượt xem</span>
                                </div>
                            </div>
                        </div>
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
            <div class="news-sidebar-widget">
                <h3 class="widget-title">Bài viết phổ biến</h3>
                <div class="news-popular-list">
                    <?php 
                    $rank = 1;
                    foreach ($popular as $pop): 
                    ?>
                        <div class="news-popular-item">
                            <span class="popular-rank rank-<?= $rank ?>"><?= $rank++ ?></span>
                            <div class="popular-info">
                                <h5><a href="<?= url('post/detail/' . $pop['slug']) ?>"><?= e($pop['title']) ?></a></h5>
                                <span class="popular-views"><i class="fa-regular fa-eye"></i> <?= (int)$pop['views'] ?> lượt xem</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Box 2: Đăng ký nhận tin -->
            <div class="news-sidebar-widget news-subscribe-box">
                <div class="subscribe-icon">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
                <h4>Đăng ký nhận tin tức</h4>
                <p>Nhận các đánh giá công nghệ, thủ thuật build PC và khuyến mãi mới nhất từ TechPilot.</p>
                <form class="subscribe-form" onsubmit="event.preventDefault(); alert('Đăng ký nhận tin thành công!'); this.reset();">
                    <input type="email" placeholder="Nhập email của bạn..." required>
                    <button type="submit" class="btn btn--block">Đăng ký ngay</button>
                </form>
            </div>
            
        </aside>
    </div>

</section>
