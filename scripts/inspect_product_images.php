<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();
echo "=== DESCRIBE product_images ===\n";
print_r($db->query("DESCRIBE product_images")->fetchAll(PDO::FETCH_ASSOC));

echo "\n=== PRODUCT #636 (MSI Katana 15) ===\n";
$prod = $db->query("SELECT p.id, p.name, p.image, c.slug as category_slug FROM products p JOIN categories c ON c.id = p.category_id WHERE p.slug LIKE '%katana%'")->fetch(PDO::FETCH_ASSOC);
print_r($prod);

echo "\n=== PRODUCT IMAGES FOR PRODUCT #{$prod['id']} ===\n";
$imgs = $db->query("SELECT * FROM product_images WHERE product_id = {$prod['id']}")->fetchAll(PDO::FETCH_ASSOC);
print_r($imgs);
