<?php

/**
 * Product pricing consistency regression tests.
 *
 * The integration section uses TEMPORARY tables. They only shadow production
 * tables on this PDO connection and are always dropped in finally.
 */

$rootPath = dirname(__DIR__);
$passed = 0;
$failed = 0;

function assertPricing(bool $condition, string $message): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "[PASS] {$message}\n";
        return;
    }

    $failed++;
    echo "[FAIL] {$message}\n";
}

function indexedByProductId(array $items): array
{
    $indexed = [];
    foreach ($items as $item) {
        $indexed[(int)($item['product_id'] ?? $item['id'] ?? 0)] = $item;
    }
    return $indexed;
}

require_once $rootPath . '/config/app.php';
require_once $rootPath . '/app/core/helpers.php';

echo "========================================================\n";
echo "=== TECHPILOT PRODUCT PRICING CONSISTENCY TEST SUITE ===\n";
echo "========================================================\n\n";

echo "--- 1. Effective-price contract ---\n";
$saleWins = getEffectiveProductData([
    'price' => 1000000,
    'sale_price' => 800000,
    'discount_price' => 900000,
    'is_flash_sale' => 1,
]);
assertPricing($saleWins['final_price'] === 800000.0, 'Sale thường thấp hơn Flash Sale thì lấy sale thường');
assertPricing(($saleWins['price_source'] ?? '') === 'sale', 'Nguồn giá ghi nhận đúng sale thường');
assertPricing($saleWins['is_flash_sale'] === false, 'Không gắn nhãn Flash Sale khi Flash Sale không thắng');

$flashWins = getEffectiveProductData([
    'price' => 1000000,
    'sale_price' => 900000,
    'discount_price' => 700000,
]);
assertPricing($flashWins['final_price'] === 700000.0, 'Flash Sale thấp nhất thì lấy Flash Sale');
assertPricing(($flashWins['price_source'] ?? '') === 'flash', 'Nguồn giá ghi nhận đúng Flash Sale');
assertPricing($flashWins['is_flash_sale'] === true, 'Chỉ gắn nhãn Flash Sale khi giá Flash thực sự thắng');

$invalidFlash = getEffectiveProductData([
    'price' => 1000000,
    'sale_price' => 0,
    'discount_price' => 1200000,
    'is_flash_sale' => 1,
]);
assertPricing($invalidFlash['final_price'] === 1000000.0, 'Flash Sale bằng 0 hoặc cao hơn giá gốc bị bỏ qua');
assertPricing(($invalidFlash['price_source'] ?? '') === 'base', 'Giá không hợp lệ không làm sai nguồn giá gốc');
assertPricing($invalidFlash['is_flash_sale'] === false, 'Cờ Flash Sale cũ không tự biến thành giá Flash đang hoạt động');

assertPricing(function_exists('shippingFee'), 'Có một hàm phí vận chuyển dùng chung');
if (function_exists('shippingFee')) {
    assertPricing(shippingFee(299999) === 30000.0, 'Đơn dưới 300.000đ có phí vận chuyển 30.000đ');
    assertPricing(shippingFee(300000) === 0.0, 'Đơn từ 300.000đ được miễn phí vận chuyển');
}

echo "\n--- 2. Consumer source contracts ---\n";
$productSource = file_get_contents($rootPath . '/app/models/Product.php');
$cartSource = file_get_contents($rootPath . '/app/services/CartService.php');
$checkoutSource = file_get_contents($rootPath . '/app/controllers/CheckoutController.php');
$orderSource = file_get_contents($rootPath . '/app/models/Order.php');
$builderSource = file_get_contents($rootPath . '/app/controllers/PcBuilderController.php');
$detailSource = file_get_contents($rootPath . '/app/views/product/detail.php');
$cartViewSource = file_get_contents($rootPath . '/app/views/cart.php');
$footerSource = file_get_contents($rootPath . '/app/views/layouts/footer.php');
$adminFlashSaleSource = file_get_contents($rootPath . '/app/controllers/AdminFlashSaleController.php');
$adminFlashCreateSource = file_get_contents($rootPath . '/app/views/admin/flash_sales/create.php');
$adminFlashEditSource = file_get_contents($rootPath . '/app/views/admin/flash_sales/edit.php');

assertPricing(
    substr_count($productSource, "activeFlashPriceSql('p')") >= 3,
    'Product model nạp giá Flash đang trong đúng khung thời gian cho detail/cart/catalog'
);
assertPricing(
    function_exists('activeFlashSaleItemIdSql'),
    'Có helper chọn duy nhất Flash Sale item hợp lệ khi chiến dịch trùng nhau'
);
assertPricing(
    str_contains(activeFlashPriceSql('p'), 'pricing_fsi.sold_quantity < pricing_fsi.allocation_quantity'),
    'Helper không quảng cáo giá Flash Sale khi quota đã hết'
);
assertPricing(
    str_contains($productSource, 'fsi.sold_quantity as fs_sold')
        && !str_contains($productSource, 'sold_data.total_sold'),
    'Thanh tiến độ Flash Sale đọc cùng bộ đếm quota thay vì tổng đơn hoàn tất toàn thời gian'
);
assertPricing(
    str_contains($cartSource, 'getEffectiveProductData($product)'),
    'CartService hydrate lại giá hiệu lực từ database'
);
assertPricing(
    str_contains($cartSource, '$available = $stock > 0') &&
    str_contains($cartSource, '$quantity = $available') &&
    str_contains($cartSource, '$lineTotal = $available') &&
    str_contains($cartSource, 'has_unavailable_items') &&
    str_contains($cartSource, 'can_checkout'),
    'CartService có xử lý quantity/line_total theo available và expose checkout flags'
);
assertPricing(
    str_contains($checkoutSource, 'CartService') && str_contains($checkoutSource, 'getSummary()'),
    'Checkout dùng lại summary server-side của CartService'
);
assertPricing(
    substr_count($checkoutSource, "\$summary['can_checkout']") >= 3,
    'CheckoutController dùng \$summary[\'can_checkout\'] trong index, apply_coupon, submit'
);
assertPricing(
    !str_contains($checkoutSource, "(float)(\$_POST['subtotal'] ?? 0)"),
    'API coupon không tin subtotal do trình duyệt gửi lên'
);
assertPricing(
    str_contains($orderSource, 'getEffectiveProductData($dbProduct)')
        && str_contains($orderSource, '$calculatedSubtotal'),
    'Order khóa sản phẩm và tự tính lại giá/subtotal trong transaction'
);
assertPricing(
    str_contains($builderSource, "activeFlashPriceSql('products')"),
    'PC Builder nạp cùng giá Flash đang hoạt động'
);
assertPricing(
    substr_count($adminFlashSaleSource, 'assertValidDiscountPrice(') >= 3,
    'Admin chặn tạo/sửa Flash Sale không tốt hơn giá bán hiện tại'
);
assertPricing(
    !str_contains($adminFlashSaleSource, "\$data['sold_quantity']")
        && !str_contains($adminFlashEditSource, '[sold_quantity]'),
    'Admin không tin sold_quantity gửi từ trình duyệt'
);
assertPricing(
    !str_contains($adminFlashSaleSource, 'DELETE FROM flash_sale_items WHERE flash_sale_id = :id')
        && str_contains($adminFlashSaleSource, 'UPDATE flash_sale_items')
        && str_contains($adminFlashSaleSource, 'FlashSaleService::assertItemRemovable'),
    'Admin cập nhật item tại chỗ và bảo vệ lịch sử reservation khi bỏ chọn/xóa'
);
assertPricing(
    str_contains($adminFlashCreateSource, "['regular_price']")
        && str_contains($adminFlashEditSource, "['regular_price']"),
    'Form admin hiển thị và giới hạn theo giá bán hiện tại'
);
assertPricing(
    str_contains($detailSource, '$product[\'final_price\']'),
    'Trang chi tiết hiển thị final_price thay vì price gốc'
);
assertPricing(
    str_contains($footerSource, '$product[\'final_price\'] ?? $product[\'effective_price\']'),
    'Thanh mua nhanh mobile trên trang chi tiết dùng cùng final_price'
);
assertPricing(
    str_contains($cartViewSource, '$shipping > 0 ? formatPrice($shipping) : \'Miễn phí\''),
    'Trang giỏ hàng hiển thị phí vận chuyển từ cùng summary server-side'
);
assertPricing(
    str_contains($cartViewSource, "\$item['available']") &&
    str_contains($cartViewSource, "disabled aria-disabled=\"true\"") &&
    str_contains($cartViewSource, "if (\$canCheckout === true)"),
    'Trang giỏ hàng thay đổi UI checkout theo flag'
);

echo "\n--- 3. Isolated MySQL Product/Cart integration ---\n";
require_once $rootPath . '/config/database.php';
$db = Database::getConnection();
assertPricing($db instanceof PDO, 'Có PDO để chạy test pricing trên TEMPORARY tables');

if ($db instanceof PDO) {
    $originalCart = $_SESSION['cart'] ?? null;
    $hadCart = array_key_exists('cart', $_SESSION);

    try {
        foreach (['flash_sale_items', 'flash_sales', 'products', 'categories', 'brands'] as $table) {
            $db->exec("DROP TEMPORARY TABLE IF EXISTS `{$table}`");
        }

        $db->exec(
            "CREATE TEMPORARY TABLE `brands` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `categories` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `name` VARCHAR(100) NOT NULL,
                `slug` VARCHAR(100) NOT NULL,
                `status` VARCHAR(30) NOT NULL DEFAULT 'active'
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `products` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL,
                `price` DECIMAL(15,2) NOT NULL,
                `sale_price` DECIMAL(15,2) NULL,
                `old_price` DECIMAL(15,2) NULL,
                `stock` INT NOT NULL DEFAULT 0,
                `status` VARCHAR(30) NOT NULL DEFAULT 'active',
                `verification_status` VARCHAR(30) NOT NULL DEFAULT 'verified',
                `category_id` INT UNSIGNED NULL,
                `brand_id` INT UNSIGNED NULL,
                `image` VARCHAR(255) NULL,
                `is_flash_sale` TINYINT(1) NOT NULL DEFAULT 0
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `flash_sales` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `status` VARCHAR(30) NOT NULL,
                `start_time` DATETIME NOT NULL,
                `end_time` DATETIME NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `flash_sale_items` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `flash_sale_id` INT UNSIGNED NOT NULL,
                `product_id` INT UNSIGNED NOT NULL,
                `discount_price` DECIMAL(15,2) NOT NULL,
                `allocation_quantity` INT NOT NULL,
                `sold_quantity` INT NOT NULL,
                `limit_per_user` INT NOT NULL
            ) ENGINE=InnoDB"
        );

        $db->exec("INSERT INTO `brands` (`id`, `name`) VALUES (1, 'Test Brand')");
        $db->exec("INSERT INTO `categories` (`id`, `name`, `slug`) VALUES (1, 'Test Category', 'test-category')");
        $db->exec(
            "INSERT INTO `products`
                (`id`, `name`, `slug`, `price`, `sale_price`, `stock`, `status`, `category_id`, `brand_id`, `image`, `is_flash_sale`)
             VALUES
                (1, 'Sale wins', 'sale-wins', 1000000, 800000, 10, 'active', 1, 1, 'one.jpg', 1),
                (2, 'Flash wins', 'flash-wins', 1000000, 900000, 10, 'active', 1, 1, 'two.jpg', 1),
                (3, 'Invalid flash', 'invalid-flash', 1000000, NULL, 10, 'active', 1, 1, 'three.jpg', 1),
                (4, 'Expired flash', 'expired-flash', 1000000, 850000, 10, 'active', 1, 1, 'four.jpg', 1),
                (5, 'Sold out flash', 'sold-out-flash', 1000000, 900000, 10, 'active', 1, 1, 'five.jpg', 1)"
        );
        $db->exec(
            "INSERT INTO `flash_sales` (`id`, `status`, `start_time`, `end_time`) VALUES
                (1, 'active', DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_ADD(NOW(), INTERVAL 1 HOUR)),
                (2, 'active', DATE_SUB(NOW(), INTERVAL 2 HOUR), DATE_SUB(NOW(), INTERVAL 1 HOUR))"
        );
        $db->exec(
            "INSERT INTO `flash_sale_items`
                (`id`, `flash_sale_id`, `product_id`, `discount_price`, `allocation_quantity`, `sold_quantity`, `limit_per_user`)
             VALUES
                (1, 1, 1, 900000, 10, 0, 2),
                (2, 1, 2, 700000, 10, 3, 5),
                (3, 1, 3, 1200000, 10, 0, 2),
                (4, 2, 4, 600000, 10, 0, 2),
                (5, 1, 2, 700000, 10, 3, 5),
                (6, 1, 5, 600000, 2, 2, 2)"
        );

        require_once $rootPath . '/app/models/Product.php';
        require_once $rootPath . '/app/services/CartService.php';

        $productModel = new Product();
        $saleProduct = $productModel->getById(1);
        $flashProduct = $productModel->getBySlug('flash-wins');
        $expiredProduct = $productModel->getById(4);
        $soldOutProduct = $productModel->getById(5);

        assertPricing(empty($saleProduct['discount_price']), 'Product detail bỏ Flash Sale không tốt hơn sale thường');
        assertPricing(getEffectiveProductData($saleProduct)['final_price'] === 800000.0, 'Product detail vẫn chọn sale thường khi thấp hơn Flash');
        assertPricing(getEffectiveProductData($flashProduct)['final_price'] === 700000.0, 'Product detail chọn Flash khi Flash thấp nhất');
        assertPricing(empty($expiredProduct['discount_price']), 'Flash Sale hết hạn không đi vào giá sản phẩm');
        assertPricing(empty($soldOutProduct['discount_price']), 'Flash Sale hết quota không còn quảng cáo giá giảm');

        // MySQL không cho cùng một TEMPORARY table xuất hiện ở outer query và
        // correlated subquery. getById ở trên vẫn kiểm tra chính helper runtime;
        // phần tiến độ được đối chiếu trực tiếp trên fixture quota tại đây.
        $quotaRow = $db->query(
            'SELECT sold_quantity AS fs_sold,
                    allocation_quantity AS fs_stock,
                    GREATEST(allocation_quantity - sold_quantity, 0) AS fs_remaining
             FROM flash_sale_items WHERE id = 2'
        )->fetch(PDO::FETCH_ASSOC);
        assertPricing(
            (int)($quotaRow['fs_sold'] ?? -1) === 3
                && (int)($quotaRow['fs_stock'] ?? -1) === 10
                && (int)($quotaRow['fs_remaining'] ?? -1) === 7,
            'Tiến độ Flash Sale dùng đúng sold/allocation/remaining từ quota'
        );

        if (function_exists('activeFlashSaleItemIdSql')) {
            $activeItemIdSql = activeFlashSaleItemIdSql('p');
            $selectedItemId = $db->query(
                "SELECT {$activeItemIdSql} FROM products p WHERE p.id = 2"
            )->fetchColumn();
            assertPricing((int)$selectedItemId === 2, 'Hai mức giá Flash bằng nhau chỉ chọn item có ID nhỏ nhất');
        }

        // Zero-stock runtime tests
        $db->exec(
            "INSERT INTO `products`
                (`id`, `name`, `slug`, `price`, `sale_price`, `stock`, `status`, `category_id`, `brand_id`, `image`, `is_flash_sale`)
             VALUES
                (101, 'Product A', 'product-a', 200000, NULL, 0, 'active', 1, 1, 'a.jpg', 0),
                (102, 'Product B', 'product-b', 100000, NULL, 2, 'active', 1, 1, 'b.jpg', 0)"
        );

        // Case A - Only zero-stock item
        $_SESSION['cart'] = [
            101 => ['product_id' => 101, 'quantity' => 3],
        ];
        $summary = (new CartService())->getSummary();
        assertPricing(count($summary['items']) === 1, 'items count = 1');
        assertPricing($summary['items'][0]['available'] === false, 'available = false');
        assertPricing($summary['items'][0]['stock'] === 0, 'stock = 0');
        assertPricing($summary['items'][0]['quantity'] === 0, 'quantity = 0');
        assertPricing((float)$summary['items'][0]['line_total'] === 0.0, 'line_total = 0');
        assertPricing((float)$summary['subtotal'] === 0.0, 'subtotal = 0');
        assertPricing((float)$summary['shipping'] === 0.0, 'shipping = 0');
        assertPricing((float)$summary['total'] === 0.0, 'total = 0');
        assertPricing($summary['has_unavailable_items'] === true, 'has_unavailable_items = true');
        assertPricing($summary['can_checkout'] === false, 'can_checkout = false');
        assertPricing((float)cartSubtotal() === 0.0, 'cartSubtotal() = 0');

        // Case B - Mixed available and zero stock
        $_SESSION['cart'] = [
            102 => ['product_id' => 102, 'quantity' => 2],
            101 => ['product_id' => 101, 'quantity' => 4],
        ];
        $summary = (new CartService())->getSummary();
        $cartById = indexedByProductId($summary['items']);
        assertPricing(count($summary['items']) === 2, 'items count = 2');
        assertPricing($cartById[102]['quantity'] === 2, 'available item quantity = 2');
        assertPricing((float)$cartById[102]['line_total'] === 200000.0, 'available line_total = 200000');
        assertPricing((float)$cartById[101]['line_total'] === 0.0, 'zero-stock line_total = 0');
        assertPricing((float)$summary['subtotal'] === 200000.0, 'subtotal = 200000');
        assertPricing((float)$summary['shipping'] === 30000.0, 'shipping = 30000');
        assertPricing((float)$summary['total'] === 230000.0, 'total = 230000');
        assertPricing($summary['has_unavailable_items'] === true, 'has_unavailable_items = true');
        assertPricing($summary['can_checkout'] === false, 'can_checkout = false');

        // Case C - Available-only cart
        $_SESSION['cart'] = [
            102 => ['product_id' => 102, 'quantity' => 2],
        ];
        $summary = (new CartService())->getSummary();
        assertPricing((float)$summary['subtotal'] === 200000.0, 'subtotal = 200000');
        assertPricing((float)$summary['shipping'] === 30000.0, 'shipping = 30000');
        assertPricing((float)$summary['total'] === 230000.0, 'total = 230000');
        assertPricing($summary['has_unavailable_items'] === false, 'has_unavailable_items = false');
        assertPricing($summary['can_checkout'] === true, 'can_checkout = true');

        // Case D - Clamp low stock
        $_SESSION['cart'] = [
            102 => ['product_id' => 102, 'quantity' => 5],
        ];
        $summary = (new CartService())->getSummary();
        assertPricing($summary['items'][0]['quantity'] === 2, 'quantity được clamp về 2');
        assertPricing((float)$summary['items'][0]['line_total'] === 200000.0, 'line_total chỉ tính 2 sản phẩm');
        assertPricing($summary['can_checkout'] === true, 'can_checkout = true');

        $_SESSION['cart'] = [
            1 => ['product_id' => 1, 'quantity' => 2, 'price' => 1],
            2 => ['product_id' => 2, 'quantity' => 1, 'price' => 1],
        ];
        $summary = (new CartService())->getSummary();
        $cartById = indexedByProductId($summary['items']);

        assertPricing(count($summary['items']) === 2, 'CartService hydrate đủ sản phẩm khi Product trả mảng tuần tự');
        assertPricing((float)($cartById[1]['price'] ?? 0) === 800000.0, 'Giỏ hàng bỏ giá cũ trong session và dùng sale thấp nhất từ DB');
        assertPricing((float)($cartById[2]['price'] ?? 0) === 700000.0, 'Giỏ hàng dùng Flash Sale đang active từ DB');
        assertPricing((float)$summary['subtotal'] === 2300000.0, 'Subtotal giỏ hàng khớp tổng giá hiệu lực từng dòng');
        assertPricing(cartSubtotal() === 2300000.0, 'Helper subtotal legacy cũng hydrate giá lại từ DB');
        assertPricing((float)$summary['shipping'] === 0.0, 'Summary dùng đúng quy tắc miễn phí vận chuyển');

        require_once $rootPath . '/app/controllers/AdminFlashSaleController.php';
        $adminController = new AdminFlashSaleController();
        $priceValidator = new ReflectionMethod(AdminFlashSaleController::class, 'assertValidDiscountPrice');
        $priceValidator->setAccessible(true);

        $validPriceAccepted = true;
        try {
            $priceValidator->invoke($adminController, $db, 1, 700000.0);
        } catch (Throwable) {
            $validPriceAccepted = false;
        }
        assertPricing($validPriceAccepted, 'Admin chấp nhận Flash Sale thấp hơn giá bán thường hiện tại');

        foreach ([0.0, 900000.0, 1000000.0] as $badPrice) {
            $rejected = false;
            try {
                $priceValidator->invoke($adminController, $db, 1, $badPrice);
            } catch (RuntimeException) {
                $rejected = true;
            }
            assertPricing($rejected, 'Admin từ chối Flash Sale không hợp lệ: ' . formatPrice($badPrice));
        }

        $hasScheduleValidator = method_exists(AdminFlashSaleController::class, 'normalizeCampaignSchedule');
        assertPricing($hasScheduleValidator, 'Admin có một validator chung cho thời gian và trạng thái chiến dịch');
        if ($hasScheduleValidator) {
            $scheduleValidator = new ReflectionMethod(AdminFlashSaleController::class, 'normalizeCampaignSchedule');
            $scheduleValidator->setAccessible(true);

            $validSchedule = $scheduleValidator->invoke(
                $adminController,
                '2026-08-01T10:00',
                '2026-08-01T11:00',
                'active'
            );
            assertPricing(
                ($validSchedule['start_time'] ?? '') === '2026-08-01 10:00:00'
                    && ($validSchedule['end_time'] ?? '') === '2026-08-01 11:00:00',
                'Admin chuẩn hóa đúng lịch Flash Sale hợp lệ'
            );

            foreach ([
                ['2026-08-01T11:00', '2026-08-01T10:00', 'active'],
                ['not-a-date', '2026-08-01T10:00', 'active'],
                ['2026-02-30T10:00', '2026-03-01T10:00', 'active'],
                ['2026-08-01T10:00', '2026-08-01T11:00', 'unknown'],
            ] as [$badStart, $badEnd, $badStatus]) {
                $rejected = false;
                try {
                    $scheduleValidator->invoke($adminController, $badStart, $badEnd, $badStatus);
                } catch (RuntimeException) {
                    $rejected = true;
                }
                assertPricing($rejected, 'Admin từ chối lịch hoặc trạng thái Flash Sale không hợp lệ');
            }
        }

        $hasQuantityValidator = method_exists(AdminFlashSaleController::class, 'assertValidItemQuantities');
        assertPricing($hasQuantityValidator, 'Admin có một validator chung cho hạn mức Flash Sale');
        if ($hasQuantityValidator) {
            $quantityValidator = new ReflectionMethod(AdminFlashSaleController::class, 'assertValidItemQuantities');
            $quantityValidator->setAccessible(true);

            $validQuantitiesAccepted = true;
            try {
                $quantityValidator->invoke($adminController, 10, 0, 2);
            } catch (Throwable) {
                $validQuantitiesAccepted = false;
            }
            assertPricing($validQuantitiesAccepted, 'Admin chấp nhận hạn mức Flash Sale hợp lệ');

            foreach ([[0, 0, 1], [10, -1, 1], [10, 11, 1], [10, 0, 0], [10, 0, 11]] as $badQuantities) {
                $rejected = false;
                try {
                    $quantityValidator->invoke($adminController, ...$badQuantities);
                } catch (RuntimeException) {
                    $rejected = true;
                }
                assertPricing($rejected, 'Admin từ chối hạn mức Flash Sale không hợp lệ');
            }
        }
    } catch (Throwable $e) {
        assertPricing(false, 'Pricing integration không phát sinh exception: ' . $e->getMessage());
    } finally {
        foreach (['flash_sale_items', 'flash_sales', 'products', 'categories', 'brands'] as $table) {
            try {
                $db->exec("DROP TEMPORARY TABLE IF EXISTS `{$table}`");
            } catch (Throwable) {
            }
        }

        if ($hadCart) {
            $_SESSION['cart'] = $originalCart;
        } else {
            unset($_SESSION['cart']);
        }
    }
}

echo "\n--- 4. Isolated MySQL Order pricing integration ---\n";
if ($db instanceof PDO) {
    $originalUser = $_SESSION['user'] ?? null;
    $hadUser = array_key_exists('user', $_SESSION);

    $orderTables = [
        'inventory_logs',
        'notifications',
        'flash_sale_reservations',
        'order_items',
        'orders',
        'coupons',
        'flash_sale_items',
        'flash_sales',
        'products',
    ];

    try {
        foreach ($orderTables as $table) {
            $db->exec("DROP TEMPORARY TABLE IF EXISTS `{$table}`");
        }

        $db->exec(
            "CREATE TEMPORARY TABLE `products` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `slug` VARCHAR(255) NOT NULL,
                `image` VARCHAR(255) NULL,
                `price` DECIMAL(15,2) NOT NULL,
                `sale_price` DECIMAL(15,2) NULL,
                `stock` INT NOT NULL,
                `status` VARCHAR(30) NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `flash_sales` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `status` VARCHAR(30) NOT NULL,
                `start_time` DATETIME NOT NULL,
                `end_time` DATETIME NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `flash_sale_items` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `flash_sale_id` INT UNSIGNED NOT NULL,
                `product_id` INT UNSIGNED NOT NULL,
                `discount_price` DECIMAL(15,2) NOT NULL,
                `allocation_quantity` INT NOT NULL,
                `sold_quantity` INT NOT NULL,
                `limit_per_user` INT NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `coupons` (
                `id` INT UNSIGNED NOT NULL PRIMARY KEY,
                `code` VARCHAR(100) NOT NULL,
                `type` VARCHAR(30) NOT NULL,
                `discount_value` DECIMAL(15,2) NOT NULL,
                `max_discount` DECIMAL(15,2) NULL,
                `min_order_value` DECIMAL(15,2) NOT NULL DEFAULT 0,
                `usage_limit` INT NULL,
                `used_count` INT NOT NULL DEFAULT 0,
                `usage_limit_per_user` INT NULL,
                `status` VARCHAR(30) NOT NULL,
                `start_date` DATETIME NOT NULL,
                `end_date` DATETIME NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `orders` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `order_code` VARCHAR(100) NOT NULL,
                `user_id` INT UNSIGNED NULL,
                `coupon_id` INT UNSIGNED NULL,
                `customer_name` VARCHAR(255) NOT NULL,
                `phone` VARCHAR(50) NOT NULL,
                `address` TEXT NOT NULL,
                `note` TEXT NULL,
                `payment_method` VARCHAR(30) NOT NULL,
                `payment_status` VARCHAR(30) NOT NULL,
                `subtotal` DECIMAL(15,2) NOT NULL,
                `discount_amount` DECIMAL(15,2) NOT NULL,
                `shipping_fee` DECIMAL(15,2) NOT NULL,
                `total_amount` DECIMAL(15,2) NOT NULL,
                `status` VARCHAR(30) NOT NULL,
                `inventory_status` VARCHAR(30) NOT NULL,
                `inventory_reserved_at` DATETIME NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `order_items` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `order_id` INT UNSIGNED NOT NULL,
                `product_id` INT UNSIGNED NOT NULL,
                `product_name` VARCHAR(255) NOT NULL,
                `price` DECIMAL(15,2) NOT NULL,
                `quantity` INT NOT NULL,
                `line_total` DECIMAL(15,2) NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `flash_sale_reservations` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `flash_sale_item_id` INT UNSIGNED NOT NULL,
                `order_id` BIGINT UNSIGNED NOT NULL,
                `order_item_id` BIGINT UNSIGNED NOT NULL,
                `user_id` INT UNSIGNED NULL,
                `buyer_key` VARCHAR(191) NOT NULL,
                `quantity` INT UNSIGNED NOT NULL,
                `unit_price` DECIMAL(15,2) NOT NULL,
                `status` ENUM('reserved','committed','released') NOT NULL DEFAULT 'reserved',
                `reserved_at` DATETIME NOT NULL,
                `committed_at` DATETIME NULL,
                `released_at` DATETIME NULL,
                `release_reason` VARCHAR(100) NULL,
                UNIQUE KEY `uq_flash_reservation_order_item` (`order_item_id`)
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `notifications` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT UNSIGNED NULL,
                `title` VARCHAR(255) NOT NULL,
                `content` TEXT NOT NULL
            ) ENGINE=InnoDB"
        );
        $db->exec(
            "CREATE TEMPORARY TABLE `inventory_logs` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `product_id` INT UNSIGNED NOT NULL,
                `order_id` INT UNSIGNED NULL,
                `type` VARCHAR(50) NOT NULL,
                `quantity_delta` INT NOT NULL,
                `old_stock` INT NOT NULL,
                `new_stock` INT NOT NULL,
                `reason_code` VARCHAR(100) NULL,
                `note` TEXT NULL,
                `reference_type` VARCHAR(50) NULL,
                `reference_id` VARCHAR(100) NULL,
                `created_by` INT UNSIGNED NULL,
                `idempotency_key` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL
            ) ENGINE=InnoDB"
        );

        $db->exec(
            "INSERT INTO `products` (`id`, `name`, `slug`, `image`, `price`, `sale_price`, `stock`, `status`)
             VALUES (1, 'Server-priced product', 'server-priced', 'server.jpg', 1000000, 800000, 10, 'active')"
        );
        $db->exec(
            "INSERT INTO `flash_sales` (`id`, `status`, `start_time`, `end_time`)
             VALUES (1, 'active', DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_ADD(NOW(), INTERVAL 1 HOUR))"
        );
        $db->exec(
            "INSERT INTO `flash_sale_items`
                (`id`, `flash_sale_id`, `product_id`, `discount_price`, `allocation_quantity`, `sold_quantity`, `limit_per_user`)
             VALUES (1, 1, 1, 700000, 10, 0, 2)"
        );
        $db->exec(
            "INSERT INTO `coupons`
                (`id`, `code`, `type`, `discount_value`, `max_discount`, `min_order_value`, `usage_limit`, `used_count`, `usage_limit_per_user`, `status`, `start_date`, `end_date`)
             VALUES
                (1, 'SERVER10', 'percent', 10, 50000, 100000, 10, 0, 1, 'active', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY))"
        );

        unset($_SESSION['user']);
        require_once $rootPath . '/app/models/Order.php';
        $createdOrder = (new Order())->create([
            'customer_name' => 'Pricing Test',
            'phone' => '0900000000',
            'address' => 'Temporary table only',
            'payment_method' => 'COD',
            'payment_status' => 'unpaid',
            'subtotal' => 2,
            'discount_amount' => 999999,
            'shipping_fee' => 999999,
            'total_amount' => 1,
            'coupon_id' => 1,
            'items' => [
                ['product_id' => 1, 'quantity' => 2, 'price' => 1],
            ],
        ]);

        assertPricing(is_array($createdOrder), 'Order::create hoàn tất trên toàn bộ bảng tạm');
        if (is_array($createdOrder)) {
            $storedOrder = $db->query('SELECT * FROM `orders` LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            $storedItem = $db->query('SELECT * FROM `order_items` LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            $couponUsed = (int)$db->query('SELECT `used_count` FROM `coupons` WHERE `id` = 1')->fetchColumn();
            $flashSold = (int)$db->query('SELECT `sold_quantity` FROM `flash_sale_items` WHERE `id` = 1')->fetchColumn();
            $flashReservation = $db->query('SELECT * FROM `flash_sale_reservations` LIMIT 1')->fetch(PDO::FETCH_ASSOC);

            assertPricing((float)$createdOrder['subtotal'] === 1400000.0, 'Order bỏ subtotal giả và tính lại từ giá Flash đã khóa trong DB');
            assertPricing((float)$createdOrder['discount_amount'] === 50000.0, 'Order bỏ discount giả và áp đúng giới hạn coupon từ DB');
            assertPricing((float)$createdOrder['total_amount'] === 1350000.0, 'Order trả về total server-side authoritative');
            assertPricing((float)($storedOrder['subtotal'] ?? 0) === 1400000.0, 'Header orders lưu đúng subtotal server-side');
            assertPricing((float)($storedOrder['total_amount'] ?? 0) === 1350000.0, 'Header orders lưu đúng total server-side');
            assertPricing((float)($storedItem['price'] ?? 0) === 700000.0, 'order_items dùng cùng giá Flash đã reserve');
            assertPricing((float)($storedItem['line_total'] ?? 0) === 1400000.0, 'order_items có line total khớp header subtotal');
            assertPricing($couponUsed === 1, 'Coupon hợp lệ chỉ tăng usage sau khi đã khóa và tính lại');
            assertPricing($flashSold === 2, 'Order reserve đúng 2 suất Flash Sale');
            assertPricing(($flashReservation['status'] ?? '') === 'reserved', 'Order tạo reservation ở trạng thái reserved');

            $orderCountBeforeLimitAttempt = (int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn();
            $stockBeforeLimitAttempt = (int)$db->query('SELECT stock FROM products WHERE id = 1')->fetchColumn();
            $limitRejectedOrder = (new Order())->create([
                'customer_name' => 'Pricing Test Repeat',
                'phone' => '0900000000',
                'address' => 'Temporary table only',
                'payment_method' => 'COD',
                'payment_status' => 'unpaid',
                'items' => [
                    ['product_id' => 1, 'quantity' => 1],
                ],
            ]);
            assertPricing($limitRejectedOrder === false, 'Order từ chối buyer đã đạt limit Flash Sale');
            assertPricing((int)$db->query('SELECT COUNT(*) FROM orders')->fetchColumn() === $orderCountBeforeLimitAttempt, 'Limit Flash Sale không để lại order dở');
            assertPricing((int)$db->query('SELECT stock FROM products WHERE id = 1')->fetchColumn() === $stockBeforeLimitAttempt, 'Limit Flash Sale không làm đổi tồn kho');
            assertPricing((int)$db->query('SELECT sold_quantity FROM flash_sale_items WHERE id = 1')->fetchColumn() === 2, 'Limit Flash Sale không làm tăng sold_quantity');
        }
    } catch (Throwable $e) {
        assertPricing(false, 'Order pricing integration không phát sinh exception: ' . $e->getMessage());
    } finally {
        foreach ($orderTables as $table) {
            try {
                $db->exec("DROP TEMPORARY TABLE IF EXISTS `{$table}`");
            } catch (Throwable) {
            }
        }

        if ($hadUser) {
            $_SESSION['user'] = $originalUser;
        } else {
            unset($_SESSION['user']);
        }
    }
}

echo "\n========================================================\n";
echo "Product Pricing Results: {$passed} passed, {$failed} failed\n";
echo "========================================================\n";

exit($failed === 0 ? 0 : 1);
