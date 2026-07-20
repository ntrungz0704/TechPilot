<div class="news-related-item">
    <a href="<?= url('post/detail/' . $r['slug']) ?>" class="news-related-img">
        <img src="<?= url('assets/images/news/' . e($r['image'])) ?>" alt="<?= e($r['title']) ?>">
    </a>
    <div class="news-related-content">
        <h5><a href="<?= url('post/detail/' . $r['slug']) ?>"><?= e($r['title']) ?></a></h5>
        <span><i class="fa-regular fa-clock"></i> <?= date('d/m/Y', strtotime($r['published_at'] ?? $r['created_at'])) ?></span>
    </div>
</div>
