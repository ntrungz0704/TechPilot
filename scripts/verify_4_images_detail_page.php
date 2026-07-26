<?php
$url = 'http://localhost:8000/product/detail/laptop-gaming-msi-katana-15-b13vgk-1211vn';
$html = @file_get_contents($url);

echo "=== Product Detail Images Verification ===\n";
echo "URL: $url\n";
echo "Content Length: " . strlen($html) . " bytes\n\n";

$containsMouse = str_contains($html, 'chuot') || str_contains($html, 'razer');
$containsLaptopAngle1 = str_contains($html, 'placeholder-laptop-1.svg');
$containsLaptopAngle2 = str_contains($html, 'placeholder-laptop-2.svg');
$containsLaptopAngle3 = str_contains($html, 'placeholder-laptop-3.svg');
$containsLaptopAngle4 = str_contains($html, 'placeholder-laptop-4.svg');

echo "Contains Mismatched Mouse Image: " . ($containsMouse ? "YES [FAIL]" : "NO [PASS]") . "\n";
echo "Contains Laptop Angle 1: " . ($containsLaptopAngle1 ? "YES [PASS]" : "NO [FAIL]") . "\n";
echo "Contains Laptop Angle 2: " . ($containsLaptopAngle2 ? "YES [PASS]" : "NO [FAIL]") . "\n";
echo "Contains Laptop Angle 3: " . ($containsLaptopAngle3 ? "YES [PASS]" : "NO [FAIL]") . "\n";
echo "Contains Laptop Angle 4: " . ($containsLaptopAngle4 ? "YES [PASS]" : "NO [FAIL]") . "\n";
