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
        if ($flashPrice > 0 && $flashPrice < $finalPrice) {
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
        return BASE_URL . '/' . ltrim($path, '/');
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

if (!function_exists('cartItems')) {
    function cartItems(): array
    {
        if (currentUser()) {
            return $_SESSION['cart'] ?? [];
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
    /** Tính số tiền coupon từ subtotal đã được server xác nhận. */
    function calculateCouponDiscount(array $coupon, float $subtotal): float
    {
        if ($subtotal <= 0 || $subtotal < (float)($coupon['min_order_value'] ?? 0)) {
            return 0.0;
        }

        $value = max(0.0, (float)($coupon['discount_value'] ?? 0));
        if (($coupon['type'] ?? '') === 'percent') {
            $discount = $subtotal * ($value / 100);
            $maxDiscount = max(0.0, (float)($coupon['max_discount'] ?? 0));
            if ($maxDiscount > 0) {
                $discount = min($discount, $maxDiscount);
            }
        } else {
            $discount = $value;
        }

        return min($subtotal, max(0.0, $discount));
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
        unset($_SESSION['flashes']);
        return $flashes;
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
