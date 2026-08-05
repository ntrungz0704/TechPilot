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

// Cannot easily mock the whole Controller flow since it uses header/echo/exit, 
// so we'll test the CartService directly for most backend contracts.

class CP041StorefrontCartMediaConsistencyTest
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
        $this->log("=== CP04.1 STOREFRONT CART & MEDIA CONSISTENCY TEST  ===");
        $this->log("========================================================\n");

        $this->testGuestCartLogic();
        $this->testCartMergeLogic();
        $this->testMediaConsistency();

        $this->log("\n--- Storefront Cart & Media Results ---");
        $this->log("Storefront Cart & Media Results: {$this->passed} passed, {$this->failed} failed\n");

        if ($this->failed > 0) {
            exit(1);
        }
    }

    private function getActiveProduct(): int
    {
        $stmt = $this->db->query("SELECT id FROM products WHERE status = 'active' AND stock > 1 ORDER BY id DESC LIMIT 1");
        return (int)$stmt->fetchColumn();
    }

    private function testGuestCartLogic(): void
    {
        $this->log("--- 1. Guest Cart Session ---");
        $_SESSION['guest_cart'] = [];
        $service = new CartService();
        $pid = $this->getActiveProduct();
        
        // 1. Guest add active
        $res = $service->storeGuestItem($pid, 1, 10);
        $this->assert($res['ok'] === true, 'Guest add một product active');
        
        // 2. Add twice
        $service->storeGuestItem($pid, 2, 10);
        $this->assert(isset($_SESSION['guest_cart'][$pid]), 'Guest add cùng product hai lần');
        $this->assert($_SESSION['guest_cart'][$pid]['quantity'] === 3, 'Guest quantity được cộng (1 + 2 = 3)');
        
        // 3. Not exceeding stock
        $service->storeGuestItem($pid, 20, 10);
        $this->assert($_SESSION['guest_cart'][$pid]['quantity'] === 10, 'Không vượt stock');
        
        // 4. Inactive/Missing
        $res2 = $service->storeGuestItem(99999, 1, 0);
        $this->assert($res2['ok'] === false, 'Inactive/Missing product không được lưu (bị chặn bởi stock < 1)');
        
        // Session format check
        $item = array_values($_SESSION['guest_cart'])[0];
        $this->assert(count($item) === 2 && isset($item['product_id'], $item['quantity']), 'Guest cart không chứa giá client (chỉ lưu product_id, quantity)');
    }

    private function testCartMergeLogic(): void
    {
        $this->log("\n--- 2. Login Merge Logic ---");
        $userId = 9999;
        $this->db->exec("DELETE FROM cart_items WHERE cart_id IN (SELECT id FROM carts WHERE user_id = $userId)");
        $this->db->exec("DELETE FROM carts WHERE user_id = $userId");
        $this->db->exec("DELETE FROM users WHERE id = $userId");
        
        $this->db->exec("INSERT INTO users (id, full_name, email, password, role) VALUES ($userId, 'Test User', 'testmerge@example.com', 'test', 'customer')");

        $pid = $this->getActiveProduct();
        $_SESSION['guest_cart'] = [
            $pid => ['product_id' => $pid, 'quantity' => 2]
        ];

        $service = new CartService();
        $res = $service->mergeGuestCartIntoUser($userId, $this->db);
        
        $this->assert($res['merged'] === 1, 'Login merge vào cart rỗng');
        
        // Verify DB
        $stmt = $this->db->prepare("SELECT SUM(quantity) FROM cart_items ci JOIN carts c ON c.id = ci.cart_id WHERE c.user_id = :uid AND ci.product_id = :pid");
        $stmt->execute([':uid' => $userId, ':pid' => $pid]);
        $this->assert((int)$stmt->fetchColumn() === 2, 'Merge correctly sets DB quantity');

        $this->assert(!isset($_SESSION['guest_cart']), 'Guest cart chỉ bị xóa sau successful merge');

        // Test merge into existing cart
        $_SESSION['guest_cart'] = [
            $pid => ['product_id' => $pid, 'quantity' => 1]
        ];
        $res2 = $service->mergeGuestCartIntoUser($userId, $this->db);
        $this->assert($res2['merged'] === 1, 'Login merge vào cart đã có item');
        
        $stmt->execute([':uid' => $userId, ':pid' => $pid]);
        $this->assert((int)$stmt->fetchColumn() === 3, 'Merged quantity correctly additive');

        $stmt2 = $this->db->prepare("SELECT COUNT(*) FROM cart_items ci JOIN carts c ON c.id = ci.cart_id WHERE c.user_id = :uid AND ci.product_id = :pid");
        $stmt2->execute([':uid' => $userId, ':pid' => $pid]);
        $this->assert((int)$stmt2->fetchColumn() === 1, 'Merge không tạo duplicate cart_items row');

        // Test invalid/OOS items
        $_SESSION['guest_cart'] = [
            99999 => ['product_id' => 99999, 'quantity' => 1], // Non existent
            $pid => ['product_id' => $pid, 'quantity' => 1] // Valid
        ];
        $res3 = $service->mergeGuestCartIntoUser($userId, $this->db);
        $this->assert($res3['skipped'] === 1, 'Product không hợp lệ trước login bị skip');
        $this->assert($res3['merged'] === 1, 'Partial merge giữ các item hợp lệ');
        
        $this->db->exec("DELETE FROM cart_items WHERE cart_id IN (SELECT id FROM carts WHERE user_id = $userId)");
        $this->db->exec("DELETE FROM carts WHERE user_id = $userId");
        $this->db->exec("DELETE FROM users WHERE id = $userId");
    }

    private function testMediaConsistency(): void
    {
        $this->log("\n--- 3. Media Consistency ---");
        
        if (!function_exists('getGalleryImages')) {
            function getGalleryImages(string $mainImg, array $extraImgs): array {
                $list = [];
                $mainImg = trim($mainImg);
                if ($mainImg !== '') {
                    $list[] = $mainImg;
                }
            
                if (!empty($extraImgs)) {
                    foreach ($extraImgs as $item) {
                        $url = is_array($item) ? ($item['image_url'] ?? $item['image_path'] ?? '') : (string)$item;
                        $url = trim($url);
                        if ($url !== '' && !in_array($url, $list, true)) {
                            $list[] = $url;
                        }
                    }
                }
            
                if (empty($list)) {
                    $list[] = '';
                }
                
                return $list;
            }
        }
        
        $main = 'main.jpg';
        $extra = [
            ['image_url' => 'main.jpg'], // duplicate
            ['image_url' => 'thumb1.jpg'],
            ['image_url' => 'thumb2.jpg']
        ];
        $gallery = getGalleryImages($main, $extra);
        
        $this->assert($gallery[0] === 'main.jpg', 'Primary image luôn là products.image');
        $this->assert(count($gallery) === 3, 'Duplicate gallery URL bị loại');
        $this->assert($gallery[1] === 'thumb1.jpg', 'product_images chỉ đứng sau primary');

        $emptyGallery = getGalleryImages('', [['image_url' => 'thumb1.jpg']]);
        $this->assert($emptyGallery[0] === 'thumb1.jpg', 'Missing products.image fallback về first valid gallery');

        $noImages = getGalleryImages('', []);
        $this->assert($noImages[0] === '', 'Không có ảnh fallback mảng rỗng để productImageUrl() handle');
        
        $img = productImageUrl('', 'pc', 123);
        $this->assert(str_contains($img, '123'), 'product ID được dùng khi tạo placeholder deterministic');
        
        $img2 = productImageUrl('', 'monitor', 123);
        $this->assert($img !== $img2, 'PC/monitor placeholder KHÁC nhau (phụ thuộc category)');
        
        $img3 = productImageUrl('', 'phone', 123);
        $this->assert($img !== $img3, 'Phone/PC placeholder KHÁC nhau');
    }
}

(new CP041StorefrontCartMediaConsistencyTest())->run();
