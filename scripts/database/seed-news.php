<?php
/**
 * scripts/database/seed-news.php
 * Safe, non-destructive, per-slug CLI seeder & content repair script for TechPilot News.
 *
 * Options:
 *   --dry-run              Inspect and display planned changes without modifying database.
 *   --repair-placeholders  Allow repairing posts that currently contain empty/placeholder content.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script can only run via CLI.\n");
    exit(1);
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/services/NewsSeederService.php';

function runNewsSeeder(): void
{
    $options = getopt('', ['dry-run', 'repair-placeholders']);
    $isDryRun = isset($options['dry-run']);
    $repairPlaceholders = isset($options['repair-placeholders']);

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

    try {
        $seeder = new NewsSeederService();
        $result = $seeder->run($seedPosts, $isDryRun, $repairPlaceholders);

        foreach ($result['actions'] as $action) {
            echo $action . "\n";
        }

        $totalSkipped = $result['skipped_rich'] + $result['skipped_placeholder'];
        echo "\nSummary: Inserted={$result['inserted']}, Repaired={$result['repaired']}, Skipped={$totalSkipped}\n";
        echo "Seeder finished cleanly.\n";
        exit(0);
    } catch (Throwable $e) {
        fwrite(STDERR, "Error during seeding: " . $e->getMessage() . "\n");
        exit(1);
    }
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    runNewsSeeder();
}
