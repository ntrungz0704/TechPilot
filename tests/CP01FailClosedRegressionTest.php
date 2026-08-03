<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';

class FailClosedRegressionTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];
    
    private function assert(bool $condition, string $testName, string $failureMsg = ''): void
    {
        if ($condition) {
            $this->passed++;
            echo "[PASS] {$testName}\n";
        } else {
            $this->failed++;
            $msg = "[FAIL] {$testName}" . ($failureMsg ? ": {$failureMsg}" : '');
            echo "{$msg}\n";
            $this->errors[] = $msg;
        }
    }

    private function setDbInstance(?PDO $instance)
    {
        $ref = new ReflectionClass('Database');
        $prop = $ref->getProperty('instance');
        $prop->setAccessible(true);
        $prop->setValue(null, $instance);
    }

    private function getRealDb(): PDO
    {
        $this->setDbInstance(null);
        $db = Database::getConnection();
        if (!$db) {
            throw new RuntimeException("Cannot get real DB for setup");
        }
        return $db;
    }

    public function run(): void
    {
        echo "========================================================\n";
        echo "=== TECHPILOT FAIL-CLOSED REGRESSION TEST SUITE      ===\n";
        echo "========================================================\n\n";

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->testAuthFailClosed();
        $this->testCheckoutFailClosed();
        
        $this->setDbInstance(null); // restore
        $this->testDatabaseAvailableAuth();
        $this->testDatabaseAvailableCheckout();

        echo "\n════════════════════════════════════════════════════════\n";
        echo "Fail-Closed Test Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "════════════════════════════════════════════════════════\n";

        if ($this->failed > 0) {
            exit(1);
        }
        exit(0);
    }

    private function forceDbFailure()
    {
        $this->setDbInstance(null);
        if (file_exists(ROOT_PATH . '/config/database.local.php')) {
            rename(ROOT_PATH . '/config/database.local.php', ROOT_PATH . '/config/database.local.php.bak');
        }
        putenv('DB_PORT=9999');
    }

    private function restoreDb()
    {
        putenv('DB_PORT');
        if (file_exists(ROOT_PATH . '/config/database.local.php.bak')) {
            rename(ROOT_PATH . '/config/database.local.php.bak', ROOT_PATH . '/config/database.local.php');
        }
        $this->setDbInstance(null);
    }

    private function testAuthFailClosed()
    {
        echo "\n--- A. Database unavailable + fallback credentials ---\n";
        $this->forceDbFailure();
        $this->assert(Database::getConnection() === null, "Database is properly disconnected for testing");

        require_once ROOT_PATH . '/app/models/User.php';
        $userModel = new User();
        
        $user = $userModel->verify('admin@techpilot.vn', 'admin123');
        $this->assert($user === false, "Login thất bại khi DB unavailable", "Vẫn cho phép đăng nhập bằng fallback");
        $this->assert(!isset($_SESSION['user']), "Không tạo session user", "Vẫn tạo session user");
        
        echo "\n--- B. Database unavailable + remember cookie ---\n";
        $_COOKIE['remember_techpilot'] = 'fake_token';
        $userByToken = $userModel->findByRememberToken('fake_token');
        $this->assert($userByToken === false, "Cookie không tạo session admin", "Vẫn cho đăng nhập bằng remember token fallback");

        echo "\n--- C. Database unavailable + register ---\n";
        $created = $userModel->create('Test', 'test@example.com', '123456789', 'password');
        $this->assert($created === false, "Không báo đăng ký thành công", "Vẫn báo tạo tài khoản thành công bằng fallback");
        
        $this->restoreDb();
    }
    
    private function testCheckoutFailClosed()
    {
        require_once ROOT_PATH . '/app/models/Order.php';
        $this->forceDbFailure();
        
        echo "\n--- Checkout: Database unavailable + COD / VNPay ---\n";
        $orderModel = new Order();
        
        $payload = [
            'customer_name' => 'John Doe',
            'phone' => '1234567890',
            'address' => '123 Street',
            'payment_method' => 'COD',
            'subtotal' => 100000,
            'items' => [
                ['product_id' => 1, 'quantity' => 1]
            ]
        ];
        
        $order = $orderModel->create($payload);
        $this->assert($order === false, "Order creation thất bại", "Order tạo thành công bằng giả lập (id=0)");
        
        $this->restoreDb();
    }
    
    private function testDatabaseAvailableAuth()
    {
        echo "\n--- D. Database available Auth ---\n";
        $db = $this->getRealDb();
        require_once ROOT_PATH . '/app/models/User.php';
        $userModel = new User();
        
        // Find a real user or test wrong password
        $user = $userModel->verify('nonexistent@example.com', 'wrongpass');
        $this->assert($user === false, "User sai password vẫn bị từ chối");
        
        // Ensure connection works
        $this->assert($db instanceof PDO, "Database is available for normal operations");
    }
    
    private function testDatabaseAvailableCheckout()
    {
        echo "\n--- D. Database available Checkout ---\n";
        $db = $this->getRealDb();
        require_once ROOT_PATH . '/app/models/Order.php';
        $orderModel = new Order();
        $this->assert($db instanceof PDO, "Database is available for checkout ops");
    }
}

$test = new FailClosedRegressionTest();
$test->run();
