<?php
define('ROOT_PATH', __DIR__);
require 'config/database.php';
$db = Database::getConnection();
if ($db === null) { echo "DB connection failed\n"; exit(1); }
echo 'Products: ' . $db->query('SELECT COUNT(*) FROM products')->fetchColumn() . "\n";
echo 'Images: ' . $db->query('SELECT COUNT(*) FROM product_images')->fetchColumn() . "\n";
echo 'Categories: ' . $db->query('SELECT COUNT(*) FROM categories')->fetchColumn() . "\n";
