<?php
$cartItems = $cartItems ?? [];
$subtotal = $subtotal ?? 0;
$shipping = $shipping ?? 0;
$total = $total ?? 0;
?>

<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <a href="<?= url('cart') ?>">Giỏ hàng</a> <i class="fa-solid fa-chevron-right"></i>
    <span>Thanh toán</span>
</section>

<section class="container checkout-page">
    <div class="checkout-card">
        <h1>Thông tin giao hàng</h1>
        <p class="checkout-sub">Vui lòng nhập đầy đủ địa chỉ nhận hàng để chúng tôi vận chuyển nhanh nhất.</p>

        <?php if (!empty($_SESSION['checkout_error'] ?? '')): ?>
            <div class="alert alert--error">
                <p><i class="fa-solid fa-circle-exclamation"></i> <?= e($_SESSION['checkout_error']) ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url('checkout/submit') ?>" class="checkout-form">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>Họ và tên người nhận</label>
                <input type="text" name="customer_name" required placeholder="Nguyễn Văn A">
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="phone" required placeholder="0909 123 456">
            </div>
            <div class="form-group">
                <label>Địa chỉ nhận hàng</label>
                <textarea name="address" required rows="4" placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố"></textarea>
            </div>
            <div class="form-group">
                <label>Phương thức thanh toán</label>
                <input type="hidden" name="payment_method" value="COD">
                <div style="padding: 12px; background-color: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; font-size: 14px; font-weight: 600; color: #2563EB; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-truck-fast"></i> Thanh toán khi nhận hàng (COD)
                </div>
            </div>
            <div class="form-group">
                <label>Ghi chú đơn hàng (Không bắt buộc)</label>
                <textarea name="note" rows="3" placeholder="Ví dụ: Giao hàng vào giờ hành chính, gọi điện trước khi giao..."></textarea>
            </div>
            <input type="hidden" name="submit_token" value="<?= e($_SESSION['submit_token'] ?? '') ?>">
            <button type="submit" class="btn btn--block" style="height: 48px; font-size: 15px;">Xác nhận đặt hàng ngay <i class="fa-solid fa-square-check"></i></button>
        </form>
    </div>

    <aside class="checkout-summary">
        <h3>Sản phẩm đặt mua</h3>
        <div class="summary-items-list" style="margin-bottom: 20px;">
            <?php foreach ($cartItems as $item): ?>
                <div class="summary-item">
                    <span><?= e($item['name']) ?> <strong style="color: var(--text-secondary);">x<?= (int)$item['quantity'] ?></strong></span>
                    <strong><?= formatPrice($item['line_total']) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="summary-row"><span>Tạm tính</span><strong><?= formatPrice($subtotal) ?></strong></div>
        <div class="summary-row" id="discountRow" style="display: none;"><span>Giảm giá</span><strong id="discountValue" style="color: var(--primary);">-0đ</strong></div>
        <div class="summary-row"><span>Phí vận chuyển</span><strong style="color: var(--success);"><?= $shipping > 0 ? formatPrice($shipping) : 'Miễn phí' ?></strong></div>
        <div class="summary-row total"><span>Tổng tiền phải trả</span><strong id="totalValue"><?= formatPrice($total) ?></strong></div>

        <!-- Coupon Form -->
        <div class="coupon-section" style="margin-top: 20px; border-top: 1px dashed var(--border); padding-top: 20px;">
            <div style="display: flex; gap: 8px;">
                <input type="text" id="couponInput" placeholder="Nhập mã giảm giá..." style="flex: 1; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px;">
                <button type="button" id="applyCouponBtn" class="btn btn--sm" style="padding: 0 15px; font-size: 13px;">Áp dụng</button>
            </div>
            <p id="couponMsg" style="margin: 6px 0 0 0; font-size: 12px; display: none;"></p>
        </div>
    </aside>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const applyBtn = document.getElementById('applyCouponBtn');
    const couponInput = document.getElementById('couponInput');
    const couponMsg = document.getElementById('couponMsg');
    const discountRow = document.getElementById('discountRow');
    const discountValue = document.getElementById('discountValue');
    const totalValue = document.getElementById('totalValue');

    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            const code = couponInput.value.trim();
            if (code === '') {
                showMsg('Vui lòng nhập mã giảm giá.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('coupon_code', code);
            formData.append('subtotal', '<?= (float)$subtotal ?>');
            formData.append('csrf_token', '<?= csrf_token() ?>');

            applyBtn.disabled = true;
            applyBtn.innerText = 'Đang áp dụng...';

            fetch('<?= url("checkout/apply_coupon") ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                applyBtn.disabled = false;
                applyBtn.innerText = 'Áp dụng';

                if (data.success) {
                    showMsg(data.message, 'success');
                    discountRow.style.display = 'flex';
                    discountValue.innerText = data.discount_formatted;
                    totalValue.innerText = data.new_total_formatted;
                } else {
                    showMsg(data.message, 'error');
                    discountRow.style.display = 'none';
                    totalValue.innerText = '<?= formatPrice($total) ?>';
                }
            })
            .catch(err => {
                applyBtn.disabled = false;
                applyBtn.innerText = 'Áp dụng';
                showMsg('Lỗi kết nối máy chủ.', 'error');
            });
        });
    }

    function showMsg(text, type) {
        couponMsg.style.display = 'block';
        couponMsg.innerText = text;
        if (type === 'success') {
            couponMsg.style.color = 'var(--success)';
        } else {
            couponMsg.style.color = '#EF4444';
        }
    }
});
</script>

<style>
    .checkout-page {
        display: grid;
        grid-template-columns: 1.3fr 0.7fr;
        gap: 30px;
        margin: 24px auto 60px;
        align-items: start;
    }

    .checkout-card,
    .checkout-summary {
        background: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        padding: 30px;
    }

    .checkout-card h1 {
        font-size: 24px;
        color: var(--text-primary);
        margin-bottom: 6px;
        font-weight: 800;
    }

    .checkout-sub {
        color: var(--text-secondary);
        margin-bottom: 24px;
        font-size: 14px;
    }

    .checkout-form {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-weight: 700;
        color: var(--text-primary);
        font-size: 13.5px;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: var(--radius-elem);
        padding: 12px 16px;
        font-size: 14px;
        background-color: var(--bg-white);
        color: var(--text-primary);
        transition: var(--transition);
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(10, 91, 255, 0.15);
    }

    .summary-item,
    .summary-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 0;
        border-bottom: 1px solid var(--border);
        color: var(--text-secondary);
        font-size: 13.5px;
    }

    .summary-item span {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .summary-item strong {
        color: var(--text-primary);
        white-space: nowrap;
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
        .checkout-page {
            grid-template-columns: 1fr;
        }
    }
</style>