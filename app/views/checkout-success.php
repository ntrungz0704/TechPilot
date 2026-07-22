<?php
$order = $order ?? [];
$paymentStatus = $order['payment_status'] ?? 'unpaid';
$isVnpay = ($order['payment_method'] ?? 'COD') === 'VNPAY';
$paymentFailed = $isVnpay && $paymentStatus === 'failed';
?>
<section class="container breadcrumb">
    <a href="<?= url('/') ?>">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i>
    <span><?= $paymentFailed ? 'Thanh toán chưa hoàn tất' : 'Đặt hàng thành công' ?></span>
</section>

<section class="container success-page">
    <div class="success-card">
        <?php if (!empty($order['payment_error'])): ?>
            <div class="alert alert--error"><?= e($order['payment_error']) ?></div>
        <?php endif; ?>
        <i class="fa-solid <?= $paymentFailed ? 'fa-circle-xmark payment-failed-icon' : 'fa-circle-check' ?>"></i>
        <h1><?= $paymentFailed ? 'Thanh toán chưa thành công' : 'Đặt hàng thành công!' ?></h1>
        <p class="success-desc"><?= $paymentFailed ? 'Giao dịch VNPay đã bị hủy hoặc không thành công. Đơn hàng chưa được thanh toán.' : 'Cảm ơn bạn đã tin tưởng mua sắm tại TechPilot. Đơn hàng của bạn đã được tiếp nhận và xử lý.' ?></p>
        
        <div class="success-details">
            <div class="detail-row">
                <span>Mã đơn hàng</span>
                <strong><?= e($order['order_code'] ?? '') ?></strong>
            </div>
            <div class="detail-row">
                <span>Trạng thái thanh toán</span>
                <span class="status-badge"><?= e($paymentStatus === 'paid' ? 'Đã thanh toán' : ($paymentStatus === 'failed' ? 'Thanh toán thất bại' : ($paymentStatus === 'pending' ? 'Đang chờ VNPay' : 'Chưa thanh toán'))) ?></span>
            </div>
            <div class="detail-row">
                <span>Người nhận</span>
                <strong><?= e($order['customer_name'] ?? '') ?></strong>
            </div>
            <div class="detail-row">
                <span>Số điện thoại</span>
                <strong><?= e(formatPhone($order['phone'] ?? '')) ?></strong>
            </div>
            <div class="detail-row">
                <span>Địa chỉ giao hàng</span>
                <strong><?= e($order['address'] ?? '') ?></strong>
            </div>
            <div class="detail-row">
                <span>Phương thức thanh toán</span>
                <strong><?= e(($order['payment_method'] ?? 'COD') === 'COD' ? 'Thanh toán khi nhận hàng (COD)' : 'Thanh toán trực tuyến qua VNPay') ?></strong>
            </div>
            <div class="detail-row total">
                <span>Tổng số tiền</span>
                <strong><?= formatPrice($order['total'] ?? 0) ?></strong>
            </div>
        </div>
        
        <div style="margin-top: 30px; display: flex; gap: 16px; justify-content: center;">
            <a href="<?= url('/') ?>" class="btn">Tiếp tục mua sắm <i class="fa-solid fa-cart-shopping"></i></a>
            <a href="<?= url('profile/orders') ?>" class="btn btn--outline">Theo dõi đơn hàng <i class="fa-solid fa-truck"></i></a>
        </div>
    </div>
</section>

<style>
    .success-page {
        margin: 24px auto 60px;
    }

    .success-card {
        background: var(--bg-white);
        border: 1px solid var(--border);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        padding: 40px;
        text-align: center;
        max-width: 650px;
        margin: 0 auto;
    }

    .success-card i.fa-circle-check {
        font-size: 64px;
        color: var(--success);
        margin-bottom: 20px;
    }

    .success-card i.payment-failed-icon {
        font-size: 64px;
        color: #dc2626;
        margin-bottom: 20px;
    }

    .success-card h1 {
        font-size: 26px;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 12px;
    }

    .success-desc {
        color: var(--text-secondary);
        font-size: 14px;
        margin-bottom: 30px;
        line-height: 1.5;
    }

    .success-details {
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 24px;
        border-radius: var(--radius-elem);
        background: var(--secondary);
        border: 1px solid var(--border);
    }

    .detail-row {
        display: flex;
        justify-content: space-between;
        font-size: 13.5px;
        color: var(--text-secondary);
        gap: 20px;
    }

    .detail-row strong {
        color: var(--text-primary);
        text-align: right;
    }

    .detail-row.total {
        border-top: 1px solid var(--border);
        padding-top: 16px;
        margin-top: 8px;
        font-size: 15px;
        font-weight: 800;
        color: var(--text-primary);
    }

    .detail-row.total strong {
        color: var(--primary);
        font-size: 20px;
    }

    .status-badge {
        background-color: var(--warning);
        color: #FFFFFF;
        font-size: 11px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
    }
</style>
