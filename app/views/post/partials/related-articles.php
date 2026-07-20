<section class="related-section">
    <h2 class="section-title">Bài viết liên quan</h2>
    <div class="news-grid">
        <?php
        $isFeatured = false;
        foreach ($relatedArticles as $relatedArticle):
            // Reuse article-card partial, need to pass $article as the current item
            $article = $relatedArticle;
            require ROOT_PATH . '/app/views/post/partials/article-card.php';
        endforeach;
        ?>
    </div>
</section>
