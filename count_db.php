<?php
require 'config/database.php';
$db = Database::getConnection();
$stmt = $db->query('SELECT id, name, slug FROM categories');
print_r($stmt->fetchAll());
