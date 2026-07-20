<?php
require 'config/database.php';
try {
    $dsn = 'mysql:host=127.0.0.1;dbname=techpilot;charset=utf8mb4';
    $pdo = new PDO($dsn, 'root', '');
    echo "Connected\n";
} catch (PDOException $e) {
    echo $e->getMessage() . "\n";
}
