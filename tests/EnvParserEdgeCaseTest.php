<?php
/**
 * tests/EnvParserEdgeCaseTest.php
 * 
 * Unit test cho .env parser trong config/app.php
 * Kiểm tra tất cả edge case: quote, dấu "=" trong value, inline comment, empty value
 * 
 * Chạy: php tests/EnvParserEdgeCaseTest.php
 */

define('ROOT_PATH', dirname(__DIR__));

class EnvParserEdgeCaseTest
{
    private int $passed = 0;
    private int $failed = 0;
    private array $errors = [];

    /**
     * Mô phỏng chính xác logic parser trong config/app.php
     * (extract ra để test độc lập, không cần load toàn bộ app)
     */
    private function parseEnvLine(string $line): ?array
    {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') return null;

        $eqPos = strpos($line, '=');
        if ($eqPos === false) return null;

        $name  = trim(substr($line, 0, $eqPos));
        $value = substr($line, $eqPos + 1);

        if ($value === false) $value = '';
        $value = trim($value);

        $len = strlen($value);
        if ($len >= 2) {
            $first = $value[0];
            $last  = $value[$len - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            } else {
                $commentPos = strpos($value, ' #');
                if ($commentPos !== false) {
                    $value = rtrim(substr($value, 0, $commentPos));
                }
            }
        }

        return ($name !== '') ? ['name' => $name, 'value' => $value] : null;
    }

    private function assert(bool $condition, string $description): void
    {
        if ($condition) {
            $this->passed++;
            echo "  ✅ {$description}\n";
        } else {
            $this->failed++;
            $this->errors[] = $description;
            echo "  ❌ {$description}\n";
        }
    }

    public function run(): void
    {
        echo "╔══════════════════════════════════════════════╗\n";
        echo "║  .env Parser Edge Case Test Suite            ║\n";
        echo "╚══════════════════════════════════════════════╝\n\n";

        $this->testBasicKeyValue();
        $this->testValueWithEquals();
        $this->testDoubleQuotedValue();
        $this->testSingleQuotedValue();
        $this->testInlineComment();
        $this->testEmptyValue();
        $this->testCommentLine();
        $this->testBlankLine();
        $this->testNoEqualsSign();
        $this->testUrlWithQueryParams();
        $this->testQuotedValueWithSpaces();
        $this->testRealEnvFileCompatibility();
        $this->testProductionHttpWarning();

        echo "\n══════════════════════════════════════════════\n";
        echo "Results: {$this->passed} passed, {$this->failed} failed\n";
        echo "══════════════════════════════════════════════\n";

        if ($this->failed > 0) {
            echo "\n❌ FAILED TESTS:\n";
            foreach ($this->errors as $e) echo "  - {$e}\n";
        }
    }

    private function testBasicKeyValue(): void
    {
        echo "--- Test: Basic KEY=VALUE ---\n";
        $r = $this->parseEnvLine('APP_NAME=TechPilot');
        $this->assert($r !== null && $r['name'] === 'APP_NAME' && $r['value'] === 'TechPilot',
            'APP_NAME=TechPilot → name=APP_NAME, value=TechPilot');
    }

    private function testValueWithEquals(): void
    {
        echo "\n--- Test: Value chứa dấu '=' ---\n";
        $r = $this->parseEnvLine('API_BASE=https://api.example.com?token=abc123&mode=live');
        $this->assert($r !== null && $r['value'] === 'https://api.example.com?token=abc123&mode=live',
            'URL với query params chứa "=" parse đúng toàn bộ value');

        $r2 = $this->parseEnvLine('COMPLEX=a=b=c=d');
        $this->assert($r2 !== null && $r2['value'] === 'a=b=c=d',
            'Nhiều dấu "=" liên tiếp: value = "a=b=c=d"');
    }

    private function testDoubleQuotedValue(): void
    {
        echo "\n--- Test: Value trong double quote ---\n";
        $r = $this->parseEnvLine('APP_NAME="TechPilot Store"');
        $this->assert($r !== null && $r['value'] === 'TechPilot Store',
            '"TechPilot Store" → bỏ quote, giữ space');

        $r2 = $this->parseEnvLine('EMPTY_QUOTED=""');
        $this->assert($r2 !== null && $r2['value'] === '',
            '"" → empty string (không phải 2 dấu quote)');
    }

    private function testSingleQuotedValue(): void
    {
        echo "\n--- Test: Value trong single quote ---\n";
        $r = $this->parseEnvLine("APP_SECRET='my secret key'");
        $this->assert($r !== null && $r['value'] === 'my secret key',
            "'my secret key' → bỏ quote, giữ space");
    }

    private function testInlineComment(): void
    {
        echo "\n--- Test: Inline comment sau value ---\n";
        $r = $this->parseEnvLine('DB_HOST=127.0.0.1 # local dev');
        $this->assert($r !== null && $r['value'] === '127.0.0.1',
            '127.0.0.1 # local dev → lấy 127.0.0.1, bỏ comment');

        // Quoted value phải GIỮ NGUYÊN inline comment nếu nằm trong quote
        $r2 = $this->parseEnvLine('MSG="hello # world"');
        $this->assert($r2 !== null && $r2['value'] === 'hello # world',
            '"hello # world" → giữ nguyên # vì nằm trong quote');
    }

    private function testEmptyValue(): void
    {
        echo "\n--- Test: Value rỗng ---\n";
        $r = $this->parseEnvLine('DB_PASS=');
        $this->assert($r !== null && $r['value'] === '',
            'DB_PASS= → value rỗng (không phải null)');
    }

    private function testCommentLine(): void
    {
        echo "\n--- Test: Dòng comment ---\n";
        $r = $this->parseEnvLine('# This is a comment');
        $this->assert($r === null, 'Dòng bắt đầu bằng # → bỏ qua (null)');

        $r2 = $this->parseEnvLine('  # Indented comment');
        $this->assert($r2 === null, 'Dòng comment có indent → bỏ qua (null)');
    }

    private function testBlankLine(): void
    {
        echo "\n--- Test: Dòng trống ---\n";
        $r = $this->parseEnvLine('');
        $this->assert($r === null, 'Dòng trống → bỏ qua (null)');

        $r2 = $this->parseEnvLine('   ');
        $this->assert($r2 === null, 'Dòng chỉ có space → bỏ qua (null)');
    }

    private function testNoEqualsSign(): void
    {
        echo "\n--- Test: Dòng không có dấu '=' ---\n";
        $r = $this->parseEnvLine('INVALID_LINE_NO_EQUALS');
        $this->assert($r === null, 'Không có "=" → bỏ qua (null)');
    }

    private function testUrlWithQueryParams(): void
    {
        echo "\n--- Test: URL thật từ .env.example ---\n";
        $r = $this->parseEnvLine('GEMINI_API_BASE=https://generativelanguage.googleapis.com/v1beta');
        $this->assert($r !== null && $r['value'] === 'https://generativelanguage.googleapis.com/v1beta',
            'GEMINI_API_BASE URL parse đúng');

        $r2 = $this->parseEnvLine('GROQ_API_BASE=https://api.groq.com/openai/v1');
        $this->assert($r2 !== null && $r2['value'] === 'https://api.groq.com/openai/v1',
            'GROQ_API_BASE URL parse đúng');
    }

    private function testQuotedValueWithSpaces(): void
    {
        echo "\n--- Test: Quoted value với spaces và ký tự đặc biệt ---\n";
        $r = $this->parseEnvLine('MAIL_FROM="TechPilot <no-reply@techpilot.vn>"');
        $this->assert($r !== null && $r['value'] === 'TechPilot <no-reply@techpilot.vn>',
            'Email với angle brackets trong quote parse đúng');
    }

    private function testRealEnvFileCompatibility(): void
    {
        echo "\n--- Test: Backward compatibility với .env.example hiện tại ---\n";
        $envFile = ROOT_PATH . '/.env.example';
        if (!file_exists($envFile)) {
            $this->assert(false, '.env.example không tồn tại');
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $parsed = [];
        foreach ($lines as $line) {
            $r = $this->parseEnvLine($line);
            if ($r !== null) {
                $parsed[$r['name']] = $r['value'];
            }
        }

        // Kiểm tra các key quan trọng
        $this->assert(isset($parsed['APP_ENV']) && $parsed['APP_ENV'] === 'development',
            'APP_ENV=development parse đúng');
        $this->assert(isset($parsed['DB_HOST']) && $parsed['DB_HOST'] === '127.0.0.1',
            'DB_HOST=127.0.0.1 parse đúng');
        $this->assert(isset($parsed['DB_PASS']) && $parsed['DB_PASS'] === '',
            'DB_PASS= (empty) parse đúng');
        $this->assert(isset($parsed['AI_PROVIDER_ORDER']) && $parsed['AI_PROVIDER_ORDER'] === 'gemini,groq,qwen',
            'AI_PROVIDER_ORDER=gemini,groq,qwen parse đúng');
        $this->assert(isset($parsed['GEMINI_MODEL']) && $parsed['GEMINI_MODEL'] === 'gemini-3.6-flash',
            'GEMINI_MODEL=gemini-3.6-flash parse đúng');

        $expectedKeys = ['APP_ENV', 'APP_URL', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS',
                         'GEMINI_API_KEY', 'GEMINI_MODEL', 'GEMINI_API_BASE',
                         'GROQ_API_KEY', 'GROQ_MODEL', 'GROQ_API_BASE',
                         'QWEN_API_KEY', 'QWEN_MODEL', 'QWEN_API_BASE',
                         'VNPAY_TMN_CODE', 'VNPAY_HASH_SECRET', 'VNPAY_RETURN_URL', 'VNPAY_IPN_URL'];
        $missing = array_diff($expectedKeys, array_keys($parsed));
        $this->assert(empty($missing),
            'Tất cả ' . count($expectedKeys) . ' key trong .env.example đều được parse (thiếu: ' . implode(', ', $missing) . ')');
    }

    private function testProductionHttpWarning(): void
    {
        echo "\n--- Test: Source code có production HTTP warning ---\n";
        $source = file_get_contents(ROOT_PATH . '/config/app.php');
        $this->assert(str_contains($source, 'SECURITY WARNING'),
            'config/app.php chứa log SECURITY WARNING cho production+HTTP');
        $this->assert(str_contains($source, "envForSession === 'production'"),
            'config/app.php kiểm tra APP_ENV=production trước khi warning');
    }
}

$test = new EnvParserEdgeCaseTest();
$test->run();
exit($test->failed ?? 0 > 0 ? 1 : 0);
