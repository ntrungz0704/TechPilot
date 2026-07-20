<?php
$menuTree = $globalCategoryMenu ?? [];
$isStatic = $isStatic ?? false;
$wrapperClass = $isStatic ? 'category-dropdown is-static' : 'category-dropdown';
$wrapperId = $isStatic ? 'categoryStaticMenu' : 'categoryMegaDropdown';
?>

<div class="category-overlay" id="categoryMenuOverlay" aria-hidden="true" hidden></div>

<section class="<?= $wrapperClass ?>" id="<?= $wrapperId ?>" <?= !$isStatic ? 'aria-hidden="true" hidden' : '' ?>>
    <div class="category-dropdown__inner">
        <!-- Sidebar Danh mục dọc -->
        <nav class="category-dropdown__sidebar" aria-label="Danh mục sản phẩm">
            <?php foreach ($menuTree as $index => $cat): ?>
                <?php
                $catId = $cat['id'] ?? 0;
                $slug = !empty($cat['slug']) ? $cat['slug'] : '';
                $name = !empty($cat['name']) ? $cat['name'] : '';
                $icon = !empty($cat['icon']) ? $cat['icon'] : 'fa-solid fa-list';
                $hasMega = !empty($cat['mega_columns']);
                ?>
                <a href="<?= url('home/search?cat=' . urlencode($slug)) ?>" 
                   class="category-sidebar__item" 
                   data-panel-id="panel-<?= $catId ?>">
                    <div class="category-sidebar__item-left">
                        <i class="<?= e($icon) ?> category-icon"></i>
                        <span><?= e($name) ?></span>
                    </div>
                    <?php if ($hasMega): ?>
                        <i class="fa-solid fa-chevron-right category-chevron"></i>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- Mega menu panels -->
        <div class="category-dropdown__mega">
            <?php if (empty($menuTree)): ?>
                <div class="mega-panel__empty" style="padding: 24px;">Không có danh mục nào.</div>
            <?php endif; ?>

            <?php foreach ($menuTree as $index => $cat): ?>
                <?php
                $catId = $cat['id'] ?? 0;
                $slug = !empty($cat['slug']) ? $cat['slug'] : '';
                $megaColumns = $cat['mega_columns'] ?? [];
                if (empty($megaColumns)) continue;
                ?>
                <div class="category-mega__panel" id="panel-<?= $catId ?>">
                    <div class="mega-panel__inner">
                        <?php foreach ($megaColumns as $title => $subitems): ?>
                            <div class="mega-panel__column">
                                <h5 class="mega-panel__title"><?= e($title) ?></h5>
                                <ul class="mega-panel__list">
                                    <?php foreach ($subitems as $subitem): ?>
                                        <?php
                                        // Some subitems are arrays with 'name' and 'query' or 'slug'
                                        $subName = is_array($subitem) ? $subitem['name'] : $subitem;
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
