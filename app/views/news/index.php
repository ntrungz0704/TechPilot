<div class="news-page">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= url('/') ?>">Trang chủ</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span>Tin tức công nghệ</span>
        </div>

        <div class="news-header">
            <h1>Tin tức công nghệ</h1>
            <p style="color: var(--news-text-light); font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                Cập nhật thông tin công nghệ mới nhất, đánh giá sản phẩm chuyên sâu và kinh nghiệm build PC từ các chuyên gia TechPilot.
            </p>
        </div>

        <div class="category-nav">
            <a href="<?= url('tin-tuc') ?>" class="category-badge active">Tất cả</a>
            <?php foreach($categories as $cat): ?>
                <a href="#" class="category-badge"><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($featuredArticle): ?>
            <div class="featured-grid">
                <?php 
                $article = $featuredArticle;
                $isFeatured = true;
                require ROOT_PATH . '/app/views/news/partials/article-card.php'; 
                ?>
                <!-- Có thể thêm quảng cáo hoặc banner phụ ở bên cạnh featured -->
            </div>
        <?php endif; ?>

        <div class="news-grid">
            <?php 
            $isFeatured = false;
            foreach($articles as $article): 
                require ROOT_PATH . '/app/views/news/partials/article-card.php';
            endforeach; 
            ?>
        </div>

        <div style="text-align: center; margin-bottom: 60px;">
            <button class="btn btn-outline" style="border: 1px solid var(--news-primary); color: var(--news-primary); background: transparent; padding: 12px 32px; border-radius: 8px; cursor: pointer; font-weight: 600;">Xem thêm bài viết</button>
        </div>
        
        <div style="background: var(--news-dark); border-radius: 12px; padding: 40px; text-align: center; color: white; margin-bottom: 60px;">
            <h2 style="margin-top: 0; margin-bottom: 16px;">Bạn cần tư vấn sản phẩm?</h2>
            <p style="margin-bottom: 24px; color: rgba(255,255,255,0.8);">Đội ngũ chuyên gia TechPilot luôn sẵn sàng hỗ trợ bạn tìm kiếm cấu hình phù hợp nhất.</p>
            <a href="<?= url('build-pc') ?>" style="display: inline-block; background: var(--news-primary); color: white; padding: 12px 32px; border-radius: 8px; font-weight: 600; text-decoration: none;">Build PC ngay</a>
        </div>
    </div>
</div>
