<section class="product-recommendations">
    <h2 class="section-title">Gợi ý từ TechPilot</h2>
    <div class="product-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px;">
        <?php foreach ($recommendedProducts as $product): ?>
            <!-- Sử dụng lại CSS của product card hệ thống hiện tại hoặc tạo tối giản -->
            <div class="product-card" style="border: 1px solid var(--news-border); border-radius: 8px; padding: 16px; text-align: center; background: white;">
                <img src="<?= e(productImageUrl($product['image'] ?? '')) ?>" alt="<?= e($product['name']) ?>" style="height: 160px; object-fit: contain; margin-bottom: 12px;">
                <h3 style="font-size: 1rem; margin-bottom: 8px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><a href="<?= url('product/detail/' . $product['slug']) ?>" style="color: inherit;"><?= e($product['name']) ?></a></h3>
                <div style="color: var(--news-error); font-weight: 700; font-size: 1.1rem; margin-bottom: 12px;"><?= formatPrice($product['price']) ?></div>
                <a href="<?= url('product/detail/' . $product['slug']) ?>" class="btn btn-primary" style="display: block; background: var(--news-primary); color: white; border-radius: 4px; padding: 8px;">Xem chi tiết</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>
