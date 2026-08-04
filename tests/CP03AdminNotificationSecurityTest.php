<?php
define('ROOT_PATH', dirname(__DIR__));

class CP03AdminNotificationSecurityTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $logs = [];

    private function assert(bool $condition, string $testName, string $failureMsg = ''): void
    {
        if ($condition) {
            $this->passed++;
            $this->logs[] = "[PASS] {$testName}";
        } else {
            $this->failed++;
            $this->logs[] = "[FAIL] {$testName}" . ($failureMsg ? ": {$failureMsg}" : '');
        }
    }
    
    private function log(string $msg) {
        $this->logs[] = $msg;
    }

    private function runIndexPhp(array $serverEnv, array $sessionData = [], array $postData = [])
    {
        $env = [];
        foreach ($serverEnv as $k => $v) {
            $env[$k] = $v;
        }
        $env['DOCUMENT_ROOT'] = ROOT_PATH . '/public';
        $env['SCRIPT_FILENAME'] = ROOT_PATH . '/public/index.php';
        $env['REDIRECT_STATUS'] = '200';
        
        $sessionExport = var_export($sessionData, true);
        $postExport = var_export($postData, true);
        
        $serverExport = var_export($serverEnv, true);
        
        // Write a small wrapper to set session, POST, GET and SERVER data
        $wrapper = '<?php session_start(); $_SESSION = ' . $sessionExport . '; $_POST = ' . $postExport . '; foreach (' . $serverExport . ' as $k => $v) { $_SERVER[$k] = $v; } $_GET["url"] = "' . ($serverEnv["REQUEST_URI"] ?? "") . '"; require "' . ROOT_PATH . '/public/index.php";';
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, $wrapper);
        $env['SCRIPT_FILENAME'] = $tmpFile;
        
        // Add minimal required Windows env vars
        $env['SYSTEMROOT'] = getenv('SYSTEMROOT') ?: 'C:\\Windows';
        $env['PATH'] = getenv('PATH');

        $cmd = "php-cgi.exe -q " . escapeshellarg($tmpFile);
        $descriptorspec = [
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        
        $process = proc_open($cmd, $descriptorspec, $pipes, null, $env);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        
        unlink($tmpFile);
        
        $parts = explode("\r\n\r\n", $output, 2);
        $headers = $parts[0] ?? '';
        $body = $parts[1] ?? '';
        
        $code = 200;
        if (preg_match('/Status:\s*(\d+)/i', $headers, $m)) {
            $code = (int)$m[1];
        }
        
        return ['code' => $code, 'output' => $body];
    }

    public function run()
    {
        $this->log("========================================================");
        $this->log("=== CP03 ADMIN NOTIFICATION SECURITY TEST SUITE      ===");
        $this->log("========================================================");

        $this->testMissingCsrf();
        $this->testWrongCsrf();
        $this->testValidCsrfAndAdminAuth();
        $this->testDbUnavailable();
        $this->testSourceSecurityScan();
        
        echo implode("\n", $this->logs) . "\n";
        echo "\n========================================================\n";
        echo "CP03 Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "========================================================\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    private function testMissingCsrf()
    {
        $this->log("\n--- A. Missing CSRF ---");
        
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
        ], ['csrf_token' => 'real_token']);

        $this->assert($res['code'] === 403, "Admin POST mark-read không token -> HTTP 403. Actual: {$res['code']}");
        $data = json_decode($res['output'], true);
        if (!$data) $this->log("DEBUG BODY: " . $res['output']);
        $this->assert(isset($data['error']['code']) && $data['error']['code'] === 'CSRF_TOKEN_MISMATCH', "JSON error code CSRF_TOKEN_MISMATCH");
    }

    private function testWrongCsrf()
    {
        $this->log("\n--- B. Wrong CSRF ---");
        
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => 'wrong_token',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
        ], ['csrf_token' => 'real_token']);

        $this->assert($res['code'] === 403, "Wrong CSRF -> HTTP 403. Actual: {$res['code']}");
        $data = json_decode($res['output'], true);
        $this->assert(isset($data['error']['code']) && $data['error']['code'] === 'CSRF_TOKEN_MISMATCH', "JSON error code CSRF_TOKEN_MISMATCH");
    }
    
    private function testValidCsrfAndAdminAuth()
    {
        $this->log("\n--- C/D/E. Controller Authorization & Valid Logic ---");
        
        // Admin - Valid mark-all
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => 'real_token',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
        ], [
            'csrf_token' => 'real_token',
            'user' => ['id' => 1, 'role' => 'admin']
        ], []);
        
        $data = json_decode($res['output'], true);
        $this->assert($res['code'] === 200, "Valid mark-all request -> HTTP 200. Actual: {$res['code']}");
        $this->assert(isset($data['success']) && $data['success'] === true, "success=true");
        
        // Guest Auth
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => 'real_token',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
        ], [
            'csrf_token' => 'real_token'
        ], ['id' => 1]);
        $this->assert($res['code'] === 401 || $res['code'] === 403, "Guest mark-read -> HTTP 401/403. Actual: {$res['code']}");
        
        // Customer Auth
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => 'real_token',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
        ], [
            'csrf_token' => 'real_token',
            'user' => ['id' => 2, 'role' => 'customer']
        ], ['id' => 1]);
        $this->assert($res['code'] === 403, "Customer mark-read -> HTTP 403. Actual: {$res['code']}");
        
        // Invalid ID
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => 'real_token',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
        ], [
            'csrf_token' => 'real_token',
            'user' => ['id' => 1, 'role' => 'admin']
        ], ['id' => -5]);
        $data = json_decode($res['output'], true);
        $this->assert($res['code'] === 400, "Invalid ID -> HTTP 400. Actual: {$res['code']}");
        $this->assert(isset($data['error']['code']) && $data['error']['code'] === 'INVALID_NOTIFICATION_ID', "code INVALID_NOTIFICATION_ID");
    }

    private function testDbUnavailable()
    {
        $this->log("\n--- F. DB Unavailable ---");
        $dbPath = ROOT_PATH . '/config/database.local.php';
        $dbBackupPath = ROOT_PATH . '/config/database.local.php.bak';
        if (file_exists($dbPath)) {
            rename($dbPath, $dbBackupPath);
        }
        
        file_put_contents($dbPath, "<?php return ['host' => '0.0.0.0', 'port' => 1234, 'name' => 'invalid', 'user' => 'invalid', 'pass' => 'invalid', 'charset' => 'utf8mb4'];");

        try {
            $resGet = $this->runIndexPhp([
                'REQUEST_METHOD' => 'GET',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                'REQUEST_URI' => '/api/admin/notifications',
                'QUERY_STRING' => 'url=api/admin/notifications',
            ], [
                'user' => ['id' => 1, 'role' => 'admin']
            ]);

            $dataGet = json_decode($resGet['output'], true);
            $this->assert($resGet['code'] === 503, "GET notifications -> 503. Actual: {$resGet['code']}");
            $this->assert(isset($dataGet['success']) && $dataGet['success'] === false, "success=false");
            $this->assert(isset($dataGet['error']['code']) && $dataGet['error']['code'] === 'DATABASE_UNAVAILABLE', "code=DATABASE_UNAVAILABLE");

            $resPost = $this->runIndexPhp([
                'REQUEST_METHOD' => 'POST',
                'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
                'HTTP_X_CSRF_TOKEN' => 'real_token',
                'REQUEST_URI' => '/api/admin/notifications/mark_read',
                'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
            ], [
                'csrf_token' => 'real_token',
                'user' => ['id' => 1, 'role' => 'admin']
            ], ['id' => 1]);

            $dataPost = json_decode($resPost['output'], true);
            $this->assert($resPost['code'] === 503, "POST mark-read -> 503. Actual: {$resPost['code']}");
            $this->assert(isset($dataPost['success']) && $dataPost['success'] === false, "success=false");
            
        } finally {
            unlink($dbPath);
            if (file_exists($dbBackupPath)) {
                rename($dbBackupPath, $dbPath);
            }
        }
    }
    
    private function testSourceSecurityScan()
    {
        $this->log("\n--- G/H. Source Security Scan (DOM XSS / Unsafe Links) ---");
        $jsPath = ROOT_PATH . '/public/assets/js/admin-notifications.js';
        $this->assert(file_exists($jsPath), "Extracted JS exists");
        
        $content = file_exists($jsPath) ? file_get_contents($jsPath) : '';
        
        $this->assert(str_contains($content, 'document.createElement'), "Sử dụng document.createElement");
        $this->assert(!preg_match('/innerHTML\s*\+?=\s*[^;]*item\.(title|content)/i', $content), "Không đưa item.title/content vào innerHTML");
        $this->assert(!str_contains($content, 'onclick='), "Không sử dụng inline onclick trong payload");
        $this->assert(!str_contains($content, 'setAttribute(\'onclick\''), "Không setAttribute onclick");
        $this->assert(!str_contains($content, 'eval('), "Không sử dụng eval");
        $this->assert(str_contains($content, 'validateLink') || str_contains($content, 'new URL('), "Có xử lý validateLink parse bằng new URL()");
        $this->assert(!preg_match('/window\.location\.href\s*=\s*item\.link/i', $content), "Không gán window.location.href = item.link trực tiếp");
        $this->assert(str_contains($content, 'X-CSRF-Token'), "Có gửi X-CSRF-Token");
    }
}

$test = new CP03AdminNotificationSecurityTest();
$test->run();
