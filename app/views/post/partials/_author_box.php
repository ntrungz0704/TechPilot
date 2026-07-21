<?php
/**
 * View Partial: Author Box (MVP - Real Data / Team Fallback)
 * Contract:
 * - $post (array|null)
 */
$authorName = !empty($post['author_name'])
    ? trim((string)$post['author_name'])
    : (!empty($post['full_name']) ? trim((string)$post['full_name']) : '');

$hasRealAuthor = $authorName !== '';
$displayAuthor = $hasRealAuthor ? $authorName : 'Đội ngũ TechPilot';
?>

<div class="news-author-box" aria-label="Thông tin tác giả">
    <div class="news-author-avatar-fallback">
        <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
    </div>
    <div class="news-author-info">
        <span class="news-author-label">Tác giả bài viết</span>
        <h4 class="news-author-name"><?= e($displayAuthor) ?></h4>
        <p class="news-author-note">
            <?= $hasRealAuthor
                ? 'Bài viết được biên soạn và kiểm duyệt bởi tác giả biên tập TechPilot.'
                : 'Bài viết được tổng hợp và biên định bởi Đội ngũ tin tức TechPilot.'
            ?>
        </p>
    </div>
</div>
