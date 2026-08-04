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
                return ["id" => 7, "role" => "admin"];
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
        $this->testPdoNotificationRepositoryOwnership();
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
        // Assert no .bak file exists
        $bakPath = ROOT_PATH . '/config/database.local.php.bak';
        $this->assert(!file_exists($bakPath), "config/database.local.php.bak does not exist");

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
        ], ['csrf_token' => 'real_token', 'user' => ['id' => 7, 'role' => 'admin']], ['id' => 1]);
        $this->assert($res['code'] === 403, "Missing CSRF -> HTTP 403");
        $this->assert(strpos($res['output'], 'CSRF_TOKEN_MISMATCH') !== false, "Missing CSRF -> CSRF_TOKEN_MISMATCH");
        
        // 2. Wrong CSRF
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => 'wrong',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
        ], ['csrf_token' => 'real_token', 'user' => ['id' => 7, 'role' => 'admin']], ['id' => 1]);
        $this->assert($res['code'] === 403, "Wrong CSRF -> HTTP 403");
        $this->assert(strpos($res['output'], 'CSRF_TOKEN_MISMATCH') !== false, "Wrong CSRF -> CSRF_TOKEN_MISMATCH");

        // 3. Guest Authorization (No Session)
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'GET',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'REQUEST_URI' => '/api/admin/notifications',
            'QUERY_STRING' => 'url=api/admin/notifications',
        ], [], []);
        $this->assert($res['code'] === 401, "Guest GET -> HTTP 401");
        
        // 4. Customer Authorization
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'GET',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'REQUEST_URI' => '/api/admin/notifications',
            'QUERY_STRING' => 'url=api/admin/notifications',
        ], ['user' => ['id' => 2, 'role' => 'customer']]);
        $this->assert($res['code'] === 403, "Customer GET -> HTTP 403");

        // 5. DB Unavailable GET
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'GET',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'REQUEST_URI' => '/api/admin/notifications',
            'QUERY_STRING' => 'url=api/admin/notifications',
            'FORCE_DB_FAILURE' => '1'
        ], ['user' => ['id' => 7, 'role' => 'admin']]);
        $this->assert($res['code'] === 503, "GET DB unavailable -> HTTP 503");
        $out = json_decode($res['output'], true);
        $this->assert($out['success'] === false && $out['error']['code'] === 'DATABASE_UNAVAILABLE', "GET DB unavailable -> success=false + DATABASE_UNAVAILABLE");

        // 6. DB Unavailable POST
        $res = $this->runIndexPhp([
            'REQUEST_METHOD' => 'POST',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_X_CSRF_TOKEN' => 'real_token',
            'REQUEST_URI' => '/api/admin/notifications/mark_read',
            'QUERY_STRING' => 'url=api/admin/notifications/mark_read',
            'FORCE_DB_FAILURE' => '1'
        ], ['csrf_token' => 'real_token', 'user' => ['id' => 7, 'role' => 'admin']], ['id' => 1]);
        $this->assert($res['code'] === 503, "POST DB unavailable -> HTTP 503");
        $out = json_decode($res['output'], true);
        $this->assert($out['success'] === false && $out['error']['code'] === 'DATABASE_UNAVAILABLE', "POST DB unavailable -> success=false + DATABASE_UNAVAILABLE");
    }

    private function testControllerLogic()
    {
        $this->log("\n--- Controller Deterministic Business Logic ---");
        
        // admin ID=7 is admin-owned (user_id=7)
        // user_id=1 is shared (admin root)
        // user_id=2 is customer (out-of-scope)
        $initialState = [
            ['id' => 1, 'user_id' => 7, 'is_read' => 0, 'title' => 'T', 'content' => 'C', 'created_at' => '2023-01-01 00:00:00', 'link' => null],
            ['id' => 2, 'user_id' => 1, 'is_read' => 1, 'title' => 'T', 'content' => 'C', 'created_at' => '2023-01-01 00:00:00', 'link' => null],
            ['id' => 3, 'user_id' => 2, 'is_read' => 0, 'title' => 'T', 'content' => 'C', 'created_at' => '2023-01-01 00:00:00', 'link' => null],
            ['id' => 4, 'user_id' => 1, 'is_read' => 0, 'title' => 'T', 'content' => 'C', 'created_at' => '2023-01-01 00:00:00', 'link' => null]
        ];

        // 1. Admin-owned notification
        $res = $this->runControllerTest('markReadNotifications', ['id' => 1], $initialState);
        $this->assert($res['code'] === 200, "Admin-owned ID -> HTTP 200");
        $out = json_decode($res['output'], true);
        $this->assert($out['success'] === true, "Admin-owned ID -> success=true");
        $this->assert($res['state'][0]['is_read'] === 1, "Admin-owned ID -> state changed");
        $this->assert($res['state'][2]['is_read'] === 0, "Customer notif remains unread");

        // 2. Already-read ID (shared, user_id=1)
        $res = $this->runControllerTest('markReadNotifications', ['id' => 2], $initialState);
        $this->assert($res['code'] === 200, "Already-read shared ID -> HTTP 200");
        $out = json_decode($res['output'], true);
        $this->assert($out['success'] === true, "Already-read ID -> success=true (idempotent)");
        $this->assert($res['state'][1]['is_read'] === 1, "Already-read ID -> state unchanged");

        // 3. Cross-user ID (user_id=2, customer)
        $res = $this->runControllerTest('markReadNotifications', ['id' => 3], $initialState);
        $this->assert($res['code'] === 404, "Cross-user ID -> HTTP 404");
        $out = json_decode($res['output'], true);
        $this->assert($out['error']['code'] === 'NOTIFICATION_NOT_FOUND', "Cross-user ID -> NOTIFICATION_NOT_FOUND");
        $this->assert($res['state'][2]['is_read'] === 0, "Cross-user ID -> state unchanged");

        // 4. Invalid ID (SQL injection attempt)
        $res = $this->runControllerTest('markReadNotifications', ['id' => '1 OR 1=1'], $initialState);
        $this->assert($res['code'] === 400, "Invalid ID -> HTTP 400");
        $out = json_decode($res['output'], true);
        $this->assert($out['error']['code'] === 'INVALID_NOTIFICATION_ID', "Invalid ID -> INVALID_NOTIFICATION_ID");

        // 5. Mark-all: admin-owned (user_id=7) and shared (user_id=1) change; customer (user_id=2) unchanged
        $res = $this->runControllerTest('markReadNotifications', [], $initialState);
        $this->assert($res['code'] === 200, "Mark-all -> HTTP 200");
        $this->assert($res['state'][0]['is_read'] === 1, "Mark-all -> admin-owned notif (user_id=7) marked read");
        $this->assert($res['state'][3]['is_read'] === 1, "Mark-all -> shared notif (user_id=1) marked read");
        $this->assert($res['state'][2]['is_read'] === 0, "Mark-all -> customer notif (user_id=2) unchanged");

        // 6. Repository failure -> 503 + success=false + full state unchanged
        $res = $this->runControllerTest('markReadNotifications', ['id' => 1], $initialState, true);
        $this->assert($res['code'] === 503, "Repository failure -> HTTP 503");
        $out = json_decode($res['output'], true);
        $this->assert($out['success'] === false, "Repository failure -> success=false");
        $this->assert($res['state'][0]['is_read'] === 0, "Repository failure -> state unchanged (id=1)");
        $this->assert($res['state'][2]['is_read'] === 0, "Repository failure -> state unchanged (id=3)");

        // 7. GET admin success
        $res = $this->runControllerTest('notifications', [], $initialState);
        $this->assert($res['code'] === 200, "GET notifications -> HTTP 200");
        $out = json_decode($res['output'], true);
        $this->assert($out['success'] === true && $out['unread'] === 2, "GET notifications -> success=true, unread=2");
    }

    private function testPdoNotificationRepositoryOwnership()
    {
        $this->log("\n--- PDO Repository Ownership Validation ---");
        
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $db->exec('CREATE TABLE notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            is_read INTEGER DEFAULT 0,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
        
        $db->exec("INSERT INTO notifications (id, user_id, is_read, title, content) VALUES (1, 7, 0, 'T', 'C')");
        $db->exec("INSERT INTO notifications (id, user_id, is_read, title, content) VALUES (2, 1, 0, 'T', 'C')");
        $db->exec("INSERT INTO notifications (id, user_id, is_read, title, content) VALUES (3, 2, 0, 'T', 'C')");
        
        require_once ROOT_PATH . '/app/services/PdoNotificationRepository.php';
        $repo = new PdoNotificationRepository();
        
        // Admin 7 reads own notif
        $res = $repo->markRead($db, 1, 7);
        $this->assert($res === true, "Own ID: return true");
        $row = $db->query("SELECT is_read FROM notifications WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
        $this->assert($row['is_read'] == 1, "Own ID: state đổi");
        
        // Admin 7 reads shared notif (user_id = 1)
        $res = $repo->markRead($db, 2, 7);
        $this->assert($res === true, "Shared ID: return true");
        $row = $db->query("SELECT is_read FROM notifications WHERE id = 2")->fetch(PDO::FETCH_ASSOC);
        $this->assert($row['is_read'] == 1, "Shared ID: state đổi");
        
        // Admin 7 tries to read Customer 2's notif
        $res = $repo->markRead($db, 3, 7);
        $this->assert($res === false, "Out-of-scope ID: return false");
        $row = $db->query("SELECT is_read FROM notifications WHERE id = 3")->fetch(PDO::FETCH_ASSOC);
        $this->assert($row['is_read'] == 0, "Out-of-scope ID: state không đổi");

        // Admin 7 tries to read missing ID
        $res = $repo->markRead($db, 999, 7);
        $this->assert($res === false, "Missing ID: return false");
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
