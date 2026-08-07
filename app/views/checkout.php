<?php
$cartItems = $cartItems ?? [];
$subtotal = $subtotal ?? 0;
$shipping = $shipping ?? 0;
$total = $total ?? 0;
$savedAddresses = $savedAddresses ?? [];
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
            <?php unset($_SESSION['checkout_error']); ?>
        <?php endif; ?>

        <?php $user = currentUser(); ?>
        <?php if (!$user): ?>
            <div class="alert alert--info" style="border-radius: 8px; margin-bottom: 20px; font-size: 13.5px;">
                <i class="fa-solid fa-circle-info"></i> Bạn đang đặt hàng với vai trò <strong>Khách vãng lai</strong>. Bạn có thể <a href="<?= url('auth/login?redirect=checkout') ?>" style="font-weight: 700; text-decoration: underline;">Đăng nhập</a> để sử dụng sổ địa chỉ và theo dõi đơn hàng dễ dàng hơn.
            </div>
        <?php endif; ?>

        <form method="post" action="<?= url('checkout/submit') ?>" class="checkout-form">
            <?= csrf_field() ?>
            <input type="hidden" name="submit_token" value="<?= e($_SESSION['submit_token'] ?? '') ?>">
            <?php if ($savedAddresses): ?>
                <div class="form-group">
                    <label>Chọn từ sổ địa chỉ</label>
                    <select id="savedAddress" name="saved_address_id">
                        <option value="">Nhập địa chỉ khác</option>
                        <?php foreach ($savedAddresses as $saved): ?>
                            <option value="<?= (int)$saved['id'] ?>" data-name="<?= e($saved['recipient_name']) ?>" data-phone="<?= e($saved['phone']) ?>" data-address="<?= e($saved['address_line']) ?>">
                                <?= e($saved['recipient_name']) ?> — <?= e($saved['address_line']) ?><?= !empty($saved['is_default']) ? ' (Mặc định)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <a href="<?= url('profile/addresses') ?>" style="font-size:13px">Quản lý sổ địa chỉ</a>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label>Họ và tên người nhận</label>
                <input type="text" name="customer_name" required value="<?= e($_POST['customer_name'] ?? $user['full_name'] ?? $user['name'] ?? '') ?>" placeholder="Nguyễn Văn A">
            </div>
            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="text" name="phone" required value="<?= e($_POST['phone'] ?? $user['phone'] ?? '') ?>" placeholder="0909 123 456">
            </div>
            <div class="form-group">
                <label>Địa chỉ nhận hàng</label>
                <textarea name="address" required rows="4" placeholder="Số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố"><?= e($_POST['address'] ?? '') ?></textarea>
            </div>
            <?php if ($user): ?>
                <label class="save-address-option">
                    <input type="checkbox" name="save_address" value="1">
                    <span><strong>Lưu thông tin giao hàng vào sổ địa chỉ</strong><small>Dùng lại nhanh chóng cho lần mua hàng tiếp theo.</small></span>
                </label>
            <?php endif; ?>
            <div class="form-group">
                <label>Phương thức thanh toán</label>

                <div class="payment-methods">
                    <label class="payment-label">
                        <input
                            type="radio"
                            name="payment_method"
                            value="COD"
                            checked
                            class="payment-radio">

                        <span class="payment-text">
                            <i class="fa-solid fa-truck-fast"></i>
                            Thanh toán khi nhận hàng (COD)
                        </span>
                    </label>

                    <label class="payment-label">
                        <input
                            type="radio"
                            name="payment_method"
                            value="VNPAY"
                            class="payment-radio">

                        <span class="payment-text">
                            <i class="fa-solid fa-credit-card"></i>
                            Thanh toán Online (VNPAY)
                        </span>
                    </label>
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
        <div class="summary-items-list" style="margin-bottom: 20px; display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($cartItems as $item): ?>
                <?php 
                    $imgUrl = productImageUrl(
                        $item['image'] ?? '', 
                        $item['category_slug'] ?? $item['slug'] ?? '', 
                        (int)($item['product_id'] ?? $item['id'] ?? 0)
                    ); 
                ?>
                <div class="summary-item" style="display: flex; align-items: center; gap: 12px; padding-bottom: 10px; border-bottom: 1px dashed var(--border);">
                    <div style="width: 52px; height: 52px; border-radius: 8px; border: 1px solid var(--border); background: var(--bg-card); display: flex; align-items: center; justify-content: center; flex-shrink: 0; padding: 4px;">
                        <img src="<?= e($imgUrl) ?>" alt="<?= e($item['name']) ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <span style="display: block; font-weight: 600; font-size: 13.5px; line-height: 1.35; color: var(--text-primary); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= e($item['name']) ?></span>
                        <span style="font-size: 12px; color: var(--text-secondary); margin-top: 2px; display: block;">Số lượng: <strong><?= (int)$item['quantity'] ?></strong></span>
                    </div>
                    <strong style="font-size: 14px; color: var(--primary); white-space: nowrap;"><?= formatPrice($item['line_total']) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>

        <?php 
            $discountAmount = $discountAmount ?? 0;
            $appliedCoupon = $appliedCoupon ?? null;
        ?>

        <div class="summary-row"><span>Tạm tính</span><strong><?= formatPrice($subtotal) ?></strong></div>
        <div class="summary-row" id="discountRow" style="display: <?= $discountAmount > 0 ? 'flex' : 'none' ?>;"><span>Giảm giá</span><strong id="discountValue" style="color: var(--primary);">-<?= formatPrice($discountAmount) ?></strong></div>
        <div class="summary-row"><span>Phí vận chuyển</span><strong style="color: var(--success);"><?= $shipping > 0 ? formatPrice($shipping) : 'Miễn phí' ?></strong></div>
        <div class="summary-row total"><span>Tổng tiền phải trả</span><strong id="totalValue"><?= formatPrice($total) ?></strong></div>

        <!-- Coupon Form -->
        <div class="coupon-section" style="margin-top: 20px; border-top: 1px dashed var(--border); padding-top: 20px;">
            <div style="display: flex; gap: 8px;">
                <input type="text" id="couponInput" placeholder="Nhập mã giảm giá..." value="<?= e($appliedCoupon['code'] ?? '') ?>" style="flex: 1; padding: 8px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px;">
                <button type="button" id="applyCouponBtn" class="btn btn--sm" style="padding: 0 15px; font-size: 13px;">Áp dụng</button>
                <button type="button" id="removeCouponBtn" class="btn btn--outline btn--sm" style="padding: 0 12px; font-size: 13px; display: <?= !empty($appliedCoupon) ? 'inline-flex' : 'none' ?>; align-items: center; gap: 4px; color: #EF4444; border-color: #FCA5A5;" title="Gỡ mã giảm giá"><i class="fa-solid fa-trash-can"></i> Gỡ mã</button>
            </div>
            <p id="couponMsg" style="margin: 6px 0 0 0; font-size: 12px; display: none;"></p>

            <!-- Recommended Available Coupons List -->
            <?php if (!empty($availableCoupons)): ?>
                <div style="margin-top: 15px; border-top: 1px dashed var(--border); padding-top: 12px;">
                    <span style="font-size: 12.5px; font-weight: 700; color: var(--primary); display: flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                        <i class="fa-solid fa-ticket"></i> Mã giảm giá gợi ý cho đơn hàng:
                    </span>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <?php foreach ($availableCoupons as $ac): ?>
                            <div style="background: rgba(37, 99, 235, 0.04); border: 1px dashed var(--primary); border-radius: 6px; padding: 8px 12px; display: flex; justify-content: space-between; align-items: center; <?= $ac['is_disabled'] ? 'opacity: 0.5;' : '' ?>">
                                <div>
                                    <strong style="font-size: 12.5px; color: var(--primary); display: block;">
                                        <?= e($ac['code']) ?> 
                                        <?php if ($ac['is_disabled']): ?>
                                            <span style="color: #EF4444; font-size: 10px; font-weight: normal; margin-left: 4px;">(<?= $ac['disable_reason'] ?>)</span>
                                        <?php endif; ?>
                                    </strong>
                                    <small style="font-size: 11px; color: var(--text-secondary);">
                                        <?= $ac['type'] === 'percent' ? 'Giảm ' . (int)$ac['discount_value'] . '%' : 'Giảm ' . formatPrice($ac['discount_value']) ?>
                                        (Đơn từ <?= formatPrice($ac['min_order_value']) ?>)
                                    </small>
                                </div>
                                <button type="button" class="btn btn--outline btn--sm" style="font-size: 11px; padding: 4px 10px; font-weight: 700;" onclick="applyVoucherCode('<?= e($ac['code']) ?>')" <?= $ac['is_disabled'] ? 'disabled' : '' ?>>Dùng ngay</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </aside>
</section>

<script>
    function applyVoucherCode(code) {
        const couponInput = document.getElementById('couponInput');
        const applyBtn = document.getElementById('applyCouponBtn');
        if (couponInput && applyBtn) {
            couponInput.value = code;
            applyBtn.click();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedAddress = document.getElementById('savedAddress');
        if (savedAddress) savedAddress.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (!option.value) return;
            const saveCheckbox = document.querySelector('[name="save_address"]');
            if (saveCheckbox) saveCheckbox.checked = false;
            document.querySelector('[name="customer_name"]').value = option.dataset.name || '';
            document.querySelector('[name="phone"]').value = option.dataset.phone || '';
            document.querySelector('[name="address"]').value = option.dataset.address || '';
        });

        const applyBtn = document.getElementById('applyCouponBtn');
        const removeBtn = document.getElementById('removeCouponBtn');
        const couponInput = document.getElementById('couponInput');
        const couponMsg = document.getElementById('couponMsg');
        const discountRow = document.getElementById('discountRow');
        const discountValue = document.getElementById('discountValue');
        const totalValue = document.getElementById('totalValue');

        function doRemoveCoupon() {
            const formData = new FormData();
            formData.append('csrf_token', '<?= csrf_token() ?>');

            fetch('<?= url("checkout/remove_coupon") ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                couponInput.value = '';
                if (removeBtn) removeBtn.style.display = 'none';
                discountRow.style.display = 'none';
                if (data.new_total_formatted) {
                    totalValue.innerText = data.new_total_formatted;
                }
                showMsg('Đã gỡ mã giảm giá.', 'info');
            })
            .catch(err => {
                console.error(err);
            });
        }

        // Tự động gỡ mã giảm giá khi người dùng xóa nội dung trong ô nhập mã
        let isRemoving = false;
        if (couponInput) {
            couponInput.addEventListener('input', function() {
                if (this.value.trim() === '' && discountRow.style.display !== 'none' && !isRemoving) {
                    isRemoving = true;
                    doRemoveCoupon();
                    setTimeout(() => { isRemoving = false; }, 300);
                }
            });
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                doRemoveCoupon();
            });
        }

        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                const code = couponInput.value.trim();
                if (code === '') {
                    doRemoveCoupon();
                    return;
                }

                const formData = new FormData();
                formData.append('coupon_code', code);
                formData.append('csrf_token', '<?= csrf_token() ?>');

                applyBtn.disabled = true;
                applyBtn.innerText = 'Đang áp...';

                fetch('<?= url("checkout/apply_coupon") ?>', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        applyBtn.disabled = false;
                        applyBtn.innerText = 'Áp dụng';

                        if (data.success) {
                            if (data.removed) {
                                if (removeBtn) removeBtn.style.display = 'none';
                                discountRow.style.display = 'none';
                                totalValue.innerText = data.new_total_formatted;
                                showMsg(data.message, 'info');
                            } else {
                                showMsg(data.message, 'success');
                                discountRow.style.display = 'flex';
                                discountValue.innerText = data.discount_formatted;
                                totalValue.innerText = data.new_total_formatted;
                                if (removeBtn) removeBtn.style.display = 'inline-flex';
                            }
                        } else {
                            showMsg(data.message, 'error');
                            if (removeBtn) removeBtn.style.display = 'none';
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
            } else if (type === 'info') {
                couponMsg.style.color = 'var(--primary)';
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

    .checkout-summary {
        position: sticky;
        top: 165px;
        max-height: calc(100vh - 185px);
        overflow-y: auto;
        scrollbar-width: thin;
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

    .save-address-option { display:flex; align-items:flex-start; gap:10px; padding:14px; border:1px solid var(--border); border-radius:var(--radius-elem); cursor:pointer; background:#f8fafc; }
    .save-address-option input { width:auto; margin-top:3px; }
    .save-address-option span { display:grid; gap:3px; }
    .save-address-option small { color:var(--text-secondary); font-weight:400; }

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

        .checkout-summary {
            position: static;
            max-height: none;
            overflow: visible;
        }
    }
</style>
