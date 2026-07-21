<?php
/**
 * Hot Topics (Xu hướng tìm kiếm)
 */
$hotTopics = [
    ['title' => 'Review RTX 5090', 'q' => 'RTX 5090', 'icon' => 'fa-fire'],
    ['title' => 'CES 2026', 'q' => 'CES 2026', 'icon' => 'fa-bolt'],
    ['title' => 'Build PC AI', 'q' => 'PC AI', 'icon' => 'fa-microchip'],
    ['title' => 'Laptop sinh viên', 'q' => 'laptop sinh viên', 'icon' => 'fa-graduation-cap'],
];
?>
<div class="news-hot-topics">
    <span class="news-hot-topics__label">
        <i class="fa-solid fa-arrow-trend-up"></i> Xu hướng:
    </span>
    <div class="news-hot-topics__list">
        <?php foreach ($hotTopics as $topic): ?>
            <a href="<?= url('post?q=' . urlencode($topic['q'])) ?>" class="news-hot-topic-link">
                <?php if (!empty($topic['icon'])): ?>
                    <i class="fa-solid <?= $topic['icon'] ?>"></i>
                <?php endif; ?>
                <?= e($topic['title']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>
