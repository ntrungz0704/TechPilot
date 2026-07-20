<div class="toc-widget">
    <h4>Mục lục bài viết</h4>
    <ul class="toc-list">
        <?php foreach ($article['sections'] as $section): ?>
            <li><a href="#<?= e($section['id']) ?>"><?= e($section['heading']) ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
