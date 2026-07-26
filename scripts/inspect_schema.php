<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();
if (!$db) {
    echo "Cannot connect to database\n";
    exit(1);
}

echo "=== Products Table Columns ===\n";
$stmt = $db->query("DESCRIBE products");
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo "- {$col['Field']} ({$col['Type']}) " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
}
