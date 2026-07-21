<?php
require 'config/database.php';
$db = Database::getConnection();
$stmt = $db->query('SHOW COLUMNS FROM products');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $col) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}
