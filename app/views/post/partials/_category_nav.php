<?php
/**
 * Category Navigation - Tier 1 Content Categories & Tier 2 Topic Filters
 * Variables: $currentType (string), $currentCategory (string), $currentTag (string), $currentQ (string)
 */
$currentType     = $currentType     ?? '';
$currentCategory = $currentCategory ?? '';
$currentTag      = $currentTag      ?? '';
$currentQ        = $currentQ        ?? '';

// ── Xử lý Normalized Nav State (chỉ dùng để active highlight UI) ────────────────
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

// Tầng 1: Loại nội dung (Content Types)
$contentTypes = [
    '' => [
        'title' => 'Tất cả nội dung',
        'icon'  => 'fa-solid fa-layer-group',
    ],
    'news' => [
        'title' => 'Ra mắt & Xu hướng',
        'icon'  => 'fa-solid fa-bolt',
    ],
    'review' => [
        'title' => 'Đánh giá & Review',
        'icon'  => 'fa-solid fa-star',
    ],
    'guide' => [
        'title' => 'Tư vấn chọn mua',
        'icon'  => 'fa-solid fa-compass',
    ],
    'howto' => [
        'title' => 'Mẹo hay & Thủ thuật',
        'icon'  => 'fa-solid fa-wand-magic-sparkles',
    ],
    'comparison' => [
        'title' => 'So sánh sản phẩm',
        'icon'  => 'fa-solid fa-code-compare',
    ],
];

// Tầng 2: Chủ đề thiết bị (Device Topics)
$topics = [
    ''                 => 'Tất cả thiết bị',
    'laptop'           => 'Laptop',
    'pc-gaming'        => 'PC Gaming',
    'pc-linh-kien'     => 'PC & Linh kiện',
    'man-hinh'         => 'Màn hình',
    'gaming-gear'      => 'Gaming Gear',
    'ai-cong-nghe-moi' => 'AI & Công nghệ',
];
?>

<div class="news-category-section">
    <!-- Intro Header & Search Form -->
    <header class="news-intro-header">
        <div class="news-intro-text">
            <span class="news-intro-eyebrow">
                <i class="fa-solid fa-newspaper" aria-hidden="true"></i> TechPilot Editorial
            </span>
            <h1 class="news-intro-title">Tin Tức & Điểm Tin Công Nghệ</h1>
            <p class="news-intro-desc">Khám phá các bài đánh giá sản phẩm chuyên sâu, tư vấn chọn mua tối ưu và tin tức công nghệ mới nhất từ đội ngũ TechPilot.</p>
        </div>

        <!-- Header Search Form (sử dụng RAW variables) -->
        <form action="<?= url('post') ?>" method="get" class="news-search-form">
            <?php if (!empty($currentType)): ?>
                <input type="hidden" name="type" value="<?= e($currentType) ?>">
            <?php endif; ?>
            <?php if (!empty($currentCategory)): ?>
                <input type="hidden" name="category" value="<?= e($currentCategory) ?>">
            <?php endif; ?>
            <?php if (empty($currentType) && empty($currentCategory) && !empty($currentTag)): ?>
                <input type="hidden" name="tag" value="<?= e($currentTag) ?>">
            <?php endif; ?>

            <div class="news-search-input-wrapper">
                <input
                    type="text"
                    name="q"
                    value="<?= e($currentQ) ?>"
                    placeholder="Tìm bài viết, hướng dẫn..."
                    aria-label="Tìm kiếm bài viết"
                    required
                    maxlength="150"
                >
                <button type="submit" class="news-search-submit-btn" aria-label="Thực hiện tìm kiếm">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                </button>
                <?php if (!empty($currentQ)): ?>
                    <?php
                    $clearParams = [];
                    if (!empty($currentType))     $clearParams['type']     = $currentType;
                    if (!empty($currentCategory)) $clearParams['category'] = $currentCategory;
                    if (empty($currentType) && empty($currentCategory) && !empty($currentTag)) {
                        $clearParams['tag'] = $currentTag;
                    }
                    ?>
                    <a
                        href="<?= url('post' . (!empty($clearParams) ? '?' . http_build_query($clearParams) : '')) ?>"
                        class="news-search-clear-btn"
                        aria-label="Xóa từ khóa tìm kiếm"
                    >
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </header>

    <!-- Filter Control Group: Content Type Tabs & Topic Select Form -->
    <div class="news-filter-controls">
        <!-- Tier 1: Content Category Tabs -->
        <nav class="news-type-pills" aria-label="Loại nội dung">
            <?php foreach ($contentTypes as $typeKey => $info): ?>
                <?php
                $isActive = ($navType === $typeKey);
                $query = [];
                if (!empty($typeKey)) {
                    $query['type'] = $typeKey;
                }
                if (!empty($currentCategory)) {
                    $query['category'] = $currentCategory;
                }
                if (!empty($currentQ)) {
                    $query['q'] = $currentQ;
                }
                // Loại bỏ tag khi người dùng chủ động chọn tab type mới
                $linkUrl = url('post' . (!empty($query) ? '?' . http_build_query($query) : ''));
                ?>
                <a
                    href="<?= $linkUrl ?>"
                    class="news-type-pill <?= $isActive ? 'is-active' : '' ?>"
                    <?= $isActive ? 'aria-current="page"' : '' ?>
                >
                    <i class="<?= e($info['icon']) ?>" aria-hidden="true"></i>
                    <span><?= e($info['title']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Tier 2: Native GET Form cho Device Topics (Sử dụng RAW variables cho hidden inputs) -->
        <form action="<?= url('post') ?>" method="get" class="news-topic-form">
            <?php if (!empty($currentType)): ?>
                <input type="hidden" name="type" value="<?= e($currentType) ?>">
            <?php endif; ?>
            <?php if (!empty($currentQ)): ?>
                <input type="hidden" name="q" value="<?= e($currentQ) ?>">
            <?php endif; ?>

            <div class="news-topic-select-wrapper">
                <label for="newsTopicSelect" class="news-topic-label">
                    <i class="fa-solid fa-filter" aria-hidden="true"></i> Chủ đề:
                </label>
                <select name="category" id="newsTopicSelect" class="news-topic-select">
                    <?php foreach ($topics as $catKey => $catName): ?>
                        <?php
                        $isCatActive = false;
                        if (empty($catKey)) {
                            $isCatActive = empty($navCategory);
                        } else {
                            $isCatActive = ($navCategory === $catKey)
                                || ($catKey === 'pc-gaming' && $navCategory === 'gaming')
                                || ($catKey === 'ai-cong-nghe-moi' && $navCategory === 'ai');
                        }
                        ?>
                        <option value="<?= e($catKey) ?>" <?= $isCatActive ? 'selected' : '' ?>>
                            <?= e($catName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="news-topic-submit-btn btn btn--primary btn--sm">
                    Áp dụng
                </button>
            </div>
        </form>
    </div>

    <!-- Instance Mobile cho Hot Topics (< 1024px) -->
    <?php
    $hotTopicsVariant = 'mobile';
    require __DIR__ . '/_hot_topics.php';
    ?>
</div>
