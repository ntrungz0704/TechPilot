<div class="card">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 class="card-title" style="margin-bottom: 0;">Danh sách sản phẩm</h3>
        <a href="<?= url('admin/products/create') ?>" class="btn"><i class="fa-solid fa-plus"></i> Thêm sản phẩm mới</a>
    </div>

    <!-- Filters & Search Form -->
    <form method="get" action="<?= url('admin/products') ?>" style="margin-bottom: 25px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 15px;">
        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--text-secondary); display: block; margin-bottom: 6px;">Tìm theo tên</label>
            <input type="text" name="search" class="form-control" placeholder="Từ khoá..." value="<?= e($search) ?>">
        </div>
        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--text-secondary); display: block; margin-bottom: 6px;">Danh mục</label>
            <select name="category_id" class="form-control">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--text-secondary); display: block; margin-bottom: 6px;">Thương hiệu</label>
            <select name="brand_id" class="form-control">
                <option value="">Tất cả thương hiệu</option>
                <?php foreach ($brands as $b): ?>
                    <option value="<?= (int)$b['id'] ?>" <?= $brandId === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size: 12px; font-weight: 700; color: var(--text-secondary); display: block; margin-bottom: 6px;">Trạng thái</label>
            <select name="status" class="form-control">
                <option value="">Tất cả trạng thái</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Hiển thị</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Tạm ẩn/Khoá</option>
            </select>
        </div>
        <div style="display: flex; align-items: flex-end; gap: 8px;">
            <label class="checkbox" style="font-size: 12px; font-weight: 700; color: var(--text-secondary); display: flex; align-items: center; gap: 6px; height: 40px; margin-bottom: 0; cursor: pointer;">
                <input type="checkbox" name="low_stock" value="1" <?= $lowStock === 1 ? 'checked' : '' ?>> Tồn kho thấp (<10)
            </label>
        </div>
        <div style="display: flex; align-items: flex-end; gap: 8px;">
            <button type="submit" class="btn btn--outline" style="width: 100%; height: 40px; justify-content: center;"><i class="fa-solid fa-filter"></i> Lọc</button>
            <?php if ($search !== '' || $categoryId > 0 || $brandId > 0 || $status !== '' || $lowStock > 0): ?>
                <a href="<?= url('admin/products') ?>" class="btn btn--secondary" style="height: 40px; display: inline-flex; align-items: center; justify-content: center;" title="Xoá tất cả bộ lọc"><i class="fa-solid fa-filter-circle-xmark"></i></a>
            <?php endif; ?>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th style="width: 80px;">Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục / Brand</th>
                    <th>Giá gốc</th>
                    <th>Giá Sale</th>
                    <th>Tồn kho</th>
                    <th>Trạng thái</th>
                    <th style="width: 180px; text-align: center;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td><?= (int)$p['id'] ?></td>
                            <td>
                                <img src="<?= e(productImageUrl($p['image'] ?? '', $p['name'] ?? '')) ?>" alt="<?= e($p['name']) ?>" style="width: 44px; height: 44px; object-fit: contain; border: 1px solid var(--border); border-radius: 4px; padding: 2px; background: var(--bg-body);">
                            </td>
                            <td>
                                <strong style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 13.5px;"><?= e($p['name']) ?></strong>
                            </td>
                            <td>
                                <span style="font-size: 12.5px; display: block; font-weight: 600; color: var(--text-primary);"><?= e($p['category_name']) ?></span>
                                <small style="color: var(--text-secondary);"><?= e($p['brand_name']) ?></small>
                            </td>
                            <td><?= formatPrice($p['price']) ?></td>
                            <td>
                                <?php if ($p['sale_price'] !== null): ?>
                                    <span style="color: var(--primary); font-weight: 700;"><?= formatPrice($p['sale_price']) ?></span>
                                <?php else: ?>
                                    <span style="color: #9CA3AF; font-style: italic;">Không sale</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ((int)$p['stock'] < 10): ?>
                                    <span class="badge badge--danger" style="font-weight: 700;"><?= (int)$p['stock'] ?> chiếc</span>
                                <?php else: ?>
                                    <span class="badge badge--success" style="background-color: #E0F2FE; color: #0369A1;"><?= (int)$p['stock'] ?> chiếc</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $p['status'] === 'active' ? 'badge--success' : 'badge--danger' ?>">
                                    <?= $p['status'] === 'active' ? 'Hiển thị' : 'Ẩn/Khoá' ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center; align-items: center; min-height: 38px; flex-wrap: wrap;">
                                    <a href="<?= url('admin/products/edit/' . $p['id']) ?>" class="btn btn--outline btn--sm" style="padding: 6px 10px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-pen-to-square"></i> Sửa</a>
                                    
                                    <form method="post" action="<?= url('admin/products/delete/' . $p['id']) ?>" onsubmit="return confirm('Bạn có chắc chắn muốn xoá sản phẩm này?');" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--danger btn--sm" style="padding: 6px 10px; font-size: 12px; white-space: nowrap;"><i class="fa-solid fa-trash-can"></i> Xoá</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-secondary); padding: 30px;">Không tìm thấy sản phẩm nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div style="display: flex; justify-content: center; gap: 8px; margin-top: 25px;">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                // Giữ lại bộ lọc query string khi phân trang
                $query = $_GET;
                $query['page'] = $i;
                $pageUrl = url('admin/products?' . http_build_query($query));
                ?>
                <a href="<?= $pageUrl ?>" class="btn <?= $page === $i ? '' : 'btn--outline' ?>" style="padding: 6px 12px; font-size: 13px;"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
