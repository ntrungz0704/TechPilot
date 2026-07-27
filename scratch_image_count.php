<?php
define('ROOT_PATH', __DIR__);
require_once 'config/database.php';
$db = Database::getConnection();
if ($db) {
    $stmt = $db->query('SELECT product_id, COUNT(*) as c FROM product_images GROUP BY product_id HAVING c != 4');
    $res = $stmt->fetchAll();
    if (empty($res)) {
        echo "All products have exactly 4 images.\n";
    } else {
        print_r($res);
    }
} else {
    echo "DB is offline.\n";
}
