<?php

require_once __DIR__ . '/../config/app.php';

class AdminRouteAuthorizationTest
{
    private string $baseUrl;
    private $ch;

    // endpoints to test
    private array $uiGetEndpoints = [
        '/admin',
        '/admin/categories',
        '/admin/products'
    ];

    private array $postEndpoints = [
        '/admin/categories/store',
        '/admin/brands/store',
        '/admin/coupons/store',
        '/admin/flash-sales/store',
        '/admin/banners/store'
    ];

    private array $apiEndpoints = [
        '/api/admin/notifications'
    ];

    private string $guestCookieFile;
    private string $customerCookieFile;
    private string $adminCookieFile;

    private string $guestCsrf;
    private string $customerCsrf;
    private string $adminCsrf;

    private PDO $db;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');

        $this->guestCookieFile = tempnam(sys_get_temp_dir(), 'cookie_guest_');
        $this->customerCookieFile = tempnam(sys_get_temp_dir(), 'cookie_customer_');
        $this->adminCookieFile = tempnam(sys_get_temp_dir(), 'cookie_admin_');

        require_once __DIR__ . '/../config/database.php';
        $this->db = Database::getConnection();
    }

    public function __destruct()
    {
        @unlink($this->guestCookieFile);
        @unlink($this->customerCookieFile);
        @unlink($this->adminCookieFile);
    }

    public function run(): void
    {
        try {
            $this->setupSessions();

            $this->testGuestUiGet();
            $this->testCustomerUiGet();

            $countsBefore = $this->getDatabaseCounts();
            $this->testGuestPost();
            $this->testCustomerPost();
            $countsAfter = $this->getDatabaseCounts();

            if ($countsBefore !== $countsAfter) {
                throw new RuntimeException("Database counts changed during unauthorized POST tests.");
            }

            $this->testGuestApi();
            $this->testCustomerApi();
            $this->testAdminApi();

            echo "AdminRouteAuthorizationTest: 0 failed, 0 skipped\n";
            exit(0);
        } catch (Throwable $e) {
            echo "AdminRouteAuthorizationTest FAILED: " . $e->getMessage() . "\n";
            exit(1);
        } finally {
            $this->cleanup();
        }
    }

    private function cleanup(): void
    {
        $this->db->exec("DELETE FROM users WHERE email IN ('customer99@test.com', 'admin99@test.com')");
    }

    private function getDatabaseCounts(): array
    {
        return [
            'categories' => $this->db->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
            'brands' => $this->db->query("SELECT COUNT(*) FROM brands")->fetchColumn(),
            'coupons' => $this->db->query("SELECT COUNT(*) FROM coupons")->fetchColumn(),
            'flash_sales' => $this->db->query("SELECT COUNT(*) FROM flash_sales")->fetchColumn(),
            'banners' => $this->db->query("SELECT COUNT(*) FROM banners")->fetchColumn(),
        ];
    }

    private function setupSessions(): void
    {
        // 1. Guest
        $res = $this->request('GET', '/auth/login', [], $this->guestCookieFile);
        $this->guestCsrf = $this->extractCsrfToken($res['body']);

        // 2. Customer
        $this->db->exec("DELETE FROM users WHERE email = 'customer99@test.com'");
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $this->db->exec("INSERT INTO users (email, password, role, full_name, status) VALUES ('customer99@test.com', '{$passwordHash}', 'customer', 'Test Customer', 'active')");

        $res = $this->request('GET', '/auth/login', [], $this->customerCookieFile);
        $this->customerCsrf = $this->extractCsrfToken($res['body']);
        $this->request('POST', '/auth/login', [
            'csrf_token' => $this->customerCsrf,
            'email' => 'customer99@test.com',
            'password' => 'password123'
        ], $this->customerCookieFile);

        // 3. Admin
        $this->db->exec("DELETE FROM users WHERE email = 'admin99@test.com'");
        $this->db->exec("INSERT INTO users (email, password, role, full_name, status) VALUES ('admin99@test.com', '{$passwordHash}', 'admin', 'Test Admin', 'active')");

        $res = $this->request('GET', '/auth/login', [], $this->adminCookieFile);
        $this->adminCsrf = $this->extractCsrfToken($res['body']);
        $this->request('POST', '/auth/login', [
            'csrf_token' => $this->adminCsrf,
            'email' => 'admin99@test.com',
            'password' => 'password123'
        ], $this->adminCookieFile);
    }

    private function testGuestUiGet(): void
    {
        foreach ($this->uiGetEndpoints as $endpoint) {
            $res = $this->request('GET', $endpoint, [], $this->guestCookieFile);
            if ($res['code'] !== 302) {
                echo "DEBUG BODY: " . substr($res['body'], 0, 500) . "\n";
                throw new RuntimeException("Guest GET {$endpoint} expected 302, got {$res['code']}");
            }
            if (!str_contains($res['headers'], 'Location: /auth/login')) {
                throw new RuntimeException("Guest GET {$endpoint} expected Location /auth/login, got header: " . $res['headers']);
            }
            if (str_contains(strtolower($res['body']), 'admin content') || str_contains(strtolower($res['body']), 'dashboard')) {
                throw new RuntimeException("Guest GET {$endpoint} rendered admin content");
            }
        }
    }

    private function testCustomerUiGet(): void
    {
        foreach ($this->uiGetEndpoints as $endpoint) {
            $res = $this->request('GET', $endpoint, [], $this->customerCookieFile);
            if ($res['code'] !== 403) {
                throw new RuntimeException("Customer GET {$endpoint} expected 403, got {$res['code']}");
            }
            if (str_contains($res['headers'], 'Location: /auth/login')) {
                throw new RuntimeException("Customer GET {$endpoint} unexpectedly redirected to login");
            }
        }
    }

    private function testGuestPost(): void
    {
        foreach ($this->postEndpoints as $endpoint) {
            $res = $this->request('POST', $endpoint, ['csrf_token' => $this->guestCsrf, 'name' => 'Test'], $this->guestCookieFile);
            if ($res['code'] !== 302) {
                throw new RuntimeException("Guest POST {$endpoint} expected 302, got {$res['code']}");
            }
            if (!str_contains($res['headers'], 'Location: /auth/login')) {
                throw new RuntimeException("Guest POST {$endpoint} expected Location /auth/login, got: " . $res['headers']);
            }
        }
    }

    private function testCustomerPost(): void
    {
        foreach ($this->postEndpoints as $endpoint) {
            $res = $this->request('POST', $endpoint, ['csrf_token' => $this->customerCsrf, 'name' => 'Test'], $this->customerCookieFile);
            if ($res['code'] !== 403) {
                throw new RuntimeException("Customer POST {$endpoint} expected 403, got {$res['code']}");
            }
        }
    }

    private function testGuestApi(): void
    {
        foreach ($this->apiEndpoints as $endpoint) {
            $res = $this->request('GET', $endpoint, [], $this->guestCookieFile);
            if ($res['code'] !== 401) {
                throw new RuntimeException("Guest API {$endpoint} expected 401, got {$res['code']}");
            }
            if (!str_contains(strtolower($res['headers']), 'content-type: application/json')) {
                throw new RuntimeException("Guest API {$endpoint} expected JSON content type");
            }
            $data = json_decode($res['body'], true);
            if (!$data || !isset($data['success']) || $data['success'] !== false || !isset($data['error']) || $data['error'] !== 'Unauthenticated') {
                throw new RuntimeException("Guest API {$endpoint} invalid JSON payload");
            }
        }
    }

    private function testCustomerApi(): void
    {
        foreach ($this->apiEndpoints as $endpoint) {
            $res = $this->request('GET', $endpoint, [], $this->customerCookieFile);
            if ($res['code'] !== 403) {
                throw new RuntimeException("Customer API {$endpoint} expected 403, got {$res['code']}");
            }
            if (!str_contains(strtolower($res['headers']), 'content-type: application/json')) {
                throw new RuntimeException("Customer API {$endpoint} expected JSON content type");
            }
        }
    }

    private function testAdminApi(): void
    {
        foreach ($this->apiEndpoints as $endpoint) {
            $res = $this->request('GET', $endpoint, [], $this->adminCookieFile);
            if ($res['code'] === 401 || $res['code'] === 403) {
                throw new RuntimeException("Admin API {$endpoint} expected success, got {$res['code']}");
            }
            if (str_contains($res['body'], 'Fatal error') || str_contains($res['body'], 'Warning:')) {
                throw new RuntimeException("Admin API {$endpoint} has PHP diagnostic output");
            }
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
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, null);
            curl_setopt($ch, CURLOPT_HTTPHEADER, []);
        }

        if ($cookieFile !== '') {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        }

        // Prevent cURL from automatically following redirects so we can assert the 302
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
    die("Usage: php AdminRouteAuthorizationTest.php <baseUrl>\n");
}

$test = new AdminRouteAuthorizationTest($argv[1]);
$test->run();
