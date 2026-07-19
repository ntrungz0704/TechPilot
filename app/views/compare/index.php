<?php include ROOT_PATH . '/app/views/layouts/header.php'; ?>

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
            <table style="width: 100%; border-collapse: collapse; min-width: 600px; text-align: left; font-size: 14.5px;">
                <thead>
                    <tr style="background-color: #F9FAFB; border-bottom: 1px solid var(--border);">
                        <th style="padding: 20px; font-weight: 700; color: var(--text-secondary); width: 25%;">Thuộc tính</th>
                        <?php foreach ($products as $p): ?>
                            <th style="padding: 20px; width: 25%; position: relative;">
                                <form method="post" action="<?= url('compare/remove') ?>" style="position: absolute; top: 10px; right: 10px;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" style="background: none; border: none; color: #9CA3AF; cursor: pointer; font-size: 16px;" title="Xóa khỏi so sánh"><i class="fa-solid fa-xmark-circle"></i></button>
                                </form>
                                <div style="display: flex; flex-direction: column; align-items: center; text-align: center; margin-top: 10px;">
                                    <img src="<?= url('assets/images/' . e($p['image'])) ?>" alt="<?= e($p['name']) ?>" style="height: 100px; object-fit: contain; margin-bottom: 15px;">
                                    <strong style="font-size: 14px; line-height: 1.4; height: 40px; overflow: hidden; display: block; margin-bottom: 8px;"><?= e($p['name']) ?></strong>
                                    <span style="color: var(--primary); font-weight: 700; font-size: 16px;"><?= number_format($p['price'], 0, ',', '.') ?>đ</span>
                                </div>
                            </th>
                        <?php endforeach; ?>
                        <?php // Điền cột trống nếu so sánh dưới 3 sản phẩm
                        for ($i = 0; $i < (3 - count($products)); $i++): ?>
                            <th style="padding: 20px; width: 25%; text-align: center; color: #D1D5DB; border-left: 1px solid var(--border);">
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
                            <td style="padding: 15px 20px; font-weight: 500;"><?= e($p['brand_name']) ?></td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (3 - count($products)); $i++): ?>
                            <td style="border-left: 1px solid var(--border);"></td>
                        <?php endfor; ?>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 15px 20px; font-weight: 600; color: var(--text-secondary); background-color: #F9FAFB;">Danh mục</td>
                        <?php foreach ($products as $p): ?>
                            <td style="padding: 15px 20px;"><?= e($p['category_name']) ?></td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (3 - count($products)); $i++): ?>
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
                        $allSpecsKeys = ['CPU', 'RAM', 'SSD', 'VGA']; // default specs keys
                    }

                    foreach ($allSpecsKeys as $key):
                    ?>
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 15px 20px; font-weight: 600; color: var(--text-secondary); background-color: #F9FAFB;"><?= e($key) ?></td>
                            <?php foreach ($products as $p): 
                                $specs = json_decode($p['specs'] ?? '{}', true);
                                $val = $specs[$key] ?? '-';
                            ?>
                                <td style="padding: 15px 20px; font-weight: 500;"><?= e($val) ?></td>
                            <?php endforeach; ?>
                            <?php for ($i = 0; $i < (3 - count($products)); $i++): ?>
                                <td style="border-left: 1px solid var(--border);"></td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>

                    <tr>
                        <td style="padding: 15px 20px; font-weight: 600; color: var(--text-secondary); background-color: #F9FAFB;">Hành động</td>
                        <?php foreach ($products as $p): ?>
                            <td style="padding: 20px 15px;">
                                <form method="post" action="<?= url('cart/add') ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn--sm" style="width: 100%; text-align: center;"><i class="fa-solid fa-cart-shopping" style="margin-right: 6px;"></i> Thêm giỏ hàng</button>
                                </form>
                            </td>
                        <?php endforeach; ?>
                        <?php for ($i = 0; $i < (3 - count($products)); $i++): ?>
                            <td style="border-left: 1px solid var(--border);"></td>
                        <?php endfor; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php include ROOT_PATH . '/app/views/layouts/footer.php'; ?>
