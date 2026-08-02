<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/services/CouponService.php';

function assertCouponUsage(bool $condition, string $message): void
{
    if ($condition) {
        echo "[PASS] $message\n";
    } else {
        echo "[FAIL] $message\n";
        exit(1);
    }
}

$db = Database::getConnection();

// Bảng tạm
$db->exec('DROP TEMPORARY TABLE IF EXISTS orders');
$db->exec('DROP TEMPORARY TABLE IF EXISTS coupons');

$db->exec('CREATE TEMPORARY TABLE coupons (
    id int unsigned NOT NULL AUTO_INCREMENT,
    code varchar(50) NOT NULL,
    discount_value decimal(12,0) NOT NULL,
    type enum("fixed","percent","free_shipping") NOT NULL DEFAULT "fixed",
    max_discount decimal(12,0) DEFAULT NULL,
    min_order_value decimal(12,0) DEFAULT "0",
    usage_limit int DEFAULT NULL,
    usage_limit_per_user int DEFAULT "1",
    used_count int NOT NULL DEFAULT "0",
    start_date datetime DEFAULT NULL,
    end_date datetime DEFAULT NULL,
    description varchar(500) DEFAULT NULL,
    status enum("active","inactive") NOT NULL DEFAULT "active",
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY code (code)
)');

$db->exec('CREATE TEMPORARY TABLE orders (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    order_code varchar(50) NOT NULL,
    user_id int unsigned DEFAULT NULL,
    coupon_id int unsigned DEFAULT NULL,
    customer_name varchar(150) NOT NULL,
    phone varchar(50) NOT NULL,
    email varchar(150) DEFAULT NULL,
    address text NOT NULL,
    note text,
    payment_method varchar(50) NOT NULL DEFAULT "COD",
    payment_status enum("unpaid","pending","paid","failed","refunded") NOT NULL DEFAULT "unpaid",
    shipping_carrier varchar(100) DEFAULT NULL,
    shipping_tracking_code varchar(120) DEFAULT NULL,
    shipping_fee decimal(12,0) NOT NULL DEFAULT "0",
    subtotal decimal(12,0) NOT NULL DEFAULT "0",
    discount_amount decimal(12,0) NOT NULL DEFAULT "0",
    total_amount decimal(12,0) NOT NULL DEFAULT "0",
    status enum("pending","confirmed","processing","shipping","completed","cancelled") NOT NULL DEFAULT "pending",
    PRIMARY KEY (id)
)');

// Setup
$db->exec("INSERT INTO coupons (id, code, discount_value, used_count, usage_limit) VALUES (1, 'DISCOUNT10', 10000, 1, 1)");
$db->exec("INSERT INTO coupons (id, code, discount_value, used_count, usage_limit) VALUES (2, 'NO_LIMIT', 10000, 2, NULL)");
$db->exec("INSERT INTO coupons (id, code, discount_value, used_count, usage_limit) VALUES (3, 'DRIFT_TEST', 10000, 0, NULL)");

$db->exec("INSERT INTO orders (id, order_code, coupon_id, status, user_id, customer_name, phone, address) VALUES (101, 'ORD101', 1, 'pending', 1, 'A', '1', 'A')"); // For Case C, D, F, G, I
$db->exec("INSERT INTO orders (id, order_code, coupon_id, status, user_id, customer_name, phone, address) VALUES (102, 'ORD102', 2, 'pending', 2, 'A', '1', 'A')"); // For Case E
$db->exec("INSERT INTO orders (id, order_code, coupon_id, status, user_id, customer_name, phone, address) VALUES (103, 'ORD103', 2, 'pending', 3, 'A', '1', 'A')"); // For Case E
$db->exec("INSERT INTO orders (id, order_code, coupon_id, status, user_id, customer_name, phone, address) VALUES (104, 'ORD104', NULL, 'pending', 4, 'A', '1', 'A')"); // For Case B
$db->exec("INSERT INTO orders (id, order_code, coupon_id, status, user_id, customer_name, phone, address) VALUES (105, 'ORD105', 3, 'pending', 5, 'A', '1', 'A')"); // For Case H

echo "--- 1. Guard and basic ---\n";

// Case A
try {
    CouponService::releaseOrderCoupon($db, 101);
    assertCouponUsage(false, 'Gọi service ngoài transaction phải throw LogicException');
} catch (LogicException $e) {
    assertCouponUsage(true, 'Throw LogicException khi gọi ngoài transaction');
}

// Case B
$db->beginTransaction();
$res = CouponService::releaseOrderCoupon($db, 104);
$db->commit();
assertCouponUsage($res === true, 'Service trả true khi order không có coupon');

echo "--- 2. Release lifecycle ---\n";

// Case C, F, G
$db->beginTransaction();
CouponService::releaseOrderCoupon($db, 101);
$db->exec("UPDATE orders SET status = 'cancelled' WHERE id = 101");
$db->commit();
$usedCount = (int)$db->query('SELECT used_count FROM coupons WHERE id = 1')->fetchColumn();
$orderStatus = $db->query('SELECT status FROM orders WHERE id = 101')->fetchColumn();
assertCouponUsage($usedCount === 0, 'Release pending order làm used_count = 0');
assertCouponUsage($orderStatus === 'cancelled', 'Order status được cập nhật thành cancelled');
assertCouponUsage($usedCount === 0, 'Coupon có lại global capacity (limit 1, used_count 0)');

$perUserCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE user_id = 1 AND coupon_id = 1 AND status != 'cancelled'")->fetchColumn();
assertCouponUsage($perUserCount === 0, 'Per-user reuse consistency: COUNT(orders WHERE status != cancelled) = 0');

// Case D
$db->beginTransaction();
$res = CouponService::releaseOrderCoupon($db, 101);
$db->commit();
$usedCount = (int)$db->query('SELECT used_count FROM coupons WHERE id = 1')->fetchColumn();
assertCouponUsage($res === true, 'Repeated cancellation trả về true');
assertCouponUsage($usedCount === 0, 'Repeated cancellation không làm âm used_count');

// Case E
$db->beginTransaction();
CouponService::releaseOrderCoupon($db, 102);
$db->exec("UPDATE orders SET status = 'cancelled' WHERE id = 102");
$db->commit();
$usedCount2 = (int)$db->query('SELECT used_count FROM coupons WHERE id = 2')->fetchColumn();
assertCouponUsage($usedCount2 === 1, 'Hủy order thứ nhất làm used_count = 1');

$db->beginTransaction();
CouponService::releaseOrderCoupon($db, 103);
$db->exec("UPDATE orders SET status = 'cancelled' WHERE id = 103");
$db->commit();
$usedCount2 = (int)$db->query('SELECT used_count FROM coupons WHERE id = 2')->fetchColumn();
assertCouponUsage($usedCount2 === 0, 'Hủy order thứ hai làm used_count = 0');

$db->beginTransaction();
CouponService::releaseOrderCoupon($db, 102);
$db->commit();
$usedCount2 = (int)$db->query('SELECT used_count FROM coupons WHERE id = 2')->fetchColumn();
assertCouponUsage($usedCount2 === 0, 'Gọi lại release order thứ nhất used_count vẫn = 0');

echo "--- 3. Drift and Rollback ---\n";

// Case H
$db->beginTransaction();
try {
    CouponService::releaseOrderCoupon($db, 105);
    $db->commit();
    assertCouponUsage(false, 'Release coupon used_count = 0 phải throw exception');
} catch (RuntimeException $e) {
    $db->rollBack();
    assertCouponUsage(true, 'Throw RuntimeException khi counter drift');
}
$usedCount3 = (int)$db->query('SELECT used_count FROM coupons WHERE id = 3')->fetchColumn();
$orderStatus3 = $db->query('SELECT status FROM orders WHERE id = 105')->fetchColumn();
assertCouponUsage($usedCount3 === 0, 'Transaction rollback giữ used_count vẫn 0');
assertCouponUsage($orderStatus3 === 'pending', 'Transaction rollback giữ order vẫn pending');

// Case I
$db->exec("INSERT INTO coupons (id, code, discount_value, used_count) VALUES (4, 'ROLLBACK_TEST', 10000, 1)");
$db->exec("INSERT INTO orders (id, order_code, coupon_id, status, customer_name, phone, address) VALUES (106, 'ORD106', 4, 'pending', 'A', '1', 'A')");

$db->beginTransaction();
CouponService::releaseOrderCoupon($db, 106);
$db->exec("UPDATE orders SET status = 'cancelled' WHERE id = 106");
try {
    throw new Exception('Rollback atomicity test');
} catch (Exception $e) {
    $db->rollBack();
}
$usedCount4 = (int)$db->query('SELECT used_count FROM coupons WHERE id = 4')->fetchColumn();
$orderStatus4 = $db->query('SELECT status FROM orders WHERE id = 106')->fetchColumn();
assertCouponUsage($usedCount4 === 1, 'Rollback atomicity: used_count trở lại giá trị cũ (1)');
assertCouponUsage($orderStatus4 === 'pending', 'Rollback atomicity: order status trở lại pending');

// Case J
$db->exec("INSERT INTO coupons (id, code, discount_value, used_count) VALUES (5, 'VNPAY_FAILED_TEST', 10000, 1)");
$db->exec("INSERT INTO orders (id, order_code, coupon_id, status, payment_status, customer_name, phone, address) VALUES (107, 'ORD107', 5, 'pending', 'pending', 'A', '1', 'A')");

$db->beginTransaction();
CouponService::releaseOrderCoupon($db, 107);
$db->exec("UPDATE orders SET payment_status = 'failed', status = 'cancelled' WHERE id = 107");
$db->commit();

$usedCount5 = (int)$db->query('SELECT used_count FROM coupons WHERE id = 5')->fetchColumn();
assertCouponUsage($usedCount5 === 0, 'Lần VNPay failed đầu tiên làm used_count giảm một lần');

$db->beginTransaction();
CouponService::releaseOrderCoupon($db, 107); // Repeated call
$db->exec("UPDATE orders SET payment_status = 'failed', status = 'cancelled' WHERE id = 107");
$db->commit();

$usedCount5 = (int)$db->query('SELECT used_count FROM coupons WHERE id = 5')->fetchColumn();
assertCouponUsage($usedCount5 === 0, 'Lần VNPay failed thứ hai không làm used_count giảm thêm');
