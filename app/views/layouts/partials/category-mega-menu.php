<?php
// Tránh lỗi chưa định nghĩa
$globalCategoryMenu = $globalCategoryMenu ?? [];
?>

<!-- Nút kích hoạt Mega Menu (Desktop) / Hiển thị danh mục Accordion (Mobile) -->
<div class="main-nav__categories" id="megaMenuTrigger" aria-expanded="false" tabindex="0" role="button">
    <i class="fa-solid fa-bars"></i> Danh mục sản phẩm
</div>

<!-- Mobile Category Accordion (ẩn trên desktop, hiển thị trên mobile khi drawer mở) -->
<div class="mobile-categories-accordion">
    <?php foreach ($globalCategoryMenu as $item): ?>
        <div class="mobile-cat-item">
            <div class="mobile-cat-header">
                <a href="<?= url('home/search?cat=' . $item['parent']['slug']) ?>">
                    <i class="<?= e($item['parent']['icon'] ?? 'fa-solid fa-tag') ?>"></i>
                    <?= e($item['parent']['name']) ?>
                </a>
                <?php if (!empty($item['children'])): ?>
                    <button type="button" class="mobile-cat-toggle" aria-expanded="false"><i class="fa-solid fa-chevron-down"></i></button>
                <?php endif; ?>
            </div>
            <?php if (!empty($item['children'])): ?>
                <div class="mobile-cat-children" style="display: none;">
                    <?php foreach ($item['children'] as $child): ?>
                        <a href="<?= url('home/search?cat=' . $child['slug']) ?>">
                            <i class="<?= e($child['icon'] ?? 'fa-solid fa-caret-right') ?>"></i> 
                            <?= e($child['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<!-- Mega Menu Overlay (Màn mờ) -->
<div class="mega-menu-overlay" id="megaMenuOverlay" aria-hidden="true"></div>

<!-- Mega Menu Container -->
<div class="mega-menu-container" id="megaMenuContainer" aria-hidden="true">
    <div class="container mega-menu-inner">
        <!-- Cột trái (Danh mục cấp 1) -->
        <div class="mega-menu-left">
            <ul class="mega-menu-parents">
                <?php foreach ($globalCategoryMenu as $index => $item): ?>
                    <li class="mega-menu-parent-item <?= $index === 0 ? 'is-active' : '' ?>" data-target="mega-panel-<?= $item['parent']['id'] ?>" tabindex="0" role="menuitem">
                        <div class="mega-menu-parent-link">
                            <i class="<?= e($item['parent']['icon'] ?? 'fa-solid fa-tag') ?>"></i>
                            <span><?= e($item['parent']['name']) ?></span>
                        </div>
                        <i class="fa-solid fa-chevron-right arrow-icon"></i>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <!-- Panel phải (Nội dung từng danh mục) -->
        <div class="mega-menu-right">
            <?php foreach ($globalCategoryMenu as $index => $item): ?>
                <div class="mega-menu-panel <?= $index === 0 ? 'is-active' : '' ?>" id="mega-panel-<?= $item['parent']['id'] ?>">
                    <!-- Tiêu đề Panel có link dẫn tới trang danh mục -->
                    <h3 class="mega-panel-title">
                        <a href="<?= url('home/search?cat=' . $item['parent']['slug']) ?>"><?= e($item['parent']['name']) ?></a>
                    </h3>
                    
                    <div class="mega-menu-grid">
                        <?php if (!empty($item['children'])): ?>
                            <div class="mega-menu-col">
                                <h4>Danh mục con</h4>
                                <ul class="mega-menu-links">
                                    <?php foreach ($item['children'] as $child): ?>
                                        <li><a href="<?= url('home/search?cat=' . $child['slug']) ?>"><?= e($child['name']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['brands'])): ?>
                            <div class="mega-menu-col">
                                <h4>Thương hiệu nổi bật</h4>
                                <div class="mega-menu-brands">
                                    <?php foreach ($item['brands'] as $brand): ?>
                                        <a href="<?= url('home/search?cat=' . $item['parent']['slug'] . '&brand=' . $brand['id']) ?>" class="mega-brand-link">
                                            <?= e($brand['name']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['price_ranges'])): ?>
                            <div class="mega-menu-col">
                                <h4>Chọn theo mức giá</h4>
                                <ul class="mega-menu-links">
                                    <?php foreach ($item['price_ranges'] as $price): ?>
                                        <li><a href="<?= url($price['url']) ?>"><?= e($price['label']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($item['featured_products'])): ?>
                            <div class="mega-menu-col mega-menu-col-products">
                                <h4>Sản phẩm nổi bật</h4>
                                <div class="mega-menu-products">
                                    <?php foreach ($item['featured_products'] as $prod): ?>
                                        <a href="<?= url('product/detail/' . $prod['slug']) ?>" class="mega-product-card">
                                            <div class="mega-product-img">
                                                <img src="<?= e(productImageUrl($prod['image'] ?? '')) ?>" alt="<?= e($prod['name']) ?>">
                                            </div>
                                            <div class="mega-product-info">
                                                <span class="mega-product-name"><?= e($prod['name']) ?></span>
                                                <span class="mega-product-price"><?= formatPrice($prod['price']) ?></span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
