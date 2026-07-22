<?php
/**
 * scripts/database/seed-news.php
 * Safe, non-destructive, per-slug CLI seeder & content repair script for TechPilot News.
 *
 * Options:
 *   --dry-run              Inspect and display planned changes without modifying database.
 *   --repair-placeholders  Allow repairing posts that currently contain empty/placeholder content.
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/config/database.php';

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

function runNewsSeeder(): void
{
    $options = getopt('', ['dry-run', 'repair-placeholders']);
    $isDryRun = isset($options['dry-run']);
    $repairPlaceholders = isset($options['repair-placeholders']);

    $db = Database::getConnection();
    if (!$db) {
        fwrite(STDERR, "Error: Database connection failed.\n");
        exit(1);
    }

    $seedFile = ROOT_PATH . '/database/seeds/news_posts.php';
    if (!file_exists($seedFile)) {
        fwrite(STDERR, "Error: Seed file {$seedFile} not found.\n");
        exit(1);
    }

    $seedPosts = require $seedFile;
    if (!is_array($seedPosts)) {
        fwrite(STDERR, "Error: Invalid seed file format.\n");
        exit(1);
    }

    echo "=== TECHPILOT SAFE NEWS SEEDER & CONTENT REPAIR ===\n";
    echo "Dry Run Mode: " . ($isDryRun ? "YES" : "NO") . "\n";
    echo "Repair Placeholders: " . ($repairPlaceholders ? "YES" : "NO") . "\n\n";

    $inserted = 0;
    $repaired = 0;
    $skipped  = 0;

    $startedTransaction = false;

    try {
        if (!$isDryRun && !$db->inTransaction()) {
            $db->beginTransaction();
            $startedTransaction = true;
        }

        $selectStmt = $db->prepare("SELECT id, title, slug, content, CHAR_LENGTH(COALESCE(content, '')) AS len FROM posts WHERE slug = :slug LIMIT 1");
        
        $insertStmt = $db->prepare(
            "INSERT INTO posts (
                title, slug, summary, content, image, category_slug, post_type, author_name, status, views, is_featured, reading_minutes, created_at
             ) VALUES (
                :title, :slug, :summary, :content, :image, :category_slug, :post_type, :author_name, :status, :views, :is_featured, :reading_minutes, :created_at
             )"
        );

        $updateStmt = $db->prepare(
            "UPDATE posts SET 
                title = :title,
                summary = :summary,
                content = :content,
                image = :image,
                category_slug = :category_slug,
                post_type = :post_type,
                author_name = :author_name,
                status = :status,
                reading_minutes = :reading_minutes
             WHERE id = :id"
        );

        foreach ($seedPosts as $post) {
            $slug = $post['slug'];
            $selectStmt->execute([':slug' => $slug]);
            $existing = $selectStmt->fetch(PDO::FETCH_ASSOC);

            if (!$existing) {
                if ($isDryRun) {
                    echo "[DRY-RUN] Would insert: {$slug}\n";
                } else {
                    $insertStmt->execute([
                        ':title'           => $post['title'],
                        ':slug'            => $post['slug'],
                        ':summary'         => $post['summary'],
                        ':content'         => $post['content'],
                        ':image'           => $post['image'] ?? 'assets/images/placeholder.jpg',
                        ':category_slug'   => $post['category_slug'] ?? 'cong-nghe',
                        ':post_type'       => $post['post_type'] ?? 'news',
                        ':author_name'     => $post['author_name'] ?? 'Đội ngũ TechPilot',
                        ':status'          => $post['status'] ?? 'published',
                        ':views'           => $post['views'] ?? 0,
                        ':is_featured'     => $post['is_featured'] ?? 0,
                        ':reading_minutes' => $post['reading_minutes'] ?? 5,
                        ':created_at'      => $post['created_at'] ?? date('Y-m-d H:i:s'),
                    ]);
                    echo "[INSERTED] {$slug}\n";
                }
                $inserted++;
            } else {
                $isPlaceholder = isPlaceholderPostContent($existing['content']);
                $isRichContent = !$isPlaceholder && ((int)$existing['len'] >= 100);

                if ($isRichContent) {
                    if ($isDryRun) {
                        echo "[DRY-RUN] Would skip: {$slug} (User/Rich content intact, length: {$existing['len']})\n";
                    } else {
                        echo "[SKIPPED] {$slug} (User/Rich content intact, length: {$existing['len']})\n";
                    }
                    $skipped++;
                } elseif ($isPlaceholder) {
                    if ($repairPlaceholders) {
                        if ($isDryRun) {
                            echo "[DRY-RUN] Would repair: {$slug} (Placeholder length: {$existing['len']})\n";
                        } else {
                            $updateStmt->execute([
                                ':title'           => $post['title'],
                                ':summary'         => $post['summary'],
                                ':content'         => $post['content'],
                                ':image'           => $post['image'] ?? 'assets/images/placeholder.jpg',
                                ':category_slug'   => $post['category_slug'] ?? 'cong-nghe',
                                ':post_type'       => $post['post_type'] ?? 'news',
                                ':author_name'     => $post['author_name'] ?? 'Đội ngũ TechPilot',
                                ':status'          => $post['status'] ?? 'published',
                                ':reading_minutes' => $post['reading_minutes'] ?? 5,
                                ':id'              => $existing['id'],
                            ]);
                            echo "[REPAIRED] {$slug}\n";
                        }
                        $repaired++;
                    } else {
                        if ($isDryRun) {
                            echo "[DRY-RUN] Would skip: {$slug} (Placeholder, requires --repair-placeholders)\n";
                        } else {
                            echo "[SKIPPED] {$slug} (Placeholder, requires --repair-placeholders)\n";
                        }
                        $skipped++;
                    }
                }
            }
        }

        if ($startedTransaction && $db->inTransaction()) {
            $db->commit();
        }

        echo "\nSummary: Inserted={$inserted}, Repaired={$repaired}, Skipped={$skipped}\n";
        echo "Seeder finished cleanly.\n";
    } catch (Throwable $e) {
        if ($startedTransaction && $db->inTransaction()) {
            $db->rollBack();
        }
        fwrite(STDERR, "Error during seeding: " . $e->getMessage() . "\n");
        exit(1);
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    runNewsSeeder();
}
