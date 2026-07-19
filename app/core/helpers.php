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
    function productImageUrl(?string $image = ''): string
    {
        $image = trim((string)$image);
        $fallback = 'assets/images/laptop.png';

        if ($image !== '') {
            $publicAssetPath = ROOT_PATH . '/public/assets/images/' . basename($image);
            if (file_exists($publicAssetPath) && !is_dir($publicAssetPath)) {
                return url('assets/images/' . basename($image));
            }
        }

        return url($fallback);
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

if (!function_exists('normalizeSearchKeyword')) {
    function normalizeSearchKeyword(string $keyword): string
    {
        $keyword = mb_strtolower(trim($keyword), 'UTF-8');
        $keyword = preg_replace('/\s+/u', ' ', $keyword);
        return $keyword ?? '';
    }
}

