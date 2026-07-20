<div class="news-page">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= url('/') ?>">Trang chủ</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span>Tin tức công nghệ</span>
        </div>

        <div class="news-header">
            <h1>Tin tức công nghệ</h1>
        </div>

        <div class="category-nav">
            <a href="<?= url('tin-tuc') ?>" class="category-badge active">Tất cả</a>
            <?php foreach($categories as $cat): ?>
                <a href="#" class="category-badge"><?= e($cat['name']) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($heroArticles)): ?>
            <div class="hero-grid">
                <?php foreach($heroArticles as $index => $article): ?>
                    <a href="<?= url('tin-tuc/' . $article['slug']) ?>" class="hero-item <?= $index === 0 ? 'hero-item--large' : '' ?>">
                        <img src="<?= e($article['featured_image']) ?>" alt="<?= e($article['title']) ?>">
                        <div class="hero-item__content">
                            <span class="hero-item__category"><?= e($article['category']['name']) ?></span>
                            <h3 class="hero-item__title"><?= e($article['title']) ?></h3>
                            <div class="hero-item__meta">
                                <span><i class="fa-regular fa-clock"></i> <?= e($article['published_at']) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="list-grid">
            <?php foreach($listArticles as $article): ?>
                <div class="list-card">
                    <a href="<?= url('tin-tuc/' . $article['slug']) ?>" class="list-card__img-wrap">
                        <img src="<?= e($article['featured_image']) ?>" alt="<?= e($article['title']) ?>">
                    </a>
                    <div class="list-card__content">
                        <h3 class="list-card__title">
                            <a href="<?= url('tin-tuc/' . $article['slug']) ?>"><?= e($article['title']) ?></a>
                        </h3>
                        <p class="list-card__excerpt"><?= e($article['excerpt']) ?></p>
                        <div class="list-card__meta">
                            <span><i class="fa-solid fa-folder-open"></i> <?= e($article['category']['name']) ?></span>
                            <span><i class="fa-regular fa-clock"></i> <?= e($article['published_at']) ?></span>
                            <span><i class="fa-solid fa-glasses"></i> <?= e($article['reading_time']) ?> phút đọc</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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
