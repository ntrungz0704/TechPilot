<?php
$product = $product ?? [];
$specs = $specs ?? [];
$related = $related ?? [];
$productImages = $productImages ?? [];
$reviews = $reviews ?? [];

require_once ROOT_PATH . '/app/services/ProductSpecPresenter.php';

// Product gallery helper — ensure products.image is always first and deduplicate based on resolved URLs
function getGalleryImages(array $product, array $extraImgs): array {
    $list = [];
    $resolvedList = [];
    $catSlug = $product['category_slug'] ?? $product['name'] ?? '';
    $productId = (int)($product['id'] ?? 0);

    $mainImg = trim((string)($product['image'] ?? ''));
    $mainResolved = productImageUrl($mainImg, $catSlug, $productId);

    // Add main image if valid
    if ($mainResolved !== '') {
        $list[] = $mainImg;
        $resolvedList[] = $mainResolved;
    }

    if (!empty($extraImgs)) {
        foreach ($extraImgs as $item) {
            $url = is_array($item) ? ($item['image_url'] ?? $item['image_path'] ?? '') : (string)$item;
            $url = trim($url);
            if ($url !== '') {
                $resolvedUrl = productImageUrl($url, $catSlug, $productId);
                if ($resolvedUrl !== '' && !in_array($resolvedUrl, $resolvedList, true)) {
                    $list[] = $url;
                    $resolvedList[] = $resolvedUrl;
                }
            }
        }
    }

    if (empty($list)) {
        $list[] = ''; // Add empty string to trigger placeholder via productImageUrl later
    }
    
    return $list;
}
$galleryImages = getGalleryImages($product, $productImages);

?>

<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <a href="<?= url('home/search?cat=' . e($product['category_slug'] ?? '')) ?>"><?= e($product['category_name'] ?? 'Danh mục') ?></a> <i class="fa-solid fa-chevron-right"></i>
    <span><?= e($product['name']) ?></span>
</section>

<section class="container product-detail">
    <!-- Gallery -->
    <div class="product-detail__gallery">
        <div class="product-detail__main-image">
            <?php if (!empty($product['has_discount']) && !empty($product['discount_pct'])): ?>
                <?php if (!empty($product['is_flash_sale'])): ?>
                    <span class="product-card__badge product-card__badge--flash" style="font-size: 13px; padding: 6px 14px;" title="⚡ Flash Sale Giảm Sâu"><i class="fa-solid fa-fire-flame-curved"></i> 🔥 -<?= (int)$product['discount_pct'] ?>% FLASH SALE</span>
                <?php else: ?>
                    <span class="product-card__badge product-card__badge--promo" style="font-size: 13px; padding: 6px 14px;" title="🏷️ Sản phẩm Khuyến Mãi"><i class="fa-solid fa-tag"></i> -<?= (int)$product['discount_pct'] ?>% TIẾT KIỆM</span>
                <?php endif; ?>
            <?php endif; ?>
            <img src="<?= e(productImageUrl($galleryImages[0] ?? '', $product['category_slug'] ?? $product['name'] ?? '', (int)($product['id'] ?? 0))) ? alt="TechPilot Asset">" alt="<?= e($product['name']) ?>" class="product-detail__main-image-src" id="mainProdImage">
        </div>
        <?php if (count($galleryImages) > 1): ?>
            <div class="product-detail__thumbs">
                <?php foreach ($galleryImages as $idx => $imgUrl): ?>
                    <div class="product-detail__thumb <?= $idx === 0 ? 'is-active' : '' ?>" onclick="changeProductImage('<?= e(productImageUrl($imgUrl, $product['category_slug'] ?? $product['name'] ?? '', (int)($product['id'] ?? 0))) ?>', this)">
                        <img src="<?= e(productImageUrl($imgUrl, $product['category_slug'] ?? $product['name'] ?? '', (int)($product['id'] ?? 0))) ? alt="TechPilot Asset">" alt="<?= e($product['name']) ?>" class="product-detail__thumb-image">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Product Info -->
    <div class="product-detail__info">
        <span class="product-detail__brand"><?= e($product['brand_name'] ?? $product['brand'] ?? 'TechPilot') ?></span>
        <h1><?= e($product['name']) ?></h1>

        <div class="product-detail__meta">
            <span class="stars"><?= renderStars((float)($product['rating'] ?? 5)) ?></span>
            <span><?= (int)($product['review_count'] ?? 0) ?> đánh giá</span>
            <span class="divider">|</span>
            <span class="in-stock"><i class="fa-solid fa-circle-check"></i> Còn hàng (<?= (int)$product['stock'] ?>)</span>
            <?php 
            require_once ROOT_PATH . '/app/services/ProductIntelligenceService.php';
            $vfm = ProductIntelligenceService::calculateValueForMoney($product);
            ?>
            <span class="divider">|</span>
            <span style="font-weight: 700; color: #10B981;" title="Độ đáng giá cấu hình so với giá thành"><i class="fa-solid fa-star" style="color: #FBBF24;"></i> AI Value: <?= $vfm ?>/10</span>
        </div>

        <p class="product-detail__short-desc"><?= e($product['short_desc'] ?? 'Đang cập nhật mô tả ngắn cho sản phẩm này.') ?></p>

        <!-- Key Highlight Spec Chips -->
        <?php if (!empty($specs)): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; margin: 15px 0;">
                <?php 
                $countChip = 0;
                foreach ($specs as $sKey => $sVal):
                    if ($countChip >= 4) break;
                    if (in_array($sKey, ['gpu_model', 'vram_gb', 'vram_type', 'recommended_psu_w', 'warranty_months', 'cpu_model', 'socket', 'screen_size_inch', 'refresh_rate_hz'])):
                        $countChip++;
                ?>
                    <div style="background-color: var(--bg-card, #F8FAFC); border: 1px solid var(--border, #E2E8F0); border-radius: 8px; padding: 8px 12px; display: flex; flex-direction: column; gap: 2px;">
                        <span style="font-size: 11px; color: var(--text-secondary, #64748B); font-weight: 600; text-transform: uppercase;"><?= ProductSpecPresenter::getLabel($sKey) ?></span>
                        <strong style="font-size: 13px; color: var(--primary, #2563EB); font-weight: 700;"><?= ProductSpecPresenter::formatValue($sKey, $sVal) ?></strong>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        <?php endif; ?>

        <div class="product-detail__price">
            <span class="price-now"><?= formatPrice($product['final_price']) ?></span>
            <?php if (!empty($product['has_discount'])): ?>
                <span class="price-old"><?= formatPrice($product['original_price']) ?></span>
                <span class="price-save">Tiết kiệm <?= formatPrice($product['original_price'] - $product['final_price']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Ưu đãi COD -->
        <div class="product-detail__promo-box">
            <div class="promo-box__title"><i class="fa-solid fa-gift"></i> Ưu đãi dành riêng cho bạn</div>
            <div class="promo-box__content">
                <span><i class="fa-solid fa-check" style="color: var(--success);"></i> Mua hàng tại TechPilot — Nhận hàng, kiểm tra rồi thanh toán (COD toàn quốc).</span>
            </div>
        </div>

        <!-- Biểu mẫu Mua hàng / Thêm giỏ hàng -->
        <form method="post" action="<?= url('cart/add') ?>" id="purchaseForm" class="product-card__add-form">
            <?= csrf_field() ?>
            <input type="hidden" name="return_url" value="<?= e($_SERVER['REQUEST_URI'] ?? '/') ?>">
            <input type="hidden" name="intent" value="add" id="purchaseIntent">
            <input type="hidden" name="product_id" value="<?= (int)($product['id'] ?? 0) ?>">
            <div class="product-detail__actions">
                <div class="qty-selector">
                    <button type="button" class="qty-btn" id="qtyDecBtn" onclick="adjustQty(-1)" disabled>-</button>
                    <input type="number" name="quantity" value="1" min="1" max="<?= (int)$product['stock'] ?>" id="qtyInput" readonly>
                    <button type="button" class="qty-btn" id="qtyIncBtn" onclick="adjustQty(1)" <?= ((int)$product['stock'] <= 1) ? 'disabled' : '' ?>>+</button>
                </div>
                <button type="submit" class="btn btn--outline"><i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ</button>
                <button type="button" class="btn" onclick="buyNowSubmit()"><i class="fa-solid fa-bolt"></i> Mua ngay</button>
                <button type="button" class="btn btn--light" onclick="toggleWishlist(<?= (int)($product['id'] ?? 0) ?>, this)" title="Thêm vào danh sách yêu thích" style="padding: 0 16px; border-radius: var(--radius-elem); display: flex; align-items: center; justify-content: center;"><i class="fa-regular fa-heart" style="font-size: 18px;"></i></button>
                <a href="<?= url('compare?add=' . (int)($product['id'] ?? 0)) ?>" class="btn btn--light" title="Thêm vào danh sách so sánh sản phẩm" style="padding: 0 16px; border-radius: var(--radius-elem); display: flex; align-items: center; justify-content: center; color: #10B981; text-decoration: none;"><i class="fa-solid fa-scale-balanced" style="font-size: 18px;"></i></a>
            </div>
        </form>

        <div class="product-detail__perks">
            <div><i class="fa-solid fa-truck-fast"></i> Miễn phí giao hàng toàn quốc</div>
            <div><i class="fa-solid fa-shield-heart"></i> Bảo hành chính hãng <?= (int)($product['warranty_months'] ?? 36) ?> tháng</div>
            <div><i class="fa-solid fa-rotate-left"></i> Đổi trả dễ dàng trong 7 ngày đầu</div>
            <div><i class="fa-solid fa-money-bill-wave"></i> Thanh toán khi nhận hàng (COD)</div>
        </div>
    </div>
</section>

<!-- ===== TABS: MÔ TẢ / THÔNG SỐ / ĐÁNH GIÁ ===== -->
<section class="container product-tabs">
    <div class="product-tabs__nav">
        <button class="product-tabs__btn is-active" onclick="switchProdTab('tab-desc', this)">Mô tả sản phẩm</button>
        <button class="product-tabs__btn" onclick="switchProdTab('tab-specs', this)">Thông số kỹ thuật</button>
        <button class="product-tabs__btn" onclick="switchProdTab('tab-ai-chat', this)"><i class="fa-solid fa-wand-magic-sparkles" style="color: var(--primary); margin-right: 4px;"></i> Hỏi Trợ lý AI</button>
        <button class="product-tabs__btn" onclick="switchProdTab('tab-reviews', this)">Đánh giá (<?= count($reviews) ?>)</button>
    </div>

    <!-- Accordion Trigger 1 (Mô tả sản phẩm) -->
    <button type="button" class="accordion-trigger is-active" onclick="toggleMobileAccordion('tab-desc', this)">
        <span>Mô tả sản phẩm</span>
        <i class="fa-solid fa-chevron-up"></i>
    </button>
    <!-- Panel Mô tả -->
    <div class="product-tabs__panel is-active" id="tab-desc">
        <?php 
        $highlights = json_decode($product['highlights'] ?? '[]', true) ?: [];
        $limitations = json_decode($product['limitations'] ?? '[]', true) ?: [];
        ?>

        <?php if (!empty($highlights)): ?>
            <div style="background-color: rgba(10, 91, 255, 0.04); border: 1px solid rgba(10, 91, 255, 0.15); border-radius: 10px; padding: 16px 20px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; color: var(--primary, #0A5BFF); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-circle-check"></i> Đặc điểm nổi bật
                </h4>
                <ul style="margin: 0; padding-left: 20px; color: var(--text-primary); font-size: 13.5px;">
                    <?php foreach ($highlights as $hl): ?>
                        <li style="margin-bottom: 4px;"><?= e($hl) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($limitations)): ?>
            <div style="background-color: rgba(245, 158, 11, 0.05); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 10px; padding: 16px 20px; margin-bottom: 20px;">
                <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 700; color: #D97706; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Điểm cần cân nhắc (Hạn chế)
                </h4>
                <ul style="margin: 0; padding-left: 20px; color: var(--text-primary); font-size: 13.5px;">
                    <?php foreach ($limitations as $lim): ?>
                        <li style="margin-bottom: 4px;"><?= e($lim) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div style="line-height: 1.8; color: var(--text-primary);">
            <?php 
            $desc = $product['description'] ?? 'Đang cập nhật thông tin chi tiết.';
            $descClean = html_entity_decode($desc, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $hasHtml = $descClean !== strip_tags($descClean);
            ?>
            <?= $hasHtml ? $descClean : nl2br(e($descClean)) ?>
        </div>
    </div>

    <!-- Accordion Trigger 2 (Thông số kỹ thuật) -->
    <button type="button" class="accordion-trigger" onclick="toggleMobileAccordion('tab-specs', this)">
        <span>Thông số kỹ thuật</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <!-- Panel Thông số -->
    <div class="product-tabs__panel" id="tab-specs">
        <?php 
        $categorySlug = $product['category_slug'] ?? '';
        require ROOT_PATH . '/app/views/product/partials/specifications.php';
        ?>

        <?php 
        // Hiệu năng chơi game ước tính (FPS) CHỈ hiển thị cho Laptop và PC nguyên bộ có đủ CPU + GPU + RAM
        if (in_array($categorySlug, ['laptop', 'pc'], true)):
            $fpsList = ProductIntelligenceService::estimateFps($specs, $categorySlug);
            if (!empty($fpsList)): 
        ?>
                <div style="margin-top: 25px; border-top: 1px dashed var(--border); padding-top: 20px;">
                    <h4 style="font-weight: 700; margin: 0 0 15px 0; font-size: 15px; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-gamepad" style="color: var(--primary);"></i> Hiệu năng chơi game ước tính (FPS)
                    </h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
                        <?php foreach ($fpsList as $game): ?>
                            <div style="background-color: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; padding: 12px 16px; display: flex; flex-direction: column; gap: 4px;">
                                <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-secondary);"><?= $game['name'] ?></span>
                                <strong style="font-size: 15px; color: #1E3A8A;"><?= $game['fps'] ?></strong>
                                <span style="font-size: 11px; color: #10B981; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> <?= $game['status'] ?> (<?= $game['settings'] ?>)</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
        <?php 
            endif;
        endif; 
        ?>
    </div>

    <!-- Accordion Trigger 2.5 (Hỏi Trợ lý AI) -->
    <button type="button" class="accordion-trigger" onclick="toggleMobileAccordion('tab-ai-chat', this)">
        <span>Hỏi Trợ lý AI</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <!-- Panel AI Chat -->
    <div class="product-tabs__panel" id="tab-ai-chat">
        <div style="background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 25px; box-shadow: var(--shadow-card); box-sizing: border-box;">
            <h3 style="font-weight: 700; margin: 0 0 10px 0; font-size: 16px; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-robot" style="color: var(--primary);"></i> Hỏi đáp AI về <?= e($product['name']) ?>
            </h3>
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 20px;">Trợ lý AI sẽ phân tích cấu hình phần cứng thực tế và trả lời ngay thắc mắc của bạn (Ví dụ: chạy mượt Photoshop không, chơi Liên Minh bao nhiêu FPS, nâng cấp ổ cứng thế nào...).</p>

            <!-- Notice Banner Container (Guest Reminder) -->
            <div id="aiGuestNoticeContainer"></div>

            <!-- Khung Chat -->
            <div id="aiProductChatMessages" style="border: 1px solid var(--border); border-radius: 12px; padding: 18px; min-height: 350px; max-height: 500px; overflow-y: auto; background-color: #F8FAFC; display: flex; flex-direction: column; gap: 14px; margin-bottom: 15px; box-sizing: border-box;">
                <!-- Tin nhắn chào mừng -->
                <div style="display: flex; gap: 10px; align-self: flex-start;">
                    <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--primary); display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 12px; overflow: hidden; flex-shrink:0;">
                        <img src="<?= url('assets/images/chatbot-avatar.png') ? alt="TechPilot Asset">" alt="AI" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="background-color: #FFFFFF; border: 1px solid var(--border); border-radius: 12px; padding: 10px 14px; font-size: 13px; max-width: 80%; line-height: 1.5; color: var(--text-primary);">
                        Xin chào! Tôi có thể giải đáp mọi thắc mắc về mẫu sản phẩm <strong><?= e($product['name']) ?></strong> này. Bạn hãy đặt câu hỏi bên dưới nhé!
                    </div>
                </div>
            </div>

            <!-- Ô nhập câu hỏi -->
            <div style="display: flex; gap: 10px;">
                <input type="text" id="aiProductChatInput" placeholder="Hỏi AI: Máy này chơi game ổn không? Nâng cấp RAM được không?..." style="flex: 1; padding: 12px 16px; border: 1px solid var(--border); border-radius: 8px; font-size: 13.5px; outline: none; background-color: var(--bg-white); color: var(--text-primary); box-sizing: border-box;" onkeydown="handleProductChatKey(event)">
                <button type="button" class="btn" style="padding: 0 24px; font-weight: 700; height: auto;" onclick="sendProductChatMessage()">Gửi</button>
            </div>
        </div>
    </div>

    <!-- Accordion Trigger 3 (Đánh giá) -->
    <button type="button" class="accordion-trigger" onclick="toggleMobileAccordion('tab-reviews', this)">
        <span>Đánh giá (<?= count($reviews) ?>)</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <!-- Panel Đánh giá -->
    <div class="product-tabs__panel" id="tab-reviews">
        <?php if (!empty($_SESSION['flashes'])): ?>
            <?php foreach (pullFlashes() as $f): ?>
                <div class="alert alert--<?= e($f['type']) ?>" style="margin-bottom: 20px; padding: 12px 16px; border-radius: 6px; font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 10px; background-color: <?= $f['type'] === 'success' ? '#D1FAE5' : '#FEE2E2' ?>; color: <?= $f['type'] === 'success' ? '#065F46' : '#991B1B' ?>; border: 1px solid <?= $f['type'] === 'success' ? '#10B981' : '#F87171' ?>;">
                    <i class="fa-solid <?= $f['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
                    <span><?= e($f['message']) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($canReview): ?>
            <div class="write-review-card" style="background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: var(--shadow-card);">
                <h3 style="font-weight: 700; margin: 0 0 15px 0; font-size: 16px; color: var(--text-primary);">Viết đánh giá của bạn</h3>
                <form method="post" action="<?= url('product/review') ?>" style="display: flex; flex-direction: column; gap: 15px;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= (int)($product['id'] ?? 0) ?>">
                    
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13.5px; color: var(--text-primary);">Số sao đánh giá</label>
                        <select name="rating" style="padding: 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 14px; background-color: var(--bg-white); color: var(--text-primary); width: 160px; font-weight: 600;">
                            <option value="5">⭐⭐⭐⭐⭐ 5 Sao</option>
                            <option value="4">⭐⭐⭐⭐ 4 Sao</option>
                            <option value="3">⭐⭐⭐ 3 Sao</option>
                            <option value="2">⭐⭐ 2 Sao</option>
                            <option value="1">⭐ 1 Sao</option>
                        </select>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <label style="font-weight: 700; font-size: 13.5px; color: var(--text-primary);">Nội dung bình luận</label>
                        <textarea name="comment" rows="4" required placeholder="Chia sẻ trải nghiệm chân thực của bạn về sản phẩm này để giúp những người mua sau nhé..." style="padding: 12px 16px; border: 1px solid var(--border); border-radius: 6px; font-size: 14px; width: 100%; resize: vertical; color: var(--text-primary); background-color: var(--bg-white);"></textarea>
                    </div>

                    <button type="submit" class="btn" style="align-self: flex-start; padding: 10px 25px; font-weight: 600;">Gửi đánh giá</button>
                </form>
            </div>
        <?php elseif (empty($_SESSION['user']['id'])): ?>
            <div style="background: linear-gradient(135deg, #EFF6FF 0%, #F0F9FF 100%); border: 1px solid #BFDBFE; border-radius: 12px; padding: 24px; margin-bottom: 30px; text-align: center;">
                <i class="fa-solid fa-user-lock" style="font-size: 28px; color: #3B82F6; margin-bottom: 10px; display: block;"></i>
                <p style="font-weight: 700; font-size: 15px; color: #1E40AF; margin: 0 0 6px 0;">Đăng nhập để đánh giá sản phẩm</p>
                <p style="font-size: 13px; color: #64748B; margin: 0 0 14px 0;">Bạn cần đăng nhập và đã mua sản phẩm này thành công để có thể viết đánh giá.</p>
                <a href="<?= url('auth/login') ?>" class="btn" style="padding: 8px 24px; font-weight: 700; font-size: 13px; text-decoration: none;"><i class="fa-solid fa-right-to-bracket" style="margin-right: 6px;"></i>Đăng nhập ngay</a>
            </div>
        <?php else: ?>
            <div style="background: linear-gradient(135deg, #FFF7ED 0%, #FFFBEB 100%); border: 1px solid #FED7AA; border-radius: 12px; padding: 24px; margin-bottom: 30px; text-align: center;">
                <i class="fa-solid fa-bag-shopping" style="font-size: 28px; color: #F59E0B; margin-bottom: 10px; display: block;"></i>
                <p style="font-weight: 700; font-size: 15px; color: #92400E; margin: 0 0 6px 0;">Chỉ khách hàng đã mua mới được đánh giá</p>
                <p style="font-size: 13px; color: #64748B; margin: 0;">Bạn cần mua và nhận sản phẩm này thành công trước khi có thể để lại đánh giá. Điều này giúp đảm bảo tính xác thực của các nhận xét trên TechPilot.</p>
            </div>
        <?php endif; ?>

        <div class="review-grid">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="review-card" style="margin-bottom: 16px; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-card);">
                        <div class="review-card__head" style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                            <i class="fa-solid fa-circle-user" style="font-size: 32px; color: var(--text-secondary);"></i>
                            <div>
                                <strong style="display: block; font-size: 14.5px; color: var(--text-primary);"><?= e($rev['reviewer_name']) ?></strong>
                                <span style="font-size: 11.5px; color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($rev['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="stars" style="margin-bottom: 10px;">
                            <?= renderStars((float)$rev['rating']) ?>
                        </div>
                        <p style="color: var(--text-primary); font-size: 14px; line-height: 1.6; margin: 0;"><?= e($rev['comment']) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: var(--text-secondary); font-size: 14px; padding: 20px 0;">Chưa có đánh giá nào cho sản phẩm này. Hãy mua hàng và để lại ý kiến đầu tiên!</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ===== SẢN PHẨM LIÊN QUAN ===== -->
<?php if (!empty($related)): ?>
    <section class="section container">
        <div class="section__head">
            <h2>Sản phẩm liên quan</h2>
        </div>
        <div class="section-slider-wrapper">
            <button type="button" class="product-slider-arrow prev" aria-label="Sản phẩm trước"><i class="fa-solid fa-chevron-left"></i></button>
            <button type="button" class="product-slider-arrow next" aria-label="Sản phẩm tiếp"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="product-slider-track">
                <?php foreach ($related as $p): ?>
                    <div>
                        <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<script>
    function changeProductImage(src, element) {
        document.getElementById('mainProdImage').src = src;
        const thumbs = document.querySelectorAll('.product-detail__thumb');
        thumbs.forEach(t => t.classList.remove('is-active'));
        element.classList.add('is-active');
    }

    function adjustQty(amount) {
        const input = document.getElementById('qtyInput');
        if (!input) return;
        let val = (parseInt(input.value) || 1) + amount;
        const max = parseInt(input.getAttribute('max')) || 100;
        if (val < 1) val = 1;
        if (val > max) val = max;
        input.value = val;

        const decBtn = document.getElementById('qtyDecBtn');
        const incBtn = document.getElementById('qtyIncBtn');
        if (decBtn) decBtn.disabled = (val <= 1);
        if (incBtn) incBtn.disabled = (val >= max);
    }

    function buyNowSubmit() {
        const form = document.getElementById('purchaseForm');
        const intentInput = document.getElementById('purchaseIntent');
        if (intentInput) {
            intentInput.value = 'buy_now';
        } else {
            form.action = "<?= url('cart/add') ?>?buynow=1";
        }
        form.submit();
    }

    function mobileAddToCart() {
        const form = document.getElementById('purchaseForm');
        const intentInput = document.getElementById('purchaseIntent');
        if (intentInput) {
            intentInput.value = 'add';
        }
        
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            // Fallback for older browsers
            form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }
    }

    function switchProdTab(tabId, btn) {
        const panels = document.querySelectorAll('.product-tabs__panel');
        panels.forEach(p => p.classList.remove('is-active'));
        document.getElementById(tabId).classList.add('is-active');

        const tabBtns = document.querySelectorAll('.product-tabs__btn');
        tabBtns.forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
    }

    function toggleMobileAccordion(panelId, btn) {
        if (window.innerWidth > 575) return;
        
        const panel = document.getElementById(panelId);
        const icon = btn.querySelector('i');
        
        if (panel.classList.contains('is-active')) {
            panel.classList.remove('is-active');
            btn.classList.remove('is-active');
            icon.className = 'fa-solid fa-chevron-down';
        } else {
            panel.classList.add('is-active');
            btn.classList.add('is-active');
            icon.className = 'fa-solid fa-chevron-up';
        }
    }

    const CURRENT_PRODUCT_ID = <?= (int)$product['id'] ?>;
    const IS_USER_LOGGED_IN = <?= isset($_SESSION['user']['id']) ? 'true' : 'false' ?>;
    const CSRF_TOKEN = '<?= $_SESSION["csrf_token"] ?? "" ?>';
    const GUEST_STORAGE_KEY = 'techpilot_chat_product_' + CURRENT_PRODUCT_ID;

    // Lấy mảng tin nhắn guest từ sessionStorage (tự xóa nếu quá 24h)
    function getGuestStorageMessages() {
        try {
            const raw = sessionStorage.getItem(GUEST_STORAGE_KEY);
            if (!raw) return [];
            const data = JSON.parse(raw);
            if (data.saved_at && (Date.now() - data.saved_at > 86400000)) { // >24h
                sessionStorage.removeItem(GUEST_STORAGE_KEY);
                return [];
            }
            return Array.isArray(data.messages) ? data.messages : [];
        } catch (e) {
            return [];
        }
    }

    // Lưu mảng tin nhắn guest vào sessionStorage
    function saveGuestStorageMessages(messages) {
        try {
            sessionStorage.setItem(GUEST_STORAGE_KEY, JSON.stringify({
                saved_at: Date.now(),
                messages: messages
            }));
        } catch (e) {}
    }

    // Kiểm tra & hiển thị banner nhắc đăng nhập cho Guest khi chat >= 4 tin nhắn
    function checkGuestNoticeBanner(messageCount) {
        if (IS_USER_LOGGED_IN) return;
        const container = document.getElementById('aiGuestNoticeContainer');
        if (!container) return;

        if (messageCount >= 4) {
            const redirectUrl = encodeURIComponent(window.location.pathname + window.location.search);
            container.innerHTML = `
                <div style="background: linear-gradient(135deg, #EFF6FF 0%, #F0F9FF 100%); border: 1px solid #BFDBFE; color: #1E40AF; border-radius: 10px; padding: 10px 14px; font-size: 12.5px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                    <span>💡 <strong>Mẹo:</strong> Bạn đang dùng chế độ Khách. Đăng nhập để tự động lưu lại toàn bộ tư vấn này và xem lại trong tài khoản nhé!</span>
                    <a href="<?= url('auth/login') ?>?redirect=${redirectUrl}" class="btn btn--outline" style="padding: 4px 12px; font-size: 12px; white-space: nowrap; text-decoration: none; border-color: #3B82F6; color: #2563EB; font-weight: 700;">Đăng nhập ngay</a>
                </div>
            `;
        }
    }

    // Append tin nhắn vào khung chat UI
    function appendChatMessage(role, messageText) {
        const msgBox = document.getElementById('aiProductChatMessages');
        if (!msgBox) return;

        if (role === 'user') {
            const userMsgHtml = `
                <div style="display: flex; gap: 10px; align-self: flex-end; flex-direction: row-reverse;">
                    <div style="background-color: var(--primary); color: #FFFFFF; border-radius: 12px; padding: 10px 14px; font-size: 13px; max-width: 80%; line-height: 1.5;">
                        ${escapeHtml(messageText)}
                    </div>
                </div>
            `;
            msgBox.insertAdjacentHTML('beforeend', userMsgHtml);
        } else {
            const answerHtml = `
                <div style="display: flex; gap: 10px; align-self: flex-start;">
                    <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--primary); display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 12px; overflow: hidden; flex-shrink:0;">
                        <img src="<?= url('assets/images/chatbot-avatar.png') ? alt="TechPilot Asset">" alt="AI" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="background-color: #FFFFFF; border: 1px solid var(--border); border-radius: 12px; padding: 10px 14px; font-size: 13px; max-width: 80%; line-height: 1.5; color: var(--text-primary);">
                        ${formatMarkdownText(messageText)}
                    </div>
                </div>
            `;
            msgBox.insertAdjacentHTML('beforeend', answerHtml);
        }
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    // Khởi tạo Lịch sử Chat trên Page Load
    document.addEventListener('DOMContentLoaded', function() {
        if (IS_USER_LOGGED_IN) {
            // Luồng User đã đăng nhập:
            // 1. Kiểm tra nếu có tin nhắn guest cũ trong sessionStorage -> sync lên DB trước
            const guestMsgs = getGuestStorageMessages();
            if (guestMsgs.length > 0) {
                const payload = guestMsgs.map(m => ({
                    product_id: CURRENT_PRODUCT_ID,
                    role: m.role,
                    message: m.text || m.message,
                    timestamp: m.timestamp || Date.now()
                }));

                fetch('<?= url("product/ai-chat-sync-guest") ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ messages: payload, _csrf: CSRF_TOKEN })
                })
                .then(res => res.json())
                .then(res => {
                    sessionStorage.removeItem(GUEST_STORAGE_KEY);
                    loadDbChatHistory();
                })
                .catch(err => {
                    loadDbChatHistory();
                });
            } else {
                loadDbChatHistory();
            }
        } else {
            // Luồng Guest: Load từ sessionStorage và render
            const guestMsgs = getGuestStorageMessages();
            if (guestMsgs.length > 0) {
                guestMsgs.forEach(m => {
                    appendChatMessage(m.role, m.text || m.message);
                });
            }
            checkGuestNoticeBanner(guestMsgs.length);
        }
    });

    function loadDbChatHistory() {
        fetch('<?= url("product/ai-chat-history") ?>?product_id=' + CURRENT_PRODUCT_ID)
            .then(res => res.json())
            .then(res => {
                if (res.success && Array.isArray(res.history) && res.history.length > 0) {
                    res.history.forEach(h => {
                        appendChatMessage(h.role, h.message);
                    });
                }
            })
            .catch(err => console.error('Error loading DB chat history:', err));
    }

    function handleProductChatKey(e) {
        if (e.key === 'Enter') sendProductChatMessage();
    }

    function sendProductChatMessage() {
        const input = document.getElementById('aiProductChatInput');
        const text = input.value.trim();
        if (text === '') return;

        input.value = '';

        // Append User Message to UI
        appendChatMessage('user', text);

        // Lưu vào sessionStorage nếu là Guest
        let guestMsgs = [];
        if (!IS_USER_LOGGED_IN) {
            guestMsgs = getGuestStorageMessages();
            guestMsgs.push({ role: 'user', text: text, timestamp: Date.now() });
            saveGuestStorageMessages(guestMsgs);
            checkGuestNoticeBanner(guestMsgs.length);
        }

        const msgBox = document.getElementById('aiProductChatMessages');

        // Render typing indicator
        const typingId = 'typing-' + Date.now();
        const typingHtml = `
            <div id="${typingId}" style="display: flex; gap: 10px; align-self: flex-start;">
                <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--primary); display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 12px; overflow: hidden; flex-shrink:0;">
                    <img src="<?= url('assets/images/chatbot-avatar.png') ? alt="TechPilot Asset">" alt="AI" style="width:100%; height:100%; object-fit:cover;">
                </div>
                <div style="background-color: #FFFFFF; border: 1px solid var(--border); border-radius: 12px; padding: 10px 14px; font-size: 13px; max-width: 80%; line-height: 1.5; color: var(--text-primary);">
                    <i class="fa-solid fa-circle-notch fa-spin"></i> Đang phân tích...
                </div>
            </div>
        `;
        msgBox.insertAdjacentHTML('beforeend', typingHtml);
        msgBox.scrollTop = msgBox.scrollHeight;

        // AJAX request
        const data = new URLSearchParams();
        data.append('product_id', CURRENT_PRODUCT_ID);
        data.append('q', text);
        data.append('_csrf', CSRF_TOKEN);

        fetch('<?= url("product/ai-chat") ?>', {
            method: 'POST',
            body: data
        })
        .then(res => res.json())
        .then(res => {
            document.getElementById(typingId)?.remove();

            if (res.success) {
                appendChatMessage('assistant', res.answer);

                if (!IS_USER_LOGGED_IN) {
                    guestMsgs = getGuestStorageMessages();
                    guestMsgs.push({ role: 'assistant', text: res.answer, timestamp: Date.now() });
                    saveGuestStorageMessages(guestMsgs);
                    checkGuestNoticeBanner(guestMsgs.length);
                }
            } else {
                const errHtml = `
                    <div style="display: flex; gap: 10px; align-self: flex-start;">
                        <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--primary); display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 12px; overflow: hidden; flex-shrink:0;">
                            <img src="<?= url('assets/images/chatbot-avatar.png') ? alt="TechPilot Asset">" alt="AI" style="width:100%; height:100%; object-fit:cover;">
                        </div>
                        <div style="background-color: #FFFFFF; border: 1px solid var(--border); border-radius: 12px; padding: 10px 14px; font-size: 13px; max-width: 80%; line-height: 1.5; color: #EF4444;">
                            Lỗi: ${escapeHtml(res.message)}
                        </div>
                    </div>
                `;
                msgBox.insertAdjacentHTML('beforeend', errHtml);
                msgBox.scrollTop = msgBox.scrollHeight;
            }
        })
        .catch(err => {
            document.getElementById(typingId)?.remove();
            const errHtml = `
                <div style="display: flex; gap: 10px; align-self: flex-start;">
                    <div style="width: 30px; height: 30px; border-radius: 50%; background-color: var(--primary); display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 12px; overflow: hidden; flex-shrink:0;">
                        <img src="<?= url('assets/images/chatbot-avatar.png') ? alt="TechPilot Asset">" alt="AI" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                    <div style="background-color: #FFFFFF; border: 1px solid var(--border); border-radius: 12px; padding: 10px 14px; font-size: 13px; max-width: 80%; line-height: 1.5; color: #EF4444;">
                        Lỗi kết nối mạng. Không thể gửi tin nhắn.
                    </div>
                </div>
            `;
            msgBox.insertAdjacentHTML('beforeend', errHtml);
            msgBox.scrollTop = msgBox.scrollHeight;
        });
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatMarkdownText(text) {
        return text
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/• (.*?)(<br>|$)/g, '<li style="margin-left: 15px; margin-bottom: 4px;">$1</li>');
    }

    // Near-realtime Stock Polling cho trang Chi tiết sản phẩm
    (function() {
        const productId = <?= (int)($product['id'] ?? 0) ?>;
        if (!productId) return;

        function checkLiveStock() {
            fetch('<?= url("api/inventory/product/") ?>' + productId)
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const stock = Number(res.data.stock || 0);
                        const purchasable = res.data.purchasable;
                        const qtyInput = document.getElementById('qtyInput');
                        const qtyIncBtn = document.getElementById('qtyIncBtn');
                        const buyBtns = document.querySelectorAll('#purchaseForm button[type="submit"], #purchaseForm button[onclick*="buyNowSubmit"]');

                        if (qtyInput) {
                            qtyInput.max = stock;
                            if (Number(qtyInput.value) > stock) {
                                qtyInput.value = Math.max(1, stock);
                            }
                        }

                        if (!purchasable || stock <= 0) {
                            if (qtyIncBtn) qtyIncBtn.disabled = true;
                            buyBtns.forEach(btn => {
                                btn.disabled = true;
                                btn.style.opacity = '0.5';
                                btn.style.cursor = 'not-allowed';
                                if (btn.innerText.includes('Thêm') || btn.innerText.includes('Mua')) {
                                    btn.innerHTML = '<i class="fa-solid fa-ban"></i> Hết hàng';
                                }
                            });
                        }
                    }
                })
                .catch(err => console.debug('Stock poll paused:', err));
        }

        setInterval(checkLiveStock, 20000);
    })();

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.section-slider-wrapper').forEach(function(wrapper) {
            const track = wrapper.querySelector('.product-slider-track');
            const prevBtn = wrapper.querySelector('.product-slider-arrow.prev');
            const nextBtn = wrapper.querySelector('.product-slider-arrow.next');
            if (!track) return;

            let autoPlayTimer = null;
            function getCardScrollWidth() {
                const firstCard = track.children[0];
                if (!firstCard) return 220;
                return firstCard.getBoundingClientRect().width + 16;
            }

            function scrollNext() {
                if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 10) {
                    track.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    track.scrollBy({ left: getCardScrollWidth(), behavior: 'smooth' });
                }
            }

            function scrollPrev() {
                if (track.scrollLeft <= 10) {
                    track.scrollTo({ left: track.scrollWidth, behavior: 'smooth' });
                } else {
                    track.scrollBy({ left: -getCardScrollWidth(), behavior: 'smooth' });
                }
            }

            if (nextBtn) nextBtn.addEventListener('click', function(e) { e.preventDefault(); scrollNext(); });
            if (prevBtn) prevBtn.addEventListener('click', function(e) { e.preventDefault(); scrollPrev(); });

            autoPlayTimer = setInterval(scrollNext, 4000);
            track.addEventListener('mouseenter', function() { if (autoPlayTimer) clearInterval(autoPlayTimer); });
            track.addEventListener('mouseleave', function() { autoPlayTimer = setInterval(scrollNext, 4000); });
        });
    });
</script>
