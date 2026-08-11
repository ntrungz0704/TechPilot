<?php
require __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.local.php';

try {
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Xóa ảnh sai lệch khỏi bảng products
    $sql1 = "UPDATE products p 
             JOIN categories c ON p.category_id = c.id 
             SET p.image = '' 
             WHERE c.slug != 'laptop' AND p.image LIKE '%laptop%'";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute();
    echo "Đã xóa " . $stmt1->rowCount() . " ảnh chính sai category.\n";

    // Xóa thư viện ảnh sai lệch khỏi bảng product_images
    $sql2 = "DELETE pi FROM product_images pi 
             JOIN products p ON pi.product_id = p.id 
             JOIN categories c ON p.category_id = c.id 
             WHERE c.slug != 'laptop' AND pi.image_url LIKE '%laptop%'";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute();
    echo "Đã xóa " . $stmt2->rowCount() . " ảnh phụ sai category.\n";

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
