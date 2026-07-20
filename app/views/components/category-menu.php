<?php
$verticalCategories = require ROOT_PATH . '/app/data/category-menu.php';
?>
<nav class="vertical-menu" id="sharedCategoryMenu">
    <?php foreach ($verticalCategories as $index => $item): ?>
        <?php $catSlug = !empty($item['slug']) ? $item['slug'] : 'laptop-gaming'; ?>
        <div class="vertical-menu__item" data-category-item="<?= (int)$index ?>">
            <div class="mobile-category-row">
                <a href="<?= url('home/search?cat=' . e($catSlug)) ?>" class="vertical-menu__link">
                    <div>
                        <i class="<?= e($item['icon']) ?>" style="width: 20px;"></i>
                        <span><?= e($item['name']) ?></span>
                    </div>
                    <i class="fa-solid fa-chevron-right arrow-right"></i>
                </a>
                <?php if (!empty($item['columns'])): ?>
                    <button type="button" class="mobile-category-toggle" aria-expanded="false" aria-label="Mở danh mục con">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                <?php endif; ?>
            </div>
            <?php if (!empty($item['columns'])): ?>
                <div class="mega-menu">
                    <div class="mega-menu__inner">
                        <?php foreach ($item['columns'] as $title => $subitems): ?>
                            <div class="mega-menu__column">
                                <h5><?= e($title) ?></h5>
                                <ul>
                                    <?php foreach ($subitems as $subitem): ?>
                                        <li><a href="<?= url('home/search?cat=' . e($catSlug) . '&q=' . urlencode($subitem)) ?>"><?= e($subitem) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</nav>
