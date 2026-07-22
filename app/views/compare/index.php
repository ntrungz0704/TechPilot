<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

<style>
    /* Styling cho so sánh */
    .badge--vfm {
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        display: inline-block;
    }
    .vfm-high { background-color: #D1FAE5; color: #065F46; }
    .vfm-good { background-color: #E0F2FE; color: #075985; }
    .vfm-med { background-color: #FEF3C7; color: #92400E; }
    .vfm-low { background-color: #FEE2E2; color: #991B1B; }

    /* Skeleton Loading cho AI */
    .ai-skeleton {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .ai-skeleton-line {
        height: 16px;
        background: linear-gradient(90deg, #E2E8F0 25%, #EDF2F7 50%, #E2E8F0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: 4px;
    }
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>

<main class="container section" id="main-content" style="margin-top: 40px; min-height: 60vh;">
    <div class="section__head" style="margin-bottom: 30px;">
        <h2><i class="fa-solid fa-code-compare" style="color: var(--primary); margin-right: 8px;"></i> So sánh sản phẩm</h2>
    </div>

    <?php if (isset($flashes['success'])): ?>
        <div class="alert alert--success" style="margin-bottom: 20px; padding: 12px; background-color: #DEF7EC; color: #03543F; border-radius: 8px;">
            <?= e($flashes['success']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($flashes['error'])): ?>
        <div class="alert alert--danger" style="margin-bottom: 20px; padding: 12px; background-color: #FDE8E8; color: #9B1C1C; border-radius: 8px;">
            <?= e($flashes['error']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($flashes['info'])): ?>
        <div class="alert alert--info" style="margin-bottom: 20px; padding: 12px; background-color: #E1EFFE; color: #1E429F; border-radius: 8px;">
            <?= e($flashes['info']) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($products)): ?>
        <div style="text-align: center; padding: 60px 20px; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px;">
            <i class="fa-solid fa-scale-unbalanced" style="font-size: 64px; color: var(--text-secondary); margin-bottom: 20px; display: block;"></i>
            <h3 style="margin-bottom: 10px; font-weight: 700;">Chưa có sản phẩm so sánh</h3>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">Vui lòng thêm sản phẩm vào danh sách so sánh từ các trang sản phẩm.</p>
            <a href="<?= url('/') ?>" class="btn" style="padding: 10px 24px;">Quay lại Trang Chủ</a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto; background-color: var(--bg-white); border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-card);">
            <table style="width: 100%; border-collapse: collapse; min-width: 700px; text-align: left; font-size: 14.5px;">
                <thead>
                    <tr style="background-color: #F9FAFB; border-bottom: 1px solid var(--border);">
                        <th style="padding: 20px; font-weight: 700; color: var(--text-secondary); width: 20%;">Thuộc tính</th>
                        <?php foreach ($products as $p): ?>
                            <th class="prod-col-<?= $p['id'] ?>" style="padding: 20px; width: 20%; position: relative;">
                                <form method="post" action="<?= url('compare/remove') ?>" style="position: absolute; top: 10px; right: 10px;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #9CA3AF; cursor: pointer; font-size: 16px;" title="Xóa khỏi so sánh"><i class="fa-solid fa-xmark-circle"></i></button>
                                </form>
                                <div style="display: flex; flex-direction: column; align-items: center; text-align: center; margin-top: 15px; position: relative;">
                                    <img src="<?= url('assets/images/' . e($p['image'])) ?>" alt="<?= e($p['name']) ?>" style="height: 90px; object-fit: contain; margin-bottom: 15px;">
                                    <strong style="font-size: 13.5px; line-height: 1.4; height: 38px; overflow: hidden; display: block; margin-bottom: 8px;"><?= e($p['name']) ?></strong>
                                    <span style="color: var(--primary); font-weight: 700; font-size: 15px;"><?= number_format($p['price'], 0, ',', '.') ?>đ</span>
                                </div>
                            </th>
                        <?php endforeach; ?>
                        <?php // Điền cột trống nếu so sánh dưới 4 sản phẩm
                        for ($i = 0; $i < (4 - count($products)); $i++): ?>
                            <th style="padding: 20px; width: 20%; text-align: center; color: #D1D5DB; border-left: 1px solid var(--border);">
                                <i class="fa-solid fa-plus-circle" style="font-size: 32px; margin-bottom: 10px;"></i>
                                <span style="display: block; font-size: 13px;">Thêm sản phẩm</span>
                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px 20px; font-weight: 600; color: var(--text-secondary); background-color: #F9FAFB;">Thương hiệu</td>
                        <?php foreach ($products as $p): ?>
                            <td class="prod-col-<?= $p['id'] ?>" style="padding: 15px 20px; font-weight: 500;"><?= e($p['brand_name']) ?></td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (4 - count($products)); $i++): ?>
                            <td style="border-left: 1px solid var(--border);"></td>
                        <?php endfor; ?>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px 20px; font-weight: 600; color: var(--text-secondary); background-color: #F9FAFB;">Danh mục</td>
                        <?php foreach ($products as $p): ?>
                            <td class="prod-col-<?= $p['id'] ?>" style="padding: 15px 20px;"><?= e($p['category_name']) ?></td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (4 - count($products)); $i++): ?>
                            <td style="border-left: 1px solid var(--border);"></td>
                        <?php endfor; ?>
                    </tr>
                    
                    <!-- Điểm đáng tiền VFM -->
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px 20px; font-weight: 600; color: var(--text-secondary); background-color: #F9FAFB;">Độ "Đáng tiền" (VFM)</td>
                        <?php foreach ($products as $p): 
                            require_once ROOT_PATH . '/app/services/ProductIntelligenceService.php';
                            $vfm = ProductIntelligenceService::calculateValueForMoney($p);
                        ?>
                            <td class="prod-col-<?= $p['id'] ?>" style="padding: 15px 20px; font-weight: 700; color: #10B981;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-star" style="color: #FBBF24;"></i>
                                    <span><?= $vfm ?> / 10</span>
                                </div>
                            </td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (4 - count($products)); $i++): ?>
                            <td style="border-left: 1px solid var(--border);"></td>
                        <?php endfor; ?>
                    </tr>

                    <!-- So sánh Hiệu năng / Giá -->
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px 20px; font-weight: 600; color: var(--text-secondary); background-color: #F9FAFB;">Hiệu năng / Giá (P/P)</td>
                        <?php foreach ($products as $p): 
                            $pp = ProductIntelligenceService::calculatePerformancePriceRatio($p);
                        ?>
                            <td class="prod-col-<?= $p['id'] ?>" style="padding: 15px 20px;">
                                <span class="badge--vfm <?= $pp['class'] ?>"><?= $pp['label'] ?></span>
                            </td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (4 - count($products)); $i++): ?>
                            <td style="border-left: 1px solid var(--border);"></td>
                        <?php endfor; ?>
                    </tr>

                    <!-- Ước tính FPS Game -->
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px 20px; font-weight: 600; color: var(--text-secondary); background-color: #F9FAFB;">Hiệu năng Game (FPS)</td>
                        <?php foreach ($products as $p): 
                            $specs = json_decode($p['specs'] ?? '{}', true) ?: [];
                            $categorySlug = $p['category_slug'] ?? $p['category_name'] ?? '';
                            $fpsList = ProductIntelligenceService::estimateFps($specs, $categorySlug);
                        ?>
                            <td class="prod-col-<?= $p['id'] ?>" style="padding: 15px 20px;">
                                <?php if (empty($fpsList)): ?>
                                    <span style="color: var(--text-secondary); font-size: 13px;">Không hỗ trợ game</span>
                                <?php else: ?>
                                    <div style="display: flex; flex-direction: column; gap: 6px; font-size: 12.5px;">
                                        <?php foreach ($fpsList as $game): ?>
                                            <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #E2E8F0; padding-bottom: 2px;">
                                                <span style="color: var(--text-secondary); font-size: 11.5px;"><?= $game['name'] ?>:</span>
                                                <strong style="color: #1E3A8A; font-size: 12px;"><?= $game['fps'] ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (4 - count($products)); $i++): ?>
                            <td style="border-left: 1px solid var(--border);"></td>
                        <?php endfor; ?>
                    </tr>
                    
                    <?php
                    // Tổng hợp specs từ tất cả các sản phẩm để làm hàng so sánh
                    $allSpecsKeys = [];
                    foreach ($products as $p) {
                        $specs = json_decode($p['specs'] ?? '{}', true);
                        if (is_array($specs)) {
                            foreach (array_keys($specs) as $key) {
                                if (!in_array($key, $allSpecsKeys)) {
                                    $allSpecsKeys[] = $key;
                                }
                            }
                        }
                    }

                    if (empty($allSpecsKeys)) {
                        $allSpecsKeys = ['CPU', 'RAM', 'SSD', 'VGA'];
                    }

                    foreach ($allSpecsKeys as $key):
                    ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 15px 20px; font-weight: 600; color: var(--text-secondary); background-color: #F9FAFB;"><?= e($key) ?></td>
                            <?php foreach ($products as $p): 
                                $specs = json_decode($p['specs'] ?? '{}', true);
                                $val = $specs[$key] ?? '-';
                            ?>
                                <td class="prod-col-<?= $p['id'] ?>" style="padding: 15px 20px; font-weight: 500;"><?= e($val) ?></td>
                            <?php endforeach; ?>
                            <?php for ($i = 0; $i < (4 - count($products)); $i++): ?>
                                <td style="border-left: 1px solid var(--border);"></td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>

                    <tr>
                        <td style="padding: 15px 20px; font-weight: 600; color: var(--text-secondary); background-color: #F9FAFB;">Hành động</td>
                        <?php foreach ($products as $p): ?>
                            <td class="prod-col-<?= $p['id'] ?>" style="padding: 20px 15px;">
                                <form method="post" action="<?= url('cart/add') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn--sm" style="width: 100%; text-align: center;"><i class="fa-solid fa-cart-shopping" style="margin-right: 6px;"></i> Thêm giỏ hàng</button>
                                </form>
                            </td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (4 - count($products)); $i++): ?>
                            <td style="border-left: 1px solid var(--border);"></td>
                        <?php endfor; ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if (count($products) >= 2): ?>
            <!-- Phần phân tích AI -->
            <div style="margin-top: 40px; display: flex; flex-direction: column; align-items: center; gap: 20px; margin-bottom: 50px;">
                <button id="btnAiCompare" class="btn" style="padding: 12px 32px; font-weight: 700; background: linear-gradient(135deg, #0A5BFF 0%, #0046CC 100%); border: none; border-radius: 8px; box-shadow: 0 4px 15px rgba(10, 91, 255, 0.4); cursor: pointer; display: flex; align-items: center; gap: 8px; color: #FFFFFF;" onclick="runAiCompare()">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Phân tích So sánh bằng AI
                </button>

                <div id="aiCompareResult" style="display: none; width: 100%; padding: 24px; background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; box-shadow: var(--shadow-card); box-sizing: border-box;">
                    <h4 style="margin: 0 0 15px 0; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 8px; color: var(--primary);">
                        <i class="fa-solid fa-robot"></i> Đánh giá từ Trợ lý ảo TechPilot AI
                    </h4>
                    <div id="aiCompareContent" style="font-size: 14.5px; line-height: 1.7; color: #334155;">
                        <!-- AI content will be loaded here -->
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

<script>
    function runAiCompare() {
        const btn = document.getElementById('btnAiCompare');
        const resultDiv = document.getElementById('aiCompareResult');
        const contentDiv = document.getElementById('aiCompareContent');

        // Hiển thị khung kết quả và đổi trạng thái nút
        resultDiv.style.display = 'block';
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Đang phân tích cấu hình...';

        // Skeleton loading
        contentDiv.innerHTML = `
            <div class="ai-skeleton">
                <div class="ai-skeleton-line" style="width: 40%"></div>
                <div class="ai-skeleton-line" style="width: 85%"></div>
                <div class="ai-skeleton-line" style="width: 70%"></div>
                <div class="ai-skeleton-line" style="width: 90%"></div>
                <div class="ai-skeleton-line" style="width: 60%"></div>
            </div>
        `;

        // Gọi AJAX tới API
        fetch('<?= url("ai/compare") ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: '_csrf=<?= $_SESSION["csrf_token"] ?? "" ?>'
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Phân tích So sánh bằng AI';

            if (res.success) {
                // Render kết quả (Format Markdown thô sơ)
                contentDiv.innerHTML = formatMarkdown(res.analysis);

                // Highlight sản phẩm được đề xuất
                const recommendedId = res.recommended_id;
                
                // Reset viền các cột trước đó
                document.querySelectorAll('[class^="prod-col-"]').forEach(el => {
                    el.style.borderLeft = '';
                    el.style.borderRight = '';
                    el.style.backgroundColor = '';
                });

                // Highlight cột được chọn
                document.querySelectorAll(`.prod-col-${recommendedId}`).forEach(el => {
                    el.style.borderLeft = '2px solid #10B981';
                    el.style.borderRight = '2px solid #10B981';
                    el.style.backgroundColor = 'rgba(16, 185, 129, 0.04)';
                });

                // Thêm nhãn AI đề xuất ở header
                const headerEl = document.querySelector(`th.prod-col-${recommendedId}`);
                if (headerEl) {
                    // Xóa các badge cũ
                    const oldBadge = headerEl.querySelector('.ai-badge');
                    if (oldBadge) oldBadge.remove();

                    const badge = document.createElement('div');
                    badge.className = 'ai-badge';
                    badge.innerHTML = '<i class="fa-solid fa-thumbs-up"></i> AI ĐỀ XUẤT';
                    badge.style.cssText = 'position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background-color: #10B981; color: #FFFFFF; font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 12px; box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4); white-space: nowrap;';
                    headerEl.querySelector('div').prepend(badge);
                }
            } else {
                contentDiv.innerHTML = `<span style="color: #EF4444;"><i class="fa-solid fa-triangle-exclamation"></i> Lỗi: ${res.message}</span>`;
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Phân tích So sánh bằng AI';
            contentDiv.innerHTML = '<span style="color: #EF4444;"><i class="fa-solid fa-triangle-exclamation"></i> Không thể kết nối tới API AI. Vui lòng thử lại.</span>';
            console.error(err);
        });
    }

    // Định dạng markdown thô sơ cho hiển thị
    function formatMarkdown(text) {
        return text
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/### (.*?)(<br>|$)/g, '<h4 style="color:#1E3A8A; font-weight:700; margin: 15px 0 8px 0;">$1</h4>')
            .replace(/#### (.*?)(<br>|$)/g, '<h5 style="color:#2563EB; font-weight:700; margin: 12px 0 6px 0;">$1</h5>')
            .replace(/• (.*?)(<br>|$)/g, '<li style="margin-left: 20px; margin-bottom: 4px;">$1</li>');
    }
</script>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
