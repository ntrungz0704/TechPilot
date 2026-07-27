<?php
define('ROOT_PATH', __DIR__);

// Load config
require_once 'config/database.php';

// Load Core Classes
require_once 'app/core/helpers.php'; // Required for safe_strlen, url, etc.

// Load Models
require_once 'app/models/Product.php';
require_once 'app/models/Brand.php';
require_once 'app/models/Banner.php';
require_once 'app/models/Post.php';
require_once 'app/models/Review.php';

$start = microtime(true);
$db = Database::getConnection();
echo "DB Connection: " . (microtime(true) - $start) . "s\n";

$productModel = new Product();

$start = microtime(true);
$categories = $productModel->getCategories();
echo "getCategories: " . (microtime(true) - $start) . "s\n";

$start = microtime(true);
$flashSale = $productModel->getFlashSale(6);
echo "getFlashSale: " . (microtime(true) - $start) . "s\n";

$start = microtime(true);
$productModel->getByCategorySlug('laptop-gaming', 6);
echo "getByCategorySlug: " . (microtime(true) - $start) . "s\n";

$start = microtime(true);
$productModel->getBestSellersByTab('laptop', 6);
echo "getBestSellersByTab: " . (microtime(true) - $start) . "s\n";

require_once 'app/services/CatalogGroupService.php';
require_once 'app/services/CategoryMenuService.php';

$start = microtime(true);
$globalCategoryMenu = CategoryMenuService::getActiveMenuTree();
echo "getActiveMenuTree: " . (microtime(true) - $start) . "s\n";
