<?php
define('ROOT_PATH', __DIR__);
require_once 'config/database.php';
$db = Database::getConnection();
if ($db) {
    echo "DB is connected\n";
    $stmt = $db->query('SHOW COLUMNS FROM products');
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $col) echo $col['Field'] . "\n";
} else {
    echo "DB is NOT connected\n";
}
