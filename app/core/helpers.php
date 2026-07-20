<?php

/**
 * Các hàm hỗ trợ (helper) dùng chung trong toàn bộ view
 */

if (!function_exists('formatPrice')) {
    function formatPrice($price): string
    {
        return number_format((float)$price, 0, ',', '.') . 'đ';
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

if (!function_exists('productImageUrl')) {
    function productImageUrl(?string $image = '', ?string $productType = ''): string
    {
        $image = trim((string)$image);
        
        // 1. Check if image file exists in the public directory
        if ($image !== '') {
            $publicAssetPath = ROOT_PATH . '/public/' . ltrim($image, '/');
            if (file_exists($publicAssetPath) && !is_dir($publicAssetPath)) {
                return url($image);
            }
            
            // Also support compatibility check with only basename
            $legacyPath = ROOT_PATH . '/public/assets/images/' . basename($image);
            if (file_exists($legacyPath) && !is_dir($legacyPath)) {
                return url('assets/images/' . basename($image));
            }
            
            $legacyPathProducts = ROOT_PATH . '/public/assets/images/products/' . basename($image);
            if (file_exists($legacyPathProducts) && !is_dir($legacyPathProducts)) {
                return url('assets/images/products/' . basename($image));
            }
        }
        
        // 2. Select placeholder based on productType
        $productType = strtolower(trim((string)$productType));
        $phName = 'placeholder-component.webp';
        
        $type = 'component';
        if (str_contains($productType, 'laptop') || str_contains($productType, 'zephyrus') || str_contains($productType, 'pavilion') || str_contains($productType, 'gram') || str_contains($productType, 'ideapad') || str_contains($productType, 'g16') || str_contains($productType, 'zenbook')) {
            $type = 'laptop';
        } elseif (str_contains($productType, 'pc-build') || str_contains($productType, 'pc gaming') || str_contains($productType, 'pc office') || str_contains($productType, 'workstation') || str_contains($productType, 'desktop_pc') || str_contains($productType, 'gaming_pc') || str_contains($productType, 'office_pc') || str_contains($productType, 'build-san') || str_contains($productType, 'techpilot extreme') || str_contains($productType, 'techpilot advanced') || str_contains($productType, 'techpilot basic')) {
            $type = 'pc';
        } elseif (str_contains($productType, 'printer') || str_contains($productType, 'máy in') || str_contains($productType, 'laserjet')) {
            $type = 'printer';
        } elseif (str_contains($productType, 'projector') || str_contains($productType, 'máy chiếu') || str_contains($productType, 'cc200')) {
            $type = 'projector';
        } elseif (str_contains($productType, 'cpu') || str_contains($productType, 'intel core') || str_contains($productType, 'ryzen') || str_contains($productType, 'ultra 5') || str_contains($productType, 'ultra 7') || str_contains($productType, 'ultra 9') || str_contains($productType, 'processor')) {
            $type = 'cpu';
        } elseif (str_contains($productType, 'motherboard') || str_contains($productType, 'mainboard') || str_contains($productType, 'main-board') || str_contains($productType, 'b760') || str_contains($productType, 'b660') || str_contains($productType, 'z790') || str_contains($productType, 'b650') || str_contains($productType, 'h610') || str_contains($productType, 'z690') || str_contains($productType, 'z890') || str_contains($productType, 'b450') || str_contains($productType, 'b550') || str_contains($productType, 'x670')) {
            $type = 'motherboard';
        } elseif (str_contains($productType, 'gpu') || str_contains($productType, 'card màn hình') || str_contains($productType, 'rtx') || str_contains($productType, 'rx 6600') || str_contains($productType, 'rx 7800') || str_contains($productType, 'gtx') || str_contains($productType, 'rx 7900') || str_contains($productType, 'radeon')) {
            $type = 'gpu';
        } elseif (str_contains($productType, 'ssd') || str_contains($productType, 'hdd') || str_contains($productType, 'ổ cứng') || str_contains($productType, 'samsung 990') || str_contains($productType, 'wd blue') || str_contains($productType, 'nv2') || str_contains($productType, 'samsung 980') || str_contains($productType, 'crucial p3') || str_contains($productType, 'wd-blue')) {
            $type = 'ssd';
        } elseif (str_contains($productType, 'ram') || str_contains($productType, 'ddr4') || str_contains($productType, 'ddr5') || str_contains($productType, 'corsair vengeance') || str_contains($productType, 'kingston fury') || str_contains($productType, 'ripjaws') || str_contains($productType, 'crucial classic') || str_contains($productType, 'trident z5')) {
            $type = 'ram';
        } elseif (str_contains($productType, 'psu') || str_contains($productType, 'nguồn') || str_contains($productType, 'power supply') || str_contains($productType, 'cv450') || str_contains($productType, 'rm750') || str_contains($productType, 'pf550') || str_contains($productType, 'a650bn') || str_contains($productType, 'cv650') || str_contains($productType, 'pk750d') || str_contains($productType, 'a750gl') || str_contains($productType, 'focus-gx') || str_contains($productType, 'rm850x')) {
            $type = 'psu';
        } elseif (str_contains($productType, 'monitor') || str_contains($productType, 'màn hình') || str_contains($productType, 'odyssey') || str_contains($productType, 'ultragear') || str_contains($productType, 'vg279') || str_contains($productType, '24gq50f')) {
            $type = 'monitor';
        } elseif (str_contains($productType, 'router') || str_contains($productType, 'wifi') || str_contains($productType, 'mạng') || str_contains($productType, 'networking') || str_contains($productType, 'rt-ax53u') || str_contains($productType, 'bộ phát')) {
            $type = 'network';
        } elseif (str_contains($productType, 'keyboard') || str_contains($productType, 'mouse') || str_contains($productType, 'headset') || str_contains($productType, 'webcam') || str_contains($productType, 'speaker') || str_contains($productType, 'bàn phím') || str_contains($productType, 'chuột') || str_contains($productType, 'tai nghe') || str_contains($productType, 'gaming-gear') || str_contains($productType, 'office-gear') || str_contains($productType, 'accessory') || str_contains($productType, 'logitech g213') || str_contains($productType, 'deathadder') || str_contains($productType, 'k70 pro')) {
            $type = 'accessory';
        }

        switch ($type) {
            case 'laptop':
                $phName = 'placeholder-laptop.webp';
                break;
            case 'pc':
                $phName = 'placeholder-desktop-pc.webp';
                break;
            case 'printer':
                $phName = 'placeholder-printer.webp';
                break;
            case 'projector':
                $phName = 'placeholder-projector.webp';
                break;
            case 'cpu':
                $phName = 'placeholder-cpu.webp';
                break;
            case 'motherboard':
                $phName = 'placeholder-motherboard.webp';
                break;
            case 'gpu':
                $phName = 'placeholder-gpu.webp';
                break;
            case 'ssd':
                $phName = 'placeholder-ssd.webp';
                break;
            case 'ram':
                $phName = 'placeholder-ram.webp';
                break;
            case 'psu':
                $phName = 'placeholder-psu.webp';
                break;
            case 'monitor':
                $phName = 'placeholder-monitor.webp';
                break;
            case 'network':
                $phName = 'placeholder-network.webp';
                break;
            case 'accessory':
                $phName = 'placeholder-accessory.webp';
                break;
            default:
                $phName = 'placeholder-component.webp';
                break;
        }
        
        return url('assets/images/products/' . $phName);
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
        return $_SESSION['cart'] ?? [];
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
        $subtotal = 0.0;
        foreach (cartItems() as $item) {
            $subtotal += (float)($item['price'] ?? 0) * max(1, (int)($item['quantity'] ?? 1));
        }
        return $subtotal;
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

