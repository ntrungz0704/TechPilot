<?php
require_once __DIR__ . '/../config/database.php';

class WebBaselineRemediationTest
{
    private string $baseUrl;
    private PDO $db;
    private string $guestCookieFile;
    private string $authCookieFile;
    private string $guestCsrf;
    private string $authCsrf;
    private int $testUserId;
    private int $testProductId;
    private string $testUserEmail = 'qa-forgot-existing@example.invalid';

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->db = Database::getConnection();
        $this->guestCookieFile = tempnam(sys_get_temp_dir(), 'cookie_guest_');
        $this->authCookieFile = tempnam(sys_get_temp_dir(), 'cookie_auth_');
    }

    public function __destruct()
    {
        @unlink($this->guestCookieFile);
        @unlink($this->authCookieFile);
    }

    public function run(): void
    {
        try {
            $this->setupData();
            
            $this->testProfileReturnRoute();
            $this->testAiFavoriteGuest();
            $this->testAiFavoriteAuthenticated();
            $this->testForgotPasswordEnumeration();

            echo "WebBaselineRemediationTest: 0 failed, 0 skipped\n";
            exit(0);
        } catch (Throwable $e) {
            echo "WebBaselineRemediationTest FAILED: " . $e->getMessage() . "\n";
            exit(1);
        } finally {
            $this->cleanup();
        }
    }

    private function setupData(): void
    {
        $this->cleanup();

        // 1. Create temporary active user
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO users (email, password, role, full_name, status) VALUES ('{$this->testUserEmail}', '{$passwordHash}', 'customer', 'QA User', 'active')");
        $this->testUserId = (int)$this->db->lastInsertId();

        // 2. Get a valid product ID
        $stmt = $this->db->query("SELECT id FROM products WHERE status = 'active' LIMIT 1");
        $this->testProductId = (int)$stmt->fetchColumn();
        if (!$this->testProductId) {
            throw new RuntimeException("No active product found for testing");
        }

        // 3. Setup Guest Session
        $res = $this->request('GET', '/auth/login', [], $this->guestCookieFile);
        $this->guestCsrf = $this->extractCsrfToken($res['body']);

        // 4. Setup Auth Session
        $res = $this->request('GET', '/auth/login', [], $this->authCookieFile);
        $this->authCsrf = $this->extractCsrfToken($res['body']);
        $this->request('POST', '/auth/login', [
            'csrf_token' => $this->authCsrf,
            'email' => $this->testUserEmail,
            'password' => 'password123'
        ], $this->authCookieFile);
    }

    private function cleanup(): void
    {
        $this->db->exec("DELETE FROM wishlists WHERE user_id IN (SELECT id FROM users WHERE email = '{$this->testUserEmail}')");
        $this->db->exec("DELETE FROM users WHERE email = '{$this->testUserEmail}'");
    }

    private function testProfileReturnRoute(): void
    {
        // Static contract check
        $indexPhp = file_get_contents(__DIR__ . '/../public/index.php');
        $posWithoutId = strpos($indexPhp, "\$router->get('/profile/return', 'ProfileController@return');");
        $posWithId = strpos($indexPhp, "\$router->get('/profile/return/{id}', 'ProfileController@return');");

        if ($posWithoutId === false) {
            throw new RuntimeException("Exact route /profile/return not registered");
        }
        if ($posWithId === false) {
            throw new RuntimeException("Parameterized route /profile/return/{id} not registered");
        }
        if ($posWithoutId > $posWithId) {
            throw new RuntimeException("Exact route must be registered BEFORE parameterized route");
        }

        $controllerPhp = file_get_contents(__DIR__ . '/../app/controllers/ProfileController.php');
        if (!str_contains($controllerPhp, 'public function return(mixed $id = null): void')) {
            throw new RuntimeException("ProfileController::return signature not updated");
        }

        // Guest query URL
        $res = $this->request('GET', '/profile/return?order_id=1', [], $this->guestCookieFile);
        if ($res['code'] !== 302) {
            throw new RuntimeException("Guest /profile/return?order_id=1 expected 302, got {$res['code']}");
        }
        if (!str_contains($res['headers'], 'Location:') || !str_contains($res['headers'], '/auth/login')) {
            throw new RuntimeException("Guest query URL did not redirect to login");
        }

        // Guest path URL
        $res = $this->request('GET', '/profile/return/1', [], $this->guestCookieFile);
        if ($res['code'] !== 302) {
            throw new RuntimeException("Guest /profile/return/1 expected 302, got {$res['code']}");
        }
    }

    private function testAiFavoriteGuest(): void
    {
        $res = $this->request('POST', '/ai/favorite', [
            'csrf_token' => $this->guestCsrf,
            'product_id' => $this->testProductId
        ], $this->guestCookieFile);

        if ($res['code'] !== 401) {
            throw new RuntimeException("Guest POST /ai/favorite expected 401, got {$res['code']}");
        }
        if (!str_contains(strtolower($res['headers']), 'content-type: application/json')) {
            throw new RuntimeException("Guest AI favorite response not JSON");
        }
        $data = json_decode($res['body'], true);
        if (!$data || $data['success'] !== false || $data['requireLogin'] !== true) {
            throw new RuntimeException("Guest AI favorite invalid JSON response");
        }
    }

    private function testAiFavoriteAuthenticated(): void
    {
        // 1. Valid POST
        $res = $this->request('POST', '/ai/favorite', [
            'csrf_token' => $this->authCsrf,
            'product_id' => $this->testProductId
        ], $this->authCookieFile);

        if ($res['code'] !== 200) {
            throw new RuntimeException("Auth POST /ai/favorite expected 200, got {$res['code']}");
        }
        $data = json_decode($res['body'], true);
        if (!$data || $data['success'] !== true || $data['inWishlist'] !== true) {
            throw new RuntimeException("Auth AI favorite invalid JSON response on success");
        }
        
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$this->testUserId, $this->testProductId]);
        if ((int)$stmt->fetchColumn() !== 1) {
            throw new RuntimeException("Wishlist row not created");
        }

        // 2. Duplicate POST
        $res = $this->request('POST', '/ai/favorite', [
            'csrf_token' => $this->authCsrf,
            'product_id' => $this->testProductId
        ], $this->authCookieFile);
        if ($res['code'] !== 200) {
            throw new RuntimeException("Duplicate POST /ai/favorite expected 200, got {$res['code']}");
        }
        $stmt->execute([$this->testUserId, $this->testProductId]);
        if ((int)$stmt->fetchColumn() !== 1) {
            throw new RuntimeException("Duplicate POST created duplicate row");
        }

        // 3. Invalid product ID
        $res = $this->request('POST', '/ai/favorite', [
            'csrf_token' => $this->authCsrf,
            'product_id' => -1
        ], $this->authCookieFile);
        if ($res['code'] !== 422) {
            throw new RuntimeException("Invalid product POST /ai/favorite expected 422, got {$res['code']}");
        }

        // 4. Nonexistent product
        $res = $this->request('POST', '/ai/favorite', [
            'csrf_token' => $this->authCsrf,
            'product_id' => 999999
        ], $this->authCookieFile);
        if ($res['code'] !== 404) {
            throw new RuntimeException("Nonexistent product POST /ai/favorite expected 404, got {$res['code']}");
        }
    }

    private function testForgotPasswordEnumeration(): void
    {
        $expectedMessage = 'Nếu địa chỉ email tồn tại trong hệ thống, chúng tôi sẽ gửi hướng dẫn đặt lại mật khẩu.';
        
        // 1. Existing email
        $res = $this->request('POST', '/auth/forgot', [
            'csrf_token' => $this->guestCsrf,
            'email' => $this->testUserEmail
        ], $this->guestCookieFile);

        if ($res['code'] !== 200) {
            throw new RuntimeException("Existing email forgot password expected 200, got {$res['code']}");
        }
        if (str_contains($res['body'], 'Không tìm thấy tài khoản') || str_contains($res['body'], '?token=')) {
            throw new RuntimeException("Existing email forgot password leaked information");
        }
        if (!str_contains($res['body'], $expectedMessage)) {
            throw new RuntimeException("Existing email forgot password missing generic message");
        }

        // Check DB
        $stmt = $this->db->prepare("SELECT reset_token, reset_token_expiry FROM users WHERE email = ?");
        $stmt->execute([$this->testUserEmail]);
        $user = $stmt->fetch();
        if (empty($user['reset_token']) || empty($user['reset_token_expiry']) || strtotime($user['reset_token_expiry']) <= time()) {
            throw new RuntimeException("Existing email did not store valid reset token");
        }

        // 2. Non-existing email
        $nonExistingEmail = 'nonexistent-12345@example.invalid';
        $res = $this->request('POST', '/auth/forgot', [
            'csrf_token' => $this->guestCsrf,
            'email' => $nonExistingEmail
        ], $this->guestCookieFile);

        if ($res['code'] !== 200) {
            throw new RuntimeException("Non-existing email forgot password expected 200, got {$res['code']}");
        }
        if (str_contains($res['body'], 'Không tìm thấy tài khoản') || str_contains($res['body'], '?token=')) {
            throw new RuntimeException("Non-existing email forgot password leaked information");
        }
        if (!str_contains($res['body'], $expectedMessage)) {
            throw new RuntimeException("Non-existing email forgot password missing generic message");
        }

        // Check DB
        $stmt->execute([$nonExistingEmail]);
        if ($stmt->fetch()) {
            throw new RuntimeException("Non-existing email created a user account");
        }
    }

    private function request(string $method, string $path, array $data = [], string $cookieFile = ''): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        }

        if ($cookieFile !== '') {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'code' => $code,
            'headers' => $headers,
            'body' => $body
        ];
    }

    private function extractCsrfToken(string $html): string
    {
        if (preg_match('/name="csrf_token" value="([^"]+)"/', $html, $matches)) {
            return $matches[1];
        }
        return '';
    }
}

if ($argc < 2) {
    die("Usage: php WebBaselineRemediationTest.php <baseUrl>\n");
}

$test = new WebBaselineRemediationTest($argv[1]);
$test->run();
