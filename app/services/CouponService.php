<?php

final class CouponService
{
    public static function releaseOrderCoupon(PDO $db, int $orderId): bool
    {
        if (!$db->inTransaction()) {
            throw new LogicException('Release coupon usage phải chạy bên trong database transaction.');
        }

        $stmt = $db->prepare('SELECT id, coupon_id, status FROM orders WHERE id = :order_id LIMIT 1 FOR UPDATE');
        $stmt->execute([':order_id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            throw new RuntimeException('Order không tồn tại.');
        }

        if ($order['coupon_id'] === null) {
            return true;
        }

        if ($order['status'] === 'cancelled') {
            return true;
        }

        $couponStmt = $db->prepare('SELECT id, used_count FROM coupons WHERE id = :coupon_id LIMIT 1 FOR UPDATE');
        $couponStmt->execute([':coupon_id' => $order['coupon_id']]);
        $coupon = $couponStmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) {
            throw new RuntimeException('Coupon không tồn tại.');
        }

        if ((int)$coupon['used_count'] <= 0) {
            throw new RuntimeException('Counter drift: used_count không thể <= 0 khi release.');
        }

        $updateStmt = $db->prepare('UPDATE coupons SET used_count = used_count - 1 WHERE id = :coupon_id AND used_count > 0');
        $updateStmt->execute([':coupon_id' => $order['coupon_id']]);

        if ($updateStmt->rowCount() !== 1) {
            throw new RuntimeException('Cập nhật coupon failed do counter drift.');
        }

        return true;
    }
}
