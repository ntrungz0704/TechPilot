<?php
$url = 'http://localhost:8000/product/detail/laptop-gaming-msi-katana-15-b13vgk-1211vn';
$html = file_get_contents($url);

preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);
echo "=== ALL IMAGE SRCS ON DETAIL PAGE ===\n";
foreach ($matches[1] as $src) {
    if (str_contains($src, 'placeholder') || str_contains($src, 'products')) {
        echo " - $src\n";
    }
}
