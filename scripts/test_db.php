<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();
if ($db) {
    echo 'DATABASE: OK - Ket noi thanh cong' . PHP_EOL;
    try {
        $cats = $db->query('SELECT COUNT(*) as cnt FROM categories')->fetch();
        echo 'CATEGORIES: ' . $cats['cnt'] . ' ban ghi' . PHP_EOL;
    } catch (Exception $e) {
        echo 'CATEGORIES ERROR: ' . $e->getMessage() . PHP_EOL;
    }
    try {
        $prods = $db->query('SELECT COUNT(*) as cnt FROM products')->fetch();
        echo 'PRODUCTS: ' . $prods['cnt'] . ' ban ghi' . PHP_EOL;
    } catch (Exception $e) {
        echo 'PRODUCTS ERROR: ' . $e->getMessage() . PHP_EOL;
    }
    try {
        $banners = $db->query('SELECT COUNT(*) as cnt FROM banners')->fetch();
        echo 'BANNERS: ' . $banners['cnt'] . ' ban ghi' . PHP_EOL;
    } catch (Exception $e) {
        echo 'BANNERS ERROR: ' . $e->getMessage() . PHP_EOL;
    }
} else {
    echo 'DATABASE: FAILED - Khong ket noi duoc' . PHP_EOL;
}
