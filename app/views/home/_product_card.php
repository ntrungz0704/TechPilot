<?php if (!empty($p)): ?>
    <?php
    $imageUrl = productImageUrl($p['image'] ?? '');
    $price = (float)($p['price'] ?? 0);
    $salePrice = isset($p['sale_price']) && $p['sale_price'] !== null ? (float)$p['sale_price'] : (isset($p['discount_price']) ? (float)$p['discount_price'] : null);
    $currentPrice = ($salePrice !== null && $salePrice > 0 && $salePrice < $price) ? $salePrice : $price;
    $hasDiscount = ($salePrice !== null && $salePrice > 0 && $price > $salePrice);
    $discountPercent = $hasDiscount ? round((($price - $salePrice) / $price) * 100) : (int)($p['discount_percent'] ?? 0);
    $isFlashSaleCard = !empty($p['is_flash_sale']) || isset($p['discount_price']);
    ?>
    <div class="product-card">
        <?php if ($discountPercent > 0): ?>
            <span class="product-card__badge">-<?= (int)$discountPercent ?>%</span>
        <?php endif; ?>
        
        <a href="<?= url('product/detail/' . e($p['slug'] ?? '')) ?>" class="product-card__thumb">
            <img class="product-card__image" src="<?= e($imageUrl) ?>" alt="<?= e($p['name'] ?? '') ?>" loading="lazy">
        </a>
        
        <div class="product-card__body">
            <a href="<?= url('product/detail/' . e($p['slug'] ?? '')) ?>" class="product-card__name">
                <?= e($p['name'] ?? '') ?>
            </a>
            
            <div class="product-card__price">
                <span class="product-card__price-now"><?= formatPrice($currentPrice) ?></span>
                <?php if ($hasDiscount): ?>
                    <span class="product-card__price-old"><?= formatPrice($price) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="product-card__rating">
                <span class="stars"><?= renderStars((float)($p['rating'] ?? 5)) ?></span>
                <span class="product-card__reviews">(<?= (int)($p['review_count'] ?? $p['rating_count'] ?? 0) ?>)</span>
            </div>
            
            <?php if ($isFlashSaleCard): ?>
                <?php
                $sold = (int)($p['fs_sold'] ?? 0);
                $stock = (int)($p['fs_stock'] ?? $p['stock'] ?? 10);
                // Tính tổng stock gốc = stock còn lại + đã bán
                $totalStock = $stock + $sold;
                $percent = $totalStock > 0 ? max(0, min(100, round(($sold / $totalStock) * 100))) : 0;
                ?>
                <div class="sold-bar">
                    <div class="sold-bar__track">
                        <div class="sold-bar__fill" style="width: <?= $percent ?>%"></div>
                        <div class="sold-bar__text <?= $percent < 40 ? 'sold-bar__text-dark' : '' ?>">
                            Đã bán <?= $sold ?>/<?= $totalStock ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        
        <!-- Nút thêm nhanh vào giỏ hàng xuất hiện khi hover -->
        <form method="post" action="<?= url('cart/add') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int)($p['id'] ?? 0) ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="product-card__add">
                <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
            </button>
        </form>
    </div>
<?php endif; ?>