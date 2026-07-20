<div class="key-takeaways">
    <h3><i class="fa-regular fa-lightbulb"></i> Những điểm chính</h3>
    <ul>
        <?php foreach ($article['key_takeaways'] as $takeaway): ?>
            <li><?= e($takeaway) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
