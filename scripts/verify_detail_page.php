<?php
$url = 'http://localhost:8000/product/detail/laptop-gaming-asus-rog-zephyrus-g16-gu605mi-qr116w';
$html = @file_get_contents($url);

echo "=== Product Detail Page Verification ===\n";
echo "URL: $url\n";
echo "Content Length: " . strlen($html) . " bytes\n";
echo "Title Tag: " . (preg_match('/<title>(.*?)<\/title>/', $html, $m) ? $m[1] : 'Not Found') . "\n";
echo "Product Name Present: " . (str_contains($html, 'ASUS ROG Zephyrus G16') ? 'YES' : 'NO') . "\n";
echo "Contains Parse Error: " . (str_contains($html, 'Parse error') ? 'YES' : 'NO') . "\n";
