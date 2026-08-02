<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Ho_Chi_Minh');

/**
 * Bộ kiểm thử web an toàn cho TechPilot.
 *
 * Chạy:
 *   php tests/web/WebQaTest.php http://127.0.0.1:8000
 *   php tests/web/WebQaTest.php http://127.0.0.1:8000 --report=tests/web/reports/qa-report.md
 *
 * Phạm vi cố ý không tạo, sửa hoặc xóa dữ liệu nghiệp vụ. Các POST tới khu vực
 * quản trị chỉ gửi payload không hợp lệ để controller dừng ở bước validation.
 */
final class WebQaHttpClient
{
    private string $baseUrl;
    private string $origin;
    private array $cookies = [];

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $parts = parse_url($this->baseUrl);
        $scheme = (string)($parts['scheme'] ?? '');
        $host = (string)($parts['host'] ?? '');
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';

        if (!in_array($scheme, ['http', 'https'], true) || $host === '') {
            throw new InvalidArgumentException('Base URL phải là địa chỉ HTTP/HTTPS hợp lệ.');
        }

        $this->origin = $scheme . '://' . $host . $port;
    }

    public function request(
        string $method,
        string $path,
        array $form = [],
        array $extraHeaders = []
    ): array {
        $url = $this->resolveUrl($path);
        $responseHeaders = [];
        $method = strtoupper($method);

        $curl = curl_init($url);
        if ($curl === false) {
            return $this->connectionFailure($method, $url, 'Không thể khởi tạo cURL.');
        }

        $headers = array_merge([
            'Accept: text/html,application/json;q=0.9,*/*;q=0.8',
            'User-Agent: TechPilot-Web-QA/1.0',
        ], $extraHeaders);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADERFUNCTION => function ($handle, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                $trimmed = trim($line);
                if ($trimmed === '' || !str_contains($trimmed, ':')) {
                    return $length;
                }

                [$name, $value] = explode(':', $trimmed, 2);
                $name = strtolower(trim($name));
                $value = trim($value);
                $responseHeaders[$name][] = $value;

                if ($name === 'set-cookie') {
                    $pair = explode(';', $value, 2)[0];
                    if (str_contains($pair, '=')) {
                        [$cookieName, $cookieValue] = explode('=', $pair, 2);
                        $cookieName = trim($cookieName);
                        if ($cookieName !== '') {
                            if ($cookieValue === '') {
                                unset($this->cookies[$cookieName]);
                            } else {
                                $this->cookies[$cookieName] = $cookieValue;
                            }
                        }
                    }
                }

                return $length;
            },
        ]);

        if ($this->cookies !== []) {
            $cookiePairs = [];
            foreach ($this->cookies as $name => $value) {
                $cookiePairs[] = $name . '=' . $value;
            }
            curl_setopt($curl, CURLOPT_COOKIE, implode('; ', $cookiePairs));
        }

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($form));
            curl_setopt($curl, CURLOPT_HTTPHEADER, array_merge($headers, [
                'Content-Type: application/x-www-form-urlencoded',
            ]));
        } elseif ($method === 'HEAD') {
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
        } elseif ($method !== 'GET') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        $body = curl_exec($curl);
        $error = curl_error($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        return [
            'method' => $method,
            'url' => $url,
            'status' => $status,
            'headers' => $responseHeaders,
            'body' => is_string($body) ? $body : '',
            'error' => $error,
        ];
    }

    public function resolveUrl(string $path): string
    {
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        if (str_starts_with($path, '//')) {
            $scheme = (string)(parse_url($this->baseUrl, PHP_URL_SCHEME) ?: 'http');
            return $scheme . ':' . $path;
        }

        if (str_starts_with($path, '/')) {
            return $this->origin . $path;
        }

        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    public function isLocalUrl(string $url): bool
    {
        $resolved = $this->resolveUrl($url);
        return strtolower((string)parse_url($resolved, PHP_URL_HOST))
            === strtolower((string)parse_url($this->baseUrl, PHP_URL_HOST));
    }

    private function connectionFailure(string $method, string $url, string $error): array
    {
        return [
            'method' => $method,
            'url' => $url,
            'status' => 0,
            'headers' => [],
            'body' => '',
            'error' => $error,
        ];
    }
}

final class TechPilotWebQaSuite
{
    private WebQaHttpClient $client;
    private string $baseUrl;
    private ?string $reportPath;
    private int $maxAssets;
    private array $results = [];
    private string $csrfToken = '';

    public function __construct(string $baseUrl, ?string $reportPath, int $maxAssets)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->client = new WebQaHttpClient($this->baseUrl);
        $this->reportPath = $reportPath;
        $this->maxAssets = max(1, min($maxAssets, 200));
    }

    public function run(): int
    {
        echo "============================================================\n";
        echo "TECHPILOT WEB QA - SAFE SMOKE, ROUTING & AUTHORIZATION TESTS\n";
        echo "Base URL: {$this->baseUrl}\n";
        echo "============================================================\n\n";

        $home = $this->testPublicPages();
        $this->testSecurityHeaders($home);
        $this->testCsrfContract($home);
        $this->testLocalAssets($home);
        $this->testGuestAccessRules();
        $this->testBrokenProfileLinks();
        $this->testMissingAiFavoriteHandler();
        $this->testAdminPostAuthorization();
        $this->testForgotPasswordEnumeration();
        $this->testNotFoundContract();

        $passed = count(array_filter($this->results, static fn(array $result): bool => $result['passed']));
        $failed = count($this->results) - $passed;

        echo "\n============================================================\n";
        echo "KẾT QUẢ: {$passed} PASS, {$failed} FAIL, " . count($this->results) . " TOTAL\n";
        echo "============================================================\n";

        if ($this->reportPath !== null) {
            $this->writeMarkdownReport($passed, $failed);
        }

        return $failed === 0 ? 0 : 1;
    }

    private function testPublicPages(): array
    {
        echo "--- A. Smoke test các trang public ---\n";
        $cases = [
            ['TP-WEB-001', '/', 'Trang chủ', 'TechPilot'],
            ['TP-WEB-002', '/search?q=laptop', 'Tìm kiếm', 'Tìm'],
            ['TP-WEB-003', '/product/detail/asus-rog-zephyrus-g16', 'Chi tiết sản phẩm', 'ROG'],
            ['TP-WEB-004', '/ai-assistant', 'Trợ lý AI', 'AI'],
            ['TP-WEB-005', '/compare', 'So sánh sản phẩm', 'So sánh'],
            ['TP-WEB-006', '/build-pc', 'PC Builder', 'PC'],
            ['TP-WEB-007', '/tin-tuc', 'Tin tức', 'Tin'],
            ['TP-WEB-008', '/auth/login', 'Đăng nhập', 'Đăng nhập'],
            ['TP-WEB-009', '/cart', 'Route giỏ hàng', 'Đăng nhập'],
        ];

        $home = [];
        foreach ($cases as [$id, $path, $name, $marker]) {
            $initialResponse = $this->client->request('GET', $path);
            if ($path === '/') {
                $home = $initialResponse;
            }

            $response = $initialResponse;
            $redirectSummary = '';
            $location = $this->lastHeader($initialResponse, 'location');
            if (
                $initialResponse['status'] >= 300
                && $initialResponse['status'] < 400
                && $location !== ''
                && $this->client->isLocalUrl($location)
            ) {
                $response = $this->client->request('GET', $location);
                $redirectSummary = '; theo redirect ' . $location . ' => ' . $this->httpSummary($response);
            }

            $hasFatal = preg_match(
                '/(?:Fatal error|Parse error|Uncaught (?:Error|Exception)|Warning:)/i',
                $response['body']
            ) === 1;
            $markerFound = stripos($this->visibleText($response['body']), $marker) !== false;
            $passed = $response['status'] === 200
                && $response['error'] === ''
                && !$hasFatal
                && $markerFound;

            $this->record(
                $id,
                'Smoke',
                'P1',
                $name . ' tải thành công',
                $passed,
                'HTTP 200 sau tối đa một redirect local, có nội dung đặc trưng và không có PHP fatal/warning',
                $this->httpSummary($initialResponse)
                    . $redirectSummary
                    . ($markerFound ? '' : "; thiếu marker \"{$marker}\""),
                'GET ' . $path
            );
        }

        return $home;
    }

    private function testSecurityHeaders(array $home): void
    {
        echo "\n--- B. Security headers và CSRF ---\n";
        $actual = [
            'x-content-type-options' => $this->lastHeader($home, 'x-content-type-options'),
            'x-frame-options' => $this->lastHeader($home, 'x-frame-options'),
            'referrer-policy' => $this->lastHeader($home, 'referrer-policy'),
        ];

        $passed = strtolower($actual['x-content-type-options']) === 'nosniff'
            && strtoupper($actual['x-frame-options']) === 'SAMEORIGIN'
            && strtolower($actual['referrer-policy']) === 'strict-origin-when-cross-origin';

        $this->record(
            'TP-WEB-010',
            'Security headers',
            'P2',
            'Trang public trả về các header bảo vệ cơ bản',
            $passed,
            'nosniff; SAMEORIGIN; strict-origin-when-cross-origin',
            json_encode($actual, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '',
            'GET /'
        );
    }

    private function testCsrfContract(array $home): void
    {
        $this->csrfToken = $this->extractCsrfToken($home['body'] ?? '');
        $this->record(
            'TP-WEB-011',
            'CSRF',
            'P1',
            'Trang public cấp CSRF token cho form/AJAX',
            $this->csrfToken !== '',
            'Meta csrf-token có giá trị không rỗng',
            $this->csrfToken !== '' ? 'Đã nhận token 64 ký tự (đã che)' : 'Không tìm thấy token',
            'GET /'
        );

        $freshClient = new WebQaHttpClient($this->baseUrl);
        $response = $freshClient->request('POST', '/auth/login', [
            'email' => 'qa@example.invalid',
            'password' => 'invalid',
        ]);
        $this->record(
            'TP-WEB-012',
            'CSRF',
            'P1',
            'POST không có CSRF token bị từ chối',
            $response['status'] === 403,
            'HTTP 403',
            $this->httpSummary($response),
            'POST /auth/login (không gửi token)'
        );
    }

    private function testLocalAssets(array $home): void
    {
        echo "\n--- C. Tài nguyên tĩnh xuất hiện trên trang chủ ---\n";
        $assets = array_slice(
            $this->extractLocalAssets($home['body'] ?? ''),
            0,
            $this->maxAssets
        );
        $broken = [];

        foreach ($assets as $asset) {
            $response = $this->client->request('HEAD', $asset);
            if ($response['status'] < 200 || $response['status'] >= 400) {
                $broken[] = $asset . ' => HTTP ' . $response['status'];
            }
        }

        $actual = count($assets) . ' tài nguyên đã kiểm tra';
        if ($broken !== []) {
            $actual .= '; lỗi: ' . implode(', ', array_slice($broken, 0, 8));
            if (count($broken) > 8) {
                $actual .= ' và ' . (count($broken) - 8) . ' lỗi khác';
            }
        }

        $this->record(
            'TP-WEB-013',
            'Static assets',
            'P1',
            'CSS/JS/ảnh local trên trang chủ không bị 404',
            $assets !== [] && $broken === [],
            'Ít nhất một asset được kiểm tra và tất cả trả HTTP 2xx/3xx',
            $actual,
            'GET /, sau đó HEAD tối đa ' . $this->maxAssets . ' asset local'
        );
    }

    private function testGuestAccessRules(): void
    {
        echo "\n--- D. Phân quyền khách chưa đăng nhập ---\n";

        // TP-WEB-014 và TP-WEB-016: vẫn phải chặn guest như trước.
        $blockedCases = [
            ['TP-WEB-014', '/admin', 'Trang quản trị'],
            ['TP-WEB-016', '/profile/orders', 'Danh sách đơn hàng cá nhân'],
        ];

        foreach ($blockedCases as [$id, $path, $name]) {
            $response = $this->client->request('GET', $path);
            $this->record(
                $id,
                'Authorization',
                'P0',
                $name . ' chặn khách chưa đăng nhập',
                $this->isUnauthorized($response),
                'HTTP 401/403 hoặc redirect tới /auth/login',
                $this->httpSummary($response),
                'GET ' . $path
            );
        }

        // TP-WEB-015: /checkout cho phép guest (Guest Checkout).
        // Khi giỏ rỗng, chấp nhận duy nhất HTTP 302 với Location chính xác là /cart.
        // Khi có giỏ, chấp nhận HTTP 200.
        // Từ chối hoàn toàn: HTTP >= 400, redirect tới /auth/login, cURL error, PHP fatal/warning.
        $checkoutRes = $this->client->request('GET', '/checkout');
        $checkoutStatus = $checkoutRes['status'];
        $rawLocation = $this->lastHeader($checkoutRes, 'location');
        $checkoutLocation = strtolower($rawLocation);
        $checkoutBody = $checkoutRes['body'];

        $redirectsToLogin  = str_contains($checkoutLocation, '/auth/login');
        $isHttpError       = $checkoutStatus >= 400;
        
        $redirectPath = parse_url($rawLocation, PHP_URL_PATH);
        $normalizedRedirectPath = '/' . trim((string)$redirectPath, '/');
        
        // Host validation if it's an absolute URL
        $redirectHost = parse_url($rawLocation, PHP_URL_HOST);
        $baseHost = parse_url($this->baseUrl, PHP_URL_HOST);
        $isValidHost = ($redirectHost === null || strcasecmp($redirectHost, (string)$baseHost) === 0);

        $isValidEmptyCart  = (
            $checkoutStatus === 302 
            && $normalizedRedirectPath === '/cart'
            && $isValidHost
        );
        
        $isValidWithCart   = ($checkoutStatus === 200);

        $hasPhpDiagnostic = preg_match(
            '/(?:Fatal error|Parse error|Warning:|Notice:|Deprecated:|Uncaught (?:Error|Exception)|Trying to access array offset)/i',
            $checkoutBody
        ) === 1;

        $guestCheckoutPassed = ($isValidEmptyCart || $isValidWithCart)
            && !$redirectsToLogin
            && !$isHttpError
            && !$hasPhpDiagnostic
            && $checkoutRes['error'] === '';

        $this->record(
            'TP-WEB-015',
            'Authorization',
            'P0',
            'Trang thanh toán cho phép guest (HTTP 302 -> /cart khi rỗng hoặc HTTP 200)',
            $guestCheckoutPassed,
            'HTTP 302 Location: /cart hoặc HTTP 200; Không HTTP >= 400; Không redirect login; Không PHP fatal/warning',
            $this->httpSummary($checkoutRes)
                . ($redirectsToLogin  ? '; FAIL: redirect tới login' : '')
                . ($isHttpError       ? '; FAIL: HTTP >= 400' : '')
                . ($checkoutStatus === 302 && !$isValidEmptyCart ? '; FAIL: location không phải /cart' : '')
                . ($hasPhpDiagnostic  ? '; FAIL: PHP diagnostic detected' : ''),
            'GET /checkout (guest, giỏ rỗng mặc định)'
        );

        $response = $this->client->request('GET', '/api/admin/notifications');
        $this->record(
            'TP-WEB-017',
            'Authorization',
            'P0',
            'API thông báo admin chặn khách chưa đăng nhập',
            $this->isUnauthorized($response),
            'HTTP 401/403 hoặc redirect tới /auth/login',
            $this->httpSummary($response) . '; body=' . $this->bodySnippet($response['body']),
            'GET /api/admin/notifications'
        );
    }

    private function testBrokenProfileLinks(): void
    {
        echo "\n--- E. Route hồ sơ được view/controller đang sử dụng ---\n";
        $cases = [
            ['TP-WEB-018', '/profile/order_detail?id=1', 'Link chi tiết đơn hàng dạng query string'],
            ['TP-WEB-019', '/profile/return?order_id=1', 'Link yêu cầu đổi trả dạng query string'],
        ];

        foreach ($cases as [$id, $path, $name]) {
            $response = $this->client->request('GET', $path);
            $this->record(
                $id,
                'Routing',
                'P1',
                $name . ' tồn tại',
                $response['status'] !== 404 && $response['status'] < 500,
                'Không trả HTTP 404/5xx; khách sẽ được chuyển tới đăng nhập',
                $this->httpSummary($response),
                'GET ' . $path
            );
        }
    }

    private function testMissingAiFavoriteHandler(): void
    {
        echo "\n--- F. Route AJAX của AI Assistant ---\n";
        $response = $this->client->request('POST', '/ai/favorite', [
            'csrf_token' => $this->csrfToken,
            'product_id' => '',
        ], [
            'Accept: application/json',
            'X-Requested-With: XMLHttpRequest',
        ]);

        $this->record(
            'TP-WEB-020',
            'Routing',
            'P1',
            'Endpoint lưu sản phẩm AI yêu thích có handler',
            $response['status'] !== 404 && $response['status'] < 500,
            'Endpoint được xử lý (401/403/422/redirect đều hợp lệ), không 404/5xx',
            $this->httpSummary($response) . '; body=' . $this->bodySnippet($response['body']),
            'POST /ai/favorite với product_id rỗng'
        );
    }

    private function testAdminPostAuthorization(): void
    {
        echo "\n--- G. Phân quyền POST khu vực admin (payload dừng trước DB) ---\n";
        $cases = [
            ['TP-WEB-021', '/admin/categories/store', 'Tạo danh mục', ['name' => '']],
            ['TP-WEB-022', '/admin/brands/store', 'Tạo thương hiệu', ['name' => '']],
            [
                'TP-WEB-023',
                '/admin/coupons/store',
                'Tạo coupon',
                ['code' => '', 'discount_value' => '0'],
            ],
            [
                'TP-WEB-024',
                '/admin/flash-sales/store',
                'Tạo flash sale',
                ['title' => '', 'start_time' => '', 'end_time' => ''],
            ],
            ['TP-WEB-025', '/admin/banners/store', 'Tạo banner', ['title' => '']],
        ];

        foreach ($cases as [$id, $path, $name, $payload]) {
            $response = $this->client->request('POST', $path, array_merge($payload, [
                'csrf_token' => $this->csrfToken,
            ]));

            $this->record(
                $id,
                'Authorization',
                'P0',
                $name . ' chặn khách dù CSRF hợp lệ',
                $this->isUnauthorized($response),
                'HTTP 401/403 hoặc redirect tới /auth/login trước validation',
                $this->httpSummary($response),
                'POST ' . $path . ' với payload không hợp lệ, không ghi DB'
            );
        }
    }

    private function testForgotPasswordEnumeration(): void
    {
        echo "\n--- H. Quên mật khẩu không tiết lộ sự tồn tại tài khoản ---\n";
        $response = $this->client->request('POST', '/auth/forgot', [
            'csrf_token' => $this->csrfToken,
            'email' => 'qa-account-does-not-exist-20260730@example.invalid',
        ]);
        $text = $this->visibleText($response['body']);
        $exposesAccountState = stripos($text, 'Không tìm thấy tài khoản') !== false;

        $this->record(
            'TP-WEB-026',
            'Authentication',
            'P1',
            'Quên mật khẩu dùng phản hồi chung để chống dò email',
            $response['status'] === 200 && !$exposesAccountState,
            'HTTP 200 và thông báo chung, không xác nhận email có/không tồn tại',
            $this->httpSummary($response)
                . ($exposesAccountState ? '; phát hiện thông báo \"Không tìm thấy tài khoản\"' : ''),
            'POST /auth/forgot với email chắc chắn không tồn tại'
        );
    }

    private function testNotFoundContract(): void
    {
        echo "\n--- I. Trang không tồn tại ---\n";
        $response = $this->client->request('GET', '/qa-route-does-not-exist');
        $this->record(
            'TP-WEB-027',
            'Routing',
            'P2',
            'Route không tồn tại trả đúng HTTP 404',
            $response['status'] === 404,
            'HTTP 404',
            $this->httpSummary($response),
            'GET /qa-route-does-not-exist'
        );
    }

    private function record(
        string $id,
        string $area,
        string $severity,
        string $name,
        bool $passed,
        string $expected,
        string $actual,
        string $reproduction
    ): void {
        $this->results[] = [
            'id' => $id,
            'area' => $area,
            'severity' => $severity,
            'name' => $name,
            'passed' => $passed,
            'expected' => $expected,
            'actual' => $actual,
            'reproduction' => $reproduction,
        ];

        echo sprintf(
            "[%s] %s [%s] %s\n",
            $passed ? 'PASS' : 'FAIL',
            $id,
            $severity,
            $name
        );
        if (!$passed) {
            echo "       Actual: {$actual}\n";
        }
    }

    private function isUnauthorized(array $response): bool
    {
        if (in_array($response['status'], [401, 403], true)) {
            return true;
        }

        if ($response['status'] >= 300 && $response['status'] < 400) {
            return str_contains(strtolower($this->lastHeader($response, 'location')), '/auth/login');
        }

        return false;
    }

    private function extractCsrfToken(string $html): string
    {
        if (preg_match(
            '/<meta\b[^>]*\bname=(["\'])csrf-token\1[^>]*\bcontent=(["\'])(.*?)\2/i',
            $html,
            $matches
        ) !== 1) {
            return '';
        }

        return html_entity_decode($matches[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function extractLocalAssets(string $html): array
    {
        $candidates = [];
        if (preg_match_all(
            '/<(?:img|script)\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/i',
            $html,
            $sourceMatches
        )) {
            $candidates = array_merge($candidates, $sourceMatches[2]);
        }
        if (preg_match_all(
            '/<link\b[^>]*\bhref\s*=\s*(["\'])(.*?)\1/i',
            $html,
            $linkMatches
        )) {
            $candidates = array_merge($candidates, $linkMatches[2]);
        }

        $assets = [];
        foreach ($candidates as $candidate) {
            $candidate = html_entity_decode(trim($candidate), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (
                $candidate === ''
                || str_starts_with($candidate, 'data:')
                || str_starts_with($candidate, 'javascript:')
                || !$this->client->isLocalUrl($candidate)
            ) {
                continue;
            }

            $path = (string)(parse_url($candidate, PHP_URL_PATH) ?? '');
            if (preg_match('/\.(?:css|js|png|jpe?g|gif|svg|webp|ico|woff2?|ttf)$/i', $path) !== 1) {
                continue;
            }

            $assets[$candidate] = true;
        }

        return array_keys($assets);
    }

    private function lastHeader(array $response, string $name): string
    {
        $values = $response['headers'][strtolower($name)] ?? [];
        if (!is_array($values) || $values === []) {
            return '';
        }

        return (string)end($values);
    }

    private function httpSummary(array $response): string
    {
        $summary = 'HTTP ' . $response['status'];
        $location = $this->lastHeader($response, 'location');
        if ($location !== '') {
            $summary .= ', Location=' . $location;
        }
        if ($response['error'] !== '') {
            $summary .= ', cURL=' . $response['error'];
        }
        return $summary;
    }

    private function bodySnippet(string $body): string
    {
        $text = $this->visibleText($body);
        if (function_exists('mb_substr')) {
            return mb_substr($text, 0, 180, 'UTF-8');
        }
        return substr($text, 0, 180);
    }

    private function visibleText(string $html): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim((string)preg_replace('/\s+/u', ' ', $text));
    }

    private function writeMarkdownReport(int $passed, int $failed): void
    {
        $reportPath = (string)$this->reportPath;
        $directory = dirname($reportPath);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Không thể tạo thư mục báo cáo: ' . $directory);
        }

        $lines = [
            '# Báo cáo kiểm thử web TechPilot',
            '',
            '- Thời điểm: ' . date('Y-m-d H:i:s T'),
            '- Base URL: `' . $this->markdown($this->baseUrl) . '`',
            '- Phạm vi: smoke, routing, CSRF, authorization, authentication và static assets',
            '- An toàn dữ liệu: không tạo/sửa/xóa dữ liệu nghiệp vụ',
            '- Kết quả: **' . $passed . ' PASS / ' . $failed . ' FAIL / '
                . count($this->results) . ' TOTAL**',
            '',
            '## Bảng kết quả',
            '',
            '| ID | Trạng thái | Mức độ | Khu vực | Bài kiểm thử | Thực tế |',
            '|---|---|---|---|---|---|',
        ];

        foreach ($this->results as $result) {
            $lines[] = '| ' . $result['id']
                . ' | ' . ($result['passed'] ? 'PASS' : 'FAIL')
                . ' | ' . $result['severity']
                . ' | ' . $this->markdown($result['area'])
                . ' | ' . $this->markdown($result['name'])
                . ' | ' . $this->markdown($result['actual']) . ' |';
        }

        $lines[] = '';
        $lines[] = '## Chi tiết lỗi';
        $lines[] = '';

        $failures = array_filter(
            $this->results,
            static fn(array $result): bool => !$result['passed']
        );

        if ($failures === []) {
            $lines[] = 'Không phát hiện lỗi trong phạm vi kiểm thử.';
        } else {
            foreach ($failures as $result) {
                $lines[] = '### [' . $result['severity'] . '] ' . $result['id']
                    . ' — ' . $result['name'];
                $lines[] = '';
                $lines[] = '- Khu vực: ' . $result['area'];
                $lines[] = '- Mong đợi: ' . $result['expected'];
                $lines[] = '- Thực tế: ' . $result['actual'];
                $lines[] = '- Tái hiện: `' . $this->markdown($result['reproduction']) . '`';
                $lines[] = '';
            }
        }

        $lines[] = '## Cách chạy lại';
        $lines[] = '';
        $lines[] = '```powershell';
        $lines[] = 'php -S 127.0.0.1:8000 router.php';
        $lines[] = 'php tests/web/WebQaTest.php http://127.0.0.1:8000 '
            . '--report=tests/web/reports/qa-report.md';
        $lines[] = '```';
        $lines[] = '';
        $lines[] = '> Exit code `1` nghĩa là có lỗi web được phát hiện, không phải runner bị hỏng.';
        $lines[] = '';

        if (file_put_contents($reportPath, implode(PHP_EOL, $lines)) === false) {
            throw new RuntimeException('Không thể ghi báo cáo: ' . $reportPath);
        }

        echo "Báo cáo Markdown: {$reportPath}\n";
    }

    private function markdown(string $value): string
    {
        return str_replace(
            ["\r", "\n", '|'],
            ['', '<br>', '\|'],
            $value
        );
    }
}

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Bộ kiểm thử này chỉ chạy từ CLI.\n");
    exit(2);
}

if (!extension_loaded('curl')) {
    fwrite(STDERR, "Thiếu PHP extension curl. Hãy bật curl trước khi chạy test.\n");
    exit(2);
}

$baseUrl = (string)(getenv('TECHPILOT_BASE_URL') ?: 'http://127.0.0.1:8000');
$reportPath = null;
$maxAssets = 60;

foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--report=')) {
        $reportPath = substr($argument, strlen('--report='));
    } elseif (str_starts_with($argument, '--max-assets=')) {
        $maxAssets = (int)substr($argument, strlen('--max-assets='));
    } elseif (!str_starts_with($argument, '--')) {
        $baseUrl = $argument;
    }
}

try {
    $suite = new TechPilotWebQaSuite($baseUrl, $reportPath, $maxAssets);
    exit($suite->run());
} catch (Throwable $error) {
    fwrite(STDERR, '[RUNNER ERROR] ' . $error->getMessage() . PHP_EOL);
    exit(2);
}
