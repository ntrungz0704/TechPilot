<?php
require 'config/database.php';
$db = Database::getConnection();
$stmt = $db->query("SELECT p.id, p.name, p.image FROM products p JOIN categories c ON p.category_id = c.id WHERE c.name LIKE '%Laptop%'");
$products = $stmt->fetchAll();
foreach ($products as $p) {
    echo $p['id'] . " | " . $p['name'] . " | " . $p['image'] . "\n";
}
