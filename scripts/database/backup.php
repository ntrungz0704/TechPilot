<?php
/**
 * TechPilot — Database Backup Script
 * 
 * Tạo bản sao lưu đầy đủ cơ sở dữ liệu MySQL vào thư mục storage/backups/
 * 
 * Sử dụng:
 *   php scripts/database/backup.php              # Backup bình thường
 *   php scripts/database/backup.php --keep=7     # Giữ 7 bản gần nhất (mặc định)
 *   php scripts/database/backup.php --keep=14    # Giữ 14 bản gần nhất
 * 
 * Yêu cầu: PHP 8.0+, PDO MySQL, quyền đọc toàn bộ bảng
 */

// ── Bootstrap ────────────────────────────────────────────────────────────────
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    echo 'Script chỉ chạy qua CLI.';
    exit(1);
}

define('ROOT_PATH', dirname(__DIR__, 2));

// Load .env nếu có (lấy DB config)
$envPath = ROOT_PATH . '/.env';
if (file_exists($envPath)) {
    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$name, $value] = explode('=', $line, 2);
            putenv(trim($name) . '=' . trim($value));
        }
    }
}

// Load database.local.php nếu có
$localConfigFile = ROOT_PATH . '/config/database.local.php';
$host   = getenv('DB_HOST')   ?: '127.0.0.1';
$port   = getenv('DB_PORT')   ?: '3306';
$dbname = getenv('DB_NAME')   ?: 'techpilot';
$user   = getenv('DB_USER') !== false ? getenv('DB_USER') : 'root';
$pass   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

if (file_exists($localConfigFile)) {
    $lc = require $localConfigFile;
    if (is_array($lc)) {
        $host   = $lc['host']     ?? $host;
        $port   = $lc['port']     ?? $port;
        $dbname = $lc['database'] ?? $lc['dbname'] ?? $dbname;
        $user   = $lc['username'] ?? $lc['user']   ?? $user;
        $pass   = $lc['password'] ?? $lc['pass']   ?? $pass;
    }
}

// ── Parse CLI args ───────────────────────────────────────────────────────────
$keepCount = 7; // Mặc định giữ 7 bản backup gần nhất
foreach ($argv as $arg) {
    if (preg_match('/^--keep=(\d+)$/', $arg, $m)) {
        $keepCount = max(1, (int)$m[1]);
    }
}

// ── Backup directory ─────────────────────────────────────────────────────────
$backupDir = ROOT_PATH . '/storage/backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "📁 Tạo thư mục backup: {$backupDir}\n";
}

// ── Kết nối DB ───────────────────────────────────────────────────────────────
$timestamp = date('Y-m-d_His');
$backupFile = "{$backupDir}/techpilot_{$timestamp}.sql";

echo "╔══════════════════════════════════════════╗\n";
echo "║  TechPilot Database Backup               ║\n";
echo "╚══════════════════════════════════════════╝\n";
echo "⏰ Thời gian: " . date('Y-m-d H:i:s') . "\n";
echo "🗄️  Database: {$dbname}@{$host}:{$port}\n";
echo "📦 Output: {$backupFile}\n\n";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "✅ Kết nối database thành công\n";
} catch (PDOException $e) {
    echo "❌ Lỗi kết nối database: " . $e->getMessage() . "\n";
    exit(1);
}

// ── Dump schema + data bằng PDO thuần (không cần mysqldump binary) ───────────
$output = "";

// Header
$output .= "-- ============================================================================\n";
$output .= "-- TechPilot Database Backup\n";
$output .= "-- Generated: {$timestamp}\n";
$output .= "-- Database: {$dbname}\n";
$output .= "-- ============================================================================\n\n";
$output .= "SET NAMES utf8mb4;\n";
$output .= "SET FOREIGN_KEY_CHECKS = 0;\n";
$output .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n";

// Get all tables
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$tableCount = count($tables);
echo "📊 Tìm thấy {$tableCount} bảng\n\n";

$totalRows = 0;
foreach ($tables as $i => $table) {
    $num = $i + 1;
    
    // CREATE TABLE
    $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
    $createSql = $createStmt['Create Table'] ?? $createStmt[1] ?? '';
    
    $output .= "-- ──────────────────────────────────────────\n";
    $output .= "-- Table: {$table}\n";
    $output .= "-- ──────────────────────────────────────────\n\n";
    $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
    $output .= $createSql . ";\n\n";
    
    // INSERT data
    $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll();
    $rowCount = count($rows);
    $totalRows += $rowCount;
    
    if ($rowCount > 0) {
        // Get column names
        $columns = array_keys($rows[0]);
        $colList = implode('`, `', $columns);
        
        // Batch INSERT (max 100 rows per statement for safety)
        $chunks = array_chunk($rows, 100);
        foreach ($chunks as $chunk) {
            $values = [];
            foreach ($chunk as $row) {
                $escaped = [];
                foreach ($row as $value) {
                    if ($value === null) {
                        $escaped[] = 'NULL';
                    } else {
                        $escaped[] = $pdo->quote($value);
                    }
                }
                $values[] = '(' . implode(',', $escaped) . ')';
            }
            $output .= "INSERT INTO `{$table}` (`{$colList}`) VALUES\n" . implode(",\n", $values) . ";\n\n";
        }
    }
    
    echo "  [{$num}/{$tableCount}] {$table}: {$rowCount} rows\n";
}

// Footer
$output .= "SET FOREIGN_KEY_CHECKS = 1;\n";
$output .= "\n-- Backup completed: " . date('Y-m-d H:i:s') . "\n";

// ── Ghi file ─────────────────────────────────────────────────────────────────
$bytesWritten = file_put_contents($backupFile, $output);

if ($bytesWritten === false) {
    echo "\n❌ Lỗi ghi file backup!\n";
    exit(1);
}

$sizeMB = round($bytesWritten / 1024 / 1024, 2);
echo "\n✅ Backup thành công!\n";
echo "   📦 File: {$backupFile}\n";
echo "   📊 Bảng: {$tableCount} | Dòng: {$totalRows} | Kích thước: {$sizeMB} MB\n";

// ── Xóa backup cũ (giữ $keepCount bản gần nhất) ────────────────────────────
$files = glob("{$backupDir}/techpilot_*.sql");
if (count($files) > $keepCount) {
    // Sort by modification time (oldest first)
    usort($files, function ($a, $b) { return filemtime($a) - filemtime($b); });
    
    $toDelete = array_slice($files, 0, count($files) - $keepCount);
    echo "\n🧹 Xóa " . count($toDelete) . " bản backup cũ (giữ {$keepCount} bản gần nhất):\n";
    foreach ($toDelete as $old) {
        unlink($old);
        echo "   🗑️  Đã xóa: " . basename($old) . "\n";
    }
}

echo "\n══════════════════════════════════════════\n";
echo "✅ Backup hoàn tất thành công!\n";
echo "══════════════════════════════════════════\n";
exit(0);
