<?php

/**
 * Migration: Sync official brand logos with database
 * Idempotent: Updates brand logo paths from config/brand-logo-sources.php
 */
class Migration_2026_07_29_000003_sync_official_brand_logos
{
    public static function up(PDO $db): bool
    {
        $sourcesPath = ROOT_PATH . '/config/brand-logo-sources.php';
        if (!file_exists($sourcesPath)) {
            return false;
        }

        $sources = require $sourcesPath;
        if (!is_array($sources)) {
            return false;
        }

        $stmt = $db->prepare("UPDATE `brands` SET `logo` = :logo WHERE `slug` = :slug");

        foreach ($sources as $slug => $data) {
            $logoPath = $data['file'] ?? null;
            $stmt->execute([
                ':logo' => $logoPath,
                ':slug' => $slug
            ]);
        }

        return true;
    }

    public static function down(PDO $db): bool
    {
        return true;
    }
}
