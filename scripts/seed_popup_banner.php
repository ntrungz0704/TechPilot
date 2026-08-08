<?php
require_once __DIR__ . '/../app/core/helpers.php';
require_once __DIR__ . '/../config/database.php';

$db = Database::getConnection();
if (!$db) {
    echo "Lỗi kết nối CSDL.\n";
    exit(1);
}

$stmt = $db->query("SELECT id FROM banners WHERE type = 'popup' LIMIT 1");
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    $up = $db->prepare("UPDATE banners SET title = :title, image = :image, link = :link, status = 'active' WHERE id = :id");
    $up->execute([
        ':title' => 'MEGA SALE TECHPILOT - BÙNG NỔ ƯU ĐÃI 50%',
        ':image' => 'banners/popup_mega_sale.jpg',
        ':link'  => url('products'),
        ':id'    => $existing['id']
    ]);
    echo "Đã cập nhật Popup Banner ID {$existing['id']}.\n";
} else {
    $ins = $db->prepare("INSERT INTO banners (title, image, link, type, position, status) VALUES (:title, :image, :link, 'popup', 1, 'active')");
    $ins->execute([
        ':title' => 'MEGA SALE TECHPILOT - BÙNG NỔ ƯU ĐÃI 50%',
        ':image' => 'banners/popup_mega_sale.jpg',
        ':link'  => url('products')
    ]);
    echo "Đã thêm Popup Banner mới vào CSDL.\n";
}
