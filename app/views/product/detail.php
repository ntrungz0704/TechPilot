<?php
$product = $product ?? [];
$specs = $specs ?? [];
$related = $related ?? [];
$productImages = $productImages ?? [];
$reviews = $reviews ?? [];
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
            <?php if (!empty($product['discount_percent']) && $product['discount_percent'] > 0): ?>
                <span class="product-card__badge">-<?= (int)$product['discount_percent'] ?>%</span>
            <?php endif; ?>
            <img src="<?= e(productImageUrl($product['image'] ?? '')) ?>" alt="<?= e($product['name']) ?>" class="product-detail__main-image-src" id="mainProdImage">
        </div>
        <div class="product-detail__thumbs">
            <!-- Thêm ảnh đại diện gốc vào danh sách thumbnail -->
            <div class="product-detail__thumb is-active" onclick="changeProductImage('<?= e(productImageUrl($product['image'] ?? '')) ?>', this)">
                <img src="<?= e(productImageUrl($product['image'] ?? '')) ?>" alt="<?= e($product['name']) ?>" class="product-detail__thumb-image">
            </div>
            <!-- Hiển thị các ảnh chi tiết nếu có -->
            <?php foreach ($productImages as $img): ?>
                <div class="product-detail__thumb" onclick="changeProductImage('<?= e(productImageUrl($img['image_url'])) ?>', this)">
                    <img src="<?= e(productImageUrl($img['image_url'])) ?>" alt="Detail" class="product-detail__thumb-image">
                </div>
            <?php endforeach; ?>
            <!-- Fallback điền thêm thumbnail cho đầy giao diện -->
            <?php if (count($productImages) < 3): ?>
                <?php for ($i = 0; $i < (3 - count($productImages)); $i++): ?>
                    <div class="product-detail__thumb" onclick="changeProductImage('<?= e(productImageUrl($product['image'] ?? '')) ?>', this)">
                        <img src="<?= e(productImageUrl($product['image'] ?? '')) ?>" alt="Detail" class="product-detail__thumb-image">
                    </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
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
        </div>

        <p class="product-detail__short-desc"><?= e($product['short_desc'] ?? 'Đang cập nhật mô tả ngắn cho sản phẩm này.') ?></p>

        <div class="product-detail__price">
            <span class="price-now"><?= formatPrice($product['price']) ?></span>
            <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                <span class="price-old"><?= formatPrice($product['old_price']) ?></span>
                <span class="price-save">Tiết kiệm <?= formatPrice($product['old_price'] - $product['price']) ?></span>
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
        <form method="post" action="<?= url('cart/add') ?>" id="purchaseForm">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int)($product['id'] ?? 0) ?>">
            <div class="product-detail__actions">
                <div class="qty-selector">
                    <button type="button" class="qty-btn" onclick="adjustQty(-1)">-</button>
                    <input type="number" name="quantity" value="1" min="1" max="<?= (int)$product['stock'] ?>" id="qtyInput" readonly>
                    <button type="button" class="qty-btn" onclick="adjustQty(1)">+</button>
                </div>
                <button type="submit" class="btn btn--outline"><i class="fa-solid fa-cart-plus"></i> Thêm vào giỏ</button>
                <button type="button" class="btn" onclick="buyNowSubmit()"><i class="fa-solid fa-bolt"></i> Mua ngay</button>
            </div>
        </form>

        <div class="product-detail__perks">
            <div><i class="fa-solid fa-truck-fast"></i> Miễn phí giao hàng toàn quốc</div>
            <div><i class="fa-solid fa-shield-heart"></i> Bảo hành chính hãng 12 tháng</div>
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
        <button class="product-tabs__btn" onclick="switchProdTab('tab-reviews', this)">Đánh giá (<?= count($reviews) ?>)</button>
    </div>

    <!-- Accordion Trigger 1 (Mô tả sản phẩm) -->
    <button type="button" class="accordion-trigger is-active" onclick="toggleMobileAccordion('tab-desc', this)">
        <span>Mô tả sản phẩm</span>
        <i class="fa-solid fa-chevron-up"></i>
    </button>
    <!-- Panel Mô tả -->
    <div class="product-tabs__panel is-active" id="tab-desc">
        <p><?= nl2br(e($product['description'] ?? 'Đang cập nhật thông tin chi tiết.')) ?></p>
    </div>

    <!-- Accordion Trigger 2 (Thông số kỹ thuật) -->
    <button type="button" class="accordion-trigger" onclick="toggleMobileAccordion('tab-specs', this)">
        <span>Thông số kỹ thuật</span>
        <i class="fa-solid fa-chevron-down"></i>
    </button>
    <!-- Panel Thông số -->
    <div class="product-tabs__panel" id="tab-specs">
        <table class="specs-table">
            <tbody>
                <?php if (!empty($specs)): ?>
                    <?php foreach ($specs as $key => $value): ?>
                        <tr>
                            <th><?= e($key) ?></th>
                            <td><?= e($value) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align: center;">Chưa cập nhật thông số kỹ thuật chi tiết.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
        <div class="product-grid product-grid--6">
            <?php foreach ($related as $p): ?>
                <?php include ROOT_PATH . '/app/views/home/_product_card.php'; ?>
            <?php endforeach; ?>
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
        let val = parseInt(input.value) + amount;
        const max = parseInt(input.getAttribute('max')) || 100;
        if (val < 1) val = 1;
        if (val > max) val = max;
        input.value = val;
    }

    function buyNowSubmit() {
        const form = document.getElementById('purchaseForm');
        // Thay đổi action trỏ sang thanh toán trực tiếp hoặc bổ sung params
        form.action = "<?= url('cart/add') ?>?buynow=1";
        form.submit();
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
</script>