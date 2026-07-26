<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getConnection();
$cats = $db->query('SELECT id, name, slug FROM categories')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cats as $c) {
    echo "#{$c['id']}: name='{$c['name']}' | slug='{$c['slug']}'\n";
}
