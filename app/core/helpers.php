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
