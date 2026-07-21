<?php
/**
 * View Partial Table of Contents (Dual TOC support)
 * Contract:
 * - $tocVariant (string) 'mobile' | 'desktop'
 * - $tocIdPrefix (string) e.g. 'mobile-toc' | 'desktop-toc'
 * - $articleHeadings (array)
 */

$tocVariant      = $tocVariant ?? 'mobile';
$tocIdPrefix     = $tocIdPrefix ?? 'toc';
$articleHeadings = is_array($articleHeadings ?? null) ? $articleHeadings : [];

if (empty($articleHeadings)) return;

$headerId = $tocIdPrefix . '-title';
$listId   = $tocIdPrefix . '-list';
$isDesktop = ($tocVariant === 'desktop');
?>

<nav class="news-toc news-toc--<?= e($tocVariant) ?>" aria-labelledby="<?= e($headerId) ?>">
    <div class="news-toc-header">
        <strong id="<?= e($headerId) ?>">Nội dung bài viết</strong>
        <button
            type="button"
            class="news-toc-toggle"
            aria-expanded="<?= $isDesktop ? 'true' : 'false' ?>"
            aria-controls="<?= e($listId) ?>"
            aria-label="Thu gọn/Mở rộng mục lục"
        >
            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
        </button>
    </div>
    <ul id="<?= e($listId) ?>" class="news-toc-list">
        <?php foreach ($articleHeadings as $heading): ?>
            <li class="news-toc-item news-toc-level-<?= (int)($heading['level'] ?? 2) ?>">
                <a href="#<?= e($heading['id'] ?? '') ?>"><?= e($heading['text'] ?? '') ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
