<?php
$cartItems = $cartItems ?? [];
$subtotal = $subtotal ?? 0;
$total = $total ?? 0;
?>

<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <span>Giỏ hàng</span>
</section>

<section class="container cart-page">
    <div class="cart-card">
        <h1>Giỏ hàng của bạn</h1>
        <p class="cart-sub">Kiểm tra các mặt hàng đã chọn trước khi tiến hành thanh toán.</p>

        <?php if (empty($cartItems)): ?>
            <div class="cart-empty">
                <i class="fa-solid fa-cart-shopping"></i>
                <h3>Giỏ hàng hiện đang trống</h3>
                <p>Hãy thêm sản phẩm từ trang chủ hoặc danh mục để bắt đầu.</p>
                <a href="<?= url('/') ?>" class="btn">Tiếp tục mua sắm</a>
            </div>
        <?php else: ?>
            <div class="cart-list">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item__info">
                            <div class="cart-item__thumb">
                                <i class="fa-solid fa-laptop-code"></i>
                            </div>
                            <div>
                                <h3>
                                    <a href="<?= url('product/detail/' . e($item['slug'] ?? '')) ?>" style="color: var(--text-primary); text-decoration: none; transition: var(--transition);" onmouseover="this.style.color='var(--primary)';" onmouseout="this.style.color='var(--text-primary)';">
                                        <?= e($item['name']) ?>
                                    </a>
                                </h3>
                                <p><?= formatPrice($item['price']) ?> / sản phẩm</p>
                            </div>
                        </div>
                        <div class="cart-item__controls">
                            <form method="post" action="<?= url('cart/update') ?>" class="qty-form">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                                <button type="submit" name="quantity" value="<?= max(1, (int)$item['quantity'] - 1) ?>" class="qty-btn">-</button>
                                <span><?= (int)$item['quantity'] ?></span>
                                <button type="submit" name="quantity" value="<?= (int)$item['quantity'] + 1 ?>" class="qty-btn">+</button>
                            </form>
                            <form method="post" action="<?= url('cart/remove') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="product_id" value="<?= (int)$item['product_id'] ?>">
                                <button type="submit" class="btn btn--outline btn--sm" style="box-shadow: none;">Xóa</button>
                            </form>
                        </div>
                        <strong style="font-size: 16px; color: var(--primary);"><?= formatPrice($item['line_total']) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($cartItems)): ?>
        <aside class="cart-summary">
            <h3>Tóm tắt đơn hàng</h3>
            <div class="summary-row"><span>Tạm tính</span><strong><?= formatPrice($subtotal) ?></strong></div>
            <div class="summary-row"><span>Phí vận chuyển</span><strong style="color: var(--success);">Miễn phí</strong></div>
            <div class="summary-row total"><span>Tổng tiền thanh toán</span><strong><?= formatPrice($total) ?></strong></div>
            <a href="<?= url('checkout') ?>" class="btn btn--block" style="margin-top: 20px;">Tiến hành thanh toán <i class="fa-solid fa-credit-card"></i></a>
        </aside>
    <?php endif; ?>
</section>

<style>
    .cart-page {
        display: grid;
        grid-template-columns: 1.6fr 0.8fr;
        gap: 30px;
        margin: 24px auto 60px;
        align-items: start;
    }

    .cart-card,
    .cart-summary {
        background: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        padding: 30px;
    }

    .cart-card h1 {
        font-size: 24px;
        color: var(--text-primary);
        margin-bottom: 6px;
        font-weight: 800;
    }

    .cart-sub {
        color: var(--text-secondary);
        margin-bottom: 24px;
        font-size: 14px;
    }

    .cart-empty {
        text-align: center;
        padding: 60px 20px;
    }

    .cart-empty i {
        font-size: 56px;
        color: var(--primary);
        margin-bottom: 16px;
    }

    .cart-empty h3 {
        margin-bottom: 8px;
        color: var(--text-primary);
        font-weight: 700;
    }

    .cart-empty p {
        color: var(--text-secondary);
        margin-bottom: 24px;
        font-size: 14px;
    }

    .cart-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .cart-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 0;
        border-bottom: 1px solid var(--border);
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .cart-item__info {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1.2;
    }

    .cart-item__thumb {
        width: 64px;
        height: 64px;
        border-radius: var(--radius-elem);
        background-color: var(--secondary);
        display: grid;
        place-items: center;
        color: var(--primary);
        font-size: 24px;
    }

    .cart-item__thumb img { width:100%; height:100%; object-fit:contain; padding:6px; }

    .cart-item__info h3 {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.4;
    }

    .cart-item__info p {
        font-size: 13px;
        color: var(--text-secondary);
        margin-top: 4px;
    }

    .cart-item__controls {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
    }

    .qty-form {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1px solid var(--border);
        border-radius: var(--radius-elem);
        padding: 4px 8px;
        background-color: var(--bg-white);
    }

    .qty-form span {
        font-size: 15px;
        font-weight: 700;
        min-width: 24px;
        text-align: center;
    }

    .qty-form .qty-btn {
        border: none;
        background: var(--secondary);
        width: 28px;
        height: 28px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        transition: var(--transition);
    }

    .qty-form .qty-btn:hover {
        background-color: var(--primary);
        color: #FFFFFF;
    }

    .cart-summary h3 {
        font-size: 18px;
        margin-bottom: 20px;
        color: var(--text-primary);
        font-weight: 800;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        color: var(--text-secondary);
        font-size: 14px;
        border-bottom: 1px solid var(--border);
    }

    .summary-row.total {
        font-size: 16px;
        font-weight: 800;
        color: var(--text-primary);
        border-bottom: none;
        padding-top: 20px;
    }

    .summary-row.total strong {
        color: var(--primary);
        font-size: 20px;
    }

    @media (max-width: 992px) {
        .cart-page {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .cart-item {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }
        .cart-item__controls {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
