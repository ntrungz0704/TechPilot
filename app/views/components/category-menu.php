<?php
// Đánh dấu đã render component này
$categoryMenuRendered = true;

// Nạp dữ liệu
$verticalCategories = require ROOT_PATH . '/app/data/category-menu.php';
?>
<!-- ID duy nhất được JS tìm kiếm và di chuyển -->
<nav class="vertical-menu catalog-menu--hero" id="sharedCategoryMenu">
    <?php foreach ($verticalCategories as $index => $item): ?>
        <div class="vertical-menu__item" data-category-item="<?= $index ?>">
            <a href="<?= url('home/search?q=' . urlencode($item['name'])) ?>" class="vertical-menu__link">
                <div>
                    <i class="<?= e($item['icon']) ?>" style="width: 20px;"></i>
                    <span><?= e($item['name']) ?></span>
                </div>
                <i class="fa-solid fa-chevron-right arrow-right"></i>
            </a>
            
            <?php if (!empty($item['columns'])): ?>
                <div class="mega-menu">
                    <div class="mega-menu__inner">
                        <?php foreach ($item['columns'] as $title => $subitems): ?>
                            <div class="mega-menu__column">
                                <h5><?= e($title) ?></h5>
                                <ul>
                                    <?php foreach ($subitems as $subitem): ?>
                                        <li><a href="<?= url('home/search?q=' . urlencode($subitem)) ?>"><?= e($subitem) ?></a></li>
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
