<?php
require 'config/database.php';
$db = Database::getConnection();

$catSlug = 'office-gear';
$maxPrice = 15000000;

$stmt = $db->prepare("SELECT p.name, p.price, p.sale_price, c.slug as cat_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE c.slug = :slug");
$stmt->execute([':slug' => $catSlug]);
$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "All products in $catSlug:\n";
foreach($all as $row) {
    echo $row['name'] . ' - Price: ' . $row['price'] . ' - Sale: ' . $row['sale_price'] . "\n";
}
