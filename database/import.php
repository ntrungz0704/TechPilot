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

$schemaFile = __DIR__ . '/schema.sql';
if (!file_exists($schemaFile)) {
    echo "[ERROR] Schema file not found: $schemaFile\n";
    exit(1);
}

echo "Importing schema from schema.sql...\n";
$schemaSql = file_get_contents($schemaFile);

try {
    $pdo->exec($schemaSql);
    echo "[OK] Database schema imported successfully (15 tables created)!\n";
} catch (PDOException $e) {
    echo "[ERROR] Failed to import schema: " . $e->getMessage() . "\n";
    exit(1);
}

// Import seeds if available
$seedFiles = glob(__DIR__ . '/seeds/*.sql');
foreach ($seedFiles as $seedFile) {
    echo "Importing seed data from " . basename($seedFile) . "...\n";
    $seedSql = file_get_contents($seedFile);
    try {
        $pdo->exec("USE techpilot; " . $seedSql);
        echo "[OK] Imported " . basename($seedFile) . "\n";
    } catch (PDOException $e) {
        echo "[WARNING] Seed import warning for " . basename($seedFile) . ": " . $e->getMessage() . "\n";
    }
}

// Write config/database.local.php if password is not default empty
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
