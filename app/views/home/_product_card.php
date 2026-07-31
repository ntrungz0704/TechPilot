<?php if (!empty($p)): ?>
    <?php
    $imageUrl = productImageUrl($p['image'] ?? '', $p['category_slug'] ?? $p['name'] ?? '', (int)($p['id'] ?? 0));
    $price = (float)($p['price'] ?? 0);

    // Giá gốc: ưu tiên old_price nếu lớn hơn price, kế đó dùng price
    $oldPrice = isset($p['old_price']) && (float)$p['old_price'] > $price ? (float)$p['old_price'] : null;

    // Giá khuyến mãi: ưu tiên sale_price, rồi discount_price (alias từ flash sale query)
    $salePrice = isset($p['sale_price']) && $p['sale_price'] !== null ? (float)$p['sale_price'] : (isset($p['discount_price']) ? (float)$p['discount_price'] : null);

    // Xác định giá hiện tại và giá gốc để hiển thị
    if ($salePrice !== null && $salePrice > 0 && $salePrice < $price) {
        // Trường hợp: price là giá gốc, sale_price là giá sale
        $currentPrice   = $salePrice;
        $originalPrice  = $price;
        $hasDiscount    = true;
    } elseif ($oldPrice !== null) {
        // Trường hợp: old_price là giá gốc, price là giá đã giảm
        $currentPrice   = $price;
        $originalPrice  = $oldPrice;
        $hasDiscount    = true;
    } else {
        $currentPrice   = $price;
        $originalPrice  = $price;
        $hasDiscount    = false;
    }

    $discountPercent = $hasDiscount
        ? round((($originalPrice - $currentPrice) / $originalPrice) * 100)
        : 0;
    $isFlashSaleCard = !empty($p['is_flash_sale']) || isset($p['discount_price']);
    ?>

    <div class="product-card">
        <?php if ($discountPercent > 0): ?>
            <span class="product-card__badge">-<?= (int)$discountPercent ?>%</span>
        <?php endif; ?>
        
        <button type="button" class="product-card__wishlist-btn" onclick="toggleWishlist(<?= (int)($p['id'] ?? 0) ?>, this)" title="Thêm vào yêu thích" style="position: absolute; top: 12px; right: 12px; z-index: 5; background: var(--bg-card, #FFFFFF); border: 1px solid var(--border, #E2E8F0); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary, #64748B); box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: all 0.2s ease;">
            <i class="fa-regular fa-heart" style="font-size: 14px;"></i>
        </button>
        <a href="<?= url('compare?add=' . (int)($p['id'] ?? 0)) ?>" class="product-card__compare-btn" title="Thêm vào so sánh sản phẩm" style="position: absolute; top: 12px; right: 50px; z-index: 5; background: var(--bg-card, #FFFFFF); border: 1px solid var(--border, #E2E8F0); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #10B981; box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: all 0.2s ease; text-decoration: none;">
            <i class="fa-solid fa-scale-balanced" style="font-size: 13px;"></i>
        </a>
        
        <a href="<?= url('product/detail/' . e($p['slug'] ?? '')) ?>" class="product-card__thumb">
            <img class="product-card__image" src="<?= e($imageUrl) ?>" alt="<?= e($p['name'] ?? '') ?>" loading="lazy">
        </a>
        
        <div class="product-card__body">
            <a href="<?= url('product/detail/' . e($p['slug'] ?? '')) ?>" class="product-card__name">
                <?= e($p['name'] ?? '') ?>
            </a>

            <?php
            $quickSpecs = [];
            if (!empty($p['specs'])) {
                $rawS = is_array($p['specs']) ? $p['specs'] : json_decode($p['specs'], true);
                if (isset($rawS['specs']) && is_array($rawS['specs'])) $rawS = $rawS['specs'];
                if (is_array($rawS)) {
                    if (!empty($rawS['cpu_short'])) $quickSpecs[] = ['icon' => 'fa-microchip', 'text' => $rawS['cpu_short']];
                    elseif (!empty($rawS['cpu_model'])) $quickSpecs[] = ['icon' => 'fa-microchip', 'text' => mb_substr($rawS['cpu_model'], 0, 15)];
                    
                    if (!empty($rawS['gpu_short'])) $quickSpecs[] = ['icon' => 'fa-desktop', 'text' => $rawS['gpu_short']];
                    elseif (!empty($rawS['vga_short'])) $quickSpecs[] = ['icon' => 'fa-desktop', 'text' => $rawS['vga_short']];
                    
                    if (!empty($rawS['ram_capacity_gb'])) $quickSpecs[] = ['icon' => 'fa-memory', 'text' => $rawS['ram_capacity_gb'] . 'GB'];
                    elseif (!empty($rawS['ram'])) $quickSpecs[] = ['icon' => 'fa-memory', 'text' => $rawS['ram']];
                    
                    if (!empty($rawS['storage_capacity_gb'])) {
                        $cap = (int)$rawS['storage_capacity_gb'];
                        $quickSpecs[] = ['icon' => 'fa-hard-drive', 'text' => $cap >= 1024 ? round($cap/1024) . 'TB' : $cap . 'GB'];
                    } elseif (!empty($rawS['ssd'])) {
                        $quickSpecs[] = ['icon' => 'fa-hard-drive', 'text' => $rawS['ssd']];
                    }

                    if (!empty($rawS['screen_short'])) $quickSpecs[] = ['icon' => 'fa-tv', 'text' => $rawS['screen_short']];
                    elseif (!empty($rawS['screen_size_inch'])) $quickSpecs[] = ['icon' => 'fa-tv', 'text' => $rawS['screen_size_inch'] . '"'];
                }
            }
            ?>

            <?php if (!empty($quickSpecs)): ?>
                <div class="product-card__specs-box">
                    <div class="specs-pill-grid">
                        <?php foreach (array_slice($quickSpecs, 0, 4) as $qs): ?>
                            <span class="specs-pill"><i class="fa-solid <?= $qs['icon'] ?>"></i> <?= e($qs['text']) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="product-card__price">
                <span class="product-card__price-now"><?= formatPrice($currentPrice) ?></span>
                <?php if ($hasDiscount): ?>
                    <span class="product-card__price-old"><?= formatPrice($originalPrice) ?></span>
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