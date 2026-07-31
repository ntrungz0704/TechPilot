<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/config/database.php';

$db = Database::getConnection();

$baseDir = ROOT_PATH . '/public/assets/images/products/';

$categories = [
    'laptop', 'pc', 'monitor', 'mainboard', 'cpu', 'vga', 'ram', 'storage', 
    'case', 'cooling', 'psu', 'keyboard', 'mouse', 'chair', 'headset', 
    'speaker', 'console', 'accessories', 'office-equipment', 'power-bank', 'networking'
];

$stmtUpdate = $db->prepare("
    UPDATE products p
    JOIN categories c ON p.category_id = c.id
    SET p.image = :image
    WHERE c.slug = :slug
");

echo "====================================================\n";
echo "🔄 TỰ ĐỘNG ĐỒNG BỘ ẢNH CHO TẤT CẢ SẢN PHẨM PHÂN LOẠI\n";
echo "====================================================\n\n";

$updatedCatCount = 0;
$totalUpdatedProds = 0;

foreach ($categories as $slug) {
    $dirPath = $baseDir . $slug;
    $foundImage = null;

    if (is_dir($dirPath)) {
        $files = scandir($dirPath);
        foreach ($files as $f) {
            if ($f === '.' || $f === '..') continue;
            $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                $foundImage = 'assets/images/products/' . $slug . '/' . $f;
                break;
            }
        }
    }

    if ($foundImage) {
        $stmtUpdate->execute([':image' => $foundImage, ':slug' => $slug]);
        $affected = $stmtUpdate->rowCount();
        $updatedCatCount++;
        $totalUpdatedProds += $affected;
        echo "✅ Danh mục [{$slug}]: Đã áp dụng ảnh '$foundImage' cho $affected sản phẩm.\n";
    } else {
        // Nếu chưa bỏ ảnh vào thư mục con, tạm thời gán ảnh placeholder cao cấp
        $phName = ($slug === 'networking') ? 'office-equipment' : ($slug === 'vga' ? 'vga' : $slug);
        $phPath = "assets/images/placeholders/placeholder-{$phName}-1.png";
        if (!file_exists(ROOT_PATH . '/public/' . $phPath)) {
            $phPath = "assets/images/placeholders/placeholder-laptop-1.png";
        }
        $stmtUpdate->execute([':image' => $phPath, ':slug' => $slug]);
        echo "⏳ Danh mục [{$slug}]: Chưa có ảnh trong thư mục. Đã gán tạm '$phPath'.\n";
    }
}

echo "\n----------------------------------------------------\n";
echo "🎉 ĐỒNG BỘ HOÀN TẤT!\n";
echo "Số danh mục đã nạp ảnh thực tế: $updatedCatCount / " . count($categories) . "\n";
echo "====================================================\n";
