<article class="article-card <?= $isFeatured ? 'article-card--featured' : '' ?>">
    <a href="<?= url('tin-tuc/' . $article['slug']) ?>" class="article-card__img-wrap">
        <span class="article-card__category"><?= e($article['category']['name']) ?></span>
        <img src="<?= e($article['featured_image']) ?>" alt="<?= e($article['title']) ?>" class="article-card__img" loading="<?= $isFeatured ? 'eager' : 'lazy' ?>">
    </a>
    <div class="article-card__content">
        <h3 class="article-card__title">
            <a href="<?= url('tin-tuc/' . $article['slug']) ?>"><?= e($article['title']) ?></a>
        </h3>
        <p class="article-card__excerpt"><?= e($article['excerpt']) ?></p>
        <div class="article-card__meta">
            <div><i class="fa-regular fa-clock"></i> <?= e($article['published_at']) ?></div>
            <div><i class="fa-solid fa-book-open-reader"></i> <?= e($article['reading_time']) ?> phút</div>
        </div>
    </div>
</article>
