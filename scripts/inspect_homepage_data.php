<?php
define('ROOT_PATH', dirname(__DIR__));
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Product.php';

$db = Database::getConnection();
echo "=== FLASH SALES TABLE ===\n";
$fs = $db->query("SELECT * FROM flash_sales")->fetchAll(PDO::FETCH_ASSOC);
print_r($fs);

echo "\n=== FLASH SALE ITEMS TABLE ===\n";
$fsi = $db->query("SELECT fsi.*, p.name, p.status, p.verification_status FROM flash_sale_items fsi JOIN products p ON p.id = fsi.product_id")->fetchAll(PDO::FETCH_ASSOC);
print_r($fsi);

$productModel = new Product();
$activeFs = $productModel->getFlashSale();
echo "\n=== Product::getFlashSale() RETURN ===\n";
print_r($activeFs);
