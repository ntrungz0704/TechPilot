<?php
/**
 * View Partial: Author Box (Compact Signature)
 * Contract:
 * - $post (array|null)
 */
$hasRealAuthor = !empty($post['has_real_author']);
$displayAuthor = !empty($post['author_name']) ? $post['author_name'] : 'Đội ngũ TechPilot';
?>

<div class="news-author-box news-author-box--compact" aria-label="Thông tin tác giả">
    <div class="news-author-avatar-compact">
        <i class="fa-solid fa-user-pen" aria-hidden="true"></i>
    </div>
    <div class="news-author-compact-info">
        <span class="news-author-compact-by">Biên soạn bởi:</span>
        <strong class="news-author-compact-name"><?= e($displayAuthor) ?></strong>
        <span class="news-author-compact-divider" aria-hidden="true">•</span>
        <span class="news-author-compact-verified"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> TechPilot Editorial</span>
    </div>
</div>
