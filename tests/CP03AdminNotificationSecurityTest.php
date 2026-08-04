<?php
define('ROOT_PATH', dirname(__DIR__));

class CP03AdminNotificationSecurityTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $logs = [];
    private string $dbLocalPath;
    private ?string $dbLocalSha = null;

    public function __construct()
    {
        $this->dbLocalPath = ROOT_PATH . '/config/database.local.php';
        if (file_exists($this->dbLocalPath)) {
            $this->dbLocalSha = hash_file('sha256', $this->dbLocalPath);
        }
    }

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
        $env['APP_ENV'] = 'test';
        
        $sessionExport = var_export($sessionData, true);
        $postExport = var_export($postData, true);
        $serverExport = var_export($env, true);
        
        $wrapper = '<?php session_start(); $_SESSION = ' . $sessionExport . '; $_POST = ' . $postExport . '; foreach (' . $serverExport . ' as $k => $v) { $_SERVER[$k] = $v; putenv("$k=$v"); } $_GET["url"] = "' . ($serverEnv["REQUEST_URI"] ?? "") . '"; require "' . ROOT_PATH . '/public/index.php";';
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, $wrapper);
        $env['SCRIPT_FILENAME'] = $tmpFile;
        
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
    
    private function runControllerTest(string $method, array $postData, array $initialState, bool $simulateFailure = false)
    {
        $postExport = var_export($postData, true);
        $stateExport = var_export($initialState, true);
        $simFailExport = var_export($simulateFailure, true);
        
        $wrapper = '<?php session_start(); $_SESSION = []; $_POST = ' . var_export($postData, true) . '; foreach ([' .
            '\'SYSTEMROOT\' => \'' . str_replace('\\', '\\\\', getenv('SYSTEMROOT') ?: 'C:\\\\Windows') . '\', ' .
            '\'PATH\' => \'' . str_replace('\\', '\\\\', getenv('PATH')) . '\', ' .
            '\'REQUEST_METHOD\' => \'POST\'' .
        '] as $k => $v) { $_SERVER[$k] = $v; putenv("$k=$v"); } $_GET["url"] = "";
        define("ROOT_PATH", ' . var_export(ROOT_PATH, true) . ');
        require_once ROOT_PATH . "/app/core/Controller.php";
        require_once ROOT_PATH . "/app/controllers/AdminController.php";
        require_once ROOT_PATH . "/tests/support/InMemoryNotificationRepository.php";
        
        if (!class_exists("Auth")) {
            class Auth {
                public static function user() { return ["id" => 1, "role" => "admin"]; }
            }
        }
        
        class TestableAdminController extends AdminController {
            public $repo;
            public function __construct($state) {
                $this->repo = new InMemoryNotificationRepository($state);
            }
            protected function getNotificationRepository(): NotificationRepositoryInterface {
                return $this->repo;
            }
            protected function requireApiAdmin(): array {
                return ["id" => 1, "role" => "admin"];
            }
        }
        
        $controller = new TestableAdminController(' . $stateExport . ');
        $controller->repo->setSimulateFailure(' . $simFailExport . ');
        
        $_POST = ' . $postExport . ';
        
        ob_start();
        
        register_shutdown_function(function() use ($controller) {
            $out = ob_get_clean();
            $code = http_response_code() ?: 200;
            $ref = new ReflectionClass($controller->repo);
            $prop = $ref->getProperty("state");
            $prop->setAccessible(true);
            $finalState = $prop->getValue($controller->repo);
            // Prefix output with a magic string so we can parse it reliably
            echo "\n---TEST_WRAPPER_RESULT---\n";
            echo json_encode(["code" => $code, "output" => $out, "state" => $finalState]);
        });

        try {
            $controller->' . $method . '();
        } catch (Throwable $e) {
            http_response_code(500);
            echo "Exception: " . $e->getMessage();
        }
        ';
        
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_ctr_');
        file_put_contents($tmpFile, $wrapper);
        
        $cmd = PHP_BINARY . " -f " . escapeshellarg($tmpFile);
        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        
        $env = [];
        $env['SYSTEMROOT'] = getenv('SYSTEMROOT') ?: 'C:\\Windows';
        $env['PATH'] = getenv('PATH');
        $env['REQUEST_METHOD'] = empty($postData) ? 'GET' : 'POST';

        $process = proc_open($cmd, $descriptorspec, $pipes, null, $env);
        if (!empty($postData)) {
            fwrite($pipes[0], json_encode($postData));
        }
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        
        unlink($tmpFile);
        
        $parts = explode("\n---TEST_WRAPPER_RESULT---\n", $output);
        $jsonStr = end($parts);
        
        $res = json_decode($jsonStr, true);
        if ($res === null) {
            echo "\n--- FATAL: WRAPPER FAILED ---\n";
            echo "STDOUT:\n" . $output . "\n";
            echo "STDERR:\n" . $stderr . "\n";
            exit(1);
        }
        
        return $res;
    }

    public function run()
    {
        $this->log("========================================================");
        $this->log("=== CP03 ADMIN NOTIFICATION SECURITY TEST SUITE      ===");
        $this->log("========================================================");

        $this->testFrontControllerSecurity();
        $this->testControllerLogic();
        $this->testSourceSecurityScan();
        $this->checkDatabaseIntegrity();
        
        echo implode("\n", $this->logs) . "\n";
        echo "\n========================================================\n";
        echo "CP03 Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "========================================================\n";

        if ($this->failed > 0) {
            exit(1);
        }
    }

    private function checkDatabaseIntegrity()
    {
        $this->log("\n--- Database Local Integrity ---");
        if ($this->dbLocalSha === null) {
            $this->assert(false, "DB Config File", "database.local.php does not exist");
        } else {
            $currentSha = hash_file('sha256', $this->dbLocalPath);
            $this->assert($currentSha === $this->dbLocalSha, "DB Config Unmodified", "database.local.php was modified during tests!");
        }
    }

    private function testFrontControllerSecurity()
    {
        $this->log("\n--- Front-Controller Authorization & CSRF ---");
        
        // 1. Missing CSRF
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
        ], ['csrf_token' => 'real_token', 'user' => ['id' => 1, 'role' => 'admin']], ['id' => 1]);
        $this->assert($res['code'] === 403, "Missing CSRF -> HTTP 403");
        
        // 2. Wrong CSRF
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => 'wrong',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
        ], ['csrf_token' => 'real_token', 'user' => ['id' => 1, 'role' => 'admin']], ['id' => 1]);
        $this->assert($res['code'] === 403, "Wrong CSRF -> HTTP 403");

        // 3. Guest Authorization (No Session)
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => 'real_token',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
        ], ['csrf_token' => 'real_token'], ['id' => 1]);
        $this->assert($res['code'] === 401 || $res['code'] === 403, "Guest POST -> HTTP 401/403");
        
        // 4. Customer Authorization
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => 'real_token',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
        ], ['csrf_token' => 'real_token', 'user' => ['id' => 2, 'role' => 'customer']], ['id' => 1]);
        $this->assert($res['code'] === 403, "Customer POST -> HTTP 403");

        // 5. GET guest/customer authorization
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'GET',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'REQUEST_URI' => '/api/admin/notifications',
            'QUERY_STRING' => 'url=api/admin/notifications',
        ], ['user' => ['id' => 2, 'role' => 'customer']]);
        $this->assert($res['code'] === 403, "Customer GET -> HTTP 403");

        // 6. DB Unavailable (FORCE_DB_FAILURE=1)
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'GET',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'REQUEST_URI' => '/api/admin/notifications',
            'QUERY_STRING' => 'url=api/admin/notifications',
            'FORCE_DB_FAILURE' => '1'
        ], ['user' => ['id' => 1, 'role' => 'admin']]);
        $this->assert($res['code'] === 503, "DB Unavailable -> HTTP 503");
    }

    private function testControllerLogic()
    {
        $this->log("\n--- Controller Deterministic Business Logic ---");
        
        $initialState = [
            ['id' => 1, 'user_id' => 1, 'is_read' => 0, 'title' => 'T', 'content' => 'C', 'created_at' => '2023-01-01 00:00:00', 'link' => null],
            ['id' => 2, 'user_id' => 1, 'is_read' => 1, 'title' => 'T', 'content' => 'C', 'created_at' => '2023-01-01 00:00:00', 'link' => null],
            ['id' => 3, 'user_id' => 2, 'is_read' => 0, 'title' => 'T', 'content' => 'C', 'created_at' => '2023-01-01 00:00:00', 'link' => null],
            ['id' => 4, 'user_id' => 1, 'is_read' => 0, 'title' => 'T', 'content' => 'C', 'created_at' => '2023-01-01 00:00:00', 'link' => null]
        ];

        // 1. Owned notification ID
        $res = $this->runControllerTest('markReadNotifications', ['id' => 1], $initialState);
        $this->assert($res['code'] === 200, "Valid specific ID -> HTTP 200");
        $out = json_decode($res['output'], true);
        $this->assert($out['success'] === true, "success=true");
        $is_read_0 = $res['state'][0]['is_read'];
        if ($is_read_0 !== 1) {
            $this->log("DEBUG: state[0] is " . json_encode($res['state'][0]));
        }
        $this->assert($is_read_0 === 1, "Only target is read");
        $this->assert($res['state'][2]['is_read'] === 0, "Customer notif remains unread");

        // 2. Already-read ID
        $res = $this->runControllerTest('markReadNotifications', ['id' => 2], $initialState);
        $this->assert($res['code'] === 200, "Already read ID -> HTTP 200");
        $out = json_decode($res['output'], true);
        $this->assert($out['success'] === true, "success=true");
        $this->assert($res['state'][1]['is_read'] === 1, "State unchanged");

        // 3. Cross-user numeric ID
        $res = $this->runControllerTest('markReadNotifications', ['id' => 3], $initialState);
        $this->assert($res['code'] === 404, "Cross-user ID -> HTTP 404");
        $out = json_decode($res['output'], true);
        $this->assert($out['error']['code'] === 'NOTIFICATION_NOT_FOUND', "code NOTIFICATION_NOT_FOUND");
        $this->assert($res['state'][2]['is_read'] === 0, "State unchanged");

        // 4. Invalid ID
        $res = $this->runControllerTest('markReadNotifications', ['id' => '1 OR 1=1'], $initialState);
        $this->assert($res['code'] === 400, "Invalid ID -> HTTP 400");
        $out = json_decode($res['output'], true);
        $this->assert($out['error']['code'] === 'INVALID_NOTIFICATION_ID', "code INVALID_NOTIFICATION_ID");

        // 5. Mark-all exact scope
        $res = $this->runControllerTest('markReadNotifications', [], $initialState);
        $this->assert($res['code'] === 200, "Mark-all -> HTTP 200");
        $this->assert($res['state'][0]['is_read'] === 1 && $res['state'][3]['is_read'] === 1, "Admin notifs marked read");
        $this->assert($res['state'][2]['is_read'] === 0, "Customer notif unchanged");

        // 6. Repository failure
        $res = $this->runControllerTest('markReadNotifications', ['id' => 1], $initialState, true);
        $this->assert($res['code'] === 503, "Repository failure -> HTTP 503");
        
        // 7. GET admin success
        $res = $this->runControllerTest('notifications', [], $initialState);
        $this->assert($res['code'] === 200, "GET notifications -> HTTP 200");
        $out = json_decode($res['output'], true);
        $this->assert($out['success'] === true && $out['unread'] === 2, "Success and unread count is correct");
    }

    private function testSourceSecurityScan()
    {
        $this->log("\n--- Source Code Security Scan ---");
        
        $jsContent = file_get_contents(ROOT_PATH . '/public/assets/js/admin-notifications.js');
        $hasInnerHTML = strpos($jsContent, 'innerHTML') !== false;
        $hasOnclick = strpos($jsContent, 'onclick') !== false;
        
        $this->assert(!$hasInnerHTML, "No innerHTML in admin-notifications.js", "Found innerHTML usage");
        $this->assert(!$hasOnclick, "No inline onclick in admin-notifications.js", "Found onclick usage");

        $layoutContent = file_get_contents(ROOT_PATH . '/app/views/admin/layout.php');
        $hasBaseUrlMeta = strpos($layoutContent, '<meta name="app-base-url"') !== false;
        $escapedBaseUrl = preg_match('/<meta name="app-base-url"\s+content="<\?=\s*e\(url\(/', $layoutContent);
        
        $this->assert($hasBaseUrlMeta, "app-base-url meta tag exists");
        $this->assert($escapedBaseUrl, "Meta URL attributes are correctly escaped with e()");
    }
}

$test = new CP03AdminNotificationSecurityTest();
$test->run();
