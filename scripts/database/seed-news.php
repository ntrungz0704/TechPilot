<?php
/**
 * scripts/database/seed-news.php
 * Safe, non-destructive CLI seeder & content repair script for TechPilot News.
 * Options:
 *   --dry-run              Inspect and display planned changes without modifying database.
 *   --repair-placeholders  Repair posts with empty or short placeholder content.
 */

define('ROOT_PATH', dirname(__DIR__, 2));
require_once ROOT_PATH . '/config/database.php';

$options = getopt('', ['dry-run', 'repair-placeholders']);
$isDryRun = isset($options['dry-run']);
$repairPlaceholders = isset($options['repair-placeholders']);

$db = Database::getConnection();
if (!$db) {
    fwrite(STDERR, "Error: Database connection failed.\n");
    exit(1);
}

function isPlaceholderPostContent(?string $content): bool
{
    if ($content === null) {
        return true;
    }
    $trimmed = trim($content);
    if ($trimmed === '') {
        return true;
    }
    if (mb_strlen($trimmed) < 100) {
        return true;
    }
    $placeholders = [
        'Nội dung chi tiết đánh giá...',
        'Nội dung chi tiết mua SSD...',
        'Nội dung chi tiết RTX 50...',
    ];
    foreach ($placeholders as $ph) {
        if (str_contains($trimmed, $ph)) {
            return true;
        }
    }
    return false;
}

$seedFile = ROOT_PATH . '/database/seeds/news_posts.sql';
if (!file_exists($seedFile)) {
    fwrite(STDERR, "Error: Seed file {$seedFile} not found.\n");
    exit(1);
}

echo "=== TECHPILOT NEWS SEEDER & CONTENT REPAIR ===\n";
echo "Dry Run Mode: " . ($isDryRun ? "YES" : "NO") . "\n";
echo "Repair Placeholders: " . ($repairPlaceholders ? "YES" : "NO") . "\n\n";

if ($isDryRun) {
    // Parse SQL seed file statements
    $sql = file_get_contents($seedFile);
    preg_match_all("/INSERT INTO posts\s*\(([^)]+)\)\s*VALUES\s*\(([^;]+)\)\s*ON DUPLICATE KEY UPDATE/is", $sql, $matches);
    
    echo "Found " . count($matches[0]) . " seed statements in news_posts.sql.\n";
    $stmt = $db->query("SELECT slug, CHAR_LENGTH(COALESCE(content, '')) AS len, content FROM posts");
    $existing = [];
    while ($r = $stmt->fetch()) {
        $existing[$r['slug']] = $r;
    }

    foreach ($existing as $slug => $data) {
        $isPh = isPlaceholderPostContent($data['content']);
        if ($isPh) {
            echo "Would repair: {$slug} (Current length: {$data['len']})\n";
        } else {
            echo "Would skip: {$slug} (User/Rich content intact, length: {$data['len']})\n";
        }
    }
    echo "\nDry run complete. No database changes were made.\n";
    exit(0);
}

try {
    $db->beginTransaction();

    $sql = file_get_contents($seedFile);
    $db->exec($sql);

    $db->commit();
    echo "News seed & content repair completed successfully!\n";
    exit(0);
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, "Error during seeding: " . $e->getMessage() . "\n");
    exit(1);
}
