<?php
require_once __DIR__ . '/../config/database.php';
$pdo = Database::getConnection();
$cats = $pdo->query("SELECT id, name, slug, status FROM categories ORDER BY id")->fetchAll();
print_r($cats);
