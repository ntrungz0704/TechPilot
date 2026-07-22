<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "=== TABLES HIEN CO ===" . PHP_EOL;
foreach ($tables as $t) {
    echo " - " . $t . PHP_EOL;
}

// Kiem tra cac bang can thiet
$needed = ['flash_sales', 'flash_sale_items', 'categories', 'products', 'brands', 'banners', 'posts', 'reviews'];
echo PHP_EOL . "=== KIEM TRA BANG CAN THIET ===" . PHP_EOL;
foreach ($needed as $n) {
    $exists = in_array($n, $tables);
    echo ($exists ? "OK  " : "MISS") . " => " . $n . PHP_EOL;
}
