<?php
/**
 * FRONT CONTROLLER
 * Toàn bộ request đều đi qua file này (nhờ .htaccess rewrite)
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/app/core/helpers.php';
require_once dirname(__DIR__) . '/app/core/Controller.php';
require_once dirname(__DIR__) . '/app/core/Router.php';

// Lấy phần URL sau index.php, ví dụ: product/detail/asus-rog-zephyrus-g16
$url = $_GET['url'] ?? '';

// Kiểm tra bảo mật CSRF cho toàn bộ các POST request (chống giả mạo yêu cầu)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Chấp nhận cả 2 tên field: csrf_token (mặc định) và _csrf (legacy)
    $token = $_POST['csrf_token'] ?? $_POST['_csrf'] ?? '';
    $savedToken = $_SESSION['csrf_token'] ?? '';
    if ($token === '' || !hash_equals($savedToken, $token)) {
        http_response_code(403);
        die('Yêu cầu không hợp lệ (CSRF Token mismatch). Vui lòng tải lại trang.');
    }
}

$router = new Router();
$router->post('/checkout/apply_coupon', 'CheckoutController@apply_coupon');
$router->post('/product/review', 'ProductController@review');
$router->post('/profile/cancel_order', 'ProfileController@cancel_order');

// Admin Category Routes
$router->get('/admin/categories', 'AdminCategoryController@index');
$router->get('/admin/categories/create', 'AdminCategoryController@create');
$router->post('/admin/categories/store', 'AdminCategoryController@store');
$router->get('/admin/categories/edit/{id}', 'AdminCategoryController@edit');
$router->post('/admin/categories/update/{id}', 'AdminCategoryController@update');
$router->post('/admin/categories/delete/{id}', 'AdminCategoryController@delete');

// Admin Brand Routes
$router->get('/admin/brands', 'AdminBrandController@index');
$router->get('/admin/brands/create', 'AdminBrandController@create');
$router->post('/admin/brands/store', 'AdminBrandController@store');
$router->get('/admin/brands/edit/{id}', 'AdminBrandController@edit');
$router->post('/admin/brands/update/{id}', 'AdminBrandController@update');
$router->post('/admin/brands/delete/{id}', 'AdminBrandController@delete');

// Admin Product Routes
$router->get('/admin/products', 'AdminProductController@index');
$router->get('/admin/products/create', 'AdminProductController@create');
$router->post('/admin/products/store', 'AdminProductController@store');
$router->get('/admin/products/edit/{id}', 'AdminProductController@edit');
$router->post('/admin/products/update/{id}', 'AdminProductController@update');
$router->post('/admin/products/delete/{id}', 'AdminProductController@delete');

// Admin Order Routes
$router->get('/admin/orders', 'AdminOrderController@index');
$router->get('/admin/orders/detail/{id}', 'AdminOrderController@detail');
$router->post('/admin/orders/update_status/{id}', 'AdminOrderController@updateStatus');

// Admin User Routes
$router->get('/admin/users', 'AdminUserController@index');
$router->post('/admin/users/toggle_status/{id}', 'AdminUserController@toggleStatus');
$router->post('/admin/users/change_role/{id}', 'AdminUserController@changeRole');

// Admin Review Routes
$router->get('/admin/reviews', 'AdminReviewController@index');
$router->post('/admin/reviews/approve/{id}', 'AdminReviewController@approve');
$router->post('/admin/reviews/hide/{id}', 'AdminReviewController@hide');

// Admin Flash Sale Routes
$router->get('/admin/flash-sales', 'AdminFlashSaleController@index');
$router->get('/admin/flash-sales/create', 'AdminFlashSaleController@create');
$router->post('/admin/flash-sales/store', 'AdminFlashSaleController@store');
$router->get('/admin/flash-sales/edit/{id}', 'AdminFlashSaleController@edit');
$router->post('/admin/flash-sales/update/{id}', 'AdminFlashSaleController@update');
$router->post('/admin/flash-sales/delete/{id}', 'AdminFlashSaleController@delete');

// Admin Coupon Routes
$router->get('/admin/coupons', 'AdminCouponController@index');
$router->get('/admin/coupons/create', 'AdminCouponController@create');
$router->post('/admin/coupons/store', 'AdminCouponController@store');
$router->get('/admin/coupons/edit/{id}', 'AdminCouponController@edit');
$router->post('/admin/coupons/update/{id}', 'AdminCouponController@update');
$router->post('/admin/coupons/delete/{id}', 'AdminCouponController@delete');

// Admin Banner Routes
$router->get('/admin/banners', 'AdminBannerController@index');
$router->get('/admin/banners/create', 'AdminBannerController@create');
$router->post('/admin/banners/store', 'AdminBannerController@store');
$router->get('/admin/banners/edit/{id}', 'AdminBannerController@edit');
$router->post('/admin/banners/update/{id}', 'AdminBannerController@update');
$router->post('/admin/banners/delete/{id}', 'AdminBannerController@delete');

// Admin Post Routes
$router->get('/admin/posts', 'AdminPostController@index');
$router->get('/admin/posts/create', 'AdminPostController@create');
$router->post('/admin/posts/store', 'AdminPostController@store');
$router->get('/admin/posts/edit/{id}', 'AdminPostController@edit');
$router->post('/admin/posts/update/{id}', 'AdminPostController@update');
$router->post('/admin/posts/delete/{id}', 'AdminPostController@delete');

// PC Builder Routes
$router->get('/build-pc', 'PcBuilderController@index');
$router->get('/pc-builder/products', 'PcBuilderController@getProducts');
$router->get('/pc-builder/analysis', 'PcBuilderController@getAnalysis');
$router->post('/pc-builder/add-to-cart', 'PcBuilderController@addToCart');

// News Routes
$router->get('/tin-tuc', 'NewsController@index');
$router->get('/tin-tuc/{slug}', 'NewsController@show');

$router->dispatch($url);
