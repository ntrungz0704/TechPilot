<?php
/**
 * app/services/NewsSeederService.php
 * Production Seeder & Content Repair Service for TechPilot News.
 * Guarantees zero overwriting of rich user content and metadata preservation during placeholder repair.
 */

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/app/models/Post.php';

class NewsSeederService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $connection = $db ?? Database::getConnection();
        if (!$connection) {
            throw new RuntimeException('Database connection unavailable for NewsSeederService.');
        }
        $this->db = $connection;
    }

    public function run(
        array $seedPosts,
        bool $dryRun = false,
        bool $repairPlaceholders = false
    ): array {
        $inserted           = 0;
        $repaired           = 0;
        $skippedRich        = 0;
        $skippedPlaceholder = 0;
        $failed             = 0;
        $actions            = [];

        $startedTransaction = false;

        try {
            if (!$dryRun && !$this->db->inTransaction()) {
                $this->db->beginTransaction();
                $startedTransaction = true;
            }

            $selectStmt = $this->db->prepare(
                "SELECT id, title, slug, summary, content, image, category_slug, post_type, author_id, author_name, status, views, is_featured, reading_minutes, created_at, published_at, CHAR_LENGTH(COALESCE(content, '')) AS len 
                 FROM posts WHERE slug = :slug LIMIT 1"
            );

            $insertStmt = $this->db->prepare(
                "INSERT INTO posts (
                    title, slug, summary, content, image, category_slug, post_type, author_name, status, views, is_featured, reading_minutes, created_at
                 ) VALUES (
                    :title, :slug, :summary, :content, :image, :category_slug, :post_type, :author_name, :status, :views, :is_featured, :reading_minutes, :created_at
                 )"
            );

            $updateStmt = $this->db->prepare(
                "UPDATE posts SET
                    title = :title,
                    summary = :summary,
                    content = :content,
                    image = CASE
                        WHEN image IS NULL OR TRIM(image) = ''
                        THEN :seed_image
                        ELSE image
                    END,
                    category_slug = CASE
                        WHEN category_slug IS NULL OR TRIM(category_slug) = ''
                        THEN :category_slug
                        ELSE category_slug
                    END,
                    post_type = CASE
                        WHEN post_type IS NULL OR TRIM(post_type) = ''
                        THEN :post_type
                        ELSE post_type
                    END,
                    author_name = CASE
                        WHEN author_name IS NULL OR TRIM(author_name) = ''
                        THEN :author_name
                        ELSE author_name
                    END,
                    reading_minutes = CASE
                        WHEN reading_minutes IS NULL OR reading_minutes = 0
                        THEN :reading_minutes
                        ELSE reading_minutes
                    END,
                    updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );

            foreach ($seedPosts as $post) {
                $slug = $post['slug'] ?? '';
                if (empty($slug)) {
                    $failed++;
                    continue;
                }

                $selectStmt->execute([':slug' => $slug]);
                $existing = $selectStmt->fetch(PDO::FETCH_ASSOC);

                if (!$existing) {
                    if ($dryRun) {
                        $actions[] = "[DRY-RUN] Would insert: {$slug}";
                    } else {
                        $insertStmt->execute([
                            ':title'           => $post['title'] ?? '',
                            ':slug'            => $slug,
                            ':summary'         => $post['summary'] ?? '',
                            ':content'         => $post['content'] ?? '',
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
                        $actions[] = "[INSERTED] {$slug}";
                    }
                    $inserted++;
                } else {
                    $isPlaceholder = Post::isPlaceholderContent($existing['content']);
                    $isRichContent = !$isPlaceholder && ((int)$existing['len'] >= 100);

                    if ($isRichContent) {
                        $actions[] = "[SKIPPED] {$slug} (User/Rich content intact, length: {$existing['len']})";
                        $skippedRich++;
                    } elseif ($isPlaceholder) {
                        if ($repairPlaceholders) {
                            if ($dryRun) {
                                $actions[] = "[DRY-RUN] Would repair: {$slug} (Placeholder length: {$existing['len']})";
                            } else {
                                $updateStmt->execute([
                                    ':title'           => $post['title'] ?? $existing['title'],
                                    ':summary'         => $post['summary'] ?? $existing['summary'],
                                    ':content'         => $post['content'] ?? $existing['content'],
                                    ':seed_image'      => $post['image'] ?? 'assets/images/placeholder.jpg',
                                    ':category_slug'   => $post['category_slug'] ?? 'cong-nghe',
                                    ':post_type'       => $post['post_type'] ?? 'news',
                                    ':author_name'     => $post['author_name'] ?? 'Đội ngũ TechPilot',
                                    ':reading_minutes' => $post['reading_minutes'] ?? 5,
                                    ':id'              => $existing['id'],
                                ]);
                                $actions[] = "[REPAIRED] {$slug}";
                            }
                            $repaired++;
                        } else {
                            $actions[] = "[SKIPPED] {$slug} (Placeholder content, requires --repair-placeholders)";
                            $skippedPlaceholder++;
                        }
                    }
                }
            }

            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->commit();
            }
        } catch (Throwable $e) {
            if ($startedTransaction && $this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        return [
            'inserted'            => $inserted,
            'repaired'            => $repaired,
            'skipped_rich'        => $skippedRich,
            'skipped_placeholder' => $skippedPlaceholder,
            'failed'              => $failed,
            'actions'             => $actions,
        ];
    }
}
