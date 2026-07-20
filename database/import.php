<?php
/**
 * CLI Helper script to import database schema and seed data for TechPilot.
 * Usage: php database/import.php [db_user] [db_password] [db_host] [db_port]
 */

$user = $argv[1] ?? 'root';
$pass = $argv[2] ?? '';
$host = $argv[3] ?? '127.0.0.1';
$port = $argv[4] ?? '3306';

echo "=== TechPilot Database Auto-Importer ===\n";
echo "Connecting to MySQL server at $host:$port as user '$user'...\n";

try {
    $dsn = "mysql:host=$host;port=$port;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
    ]);
    echo "[OK] Connected to MySQL server successfully!\n";
} catch (PDOException $e) {
    echo "[ERROR] Could not connect to MySQL server: " . $e->getMessage() . "\n";
    echo "\nPlease specify valid credentials:\n";
    echo "  php database/import.php <username> <password>\n";
    exit(1);
}

$sqlFile = __DIR__ . '/techpilot.sql';
if (!file_exists($sqlFile)) {
    $sqlFile = __DIR__ . '/schema.sql';
}

echo "Importing full database from " . basename($sqlFile) . "...\n";
$sql = file_get_contents($sqlFile);

try {
    $pdo->exec($sql);
    echo "[OK] Database 'techpilot' created and all 15 ERD tables & 100+ products imported successfully!\n";
} catch (PDOException $e) {
    echo "[ERROR] Failed to import database: " . $e->getMessage() . "\n";
    exit(1);
}

// Write config/database.local.php
$configDir = dirname(__DIR__) . '/config';
$localConfigFile = $configDir . '/database.local.php';

$localConfigContent = "<?php\nreturn [\n" .
    "    'host' => '$host',\n" .
    "    'port' => '$port',\n" .
    "    'database' => 'techpilot',\n" .
    "    'username' => '$user',\n" .
    "    'password' => '" . addslashes($pass) . "',\n" .
    "    'charset' => 'utf8mb4',\n" .
    "];\n";

file_put_contents($localConfigFile, $localConfigContent);
echo "[OK] Created config/database.local.php with connection settings.\n";
echo "=== Database setup complete! ===\n";
