<?php
/**
 * Category Navigation - AAA Standard Streamlined Filter Bar
 * Variables: $currentType (string), $currentCategory (string), $currentTag (string), $currentQ (string)
 */
$currentType     = $currentType     ?? '';
$currentCategory = $currentCategory ?? '';
$currentTag      = $currentTag      ?? '';
$currentQ        = $currentQ        ?? '';

// Normalize state
$navType     = $currentType;
$navCategory = $currentCategory;

if (empty($navType) && empty($navCategory) && !empty($currentTag)) {
    $tagMap = [
        'danh-gia'     => ['type' => 'review'],
        'thu-thuat'    => ['type' => 'guide'],
        'tin-moi'      => ['type' => 'news'],
        'so-sanh'      => ['type' => 'comparison'],
        'laptop'       => ['category' => 'laptop'],
        'gaming'       => ['category' => 'pc-gaming'],
        'pc-linh-kien' => ['category' => 'pc-linh-kien'],
    ];
    if (isset($tagMap[$currentTag])) {
        $navType     = $tagMap[$currentTag]['type'] ?? '';
        $navCategory = $tagMap[$currentTag]['category'] ?? '';
    } else {
        $navCategory = $currentTag;
    }
}

// Danh sách các Filter Pill 1-click tinh gọn chuẩn AAA
$filterPills = [
    [
        'key'       => 'all',
        'title'     => 'Tất cả',
        'icon'      => 'fa-solid fa-layer-group',
        'is_active' => (empty($navType) && empty($navCategory)),
        'params'    => [],
    ],
    [
        'key'       => 'ai',
        'title'     => '✨ AI & Copilot+',
        'icon'      => 'fa-solid fa-wand-magic-sparkles',
        'is_ai'     => true,
        'is_active' => ($navCategory === 'ai-cong-nghe-moi' || $navCategory === 'ai'),
        'params'    => ['category' => 'ai-cong-nghe-moi'],
    ],
    [
        'key'       => 'review',
        'title'     => 'Đánh giá & Review',
        'icon'      => 'fa-solid fa-star',
        'is_active' => (empty($navCategory) && $navType === 'review'),
        'params'    => ['type' => 'review'],
    ],
    [
        'key'       => 'guide',
        'title'     => 'Tư vấn chọn mua',
        'icon'      => 'fa-solid fa-compass',
        'is_active' => (empty($navCategory) && $navType === 'guide'),
        'params'    => ['type' => 'guide'],
    ],
    [
        'key'       => 'howto',
        'title'     => 'Mẹo hay & Thủ thuật',
        'icon'      => 'fa-solid fa-sliders',
        'is_active' => (empty($navCategory) && $navType === 'howto'),
        'params'    => ['type' => 'howto'],
    ],
    [
        'key'       => 'comparison',
        'title'     => 'So sánh sản phẩm',
        'icon'      => 'fa-solid fa-code-compare',
        'is_active' => (empty($navCategory) && $navType === 'comparison'),
        'params'    => ['type' => 'comparison'],
    ],
];
?>

<div class="news-category-section news-category-section--aaa">
    <!-- Intro Header & Search Form -->
    <header class="news-intro-header">
        <div class="news-intro-text">
            <h1 class="news-intro-title">Tin Tức Công Nghệ</h1>
            <p class="news-intro-desc">Cập nhật tin tức, đánh giá phần cứng & hướng dẫn chọn mua mới nhất</p>
        </div>

        <!-- Header Search Form -->
        <form action="<?= url('post') ?>" method="get" class="news-search-form">
            <?php if (!empty($currentType)): ?>
                <input type="hidden" name="type" value="<?= e($currentType) ?>">
            <?php endif; ?>
            <?php if (!empty($currentCategory)): ?>
                <input type="hidden" name="category" value="<?= e($currentCategory) ?>">
            <?php endif; ?>

            <div class="news-search-input-wrapper">
                <input
                    type="text"
                    name="q"
                    value="<?= e($currentQ) ?>"
                    placeholder="Tìm bài viết, thủ thuật..."
                    aria-label="Tìm kiếm bài viết"
                    required
                    maxlength="150"
                >
                <button type="submit" class="news-search-submit-btn" aria-label="Thực hiện tìm kiếm">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                </button>
                <?php if (!empty($currentQ)): ?>
                    <a
                        href="<?= url('post' . (!empty($currentCategory) ? '?category=' . urlencode($currentCategory) : (!empty($currentType) ? '?type=' . urlencode($currentType) : ''))) ?>"
                        class="news-search-clear-btn"
                        aria-label="Xóa từ khóa tìm kiếm"
                    >
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </header>

    <!-- Filter Bar: 1-Click Streamlined Pills -->
    <div class="news-filter-controls">
        <nav class="news-type-pills news-type-pills--streamlined" aria-label="Bộ lọc bài viết">
            <?php foreach ($filterPills as $pill): ?>
                <?php
                $queryParams = $pill['params'];
                if (!empty($currentQ)) {
                    $queryParams['q'] = $currentQ;
                }
                $linkUrl = url('post' . (!empty($queryParams) ? '?' . http_build_query($queryParams) : ''));
                $isActive = $pill['is_active'];
                $aiClass = !empty($pill['is_ai']) ? 'news-type-pill--ai' : '';
                ?>
                <a
                    href="<?= $linkUrl ?>"
                    class="news-type-pill <?= $aiClass ?> <?= $isActive ? 'is-active' : '' ?>"
                    <?= $isActive ? 'aria-current="page"' : '' ?>
                >
                    <i class="<?= e($pill['icon']) ?>" aria-hidden="true"></i>
                    <span><?= e($pill['title']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- Instance Mobile cho Hot Topics (< 1024px) -->
    <?php
    $hotTopicsVariant = 'mobile';
    require __DIR__ . '/_hot_topics.php';
    ?>
</div>
