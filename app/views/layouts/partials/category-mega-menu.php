<?php
$menuTree = $globalCategoryMenu ?? [];
$isStatic = $isStatic ?? false;
$wrapperClass = $isStatic ? 'category-dropdown is-static' : 'category-dropdown';
$wrapperId = $isStatic ? 'categoryStaticMenu' : 'categoryMegaDropdown';

$formatRangeName = function (string $name): string {
    return match ($name) {
        'Dưới 15 triệu'     => 'Đến 15 triệu',
        'Từ 15 - 20 triệu'  => 'Trên 15 đến 20 triệu',
        'Từ 20 - 30 triệu'  => 'Trên 20 đến 30 triệu',
        'Dưới 2 triệu'      => 'Đến 2 triệu',
        'Từ 2 - 5 triệu'   => 'Trên 2 đến 5 triệu',
        'Từ 5 - 10 triệu'  => 'Trên 5 đến 10 triệu',
        'Dưới 5 triệu'      => 'Đến 5 triệu',
        default             => $name,
    };
};
?>

<div class="category-overlay" id="categoryMenuOverlay" aria-hidden="true" hidden></div>

<section class="<?= $wrapperClass ?>" id="<?= $wrapperId ?>" <?= !$isStatic ? 'aria-hidden="true" hidden' : '' ?> aria-label="Menu danh mục sản phẩm">
    <div class="category-dropdown__inner">
        <!-- Sidebar Danh mục dọc -->
        <nav class="category-dropdown__sidebar" aria-label="Danh mục chính">
            <?php foreach ($menuTree as $index => $cat): ?>
                <?php
                $catId = $cat['id'] ?? 0;
                $slug = !empty($cat['slug']) ? $cat['slug'] : '';
                $name = !empty($cat['name']) ? $cat['name'] : '';
                $icon = !empty($cat['icon']) ? $cat['icon'] : 'fa-solid fa-list';
                $hasMega = !empty($cat['mega_columns']);
                $virtualUrl = url('home/search?cat=' . urlencode($slug));
                ?>
                <div class="category-sidebar__row" data-panel-id="panel-<?= $catId ?>">
                    <a href="<?= $virtualUrl ?>" 
                       class="category-sidebar__item" 
                       data-panel-id="panel-<?= $catId ?>"
                       role="menuitem"
                       aria-controls="panel-<?= $catId ?>">
                        <div class="category-sidebar__item-left">
                            <i class="<?= e($icon) ?> category-icon" aria-hidden="true"></i>
                            <span><?= e($name) ?></span>
                        </div>
                        <?php if ($hasMega): ?>
                            <i class="fa-solid fa-chevron-right category-chevron" aria-hidden="true"></i>
                        <?php endif; ?>
                    </a>
                    <?php if ($hasMega): ?>
                        <button type="button" 
                                class="category-mobile-accordion-toggle" 
                                aria-expanded="false" 
                                aria-controls="panel-<?= $catId ?>"
                                aria-label="Mở danh mục con <?= e($name) ?>">
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </nav>

        <!-- Mega menu panels -->
        <div class="category-dropdown__mega" role="region" aria-label="Chi tiết danh mục">
            <?php if (empty($menuTree)): ?>
                <div class="mega-panel__empty" style="padding: 24px;">Không có danh mục nào.</div>
            <?php endif; ?>

            <?php foreach ($menuTree as $index => $cat): ?>
                <?php
                $catId = $cat['id'] ?? 0;
                $slug = !empty($cat['slug']) ? $cat['slug'] : '';
                $groupName = !empty($cat['name']) ? $cat['name'] : '';
                $megaColumns = $cat['mega_columns'] ?? [];
                if (empty($megaColumns)) continue;
                $virtualUrl = url('home/search?cat=' . urlencode($slug));
                ?>
                <div class="category-mega__panel" id="panel-<?= $catId ?>" aria-hidden="true">
                    <div class="mega-panel__header">
                        <span class="mega-panel__group-name"><?= e($groupName) ?></span>
                        <a href="<?= $virtualUrl ?>" class="mega-panel__view-all">
                            Xem tất cả <?= e($groupName) ?> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    </div>
                    <div class="mega-panel__inner <?= $slug === 'pc-linh-kien' ? 'is-linh-kien-grid' : '' ?>">
                        <?php foreach ($megaColumns as $title => $subitems): ?>
                            <div class="mega-panel__column">
                                <h5 class="mega-panel__title"><?= e($title) ?></h5>
                                <ul class="mega-panel__list">
                                    <?php foreach ($subitems as $subitem): ?>
                                        <?php
                                        $subName = is_array($subitem) ? $subitem['name'] : $subitem;
                                        if ($title === 'Mức giá' || $title === 'Khoảng giá') {
                                            $subName = $formatRangeName($subName);
                                        }
                                        
                                        if ($title === 'Danh mục con') {
                                            $subSlug = is_array($subitem) ? $subitem['slug'] : '';
                                            $link = url('home/search?cat=' . urlencode($subSlug));
                                        } else {
                                            $subQuery = is_array($subitem) ? $subitem['query'] : ('q=' . urlencode($subitem));
                                            $link = url('home/search?cat=' . urlencode($slug) . '&' . $subQuery);
                                        }
                                        ?>
                                        <li><a href="<?= $link ?>"><?= e($subName) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
