<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/app/models/Product.php';

$p = new Product();
echo "Total search count without filters: " . $p->countSearch() . "\n";
echo "Search 'laptop' count: " . $p->countSearch('laptop') . "\n";
echo "Search 'vga' count: " . $p->countSearch('vga') . "\n";
