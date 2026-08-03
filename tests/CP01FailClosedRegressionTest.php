<?php
define('ROOT_PATH', dirname(__DIR__));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/controllers/AuthController.php';
require_once ROOT_PATH . '/app/controllers/CheckoutController.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/models/Order.php';

// Test doubles
class TestAuthController extends AuthController {
    public int $lastResponseCode = 200;
    public ?string $lastRedirect = null;

    protected function redirect(string $url): void {
        $this->lastRedirect = $url;
    }
    
    protected function render(string $view, array $data = [], bool $useLayout = true): void {
        // do nothing for test
    }

    public function model(string $name) {
        return parent::model($name);
    }
}

class TestCheckoutController extends CheckoutController {
    public ?string $lastRedirect = null;
    public int $lastResponseCode = 200;

    protected function redirect(string $url): void {
        $this->lastRedirect = $url;
    }

    public function model(string $name) {
        if ($name === 'Order') {
            return new TestOrder();
        }
        return parent::model($name);
    }
}

class TestOrder extends Order {
    public int $createCallCount = 0;
    public function create(array $data): array|false {
        $this->createCallCount++;
        return false;
    }
}

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

    private function runIsolated(callable $fn)
    {
        // Backup superglobals
        $oldPost = $_POST;
        $oldGet = $_GET;
        $oldSession = $_SESSION;
        $oldCookie = $_COOKIE;
        
        try {
            $fn();
        } finally {
            // Restore
            $_POST = $oldPost;
            $_GET = $oldGet;
            $_SESSION = $oldSession;
            $_COOKIE = $oldCookie;
        }
    }

    private function forceDbFailure()
    {
        putenv('APP_ENV=test');
        putenv('FORCE_DB_FAILURE=1');
        
        // Use reflection to clear Database::$instance so the next getConnection() fails
        require_once ROOT_PATH . '/config/database.php';
        $ref = new ReflectionClass('Database');
        $prop = $ref->getProperty('instance');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    private function restoreDb()
    {
        putenv('APP_ENV');
        putenv('FORCE_DB_FAILURE');
        
        require_once ROOT_PATH . '/config/database.php';
        $ref = new ReflectionClass('Database');
        $prop = $ref->getProperty('instance');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    public function run(): void
    {
        ob_start(); // Prevent headers from being sent
        echo "========================================================\n";
        echo "=== TECHPILOT FAIL-CLOSED REGRESSION TEST SUITE      ===\n";
        echo "========================================================\n\n";

        $this->testAuthFailClosed();
        $this->testCheckoutFailClosed();

        echo "\n════════════════════════════════════════════════════════\n";
        echo "Fail-Closed Test Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "════════════════════════════════════════════════════════\n";

        ob_end_flush(); // Output everything

        if ($this->failed > 0) {
            exit(1);
        }
        exit(0);
    }

    private function testAuthFailClosed()
    {
        $this->forceDbFailure();

        $this->runIsolated(function() {
            echo "\n--- A. Login khi DB unavailable ---\n";
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['email'] = 'admin@techpilot.vn';
            $_POST['password'] = 'admin123';
            
            $controller = new TestAuthController();
            $controller->login();
            
            $this->assert(!isset($_SESSION['user']), "Không tạo session user");
            $this->assert($controller->lastRedirect === null, "Không redirect như login thành công");
            $this->assert(http_response_code() === 503, "HTTP status là 503");
        });

        $this->runIsolated(function() {
            echo "\n--- B. Register khi DB unavailable ---\n";
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['full_name'] = 'Test';
            $_POST['email'] = 'test@example.com';
            $_POST['password'] = 'password';
            $_POST['confirm_password'] = 'password';
            
            $controller = new TestAuthController();
            $controller->register();
            
            $this->assert(!isset($_SESSION['user']), "Không tạo session user");
            $this->assert($controller->lastRedirect === null, "Không redirect như đăng ký thành công");
            $this->assert(http_response_code() === 503, "HTTP status là 503");
        });

        $this->runIsolated(function() {
            echo "\n--- C. Remember-me khi DB unavailable ---\n";
            $_COOKIE['remember_techpilot'] = 'fake_token';
            $controller = new TestAuthController();
            // Trigger check auth
            $userModel = $controller->model('User');
            $userByToken = $userModel->findByRememberToken('fake_token');
            $this->assert($userByToken === false, "Base Controller không tạo session user");
        });

        $this->runIsolated(function() {
            echo "\n--- D. Reset password khi DB unavailable ---\n";
            $_GET['token'] = 'token_abc';
            $controller = new TestAuthController();
            $controller->reset();
            
            $this->assert($controller->lastRedirect === null, "Không redirect như login giả dạng lỗi");
            $this->assert(http_response_code() === 503, "Status cuối cùng là 503");
        });

        $this->restoreDb();
    }
    
    private function testCheckoutFailClosed()
    {
        $this->forceDbFailure();

        $this->runIsolated(function() {
            echo "\n--- Checkout COD khi DB unavailable ---\n";
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['submit_token'] = 'valid_token';
            $_SESSION['submit_token'] = 'valid_token';
            $_SESSION['user'] = ['id' => 1, 'role' => 'customer'];
            $_SESSION['cart'] = [1 => 1]; // non empty cart
            $_SESSION['applied_coupon'] = ['id' => 1, 'code' => 'DISCOUNT10', 'discount' => 10000];
            
            $_POST['customer_name'] = 'John';
            $_POST['phone'] = '1234567890';
            $_POST['address'] = '123 Test St';
            $_POST['payment_method'] = 'COD';

            $controller = new TestCheckoutController();
            $controller->submit();

            $this->assert($controller->lastRedirect === 'checkout', "Redirect destination là checkout");
            $this->assert(isset($_SESSION['cart']), "Cart vẫn còn nguyên");
            $this->assert(isset($_SESSION['applied_coupon']), "applied_coupon vẫn còn nguyên");
            $this->assert(!isset($_SESSION['last_order']), "last_order không được tạo");
            $this->assert(isset($_SESSION['submit_token']), "submit_token mới tồn tại");
            $this->assert(isset($_SESSION['checkout_error']), "checkout_error tồn tại");
        });

        $this->runIsolated(function() {
            echo "\n--- Checkout VNPay khi DB unavailable ---\n";
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $_POST['submit_token'] = 'valid_token';
            $_SESSION['submit_token'] = 'valid_token';
            $_SESSION['user'] = ['id' => 1, 'role' => 'customer'];
            $_SESSION['cart'] = [1 => 1];
            $_SESSION['applied_coupon'] = ['id' => 1, 'code' => 'DISCOUNT10', 'discount' => 10000];
            
            $_POST['customer_name'] = 'John';
            $_POST['phone'] = '1234567890';
            $_POST['address'] = '123 Test St';
            $_POST['payment_method'] = 'VNPAY';

            // Simulate VNPay Configured for test
            putenv('VNP_TMN_CODE=TESTCODE');
            putenv('VNP_HASH_SECRET=TESTSECRET');

            $controller = new TestCheckoutController();
            $controller->submit();

            $this->assert($controller->lastRedirect === 'checkout', "Redirect destination là checkout");
            $this->assert(isset($_SESSION['cart']), "Cart vẫn còn nguyên");
            $this->assert(!isset($_SESSION['last_order']), "last_order không được tạo");
            
            putenv('VNP_TMN_CODE');
            putenv('VNP_HASH_SECRET');
        });

        $this->restoreDb();
    }
}

$test = new FailClosedRegressionTest();
$test->run();
