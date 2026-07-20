<div class="comparison-table-wrap">
    <table class="comparison-table">
        <caption style="font-weight: 600; text-align: left; margin-bottom: 12px; color: var(--news-dark); font-size: 1.25rem;"><?= e($article['comparison']['title']) ?></caption>
        <thead>
            <tr>
                <th>Tiêu chí</th>
                <th>Laptop cũ</th>
                <th>Laptop mới</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($article['comparison']['criteria'] as $index => $criterion): ?>
                <tr>
                    <td><strong><?= e($criterion) ?></strong></td>
                    <td><?= e($article['comparison']['old_laptop'][$index] ?? '') ?></td>
                    <td><?= e($article['comparison']['new_laptop'][$index] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
