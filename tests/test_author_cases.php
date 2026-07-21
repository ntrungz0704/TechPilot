<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Post.php';

$db = Database::getConnection();
if (!$db) {
    echo "DB Connection Failed\n";
    exit(1);
}

$postModel = new Post();

// Ensure test user exists or cleanup test records
$db->exec("DELETE FROM posts WHERE slug LIKE 'test-author-%'");
$db->exec("DELETE FROM users WHERE email = 'test_hieu@techpilot.vn'");

// Create user for Case A
$db->exec("INSERT INTO users (full_name, email, password, role) VALUES ('Nguyễn Minh Hiếu', 'test_hieu@techpilot.vn', 'hash', 'admin')");
$userId = (int)$db->lastInsertId();

// Case A: author_id = userId, author_name = null
$db->exec("INSERT INTO posts (author_id, author_name, title, slug, summary, content, image, status) VALUES ({$userId}, NULL, 'Test Case A', 'test-author-a', 'summary', 'content', 'img.jpg', 'published')");

// Case B: author_id = null, author_name = 'Ban biên tập TechPilot'
$db->exec("INSERT INTO posts (author_id, author_name, title, slug, summary, content, image, status) VALUES (NULL, 'Ban biên tập TechPilot', 'Test Case B', 'test-author-b', 'summary', 'content', 'img.jpg', 'published')");

// Case C: author_id = null, author_name = null
$db->exec("INSERT INTO posts (author_id, author_name, title, slug, summary, content, image, status) VALUES (NULL, NULL, 'Test Case C', 'test-author-c', 'summary', 'content', 'img.jpg', 'published')");

$postA = $postModel->getBySlug('test-author-a');
$postB = $postModel->getBySlug('test-author-b');
$postC = $postModel->getBySlug('test-author-c');

$resA = $postA['author_name'] ?? '';
$resB = $postB['author_name'] ?? '';
$resC = $postC['author_name'] ?? '';

$passA = ($resA === 'Nguyễn Minh Hiếu');
$passB = ($resB === 'Ban biên tập TechPilot');
$passC = ($resC === 'Đội ngũ TechPilot');

echo "Case A (User Author): " . ($passA ? "[PASS]" : "[FAIL]") . " -> Expected 'Nguyễn Minh Hiếu', Got '{$resA}'\n";
echo "Case B (Manual Author): " . ($passB ? "[PASS]" : "[FAIL]") . " -> Expected 'Ban biên tập TechPilot', Got '{$resB}'\n";
echo "Case C (Default Author): " . ($passC ? "[PASS]" : "[FAIL]") . " -> Expected 'Đội ngũ TechPilot', Got '{$resC}'\n";

// Cleanup test records
$db->exec("DELETE FROM posts WHERE slug LIKE 'test-author-%'");
$db->exec("DELETE FROM users WHERE email = 'test_hieu@techpilot.vn'");

exit(($passA && $passB && $passC) ? 0 : 1);
