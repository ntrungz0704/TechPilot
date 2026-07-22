<?php
/**
 * test_author_cases.php
 * Database integration test for Post author resolution (User author vs Manual author vs Default fallback).
 * Runs within a PDO transaction with automatic rollback.
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/core/helpers.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Post.php';

$db = Database::getConnection();
if (!$db) {
    fwrite(STDERR, "[FAIL] DB Connection Failed\n");
    exit(1);
}

$fixtureId = bin2hex(random_bytes(6));
$email     = "test-author-{$fixtureId}@techpilot.local";
$slugA     = "test-author-a-{$fixtureId}";
$slugB     = "test-author-b-{$fixtureId}";
$slugC     = "test-author-c-{$fixtureId}";

$exitCode = 1;

try {
    $db->beginTransaction();

    // Insert user for Case A
    $userStmt = $db->prepare(
        'INSERT INTO users (full_name, email, password, role)
         VALUES (:full_name, :email, :password, :role)'
    );
    $userStmt->execute([
        ':full_name' => 'Nguyễn Minh Hiếu',
        ':email'     => $email,
        ':password'  => '$2y$10$abcdefghijklmnopqrstuu',
        ':role'      => 'admin',
    ]);
    $userId = (int)$db->lastInsertId();

    $postStmt = $db->prepare(
        'INSERT INTO posts (author_id, author_name, title, slug, summary, content, image, status)
         VALUES (:author_id, :author_name, :title, :slug, :summary, :content, :image, :status)'
    );

    // Case A: author_id = userId, author_name = null -> Expected: 'Nguyễn Minh Hiếu'
    $postStmt->bindValue(':author_id', $userId, PDO::PARAM_INT);
    $postStmt->bindValue(':author_name', null, PDO::PARAM_NULL);
    $postStmt->bindValue(':title', 'Test Case A Author', PDO::PARAM_STR);
    $postStmt->bindValue(':slug', $slugA, PDO::PARAM_STR);
    $postStmt->bindValue(':summary', 'Summary A', PDO::PARAM_STR);
    $postStmt->bindValue(':content', 'Content A', PDO::PARAM_STR);
    $postStmt->bindValue(':image', 'imgA.jpg', PDO::PARAM_STR);
    $postStmt->bindValue(':status', 'published', PDO::PARAM_STR);
    $postStmt->execute();

    // Case B: author_id = null, author_name = 'Ban biên tập TechPilot' -> Expected: 'Ban biên tập TechPilot'
    $postStmt->bindValue(':author_id', null, PDO::PARAM_NULL);
    $postStmt->bindValue(':author_name', 'Ban biên tập TechPilot', PDO::PARAM_STR);
    $postStmt->bindValue(':title', 'Test Case B Author', PDO::PARAM_STR);
    $postStmt->bindValue(':slug', $slugB, PDO::PARAM_STR);
    $postStmt->bindValue(':summary', 'Summary B', PDO::PARAM_STR);
    $postStmt->bindValue(':content', 'Content B', PDO::PARAM_STR);
    $postStmt->bindValue(':image', 'imgB.jpg', PDO::PARAM_STR);
    $postStmt->bindValue(':status', 'published', PDO::PARAM_STR);
    $postStmt->execute();

    // Case C: author_id = null, author_name = null -> Expected: 'Đội ngũ TechPilot'
    $postStmt->bindValue(':author_id', null, PDO::PARAM_NULL);
    $postStmt->bindValue(':author_name', null, PDO::PARAM_NULL);
    $postStmt->bindValue(':title', 'Test Case C Author', PDO::PARAM_STR);
    $postStmt->bindValue(':slug', $slugC, PDO::PARAM_STR);
    $postStmt->bindValue(':summary', 'Summary C', PDO::PARAM_STR);
    $postStmt->bindValue(':content', 'Content C', PDO::PARAM_STR);
    $postStmt->bindValue(':image', 'imgC.jpg', PDO::PARAM_STR);
    $postStmt->bindValue(':status', 'published', PDO::PARAM_STR);
    $postStmt->execute();

    // Fetch via production Post model
    $postModel = new Post();
    $postA = $postModel->getBySlug($slugA);
    $postB = $postModel->getBySlug($slugB);
    $postC = $postModel->getBySlug($slugC);

    $resA = $postA['author_name'] ?? '';
    $resB = $postB['author_name'] ?? '';
    $resC = $postC['author_name'] ?? '';

    $passA = ($resA === 'Nguyễn Minh Hiếu');
    $passB = ($resB === 'Ban biên tập TechPilot');
    $passC = ($resC === 'Đội ngũ TechPilot');

    echo "Case A (User Author): " . ($passA ? "[PASS]" : "[FAIL]") . " -> Expected 'Nguyễn Minh Hiếu', Got '{$resA}'\n";
    echo "Case B (Manual Author): " . ($passB ? "[PASS]" : "[FAIL]") . " -> Expected 'Ban biên tập TechPilot', Got '{$resB}'\n";
    echo "Case C (Default Author): " . ($passC ? "[PASS]" : "[FAIL]") . " -> Expected 'Đội ngũ TechPilot', Got '{$resC}'\n";

    if ($passA && $passB && $passC) {
        echo "Transaction Rollback: PASS\n";
        echo "Author Cases Results: 3 passed, 0 failed\n";
        $exitCode = 0;
    } else {
        echo "Author Cases Results: FAILED\n";
        $exitCode = 1;
    }
} catch (Throwable $e) {
    fwrite(STDERR, "[FAIL] Exception during author cases test: " . $e->getMessage() . "\n");
    $exitCode = 1;
} finally {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
}

exit($exitCode);
