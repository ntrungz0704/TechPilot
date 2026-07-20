<section class="faq-section">
    <h2>Câu hỏi thường gặp</h2>
    <div class="faq-accordion">
        <?php foreach ($article['faq'] as $index => $faqItem): ?>
            <div class="faq-item">
                <button class="faq-button" aria-expanded="false" aria-controls="faq-content-<?= $index ?>">
                    <?= e($faqItem['question']) ?>
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="faq-content" id="faq-content-<?= $index ?>">
                    <p><?= e($faqItem['answer']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
