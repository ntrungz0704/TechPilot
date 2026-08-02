<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
try {
    $db = Database::getConnection();
    if (!$db) {
        echo "DB connection is null\n";
    } else {
        $stmt = $db->query("SELECT count(*) FROM products WHERE status = 'active' AND verification_status = 'verified'");
        echo "Active and verified products: " . $stmt->fetchColumn() . "\n";
        
        $stmt2 = $db->query("SELECT * FROM flash_sales ORDER BY id DESC LIMIT 1");
        print_r($stmt2->fetch(PDO::FETCH_ASSOC));
    }
} catch (Exception $e) {
    echo "DB error: " . $e->getMessage() . "\n";
}
