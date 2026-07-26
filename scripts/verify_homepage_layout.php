<?php
$url = 'http://localhost:8000/';
$html = @file_get_contents($url);

echo "=== Homepage Layout Verification ===\n";
echo "URL: $url\n";
echo "Content Length: " . strlen($html) . " bytes\n\n";

$checks = [
    'Flash Sale Active'        => str_contains($html, 'FLASH SALE'),
    'Flash Sale Countdown'     => str_contains($html, 'data-end-time') && !str_contains($html, 'data-end-time=""'),
    'Sản phẩm nổi bật (Hot)'   => str_contains($html, 'Sản phẩm nổi bật'),
    'Sản phẩm bán chạy (Sell)' => str_contains($html, 'Sản phẩm bán chạy'),
    'Sản phẩm mới về (New)'    => str_contains($html, 'Sản phẩm mới về'),
    'Danh mục sản phẩm (20)'   => str_contains($html, 'Danh mục sản phẩm'),
    'Laptop Gaming Section'    => str_contains($html, 'Laptop Gaming'),
    'Laptop Văn Phòng Section' => str_contains($html, 'Laptop Văn Phòng'),
    'PC Build Sẵn Section'     => str_contains($html, 'PC Build Sẵn'),
    'Linh Kiện PC Section'     => str_contains($html, 'Linh Kiện PC'),
    'No Parse Error'           => !str_contains($html, 'Parse error')
];

foreach ($checks as $title => $pass) {
    echo sprintf("%-30s %s\n", $title, $pass ? "[PASS]" : "[FAIL]");
}
