<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Product.php';

$pModel = new Product();
$feat = $pModel->getFeaturedProducts(6);
echo "Featured Products Count: " . count($feat) . "\n";
foreach ($feat as $f) {
    echo " - [#{$f['id']}] {$f['name']}\n";
}
