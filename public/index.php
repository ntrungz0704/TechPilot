<?php
/**
 * FRONT CONTROLLER
 * Toàn bộ request đều đi qua file này (nhờ .htaccess rewrite)
 */

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/app/core/helpers.php';
require_once dirname(__DIR__) . '/app/core/ErrorHandler.php';
require_once dirname(__DIR__) . '/app/core/Controller.php';
require_once dirname(__DIR__) . '/app/core/Router.php';

ErrorHandler::register();

// Tự động kiểm tra trạng thái khóa tài khoản: nếu bị Admin khóa, đăng xuất và hủy session lập tức khi thực hiện bất kỳ thao tác nào
assertActiveUserSession();

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Strict-Transport-Security (Chỉ áp dụng khi truy cập qua HTTPS thực tế)
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

if ($isSecure) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

// Content-Security-Policy (Tối ưu: Bỏ 'unsafe-eval' & bỏ wildcard 'https:' ở img-src)
$cspDirectives = [
    "default-src 'self'",
    "script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com",
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
    "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com data:",
    "img-src 'self' data: blob: https://placehold.co https://ui-avatars.com https://cdnjs.cloudflare.com",
    "connect-src 'self' https://sandbox.vnpayment.vn https://vnpayment.vn",
    "frame-src 'self' https://www.youtube.com https://www.facebook.com",
    "object-src 'none'",
    "base-uri 'self'",
    "form-action 'self' https://sandbox.vnpayment.vn https://vnpayment.vn"
];
header('Content-Security-Policy: ' . implode('; ', $cspDirectives));

// Lấy phần URL sau index.php, ví dụ: product/detail/asus-rog-zephyrus-g16
$url = $_GET['url'] ?? '';
$path = '/' . trim(parse_url($url, PHP_URL_PATH), '/');

// Kiểm tra bảo mật CSRF cho toàn bộ các POST request (chống giả mạo yêu cầu)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Exemptions cho external API hoặc webhook (exact path match) - trống vì 100% POST endpoints yêu cầu CSRF
    $exactExemptions = [];

    if (!in_array($path, $exactExemptions, true)) {
        $token = $_POST['csrf_token'] ?? $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $savedToken = $_SESSION['csrf_token'] ?? '';

        $contentType   = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? ''));
        $accept        = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
        $requestedWith = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

        $isJsonRequest = str_contains($contentType, 'application/json')
            || str_contains($accept, 'application/json')
            || $requestedWith === 'xmlhttprequest';

        if ($token === '' || !hash_equals($savedToken, $token)) {
            http_response_code(403);
            if ($isJsonRequest) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error' => [
                        'code' => 'CSRF_TOKEN_MISMATCH',
                        'message' => 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.'
                    ]
                ], JSON_UNESCAPED_UNICODE);
            } else {
                ErrorHandler::renderErrorView(403);
            }
            exit;
        }
    }
}

$isAdminUiPath = $path === '/admin' || str_starts_with($path, '/admin/');
$isAdminApiPath = $path === '/api/admin' || str_starts_with($path, '/api/admin/');

if ($isAdminUiPath || $isAdminApiPath) {
    $user = $_SESSION['user'] ?? null;
    $isAuthenticated = is_array($user) && !empty($user['id']);
    $isAdmin = $isAuthenticated && ($user['role'] ?? '') === 'admin';

    if ($isAdminUiPath) {
        if (!$isAuthenticated) {
            $loginUrl = BASE_URL . '/auth/login?redirect=' . rawurlencode($path);
            header('Location: ' . $loginUrl);
            exit;
        } elseif (!$isAdmin) {
            http_response_code(403);
            ErrorHandler::renderErrorView(403);
            exit;
        }
    } else {
        if (!$isAuthenticated) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => 'Unauthenticated',
                'message' => 'Bạn cần đăng nhập để truy cập tài nguyên này.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        } elseif (!$isAdmin) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => 'Forbidden',
                'message' => 'Bạn không có quyền truy cập.',
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

$router = new Router();

// Storefront & Home Routes
$router->get('/', 'HomeController@index');
$router->get('/home', 'HomeController@index');
$router->get('/home/index', 'HomeController@index');
$router->get('/home/search', 'HomeController@search');
$router->get('/search', 'HomeController@search');
$router->get('/home/ajaxSearch', 'HomeController@ajaxSearch');

// Canonical Category & Product Detail Routes
$router->get('/category/{slug}', 'HomeController@category');
$router->get('/product', 'HomeController@search');
$router->get('/products', 'HomeController@search');
$router->get('/product/detail/{slug}', 'ProductController@detail');

// Auth Routes
$router->get('/auth/login', 'AuthController@login');
$router->post('/auth/login', 'AuthController@login');
$router->get('/auth/register', 'AuthController@register');
$router->post('/auth/register', 'AuthController@register');
$router->get('/auth/logout', 'AuthController@logout');
$router->post('/auth/logout', 'AuthController@logout');
$router->get('/auth/forgot', 'AuthController@forgot');
$router->post('/auth/forgot', 'AuthController@forgot');
$router->get('/auth/reset', 'AuthController@reset');
$router->post('/auth/reset', 'AuthController@reset');

// Cart Routes
$router->get('/cart', 'CartController@index');
$router->post('/cart/add', 'CartController@add');
$router->post('/cart/update', 'CartController@update');
$router->post('/cart/remove', 'CartController@remove');

// Checkout Routes
$router->get('/checkout', 'CheckoutController@index');
$router->post('/checkout/submit', 'CheckoutController@submit');
$router->get('/checkout/success', 'CheckoutController@success');
$router->post('/checkout/apply_coupon', 'CheckoutController@apply_coupon');
$router->post('/checkout/remove_coupon', 'CheckoutController@remove_coupon');

// Product Review Route
$router->post('/product/review', 'ProductController@review');

// Profile & Account Routes
$router->get('/profile', 'ProfileController@index');
$router->get('/profile/orders', 'ProfileController@orders');
$router->get('/profile/order_detail', 'ProfileController@order_detail');
$router->get('/profile/order_detail/{id}', 'ProfileController@order_detail');
$router->get('/profile/order-detail', 'ProfileController@order_detail');
$router->get('/profile/order-detail/{id}', 'ProfileController@order_detail');
$router->get('/profile/notifications', 'ProfileController@notifications');
$router->get('/profile/wishlist', 'ProfileController@wishlist');
$router->get('/profile/return', 'ProfileController@return');
$router->get('/profile/return/{id}', 'ProfileController@return');
$router->post('/profile/submit_return', 'ProfileController@submit_return');
$router->post('/profile/change_password', 'ProfileController@change_password');
$router->post('/profile/cancel_order', 'ProfileController@cancel_order');
$router->get('/profile/addresses', 'ProfileController@addresses');
$router->post('/profile/add-address', 'ProfileController@add_address');
$router->post('/profile/edit-address', 'ProfileController@edit_address');
$router->post('/profile/delete-address', 'ProfileController@delete_address');
$router->post('/profile/set-default-address', 'ProfileController@set_default_address');
$router->post('/profile/repay', 'ProfileController@repay');

// Payment Callbacks
$router->get('/payment/vnpay-return', 'PaymentController@vnpayReturn');
$router->get('/payment/vnpay-ipn', 'PaymentController@vnpayIpn');
$vnpayConfig = require ROOT_PATH . '/config/vnpay.php';
if (APP_ENV === 'development' && !empty($vnpayConfig['simulator_enabled'])) {
    $router->get('/payment/vnpay-sandbox-sim', 'PaymentController@vnpaySandboxSim');
}

// Wishlist Routes
$router->get('/wishlist', 'WishlistController@index');
$router->post('/wishlist/add', 'WishlistController@add');
$router->post('/wishlist/remove', 'WishlistController@remove');
$router->post('/wishlist/toggle', 'WishlistController@toggle');

// API Inventory Endpoints
$router->get('/api/inventory/summary', 'InventoryApiController@summary');
$router->get('/api/inventory/product/{id}', 'InventoryApiController@product');
$router->get('/api/inventory/category/{id}', 'InventoryApiController@category');

// API Notifications & Chatbot
$router->get('/api/notifications/unread', 'ProfileController@apiUnreadNotifications');
$router->get('/chatbot/query', 'ChatbotController@query');
$router->post('/chatbot/sync', 'ChatbotController@sync');

// Admin Dashboard Route
$router->get('/admin', 'AdminController@index');
$router->get('/admin/dashboard', 'AdminController@index');
$router->get('/api/admin/notifications', 'AdminController@notifications');
$router->post('/api/admin/notifications/mark_read', 'AdminController@markReadNotifications');

// Admin Category Routes
$router->get('/admin/categories', 'AdminCategoryController@index');
$router->get('/admin/categories/create', 'AdminCategoryController@create');
$router->post('/admin/categories/store', 'AdminCategoryController@store');
$router->get('/admin/categories/edit/{id}', 'AdminCategoryController@edit');
$router->post('/admin/categories/update/{id}', 'AdminCategoryController@update');
$router->post('/admin/categories/delete/{id}', 'AdminCategoryController@delete');
$router->post('/admin/categories/toggle-status/{id}', 'AdminCategoryController@toggleStatus');

// Admin Brand Routes
$router->get('/admin/brands', 'AdminBrandController@index');
$router->get('/admin/brands/create', 'AdminBrandController@create');
$router->post('/admin/brands/store', 'AdminBrandController@store');
$router->get('/admin/brands/edit/{id}', 'AdminBrandController@edit');
$router->post('/admin/brands/update/{id}', 'AdminBrandController@update');
$router->post('/admin/brands/delete/{id}', 'AdminBrandController@delete');

// Admin Product & Inventory Routes
$router->get('/admin/products', 'AdminProductController@index');
$router->get('/admin/products/create', 'AdminProductController@create');
$router->post('/admin/products/store', 'AdminProductController@store');
$router->get('/admin/products/edit/{id}', 'AdminProductController@edit');
$router->post('/admin/products/update/{id}', 'AdminProductController@update');
$router->post('/admin/products/delete/{id}', 'AdminProductController@delete');
$router->post('/admin/products/toggle-status/{id}', 'AdminProductController@toggleStatus');
$router->post('/admin/products/ai-assistant', 'AdminProductController@aiAssistant');
$router->post('/admin/products/ai-assistant/rewrite', 'AdminProductController@aiAssistantRewrite');
$router->get('/admin/products/ai-assistant/history', 'AdminProductController@aiAssistantHistory');
$router->post('/admin/products/ai-assistant/history/action', 'AdminProductController@aiAssistantHistoryAction');
$router->post('/admin/products/ai-assistant/feedback', 'AdminProductController@aiAssistantFeedback');
$router->post('/admin/products/adjust-stock', 'AdminProductController@adjustStock');
$router->get('/admin/inventory/logs', 'AdminInventoryController@logs');

// Admin Order Routes
$router->get('/admin/orders', 'AdminOrderController@index');
$router->get('/admin/orders/detail/{id}', 'AdminOrderController@detail');
$router->post('/admin/orders/update_status/{id}', 'AdminOrderController@updateStatus');

// Admin User & Customer Routes
$router->get('/admin/users', 'AdminUserController@index');
$router->get('/admin/customers', 'AdminUserController@index');
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

// Static frontend page: Thu cũ đổi mới máy cũ
$router->get('/thu-cu-doi-moi', 'HomeController@trade_in');

// PC Builder Routes
$router->get('/build-pc', 'PcBuilderController@index');
$router->get('/pc-builder/products', 'PcBuilderController@getProducts');
$router->get('/pc-builder/prebuilt', 'PcBuilderController@prebuilt');
$router->post('/pc-builder/analysis', 'PcBuilderController@getAnalysis');
$router->post('/pc-builder/add-to-cart', 'PcBuilderController@addToCart');

// News Routes
$router->get('/tin-tuc', 'NewsController@index');
$router->get('/tin-tuc/{slug}', 'NewsController@show');
$router->get('/post', 'PostController@index');
$router->get('/post/detail/{slug}', 'PostController@detail');
$router->get('/post/{slug}', 'PostController@detail');

// AI & Compare Routes
$router->get('/compare', 'CompareController@index');
$router->post('/compare/add', 'CompareController@add');
$router->post('/compare/remove', 'CompareController@remove');
$router->post('/compare/aiCompare', 'CompareController@aiCompare');
$router->post('/ai/compare', 'CompareController@aiCompare');

$router->get('/ai-assistant', 'AiAssistantController@index');
$router->post('/ai/recommend', 'AiAssistantController@recommend');
$router->post('/ai/favorite', 'AiAssistantController@saveFavorite');

$router->get('/product/ai-chat-history', 'ProductController@chatHistory');
$router->post('/product/ai-chat', 'ProductController@chat');
$router->post('/product/ai-chat-sync-guest', 'ProductController@syncGuestChat');
$router->get('/profile/ai-chat-history', 'ProfileController@aiChatHistory');

$router->dispatch($url);
