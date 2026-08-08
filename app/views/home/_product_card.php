<?php if (!empty($p)): ?>
    <?php
    $imageUrl = productImageUrl($p['image'] ?? '', $p['category_slug'] ?? $p['name'] ?? '', (int)($p['id'] ?? 0));
    $priceData = getEffectiveProductData($p);
    $currentPrice = $priceData['final_price'];
    $originalPrice = $priceData['original_price'];
    $hasDiscount = $priceData['has_discount'];
    $discountPercent = $priceData['discount_pct'];
    $isFlashSaleCard = $priceData['is_flash_sale'];
    ?>

    <div class="product-card" data-full-title="<?= e($p['name'] ?? '') ?>" title="<?= e($p['name'] ?? '') ?>">
        <?php if ($hasDiscount && $discountPercent > 0): ?>
            <?php if ($isFlashSaleCard): ?>
                <span class="product-card__badge product-card__badge--flash" title="⚡ Flash Sale Giảm Sâu"><i class="fa-solid fa-fire-flame-curved"></i> 🔥 -<?= (int)$discountPercent ?>%</span>
            <?php else: ?>
                <span class="product-card__badge product-card__badge--promo" title="🏷️ Sản phẩm Khuyến Mãi"><i class="fa-solid fa-tag"></i> -<?= (int)$discountPercent ?>%</span>
            <?php endif; ?>
        <?php endif; ?>
        
        <button type="button" class="product-card__wishlist-btn" onclick="toggleWishlist(<?= (int)($p['id'] ?? 0) ?>, this)" title="Thêm vào yêu thích" style="position: absolute; top: 12px; right: 12px; z-index: 5; background: var(--bg-card, #FFFFFF); border: 1px solid var(--border, #E2E8F0); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary, #64748B); box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: all 0.2s ease;">
            <i class="fa-regular fa-heart" style="font-size: 14px;"></i>
        </button>
        <a href="<?= url('compare?add=' . (int)($p['id'] ?? 0)) ?>" class="product-card__compare-btn" title="Thêm vào so sánh sản phẩm" style="position: absolute; top: 12px; right: 50px; z-index: 5; background: var(--bg-card, #FFFFFF); border: 1px solid var(--border, #E2E8F0); border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #10B981; box-shadow: 0 2px 6px rgba(0,0,0,0.06); transition: all 0.2s ease; text-decoration: none;">
            <i class="fa-solid fa-scale-balanced" style="font-size: 13px;"></i>
        </a>
        
        <a href="<?= url('product/detail/' . e($p['slug'] ?? '')) ?>" class="product-card__thumb" data-full-title="<?= e($p['name'] ?? '') ?>" title="<?= e($p['name'] ?? '') ?>">
            <img class="product-card__image" src="<?= e($imageUrl) ?>" alt="<?= e($p['name'] ?? '') ?>" loading="lazy">
        </a>
        
        <div class="product-card__body">
            <a href="<?= url('product/detail/' . e($p['slug'] ?? '')) ?>" class="product-card__name" data-full-title="<?= e($p['name'] ?? '') ?>" title="<?= e($p['name'] ?? '') ?>">
                <?= e($p['name'] ?? '') ?>
            </a>

            <?php
            $quickSpecs = function_exists('getProductHighlightBadges') ? getProductHighlightBadges($p) : [];
            ?>

            <?php if (!empty($quickSpecs)): ?>
                <div class="product-card__specs-box">
                    <div class="specs-pill-grid">
                        <?php foreach ($quickSpecs as $qs): ?>
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
                $allocation = max(0, (int)($p['fs_stock'] ?? 0));
                $sold = max(0, (int)($p['fs_sold'] ?? 0));
                $percent = $allocation > 0 ? max(0, min(100, round(($sold / $allocation) * 100))) : 0;
                $isSoldOut = ($allocation > 0 && $sold >= $allocation);
                ?>
                <div class="sold-bar" style="margin-top: 6px;">
                    <div class="sold-bar__track">
                        <div class="sold-bar__fill" style="width: <?= $percent ?>%; <?= $isSoldOut ? 'background: linear-gradient(90deg, #EF4444, #DC2626);' : '' ?>"></div>
                        <div class="sold-bar__text <?= ($percent < 40 && !$isSoldOut) ? 'sold-bar__text-dark' : '' ?>" style="<?= $isSoldOut ? 'color: #FFFFFF; font-weight: 700;' : '' ?>">
                            <?= $isSoldOut ? '🔥 Đã bán hết suất Flash Sale' : 'Đã bán ' . $sold . '/' . $allocation ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        
        <!-- Nút thêm nhanh vào giỏ hàng xuất hiện khi hover -->
        <form method="post" action="<?= url('cart/add') ?>" class="product-card__add-form">
            <input type="hidden" name="return_url" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int)($p['id'] ?? 0) ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="product-card__add">
                <i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ
            </button>
        </form>
    </div>
<?php endif; ?>
