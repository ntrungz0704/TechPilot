<div class="news-popular-item">
    <span class="popular-rank rank-<?= $rank ?>"><?= $rank ?></span>
    <div class="popular-info">
        <h5><a href="<?= url('post/detail/' . $pop['slug']) ?>"><?= e($pop['title']) ?></a></h5>
        <span class="popular-views"><i class="fa-regular fa-eye"></i> <?= (int)$pop['views'] ?> lượt xem</span>
    </div>
</div>
