<?php

define('ROOT_PATH', dirname(__DIR__));

require_once __DIR__ . '/../app/core/SecureCurl.php';
require_once __DIR__ . '/../app/services/AiService.php';
require_once __DIR__ . '/../app/services/GeminiService.php';
require_once __DIR__ . '/../app/services/SpecScraperService.php';

class CP02TlsFailClosedRegressionTest
{
    private array $envBackup = [];

    public function run()
    {
        $this->setup();
        $passed = 0;
        $failed = 0;

        $tests = [
            'testEmptyCaConfigUsesSystemTrustStore',
            'testValidCaConfigAppliesToOptions',
            'testInvalidCaConfigThrowsException',
            'testAiServiceGeminiNativeHandlesInvalidCa',
            'testAiServiceOpenAiCompatibleHandlesInvalidCa',
            'testGeminiServiceListModelsHandlesInvalidCa',
            'testSpecScraperServiceHandlesInvalidCa',
            'testSourceSecurityScanForInsecureTls'
        ];

        foreach ($tests as $test) {
            try {
                $this->$test();
                echo "[PASS] {$test}\n";
                $passed++;
            } catch (Exception $e) {
                echo "[FAIL] {$test}: " . $e->getMessage() . "\n";
                $failed++;
            }
        }

        $this->teardown();

        echo "\n========================================================\n";
        echo "CP02 TLS Fail-Closed Results: {$passed} passed, {$failed} failed\n";
        echo "========================================================\n";

        if ($failed > 0) {
            exit(1);
        }
    }

    private function setup()
    {
        $vars = ['CURL_CA_BUNDLE', 'SSL_CERT_FILE'];
        foreach ($vars as $var) {
            $this->envBackup[$var] = getenv($var);
        }
    }

    private function teardown()
    {
        foreach ($this->envBackup as $var => $val) {
            if ($val === false) {
                putenv($var);
            } else {
                putenv("{$var}={$val}");
            }
        }
    }

    private function clearCaEnv()
    {
        putenv('CURL_CA_BUNDLE=');
        putenv('SSL_CERT_FILE=');
        // We can't modify ini_get('curl.cainfo') dynamically easily via ini_set in all environments, 
        // but we'll assume the environment doesn't have it strictly set, or we'll override using env vars for tests.
    }

    private function assert($condition, $message)
    {
        if (!$condition) {
            throw new Exception($message);
        }
    }

    private function testEmptyCaConfigUsesSystemTrustStore()
    {
        $this->clearCaEnv();
        
        // Backup ini values if possible to truly test empty, but ini_set might be restricted.
        $oldCa = ini_get('curl.cainfo');
        $oldSsl = ini_get('openssl.cafile');
        
        @ini_set('curl.cainfo', '');
        @ini_set('openssl.cafile', '');

        try {
            $options = SecureCurl::buildTlsOptions();

            $this->assert($options[CURLOPT_SSL_VERIFYPEER] === true, 'CURLOPT_SSL_VERIFYPEER must be true');
            $this->assert($options[CURLOPT_SSL_VERIFYHOST] === 2, 'CURLOPT_SSL_VERIFYHOST must be 2');
            $this->assert(!isset($options[CURLOPT_CAINFO]) || str_contains((string)($options[CURLOPT_CAINFO] ?? ''), 'cacert.pem'), 'CURLOPT_CAINFO should not be set or should point to bundled cacert.pem');
        } catch (RuntimeException $e) {
            $ca = ini_get('curl.cainfo');
            $ssl = ini_get('openssl.cafile');
            if ((!empty($ca) && !file_exists($ca)) || (!empty($ssl) && !file_exists($ssl))) {
                $this->assert($e->getMessage() === 'TLS_CA_BUNDLE_INVALID', 'Must be invalid CA error');
            } else {
                throw $e;
            }
        } finally {
            @ini_set('curl.cainfo', $oldCa);
            @ini_set('openssl.cafile', $oldSsl);
        }
    }

    private function testValidCaConfigAppliesToOptions()
    {
        $this->clearCaEnv();
        
        $tempFile = tempnam(sys_get_temp_dir(), 'ca_test_');
        file_put_contents($tempFile, 'dummy ca');
        
        putenv("CURL_CA_BUNDLE={$tempFile}");
        
        try {
            $options = SecureCurl::buildTlsOptions();
            
            $this->assert($options[CURLOPT_SSL_VERIFYPEER] === true, 'CURLOPT_SSL_VERIFYPEER must be true');
            $this->assert($options[CURLOPT_SSL_VERIFYHOST] === 2, 'CURLOPT_SSL_VERIFYHOST must be 2');
            $this->assert(isset($options[CURLOPT_CAINFO]) && $options[CURLOPT_CAINFO] === $tempFile, 'CURLOPT_CAINFO must match valid temp file');
        } finally {
            unlink($tempFile);
        }
    }

    private function testInvalidCaConfigThrowsException()
    {
        $this->clearCaEnv();
        
        putenv("CURL_CA_BUNDLE=/path/that/does/not/exist/ca.pem");
        
        $threw = false;
        try {
            SecureCurl::buildTlsOptions();
        } catch (RuntimeException $e) {
            $threw = true;
            $this->assert($e->getMessage() === 'TLS_CA_BUNDLE_INVALID', 'Must throw TLS_CA_BUNDLE_INVALID');
        }
        
        $this->assert($threw, 'Must throw exception for invalid CA path');
    }

    private function testAiServiceGeminiNativeHandlesInvalidCa()
    {
        $this->clearCaEnv();
        putenv("CURL_CA_BUNDLE=/path/to/nowhere.pem");
        
        // Force provider order to gemini to test Gemini Native
        // AiService will try to call gemini native and catch RuntimeException from SecureCurl
        // Ensure API key is set for test
        putenv('GEMINI_API_KEY=test_key');
        
        $result = cloneAiServiceAndCallGenerateContent();
        
        $this->assert($result['success'] === false, 'Result must fail');
        $this->assert($result['error_code'] === 'TLS_CA_BUNDLE_INVALID', 'Error code must be TLS_CA_BUNDLE_INVALID');
    }

    private function testAiServiceOpenAiCompatibleHandlesInvalidCa()
    {
        $this->clearCaEnv();
        putenv("CURL_CA_BUNDLE=/path/to/nowhere.pem");
        
        putenv('GROQ_API_KEY=test_key');
        // AiService loop will try Gemini then Groq. If we unset Gemini, it tries Groq.
        putenv('GEMINI_API_KEY=');
        
        $result = cloneAiServiceAndCallGenerateContent();
        
        $this->assert($result['success'] === false, 'Result must fail');
        $this->assert($result['error_code'] === 'TLS_CA_BUNDLE_INVALID', 'Error code must be TLS_CA_BUNDLE_INVALID');
        
        // Restore
        putenv('GEMINI_API_KEY=test_key');
    }
    
    private function testGeminiServiceListModelsHandlesInvalidCa()
    {
        $this->clearCaEnv();
        putenv("CURL_CA_BUNDLE=/path/to/nowhere.pem");
        putenv('GEMINI_API_KEY=test_key');
        
        $result = GeminiService::listAvailableModels();
        
        $this->assert($result['success'] === false, 'Result must fail');
        $this->assert($result['error_code'] === 'TLS_CA_BUNDLE_INVALID', 'Error code must be TLS_CA_BUNDLE_INVALID');
    }
    
    private function testSpecScraperServiceHandlesInvalidCa()
    {
        $this->clearCaEnv();
        putenv("CURL_CA_BUNDLE=/path/to/nowhere.pem");
        
        // SpecScraperService ignores exception and returns empty string
        $result = SpecScraperService::fetchUrlContent('https://example.com');
        
        $this->assert($result === '', 'Result must be empty string on invalid CA');
    }

    private function testSourceSecurityScanForInsecureTls()
    {
        $directories = [ROOT_PATH . '/app', ROOT_PATH . '/scripts', ROOT_PATH . '/public', ROOT_PATH . '/cron'];
        $insecureFound = [];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) continue;
            
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $content = file_get_contents($file->getPathname());
                    if (str_contains($content, 'CURLOPT_SSL_VERIFYPEER') && preg_match('/CURLOPT_SSL_VERIFYPEER\s*=>\s*false/i', $content)) {
                        $insecureFound[] = $file->getPathname() . ' contains CURLOPT_SSL_VERIFYPEER => false';
                    }
                    if (str_contains($content, 'CURLOPT_SSL_VERIFYHOST') && preg_match('/CURLOPT_SSL_VERIFYHOST\s*=>\s*0/i', $content)) {
                        $insecureFound[] = $file->getPathname() . ' contains CURLOPT_SSL_VERIFYHOST => 0';
                    }
                }
            }
        }
        
        $this->assert(empty($insecureFound), "Found insecure TLS usage in source:\n" . implode("\n", $insecureFound));
    }
}

// Helper to bypass config loading real env if needed, but AiService uses getenv() dynamically.
function cloneAiServiceAndCallGenerateContent() {
    return AiService::generateContent('Test prompt', ['timeout' => 1]);
}

$test = new CP02TlsFailClosedRegressionTest();
$test->run();
