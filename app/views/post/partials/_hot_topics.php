<?php
/**
 * Hot Topics (Xu hướng tìm kiếm)
 * Variables: $hotTopics (array), $hotTopicsVariant ('mobile' | 'sidebar')
 */
$hotTopicsVariant = $hotTopicsVariant ?? 'mobile';

// Nếu chưa nhận $hotTopics từ view context, thử nạp từ config/news.php làm fallback an toàn
if (!isset($hotTopics) || !is_array($hotTopics)) {
    $newsConfigFile = ROOT_PATH . '/config/news.php';
    $newsConfig     = file_exists($newsConfigFile) ? require $newsConfigFile : [];
    $hotTopics      = is_array($newsConfig['hot_topics'] ?? null) ? $newsConfig['hot_topics'] : [];
}

if (empty($hotTopics)) {
    return;
}
?>

<?php if ($hotTopicsVariant === 'sidebar'): ?>
    <div class="news-sidebar-widget news-hot-topics-sidebar">
        <h3 class="widget-title">
            <i class="fa-solid fa-arrow-trend-up" aria-hidden="true"></i> Xu hướng tìm kiếm
        </h3>
        <div class="news-hot-topics__list">
            <?php foreach ($hotTopics as $topic): ?>
                <?php
                if (!is_array($topic)) continue;
                $title = trim((string)($topic['title'] ?? ''));
                $query = trim((string)($topic['q'] ?? ($topic['query'] ?? '')));
                $icon  = trim((string)($topic['icon'] ?? 'fa-magnifying-glass'));
                if ($title === '' || $query === '') continue;
                ?>
                <a href="<?= url('post?q=' . urlencode($query)) ?>" class="news-hot-topic-link">
                    <?php if ($icon !== ''): ?>
                        <i class="fa-solid <?= e($icon) ?>" aria-hidden="true"></i>
                    <?php endif; ?>
                    <?= e($title) ?>
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
                <?php
                if (!is_array($topic)) continue;
                $title = trim((string)($topic['title'] ?? ''));
                $query = trim((string)($topic['q'] ?? ($topic['query'] ?? '')));
                $icon  = trim((string)($topic['icon'] ?? 'fa-magnifying-glass'));
                if ($title === '' || $query === '') continue;
                ?>
                <a href="<?= url('post?q=' . urlencode($query)) ?>" class="news-hot-topic-link">
                    <?php if ($icon !== ''): ?>
                        <i class="fa-solid <?= e($icon) ?>" aria-hidden="true"></i>
                    <?php endif; ?>
                    <?= e($title) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
