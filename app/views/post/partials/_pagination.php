<?php
/**
 * Pagination Partial
 * Cần các biến: $currentPage, $totalPages, $pageQueryParams
 */
if ($totalPages <= 1) return;

// Tính toán các trang hiển thị
$startPage = max(2, $currentPage - 2);
$endPage   = min($totalPages - 1, $currentPage + 2);
?>
<nav class="news-pagination" aria-label="Phân trang">
    <?php if ($currentPage > 1): ?>
        <?php $prevParams = array_merge($pageQueryParams, ['page' => $currentPage - 1]); ?>
        <a href="<?= url('post?' . http_build_query($prevParams)) ?>" class="page-btn" aria-label="Trang trước">
            <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </a>
    <?php endif; ?>

    <!-- Trang đầu tiên -->
    <?php $firstParams = array_merge($pageQueryParams, ['page' => 1]); ?>
    <a href="<?= url('post?' . http_build_query($firstParams)) ?>" class="page-btn <?= $currentPage === 1 ? 'is-active' : '' ?>" <?= $currentPage === 1 ? 'aria-current="page"' : '' ?>>1</a>

    <!-- Dấu ba chấm nếu startPage > 2 -->
    <?php if ($startPage > 2): ?>
        <span class="page-dots" aria-hidden="true">&hellip;</span>
    <?php endif; ?>

    <!-- Các trang ở giữa -->
    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
        <?php $iParams = array_merge($pageQueryParams, ['page' => $i]); ?>
        <a href="<?= url('post?' . http_build_query($iParams)) ?>" class="page-btn <?= $currentPage === $i ? 'is-active' : '' ?>" <?= $currentPage === $i ? 'aria-current="page"' : '' ?>><?= $i ?></a>
    <?php endfor; ?>

    <!-- Dấu ba chấm nếu endPage < $totalPages - 1 -->
    <?php if ($endPage < $totalPages - 1): ?>
        <span class="page-dots" aria-hidden="true">&hellip;</span>
    <?php endif; ?>

    <!-- Trang cuối cùng -->
    <?php $lastParams = array_merge($pageQueryParams, ['page' => $totalPages]); ?>
    <a href="<?= url('post?' . http_build_query($lastParams)) ?>" class="page-btn <?= $currentPage === $totalPages ? 'is-active' : '' ?>" <?= $currentPage === $totalPages ? 'aria-current="page"' : '' ?>><?= $totalPages ?></a>

    <?php if ($currentPage < $totalPages): ?>
        <?php $nextParams = array_merge($pageQueryParams, ['page' => $currentPage + 1]); ?>
        <a href="<?= url('post?' . http_build_query($nextParams)) ?>" class="page-btn" aria-label="Trang sau">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </a>
    <?php endif; ?>
</nav>
