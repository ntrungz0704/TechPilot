<div class="news-page news-article-page">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?= url('/') ?>">Trang chủ</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <a href="<?= url('tin-tuc') ?>">Tin tức công nghệ</a>
            <span class="breadcrumb-separator"><i class="fa-solid fa-chevron-right"></i></span>
            <span><?= e($article['title']) ?></span>
        </div>

        <div class="article-layout">
            <main class="article-main">
                <header class="article-header">
                    <span class="article-header__category"><?= e($article['category']['name']) ?></span>
                    <h1><?= e($article['title']) ?></h1>
                    <p class="article-header__excerpt"><?= e($article['excerpt']) ?></p>
                    <div class="article-meta">
                        <div><i class="fa-solid fa-pen-nib"></i> <?= e($article['author']['name']) ?></div>
                        <div><i class="fa-regular fa-clock"></i> Đăng: <?= e($article['published_at']) ?></div>
                        <div><i class="fa-solid fa-book-open-reader"></i> Đọc: <?= e($article['reading_time']) ?> phút</div>
                    </div>
                </header>

                <img src="<?= e($article['featured_image']) ?>" alt="<?= e($article['title']) ?>" class="article-hero-image">

                <?php if (!empty($article['key_takeaways'])): ?>
                    <?php require ROOT_PATH . '/app/views/news/partials/key-takeaways.php'; ?>
                <?php endif; ?>

                <div class="article-content">
                    <?php foreach ($article['sections'] as $section): ?>
                        <h2 id="<?= e($section['id']) ?>"><?= e($section['heading']) ?></h2>
                        <?= $section['content'] ?> <!-- In HTML directly for mock data -->
                    <?php endforeach; ?>

                    <?php if (!empty($article['comparison'])): ?>
                        <?php require ROOT_PATH . '/app/views/news/partials/comparison-table.php'; ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($article['faq'])): ?>
                    <?php require ROOT_PATH . '/app/views/news/partials/faq.php'; ?>
                <?php endif; ?>

                <footer class="article-footer">
                    <div class="article-tags">
                        <?php foreach($article['tags'] as $tag): ?>
                            <span class="tag">#<?= e($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="article-share">
                        <span style="font-size: 14px; font-weight: 500;">Chia sẻ:</span>
                        <button class="share-btn copy-link-btn" title="Sao chép liên kết"><i class="fa-solid fa-link"></i></button>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/tin-tuc/' . $article['slug']) ?>" target="_blank" class="share-btn" title="Chia sẻ Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    </div>
                </footer>
            </main>

            <aside class="article-sidebar">
                <?php require ROOT_PATH . '/app/views/news/partials/table-of-contents.php'; ?>
                
                <div style="background: var(--news-primary); border-radius: 12px; padding: 24px; color: white; text-align: center; margin-bottom: 24px;">
                    <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 1.25rem;">Build PC Cực Đỉnh</h3>
                    <p style="font-size: 14px; margin-bottom: 20px; color: rgba(255,255,255,0.9);">Cấu hình mạnh mẽ, giá siêu hời chỉ có tại TechPilot.</p>
                    <a href="<?= url('build-pc') ?>" style="display: inline-block; background: white; color: var(--news-primary); padding: 10px 24px; border-radius: 8px; font-weight: 600; text-decoration: none;">Xem ngay</a>
                </div>
            </aside>
        </div>

        <?php if (!empty($recommendedProducts)): ?>
            <?php require ROOT_PATH . '/app/views/news/partials/product-recommendations.php'; ?>
        <?php endif; ?>

        <?php if (!empty($relatedArticles)): ?>
            <?php require ROOT_PATH . '/app/views/news/partials/related-articles.php'; ?>
        <?php endif; ?>
    </div>
</div>
