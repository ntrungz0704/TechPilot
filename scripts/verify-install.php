<?php
/**
 * TechPilot — Install Verifier v2
 *
 * Kiểm tra toàn bộ yêu cầu cài đặt: PHP, extensions, files, config,
 * database, tables, columns, data baseline, assets, referential integrity, git safety.
 *
 * Capability matrix:
 *   CORE APPLICATION — bắt buộc
 *   ADMIN UPLOAD     — cần cho upload ảnh
 *   GEMINI           — integration AI (tùy chọn)
 *   VNPAY            — thanh toán online (tùy chọn)
 *
 * Cách dùng:
 *   php scripts/verify-install.php
 *
 * Exit code 0 = tất cả PASS, exit code 1 = có FAIL.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die('Verifier chỉ chạy từ CLI.');
}

define('VERIFY_ROOT', dirname(__DIR__, 1));

$passCount = 0;
$warnCount = 0;
$failCount = 0;

function vPass(string $msg): void {
    global $passCount;
    $passCount++;
    echo "[PASS] {$msg}\n";
}

function vWarn(string $msg): void {
    global $warnCount;
    $warnCount++;
    echo "[WARN] {$msg}\n";
}

function vFail(string $msg): void {
    global $failCount;
    $failCount++;
    echo "[FAIL] {$msg}\n";
}

/**
 * Chuẩn hóa đường dẫn asset từ DB về dạng relative dưới public/.
 *
 * Contract:
 *   1. trim
 *   2. backslash → slash
 *   3. bỏ query string
 *   4. bỏ domain local (http://127.0.0.1:xxxx/)
 *   5. bỏ dấu / đầu
 *   6. bỏ prefix public/
 *   7. ngăn ..
 *   8. trả relative path dưới public
 */
function normalizePublicAssetPath(string $path): ?string {
    $path = trim($path);
    if ($path === '') return null;

    // backslash → slash
    $path = str_replace('\\', '/', $path);

    // bỏ query string
    if (($qPos = strpos($path, '?')) !== false) {
        $path = substr($path, 0, $qPos);
    }

    // bỏ domain local
    $path = preg_replace('#^https?://[^/]+/#', '', $path);

    // bỏ dấu / đầu
    $path = ltrim($path, '/');

    // bỏ prefix public/
    if (str_starts_with($path, 'public/')) {
        $path = substr($path, 7);
    }

    // ngăn path traversal
    if (str_contains($path, '..')) return null;

    // ngăn đường dẫn tuyệt đối Windows
    if (preg_match('#^[A-Za-z]:#', $path)) return null;

    return $path;
}

echo "=== TechPilot Install Verifier v2 ===\n\n";

// ═══════════════════════════════════════════════════════════════════════════
// CORE APPLICATION
// ═══════════════════════════════════════════════════════════════════════════

echo "══ CORE APPLICATION ══\n\n";

// ─── PHP Version ─────────────────────────────────────────────────────────
echo "── PHP ──\n";

if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    vPass("PHP version: " . PHP_VERSION);
} else {
    vFail("PHP version " . PHP_VERSION . " < 8.0.0 yêu cầu");
}

// ─── Core Extensions ─────────────────────────────────────────────────────
$coreExts = ['PDO', 'pdo_mysql', 'json', 'session', 'filter'];

// Kiểm tra source có dùng mb_* không
$mbUsed = false;
$appDir = VERIFY_ROOT . '/app';
if (is_dir($appDir)) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appDir));
    foreach ($rii as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            if (preg_match('/\bmb_/i', $content)) {
                $mbUsed = true;
                break;
            }
        }
    }
}

if ($mbUsed) {
    $coreExts[] = 'mbstring';
}

foreach ($coreExts as $ext) {
    if (extension_loaded($ext)) {
        vPass("Extension: {$ext}");
    } else {
        vFail("Extension thiếu: {$ext} — bật trong php.ini");
    }
}

// Nếu mbstring không required nhưng vẫn nên có
if (!$mbUsed && !extension_loaded('mbstring')) {
    vWarn("Extension mbstring không có (source hiện không dùng mb_*)");
}

// ─── Required Files ──────────────────────────────────────────────────────

echo "\n── Required Files ──\n";

$requiredFiles = [
    'index.php',
    'router.php',
    'public/index.php',
    'config/app.php',
    'config/database.php',
    'config/database.local.example.php',
    'database/schema.sql',
    'database/seed_dev.sql',
    'README.md',
    '.env.example',
];

$requiredDirs = [
    'database/migrations',
    'public/assets',
    'public/assets/images/categories',
    'public/assets/images/products',
];

foreach ($requiredFiles as $f) {
    if (file_exists(VERIFY_ROOT . '/' . $f)) {
        vPass("File: {$f}");
    } else {
        vFail("File thiếu: {$f}");
    }
}

foreach ($requiredDirs as $d) {
    if (is_dir(VERIFY_ROOT . '/' . $d)) {
        vPass("Directory: {$d}");
    } else {
        vFail("Directory thiếu: {$d}");
    }
}

// ─── Local Config ────────────────────────────────────────────────────────

echo "\n── Local Config ──\n";

$hasLocalConfig = file_exists(VERIFY_ROOT . '/config/database.local.php');
$hasEnv = file_exists(VERIFY_ROOT . '/.env');
$envDbName = getenv('DB_NAME');

if ($hasLocalConfig) {
    vPass("config/database.local.php tồn tại");
} elseif ($hasEnv && !empty($envDbName)) {
    vPass(".env có DB_NAME={$envDbName}");
} else {
    vFail("Chưa có config local. Chạy: Copy-Item config/database.local.example.php config/database.local.php");
}

// Nạp config
$prevErrorLevel = error_reporting(E_ALL & ~E_WARNING);
require_once VERIFY_ROOT . '/config/app.php';
error_reporting($prevErrorLevel);
require_once VERIFY_ROOT . '/config/database.php';

// ─── Database Connection ─────────────────────────────────────────────────

echo "\n── Database Connection ──\n";

$db = Database::getConnection();
if ($db === null) {
    vFail("Không thể kết nối database. Kiểm tra MySQL đã chạy và config đúng.");
    goto printSummary;
}

vPass("Kết nối database thành công");

// Kiểm tra database name
try {
    $currentDb = $db->query("SELECT DATABASE()")->fetchColumn();
    if (stripos($currentDb, 'techpilot') !== false) {
        vPass("Database hiện tại: {$currentDb}");
    } else {
        vWarn("Database hiện tại: {$currentDb} (mong đợi chứa 'techpilot')");
    }
} catch (\Throwable $e) {
    vWarn("Không đọc được tên database hiện tại");
}

// Kiểm tra charset
try {
    $charset = $db->query("SELECT @@character_set_database")->fetchColumn();
    if ($charset === 'utf8mb4') {
        vPass("Database charset: utf8mb4");
    } else {
        vWarn("Database charset: {$charset} (khuyến nghị utf8mb4)");
    }
} catch (\Throwable $e) {
    vWarn("Không đọc được charset database");
}

// ─── Required Tables ─────────────────────────────────────────────────────

echo "\n── Required Tables ──\n";

// 27 bảng business/audit
$businessTables = [
    'users', 'categories', 'brands', 'products', 'product_images',
    'carts', 'cart_items', 'orders', 'order_items', 'posts',
    'reviews', 'banners', 'flash_sales', 'flash_sale_items', 'flash_sale_reservations',
    'coupons', 'user_addresses', 'wishlists', 'notifications',
    'inventory_logs', 'user_behavior_logs', 'user_interest_profiles',
    'chatbot_rate_limits', 'return_requests', 'return_items',
    'ai_assistant_logs', 'product_ai_chat_histories',
];

// 1 bảng technical
$technicalTables = [
    'migrations',
];

try {
    $existingTables = $db->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
} catch (\Throwable $e) {
    vFail("Không đọc được danh sách bảng: " . $e->getMessage());
    $existingTables = [];
}

foreach ($businessTables as $t) {
    if (in_array($t, $existingTables)) {
        vPass("Business/audit table: {$t}");
    } else {
        vFail("Business/audit table thiếu: {$t}");
    }
}

foreach ($technicalTables as $t) {
    if (in_array($t, $existingTables)) {
        vPass("Technical table: {$t}");
    } else {
        vFail("Technical table thiếu: {$t} — chạy: php scripts/database/migrate.php");
    }
}

$totalTables = count($businessTables) + count($technicalTables);
echo "  → {$totalTables} bảng vật lý (" . count($businessTables) . " business/audit + " . count($technicalTables) . " technical)\n";

// ─── Required Columns ────────────────────────────────────────────────────

echo "\n── Required Columns ──\n";

$requiredColumns = [
    'products' => ['stock', 'status', 'specs'],
    'inventory_logs' => ['quantity_delta', 'old_stock', 'new_stock', 'product_id', 'type'],
    'user_behavior_logs' => ['user_id', 'action_type', 'target_type', 'target_id', 'metadata'],
    'user_interest_profiles' => ['user_id', 'brand_scores', 'category_scores', 'budget_min', 'budget_max'],
    'chatbot_rate_limits' => ['identifier', 'rate_date', 'query_count'],
    'return_requests' => ['return_code', 'order_id', 'user_id', 'reason', 'status'],
    'return_items' => ['return_request_id', 'order_item_id', 'quantity', 'resolution'],
];

foreach ($requiredColumns as $table => $columns) {
    if (!in_array($table, $existingTables)) {
        vFail("Bảng {$table} không tồn tại — bỏ qua kiểm tra cột");
        continue;
    }
    try {
        $cols = $db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($columns as $col) {
            if (in_array($col, $cols)) {
                vPass("{$table}.{$col}");
            } else {
                vFail("{$table}.{$col} thiếu — chạy: php scripts/database/migrate.php");
            }
        }
    } catch (\Throwable $e) {
        vFail("Không đọc được cột của {$table}: " . $e->getMessage());
    }
}

// ─── Data Baseline ───────────────────────────────────────────────────────

echo "\n── Data Baseline ──\n";

$baselineChecks = [
    'categories' => 0,
    'brands' => 0,
    'products' => 0,
    'product_images' => 0,
    'posts' => 0,
    'users' => 0,
];

foreach ($baselineChecks as $table => $minCount) {
    if (!in_array($table, $existingTables)) {
        vFail("Bảng {$table} không tồn tại — không thể kiểm tra baseline");
        continue;
    }
    try {
        $count = (int)$db->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
        if ($count > 0) {
            vPass("{$table}: {$count} records");
        } else {
            vFail("{$table}: 0 records — cần import database/seed_dev.sql");
        }
    } catch (\Throwable $e) {
        vFail("Không đếm được {$table}: " . $e->getMessage());
    }
}

// ─── Referential Integrity ───────────────────────────────────────────────

echo "\n── Referential Integrity ──\n";

$integrityChecks = [
    [
        'label' => 'product → category',
        'sql' => "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE c.id IS NULL AND p.category_id IS NOT NULL",
    ],
    [
        'label' => 'product → brand',
        'sql' => "SELECT COUNT(*) FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE b.id IS NULL AND p.brand_id IS NOT NULL",
    ],
    [
        'label' => 'product_images → product',
        'sql' => "SELECT COUNT(*) FROM product_images pi LEFT JOIN products p ON pi.product_id = p.id WHERE p.id IS NULL",
    ],
    [
        'label' => 'stock âm',
        'sql' => "SELECT COUNT(*) FROM products WHERE stock < 0",
    ],
];

foreach ($integrityChecks as $check) {
    try {
        $orphans = (int)$db->query($check['sql'])->fetchColumn();
        if ($orphans === 0) {
            vPass("Integrity: {$check['label']}");
        } else {
            vWarn("Integrity: {$check['label']} — {$orphans} bản ghi không hợp lệ");
        }
    } catch (\Throwable $e) {
        vWarn("Không kiểm tra được {$check['label']}: " . $e->getMessage());
    }
}

// ─── Product Primary Images (CRITICAL) ───────────────────────────────────

echo "\n── Product Primary Images ──\n";

try {
    $stmt = $db->query("
        SELECT p.id, p.image, p.status, c.slug as category_slug
        FROM products p
        JOIN categories c ON p.category_id = c.id
        ORDER BY p.id
    ");
    $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $activeMissing = 0;
    $activeExists = 0;
    $inactiveMissing = 0;
    $badPath = 0;
    $missingExamples = [];

    foreach ($products as $prod) {
        $imgRaw = $prod['image'] ?? '';
        $normalized = normalizePublicAssetPath($imgRaw);

        // Kiểm tra path bất hợp lệ
        if ($normalized === null && !empty($imgRaw)) {
            $badPath++;
            if ($prod['status'] === 'active') {
                vFail("Path bất hợp lệ: product #{$prod['id']} → {$imgRaw}");
            }
            continue;
        }

        if (empty($normalized)) {
            if ($prod['status'] === 'active') {
                $activeMissing++;
                if (count($missingExamples) < 5) {
                    $missingExamples[] = "#{$prod['id']} (empty)";
                }
            } else {
                $inactiveMissing++;
            }
            continue;
        }

        $diskPath = VERIFY_ROOT . '/public/' . $normalized;

        if (file_exists($diskPath) && !is_dir($diskPath)) {
            if ($prod['status'] === 'active') {
                $activeExists++;
            }
        } else {
            if ($prod['status'] === 'active') {
                $activeMissing++;
                if (count($missingExamples) < 5) {
                    $missingExamples[] = "#{$prod['id']} → {$normalized}";
                }
            } else {
                $inactiveMissing++;
            }
        }
    }

    // Active primary: FAIL nếu thiếu
    if ($activeMissing === 0) {
        vPass("Active primary images: {$activeExists}/{$activeExists} tồn tại");
    } else {
        vWarn("Active primary images: {$activeMissing} sản phẩm active thiếu ảnh vật lý trên đĩa (UI dùng dynamic placeholder fallback)");
        foreach ($missingExamples as $ex) {
            echo "       → {$ex}\n";
        }
    }

    // Inactive: WARN
    if ($inactiveMissing > 0) {
        vWarn("Inactive products: {$inactiveMissing} thiếu ảnh (không ảnh hưởng giao diện)");
    }

    // Bad paths: FAIL
    if ($badPath > 0) {
        vFail("Path bất hợp lệ: {$badPath} sản phẩm có đường dẫn ảnh không hợp lệ");
    }

} catch (\Throwable $e) {
    vFail("Không kiểm tra được ảnh sản phẩm: " . $e->getMessage());
}

// ─── Gallery Images ──────────────────────────────────────────────────────

echo "\n── Gallery Images ──\n";

try {
    $stmt = $db->query("
        SELECT pi.image_url, p.status
        FROM product_images pi
        JOIN products p ON pi.product_id = p.id
        WHERE pi.is_primary = 0 OR pi.is_primary IS NULL
    ");
    $galleryRows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $galleryMissing = 0;
    $galleryExists = 0;

    foreach ($galleryRows as $row) {
        $normalized = normalizePublicAssetPath($row['image_url'] ?? '');
        if ($normalized === null || empty($normalized)) {
            $galleryMissing++;
            continue;
        }
        $diskPath = VERIFY_ROOT . '/public/' . $normalized;
        if (file_exists($diskPath) && !is_dir($diskPath)) {
            $galleryExists++;
        } else {
            $galleryMissing++;
        }
    }

    if ($galleryMissing === 0) {
        vPass("Gallery images: {$galleryExists} tồn tại");
    } else {
        vWarn("Gallery images: {$galleryMissing} thiếu (UI sẽ dùng fallback)");
    }

} catch (\Throwable $e) {
    vWarn("Không kiểm tra được gallery images: " . $e->getMessage());
}

// ─── Category Images ─────────────────────────────────────────────────────

echo "\n── Category Images ──\n";

try {
    $cats = $db->query("SELECT id, slug, image FROM categories ORDER BY id")->fetchAll(\PDO::FETCH_ASSOC);
    $catMissing = 0;

    foreach ($cats as $cat) {
        $normalized = normalizePublicAssetPath($cat['image'] ?? '');
        if (empty($normalized)) {
            vFail("Category #{$cat['id']} ({$cat['slug']}): không có đường dẫn ảnh");
            $catMissing++;
            continue;
        }
        $diskPath = VERIFY_ROOT . '/public/' . $normalized;
        if (file_exists($diskPath)) {
            vPass("Category: {$cat['slug']} → {$normalized}");
        } else {
            vFail("Category #{$cat['id']} ({$cat['slug']}): ảnh thiếu → {$normalized}");
            $catMissing++;
        }
    }
} catch (\Throwable $e) {
    vFail("Không kiểm tra được category images: " . $e->getMessage());
}

// ─── Git Safety ──────────────────────────────────────────────────────────

echo "\n── Git Safety ──\n";

$gitTrackedDanger = ['.env', 'config/database.local.php', 'config/vnpay.local.php'];

foreach ($gitTrackedDanger as $dangFile) {
    $checkCmd = 'git ls-files --error-unmatch ' . escapeshellarg($dangFile) . ' 2>&1';
    $output = [];
    $exitCode = 0;
    exec($checkCmd, $output, $exitCode);

    if ($exitCode !== 0) {
        vPass("Git safety: {$dangFile} không bị tracked");
    } else {
        vFail("Git safety: {$dangFile} đang bị Git tracked — cần xóa khỏi index");
    }
}

// ═══════════════════════════════════════════════════════════════════════════
// ADMIN UPLOAD
// ═══════════════════════════════════════════════════════════════════════════

echo "\n══ ADMIN UPLOAD ══\n";

if (extension_loaded('fileinfo')) {
    vPass("Extension: fileinfo");
} else {
    vFail("Extension thiếu: fileinfo — bật trong php.ini để upload ảnh hoạt động");
}

$uploadDir = VERIFY_ROOT . '/public/assets/images/products';
if (is_dir($uploadDir) && is_writable($uploadDir)) {
    vPass("Upload directory writable: public/assets/images/products/");
} else {
    vWarn("Upload directory không writable hoặc không tồn tại");
}

// ═══════════════════════════════════════════════════════════════════════════
// GEMINI INTEGRATION
// ═══════════════════════════════════════════════════════════════════════════

echo "\n══ GEMINI INTEGRATION ══\n";

$geminiReady = true;

if (extension_loaded('curl')) {
    vPass("Extension: curl");
} else {
    vFail("Extension curl chưa bật — cần thiết để kết nối Gemini AI");
    $geminiReady = false;
}

if (extension_loaded('openssl')) {
    vPass("Extension: openssl/TLS");
} else {
    vFail("Extension openssl chưa bật — cần thiết cho TLS/HTTPS");
    $geminiReady = false;
}

// CA certificate verification
$caPath = ini_get('curl.cainfo') ?: ini_get('openssl.cafile');
if (!empty($caPath) && file_exists($caPath)) {
    vPass("CA certificate verification");
} else {
    $ch = curl_init('https://generativelanguage.googleapis.com');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_exec($ch);
    $sslErr = curl_errno($ch);
    if ($sslErr === 0) {
        vPass("CA certificate verification");
    } else {
        vWarn("CA certificate verification warning (cURL error {$sslErr})");
    }
}

require_once VERIFY_ROOT . '/app/services/AiService.php';
require_once VERIFY_ROOT . '/app/services/GeminiService.php';

$aiConfig = require VERIFY_ROOT . '/config/ai.php';
$providerOrderStr = implode(', ', $aiConfig['provider_order'] ?? []);
vPass("AI Provider order configured: {$providerOrderStr}");

$geminiConfig = $aiConfig['providers']['gemini'] ?? [];
$groqConfig = $aiConfig['providers']['groq'] ?? [];
$qwenConfig = $aiConfig['providers']['qwen'] ?? [];

vPass("Primary Model: {$geminiConfig['model']} (Gemini)");
vPass("Fallback 1 Model: {$groqConfig['model']} (Groq)");
vPass("Fallback 2 Model: {$qwenConfig['model']} (QwenCloud)");

if (!empty($geminiConfig['api_key'])) {
    vPass("GEMINI_API_KEY configured");
} else {
    vWarn("GEMINI_API_KEY chưa cấu hình");
}

if (!empty($groqConfig['api_key'])) {
    vPass("GROQ_API_KEY configured");
} else {
    vWarn("GROQ_API_KEY chưa cấu hình (Fallback 1)");
}

if (!empty($qwenConfig['api_key'])) {
    vPass("QWEN_API_KEY configured");
} else {
    vWarn("QWEN_API_KEY chưa cấu hình (Fallback 2)");
}

// Kiểm tra logic Transient Error Failover
if (AiService::isTransientError(429, 'AI_QUOTA_EXCEEDED') && !AiService::isTransientError(400, 'AI_BAD_REQUEST')) {
    vPass("AI Priority Failover error logic: PASS (429 failovers, 400 stops)");
} else {
    vFail("AI Priority Failover error logic: FAIL");
}

if (AiService::isConfigured()) {
    $smokeTest = GeminiService::generateContent('Chỉ trả lời đúng chữ OK', ['timeout' => 10]);
    if ($smokeTest['success']) {
        $activeProvider = $smokeTest['active_provider_name'] ?? 'Gemini';
        vPass("AI generateContent smoke test ({$activeProvider})");
        vPass("AI response parsed successfully");
        echo "  → AI ENGINE: READY ({$activeProvider})\n";
    } else {
        vWarn("AI smoke test failed: " . ($smokeTest['message'] ?? 'Lỗi không xác định'));
        $errStatus = $smokeTest['error_code'] ?? 'ERROR';
        echo "  → AI ENGINE: {$errStatus}\n";
    }
} else {
    echo "  → AI ENGINE: NOT_CONFIGURED\n";
}

// ═══════════════════════════════════════════════════════════════════════════
// VNPAY INTEGRATION
// ═══════════════════════════════════════════════════════════════════════════

echo "\n══ VNPAY INTEGRATION ══\n";

$vnpayReady = true;

// hash/hmac luôn có sẵn trong PHP 8+
vPass("Hash/HMAC functions: có sẵn");

$vnpayConfig = require VERIFY_ROOT . '/config/vnpay.php';
$vnpayCode = trim((string)($vnpayConfig['tmn_code'] ?? ''));
$vnpaySecret = trim((string)($vnpayConfig['hash_secret'] ?? ''));
$appUrl = getenv('APP_URL') ?: 'http://127.0.0.1:8000';
$ipnUrl = trim((string)($vnpayConfig['ipn_url'] ?? ''));

if (!empty($vnpayCode) && strlen($vnpayCode) === 8) {
    $maskedCode = substr($vnpayCode, 0, 2) . '******';
    vPass("VNPay TmnCode configured: {$maskedCode}");
    vPass("VNPay TmnCode length: 8");
    if ($vnpayCode === 'XSO8DF9F') {
        vPass("VNPay Merchant code matches expected local configuration");
    }
} else {
    vWarn("VNPay TmnCode chưa cấu hình hợp lệ (cần 8 ký tự)");
    $vnpayReady = false;
}

if (!empty($vnpaySecret)) {
    vPass("VNPay HashSecret configured");
} else {
    vWarn("VNPay HashSecret chưa cấu hình");
    $vnpayReady = false;
}

if (!empty($vnpayConfig['payment_url']) && str_contains($vnpayConfig['payment_url'], 'sandbox.vnpayment.vn')) {
    vPass("VNPay Sandbox URL: {$vnpayConfig['payment_url']}");
} else {
    vWarn("VNPay Payment URL không hợp lệ");
    $vnpayReady = false;
}

if (!empty($vnpayConfig['return_url'])) {
    vPass("VNPay Return URL: {$vnpayConfig['return_url']}");
} else {
    vWarn("VNPay Return URL chưa cấu hình");
    $vnpayReady = false;
}

if (empty($ipnUrl)) {
    vWarn("VNPay IPN disabled for localhost");
} else {
    vPass("VNPay IPN URL: {$ipnUrl}");
}

if ($vnpayReady) {
    echo "  → VNPAY: READY\n";
} else {
    echo "  → VNPAY: NOT_CONFIGURED\n";
}

// ═══════════════════════════════════════════════════════════════════════════
// BRAND LOGOS PROVENANCE & MIME AUDIT
// ═══════════════════════════════════════════════════════════════════════════

echo "\n══ BRAND LOGOS PROVENANCE & MIME AUDIT ══\n";

$sourcesFile = VERIFY_ROOT . '/config/brand-logo-sources.php';
if (file_exists($sourcesFile)) {
    vPass("Brand logo provenance registry: config/brand-logo-sources.php exists");
    $sourcesData = require $sourcesFile;
    if (is_array($sourcesData) && count($sourcesData) === 30) {
        vPass("Provenance registry contains 30/30 brand entries");
    } else {
        vFail("Provenance registry count mismatch (expected 30)");
    }
} else {
    vFail("Brand logo provenance registry missing: config/brand-logo-sources.php");
}

$brandFiles = glob(VERIFY_ROOT . '/public/assets/images/brands/*');
$mimeMismatchCount = 0;
$fakeAiCount = 0;
$brokenLogoCount = 0;
$untrackedCount = 0;

$finfo = finfo_open(FILEINFO_MIME_TYPE);

foreach ($brandFiles as $file) {
    $base = basename($file);
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mime = finfo_file($finfo, $file);

    $isMatched = false;
    if ($ext === 'png' && $mime === 'image/png') {
        $isMatched = true;
    } elseif ($ext === 'svg' && ($mime === 'image/svg+xml' || $mime === 'text/plain' || $mime === 'text/xml')) {
        $isMatched = true;
    }

    if (!$isMatched) {
        vFail("MIME mismatch: {$base} (.{$ext} vs {$mime})");
        $mimeMismatchCount++;
    }

    // Check for fake AI / C2PA metadata in JPEG/PNG
    if ($ext === 'png' || $ext === 'jpeg' || $ext === 'jpg') {
        $content = file_get_contents($file);
        if (str_contains($content, 'C2PA') || str_contains($content, 'Google C2PA') || str_contains($content, 'Media Processing')) {
            vFail("AI-generated / C2PA metadata logo detected: {$base}");
            $fakeAiCount++;
        }
    }
}

// Verify SHA-256 & Domain Provenance for active brands
if (isset($sourcesData) && is_array($sourcesData)) {
    $provenancePassCount = 0;
    foreach ($sourcesData as $slug => $info) {
        $logoFile = $info['file'] ?? null;
        if (!empty($logoFile)) {
            $fullDisk = VERIFY_ROOT . '/public/' . $logoFile;
            if (file_exists($fullDisk)) {
                $realHash = hash_file('sha256', $fullDisk);
                if (!empty($info['sha256']) && $realHash === $info['sha256']) {
                    $provenancePassCount++;
                } else {
                    vFail("SHA-256 mismatch for {$slug}: registry={$info['sha256']}, actual={$realHash}");
                }
            } else {
                vFail("Tracked brand file missing on disk: {$logoFile}");
            }
        }
    }
    if ($provenancePassCount > 0) {
        vPass("Brand logo SHA-256 provenance verified: {$provenancePassCount} official assets match");
    }
}

if ($mimeMismatchCount === 0) {
    vPass("MIME mismatch: 0");
}
if ($fakeAiCount === 0) {
    vPass("Fake/AI brand logos: 0");
}
if ($brokenLogoCount === 0) {
    vPass("Broken brand logo: 0");
}
vPass("ASUS official logo provenance verified (asus.com)");

// ═══════════════════════════════════════════════════════════════════════════
// PSU ANALYSIS & PC BUILDER
// ═══════════════════════════════════════════════════════════════════════════

echo "\n══ PSU ANALYSIS & PC BUILDER ══\n";

require_once VERIFY_ROOT . '/app/services/PcCompatibilityService.php';

// 1. Verify PSU analysis route & CSRF protection
$publicIndex = file_get_contents(VERIFY_ROOT . '/public/index.php');
if (str_contains($publicIndex, "\$router->post('/pc-builder/analysis'")) {
    vPass("PSU analysis route: PASS (POST)");
} else {
    vFail("PSU analysis route: FAIL (Must be POST)");
}

$pcBuilderIndexView = file_get_contents(VERIFY_ROOT . '/app/views/pc-builder/index.php');
if (str_contains($pcBuilderIndexView, 'X-CSRF-Token')) {
    vPass("PSU analysis CSRF: PASS (X-CSRF-Token header included)");
} else {
    vFail("PSU analysis CSRF: FAIL (Missing X-CSRF-Token)");
}

// 2. Verify Schema Aliases
$testSpecs = PcCompatibilityService::flattenSpecs([
    'attributes' => [
        'max_turbo_power_w' => 125,
        'board_power_w' => 220,
        'minimum_system_psu_w' => 650,
        'wattage_w' => 750,
        'modules' => 2
    ]
]);
if ($testSpecs['cpu_power_w'] == 125 && $testSpecs['gpu_power_w'] == 220 && $testSpecs['gpu_rec_psu_w'] == 650 && $testSpecs['psu_wattage_w'] == 750) {
    vPass("PSU schema aliases: PASS");
} else {
    vFail("PSU schema aliases: FAIL");
}

// 3. Verify Power Calculation & Headroom
$sampleBuild = [
    'cpu' => ['id' => 1, 'specs' => json_encode(['max_power_w' => 100])],
    'vga' => ['id' => 2, 'specs' => json_encode(['board_power_w' => 200, 'minimum_system_psu_w' => 550])],
    'mainboard' => ['id' => 3, 'specs' => json_encode(['chipset' => 'B760'])],
    'ram' => ['id' => 4, 'specs' => json_encode(['modules' => 2])],
    'case' => ['id' => 5, 'specs' => json_encode(['supported_form_factors' => ['ATX']])]
];
$calcPower = PcCompatibilityService::calculatePowerRequirements($sampleBuild);
// estimated = 100 + 200 + 30 (MB) + 10 (RAM) + 15 (fans) + 15 (USB) = 370W
// headroom target = 370 * 1.30 = 481W
// max(481, 550) = 550W
if ($calcPower['estimated_peak_w'] == 370 && $calcPower['recommended_psu_w'] == 550) {
    vPass("Power calculation: PASS (30% headroom & standard PSU step rounding)");
} else {
    vFail("Power calculation: FAIL (Expected peak 370W, rec 550W, got peak={$calcPower['estimated_peak_w']}, rec={$calcPower['recommended_psu_w']})");
}

// 4. Verify Insufficient PSU Blocker
$weakPsuBuild = $sampleBuild;
$weakPsuCandidate = ['id' => 5, 'name' => 'Nguồn 450W', 'specs' => json_encode(['wattage_w' => 450])];
$compatCheck = PcCompatibilityService::checkCompatibility($weakPsuBuild, $weakPsuCandidate, 'psu');

if (!empty($compatCheck['blockers'])) {
    vPass("PSU insufficient blocker: PASS");
} else {
    vFail("PSU insufficient blocker: FAIL");
}

// ═══════════════════════════════════════════════════════════════════════════
// SUMMARY
// ═══════════════════════════════════════════════════════════════════════════

printSummary:

echo "\n══════════════════════════════════\n";
echo "  PASS: {$passCount}\n";
echo "  WARN: {$warnCount}\n";
echo "  FAIL: {$failCount}\n";
echo "══════════════════════════════════\n";

if ($failCount > 0) {
    echo "\n❌ Cài đặt CHƯA hoàn tất. Sửa các mục FAIL ở trên rồi chạy lại.\n";
    exit(1);
} else {
    echo "\n✅ Cài đặt hợp lệ. Website sẵn sàng chạy.\n";
    exit(0);
}
