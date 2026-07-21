-- ============================================================================
-- Migration: Add 'howto' to post_type ENUM (2026-07-21)
-- ============================================================================

ALTER TABLE posts MODIFY COLUMN post_type ENUM('news', 'review', 'guide', 'comparison', 'howto') NOT NULL DEFAULT 'news';
