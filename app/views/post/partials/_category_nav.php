<?php
/**
 * Category Navigation Pills
 * Variable: $currentTag (string)
 */
$tags = [
    ''             => 'Tất cả',
    'laptop'       => 'Laptop',
    'gaming'       => 'PC Gaming',
    'pc-linh-kien' => 'Linh kiện',
    'danh-gia'     => 'Đánh giá',
    'thu-thuat'    => 'Thủ thuật',
    'so-sanh'      => 'So sánh',
    'tin-moi'      => 'Tin mới',
];
?>
<nav class="news-tags" aria-label="Lọc theo chủ đề">
    <?php foreach ($tags as $key => $name): ?>
        <?php
        $isActive = ($currentTag === $key);
        $link = url('post' . (!empty($key) ? '?tag=' . $key : ''));
        ?>
        <a
            href="<?= $link ?>"
            class="news-tag-pill <?= $isActive ? 'is-active' : '' ?>"
            <?= $isActive ? 'aria-current="page"' : '' ?>
        ><?= e($name) ?></a>
    <?php endforeach; ?>
</nav>
