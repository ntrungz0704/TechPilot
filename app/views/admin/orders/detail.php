<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 30px; margin-bottom: 30px;">
    <!-- Panel Left: Thông tin đơn hàng & sản phẩm -->
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 15px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700;">Đơn hàng: <?= e($order['order_code']) ?></h3>
            <span style="font-size: 13px; color: var(--text-secondary);">Ngày đặt: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
        </div>

        <h4 style="font-weight: 700; font-size: 14px; margin-bottom: 12px; color: var(--text-primary);">Sản phẩm đặt mua</h4>
        <div class="table-responsive" style="margin-bottom: 25px;">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá bán</th>
                        <th style="width: 100px; text-align: center;">Số lượng</th>
                        <th style="text-align: right;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <img src="<?= e(productImageUrl($item['product_image'] ?? '', $item['product_name'] ?? '')) ?>" alt="<?= e($item['product_name'] ?? '') ?>" style="width: 40px; height: 40px; object-fit: contain; border: 1px solid var(--border); border-radius: 4px; background: var(--bg-body);">
                            </td>
                            <td>
                                <strong style="font-size: 13.5px;"><?= e($item['product_name'] ?? 'Sản phẩm đã bị xoá') ?></strong>
                            </td>
                            <td><?= formatPrice($item['price']) ?></td>
                            <td style="text-align: center;">x<?= (int)$item['quantity'] ?></td>
                            <td style="text-align: right; font-weight: 700;"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tổng kết tiền -->
        <div style="max-width: 320px; margin-left: auto; display: flex; flex-direction: column; gap: 8px; font-size: 14px; border-top: 1px solid var(--border); padding-top: 15px;">
            <div style="display: flex; justify-content: space-between;"><span>Tạm tính:</span><strong><?= formatPrice($order['subtotal']) ?></strong></div>
            
            <?php if ((float)$order['discount_amount'] > 0): ?>
                <div style="display: flex; justify-content: space-between; color: var(--primary);">
                    <span>Giảm giá (<?= e($order['coupon_code'] ?? '') ?>):</span>
                    <strong>-<?= formatPrice($order['discount_amount']) ?></strong>
                </div>
            <?php endif; ?>

            <div style="display: flex; justify-content: space-between;"><span>Phí vận chuyển:</span><strong><?= $order['shipping_fee'] > 0 ? formatPrice($order['shipping_fee']) : 'Miễn phí' ?></strong></div>
            <div style="display: flex; justify-content: space-between; font-size: 16px; border-top: 1px dashed var(--border); padding-top: 8px; color: var(--primary); font-weight: 800;">
                <span>Tổng cộng:</span>
                <span><?= formatPrice($order['total_amount']) ?></span>
            </div>
        </div>
    </div>

    <!-- Panel Right: Khách hàng & Cập nhật Trạng thái -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <!-- Khách hàng -->
        <div class="card" style="margin-bottom: 0;">
            <h3 class="card-title">Thông tin giao hàng</h3>
            <div style="font-size: 14px; display: flex; flex-direction: column; gap: 12px; line-height: 1.5;">
                <p><strong>Người nhận:</strong> <?= e($order['customer_name']) ?></p>
                <p><strong>Số điện thoại:</strong> <?= e(formatPhone($order['phone'])) ?></p>
                <?php if (!empty($order['email'])): ?>
                    <p><strong>Email:</strong> <?= e($order['email']) ?></p>
                <?php endif; ?>
                <p><strong>Địa chỉ giao:</strong> <?= e($order['address']) ?></p>
                <?php if (!empty($order['note'])): ?>
                    <p style="background: #F3F4F6; padding: 10px; border-left: 3px solid var(--primary); border-radius: 4px; font-style: italic;">
                        <strong>Ghi chú:</strong> <?= e($order['note']) ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cập nhật trạng thái -->
        <div class="card" style="margin-bottom: 0;">
            <h3 class="card-title">Trạng thái đơn hàng</h3>
            <div style="margin-bottom:14px;padding:12px;border-radius:7px;background:<?= ($order['payment_status'] ?? '') === 'paid' ? '#ECFDF5' : '#FFF7ED' ?>">
                Thanh toán: <strong><?= e(($order['payment_status'] ?? '') === 'paid' ? 'Đã thanh toán' : (($order['payment_status'] ?? '') === 'failed' ? 'Thất bại' : 'Chưa thanh toán')) ?></strong>
            </div>
            
            <form method="post" action="<?= url('admin/orders/update_status/' . $order['id']) ?>" style="display: flex; flex-direction: column; gap: 15px;">
                <?= csrf_field() ?>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="status">Thay đổi trạng thái</label>
                    <select name="status" id="status" class="form-control" style="font-weight: 600;">
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Chờ xử lý (Pending)</option>
                        <option value="confirmed" <?= $order['status'] === 'confirmed' ? 'selected' : '' ?> <?= ($order['payment_method'] ?? '') === 'VNPAY' && ($order['payment_status'] ?? '') !== 'paid' ? 'disabled' : '' ?>>Đã xác nhận (Confirmed)</option>
                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Đang xử lý (Processing)</option>
                        <option value="shipping" <?= $order['status'] === 'shipping' ? 'selected' : '' ?>>Đang giao hàng (Shipping)</option>
                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Hoàn thành (Completed)</option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Đã huỷ (Cancelled)</option>
                    </select>
                </div>

                <div style="font-size: 13.5px; line-height: 1.5; background: #FFFBEB; border: 1px solid #FDE68A; padding: 12px; border-radius: 6px; color: #92400E;">
                    <i class="fa-solid fa-circle-info"></i> Đơn VNPay phải thanh toán thành công mới được xác nhận. Với COD, thanh toán tự chuyển thành <strong>Paid</strong> khi đơn hoàn thành. Đơn bị hủy sẽ được hoàn lại tồn kho.
                </div>

                <button type="submit" class="btn" style="justify-content: center;"><i class="fa-solid fa-floppy-disk"></i> Cập nhật trạng thái</button>
            </form>
        </div>
    </div>
</div>
