<?php
/**
 * Category Navigation - Tier 1 Content Categories & Tier 2 Topic Filters
 * Variables: $currentType (string), $currentCategory (string), $currentTag (string)
 */
$currentType     = $currentType     ?? '';
$currentCategory = $currentCategory ?? '';
$currentTag      = $currentTag      ?? '';

// Map legacy tag nếu chưa có currentType/currentCategory
if (empty($currentType) && empty($currentCategory) && !empty($currentTag)) {
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
        $currentType     = $tagMap[$currentTag]['type'] ?? '';
        $currentCategory = $tagMap[$currentTag]['category'] ?? '';
    } else {
        $currentCategory = $currentTag;
    }
}

// Tầng 1: Loại nội dung (Content Types)
$contentTypes = [
    '' => [
        'title'    => 'Tất cả nội dung',
        'subtitle' => 'Toàn bộ bài viết',
        'icon'     => 'fa-solid fa-layer-group',
    ],
    'news' => [
        'title'    => 'Ra mắt & Xu hướng',
        'subtitle' => 'Tin tức nóng hổi',
        'icon'     => 'fa-solid fa-bolt',
    ],
    'review' => [
        'title'    => 'Đánh giá & Review',
        'subtitle' => 'Chuyên sâu & Khách quan',
        'icon'     => 'fa-solid fa-star',
    ],
    'guide' => [
        'title'    => 'Tư vấn chọn mua',
        'subtitle' => 'Gợi ý tối ưu',
        'icon'     => 'fa-solid fa-compass',
    ],
    'howto' => [
        'title'    => 'Mẹo hay & Thủ thuật',
        'subtitle' => 'Hướng dẫn chi tiết',
        'icon'     => 'fa-solid fa-wand-magic-sparkles',
    ],
    'comparison' => [
        'title'    => 'So sánh sản phẩm',
        'subtitle' => 'Đặt lên bàn cân',
        'icon'     => 'fa-solid fa-code-compare',
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
    <!-- Intro Trang -->
    <header class="news-intro-header">
        <div class="news-intro-badge">
            <i class="fa-solid fa-newspaper" aria-hidden="true"></i> TechPilot Editorial
        </div>
        <h1 class="news-intro-title">Tin Tức & Điểm Tin Công Nghệ</h1>
        <p class="news-intro-desc">Khám phá các bài đánh giá sản phẩm chuyên sâu, tư vấn chọn mua tối ưu và tin tức công nghệ mới nhất từ đội ngũ TechPilot.</p>
    </header>

    <!-- Tầng 1: Content Category Cards -->
    <nav class="news-content-categories" aria-label="Loại nội dung">
        <?php foreach ($contentTypes as $typeKey => $info): ?>
            <?php
            $isActive = ($currentType === $typeKey);
            $query = [];
            if (!empty($typeKey)) {
                $query['type'] = $typeKey;
            }
            if (!empty($currentCategory)) {
                $query['category'] = $currentCategory;
            }
            $linkUrl = url('post' . (!empty($query) ? '?' . http_build_query($query) : ''));
            ?>
            <a
                href="<?= $linkUrl ?>"
                class="news-category-card <?= $isActive ? 'is-active' : '' ?>"
                <?= $isActive ? 'aria-current="page"' : '' ?>
            >
                <div class="news-category-card__icon">
                    <i class="<?= e($info['icon']) ?>" aria-hidden="true"></i>
                </div>
                <div class="news-category-card__info">
                    <span class="news-category-card__title"><?= e($info['title']) ?></span>
                    <span class="news-category-card__sub"><?= e($info['subtitle']) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Tầng 2: Topic Filter Bar (Chủ đề thiết bị) -->
    <nav class="news-topic-bar" aria-label="Chủ đề thiết bị">
        <span class="news-topic-bar__label">
            <i class="fa-solid fa-filter" aria-hidden="true"></i> Chủ đề:
        </span>
        <div class="news-topic-pills">
            <?php foreach ($topics as $catKey => $catName): ?>
                <?php
                $isCatActive = false;
                if (empty($catKey)) {
                    $isCatActive = empty($currentCategory);
                } else {
                    $isCatActive = ($currentCategory === $catKey)
                        || ($catKey === 'pc-gaming' && $currentCategory === 'gaming')
                        || ($catKey === 'ai-cong-nghe-moi' && $currentCategory === 'ai');
                }

                $query = [];
                if (!empty($currentType)) {
                    $query['type'] = $currentType;
                }
                if (!empty($catKey)) {
                    $query['category'] = $catKey;
                }
                $linkUrl = url('post' . (!empty($query) ? '?' . http_build_query($query) : ''));
                ?>
                <a
                    href="<?= $linkUrl ?>"
                    class="news-topic-pill <?= $isCatActive ? 'is-active' : '' ?>"
                    <?= $isCatActive ? 'aria-current="page"' : '' ?>
                >
                    <?= e($catName) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
</div>
