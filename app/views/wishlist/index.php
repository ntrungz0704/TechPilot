<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<main class="container section" id="main-content" style="margin-top: 40px; min-height: 60vh;">
    <div class="section__head" style="margin-bottom: 30px;">
        <h2><i class="fa-solid fa-heart" style="color: #EF4444; margin-right: 8px;"></i> Sản phẩm yêu thích của bạn</h2>
    </div>

    <?php if (isset($flashes['success'])): ?>
        <div class="alert alert--success" style="margin-bottom: 20px; padding: 12px; background-color: #DEF7EC; color: #03543F; border-radius: 8px;">
            <?= e($flashes['success']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($flashes['error'])): ?>
        <div class="alert alert--danger" style="margin-bottom: 20px; padding: 12px; background-color: #FDE8E8; color: #9B1C1C; border-radius: 8px;">
            <?= e($flashes['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
        <div style="text-align: center; padding: 60px 20px; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px;">
            <i class="fa-regular fa-heart" style="font-size: 64px; color: var(--text-secondary); margin-bottom: 20px; display: block;"></i>
            <h3 style="margin-bottom: 10px; font-weight: 700;">Danh sách yêu thích trống</h3>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">Hãy duyệt qua trang chủ để thêm các sản phẩm yêu thích của bạn.</p>
            <a href="<?= url('/') ?>" class="btn" style="padding: 10px 24px;">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <div class="product-grid product-grid--6">
            <?php foreach ($items as $p): ?>
                <div class="product-card" style="position: relative; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; padding: 15px; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s ease;">
                    
                    <!-- Nút xóa nhanh -->
                    <form method="post" action="<?= url('wishlist/remove') ?>" style="position: absolute; top: 12px; right: 12px; z-index: 10;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                        <button type="submit" style="background: rgba(255,255,255,0.9); border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #EF4444; box-shadow: 0 2px 6px rgba(0,0,0,0.1); transition: background-color 0.2s;" title="Xóa khỏi yêu thích">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>

                    <a href="<?= url('product/detail/' . e($p['slug'])) ?>" style="text-decoration: none; color: inherit; display: block;">
                        <div class="product-card__image-wrapper" style="height: 160px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                            <img src="<?= url('assets/images/' . e($p['image'])) ?>" alt="<?= e($p['name']) ?>" style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        </div>
                        <div class="product-card__info">
                            <span class="product-card__brand" style="font-size: 11px; text-transform: uppercase; color: var(--primary); font-weight: 700; display: block; margin-bottom: 4px;"><?= e($p['brand_name']) ?></span>
                            <h3 class="product-card__title" style="font-size: 14.5px; font-weight: 600; line-height: 1.4; height: 40px; overflow: hidden; margin-bottom: 10px;"><?= e($p['name']) ?></h3>
                            <div class="product-card__price-row" style="margin-bottom: 15px;">
                                <span class="product-card__price" style="font-size: 16px; font-weight: 700; color: var(--primary);"><?= number_format($p['price'], 0, ',', '.') ?>đ</span>
                                <?php if (!empty($p['old_price'])): ?>
                                    <span class="product-card__old-price" style="font-size: 13px; text-decoration: line-through; color: var(--text-secondary); margin-left: 8px;"><?= number_format($p['old_price'], 0, ',', '.') ?>đ</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>

                    <div style="display: flex; gap: 8px;">
                        <!-- Nút mua ngay -->
                        <form method="post" action="<?= url('cart/add?buynow=1') ?>" style="flex: 1;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn--sm" style="width: 100%; padding: 8px 0; font-size: 12px; font-weight: 600; text-align: center;">Mua Ngay</button>
                        </form>
                        <!-- Nút thêm giỏ -->
                        <form method="post" action="<?= url('cart/add') ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn--light btn--sm" style="padding: 8px 12px;" title="Thêm vào giỏ"><i class="fa-solid fa-cart-plus"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
