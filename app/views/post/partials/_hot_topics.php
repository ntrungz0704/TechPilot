<?php
/**
 * Hot Topics (Xu hướng tìm kiếm)
 * Variables: $hotTopicsVariant ('mobile' | 'sidebar')
 */
$hotTopicsVariant = $hotTopicsVariant ?? 'mobile';

$hotTopics = [
    ['title' => 'Review RTX 5090', 'q' => 'RTX 5090', 'icon' => 'fa-fire'],
    ['title' => 'CES 2026',        'q' => 'CES 2026', 'icon' => 'fa-bolt'],
    ['title' => 'Build PC AI',     'q' => 'PC AI',    'icon' => 'fa-microchip'],
    ['title' => 'Laptop sinh viên','q' => 'laptop sinh viên', 'icon' => 'fa-graduation-cap'],
];
?>

<?php if ($hotTopicsVariant === 'sidebar'): ?>
    <div class="news-sidebar-widget news-hot-topics-sidebar">
        <h3 class="widget-title">
            <i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i> Xu hướng tìm kiếm
        </h3>
        <div class="news-hot-topics__list">
            <?php foreach ($hotTopics as $topic): ?>
                <a href="<?= url('post?q=' . urlencode($topic['q'])) ?>" class="news-hot-topic-link">
                    <?php if (!empty($topic['icon'])): ?>
                        <i class="fa-solid <?= e($topic['icon']) ?>" aria-hidden="true"></i>
                    <?php endif; ?>
                    <?= e($topic['title']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="news-hot-topics-mobile">
        <span class="news-hot-topics__label">
            <i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i> Xu hướng:
        </span>
        <div class="news-hot-topics__list">
            <?php foreach ($hotTopics as $topic): ?>
                <a href="<?= url('post?q=' . urlencode($topic['q'])) ?>" class="news-hot-topic-link">
                    <?php if (!empty($topic['icon'])): ?>
                        <i class="fa-solid <?= e($topic['icon']) ?>" aria-hidden="true"></i>
                    <?php endif; ?>
                    <?= e($topic['title']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
