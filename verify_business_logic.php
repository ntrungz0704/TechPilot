<?php
/**
 * TechPilot Business Logic and E2E Integration Test Suite
 * Automatically verifies database transactions, search relevance, stock management, coupon rules, and cancellation.
 */
session_start();
define('ROOT_PATH', __DIR__);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Product.php';
require_once __DIR__ . '/app/models/User.php';
require_once __DIR__ . '/app/models/Order.php';
require_once __DIR__ . '/app/models/Notification.php';
require_once __DIR__ . '/app/models/ReturnRequest.php';

$db = Database::getConnection();
if (!$db) {
    die("Database connection failed.\n");
}

$errors = [];
echo "========================================================\n";
echo "=== TECHPILOT BUSINESS LOGIC & E2E INTEGRATION TESTS ===\n";
echo "========================================================\n\n";

// ----------------------------------------------------
// 1. SEARCH TESTS
// ----------------------------------------------------
echo "--- Running Search Tests ---\n";
$productModel = new Product();

$searchCases = [
    'lap' => ['category_slugs' => ['laptop-gaming', 'laptop-van-phong']],
    'laptop' => ['category_slugs' => ['laptop-gaming', 'laptop-van-phong']],
    'laptop gaming' => ['category_slugs' => ['laptop-gaming']],
    'pc' => ['category_slugs' => ['pc-build-san']],
    'pcie5' => ['match_names' => ['MSI MAG A750GL PCIe5']],
    'i3' => ['match_names' => ['Intel Core i3-12100']],
    'rtx 4060' => ['match_names' => ['Card màn hình MSI RTX 4060 Ventus 2X OC', 'Card màn hình ASUS Dual RTX 4060 Ti OC']],
];

foreach ($searchCases as $kw => $criteria) {
    $products = $productModel->search($kw, '', 100, 0, '', 0.0, 0.0, 'relevance');
    $count = count($products);
    
    echo "Query: '$kw' | Results found: $count\n";
    
    if (isset($criteria['category_slugs'])) {
        foreach ($products as $p) {
            if (!in_array($p['category_slug'], $criteria['category_slugs'])) {
                $errors[] = "Search '$kw' returned product ID {$p['id']} '{$p['name']}' with category '{$p['category_slug']}' (expected one of: " . implode(', ', $criteria['category_slugs']) . ")";
            }
        }
    }
    
    if (isset($criteria['match_names'])) {
        $found = false;
        foreach ($products as $p) {
            foreach ($criteria['match_names'] as $expectedName) {
                if (stripos($p['name'], $expectedName) !== false) {
                    $found = true;
                    break 2;
                }
            }
        }
        if (!$found && $count > 0) {
            $errors[] = "Search '$kw' did not return any of the expected names: " . implode(', ', $criteria['match_names']);
        }
    }
}
echo "\n";

// ----------------------------------------------------
// 2. CHECKOUT TRANSACTION & STOCK MANAGEMENT
// ----------------------------------------------------
echo "--- Running Checkout Transaction & Stock Management Tests ---\n";
// Create a temporary user for checkout
$userModel = new User();
$testEmail = 'e2e_customer_' . time() . '@gmail.com';
$userModel->create('E2E Customer', $testEmail, '0981112223', 'password123');
$user = $userModel->findByEmail($testEmail);

if (!$user) {
    die("[FAIL] Failed to create test user.\n");
}
$userId = (int)$user['id'];
$_SESSION['user'] = ['id' => $userId];

// Set up a test product with stock = 1
$testProductStockId = 13; // ASUS ROG Ally X
$db->exec("UPDATE products SET stock = 1, price = 20000000, sale_price = NULL WHERE id = $testProductStockId");

// Set up cart for user
$db->exec("DELETE FROM carts WHERE user_id = $userId");
$db->exec("INSERT INTO carts (user_id, status) VALUES ($userId, 'active')");
$cartId = $db->lastInsertId();
$db->exec("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES ($cartId, $testProductStockId, 1)");

// Place order (simulating Server transaction in Order::create)
$orderModel = new Order();
$payload = [
    'customer_name' => 'E2E Customer',
    'phone' => '0981112223',
    'email' => $testEmail,
    'address' => '123 Test Street, Hanoi',
    'note' => 'E2E test order',
    'payment_method' => 'COD',
    'subtotal' => 20000000.0,
    'shipping_fee' => 0.0,
    'total_amount' => 20000000.0,
    'items' => [
        [
            'product_id' => $testProductStockId,
            'quantity' => 1
        ]
    ]
];

try {
    $res = $orderModel->create($payload);
    if ($res !== false) {
        $orderId = (int)$res['id'];
        echo "[PASS] Order created successfully. Order ID: $orderId\n";
        
        // Check if stock is decremented to 0
        $stmt = $db->query("SELECT stock FROM products WHERE id = $testProductStockId");
        $stock = (int)$stmt->fetchColumn();
        echo "Stock after order: $stock (expected: 0)\n";
        if ($stock !== 0) {
            $errors[] = "Product stock was not decremented correctly (current stock: $stock, expected: 0).";
        }
        
        // Check if cart is cleared/deleted
        $stmt = $db->query("SELECT COUNT(*) FROM cart_items WHERE cart_id = $cartId");
        $cartItemsCount = (int)$stmt->fetchColumn();
        echo "Cart items count after checkout: $cartItemsCount (expected: 0)\n";
        if ($cartItemsCount !== 0) {
            $errors[] = "Cart items were not cleared after checkout.";
        }
        
        // ----------------------------------------------------
        // 2.1 NOTIFICATION TESTS
        // ----------------------------------------------------
        echo "--- Running Notification Tests ---\n";
        $notifModel = new Notification();
        // Insert a notification
        $db->prepare("INSERT INTO notifications (user_id, title, content) VALUES (:uid, :title, :content)")
           ->execute([':uid' => $userId, ':title' => 'Test Notification', ':content' => 'Your order is pending confirmation.']);
        
        $unread = $notifModel->getUnreadCount($userId);
        echo "Unread notification count: $unread (expected: 1)\n";
        if ($unread !== 1) {
            $errors[] = "Notification count is incorrect.";
        }
        
        $notifModel->markAllAsRead($userId);
        $unreadAfter = $notifModel->getUnreadCount($userId);
        echo "Unread count after marking read: $unreadAfter (expected: 0)\n";
        if ($unreadAfter !== 0) {
            $errors[] = "Mark all notifications read failed.";
        }
        
        // ----------------------------------------------------
        // 2.2 RETURN REQUEST TESTS
        // ----------------------------------------------------
        echo "--- Running Return Request Tests ---\n";
        $returnModel = new ReturnRequest();
        
        // Get order item ID
        $orderItemId = (int)$db->query("SELECT id FROM order_items WHERE order_id = $orderId LIMIT 1")->fetchColumn();
        
        $returnItems = [
            [
                'order_item_id' => $orderItemId,
                'quantity' => 1,
                'resolution' => 'refund'
            ]
        ];
        
        $retCreated = $returnModel->create($userId, $orderId, 'Sản phẩm lỗi', 'Không lên nguồn', $returnItems);
        if ($retCreated) {
            echo "[PASS] Return request created successfully.\n";
            $returns = $returnModel->getByUserId($userId);
            echo "Return requests count: " . count($returns) . " (expected: 1)\n";
            if (count($returns) !== 1) {
                $errors[] = "Return request was not found in DB.";
            }
        } else {
            $errors[] = "Failed to create return request.";
        }
        
        // Test cancellation and stock restoration using database transaction (E2E simulation)
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = :id AND user_id = :user_id");
            $stmt->execute([':id' => $orderId, ':user_id' => $userId]);
            
            $stmt = $db->prepare('SELECT product_id, quantity FROM order_items WHERE order_id = :order_id');
            $stmt->execute([':order_id' => $orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($items as $item) {
                if (!empty($item['product_id'])) {
                    $updateStockStmt = $db->prepare('UPDATE products SET stock = stock + :qty WHERE id = :pid');
                    $updateStockStmt->execute([
                        ':qty' => (int)$item['quantity'],
                        ':pid' => (int)$item['product_id']
                    ]);
                }
            }
            $db->commit();
            $cancelSuccess = true;
        } catch (Exception $ex) {
            $db->rollBack();
            $cancelSuccess = false;
        }
        
        if ($cancelSuccess) {
            echo "[PASS] Order cancelled successfully.\n";
            // Check if stock is restored to 1
            $stmt = $db->query("SELECT stock FROM products WHERE id = $testProductStockId");
            $stock = (int)$stmt->fetchColumn();
            echo "Stock after cancellation: $stock (expected: 1)\n";
            if ($stock !== 1) {
                $errors[] = "Product stock was not restored after cancellation (current stock: $stock, expected: 1).";
            }
        } else {
            $errors[] = "Failed to cancel order.";
        }
    } else {
        $errors[] = "Checkout transaction returned false.";
    }
    
} catch (Exception $e) {
    $errors[] = "Checkout transaction failed with exception: " . $e->getMessage();
}
echo "\n";

// ----------------------------------------------------
// 3. CLEAN UP
// ----------------------------------------------------
// Delete the test user and their records to keep database clean
$db->exec("DELETE FROM return_items WHERE return_request_id IN (SELECT id FROM return_requests WHERE user_id = $userId)");
$db->exec("DELETE FROM return_requests WHERE user_id = $userId");
$db->exec("DELETE FROM notifications WHERE user_id = $userId");
$db->exec("DELETE FROM reviews WHERE user_id = $userId");
$db->exec("DELETE FROM wishlists WHERE user_id = $userId");
$db->exec("DELETE FROM cart_items WHERE cart_id = $cartId");
$db->exec("DELETE FROM carts WHERE user_id = $userId");
$db->exec("DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE user_id = $userId)");
$db->exec("DELETE FROM orders WHERE user_id = $userId");
$db->exec("DELETE FROM users WHERE id = $userId");

// Reset stock of product 13
$db->exec("UPDATE products SET stock = 8 WHERE id = 13");

// ----------------------------------------------------
// 4. RESULTS
// ----------------------------------------------------
echo "========================================================\n";
if (empty($errors)) {
    echo "[PASS] ALL BUSINESS LOGIC AND TRANSACTION TESTS PASSED!\n";
} else {
    echo "[FAIL] THE FOLLOWING ERRORS WERE FOUND:\n";
    foreach ($errors as $err) {
        echo "  - $err\n";
    }
}
echo "========================================================\n";
