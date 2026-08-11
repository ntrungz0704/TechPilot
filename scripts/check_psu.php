<?php
require __DIR__ . '/../config/app.php';
$dbConfig = require __DIR__ . '/../config/database.local.php';

try {
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password']);
    
    // Find the Corsair PSU product
    $stmt = $pdo->query("SELECT id FROM products WHERE name LIKE '%Corsair Power-PS31%' LIMIT 1");
    $product_id = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
    $stmt->execute([$product_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
} catch (PDOException $e) {}
