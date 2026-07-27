<?php
// Verification script to check integrity of product images in database and disk
require_once __DIR__ . '/config/database.php';
$db = Database::getConnection();

$imageDir = __DIR__ . '/public';
$errors = [];
$hashes = []; // To check for duplicates between different types

// Fetch all active products
$stmt = $db->query("
    SELECT p.id, p.name, p.image, c.slug as cat_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.status = 'active'
    ORDER BY p.id
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Map categories to broad types for duplicate hash check
function getBroadType($catSlug) {
    if ($catSlug === 'laptop-gaming' || $catSlug === 'laptop-van-phong') return 'laptop';
    if ($catSlug === 'pc-build-san') return 'pc';
    if ($catSlug === 'man-hinh') return 'monitor';
    if ($catSlug === 'office-gear') return 'office'; // Can be printer, projector, etc.
    return 'component';
}

echo "=== RUNNING AUTOMATED PRODUCT IMAGE VERIFICATION ===\n\n";

foreach ($products as $p) {
    $img = $p['image'];
    $id = $p['id'];
    $name = $p['name'];
    $catSlug = $p['cat_slug'];
    $broadType = getBroadType($catSlug);
    
    // Rule: No hotlink/external URL
    if (preg_match('/^https?:\/\//i', $img)) {
        $errors[] = "[ID: $id] $name: Đường dẫn ảnh bên ngoài (hotlink): '$img'";
        continue;
    }
    
    // Rule: Path should not contain iphone, macbook, phone, tablet
    $imgLower = strtolower($img);
    if (preg_match('/(iphone|macbook|phone|tablet)/i', $imgLower)) {
        $errors[] = "[ID: $id] $name: Tên file ảnh chứa từ cấm (iphone, macbook, phone, tablet): '$img'";
    }
    
    $fullPath = $imageDir . '/' . ltrim($img, '/');
    
    // Rule: File must exist
    if (empty($img) || !file_exists($fullPath) || is_dir($fullPath)) {
        $errors[] = "[ID: $id] $name: Tệp ảnh không tồn tại trên đĩa: '$img' (đầy đủ: $fullPath)";
        continue;
    }
    
    // Rule: No 0 byte file
    $size = filesize($fullPath);
    if ($size === 0) {
        $errors[] = "[ID: $id] $name: Tệp ảnh có kích thước 0 byte: '$img'";
        continue;
    }
    
    // Rule: MIME type must be image/webp, image/jpeg, or image/png
    $info = getimagesize($fullPath);
    if (!$info) {
        $errors[] = "[ID: $id] $name: Tệp không phải là định dạng ảnh hợp lệ: '$img'";
        continue;
    }
    $mime = $info['mime'];
    if ($mime !== 'image/webp' && $mime !== 'image/jpeg' && $mime !== 'image/png') {
        $errors[] = "[ID: $id] $name: Định dạng MIME không hợp lệ ($mime): '$img'";
    }
    
    // Rule: Minimum dimensions (e.g. 400px width/height for display, WebP original generated are 1200x1200px)
    $width = $info[0];
    $height = $info[1];
    if ($width < 400 || $height < 400) {
        $errors[] = "[ID: $id] $name: Kích thước ảnh quá nhỏ ({$width}x{$height}): '$img'";
    }
    
    // Calculate hash for duplicate checking
    $hash = md5_file($fullPath);
    
    // Rule: No duplicate hashes between different product categories (e.g. PC and printer using same image)
    if (isset($hashes[$hash])) {
        $prev = $hashes[$hash];
        if ($prev['broad_type'] !== $broadType) {
            $errors[] = "[ID: $id] $name ($broadType) sử dụng cùng ảnh (trùng hash) với [ID: {$prev['id']}] {$prev['name']} ({$prev['broad_type']}) - File: '$img'";
        }
    } else {
        $hashes[$hash] = [
            'id' => $id,
            'name' => $name,
            'broad_type' => $broadType,
            'img' => $img
        ];
    }
}

echo "Kết quả kiểm thử:\n";
if (empty($errors)) {
    echo "[PASS] 100% tệp ảnh sản phẩm trên TechPilot đều hợp lệ và toàn vẹn!\n";
} else {
    echo "[FAIL] Phát hiện các lỗi sau:\n";
    foreach ($errors as $err) {
        echo "  - $err\n";
    }
}
echo "\nDONE\n";
