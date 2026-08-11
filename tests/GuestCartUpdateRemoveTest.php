<?php

define('ROOT_PATH', dirname(__DIR__));
define('BASE_URL', 'http://techpilot.test');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/services/CartService.php';
require_once ROOT_PATH . '/app/models/Product.php';
require_once ROOT_PATH . '/app/core/helpers.php';

$_SESSION = [];
$_SERVER = [];
$_POST = [];
$_GET = [];

class GuestCartUpdateRemoveTest
{
    private $db;
    private $passed = 0;
    private $failed = 0;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    private function log(string $message): void
    {
        echo $message . "\n";
    }

    private function assert(bool $condition, string $description, ?string $failureDetail = null): void
    {
        if ($condition) {
            $this->passed++;
            $this->log("[PASS] " . $description);
        } else {
            $this->failed++;
            $detail = $failureDetail ? " ($failureDetail)" : "";
            $this->log("[FAIL] " . $description . $detail);
        }
    }

    public function run(): void
    {
        $this->log("========================================================");
        $this->log("=== GUEST CART UPDATE & REMOVE TEST                  ===");
        $this->log("========================================================\n");

        $_SESSION['guest_cart'] = [];
        unset($_SESSION['user']);
        $service = new CartService();

        $stmt = $this->db->query("SELECT id, stock FROM products WHERE status = 'active' AND stock > 5 ORDER BY id DESC LIMIT 1");
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        $pid = (int)$product['id'];
        $stock = (int)$product['stock'];

        // 1. Store item for guest
        $res = $service->storeGuestItem($pid, 6, $stock);
        $this->assert($res['ok'] === true, 'Guest add sản phẩm số lượng 6');
        $this->assert($_SESSION['guest_cart'][$pid]['quantity'] === 6, 'Giỏ hàng có 6 sản phẩm');

        // 2. Decrease quantity for guest (6 -> 5)
        $resUpdate = $service->updateGuestItem($pid, 5, $stock);
        $this->assert($resUpdate['ok'] === true, 'Guest giảm số lượng xuống 5');
        $this->assert($_SESSION['guest_cart'][$pid]['quantity'] === 5, 'Giỏ hàng còn đúng 5 sản phẩm');

        // 3. Increase quantity for guest (5 -> 7)
        $resUpdate2 = $service->updateGuestItem($pid, 7, $stock);
        $this->assert($resUpdate2['ok'] === true, 'Guest tăng số lượng lên 7');
        $this->assert($_SESSION['guest_cart'][$pid]['quantity'] === 7, 'Giỏ hàng có đúng 7 sản phẩm');

        // 4. Decrease to 0 removes item
        $resZero = $service->updateGuestItem($pid, 0, $stock);
        $this->assert($resZero['ok'] === true, 'Guest giảm số lượng về 0');
        $this->assert(!isset($_SESSION['guest_cart'][$pid]), 'Sản phẩm đã bị xóa khỏi guest_cart');

        // 5. Remove item explicit
        $service->storeGuestItem($pid, 3, $stock);
        $this->assert(isset($_SESSION['guest_cart'][$pid]), 'Thêm lại 3 sản phẩm');
        $service->removeGuestItem($pid);
        $this->assert(!isset($_SESSION['guest_cart'][$pid]), 'removeGuestItem xóa sản phẩm thành công');

        $this->log("\nResults: {$this->passed} passed, {$this->failed} failed\n");
        if ($this->failed > 0) {
            exit(1);
        }
    }
}

$test = new GuestCartUpdateRemoveTest();
$test->run();
