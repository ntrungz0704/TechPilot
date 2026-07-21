<?php
/**
 * View Partial: Author Box (MVP - Real Data / Team Fallback)
 * Contract:
 * - $post (array|null)
 */
$hasRealAuthor = !empty($post['has_real_author']);
$displayAuthor = !empty($post['author_name']) ? $post['author_name'] : 'Đội ngũ TechPilot';
?>

<div class="news-author-box" aria-label="Thông tin tác giả">
    <div class="news-author-avatar-fallback">
        <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
    </div>
    <div class="news-author-info">
        <span class="news-author-label">Tác giả bài viết</span>
        <strong class="news-author-name"><?= e($displayAuthor) ?></strong>
        <p class="news-author-note">
            <?= $hasRealAuthor
                ? 'Bài viết được tổng hợp và trình bày bởi tác giả biên tập TechPilot.'
                : 'Bài viết được tổng hợp và biên tập bởi Đội ngũ tin tức TechPilot.'
            ?>
        </p>
    </div>
</div>
