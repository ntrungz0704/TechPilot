<?php

/**
 * Các hàm hỗ trợ (helper) dùng chung trong toàn bộ view
 */

// Polyfills for missing mbstring extension
if (!function_exists('mb_strtolower')) {
    function mb_strtolower(string $string, ?string $encoding = null): string {
        return strtolower($string);
    }
}
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $string, ?string $encoding = null): int {
        return strlen($string);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr(string $string, int $start, ?int $length = null, ?string $encoding = null): string {
        return $length !== null ? substr($string, $start, $length) : substr($string, $start);
    }
}


if (!function_exists('getActivePopupBanner')) {
    function getActivePopupBanner(): ?array {
        try {
            require_once ROOT_PATH . '/config/database.php';
            $db = Database::getConnection();
            if (!$db) return null;
            $stmt = $db->prepare("SELECT * FROM banners WHERE type = 'popup' AND status = 'active' ORDER BY position ASC, id DESC LIMIT 1");
            $stmt->execute();
            $banner = $stmt->fetch(PDO::FETCH_ASSOC);
            return $banner ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('formatPhone')) {
    function formatPhone(?string $phone): string
    {
        $phone = trim($phone ?? '');

        if ($phone === '') {
            return '';
        }

        if (str_starts_with($phone, '0')) {
            return '+84' . substr($phone, 1);
        }

        if (!str_starts_with($phone, '+84')) {
            return '+84' . $phone;
        }

        return $phone;
    }
}

if (!function_exists('formatPrice')) {
    function formatPrice($price): string
    {
        return number_format((float)$price, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('activeFlashSaleItemSql')) {
    /**
     * SQL scalar subquery dùng chung để chọn đúng một Flash Sale item hợp lệ.
     * Giá Flash phải tốt hơn cả giá gốc lẫn sale thường đang có hiệu lực.
     */
    function activeFlashSaleItemSql(string $selectedColumn, string $productAlias = 'p'): string
    {
        if (!in_array($selectedColumn, ['id', 'discount_price'], true)) {
            throw new InvalidArgumentException('Cột Flash Sale cần chọn không hợp lệ.');
        }
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $productAlias)) {
            throw new InvalidArgumentException('Product SQL alias không hợp lệ.');
        }

        return "(SELECT pricing_fsi.{$selectedColumn}
                 FROM flash_sale_items pricing_fsi
                 INNER JOIN flash_sales pricing_fs ON pricing_fs.id = pricing_fsi.flash_sale_id
                 WHERE pricing_fsi.product_id = {$productAlias}.id
                   AND pricing_fs.status = 'active'
                   AND pricing_fs.start_time <= NOW()
                   AND pricing_fs.end_time > NOW()
                   AND pricing_fsi.discount_price > 0
                   AND pricing_fsi.allocation_quantity > 0
                   AND pricing_fsi.sold_quantity >= 0
                   AND pricing_fsi.sold_quantity < pricing_fsi.allocation_quantity
                   AND pricing_fsi.limit_per_user > 0
                   AND pricing_fsi.discount_price < CASE
                       WHEN {$productAlias}.sale_price > 0
                        AND {$productAlias}.sale_price < {$productAlias}.price
                       THEN {$productAlias}.sale_price
                       ELSE {$productAlias}.price
                   END
                 ORDER BY pricing_fsi.discount_price ASC, pricing_fsi.id ASC
                 LIMIT 1)";
    }
}

if (!function_exists('activeFlashPriceSql')) {
    function activeFlashPriceSql(string $productAlias = 'p'): string
    {
        return activeFlashSaleItemSql('discount_price', $productAlias);
    }
}

if (!function_exists('activeFlashSaleItemIdSql')) {
    function activeFlashSaleItemIdSql(string $productAlias = 'p'): string
    {
        return activeFlashSaleItemSql('id', $productAlias);
    }
}

if (!function_exists('effectiveProductPriceSql')) {
    /** SQL expression tương đương getEffectiveProductData() để lọc/sắp xếp. */
    function effectiveProductPriceSql(string $productAlias = 'p'): string
    {
        $flashPriceSql = activeFlashPriceSql($productAlias);

        return "LEAST(
                    {$productAlias}.price,
                    CASE
                        WHEN {$productAlias}.sale_price > 0
                         AND {$productAlias}.sale_price < {$productAlias}.price
                        THEN {$productAlias}.sale_price
                        ELSE {$productAlias}.price
                    END,
                    COALESCE({$flashPriceSql}, {$productAlias}.price)
                )";
    }
}

if (!function_exists('getEffectiveProductData')) {
    /**
     * Đồng bộ duy nhất một nguồn sự thật (Single Source of Truth) cho giá sản phẩm:
     * Giá bán (final_price) luôn là giá ưu đãi nhất hiện tại giữa discount_price (Flash sale), sale_price và price gốc.
     */
    function getEffectiveProductData(array $product): array
    {
        $price = max(0.0, (float)($product['price'] ?? 0));
        $finalPrice = $price;
        $priceSource = 'base';

        $salePrice = (float)($product['sale_price'] ?? 0);
        if ($salePrice > 0 && $salePrice < $finalPrice) {
            $finalPrice = $salePrice;
            $priceSource = 'sale';
        }

        $flashPrice = (float)($product['discount_price'] ?? 0);
        $fsStock = isset($product['fs_stock']) ? (int)$product['fs_stock'] : (isset($product['allocation_quantity']) ? (int)$product['allocation_quantity'] : null);
        $fsSold = isset($product['fs_sold']) ? (int)$product['fs_sold'] : (isset($product['sold_quantity']) ? (int)$product['sold_quantity'] : null);

        $isFsDepleted = ($fsStock !== null && $fsSold !== null && $fsStock > 0 && $fsSold >= $fsStock);

        if ($flashPrice > 0 && $flashPrice < $finalPrice && !$isFsDepleted) {
            $finalPrice = $flashPrice;
            $priceSource = 'flash';
        }

        $hasDiscount = ($finalPrice < $price);
        $discountPct = ($hasDiscount && $price > 0) ? round((($price - $finalPrice) / $price) * 100) : 0;

        return [
            'original_price' => $price,
            'final_price'    => $finalPrice,
            'has_discount'   => $hasDiscount,
            'discount_pct'   => (int)$discountPct,
            'is_flash_sale'  => $priceSource === 'flash',
            'price_source'   => $priceSource,
        ];
    }
}

if (!function_exists('formatStockText')) {
    function formatStockText(int $stock, string $categorySlug = ''): string
    {
        $slug = strtolower(trim($categorySlug));
        $unit = 'sản phẩm';
        if (in_array($slug, ['laptop', 'pc', 'prebuilt_pc', 'prebuilt-pc'])) {
            $unit = 'máy';
        } elseif (in_array($slug, ['monitor', 'man-hinh', 'màn-hình'])) {
            $unit = 'chiếc';
        } elseif (in_array($slug, ['cpu', 'vga', 'mainboard', 'ram', 'storage', 'psu', 'case', 'cooling', 'keyboard', 'mouse', 'headset', 'chair', 'speaker', 'console', 'accessories'])) {
            $unit = 'cái';
        }
        return 'Còn ' . $stock . ' ' . $unit;
    }
}

if (!function_exists('renderStars')) {
    function renderStars(float $rating): string
    {
        $full = floor($rating);
        $half = ($rating - $full) >= 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;
        $html = str_repeat('<i class="fa-solid fa-star"></i>', (int)$full);
        if ($half) {
            $html .= '<i class="fa-solid fa-star-half-stroke"></i>';
        }
        $html .= str_repeat('<i class="fa-regular fa-star"></i>', (int)$empty);
        return $html;
    }
}

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $baseUrl = defined('BASE_URL') ? BASE_URL : (getenv('APP_URL') ?: '');
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('resolveAbsoluteUrl')) {
    /**
     * Thuật toán tính toán absolute URL với kiểm tra validate APP_URL nghiêm ngặt.
     */
    function resolveAbsoluteUrl(string $path = '', ?string $appUrl = null, ?string $baseUrl = null, array $server = []): string
    {
        $appUrl = is_string($appUrl) ? trim($appUrl) : '';
        $baseUrl = is_string($baseUrl) ? trim($baseUrl) : '';
        $validAppUrl = false;

        if ($appUrl !== '') {
            // Kiểm tra scheme http:// hoặc https://
            if (preg_match('/^https?:\/\//i', $appUrl)) {
                $host = parse_url($appUrl, PHP_URL_HOST);
                if (!empty($host) && preg_match('/^[a-zA-Z0-9.:\-\[\]]+$/', $host)) {
                    $validAppUrl = true;
                }
            }

            if (!$validAppUrl) {
                error_log("[TechPilot Config Warning] Invalid APP_URL configured: '{$appUrl}'. Falling back to auto-detected origin.");
            }
        }

        if ($validAppUrl) {
            $base = rtrim($appUrl, '/');
        } else {
            // Tự detect scheme
            $scheme = 'http';
            if (
                (!empty($server['HTTPS']) && $server['HTTPS'] !== 'off') ||
                (!empty($server['HTTP_X_FORWARDED_PROTO']) && $server['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (!empty($server['SERVER_PORT']) && (int)$server['SERVER_PORT'] === 443)
            ) {
                $scheme = 'https';
            }

            // Host an toàn: ưu tiên APP_HOST nếu có
            $rawHost = defined('APP_HOST') && APP_HOST !== ''
                ? APP_HOST
                : ($server['HTTP_HOST'] ?? $server['SERVER_NAME'] ?? 'localhost');

            $host = preg_replace('/[^a-zA-Z0-9.:\-\[\]]/', '', (string) $rawHost);
            if ($host === '') {
                $host = 'localhost';
            }

            $base = $scheme . '://' . $host;

            if ($baseUrl !== '') {
                $base .= '/' . ltrim($baseUrl, '/');
            }
        }

        $base = rtrim($base, '/');
        $path = ltrim($path, '/');

        return $path === '' ? $base . '/' : $base . '/' . $path;
    }
}

if (!function_exists('absoluteUrl')) {
    /**
     * Trả về absolute URL (scheme + host + BASE_URL) cho SEO meta tags.
     */
    function absoluteUrl(string $path = ''): string
    {
        return resolveAbsoluteUrl(
            $path,
            defined('APP_URL') ? APP_URL : null,
            defined('BASE_URL') ? BASE_URL : null,
            $_SERVER
        );
    }
}

if (!function_exists('bannerImageUrl')) {
    function bannerImageUrl(?string $image = ''): string
    {
        $image = trim((string)$image);
        if ($image !== '') {
            $baseName = basename($image);
            $legacyPath = ROOT_PATH . '/public/assets/images/' . $baseName;
            if (file_exists($legacyPath) && !is_dir($legacyPath)) {
                return url('assets/images/' . $baseName);
            }
            $publicPath = ROOT_PATH . '/public/' . ltrim($image, '/');
            if (file_exists($publicPath) && !is_dir($publicPath)) {
                return url(ltrim($image, '/'));
            }
        }
        return url('assets/images/banner-1.jpg');
    }
}

if (!function_exists('productImageUrl')) {
    function productImageUrl(?string $image = '', ?string $productType = '', ?int $productId = null): string
    {
        $image = trim((string)$image);
        
        // 1. If exact relative path exists inside public/ and is NOT a 68-byte dummy file
        if ($image !== '') {
            $publicAssetPath = ROOT_PATH . '/public/' . ltrim($image, '/');
            if (file_exists($publicAssetPath) && !is_dir($publicAssetPath) && filesize($publicAssetPath) > 1000) {
                return url($image);
            }
            
            // Check by basename in assets/images/
            $legacyPath = ROOT_PATH . '/public/assets/images/' . basename($image);
            if (file_exists($legacyPath) && !is_dir($legacyPath) && filesize($legacyPath) > 1000) {
                return url('assets/images/' . basename($image));
            }
            
            // Check by basename in assets/images/products/
            $legacyPathProducts = ROOT_PATH . '/public/assets/images/products/' . basename($image);
            if (file_exists($legacyPathProducts) && !is_dir($legacyPathProducts) && filesize($legacyPathProducts) > 1000) {
                return url('assets/images/products/' . basename($image));
            }

            // Check across category subfolders
            $categories = [
                'laptop', 'pc', 'monitor', 'mainboard', 'cpu', 'vga', 'ram', 'storage', 
                'case', 'cooling', 'psu', 'keyboard', 'mouse', 'chair', 'headset', 
                'speaker', 'console', 'accessories', 'office-equipment', 'power-bank', 'networking'
            ];
            foreach ($categories as $cat) {
                $subPath = ROOT_PATH . '/public/assets/images/products/' . $cat . '/' . basename($image);
                if (file_exists($subPath) && !is_dir($subPath) && filesize($subPath) > 1000) {
                    return url('assets/images/products/' . $cat . '/' . basename($image));
                }
            }

            // Check by product ID: prod_{id}.jpg/.png/.webp
            if ($productId !== null && $productId > 0) {
                $productsDir = ROOT_PATH . '/public/assets/images/products/';
                foreach (['png', 'jpg', 'webp'] as $ext) {
                    $realFile = 'prod_' . $productId . '.' . $ext;
                    $realPath = $productsDir . $realFile;
                    if (file_exists($realPath) && filesize($realPath) > 1000) {
                        return url('assets/images/products/' . $realFile);
                    }
                }
            }
        }
        
        // 2. Select category slug based on productType/image
        $str = strtolower(trim((string)$productType . ' ' . (string)$image));
        $catSlug = 'laptop';
        
        if (str_contains($str, 'office') || str_contains($str, 'hub') || str_contains($str, 'baseus') || str_contains($str, 'chuyển đổi') || str_contains($str, 'máy in') || str_contains($str, 'scan') || str_contains($str, 'thiết bị văn phòng')) {
            $catSlug = 'office-equipment';
        } elseif (str_contains($str, 'pc') || str_contains($str, 'desktop') || str_contains($str, 'workstation') || str_contains($str, 'build')) {
            $catSlug = 'pc';
        } elseif (str_contains($str, 'monitor') || str_contains($str, 'màn hình')) {
            $catSlug = 'monitor';
        } elseif (str_contains($str, 'vga') || str_contains($str, 'card màn hình') || str_contains($str, 'gpu') || str_contains($str, 'rtx') || str_contains($str, 'radeon')) {
            $catSlug = 'vga';
        } elseif (str_contains($str, 'cpu') || str_contains($str, 'intel') || str_contains($str, 'ryzen')) {
            $catSlug = 'cpu';
        } elseif (str_contains($str, 'mainboard') || str_contains($str, 'motherboard') || str_contains($str, 'bo mạch')) {
            $catSlug = 'mainboard';
        } elseif (str_contains($str, 'ram') || str_contains($str, 'ddr4') || str_contains($str, 'ddr5')) {
            $catSlug = 'ram';
        } elseif (str_contains($str, 'storage') || str_contains($str, 'ssd') || str_contains($str, 'hdd') || str_contains($str, 'ổ cứng')) {
            $catSlug = 'storage';
        } elseif (str_contains($str, 'case') || str_contains($str, 'vỏ')) {
            $catSlug = 'case';
        } elseif (str_contains($str, 'cooling') || str_contains($str, 'tản nhiệt')) {
            $catSlug = 'cooling';
        } elseif (str_contains($str, 'psu') || str_contains($str, 'nguồn')) {
            $catSlug = 'psu';
        } elseif (str_contains($str, 'keyboard') || str_contains($str, 'bàn phím')) {
            $catSlug = 'keyboard';
        } elseif (str_contains($str, 'mouse') || str_contains($str, 'chuột')) {
            $catSlug = 'mouse';
        } elseif (str_contains($str, 'chair') || str_contains($str, 'ghế')) {
            $catSlug = 'chair';
        } elseif (str_contains($str, 'headset') || str_contains($str, 'tai nghe')) {
            $catSlug = 'headset';
        } elseif (str_contains($str, 'speaker') || str_contains($str, 'loa')) {
            $catSlug = 'speaker';
        } elseif (str_contains($str, 'power-bank') || str_contains($str, 'sạc dự phòng')) {
            $catSlug = 'power-bank';
        } elseif (str_contains($str, 'networking') || str_contains($str, 'wifi') || str_contains($str, 'router')) {
            $catSlug = 'networking';
        } elseif (str_contains($str, 'accessories') || str_contains($str, 'phụ kiện')) {
            $catSlug = 'accessories';
        }

        // Check if user dropped any image file in category subfolder
        $catDir = ROOT_PATH . '/public/assets/images/products/' . $catSlug;
        if (is_dir($catDir)) {
            $files = scandir($catDir);
            foreach ($files as $f) {
                if ($f === '.' || $f === '..') continue;
                $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                    return url('assets/images/products/' . $catSlug . '/' . $f);
                }
            }
        }

        // Map user custom placeholder files in assets/images/placeholders/
        $userCustomMap = [
            'laptop' => 'laptop.png',
            'monitor' => 'màn-hình.png',
            'vga' => 'vga.png',
            'cpu' => 'cpu.png',
            'storage' => 'ssd.png',
            'case' => 'case.png',
            'cooling' => 'fan.png',
            'psu' => 'psu.png',
            'console' => 'placeholder-console-1.png',
            'pc' => 'case.png',
            'mainboard' => 'cpu.png',
            'ram' => 'ssd.png',
        ];

        $idSuffix = ($productId !== null && $productId > 0) ? "?id={$productId}" : "";

        if (isset($userCustomMap[$catSlug])) {
            $customFile = $userCustomMap[$catSlug];
            $customPath = ROOT_PATH . '/public/assets/images/placeholders/' . $customFile;
            if (file_exists($customPath)) {
                return url('assets/images/placeholders/' . $customFile) . $idSuffix;
            }
        }

        $phFile = 'placeholder-' . $catSlug . '-1.png';
        if ($catSlug === 'networking') $phFile = 'placeholder-office-equipment-1.png';
        
        $phPath = ROOT_PATH . '/public/assets/images/placeholders/' . $phFile;
        if (file_exists($phPath)) {
            return url('assets/images/placeholders/' . $phFile) . $idSuffix;
        }
        return url('assets/images/placeholders/laptop.png') . $idSuffix;
    }
}

if (!function_exists('currentUser')) {
    function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('assertActiveUserSession')) {
    /**
     * Tự động kiểm tra trạng thái khóa tài khoản trong Database trên mọi request.
     * Nếu bị Admin khóa status != active, lập tức hủy Session và Đăng xuất (Logout) ngay lập tức.
     */
    function assertActiveUserSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $sessionUser = $_SESSION['user'] ?? null;
        if (!is_array($sessionUser) || empty($sessionUser['id'])) {
            return;
        }

        require_once ROOT_PATH . '/config/database.php';
        $db = Database::getConnection();
        if (!$db) return;

        $stmt = $db->prepare('SELECT status FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => (int)$sessionUser['id']]);
        $status = $stmt->fetchColumn();

        if ($status !== false && $status !== 'active') {
            // User has been locked or deactivated by Admin -> Force Immediate Logout
            unset($_SESSION['user']);
            unset($_SESSION['cart']);
            unset($_SESSION['applied_coupon']);
            if (isset($_COOKIE['remember_techpilot'])) {
                setcookie('remember_techpilot', '', time() - 3600, '/');
            }

            $contentType   = strtolower((string)($_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? ''));
            $accept        = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
            $requestedWith = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

            $isJsonRequest = str_contains($contentType, 'application/json')
                || str_contains($accept, 'application/json')
                || $requestedWith === 'xmlhttprequest';

            if ($isJsonRequest) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error'   => 'ACCOUNT_LOCKED',
                    'message' => 'Tài khoản của bạn đã bị khóa bởi quản trị viên. Vui lòng liên hệ hỗ trợ.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                flash('error', 'Tài khoản của bạn đã bị khóa bởi quản trị viên.');
                $loginUrl = defined('BASE_URL') ? BASE_URL . '/auth/login' : '/auth/login';
                header('Location: ' . $loginUrl);
                exit;
            }
        }
    }
}

if (!function_exists('cartItems')) {
    function cartItems(): array
    {
        if (!empty($_SESSION['cart'])) {
            return $_SESSION['cart'];
        }
        return $_SESSION['guest_cart'] ?? [];
    }
}

if (!function_exists('cartCount')) {
    function cartCount(): int
    {
        $count = 0;
        foreach (cartItems() as $item) {
            $count += max(1, (int)($item['quantity'] ?? 1));
        }
        return $count;
    }
}

if (!function_exists('cartSubtotal')) {
    function cartSubtotal(): float
    {
        require_once ROOT_PATH . '/app/services/CartService.php';
        return (float)(new CartService())->getSummary()['subtotal'];
    }
}

if (!function_exists('shippingFee')) {
    /** Một quy tắc phí vận chuyển dùng chung cho cart, checkout và order. */
    function shippingFee(float $subtotal): float
    {
        if ($subtotal <= 0 || $subtotal >= 300000) {
            return 0.0;
        }

        return 30000.0;
    }
}

if (!function_exists('calculateCouponDiscount')) {
    /** Tính số tiền coupon từ subtotal đã được server xác nhận (Khóa mức giảm tối đa 20% tổng giá trị). */
    function calculateCouponDiscount(array $coupon, float $subtotal): float
    {
        if ($subtotal <= 0 || $subtotal < (float)($coupon['min_order_value'] ?? 0)) {
            return 0.0;
        }

        $value = max(0.0, (float)($coupon['discount_value'] ?? 0));
        $type = $coupon['type'] ?? 'fixed';

        if ($type === 'percent' || $type === 'percentage') {
            $percent = min(20.0, $value);
            $discount = $subtotal * ($percent / 100);
            $maxDiscount = max(0.0, (float)($coupon['max_discount'] ?? 0));
            if ($maxDiscount > 0) {
                $discount = min($discount, $maxDiscount);
            }
        } else {
            $discount = $value;
        }

        // QUY TẮC BẢO VỆ: Mức giảm tối đa tuyệt đối KHÔNG ĐƯỢC VƯỢT QUÁ 20% giá trị đơn hàng / sản phẩm
        $maxAllowed20Percent = $subtotal * 0.20;
        $finalDiscount = min($discount, $maxAllowed20Percent);

        return min($subtotal, max(0.0, $finalDiscount));
    }
}

if (!function_exists('flash')) {
    function flash(string $type, string $message): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['flashes'])) {
            $_SESSION['flashes'] = [];
        }
        $_SESSION['flashes'][] = [
            'type' => $type,
            'message' => $message
        ];
    }
}

if (!function_exists('pullFlashes')) {
    function pullFlashes(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $flashes = $_SESSION['flashes'] ?? [];
        $_SESSION['flashes'] = [];
        return $flashes;
    }
}

if (!function_exists('getProductHighlightBadges')) {
    /** Trích xuất 3-4 thông số kỹ thuật nổi bật nhất của sản phẩm thuộc tất cả 20 danh mục (GearVN Style) */
    function getProductHighlightBadges(array $p): array
    {
        $specs = [];
        $rawS = [];
        if (!empty($p['specs'])) {
            $rawS = is_array($p['specs']) ? $p['specs'] : json_decode($p['specs'], true);
            if (isset($rawS['specs']) && is_array($rawS['specs'])) {
                $rawS = $rawS['specs'];
            }
        }

        $catSlug = strtolower((string)($p['category_slug'] ?? ''));
        $name = (string)($p['name'] ?? '');

        $get = function($key) use ($rawS) {
            return $rawS[$key] ?? null;
        };

        $cleanStr = function($str) {
            $str = preg_replace('/^(AMD|Intel|NVIDIA|GeForce|Radeon)®?\s*/iu', '', (string)$str);
            $str = preg_replace('/\s*(Box|Tray|C1|C2|V2|V3|Edition)$/iu', '', $str);
            return trim($str);
        };

        $cleanGpu = function($str) {
            $str = (string)$str;
            $str = preg_replace('/^(NVIDIA®?\s*|AMD®?\s*)?/iu', '', $str);
            $str = preg_replace('/\s*GPU$/iu', '', $str);
            return trim($str);
        };

        $formatGbRam = function($gb) {
            if ($gb === null || $gb === '') return null;
            if (is_numeric($gb)) return $gb . 'GB RAM';
            $str = trim((string)$gb);
            return str_contains(strtolower($str), 'ram') ? $str : $str . ' RAM';
        };

        $formatStorage = function($gb, $type = null) {
            if ($gb === null && $type === null) return null;
            if (is_numeric($gb)) {
                $capStr = ($gb >= 1000) ? round($gb / 1000) . 'TB' : $gb . 'GB';
                return $capStr . ' SSD';
            }
            return (string)($gb ?? $type);
        };

        switch ($catSlug) {
            case 'laptop':
            case 'laptop-gaming':
            case 'laptop-van-phong':
                $cpu = $get('cpu_model') ?? $get('cpu_short') ?? $get('cpu');
                if (!$cpu && preg_match('/(Intel[^\s]*|Core\s+i\d-[^\s]*|Ryzen\s+\d[^\s]*|Apple\s+M\d[^\s]*)/i', $name, $m)) {
                    $cpu = $m[1];
                }
                if ($cpu) $specs[] = ['icon' => 'fa-microchip', 'text' => $cleanStr($cpu)];

                $ramVal = $get('ram_gb') ?? $get('ram_capacity_gb') ?? $get('ram');
                if (!$ramVal && preg_match('/(\d+GB)\s+(DDR4|DDR5|LPDDR5)/i', $name, $m)) {
                    $ramVal = $m[1] . ' ' . $m[2];
                }
                $ram = $formatGbRam($ramVal);
                if ($ram) $specs[] = ['icon' => 'fa-memory', 'text' => (string)$ram];

                $vga = $get('gpu_model') ?? $get('gpu_chip') ?? $get('gpu_short') ?? $get('vga') ?? $get('gpu');
                if (!$vga && preg_match('/(RTX\s*\d{4}[^\s]*|GTX\s*\d{4}[^\s]*|Radeon\s+[^\s]+)/i', $name, $m)) {
                    $vga = $m[1];
                }
                if ($vga) $specs[] = ['icon' => 'fa-desktop', 'text' => $cleanGpu($vga)];

                $storageVal = $get('storage_gb') ?? $get('storage_capacity_gb') ?? $get('ssd') ?? $get('storage');
                $storageType = $get('storage_type');
                if (!$storageVal && preg_match('/(\d+(?:GB|TB))\s*(SSD|NVMe)?/i', $name, $m)) {
                    $storageVal = $m[1];
                }
                $ssd = $formatStorage($storageVal, $storageType);
                if ($ssd) $specs[] = ['icon' => 'fa-hard-drive', 'text' => (string)$ssd];

                $size = $get('display_size_inch') ?? $get('screen_size_inch') ?? $get('screen_short');
                $hz = $get('refresh_rate_hz');
                if ($size && count($specs) < 4) {
                    $screenStr = $size . '"' . ($hz ? ' ' . $hz . 'Hz' : '');
                    $specs[] = ['icon' => 'fa-tv', 'text' => $screenStr];
                }
                break;

            case 'pc':
            case 'pc-gaming':
            case 'pc-van-phong':
            case 'pc-build-san':
            case 'may-tinh-bo':
                $cpu = $get('cpu_model') ?? $get('cpu_short') ?? $get('cpu');
                if (!$cpu && preg_match('/(i\d-\d+[A-Z]*|Ryzen\s+\d\s+\d+[A-Z]*)/i', $name, $m)) {
                    $cpu = $m[1];
                }
                if ($cpu) $specs[] = ['icon' => 'fa-microchip', 'text' => $cleanStr($cpu)];

                $vga = $get('gpu_model') ?? $get('gpu_chip') ?? $get('vga') ?? $get('gpu');
                if (!$vga && preg_match('/(RTX\s*\d{4}[^\s]*|RX\s*\d{4}[^\s]*)/i', $name, $m)) {
                    $vga = $m[1];
                }
                if ($vga) $specs[] = ['icon' => 'fa-desktop', 'text' => $cleanGpu($vga)];

                $ramVal = $get('ram_gb') ?? $get('ram_capacity_gb') ?? $get('ram');
                $ram = $formatGbRam($ramVal);
                if ($ram) $specs[] = ['icon' => 'fa-memory', 'text' => (string)$ram];

                $storageVal = $get('storage') ?? $get('storage_gb') ?? $get('storage_capacity_gb') ?? $get('ssd');
                $ssd = $formatStorage($storageVal);
                if ($ssd) $specs[] = ['icon' => 'fa-hard-drive', 'text' => (string)$ssd];
                break;

            case 'monitor':
            case 'man-hinh':
                $size = $get('screen_size_inch') ?? $get('display_size_inch');
                if (!$size && preg_match('/(\d{2}(?:\.\d)?)\s*(?:inch|")?/i', $name, $m)) {
                    $size = $m[1];
                }
                if ($size) $specs[] = ['icon' => 'fa-expand', 'text' => $size . ' inch'];

                $panel = $get('panel_type') ?? $get('resolution');
                if (!$panel && preg_match('/(OLED|IPS|VA|TN|4K|2K|FHD)/i', $name, $m)) {
                    $panel = $m[1];
                }
                if ($panel) $specs[] = ['icon' => 'fa-tv', 'text' => (string)$panel];

                $hz = $get('refresh_rate_hz');
                if (!$hz && preg_match('/(\d{2,3}Hz)/i', $name, $m)) {
                    $hz = $m[1];
                }
                if ($hz) $specs[] = ['icon' => 'fa-gauge-high', 'text' => (str_contains((string)$hz, 'Hz') ? $hz : $hz . 'Hz')];

                $response = $get('response_time_ms');
                if ($response && count($specs) < 4) $specs[] = ['icon' => 'fa-stopwatch', 'text' => (str_contains((string)$response, 'ms') ? $response : $response . 'ms')];
                break;

            case 'vga':
                $gpu = $get('gpu_chip') ?? $get('gpu_model') ?? $get('architecture');
                if (!$gpu && preg_match('/(RTX\s*\d{4}[^\s]*|RX\s*\d{4}[^\s]*)/i', $name, $m)) {
                    $gpu = $m[1];
                }
                if ($gpu) $specs[] = ['icon' => 'fa-desktop', 'text' => $cleanGpu($gpu)];

                $vramGb = $get('vram_gb') ?? $get('gpu_vram_gb');
                $vramType = $get('vram_type');
                if ($vramGb) {
                    $specs[] = ['icon' => 'fa-database', 'text' => $vramGb . 'GB VRAM' . ($vramType ? ' ' . $vramType : '')];
                }

                $psuReq = $get('recommended_psu_w');
                if ($psuReq) $specs[] = ['icon' => 'fa-bolt', 'text' => 'PSU ' . $psuReq . 'W'];
                break;

            case 'cpu':
                $cores = $get('cores') ?? $get('cpu_cores');
                $threads = $get('threads') ?? $get('cpu_threads');
                if ($cores && $threads) {
                    $specs[] = ['icon' => 'fa-layer-group', 'text' => $cores . ' Nhân / ' . $threads . ' Luồng'];
                } elseif ($cores) {
                    $specs[] = ['icon' => 'fa-layer-group', 'text' => $cores . ' Nhân'];
                }

                $boost = $get('boost_clock_ghz');
                if ($boost) $specs[] = ['icon' => 'fa-gauge-high', 'text' => 'Up ' . $boost . 'GHz'];

                $socket = $get('socket');
                if ($socket) $specs[] = ['icon' => 'fa-microchip', 'text' => 'Socket ' . $socket];

                $power = $get('max_turbo_power_w') ?? $get('base_power_w');
                if ($power && count($specs) < 4) $specs[] = ['icon' => 'fa-bolt', 'text' => $power . 'W'];
                break;

            case 'ram':
                $cap = $get('total_capacity_gb') ?? $get('ram_gb');
                if ($cap) $specs[] = ['icon' => 'fa-memory', 'text' => $cap . 'GB'];

                $type = $get('memory_type') ? $get('memory_type') . ($get('speed_mhz') ? ' ' . $get('speed_mhz') . 'MHz' : '') : null;
                if ($type) $specs[] = ['icon' => 'fa-bolt', 'text' => $type];

                if ($get('rgb')) $specs[] = ['icon' => 'fa-lightbulb', 'text' => 'LED RGB'];
                break;

            case 'storage':
                $type = $get('drive_type') ?? ($get('interface') ? 'SSD ' . $get('interface') : 'SSD');
                if ($type) $specs[] = ['icon' => 'fa-hard-drive', 'text' => (string)$type];

                $cap = $get('capacity_gb') ?? $get('storage_gb');
                if ($cap) {
                    $capStr = ($cap >= 1000) ? round($cap / 1000) . 'TB' : $cap . 'GB';
                    $specs[] = ['icon' => 'fa-database', 'text' => $capStr];
                }

                $speed = $get('read_speed_mbps');
                if ($speed && count($specs) < 4) $specs[] = ['icon' => 'fa-gauge-high', 'text' => 'Đọc ' . $speed . 'MB/s'];
                break;

            case 'keyboard':
                $type = $get('keyboard_type') ?? 'Phím cơ';
                if ($type) $specs[] = ['icon' => 'fa-keyboard', 'text' => (string)$type];

                $layout = $get('layout');
                if ($layout) $specs[] = ['icon' => 'fa-border-all', 'text' => (str_starts_with(strtolower((string)$layout), 'layout') ? $layout : 'Layout ' . $layout)];

                $switch = $get('switch_model') ?? $get('switch_type');
                if ($switch) $specs[] = ['icon' => 'fa-sliders', 'text' => (string)$switch];

                $conn = is_array($get('connection')) ? implode('/', $get('connection')) : $get('connection');
                if ($conn && count($specs) < 4) $specs[] = ['icon' => 'fa-wifi', 'text' => (string)$conn];
                break;

            case 'mouse':
                $dpi = $get('max_dpi');
                if ($dpi) $specs[] = ['icon' => 'fa-crosshair', 'text' => number_format((float)$dpi, 0, ',', '.') . ' DPI'];

                $weight = $get('weight_g');
                if ($weight) $specs[] = ['icon' => 'fa-feather-pointed', 'text' => $weight . 'g'];

                $sensor = $get('sensor');
                if ($sensor && count($specs) < 4) $specs[] = ['icon' => 'fa-microchip', 'text' => (string)$sensor];
                break;

            case 'headset':
                $sound = $get('surround_sound') ?? ($get('headset_type') ? 'Tai nghe ' . $get('headset_type') : 'Âm thanh 7.1');
                if ($sound) $specs[] = ['icon' => 'fa-headphones', 'text' => (string)$sound];

                $driver = $get('driver_size_mm');
                if ($driver) $specs[] = ['icon' => 'fa-circle-dot', 'text' => 'Driver ' . $driver . 'mm'];

                $conn = is_array($get('connection')) ? implode('/', $get('connection')) : $get('connection');
                if ($conn && count($specs) < 4) $specs[] = ['icon' => 'fa-wifi', 'text' => (string)$conn];
                break;

            case 'cooling':
                $type = $get('cooler_type') ? 'Tản ' . ($get('cooler_type') === 'AIO' ? 'nước AIO' : 'khí') : null;
                if ($type) $specs[] = ['icon' => 'fa-fan', 'text' => $type];

                $cap = $get('cooling_capacity_w');
                if ($cap) $specs[] = ['icon' => 'fa-temperature-arrow-down', 'text' => 'TDP ' . $cap . 'W'];
                break;

            case 'psu':
                $watt = $get('wattage') ?? $get('psu_wattage');
                if ($watt) $specs[] = ['icon' => 'fa-bolt', 'text' => $watt . 'W'];

                $eff = $get('efficiency_rating');
                if ($eff) $specs[] = ['icon' => 'fa-shield-halved', 'text' => (string)$eff];
                break;

            case 'power-bank':
                $cap = $get('capacity_mah');
                if ($cap) $specs[] = ['icon' => 'fa-battery-full', 'text' => number_format((float)$cap, 0, ',', '.') . ' mAh'];

                $out = $get('max_output_w');
                if ($out) $specs[] = ['icon' => 'fa-bolt', 'text' => 'Sạc ' . $out . 'W'];
                break;

            default:
                break;
        }

        // Catch-all safety loop for any specs array if still < 3 items
        if (count($specs) < 3 && !empty($rawS)) {
            $labelMap = [
                'cpu_cores' => 'Nhân',
                'cores' => 'Nhân',
                'cpu_threads' => 'Luồng',
                'threads' => 'Luồng',
                'ram_gb' => 'GB RAM',
                'ram_capacity_gb' => 'GB RAM',
                'storage_gb' => 'GB SSD',
                'storage_capacity_gb' => 'GB SSD',
                'capacity_mah' => 'mAh',
                'max_dpi' => 'DPI',
                'weight_g' => 'g',
                'wattage' => 'W',
                'layout' => 'Layout ',
                'screen_size_inch' => '"',
                'display_size_inch' => '"',
                'refresh_rate_hz' => 'Hz',
                'warranty_months' => 'T bảo hành',
            ];

            // List of internal/meta keys to skip
            $skipKeys = ['schema_version', 'category_slug', 'model', 'migration_status', 'confidence', 'source'];

            foreach ($rawS as $k => $v) {
                if (in_array($k, $skipKeys, true) || is_array($v) || is_bool($v)) continue;
                $vStr = trim((string)$v);
                if ($vStr === '') continue;

                // Check if already in specs
                $alreadyAdded = false;
                foreach ($specs as $sp) {
                    if (str_contains($sp['text'], $vStr)) {
                        $alreadyAdded = true;
                        break;
                    }
                }
                if ($alreadyAdded) continue;

                if (is_numeric($vStr)) {
                    if (isset($labelMap[$k])) {
                        $text = str_starts_with($labelMap[$k], 'Layout') ? $labelMap[$k] . $vStr : $vStr . ' ' . $labelMap[$k];
                        $specs[] = ['icon' => 'fa-circle-check', 'text' => $text];
                    }
                    // Crucial: NEVER push bare numeric strings without labels!
                } else {
                    if (strlen($vStr) < 30) {
                        $specs[] = ['icon' => 'fa-circle-check', 'text' => $vStr];
                    }
                }

                if (count($specs) >= 4) break;
            }
        }

        return array_slice($specs, 0, 4);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">' . 
               '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('verifyCsrf')) {
    function verifyCsrf(?string $token = null): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $token = $token ?? $_POST['csrf_token'] ?? $_POST['_csrf'] ?? '';
        $saved = $_SESSION['csrf_token'] ?? '';
        return !empty($token) && hash_equals($saved, $token);
    }
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool
    {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        return $needle === '' || substr($haystack, -strlen($needle)) === $needle;
    }
}

if (!function_exists('safe_strlen')) {
    function safe_strlen(string $str): int
    {
        return function_exists('mb_strlen') ? mb_strlen($str, 'UTF-8') : strlen($str);
    }
}

if (!function_exists('safe_strtolower')) {
    function safe_strtolower(string $str): string
    {
        return function_exists('mb_strtolower') ? mb_strtolower($str, 'UTF-8') : strtolower($str);
    }
}

if (!function_exists('safe_substr')) {
    function safe_substr(string $str, int $start, ?int $length = null): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($str, $start, $length, 'UTF-8');
        }
        return $length !== null ? substr($str, $start, $length) : substr($str, $start);
    }
}

if (!function_exists('normalizeSearchKeyword')) {
    function normalizeSearchKeyword(string $keyword): string
    {
        $keyword = safe_strtolower(trim($keyword));
        $keyword = preg_replace('/\s+/u', ' ', $keyword);
        return $keyword !== null ? $keyword : '';
    }
}

/**
 * Tạo URL ảnh bài viết một cách thống nhất.
 * Hỗ trợ: URL tuyệt đối, path posts/..., legacy filename, placeholder.
 */
if (!function_exists('postImageUrl')) {
    function postImageUrl(?string $image): string
    {
        $image = trim((string)$image);

        if ($image === '') {
            return url('assets/images/products/placeholder-component.png');
        }

        // 1. URL tuyệt đối (https:// hoặc http://)
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        // 2. Nếu image đã có prefix posts/ → assets/images/posts/...
        if (str_starts_with($image, 'posts/')) {
            $fullPath = ROOT_PATH . '/public/assets/images/' . $image;
            if (file_exists($fullPath)) {
                return url('assets/images/' . $image);
            }
        }

        // 3. Thử đường dẫn posts/ (UploadService trả về dạng posts/filename.jpg)
        $postsPath = ROOT_PATH . '/public/assets/images/posts/' . basename($image);
        if (file_exists($postsPath)) {
            return url('assets/images/posts/' . basename($image));
        }

        // 4. Legacy: assets/images/news/filename.jpg (ảnh seed cũ)
        $legacyPath = ROOT_PATH . '/public/assets/images/news/' . basename($image);
        if (file_exists($legacyPath)) {
            return url('assets/images/news/' . basename($image));
        }

        // 5. Không tìm thấy → placeholder
        return url('assets/images/products/placeholder-component.png');
    }
}

/**
 * Trả về nhãn loại bài viết (post_type).
 */
if (!function_exists('postTypeLabel')) {
    function postTypeLabel(string $postType): string
    {
        $labels = [
            'news'       => 'Ra mắt & Xu hướng',
            'review'     => 'Đánh giá & Review',
            'guide'      => 'Tư vấn chọn mua',
            'howto'      => 'Mẹo hay & Thủ thuật',
            'comparison' => 'So sánh sản phẩm',
        ];
        return e($labels[$postType] ?? 'Tin tức');
    }
}

/**
 * Trả về nhãn category bài viết (category_slug).
 */
if (!function_exists('postCategoryLabel')) {
    function postCategoryLabel(string $slug): string
    {
        $labels = [
            'cong-nghe'         => 'Công nghệ',
            'laptop'            => 'Laptop',
            'pc-gaming'         => 'PC Gaming',
            'gaming'            => 'PC Gaming',
            'pc-linh-kien'      => 'PC & Linh kiện',
            'man-hinh'          => 'Màn hình',
            'gaming-gear'       => 'Gaming Gear',
            'ai-cong-nghe-moi'  => 'AI & Công nghệ mới',
            'ai'                => 'AI & Công nghệ mới',
            'thu-thuat'         => 'Thủ thuật',
        ];
        return e($labels[$slug] ?? 'Công nghệ');
    }
}

/**
 * Tính số phút đọc cho văn bản tiếng Việt (đếm từ UTF-8).
 */
if (!function_exists('readingMinutes')) {
    function readingMinutes(string $content): int
    {
        $text = strip_tags($content);
        preg_match_all('/[\p{L}\p{N}]+/u', $text, $matches);
        $wordCount = count($matches[0]);
        return max(1, (int)ceil($wordCount / 200));
    }
}

/**
 * Trả về Web URL hợp lệ cho logo thương hiệu, hoặc null nếu không có asset thực sự.
 * Không tự tạo hoặc giả lập logo.
 */
if (!function_exists('brandLogoUrl')) {
    function brandLogoUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $relativePath = ltrim(str_replace('\\', '/', trim($path)), '/');
        if (str_contains($relativePath, '..') || str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://') || preg_match('/^[A-Za-z]:/', $relativePath)) {
            return null;
        }

        if (!str_starts_with($relativePath, 'assets/images/brands/')) {
            return null;
        }

        $fullDiskPath = ROOT_PATH . '/public/' . $relativePath;
        if (!file_exists($fullDiskPath) || !is_file($fullDiskPath) || filesize($fullDiskPath) === 0) {
            return null;
        }

        $ext = strtolower(pathinfo($fullDiskPath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['svg', 'png'], true)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fullDiskPath);

        $isValidMime = false;
        if ($ext === 'png' && $mime === 'image/png') {
            $isValidMime = true;
        } elseif ($ext === 'svg' && in_array($mime, ['image/svg+xml', 'text/plain', 'text/xml'], true)) {
            $isValidMime = true;
        }

        if (!$isValidMime) {
            return null;
        }

        return url($relativePath);
    }
}
