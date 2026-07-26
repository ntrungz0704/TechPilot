<?php
define('ROOT_PATH', __DIR__ . '/..');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Product.php';

$pModel = new Product();
$slugs = ['laptop-gaming', 'laptop-van-phong', 'pc-build-san', 'pc-linh-kien', 'gaming-gear', 'man-hinh'];

foreach ($slugs as $slug) {
    $res = $pModel->getByCategorySlug($slug, 6);
    echo sprintf("%-20s Count: %d\n", $slug, count($res));
}
