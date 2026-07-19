<?php if (!empty($p)): ?>
    <?php
    $imageUrl = productImageUrl($p['image'] ?? '');
    // Hỗ trợ cả khi $p là kết quả query từ bảng flash_sales hoặc bảng products thông thường
    $currentPrice = isset($p['discount_price']) ? $p['discount_price'] : $p['price'];
    $isFlashSaleCard = isset($p['discount_price']);
    ?>
    <div class="product-card">
        <?php if (!empty($p['discount_percent']) && $p['discount_percent'] > 0): ?>
            <span class="product-card__badge">-<?= (int)$p['discount_percent'] ?>%</span>
        <?php endif; ?>
        
        <a href="<?= url('product/detail/' . e($p['slug'])) ?>" class="product-card__thumb">
            <img class="product-card__image" src="<?= e($imageUrl) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
        </a>
        
        <div class="product-card__body">
            <a href="<?= url('product/detail/' . e($p['slug'])) ?>" class="product-card__name">
                <?= e($p['name']) ?>
            </a>
            
            <div class="product-card__price">
                <span class="product-card__price-now"><?= formatPrice($currentPrice) ?></span>
                <?php if (!empty($p['old_price']) && $p['old_price'] > $currentPrice): ?>
                    <span class="product-card__price-old"><?= formatPrice($p['old_price']) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="product-card__rating">
                <span class="stars"><?= renderStars((float)($p['rating'] ?? 5)) ?></span>
                <span class="product-card__reviews">(<?= (int)($p['review_count'] ?? 0) ?>)</span>
            </div>
            
            <?php if ($isFlashSaleCard): ?>
                <?php
                $sold = (int)($p['fs_sold'] ?? 0);
                $stock = (int)($p['fs_stock'] ?? 10);
                $percent = $stock > 0 ? max(0, min(100, round(($sold / $stock) * 100))) : 0;
                ?>
                <div class="sold-bar">
                    <div class="sold-bar__track">
                        <div class="sold-bar__fill" style="width: <?= $percent ?>%"></div>
                        <div class="sold-bar__text <?= $percent < 40 ? 'sold-bar__text-dark' : '' ?>">
                            Đã bán <?= $sold ?>/<?= $stock ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Nút thêm nhanh vào giỏ hàng xuất hiện khi hover -->
        <form method="post" action="<?= url('cart/add') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="product-card__add">
                <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
            </button>
        </form>
    </div>
<?php endif; ?>